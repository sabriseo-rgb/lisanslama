<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/utils.php';
require_once __DIR__ . '/inc/csrf.php';

check_auth();

$message = null;
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!check_csrf($token)) {
        $message = 'Oturum doğrulaması başarısız oldu. Formu tekrar göndermeyi deneyin.';
        $messageType = 'danger';
    } else {
        $owner = trim($_POST['owner'] ?? '');
        if ($owner === '') {
            $message = 'Sahip adı boş bırakılamaz.';
            $messageType = 'danger';
        } else {
            $type = $_POST['type'] === 'FULL' ? 'FULL' : 'LIMITED';
            $max_devices = max(1, (int)($_POST['max_devices'] ?? 1));
            $key = make_license($type);

            $stmt = $mysqli->prepare('INSERT INTO license_keys (license_key, owner, type, max_devices) VALUES (?, ?, ?, ?)');
            if ($stmt === false) {
                error_log('License insert prepare failed: ' . $mysqli->error);
                $message = 'Lisans oluşturulurken bir hata meydana geldi.';
                $messageType = 'danger';
            } else {
                $stmt->bind_param('sssi', $key, $owner, $type, $max_devices);
                if ($stmt->execute()) {
                    $message = 'Lisans oluşturuldu: ' . $key;
                    $messageType = 'success';
                } else {
                    error_log('License insert failed: ' . $stmt->error);
                    $message = 'Lisans oluşturulurken bir hata meydana geldi.';
                    $messageType = 'danger';
                }
            }
        }
    }
}

$csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
$safeMessage = $message ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : '';
require_once __DIR__ . '/templates/header.php';
?>
<div class="container mt-4">
    <h3>➕ Yeni Lisans Oluştur</h3>
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> mt-3" role="alert">
            <?= $safeMessage ?>
        </div>
    <?php endif; ?>
    <form method="POST" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="mb-3">
            <label for="owner" class="form-label">Sahip Adı:</label>
            <input type="text" id="owner" name="owner" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Lisans Türü:</label>
            <select id="type" name="type" class="form-select">
                <option value="FULL">FULL</option>
                <option value="LIMITED">LIMITED</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="max_devices" class="form-label">Maksimum Cihaz Sayısı:</label>
            <input type="number" id="max_devices" name="max_devices" class="form-control" value="1" min="1">
        </div>
        <button type="submit" class="btn btn-success">Oluştur</button>
    </form>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
