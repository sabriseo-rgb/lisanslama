<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/inc/utils.php';
check_auth();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash('status', 'Geçersiz lisans talebi.', 'danger');
    header('Location: licenses.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!check_csrf($token)) {
        set_flash('license', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.', 'danger');
    } else {
        $owner = trim($_POST['owner'] ?? '');
        $type = $_POST['type'] ?? 'LIMITED';
        $maxDevices = max(1, (int) ($_POST['max_devices'] ?? 1));
        $active = isset($_POST['active']) ? 1 : 0;

        if ($owner === '') {
            set_flash('license', 'Sahip adı boş bırakılamaz.', 'danger');
        } elseif (!in_array($type, ['FULL', 'LIMITED'], true)) {
            set_flash('license', 'Geçersiz lisans türü seçildi.', 'danger');
        } else {
            $stmt = $mysqli->prepare('UPDATE license_keys SET owner = ?, type = ?, max_devices = ?, active = ? WHERE id = ?');
            if ($stmt === false) {
                error_log('License update prepare failed: ' . $mysqli->error);
                set_flash('license', 'Lisans güncellenirken hata oluştu.', 'danger');
            } else {
                $stmt->bind_param('ssiii', $owner, $type, $maxDevices, $active, $id);
                if ($stmt->execute()) {
                    set_flash('license', 'Lisans bilgileri başarıyla güncellendi.', 'success');
                } else {
                    error_log('License update failed: ' . $stmt->error);
                    set_flash('license', 'Lisans güncellenirken hata oluştu.', 'danger');
                }
            }
        }
    }

    header('Location: license_edit.php?id=' . $id);
    exit;
}

$stmt = $mysqli->prepare('SELECT l.*, COUNT(a.id) AS activation_count FROM license_keys l LEFT JOIN license_activations a ON a.license_id = l.id WHERE l.id = ? GROUP BY l.id LIMIT 1');
if ($stmt === false) {
    set_flash('status', 'Lisans bilgileri okunamadı.', 'danger');
    header('Location: licenses.php');
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$license = $result ? $result->fetch_assoc() : null;

if (!$license) {
    set_flash('status', 'Lisans kaydı bulunamadı.', 'danger');
    header('Location: licenses.php');
    exit;
}

$activationStmt = $mysqli->prepare('SELECT id, machine_id, ip, activated_at FROM license_activations WHERE license_id = ? ORDER BY activated_at DESC');
$activations = [];
if ($activationStmt) {
    $activationStmt->bind_param('i', $id);
    if ($activationStmt->execute()) {
        $res = $activationStmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $activations[] = $row;
            }
        }
    }
}

$flashes = get_all_flashes();
$csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
$remainingSlots = max(0, (int) $license['max_devices'] - (int) $license['activation_count']);

require_once __DIR__ . '/templates/header.php';
?>
<div class="container mt-4">
    <a href="licenses.php" class="btn btn-link ps-0">&larr; Lisans listesine dön</a>

    <?php foreach ($flashes as $flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?> mt-3" role="alert">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endforeach; ?>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">Lisans Bilgilerini Güncelle</div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <div class="mb-3">
                            <label class="form-label">Lisans Kodu</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($license['license_key'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="owner" class="form-label">Sahip Adı</label>
                            <input type="text" id="owner" name="owner" class="form-control" required value="<?= htmlspecialchars($license['owner'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Lisans Türü</label>
                            <select id="type" name="type" class="form-select">
                                <option value="FULL" <?= $license['type'] === 'FULL' ? 'selected' : '' ?>>FULL</option>
                                <option value="LIMITED" <?= $license['type'] === 'LIMITED' ? 'selected' : '' ?>>LIMITED</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="max_devices" class="form-label">Maksimum Cihaz Sayısı</label>
                            <input type="number" id="max_devices" name="max_devices" class="form-control" value="<?= (int) $license['max_devices'] ?>" min="1">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="active" name="active" <?= (int) $license['active'] === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="active">Lisans aktiftir</label>
                        </div>
                        <button type="submit" class="btn btn-success">Değişiklikleri Kaydet</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">Lisans Özeti</div>
                <div class="card-body">
                    <p><strong>Oluşturulma:</strong> <?= htmlspecialchars($license['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Aktivasyonlar:</strong> <?= (int) $license['activation_count'] ?> / <?= (int) $license['max_devices'] ?> (Kalan: <?= $remainingSlots ?>)</p>
                    <p><strong>Durum:</strong> <?= (int) $license['active'] === 1 ? '✅ Aktif' : '❌ Pasif' ?></p>
                    <p class="text-muted">Lisans yetkileri ve cihaz limitini buradan yönetebilirsiniz.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-secondary text-white">Cihaz Aktivasyonları</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Makine ID</th>
                        <th>IP</th>
                        <th>Aktivasyon Tarihi</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($activations): ?>
                    <?php foreach ($activations as $activation): ?>
                        <tr>
                            <td><?= (int) $activation['id'] ?></td>
                            <td><code><?= htmlspecialchars($activation['machine_id'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars($activation['ip'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($activation['activated_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <form method="POST" action="delete_activation.php" class="d-inline" onsubmit="return confirm('Bu cihaz aktivasyonunu silmek istediğinize emin misiniz?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="license_id" value="<?= (int) $license['id'] ?>">
                                    <input type="hidden" name="activation_id" value="<?= (int) $activation['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Bu lisans için kayıtlı aktivasyon bulunmuyor.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
