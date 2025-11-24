<?php
// File kiểm tra cấu hình và lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Kiểm tra hệ thống</h2>";

// 1. Kiểm tra PHP version
echo "<h3>1. PHP Version:</h3>";
echo "PHP " . phpversion() . "<br><br>";

// 2. Kiểm tra database connection
echo "<h3>2. Database Connection:</h3>";
try {
    require_once __DIR__ . '/../config/database.php';
    echo "✓ Kết nối database thành công!<br>";
    echo "Database: petcare<br>";
    
    // Kiểm tra các bảng
    echo "<h4>Kiểm tra các bảng:</h4>";
    $tables = ['customers', 'pets', 'services', 'appointments', 'invoices', 'users'];
    foreach($tables as $table){
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if($stmt->rowCount() > 0){
            echo "✓ Bảng '$table' tồn tại<br>";
        } else {
            echo "✗ Bảng '$table' KHÔNG tồn tại<br>";
        }
    }
} catch (Exception $e) {
    echo "✗ Lỗi kết nối database: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 3. Kiểm tra thư mục uploads
echo "<h3>3. Thư mục uploads:</h3>";
$uploadDir = __DIR__ . '/uploads/';
if(is_dir($uploadDir)){
    echo "✓ Thư mục uploads tồn tại<br>";
    if(is_writable($uploadDir)){
        echo "✓ Thư mục uploads có quyền ghi<br>";
    } else {
        echo "✗ Thư mục uploads KHÔNG có quyền ghi<br>";
    }
} else {
    echo "✗ Thư mục uploads KHÔNG tồn tại<br>";
}
echo "<br>";

// 4. Kiểm tra các file controllers
echo "<h3>4. Kiểm tra Controllers:</h3>";
$controllers = [
    'CustomerController.php',
    'PetController.php',
    'ServiceController.php',
    'AppointmentController.php',
    'InvoiceController.php'
];
foreach($controllers as $controller){
    $path = __DIR__ . '/../app/controllers/' . $controller;
    if(file_exists($path)){
        echo "✓ $controller tồn tại<br>";
    } else {
        echo "✗ $controller KHÔNG tồn tại<br>";
    }
}
echo "<br>";

// 5. Kiểm tra session
echo "<h3>5. Kiểm tra Session:</h3>";
session_start();
if(session_status() === PHP_SESSION_ACTIVE){
    echo "✓ Session hoạt động<br>";
} else {
    echo "✗ Session KHÔNG hoạt động<br>";
}
echo "<br>";

echo "<hr>";
echo "<p><a href='login.php'>← Quay lại trang đăng nhập</a></p>";

