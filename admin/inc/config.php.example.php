<?php
// Bu dosyayı `admin/inc/config.local.php` adıyla kopyalayarak özelleştirin.
// Bu dosyayı güvenli bir konuma taşıyın ve `config.php` olarak yeniden adlandırın.
// Gerçek üretim bilgilerinizi burada saklamayın; örnek değerleri değiştirin.

define('BASE_URL', 'https://license.example.com');
define('SECRET_KEY', 'değiştirmeniz_gereken_uzun_ve_rastgele_bir_anahtar');

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'album_license_db');
define('DB_USER', 'album_license_user');
define('DB_PASS', 'güçlü_bir_parola');
