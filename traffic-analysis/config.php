<?php
/**
 * Trafik Kazası Tutanak Analiz Sistemi - Yapılandırma Dosyası
 *
 * Bu dosya API anahtarları ve sistem ayarlarını içerir.
 * Bu dosya asla public erişime açık olmamalıdır.
 */

// Hata raporlama (production'da kapatın)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Gizli bilgileri .env.php'den yükle
$envFile = __DIR__ . '/.env.php';
if (file_exists($envFile)) {
    require_once $envFile;
}

// ============================================================
// API YAPILANDIRMASI
// Kullanmak istediğiniz AI servisinin bilgilerini doldurun.
// Yalnızca BİR servis aktif olmalıdır.
// ============================================================

define('AI_PROVIDER', 'claude'); // 'gemini', 'openai' veya 'claude'

// Google Gemini
define('GEMINI_API_KEY', '');
define('GEMINI_MODEL', 'gemini-2.0-flash');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/');

// OpenAI
define('OPENAI_API_KEY', '');
define('OPENAI_MODEL', 'gpt-4o');
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');

// Claude (Anthropic)
define('CLAUDE_API_KEY', defined('ENV_CLAUDE_API_KEY') ? ENV_CLAUDE_API_KEY : '');
define('CLAUDE_MODEL', 'claude-sonnet-4-20250514');
define('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages');

// ============================================================
// VERİTABANI YAPILANDIRMASI
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'mrhasard_finansal');
define('DB_USER', 'mrhasard_finansal');
define('DB_PASS', defined('ENV_DB_PASS') ? ENV_DB_PASS : '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// DOSYA YÜKLEME AYARLARI
// ============================================================

define('UPLOAD_DIR', __DIR__ . '/uploads/temp/');
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // 20 MB
define('MAX_FILES', 10);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// ============================================================
// GÖRSEL OPTİMİZASYON
// ============================================================

define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_MAX_HEIGHT', 1920);
define('IMAGE_QUALITY', 80);

// ============================================================
// GÜVENLİK AYARLARI
// ============================================================

define('RATE_LIMIT_PER_HOUR', 10);
define('RATE_LIMIT_PER_DAY', 30);
define('SESSION_TIMEOUT', 3600); // 1 saat

// ============================================================
// SİSTEM AYARLARI
// ============================================================

define('APP_NAME', 'Trafik Kazası Tutanak Analiz Sistemi');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Europe/Istanbul');

date_default_timezone_set(TIMEZONE);

// Oturum güvenliği
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// CSRF Token oluştur
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
