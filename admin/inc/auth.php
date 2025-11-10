<?php
session_start();

function check_auth() {
    if (!isset($_SESSION['admin_user'])) {
        header('Location: login.php');
        exit;
    }
}
