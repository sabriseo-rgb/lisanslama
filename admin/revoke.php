<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
check_auth();

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $mysqli->prepare("UPDATE license_keys SET active=0 WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: licenses.php");
exit;
