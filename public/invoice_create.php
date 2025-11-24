<?php
session_start();
require_once __DIR__ . '/../app/controllers/InvoiceController.php';
require_once __DIR__ . '/../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../config/database.php';

$invoiceController = new InvoiceController($conn);
$appointmentController = new AppointmentController($conn);
$appointmentId = $_GET['appointment_id'] ?? null;

if(!$appointmentId){
    $_SESSION['error'] = 'Không tìm thấy lịch hẹn!';
    header("Location: appointments.php");
    exit;
}

$appointment = $appointmentController->getById($appointmentId);
if(!$appointment){
    $_SESSION['error'] = 'Không tìm thấy lịch hẹn!';
    header("Location: appointments.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $totalAmount = $_POST['total_amount'];
    
    if($invoiceController->create($appointmentId, $totalAmount)){
        $_SESSION['success'] = 'Tạo hóa đơn thành công!';
        header("Location: invoices.php");
        exit;
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi tạo hóa đơn!';
    }
}

$pageTitle = 'Tạo Hóa đơn';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt-cutoff"></i> Tạo Hóa đơn</h2>
    <a href="appointments.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="mb-3">
            <h5>Thông tin lịch hẹn</h5>
            <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($appointment['customer_name']); ?></p>
            <p><strong>Thú cưng:</strong> <?php echo htmlspecialchars($appointment['pet_name']); ?></p>
            <p><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($appointment['service_name']); ?></p>
            <p><strong>Giá dịch vụ:</strong> <?php echo number_format($appointment['service_price'], 0, ',', '.'); ?> đ</p>
        </div>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Tổng tiền (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="total_amount" class="form-control" 
                       value="<?php echo $appointment['service_price']; ?>" min="0" step="1000" required>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="appointments.php" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Tạo hóa đơn
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layout.php';
?>

