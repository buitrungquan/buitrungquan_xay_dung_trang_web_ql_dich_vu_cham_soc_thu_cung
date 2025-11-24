<?php
session_start();
require_once __DIR__ . '/../app/controllers/InvoiceController.php';
require_once __DIR__ . '/../config/database.php';

$invoiceController = new InvoiceController($conn);
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: invoices.php");
    exit;
}

$invoice = $invoiceController->getById($id);
if(!$invoice){
    $_SESSION['error'] = 'Không tìm thấy hóa đơn!';
    header("Location: invoices.php");
    exit;
}

$pageTitle = 'Chi tiết Hóa đơn';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-receipt"></i> Chi tiết Hóa đơn</h2>
        <p class="text-muted mb-0">Mã hóa đơn: <strong class="text-primary"><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></p>
    </div>
    <div>
        <a href="invoices.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> In hóa đơn
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Invoice Info -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin hóa đơn</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Số hóa đơn</p>
                        <h5 class="text-primary"><?php echo htmlspecialchars($invoice['invoice_number']); ?></h5>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Ngày tạo</p>
                        <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Trạng thái thanh toán</p>
                        <p class="mb-0">
                            <?php if($invoice['payment_status'] == 'paid'): ?>
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-check-circle"></i> Đã thanh toán
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning fs-6">
                                    <i class="bi bi-clock-history"></i> Chưa thanh toán
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if($invoice['payment_date']): ?>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Ngày thanh toán</p>
                            <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($invoice['payment_date'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-person"></i> Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Tên khách hàng</p>
                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($invoice['customer_name']); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Số điện thoại</p>
                        <p class="mb-0">
                            <a href="tel:<?php echo htmlspecialchars($invoice['customer_phone']); ?>" class="text-decoration-none">
                                <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($invoice['customer_phone']); ?>
                            </a>
                        </p>
                    </div>
                    <?php if($invoice['customer_address']): ?>
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1">Địa chỉ</p>
                            <p class="mb-0">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($invoice['customer_address']); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Service/Product Details -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Chi tiết đơn hàng</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <?php if($invoice['pet_name']): ?>
                        <tr>
                            <th width="30%" class="bg-light">Thú cưng</th>
                            <td><?php echo htmlspecialchars($invoice['pet_name'] . ' (' . $invoice['pet_type'] . ')'); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th class="bg-light">Sản phẩm/Dịch vụ</th>
                            <td>
                                <strong><?php echo htmlspecialchars($invoice['service_name'] ?? 'N/A'); ?></strong>
                            </td>
                        </tr>
                        <?php if($invoice['service_description']): ?>
                        <tr>
                            <th class="bg-light">Mô tả</th>
                            <td><?php echo htmlspecialchars($invoice['service_description']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th class="bg-light">Ngày hẹn</th>
                            <td>
                                <i class="bi bi-calendar-event"></i> 
                                <?php echo date('d/m/Y H:i', strtotime($invoice['appointment_date'])); ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Thành tiền</th>
                            <td>
                                <h4 class="text-success mb-0">
                                    <?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> đ
                                </h4>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php if($invoice['notes']): ?>
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong><i class="bi bi-sticky"></i> Ghi chú:</strong>
                        <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($invoice['notes'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Summary Sidebar -->
    <div class="col-lg-4">
        <div class="card shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Tổng kết</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Tổng tiền:</span>
                    <h4 class="text-success mb-0">
                        <?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> đ
                    </h4>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <?php if($invoice['payment_status'] != 'paid'): ?>
                        <a href="invoice_pay.php?id=<?php echo $invoice['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-credit-card"></i> Đánh dấu đã thanh toán
                        </a>
                    <?php else: ?>
                        <button class="btn btn-success" disabled>
                            <i class="bi bi-check-circle"></i> Đã thanh toán
                        </button>
                    <?php endif; ?>
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="bi bi-printer"></i> In hóa đơn
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layout.php';
?>

