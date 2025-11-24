<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user'){
    header("Location: login.php");
    exit;
}

if(!isset($_SESSION['order_success'])){
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/InvoiceController.php';
require_once __DIR__ . '/../config/database.php';

$invoiceController = new InvoiceController($conn);
$orderData = $_SESSION['order_success'];
$invoices = [];

// Lấy thông tin các invoice đã tạo
foreach($orderData['invoice_ids'] as $invoiceId){
    $invoice = $invoiceController->getById($invoiceId);
    if($invoice){
        $invoices[] = $invoice;
    }
}

$pageTitle = 'Đặt hàng thành công';
ob_start();
?>

<style>
    .success-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        animation: scaleIn 0.5s ease-out;
    }
    
    @keyframes scaleIn {
        from {
            transform: scale(0);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .success-icon i {
        font-size: 3.5rem;
        color: white;
    }
    
    .order-info-card {
        border-left: 4px solid #2563eb;
        background: linear-gradient(120deg, rgba(37, 99, 235, 0.05), rgba(14, 165, 233, 0.05));
    }
    
    .invoice-item {
        border-bottom: 1px solid #e2e8f0;
        padding: 15px 0;
    }
    
    .invoice-item:last-child {
        border-bottom: none;
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Success Header -->
            <div class="text-center mb-5">
                <div class="success-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1 class="display-5 fw-bold text-success mb-3">Đặt hàng thành công!</h1>
                <p class="lead text-muted">Cảm ơn bạn đã đặt hàng. Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất.</p>
            </div>

            <!-- Order Summary -->
            <div class="card shadow-sm mb-4 order-info-card">
                <div class="card-body p-4">
                    <h4 class="mb-4"><i class="bi bi-receipt-cutoff"></i> Thông tin đơn hàng</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Mã đơn hàng:</strong></p>
                            <p class="text-primary fw-bold"><?php echo htmlspecialchars($orderData['order_code']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Ngày đặt:</strong></p>
                            <p><?php echo date('d/m/Y H:i:s'); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Khách hàng:</strong></p>
                            <p><?php echo htmlspecialchars($orderData['customer_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Số điện thoại:</strong></p>
                            <p><?php echo htmlspecialchars($orderData['customer_phone']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p class="mb-2"><strong>Địa chỉ giao hàng:</strong></p>
                            <p><?php echo htmlspecialchars($orderData['customer_address']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Phương thức thanh toán:</strong></p>
                            <p>
                                <?php if($orderData['payment_method'] == 'bank'): ?>
                                    <span class="badge bg-info"><i class="bi bi-bank"></i> Chuyển khoản ngân hàng</span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><i class="bi bi-box-seam"></i> Thanh toán khi nhận hàng</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Tổng tiền:</strong></p>
                            <h4 class="text-success mb-0"><?php echo number_format($orderData['order_total'], 0, ',', '.'); ?> đ</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Chi tiết đơn hàng</h5>
                </div>
                <div class="card-body">
                    <?php if(empty($invoices)): ?>
                        <p class="text-muted">Đang tải thông tin...</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã hóa đơn</th>
                                        <th>Sản phẩm/Dịch vụ</th>
                                        <th>Ngày hẹn</th>
                                        <th>Thành tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($invoices as $invoice): ?>
                                        <tr>
                                            <td>
                                                <strong class="text-primary"><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($invoice['service_name'] ?? 'Sản phẩm'); ?>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($invoice['appointment_date'])); ?>
                                            </td>
                                            <td>
                                                <strong class="text-success"><?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> đ</strong>
                                            </td>
                                            <td>
                                                <?php if($invoice['payment_status'] == 'paid'): ?>
                                                    <span class="badge bg-success">Đã thanh toán</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Chưa thanh toán</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                        <td colspan="2">
                                            <h5 class="text-success mb-0">
                                                <?php echo number_format($orderData['order_total'], 0, ',', '.'); ?> đ
                                            </h5>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between gap-3 flex-wrap">
                <a href="customer_appointments.php" class="btn btn-outline-primary">
                    <i class="bi bi-calendar-check"></i> Xem lịch hẹn của tôi
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-house"></i> Về trang chủ
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Xóa session order_success sau khi hiển thị
unset($_SESSION['order_success']);

$content = ob_get_clean();
include __DIR__ . '/../app/views/customer_layout.php';
?>

