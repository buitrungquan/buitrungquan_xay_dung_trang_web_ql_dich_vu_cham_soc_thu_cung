<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../config/database.php';

$petController = new PetController($conn);
$customerId = $_GET['customer_id'] ?? null;

if($customerId){
    $pets = $petController->getByCustomerId($customerId);
    echo json_encode($pets);
} else {
    echo json_encode([]);
}

