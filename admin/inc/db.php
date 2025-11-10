<?php
// Veritabanı sabitleri
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'dugunalbumcm_album_license_db');
define('DB_USER', 'dugunalbumcm_sbrihtci');
define('DB_PASS', '1098564Sbr!');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($mysqli->connect_errno) {
    error_log("MySQL bağlantı hatası: " . $mysqli->connect_error);
    die("❌ Veritabanı bağlantısı başarısız! Lütfen config.php ayarlarını kontrol edin.");
}

$mysqli->set_charset('utf8mb4');
?>
