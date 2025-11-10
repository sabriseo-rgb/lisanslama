<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/csrf.php';
check_auth();
require_once __DIR__ . '/templates/header.php';

$res = $mysqli->query('SELECT * FROM license_keys ORDER BY created_at DESC');
$csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
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
        <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= (int) $row['id'] ?></td>
                <td><code><?= htmlspecialchars($row['license_key'], ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><?= htmlspecialchars($row['owner'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $row['active'] ? '✅ Aktif' : '❌ Pasif' ?></td>
                <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <form method="POST" action="revoke.php" class="d-inline" onsubmit="return confirm('Bu lisansı pasif hale getirmek istediğinize emin misiniz?');">
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-sm btn-danger">İptal Et</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
