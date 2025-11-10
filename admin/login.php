<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);
    $stmt = $mysqli->prepare("SELECT username, password_hash FROM admins WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc() && password_verify($p, $row['password_hash'])) {
        $_SESSION['admin_user'] = $u;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Geçersiz kullanıcı adı veya şifre!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="UTF-8"><title>Admin Giriş</title></head>
<body>
<h2>Foto Albüm Lisans Sistemi - Admin Giriş</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <input type="text" name="username" placeholder="Kullanıcı Adı" required><br>
    <input type="password" name="password" placeholder="Şifre" required><br>
    <button type="submit">Giriş Yap</button>
</form>
</body>
</html>
