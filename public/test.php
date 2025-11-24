<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2>Test hệ thống</h2>";

// Test 1: Session
echo "<h3>1. Test Session:</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "<br><br>";

// Test 2: Database
echo "<h3>2. Test Database:</h3>";
try {
    require_once __DIR__ . '/../config/database.php';
    echo "✓ Database connection OK<br>";
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM customers");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Customers table exists. Count: " . $result['count'] . "<br>";
} catch (Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
echo "<br>";

// Test 3: Controllers
echo "<h3>3. Test Controllers:</h3>";
try {
    require_once __DIR__ . '/../app/controllers/CustomerController.php';
    $customerController = new CustomerController($conn);
    $customers = $customerController->getAll();
    echo "✓ CustomerController works. Found " . count($customers) . " customers<br>";
} catch (Exception $e) {
    echo "✗ Controller Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
echo "<br>";

// Test 4: Include layout
echo "<h3>4. Test Layout:</h3>";
try {
    $pageTitle = "Test Page";
    $content = "<p>This is test content</p>";
    // Không include layout để tránh redirect
    echo "✓ Variables set OK<br>";
} catch (Exception $e) {
    echo "✗ Layout Error: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 5: File paths
echo "<h3>5. Test File Paths:</h3>";
echo "Current file: " . __FILE__ . "<br>";
echo "Current dir: " . __DIR__ . "<br>";
echo "Config path: " . __DIR__ . '/../config/database.php' . "<br>";
echo "Config exists: " . (file_exists(__DIR__ . '/../config/database.php') ? "Yes" : "No") . "<br>";
echo "Layout path: " . __DIR__ . '/../app/views/layout.php' . "<br>";
echo "Layout exists: " . (file_exists(__DIR__ . '/../app/views/layout.php') ? "Yes" : "No") . "<br>";

echo "<hr>";
echo "<p><a href='login.php'>← Quay lại đăng nhập</a> | <a href='check.php'>Kiểm tra hệ thống</a></p>";

