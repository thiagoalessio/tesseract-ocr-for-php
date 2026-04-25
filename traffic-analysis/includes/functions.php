<?php
/**
 * Trafik Kazası Tutanak Analiz Sistemi - Yardımcı Fonksiyonlar
 */

/**
 * Veritabanı bağlantısı oluşturur (Singleton pattern).
 */
function getDB(): ?PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            logError('Veritabanı bağlantı hatası: ' . $e->getMessage());
            return null;
        }
    }
    return $pdo;
}

/**
 * Hata loglar.
 */
function logError(string $message): void
{
    $logFile = __DIR__ . '/../logs/error.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    file_put_contents($logFile, "[$timestamp] [$ip] $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Analiz logunu veritabanına kaydeder.
 */
function logAnalysis(string $sessionId, string $provider, int $imageCount, string $status, ?string $errorMsg = null): void
{
    $db = getDB();
    if (!$db) {
        return;
    }
    try {
        $stmt = $db->prepare(
            'INSERT INTO analysis_logs (session_id, ip_address, ai_provider, image_count, status, error_message, created_at)
             VALUES (:sid, :ip, :provider, :count, :status, :error, NOW())'
        );
        $stmt->execute([
            ':sid'      => $sessionId,
            ':ip'       => $_SERVER['REMOTE_ADDR'] ?? '',
            ':provider' => $provider,
            ':count'    => $imageCount,
            ':status'   => $status,
            ':error'    => $errorMsg,
        ]);
    } catch (PDOException $e) {
        logError('Log kayıt hatası: ' . $e->getMessage());
    }
}

/**
 * CSRF token doğrular.
 */
function validateCSRF(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting kontrolü yapar.
 */
function checkRateLimit(): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $db = getDB();
    if (!$db) {
        // Veritabanı yoksa basit session tabanlı kontrol
        return checkRateLimitSession();
    }

    try {
        // Saatlik kontrol
        $stmt = $db->prepare(
            'SELECT COUNT(*) as cnt FROM analysis_logs
             WHERE ip_address = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        $stmt->execute([':ip' => $ip]);
        $hourly = $stmt->fetch()['cnt'];

        if ($hourly >= RATE_LIMIT_PER_HOUR) {
            return false;
        }

        // Günlük kontrol
        $stmt = $db->prepare(
            'SELECT COUNT(*) as cnt FROM analysis_logs
             WHERE ip_address = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)'
        );
        $stmt->execute([':ip' => $ip]);
        $daily = $stmt->fetch()['cnt'];

        return $daily < RATE_LIMIT_PER_DAY;
    } catch (PDOException $e) {
        logError('Rate limit kontrol hatası: ' . $e->getMessage());
        return checkRateLimitSession();
    }
}

/**
 * Session tabanlı basit rate limiting (veritabanı yoksa).
 */
function checkRateLimitSession(): bool
{
    $now = time();
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }

    // Eski kayıtları temizle (1 saatten eski)
    $_SESSION['rate_limit'] = array_filter(
        $_SESSION['rate_limit'],
        fn($ts) => ($now - $ts) < 3600
    );

    if (count($_SESSION['rate_limit']) >= RATE_LIMIT_PER_HOUR) {
        return false;
    }

    $_SESSION['rate_limit'][] = $now;
    return true;
}

/**
 * Yüklenen dosyayı doğrular.
 */
function validateUploadedFile(array $file): array
{
    // Hata kontrolü
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'Dosya boyutu sunucu limitini aşıyor.',
            UPLOAD_ERR_FORM_SIZE  => 'Dosya boyutu form limitini aşıyor.',
            UPLOAD_ERR_PARTIAL    => 'Dosya kısmen yüklendi.',
            UPLOAD_ERR_NO_FILE    => 'Dosya yüklenmedi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı.',
            UPLOAD_ERR_CANT_WRITE => 'Dosya yazılamadı.',
            UPLOAD_ERR_EXTENSION  => 'Dosya uzantısı engellendi.',
        ];
        return ['valid' => false, 'error' => $messages[$file['error']] ?? 'Bilinmeyen yükleme hatası.'];
    }

    // Boyut kontrolü
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'Dosya boyutu 20MB limitini aşıyor.'];
    }

    if ($file['size'] === 0) {
        return ['valid' => false, 'error' => 'Dosya boş.'];
    }

    // Uzantı kontrolü
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        return ['valid' => false, 'error' => 'Desteklenmeyen dosya uzantısı: ' . $ext];
    }

    // MIME tipi kontrolü (fileinfo)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_TYPES, true)) {
        return ['valid' => false, 'error' => 'Desteklenmeyen dosya türü: ' . $mimeType];
    }

    // Gerçek görsel kontrolü (zararlı içerik koruması)
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['valid' => false, 'error' => 'Dosya geçerli bir görsel değil.'];
    }

    return ['valid' => true, 'mime' => $mimeType, 'extension' => $ext];
}

/**
 * Görseli optimize eder: boyut küçültme, kalite ayarı, EXIF temizleme.
 */
function optimizeImage(string $sourcePath, string $mimeType): ?string
{
    $image = null;

    switch ($mimeType) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($sourcePath);
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($sourcePath);
            break;
    }

    if (!$image) {
        return null;
    }

    $origW = imagesx($image);
    $origH = imagesy($image);

    // Boyut küçültme gerekiyorsa
    $newW = $origW;
    $newH = $origH;

    if ($origW > IMAGE_MAX_WIDTH || $origH > IMAGE_MAX_HEIGHT) {
        $ratio = min(IMAGE_MAX_WIDTH / $origW, IMAGE_MAX_HEIGHT / $origH);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);

        // PNG/WebP şeffaflık desteği
        if ($mimeType !== 'image/jpeg') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($image);
        $image = $resized;
    }

    // Optimize edilmiş dosyayı kaydet (EXIF verisi otomatik olarak temizlenir)
    $optimizedPath = $sourcePath; // Aynı yere kaydet
    $saved = false;

    switch ($mimeType) {
        case 'image/jpeg':
            $saved = imagejpeg($image, $optimizedPath, IMAGE_QUALITY);
            break;
        case 'image/png':
            $saved = imagepng($image, $optimizedPath, 8);
            break;
        case 'image/webp':
            $saved = imagewebp($image, $optimizedPath, IMAGE_QUALITY);
            break;
    }

    imagedestroy($image);

    return $saved ? $optimizedPath : null;
}

/**
 * Görseli base64 formatına dönüştürür (API'ye göndermek için).
 */
function imageToBase64(string $filePath, string $mimeType): string
{
    $data = file_get_contents($filePath);
    return base64_encode($data);
}

/**
 * Geçici dosyaları temizler.
 */
function cleanupTempFiles(array $filePaths): void
{
    foreach ($filePaths as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

/**
 * Benzersiz dosya adı oluşturur.
 */
function generateSafeFilename(string $extension): string
{
    return bin2hex(random_bytes(16)) . '.' . $extension;
}

/**
 * JSON yanıt döndürür.
 */
function jsonResponse(array $data, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Uploads klasörünün güvenliğini sağlar.
 */
function ensureUploadDir(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0750, true);
    }

    $htaccess = UPLOAD_DIR . '.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Deny from all\n");
    }

    $indexFile = UPLOAD_DIR . 'index.php';
    if (!file_exists($indexFile)) {
        file_put_contents($indexFile, "<?php\n// Silence is golden.\n");
    }
}
