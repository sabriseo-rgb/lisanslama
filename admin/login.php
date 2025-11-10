<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/inc/rate_limit.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? null;

    if (!check_csrf($token)) {
        $error = 'Oturum doğrulaması başarısız. Lütfen tekrar deneyin.';
    } elseif (rate_limit('login_' . ($_SERVER['REMOTE_ADDR'] ?? 'cli'), 5, 300)) {
        $error = 'Çok sayıda giriş denemesi tespit edildi. Lütfen birkaç dakika sonra tekrar deneyin.';
    } else {
        $stmt = $mysqli->prepare('SELECT username, password_hash FROM admins WHERE username = ? LIMIT 1');
        if ($stmt === false) {
            error_log('Login query prepare failed: ' . $mysqli->error);
            $error = 'Sunucu hatası oluştu.';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;

            if ($row && password_verify($password, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_user'] = $row['username'];
                header('Location: dashboard.php');
                exit;
            }

            $error = 'Geçersiz kullanıcı adı veya şifre!';
        }
    }
}

$csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Giriş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light d-flex align-items-center" style="min-height:100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="h4 text-center mb-4">Foto Albüm Lisans Sistemi</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" id="username" name="username" class="form-control" required value="<?= $safeUsername ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
