<?php
session_start();
require_once __DIR__ . '/../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../config/database.php';

$appointmentController = new AppointmentController($conn);
$id = $_GET['id'] ?? null;

if($id){
    if($appointmentController->delete($id)){
        $_SESSION['success'] = 'Xóa lịch hẹn thành công!';
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi xóa lịch hẹn!';
    }
}

header("Location: appointments.php");
exit;

