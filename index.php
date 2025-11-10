<?php
/**
 * Foto Albüm Lisans Sistemi - Ana Sayfa
 * Dinamik olarak admin/inc/config.php dosyasını bulur.
 */

$baseDir = __DIR__;
$paths = [
    $baseDir . '/admin/inc/config.php',
    $baseDir . '/../admin/inc/config.php',
];

$configFound = false;
foreach ($paths as $p) {
    if (file_exists($p)) {
        require_once $p;
        $configFound = true;
        break;
    }
}

if (!$configFound) {
    echo "<!DOCTYPE html><html lang='tr'><head><meta charset='UTF-8'><title>Hata</title></head>" .
         "<body style='background:#0f172a;color:#f8fafc;font-family:sans-serif;text-align:center;margin-top:10%;'>" .
         "<h2>⚠️ Yapılandırma bulunamadı</h2>" .
         "<p><code>admin/inc/config.php</code> dosyası eksik veya yanlış dizinde.</p>" .
         "</body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foto Albüm Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background-color: #1e293b;
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .btn-custom {
            background-color: #2563eb;
            color: white;
            transition: 0.2s;
        }
        .btn-custom:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
<div class="container text-center">
    <div class="card p-5 rounded-4">
        <h1 class="mb-3">📘 Foto Albüm Lisans Sistemi</h1>
        <p class="text-light mb-4">
            Bu sistem <b>Foto Albüm Uygulaması</b> için lisans yönetimi sağlar.<br>
            Python tabanlı istemciler <code>license_check.php</code> üzerinden lisans doğrulaması yapar.
        </p>
        <a href="admin/login.php" class="btn btn-custom px-4 py-2">
            🔐 Yönetici Girişi
        </a>
    </div>
</div>
</body>
</html>
