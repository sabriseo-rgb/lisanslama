<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
check_auth();
require_once __DIR__ . '/templates/header.php';

$res = $mysqli->query("SELECT * FROM license_keys ORDER BY created_at DESC");
?>
<div class="container mt-4">
    <h3>📋 Kayıtlı Lisanslar</h3>
    <table class="table table-striped table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Lisans Kodu</th>
                <th>Sahip</th>
                <th>Tür</th>
                <th>Durum</th>
                <th>Oluşturulma</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><code><?= htmlspecialchars($row['license_key']) ?></code></td>
                <td><?= htmlspecialchars($row['owner']) ?></td>
                <td><?= $row['type'] ?></td>
                <td><?= $row['active'] ? '✅ Aktif' : '❌ Pasif' ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="revoke.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">İptal Et</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
