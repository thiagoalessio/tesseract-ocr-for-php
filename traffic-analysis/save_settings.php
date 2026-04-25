<?php
/**
 * Trafik Kazası Tutanak Analiz Sistemi - Ayarları Kaydet / Test Et
 *
 * API anahtarlarını kaydeder ve test eder.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

// Sadece POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Geçersiz istek.'], 405);
}

// CSRF kontrolü
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validateCSRF($csrfToken)) {
    jsonResponse(['success' => false, 'error' => 'Güvenlik doğrulaması başarısız.'], 403);
}

$action = $_POST['action'] ?? 'save';
$keysFile = __DIR__ . '/api_keys.json';

// Anahtarları topla
$allKeys = [];
$providers = ['claude', 'gemini', 'openai'];

foreach ($providers as $provider) {
    $keys = $_POST['keys'][$provider] ?? [];
    if (!is_array($keys)) {
        $keys = [$keys];
    }
    foreach ($keys as $key) {
        $key = trim($key);
        if (!empty($key)) {
            $allKeys[] = [
                'provider' => $provider,
                'key' => $key,
                'status' => 'untested',
                'added_at' => date('Y-m-d H:i:s'),
            ];
        }
    }
}

// Test işlemi
if ($action === 'test_all') {
    $testResults = [];

    foreach ($allKeys as &$keyData) {
        $result = testApiKey($keyData['provider'], $keyData['key']);
        $keyData['status'] = $result['valid'] ? 'valid' : 'invalid';
        $testResults[] = [
            'provider' => $keyData['provider'],
            'key' => $keyData['key'],
            'valid' => $result['valid'],
            'error' => $result['error'] ?? null,
        ];
    }
    unset($keyData);

    // Kaydet (test sonuçlarıyla birlikte)
    $settings = [
        'keys' => $allKeys,
        'active_provider' => $_POST['active_provider'] ?? 'claude',
        'fallback_enabled' => isset($_POST['fallback_enabled']),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    file_put_contents($keysFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    jsonResponse([
        'success' => true,
        'test_results' => $testResults,
    ]);
}

// Sadece kaydet
$settings = [
    'keys' => $allKeys,
    'active_provider' => $_POST['active_provider'] ?? 'claude',
    'fallback_enabled' => isset($_POST['fallback_enabled']),
    'updated_at' => date('Y-m-d H:i:s'),
];

$saved = file_put_contents($keysFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($saved === false) {
    jsonResponse(['success' => false, 'error' => 'Dosya yazılamadı. Dosya izinlerini kontrol edin.'], 500);
}

jsonResponse([
    'success' => true,
    'message' => 'Ayarlar kaydedildi.',
    'key_count' => count($allKeys),
]);

// ============================================================
// API ANAHTAR TEST FONKSİYONLARI
// ============================================================

/**
 * Bir API anahtarını minimal bir istekle test eder.
 */
function testApiKey(string $provider, string $apiKey): array
{
    return match ($provider) {
        'claude' => testClaudeKey($apiKey),
        'gemini' => testGeminiKey($apiKey),
        'openai' => testOpenAIKey($apiKey),
        default => ['valid' => false, 'error' => 'Bilinmeyen sağlayıcı'],
    };
}

/**
 * Claude API anahtarını test eder.
 */
function testClaudeKey(string $apiKey): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 10,
            'messages' => [['role' => 'user', 'content' => 'Hi']],
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $caPath = '/etc/ssl/certs/ca-certificates.crt';
    if (file_exists($caPath)) {
        curl_setopt($ch, CURLOPT_CAINFO, $caPath);
    }

    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['valid' => false, 'error' => 'Bağlantı hatası: ' . $error];
    }

    if ($httpCode === 401) {
        return ['valid' => false, 'error' => 'Geçersiz API anahtarı (401)'];
    }

    if ($httpCode === 403) {
        return ['valid' => false, 'error' => 'Erişim engellendi (403)'];
    }

    // 200 veya 429 (rate limit) = anahtar geçerli
    if ($httpCode === 200 || $httpCode === 429) {
        return ['valid' => true];
    }

    // Diğer başarılı kodlar
    if ($httpCode >= 200 && $httpCode < 300) {
        return ['valid' => true];
    }

    $decoded = json_decode($body, true);
    $msg = $decoded['error']['message'] ?? "HTTP $httpCode";
    return ['valid' => false, 'error' => $msg];
}

/**
 * Gemini API anahtarını test eder.
 */
function testGeminiKey(string $apiKey): array
{
    $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $caPath = '/etc/ssl/certs/ca-certificates.crt';
    if (file_exists($caPath)) {
        curl_setopt($ch, CURLOPT_CAINFO, $caPath);
    }

    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['valid' => false, 'error' => 'Bağlantı hatası: ' . $error];
    }

    if ($httpCode === 200) {
        return ['valid' => true];
    }

    if ($httpCode === 400 || $httpCode === 403) {
        return ['valid' => false, 'error' => 'Geçersiz API anahtarı'];
    }

    return ['valid' => false, 'error' => "HTTP $httpCode"];
}

/**
 * OpenAI API anahtarını test eder.
 */
function testOpenAIKey(string $apiKey): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.openai.com/v1/models',
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $caPath = '/etc/ssl/certs/ca-certificates.crt';
    if (file_exists($caPath)) {
        curl_setopt($ch, CURLOPT_CAINFO, $caPath);
    }

    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['valid' => false, 'error' => 'Bağlantı hatası: ' . $error];
    }

    if ($httpCode === 200) {
        return ['valid' => true];
    }

    if ($httpCode === 401) {
        return ['valid' => false, 'error' => 'Geçersiz API anahtarı (401)'];
    }

    return ['valid' => false, 'error' => "HTTP $httpCode"];
}
