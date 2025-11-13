<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/inc/utils.php';
check_auth();
require_once __DIR__ . '/templates/header.php';

$search = trim($_GET['q'] ?? '');
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$conditions = [];
$params = [];
$types = '';

if ($search !== '') {
    $conditions[] = "(l.license_key LIKE CONCAT('%', ?, '%') OR l.owner LIKE CONCAT('%', ?, '%'))";
    $params[] = $search;
    $params[] = $search;
    $types .= 'ss';
}

if (in_array($typeFilter, ['FULL', 'LIMITED'], true)) {
    $conditions[] = 'l.type = ?';
    $params[] = $typeFilter;
    $types .= 's';
} else {
    $typeFilter = '';
}

if ($statusFilter === 'active') {
    $conditions[] = 'l.active = 1';
} elseif ($statusFilter === 'passive') {
    $conditions[] = 'l.active = 0';
} else {
    $statusFilter = '';
}

$sql = "SELECT l.*, COUNT(a.id) AS activation_count
    FROM license_keys l
    LEFT JOIN license_activations a ON a.license_id = l.id";

if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' GROUP BY l.id ORDER BY l.created_at DESC';

$stmt = $mysqli->prepare($sql);
$licenses = [];

if ($stmt === false) {
    error_log('License listing prepare failed: ' . $mysqli->error);
} else {
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $licenses[] = $row;
            }
        }
    }
}

$csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
$redirectUrl = $_SERVER['REQUEST_URI'] ?? '/admin/licenses.php';
$flashes = get_all_flashes();
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="mb-0">📋 Kayıtlı Lisanslar</h3>
        <span class="badge bg-secondary fs-6">Toplam <?= count($licenses) ?> sonuç</span>
    </div>

    <?php foreach ($flashes as $flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?> mt-3" role="alert">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endforeach; ?>

    <form method="GET" class="card card-body shadow-sm mt-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label">Ara</label>
                <input type="text" id="q" name="q" class="form-control" placeholder="Lisans kodu veya sahip"
                       value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Tür</label>
                <select id="type" name="type" class="form-select">
                    <option value="" <?= $typeFilter === '' ? 'selected' : '' ?>>Tümü</option>
                    <option value="FULL" <?= $typeFilter === 'FULL' ? 'selected' : '' ?>>FULL</option>
                    <option value="LIMITED" <?= $typeFilter === 'LIMITED' ? 'selected' : '' ?>>LIMITED</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Durum</label>
                <select id="status" name="status" class="form-select">
                    <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>Tümü</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="passive" <?= $statusFilter === 'passive' ? 'selected' : '' ?>>Pasif</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filtrele</button>
            </div>
        </div>
    </form>

    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Lisans Kodu</th>
                    <th>Sahip</th>
                    <th>Tür</th>
                    <th>Aktivasyon</th>
                    <th>Durum</th>
                    <th>Oluşturulma</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($licenses): ?>
                <?php foreach ($licenses as $row): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><code><?= htmlspecialchars($row['license_key'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td><?= htmlspecialchars($row['owner'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $row['activation_count'] ?> / <?= (int) $row['max_devices'] ?></td>
                        <td>
                            <?php if ((int) $row['active'] === 1): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-nowrap">
                            <a href="license_edit.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-secondary">Detay</a>
                            <form method="POST" action="status.php" class="d-inline">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ((int) $row['active'] === 1): ?>
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Bu lisansı pasif hale getirmek istediğinize emin misiniz?');">Pasifleştir</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="btn btn-sm btn-success">Aktifleştir</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Filtrelere uygun lisans bulunamadı.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
