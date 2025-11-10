<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
check_auth();
require_once __DIR__ . '/templates/header.php';

$res = $mysqli->query('SELECT COUNT(*) AS total FROM license_keys');
$total = 0;
if ($res) {
    $row = $res->fetch_assoc();
    $total = (int) ($row['total'] ?? 0);
}
?>
<div class="container mt-5">
    <h2>📊 Lisans Sistemi Kontrol Paneli</h2>
    <p>Toplam Lisans: <b><?= $total ?></b></p>
    <div class="mt-4">
        <a href="licenses.php" class="btn btn-primary">📋 Tüm Lisanslar</a>
        <a href="generate.php" class="btn btn-success">➕ Yeni Lisans</a>
    </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
