<?php
if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME') || !defined('DB_PORT')) {
    throw new RuntimeException('Veritabanı yapılandırması eksik. Lütfen config.php dosyanızı kontrol edin.');
}

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($mysqli->connect_errno) {
    error_log('MySQL bağlantı hatası: ' . $mysqli->connect_error);
    http_response_code(500);
    die('❌ Veritabanı bağlantısı başarısız! Lütfen config.php ayarlarını kontrol edin.');
}

$mysqli->set_charset('utf8mb4');
