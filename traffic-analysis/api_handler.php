<?php
/**
 * Trafik Kazası Tutanak Analiz Sistemi - API İşleyici
 *
 * Bu dosya yüklenen görselleri AI servisine gönderir ve analiz sonucunu döndürür.
 * Birden fazla API anahtarını sırasıyla dener (fallback sistemi).
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Geçersiz istek yöntemi.'], 405);
}

// CSRF kontrolü
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCSRF($csrfToken)) {
    jsonResponse(['success' => false, 'error' => 'Güvenlik doğrulaması başarısız.'], 403);
}

// Session kontrolü
if (empty($_SESSION['upload_session'])) {
    jsonResponse(['success' => false, 'error' => 'Geçerli bir yükleme oturumu bulunamadı. Lütfen önce dosyalarınızı yükleyin.'], 400);
}

$uploadSession = $_SESSION['upload_session'];

// Oturum zaman aşımı kontrolü (30 dakika)
if ((time() - $uploadSession['time']) > 1800) {
    cleanupTempFiles(array_column($uploadSession['files'], 'path'));
    unset($_SESSION['upload_session']);
    jsonResponse(['success' => false, 'error' => 'Yükleme oturumu zaman aşımına uğradı. Lütfen dosyalarınızı tekrar yükleyin.'], 408);
}

// Taraf beyanlarını al
$tarafA = trim($_POST['taraf_a'] ?? '');
$tarafB = trim($_POST['taraf_b'] ?? '');

// Görselleri hazırla
$images = [];
$filePaths = [];

foreach ($uploadSession['files'] as $file) {
    if (!file_exists($file['path'])) {
        continue;
    }
    $images[] = [
        'base64'   => imageToBase64($file['path'], $file['mime']),
        'mime'     => $file['mime'],
        'original' => $file['original'],
    ];
    $filePaths[] = $file['path'];
}

if (empty($images)) {
    jsonResponse(['success' => false, 'error' => 'Yüklenen dosyalar bulunamadı.'], 404);
}

// AI analiz promptu
$systemPrompt = getAnalysisSystemPrompt();
$userPrompt = getAnalysisUserPrompt($tarafA, $tarafB);

// API anahtarlarını yükle (fallback sistemli)
$apiKeys = loadApiKeys();

// AI API çağrısı (fallback destekli)
try {
    $result = callAIWithFallback($images, $systemPrompt, $userPrompt, $apiKeys);

    // Başarılı analiz logu
    logAnalysis($uploadSession['id'], $result['provider'], count($images), 'success');

    // Geçici dosyaları temizle (KVKK uyumu)
    cleanupTempFiles($filePaths);
    unset($_SESSION['upload_session']);

    jsonResponse([
        'success'  => true,
        'analysis' => $result['text'],
        'metadata' => [
            'provider'    => $result['provider'],
            'key_index'   => $result['key_index'],
            'image_count' => count($images),
            'timestamp'   => date('Y-m-d H:i:s'),
            'fallback_attempts' => $result['attempts'],
        ],
    ]);
} catch (Exception $e) {
    logError('AI API hatası: ' . $e->getMessage());
    logAnalysis($uploadSession['id'], $apiKeys['active_provider'] ?? AI_PROVIDER, count($images), 'error', $e->getMessage());

    // Hata durumunda da dosyaları temizle
    cleanupTempFiles($filePaths);
    unset($_SESSION['upload_session']);

    jsonResponse([
        'success' => false,
        'error'   => 'Analiz sırasında bir hata oluştu: ' . $e->getMessage(),
    ], 500);
}

// ============================================================
// API ANAHTAR YÖNETİMİ
// ============================================================

/**
 * API anahtarlarını yükler (settings'den veya config'den).
 */
function loadApiKeys(): array
{
    $keysFile = __DIR__ . '/api_keys.json';
    $settings = [
        'keys' => [],
        'active_provider' => AI_PROVIDER,
        'fallback_enabled' => true,
    ];

    if (file_exists($keysFile)) {
        $data = json_decode(file_get_contents($keysFile), true);
        if ($data && !empty($data['keys'])) {
            return array_merge($settings, $data);
        }
    }

    // Fallback: config.php'den oku
    $configKeys = [];
    if (!empty(CLAUDE_API_KEY)) {
        $configKeys[] = ['provider' => 'claude', 'key' => CLAUDE_API_KEY, 'status' => 'untested'];
    }
    if (!empty(GEMINI_API_KEY)) {
        $configKeys[] = ['provider' => 'gemini', 'key' => GEMINI_API_KEY, 'status' => 'untested'];
    }
    if (!empty(OPENAI_API_KEY)) {
        $configKeys[] = ['provider' => 'openai', 'key' => OPENAI_API_KEY, 'status' => 'untested'];
    }

    $settings['keys'] = $configKeys;
    return $settings;
}

/**
 * Fallback sistemiyle AI API çağrısı yapar.
 * Önce tercih edilen sağlayıcının anahtarlarını dener,
 * sonra diğer sağlayıcılara geçer.
 */
function callAIWithFallback(array $images, string $systemPrompt, string $userPrompt, array $apiKeys): array
{
    $allKeys = $apiKeys['keys'] ?? [];
    $preferredProvider = $apiKeys['active_provider'] ?? AI_PROVIDER;
    $fallbackEnabled = $apiKeys['fallback_enabled'] ?? true;

    if (empty($allKeys)) {
        throw new Exception('Hiçbir API anahtarı yapılandırılmamış. Lütfen Ayarlar sayfasından en az bir anahtar ekleyin.');
    }

    // Anahtarları sağlayıcıya göre grupla
    $keysByProvider = ['claude' => [], 'gemini' => [], 'openai' => []];
    foreach ($allKeys as $index => $keyData) {
        $provider = $keyData['provider'] ?? 'claude';
        if (isset($keysByProvider[$provider])) {
            $keysByProvider[$provider][] = ['key' => $keyData['key'], 'index' => $index];
        }
    }

    // Deneme sırası: önce tercih edilen, sonra diğerleri
    $providerOrder = [$preferredProvider];
    if ($fallbackEnabled) {
        foreach (['claude', 'gemini', 'openai'] as $p) {
            if ($p !== $preferredProvider) {
                $providerOrder[] = $p;
            }
        }
    }

    $errors = [];
    $attempts = 0;

    foreach ($providerOrder as $provider) {
        $keys = $keysByProvider[$provider] ?? [];

        foreach ($keys as $keyEntry) {
            $attempts++;
            $apiKey = $keyEntry['key'];

            try {
                $text = match ($provider) {
                    'claude' => callClaudeWithKey($images, $systemPrompt, $userPrompt, $apiKey),
                    'gemini' => callGeminiWithKey($images, $systemPrompt, $userPrompt, $apiKey),
                    'openai' => callOpenAIWithKey($images, $systemPrompt, $userPrompt, $apiKey),
                    default => throw new Exception('Geçersiz sağlayıcı: ' . $provider),
                };

                // Başarılı - anahtarın durumunu güncelle
                updateKeyStatus($keyEntry['index'], 'valid');

                return [
                    'text' => $text,
                    'provider' => $provider,
                    'key_index' => $keyEntry['index'],
                    'attempts' => $attempts,
                ];
            } catch (Exception $e) {
                $maskedKey = substr($apiKey, 0, 10) . '...' . substr($apiKey, -4);
                $errorMsg = "[$provider] $maskedKey: " . $e->getMessage();
                $errors[] = $errorMsg;
                logError("Fallback deneme $attempts: $errorMsg");

                // Anahtarı geçersiz olarak işaretle
                updateKeyStatus($keyEntry['index'], 'invalid');
            }
        }

        // Fallback kapalıysa diğer sağlayıcıları deneme
        if (!$fallbackEnabled) {
            break;
        }
    }

    // Tüm denemeler başarısız
    $allErrors = implode(' | ', $errors);
    throw new Exception("Tüm API anahtarları başarısız oldu ($attempts deneme). Hatalar: $allErrors");
}

/**
 * Anahtar durumunu JSON dosyasında günceller.
 */
function updateKeyStatus(int $keyIndex, string $status): void
{
    $keysFile = __DIR__ . '/api_keys.json';
    if (!file_exists($keysFile)) {
        return;
    }

    $data = json_decode(file_get_contents($keysFile), true);
    if ($data && isset($data['keys'][$keyIndex])) {
        $data['keys'][$keyIndex]['status'] = $status;
        $data['keys'][$keyIndex]['last_tested'] = date('Y-m-d H:i:s');
        file_put_contents($keysFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// ============================================================
// YARDIMCI FONKSİYONLAR
// ============================================================

/**
 * Sistem promptunu döndürür.
 */
function getAnalysisSystemPrompt(): string
{
    return <<<'PROMPT'
Sen bir trafik kazası analiz uzmanısın. Türkiye Karayolları Trafik Kanunu ve ilgili mevzuat hakkında derin bilgiye sahipsin.

Görevin, sana verilen kaza tespit tutanağı fotoğrafları, kaza yeri fotoğrafları, araç hasar fotoğrafları ve taraf beyanlarını analiz ederek aşağıdaki formatta bir rapor hazırlamaktır.

Analiz yaparken şu kriterleri göz önünde bulundur:
- 2918 sayılı Karayolları Trafik Kanunu
- Trafik kazası kusur oranı kriterleri
- Tutanak krokisindeki araç pozisyonları ve yol işaretleri
- Araçlardaki hasar noktaları ve çarpışma açıları
- Taraf beyanlarındaki tutarlılık ve çelişkiler

Raporunu mutlaka aşağıdaki bölümlerden oluşacak şekilde hazırla:

1. TARAF BEYANLARI
Tutanaktan veya kullanıcıdan alınan taraf beyanlarını özetle.

2. OLAYININ MUHTEMEL OLUŞ ŞEKLİ
Tüm verileri değerlendirerek kazanın nasıl meydana geldiğini açıkla.

3. İHLAL EDİLEN TRAFİK KURALLARI
Her taraf için ihlal edilen trafik kurallarını ve ilgili kanun maddelerini belirt.

4. ARAÇLARIN KUSUR DEĞERLENDİRMESİ
Her bir tarafın kusur durumunu gerekçeleriyle açıkla.

5. TAHMİNİ KUSUR ORANLARI
Her taraf için yüzde olarak tahmini kusur oranını ver.

Önemli: Bu analiz bir ön değerlendirmedir ve hukuki bağlayıcılığı yoktur. Kesin kusur tespiti yetkili merciler tarafından yapılır.
PROMPT;
}

/**
 * Kullanıcı promptunu oluşturur.
 */
function getAnalysisUserPrompt(string $tarafA, string $tarafB): string
{
    $prompt = "Lütfen yüklenen kaza tespit tutanağı ve kaza fotoğraflarını analiz et.\n\n";

    if (!empty($tarafA) || !empty($tarafB)) {
        $prompt .= "TARAF BEYANLARI:\n";
        if (!empty($tarafA)) {
            $prompt .= "Taraf A (1. Sürücü): $tarafA\n";
        }
        if (!empty($tarafB)) {
            $prompt .= "Taraf B (2. Sürücü): $tarafB\n";
        }
        $prompt .= "\n";
    }

    $prompt .= "Yukarıdaki verileri ve yüklenen fotoğrafları kullanarak detaylı bir kaza analiz raporu hazırla.";

    return $prompt;
}

// ============================================================
// API ÇAĞRI FONKSİYONLARI (anahtar parametreli)
// ============================================================

/**
 * Claude API çağrısı (belirli anahtar ile).
 */
function callClaudeWithKey(array $images, string $systemPrompt, string $userPrompt, string $apiKey): string
{
    $content = [];
    foreach ($images as $img) {
        $content[] = [
            'type'   => 'image',
            'source' => [
                'type'       => 'base64',
                'media_type' => $img['mime'],
                'data'       => $img['base64'],
            ],
        ];
    }
    $content[] = ['type' => 'text', 'text' => $userPrompt];

    $payload = [
        'model'      => CLAUDE_MODEL,
        'max_tokens' => 4096,
        'system'     => $systemPrompt,
        'messages'   => [['role' => 'user', 'content' => $content]],
    ];

    $response = makeCurlRequest(CLAUDE_API_URL, $payload, [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
    ]);

    if (isset($response['content'][0]['text'])) {
        return $response['content'][0]['text'];
    }

    throw new Exception('Claude API yanıtı beklenmeyen formatta.');
}

/**
 * Gemini API çağrısı (belirli anahtar ile).
 */
function callGeminiWithKey(array $images, string $systemPrompt, string $userPrompt, string $apiKey): string
{
    $parts = [];
    foreach ($images as $img) {
        $parts[] = [
            'inline_data' => [
                'mime_type' => $img['mime'],
                'data'      => $img['base64'],
            ],
        ];
    }
    $parts[] = ['text' => $userPrompt];

    $payload = [
        'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
        'contents' => [['parts' => $parts]],
        'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 4096],
    ];

    $url = GEMINI_API_URL . GEMINI_MODEL . ':generateContent?key=' . $apiKey;

    $response = makeCurlRequest($url, $payload, ['Content-Type: application/json']);

    if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        return $response['candidates'][0]['content']['parts'][0]['text'];
    }

    throw new Exception('Gemini API yanıtı beklenmeyen formatta.');
}

/**
 * OpenAI API çağrısı (belirli anahtar ile).
 */
function callOpenAIWithKey(array $images, string $systemPrompt, string $userPrompt, string $apiKey): string
{
    $content = [];
    foreach ($images as $img) {
        $content[] = [
            'type'      => 'image_url',
            'image_url' => [
                'url'    => 'data:' . $img['mime'] . ';base64,' . $img['base64'],
                'detail' => 'high',
            ],
        ];
    }
    $content[] = ['type' => 'text', 'text' => $userPrompt];

    $payload = [
        'model'       => OPENAI_MODEL,
        'messages'     => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $content],
        ],
        'max_tokens'  => 4096,
        'temperature' => 0.3,
    ];

    $response = makeCurlRequest(OPENAI_API_URL, $payload, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);

    if (isset($response['choices'][0]['message']['content'])) {
        return $response['choices'][0]['message']['content'];
    }

    throw new Exception('OpenAI API yanıtı beklenmeyen formatta.');
}

/**
 * cURL ile HTTP POST isteği yapar.
 */
function makeCurlRequest(string $url, array $payload, array $headers): array
{
    $ch = curl_init();

    $jsonPayload = json_encode($payload);
    if ($jsonPayload === false) {
        throw new Exception('JSON encode hatası: ' . json_last_error_msg());
    }

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $jsonPayload,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    // Shared hosting'de CA bundle bulunamazsa fallback
    $caPath = '/etc/ssl/certs/ca-certificates.crt';
    if (file_exists($caPath)) {
        curl_setopt($ch, CURLOPT_CAINFO, $caPath);
    }

    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("cURL hatası: $error");
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new Exception("API HTTP hatası ($httpCode): $responseBody");
    }

    $decoded = json_decode($responseBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('API yanıtı JSON olarak ayrıştırılamadı: ' . json_last_error_msg());
    }

    return $decoded;
}
