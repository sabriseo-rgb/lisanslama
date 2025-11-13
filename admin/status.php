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

$id = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? 'deactivate';
$token = $_POST['csrf_token'] ?? null;
$redirect = $_POST['redirect'] ?? '/admin/licenses.php';

if (strpos($redirect, '/') !== 0) {
    $redirect = '/admin/licenses.php';
}

if ($id > 0 && check_csrf($token)) {
    $newStatus = $action === 'activate' ? 1 : 0;
    $stmt = $mysqli->prepare('UPDATE license_keys SET active = ? WHERE id = ?');

    if ($stmt === false) {
        error_log('License status update prepare failed: ' . $mysqli->error);
        set_flash('status', 'Lisans durumu güncellenirken hata oluştu.', 'danger');
    } else {
        $stmt->bind_param('ii', $newStatus, $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = $newStatus === 1 ? 'Lisans yeniden aktifleştirildi.' : 'Lisans pasifleştirildi.';
                set_flash('status', $message, 'success');
            } else {
                set_flash('status', 'Lisans durumu güncellenemedi.', 'warning');
            }
        } else {
            error_log('License status update failed: ' . $stmt->error);
            set_flash('status', 'Lisans durumu güncellenirken hata oluştu.', 'danger');
        }
    }
} else {
    set_flash('status', 'Geçersiz istek veya oturum anahtarı.', 'danger');
}

header('Location: ' . $redirect);
exit;
