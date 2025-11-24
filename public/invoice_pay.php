<?php
session_start();
require_once __DIR__ . '/../app/controllers/InvoiceController.php';
require_once __DIR__ . '/../config/database.php';

$invoiceController = new InvoiceController($conn);
$id = $_GET['id'] ?? null;

if($id){
    if($invoiceController->updatePaymentStatus($id, 'paid', date('Y-m-d H:i:s'))){
        $_SESSION['success'] = 'Cập nhật trạng thái thanh toán thành công!';
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
    }
}

header("Location: invoices.php");
exit;

