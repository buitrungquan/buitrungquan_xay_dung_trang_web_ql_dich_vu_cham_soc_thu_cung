<?php
session_start();
require_once __DIR__ . '/../app/controllers/ServiceController.php';
require_once __DIR__ . '/../config/database.php';

$serviceController = new ServiceController($conn);
$id = $_GET['id'] ?? null;

if($id){
    if($serviceController->delete($id)){
        $_SESSION['success'] = 'Xóa dịch vụ thành công!';
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi xóa dịch vụ!';
    }
}

header("Location: services.php");
exit;

