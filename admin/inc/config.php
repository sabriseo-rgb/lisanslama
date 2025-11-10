<?php
/**
 * Uygulama yapılandırması.
 *
 * Bu dosya öncelikle yerel bir yapılandırma dosyası veya ortam değişkenlerini
 * okuyarak gerekli sabitleri tanımlar. Üretim ortamında `config.local.php`
 * dosyası oluşturarak gerçek bilgilerinizle doldurabilirsiniz; bu dosya
 * kaynağa dahil edilmez.
 */

if (defined('BASE_URL')) {
    // Yapılandırma sabitleri zaten tanımlanmış; muhtemelen başka bir scriptte yüklendi.
    return;
}

$overrideCandidates = [];

if (($customPath = getenv('APP_CONFIG_PATH')) !== false && $customPath !== '') {
    $overrideCandidates[] = $customPath;
}

$overrideCandidates[] = __DIR__ . '/config.local.php';
$overrideCandidates[] = dirname(__DIR__, 2) . '/config.php';

foreach ($overrideCandidates as $candidate) {
    if ($candidate && is_file($candidate)) {
        require_once $candidate;
        // `config.local.php` içinde sabitler tanımlandığını varsayıyoruz.
        if (defined('BASE_URL')) {
            return;
        }
    }
}

$env = static function (string $key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
};

$defaultBase = $env('BASE_URL');
if (!$defaultBase) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $defaultBase = $scheme . $host;
}

define('BASE_URL', rtrim($defaultBase, '/'));
define('SECRET_KEY', $env('SECRET_KEY', 'change_me_secret_key'));

define('DB_HOST', $env('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) $env('DB_PORT', 3306));
define('DB_NAME', $env('DB_NAME', 'album_license_db'));
define('DB_USER', $env('DB_USER', 'album_license_user'));
define('DB_PASS', $env('DB_PASS', 'album_license_pass'));
