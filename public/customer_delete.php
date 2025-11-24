<?php
session_start();
require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../config/database.php';

$customerController = new CustomerController($conn);
$id = $_GET['id'] ?? null;

if($id){
    if($customerController->delete($id)){
        $_SESSION['success'] = 'Xóa khách hàng thành công!';
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi xóa khách hàng!';
    }
}

header("Location: customers.php");
exit;

