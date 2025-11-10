<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function check_auth(): void
{
    if (empty($_SESSION['admin_user'])) {
        header('Location: login.php');
        exit;
    }
}
