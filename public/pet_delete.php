<?php
session_start();
require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../config/database.php';

$petController = new PetController($conn);
$id = $_GET['id'] ?? null;

if($id){
    if($petController->delete($id)){
        $_SESSION['success'] = 'Xóa thú cưng thành công!';
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi xóa thú cưng!';
    }
}

header("Location: pets.php");
exit;

