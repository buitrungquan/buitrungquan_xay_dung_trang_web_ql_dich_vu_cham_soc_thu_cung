<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user'){
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../config/database.php';

$appointmentController = new AppointmentController($conn);
$username = $_SESSION['username'];

// Lấy appointments của khách hàng
$appointments = [];
try {
    // Tìm customer theo username (email hoặc phone)
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = :username OR phone = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($customer){
        $stmt = $conn->prepare("
            SELECT a.*, 
                   c.name as customer_name,
                   p.name as pet_name, p.type as pet_type, p.photo as pet_photo,
                   s.name as service_name, s.price as service_price
            FROM appointments a
            LEFT JOIN customers c ON a.customer_id = c.id
            LEFT JOIN pets p ON a.pet_id = p.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.customer_id = :customer_id
            ORDER BY a.appointment_date DESC
        ");
        $stmt->execute(['customer_id' => $customer['id']]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Nếu lỗi, để mảng rỗng
    error_log("Error fetching appointments: " . $e->getMessage());
}

$statusColors = [
    'pending' => 'warning',
    'in_progress' => 'info',
    'completed' => 'success',
    'canceled' => 'danger'
];

$statusLabels = [
    'pending' => 'Chờ xử lý',
    'in_progress' => 'Đang thực hiện',
    'completed' => 'Hoàn thành',
    'canceled' => 'Đã hủy'
];

$pageTitle = 'Lịch hẹn của tôi';
ob_start();
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-check"></i> Lịch hẹn của tôi</h2>
        <a href="index.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Đặt lịch mới
        </a>
    </div>

    <?php if(empty($appointments)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Chưa có lịch hẹn nào</h4>
                <p class="text-muted">Hãy đặt lịch dịch vụ cho thú cưng của bạn!</p>
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle"></i> Đặt lịch ngay
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($appointments as $apt): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title"><?php echo htmlspecialchars($apt['service_name']); ?></h5>
                                <span class="badge bg-<?php echo $statusColors[$apt['status']]; ?>">
                                    <?php echo $statusLabels[$apt['status']]; ?>
                                </span>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-heart text-primary"></i>
                                <strong>Thú cưng:</strong> 
                                <?php if($apt['pet_photo']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($apt['pet_photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($apt['pet_name']); ?>" 
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; margin-left: 10px; vertical-align: middle;">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($apt['pet_name'] . ' (' . $apt['pet_type'] . ')'); ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-calendar text-primary"></i>
                                <strong>Ngày giờ:</strong> <?php echo date('d/m/Y H:i', strtotime($apt['appointment_date'])); ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-cash text-success"></i>
                                <strong>Giá:</strong> <?php echo number_format($apt['service_price'], 0, ',', '.'); ?> đ
                            </div>
                            
                            <?php if($apt['notes']): ?>
                                <div class="mb-2">
                                    <i class="bi bi-file-text"></i>
                                    <strong>Ghi chú:</strong> <?php echo htmlspecialchars($apt['notes']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/customer_layout.php';
?>

