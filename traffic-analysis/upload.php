<?php
/**
 * Trafik Kazası Tutanak Analiz Sistemi - Dosya Yükleme İşleyici
 *
 * Bu dosya AJAX ile gönderilen fotoğrafları alır, doğrular,
 * optimize eder ve geçici klasöre kaydeder.
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

// Rate limit kontrolü
if (!checkRateLimit()) {
    jsonResponse(['success' => false, 'error' => 'İstek limiti aşıldı. Lütfen daha sonra tekrar deneyin.'], 429);
}

// Upload klasörünü hazırla
ensureUploadDir();

// Dosya kontrolü
if (empty($_FILES['images'])) {
    jsonResponse(['success' => false, 'error' => 'Lütfen en az bir fotoğraf yükleyin.'], 400);
}

$files = $_FILES['images'];
$uploadedFiles = [];
$errors = [];

// Dosya sayısı kontrolü
$fileCount = is_array($files['name']) ? count($files['name']) : 1;
if ($fileCount > MAX_FILES) {
    jsonResponse(['success' => false, 'error' => "En fazla " . MAX_FILES . " dosya yükleyebilirsiniz."], 400);
}

// Çoklu dosya yüklemelerini normalize et
$normalizedFiles = [];
if (is_array($files['name'])) {
    for ($i = 0; $i < $fileCount; $i++) {
        $normalizedFiles[] = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
    }
} else {
    $normalizedFiles[] = $files;
}

// Her dosyayı işle
foreach ($normalizedFiles as $index => $file) {
    $fileNum = $index + 1;

    // Doğrulama
    $validation = validateUploadedFile($file);
    if (!$validation['valid']) {
        $errors[] = "Dosya $fileNum: " . $validation['error'];
        continue;
    }

    // Güvenli dosya adı oluştur
    $safeFilename = generateSafeFilename($validation['extension']);
    $destPath = UPLOAD_DIR . $safeFilename;

    // Dosyayı taşı
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = "Dosya $fileNum: Sunucuya kaydedilemedi.";
        continue;
    }

    // Görseli optimize et
    $optimized = optimizeImage($destPath, $validation['mime']);
    if ($optimized === null) {
        $errors[] = "Dosya $fileNum: Görsel optimizasyonu başarısız.";
        if (file_exists($destPath)) {
            unlink($destPath);
        }
        continue;
    }

    $uploadedFiles[] = [
        'filename' => $safeFilename,
        'path'     => $destPath,
        'mime'     => $validation['mime'],
        'original' => basename($file['name']),
    ];
}

// Hiç dosya yüklenemedi mi?
if (empty($uploadedFiles)) {
    jsonResponse([
        'success' => false,
        'error'   => 'Hiçbir dosya yüklenemedi.',
        'details' => $errors,
    ], 400);
}

// Session'a kaydet (api_handler kullanacak)
$sessionId = bin2hex(random_bytes(16));
$_SESSION['upload_session'] = [
    'id'    => $sessionId,
    'files' => $uploadedFiles,
    'time'  => time(),
];

// Başarılı yanıt
jsonResponse([
    'success'    => true,
    'session_id' => $sessionId,
    'file_count' => count($uploadedFiles),
    'errors'     => $errors,
    'message'    => count($uploadedFiles) . ' dosya başarıyla yüklendi.',
]);
