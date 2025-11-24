<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/LoginController.php';
require_once __DIR__ . '/../config/database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) && $_POST['remember'] == '1';

    $login = new LoginController($conn);
    $login->login($username, $password, $remember);
} else {
    header("Location: login.php");
    exit;
}
