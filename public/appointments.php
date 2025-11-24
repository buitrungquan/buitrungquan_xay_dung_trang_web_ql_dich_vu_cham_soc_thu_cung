<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    $_SESSION['error'] = 'Bạn không có quyền truy cập trang này!';
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../config/database.php';

try {
    $appointmentController = new AppointmentController($conn);
    $appointments = $appointmentController->getAll();
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    $appointments = [];
}

// Màu sắc cho các trạng thái
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

$pageTitle = 'Quản lý Lịch hẹn';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-check"></i> Quản lý Lịch hẹn</h2>
    <a href="appointment_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Đặt lịch hẹn
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Thú cưng</th>
                        <th>Dịch vụ</th>
                        <th>Ngày giờ</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($appointments)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Chưa có lịch hẹn nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo $appointment['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($appointment['customer_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($appointment['customer_phone']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['pet_name'] . ' (' . $appointment['pet_type'] . ')'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($appointment['service_name']); ?><br>
                                    <small class="text-success"><?php echo number_format($appointment['service_price'], 0, ',', '.'); ?> đ</small>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($appointment['appointment_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $statusColors[$appointment['status']]; ?>">
                                        <?php echo $statusLabels[$appointment['status']]; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="appointment_edit.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <?php if($appointment['status'] == 'completed'): ?>
                                        <a href="invoice_create.php?appointment_id=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-receipt"></i> Tạo hóa đơn
                                        </a>
                                    <?php endif; ?>
                                    <a href="appointment_delete.php?id=<?php echo $appointment['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn có chắc muốn xóa lịch hẹn này?');">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layout.php';
?>

