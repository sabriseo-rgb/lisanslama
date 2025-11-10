<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/csrf.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: licenses.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$token = $_POST['csrf_token'] ?? null;

if ($id > 0 && check_csrf($token)) {
    $stmt = $mysqli->prepare('UPDATE license_keys SET active = 0 WHERE id = ?');
    if ($stmt !== false) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}

header('Location: licenses.php');
exit;
