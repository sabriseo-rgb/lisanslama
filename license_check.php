<?php
header('Content-Type: application/json; charset=utf-8');

$baseDir = __DIR__;
$configPaths = [
    $baseDir . '/admin/inc/config.php',
    dirname($baseDir) . '/admin/inc/config.php',
];

$configLoaded = false;
foreach ($configPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $configLoaded = true;
        break;
    }
}

if (!$configLoaded || !defined('SECRET_KEY')) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => 'Configuration file missing']);
    exit;
}

require_once __DIR__ . '/admin/inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'msg' => 'Method not allowed']);
    exit;
}

$key = strtoupper(trim($_POST['key'] ?? ''));
$machine = trim($_POST['machine'] ?? '');
$signature = trim($_POST['signature'] ?? '');

if ($key === '' || $machine === '' || $signature === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'msg' => 'Missing parameters']);
    exit;
}

if (strlen($key) > 128) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'msg' => 'License key too long']);
    exit;
}

if (strlen($machine) > 128) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'msg' => 'Machine identifier too long']);
    exit;
}

$expected = hash_hmac('sha256', $key . $machine, SECRET_KEY);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'msg' => 'Invalid signature']);
    exit;
}

$stmt = $mysqli->prepare('SELECT id, owner, type, max_devices, active FROM license_keys WHERE license_key = ? LIMIT 1');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => 'Server error']);
    exit;
}

$stmt->bind_param('s', $key);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;

if (!$row) {
    echo json_encode(['status' => 'fail', 'msg' => 'License not found']);
    exit;
}

if (!(int) $row['active']) {
    echo json_encode(['status' => 'fail', 'msg' => 'License revoked']);
    exit;
}

$licenseId = (int) $row['id'];
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Cihaz zaten kayıtlı mı?
$existingStmt = $mysqli->prepare('SELECT id FROM license_activations WHERE license_id = ? AND machine_id = ? LIMIT 1');
$existingId = null;
if ($existingStmt !== false) {
    $existingStmt->bind_param('is', $licenseId, $machine);
    $existingStmt->execute();
    $existingRes = $existingStmt->get_result();
    $existingRow = $existingRes ? $existingRes->fetch_assoc() : null;
    $existingId = $existingRow['id'] ?? null;
}

$usedDevices = 0;
$countStmt = $mysqli->prepare('SELECT COUNT(DISTINCT machine_id) AS total FROM license_activations WHERE license_id = ?');
if ($countStmt !== false) {
    $countStmt->bind_param('i', $licenseId);
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $countRow = $countRes ? $countRes->fetch_assoc() : ['total' => 0];
    $usedDevices = (int) ($countRow['total'] ?? 0);
}

if ($existingId === null && $usedDevices >= (int) $row['max_devices']) {
    echo json_encode(['status' => 'fail', 'msg' => 'Device limit exceeded']);
    exit;
}

if ($existingId !== null) {
    $updateStmt = $mysqli->prepare('UPDATE license_activations SET activated_at = NOW(), ip = ? WHERE id = ?');
    if ($updateStmt !== false) {
        $updateStmt->bind_param('si', $ip, $existingId);
        $updateStmt->execute();
    }
} else {
    $insertStmt = $mysqli->prepare('INSERT INTO license_activations (license_id, machine_id, ip) VALUES (?, ?, ?)');
    if ($insertStmt !== false) {
        $insertStmt->bind_param('iss', $licenseId, $machine, $ip);
        if ($insertStmt->execute()) {
            $usedDevices++;
        }
    }
}

$remaining = max(0, (int) $row['max_devices'] - $usedDevices);

echo json_encode([
    'status' => 'ok',
    'owner' => $row['owner'],
    'type' => $row['type'],
    'activated_at' => date('Y-m-d H:i:s'),
    'remaining_slots' => $remaining,
]);
