<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/inc/utils.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: licenses.php');
    exit;
}

$licenseId = (int) ($_POST['license_id'] ?? 0);
$activationId = (int) ($_POST['activation_id'] ?? 0);
$token = $_POST['csrf_token'] ?? null;

if ($licenseId <= 0) {
    set_flash('status', 'Geçersiz lisans isteği.', 'danger');
    header('Location: licenses.php');
    exit;
}

$redirect = 'license_edit.php?id=' . $licenseId;

if ($activationId > 0 && check_csrf($token)) {
    $stmt = $mysqli->prepare('DELETE FROM license_activations WHERE id = ? AND license_id = ?');
    if ($stmt === false) {
        error_log('Activation delete prepare failed: ' . $mysqli->error);
        set_flash('license', 'Aktivasyon silinirken bir hata oluştu.', 'danger');
    } else {
        $stmt->bind_param('ii', $activationId, $licenseId);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                set_flash('license', 'Cihaz aktivasyonu kaldırıldı.', 'success');
            } else {
                set_flash('license', 'Aktivasyon kaydı bulunamadı.', 'warning');
            }
        } else {
            error_log('Activation delete failed: ' . $stmt->error);
            set_flash('license', 'Aktivasyon silinirken bir hata oluştu.', 'danger');
        }
    }
} else {
    set_flash('license', 'Geçersiz istek veya oturum anahtarı.', 'danger');
}

header('Location: ' . $redirect);
exit;
