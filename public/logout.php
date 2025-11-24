<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/controllers/LoginController.php';
require_once __DIR__ . '/../config/database.php';

// Xóa cookie remember me
if(isset($_COOKIE['remember_token'])){
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}
if(isset($_COOKIE['remember_username'])){
    setcookie('remember_username', '', time() - 3600, '/', '', false, true);
}

$login = new LoginController($conn);
$login->logout();
