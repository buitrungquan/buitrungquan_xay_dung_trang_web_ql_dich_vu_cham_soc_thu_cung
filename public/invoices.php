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

require_once __DIR__ . '/../app/controllers/InvoiceController.php';
require_once __DIR__ . '/../config/database.php';

try {
    $invoiceController = new InvoiceController($conn);
    $invoices = $invoiceController->getAll();
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    $invoices = [];
}

$pageTitle = 'Quản lý Hóa đơn';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt"></i> Quản lý Hóa đơn</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Số hóa đơn</th>
                        <th>Khách hàng</th>
                        <th>Thú cưng</th>
                        <th>Dịch vụ</th>
                        <th>Ngày hẹn</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($invoices)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Chưa có hóa đơn nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($invoices as $invoice): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($invoice['customer_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($invoice['customer_phone']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($invoice['pet_name']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['service_name']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($invoice['appointment_date'])); ?></td>
                                <td><strong class="text-success"><?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> đ</strong></td>
                                <td>
                                    <?php if($invoice['payment_status'] == 'paid'): ?>
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="invoice_view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Xem
                                    </a>
                                    <?php if($invoice['payment_status'] == 'unpaid'): ?>
                                        <a href="invoice_pay.php?id=<?php echo $invoice['id']; ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Xác nhận thanh toán hóa đơn này?');">
                                            <i class="bi bi-check-circle"></i> Thanh toán
                                        </a>
                                    <?php endif; ?>
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

