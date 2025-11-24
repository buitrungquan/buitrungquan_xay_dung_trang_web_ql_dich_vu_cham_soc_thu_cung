<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../config/database.php';

$productController = new ProductController($conn);
$id = $_GET['id'] ?? null;

if($id && $productController->delete($id)){
    $_SESSION['success'] = 'Xóa sản phẩm thành công!';
} else {
    $_SESSION['error'] = 'Có lỗi xảy ra khi xóa sản phẩm!';
}

header("Location: products.php");
exit;

