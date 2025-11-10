<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../admin/inc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "msg" => "Method not allowed"]);
    exit;
}

$key = strtoupper(trim($_POST['key'] ?? ''));
$machine = trim($_POST['machine'] ?? '');
$signature = trim($_POST['signature'] ?? '');

if ($key === '' || $machine === '' || $signature === '') {
    http_response_code(400);
    echo json_encode(["status" => "error", "msg" => "Missing parameters"]);
    exit;
}

$expected = hash_hmac('sha256', $key.$machine, SECRET_KEY);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "msg" => "Invalid signature"]);
    exit;
}

require_once __DIR__ . '/../admin/inc/db.php';

$stmt = $mysqli->prepare("SELECT id, owner, type, max_devices, active FROM license_keys WHERE license_key=? LIMIT 1");
$stmt->bind_param("s", $key);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    if (!$row['active']) {
        echo json_encode(["status"=>"fail","msg"=>"License revoked"]);
        exit;
    }
    $stmt2 = $mysqli->prepare("INSERT INTO license_activations (license_id, machine_id, ip) VALUES (?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt2->bind_param("iss", $row['id'], $machine, $ip);
    $stmt2->execute();

    echo json_encode([
        "status" => "ok",
        "owner" => $row['owner'],
        "type" => $row['type'],
        "activated_at" => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode(["status"=>"fail","msg"=>"License not found"]);
}
