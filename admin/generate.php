<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/utils.php';
check_auth();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner = trim($_POST['owner']);
    $type = $_POST['type'];
    $max_devices = intval($_POST['max_devices']);
    $key = make_license($type);

    $stmt = $mysqli->prepare("INSERT INTO license_keys (license_key, owner, type, max_devices) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $key, $owner, $type, $max_devices);
    if ($stmt->execute()) {
        $msg = "✅ Lisans oluşturuldu: <code>$key</code>";
    } else {
        $msg = "❌ Hata oluştu: ".$mysqli->error;
    }
}

require_once __DIR__ . '/templates/header.php';
?>
<div class="container mt-4">
    <h3>➕ Yeni Lisans Oluştur</h3>
    <?php if ($msg) echo "<div class='alert alert-info mt-3'>$msg</div>"; ?>
    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label>Sahip Adı:</label>
            <input type="text" name="owner" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Lisans Türü:</label>
            <select name="type" class="form-select">
                <option value="FULL">FULL</option>
                <option value="LIMITED">LIMITED</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Maksimum Cihaz Sayısı:</label>
            <input type="number" name="max_devices" class="form-control" value="1" min="1">
        </div>
        <button type="submit" class="btn btn-success">Oluştur</button>
    </form>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
