<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user'){
    $_SESSION['error'] = 'Vui lòng đăng nhập để thanh toán!';
    header("Location: login.php");
    exit;
}

if(empty($_SESSION['cart'])){
    $_SESSION['error'] = 'Giỏ hàng trống!';
    header("Location: cart.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../app/controllers/InvoiceController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../config/database.php';
$paymentConfig = require __DIR__ . '/../config/payment.php';

$customerController = new CustomerController($conn);
$petController = new PetController($conn);
$appointmentController = new AppointmentController($conn);
$invoiceController = new InvoiceController($conn);
$productController = new ProductController($conn);

$username = $_SESSION['username'];

// Lấy thông tin khách hàng
$customer = null;
try {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = :email OR phone = :phone LIMIT 1");
    $stmt->execute(['email' => $username, 'phone' => $username]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Nếu không tìm thấy
}

$pets = [];
if($customer){
    $pets = $petController->getByCustomerId($customer['id']);
}

$defaultCustomerName = $customer['name'] ?? ($_SESSION['full_name'] ?? $_SESSION['username']);
$defaultCustomerEmail = $customer['email'] ?? (filter_var($username, FILTER_VALIDATE_EMAIL) ? $username : null);

// Tính tổng tiền
$total = 0;
foreach($_SESSION['cart'] as $item){
    $total += $item['price'] * $item['quantity'];
}

$orderCode = 'PC' . strtoupper(dechex(time()));
$qrTemplate = $paymentConfig['qr_api_template'] ?? null;
$qrImageUrl = null;
if($qrTemplate){
    $qrImageUrl = str_replace(
        ['{amount}', '{order_code}'],
        [ (int)$total, $orderCode ],
        $qrTemplate
    );
}
$selectedPaymentMethod = $_POST['payment_method'] ?? 'cod';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $paymentMethod = $_POST['payment_method'] ?? 'cod';
    $submittedName = trim($_POST['customer_name'] ?? '');
    if($submittedName === ''){
        $submittedName = $defaultCustomerName;
    }
    $submittedEmail = trim($_POST['customer_email'] ?? $defaultCustomerEmail);
    $noteInput = trim($_POST['notes'] ?? '');
    $paymentNote = $paymentMethod === 'bank' ? 'Phương thức: Chuyển khoản ngân hàng' : 'Phương thức: Thanh toán khi nhận hàng';
    $combinedNotes = trim($noteInput ? $noteInput . ' | ' . $paymentNote : $paymentNote);

    // Tạo hoặc lấy customer
    if(!$customer){
        $customerData = [
            'name' => $submittedName,
            'email' => $submittedEmail,
            'phone' => $_POST['customer_phone'],
            'address' => $_POST['customer_address'] ?? null
        ];
        $customerController->create($customerData);
        $customerId = $conn->lastInsertId();
    } else {
        $customerId = $customer['id'];
    }
    
    // Tạo appointments và invoices cho từng item trong giỏ hàng
    $createdInvoices = [];
    $successCount = 0;
    $orderTotal = 0;
    
    foreach($_SESSION['cart'] as $item){
        // Kiểm tra stock nếu là sản phẩm
        if($item['type'] == 'product'){
            $product = $productController->getById($item['id']);
            if(!$product || $product['stock'] < $item['quantity']){
                $_SESSION['error'] = 'Sản phẩm "' . htmlspecialchars($item['name']) . '" không đủ tồn kho!';
                header("Location: cart.php");
                exit;
            }
        }
        
        // Tạo appointment cho dịch vụ hoặc sản phẩm
        $appointmentDate = $_POST['appointment_date'] ?? date('Y-m-d H:i:s');
        
        for($i = 0; $i < $item['quantity']; $i++){
            $appointmentData = [
                'customer_id' => $customerId,
                'pet_id' => null, // Sẽ được xử lý trong AppointmentController
                'service_id' => $item['type'] == 'service' ? $item['id'] : null,
                'appointment_date' => $item['type'] == 'service' ? $appointmentDate : date('Y-m-d H:i:s'),
                'status' => 'pending',
                'notes' => $combinedNotes ?: null
            ];
            
            if($appointmentController->create($appointmentData)){
                $appointmentId = $conn->lastInsertId();
                // Tạo invoice với giá đúng
                if($invoiceController->create($appointmentId, $item['price'])){
                    $invoiceId = $conn->lastInsertId();
                    $createdInvoices[] = $invoiceId;
                    $orderTotal += $item['price'];
                    $successCount++;
                }
            }
        }
        
        // Cập nhật stock nếu là sản phẩm
        if($item['type'] == 'product'){
            $stmt = $conn->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :id");
            $stmt->execute(['quantity' => $item['quantity'], 'id' => $item['id']]);
        }
    }
    
    if($successCount > 0){
        // Lưu thông tin đơn hàng vào session để hiển thị trang thành công
        $_SESSION['order_success'] = [
            'invoice_ids' => $createdInvoices,
            'order_total' => $orderTotal,
            'customer_name' => $submittedName,
            'customer_phone' => $_POST['customer_phone'],
            'customer_address' => $_POST['customer_address'] ?? '',
            'payment_method' => $paymentMethod,
            'order_code' => $orderCode
        ];
        
        // Xóa giỏ hàng
        unset($_SESSION['cart']);
        header("Location: order_success.php");
        exit;
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi thanh toán!';
    }
}

$pageTitle = 'Thanh toán';
ob_start();
?>
<style>
    .payment-option {
        border: 2px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px 18px;
        display: flex;
        gap: 12px;
        align-items: center;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background: #fff;
        height: 100%;
    }
    .payment-option.active {
        border-color: #667eea;
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.25);
    }
    .payment-option input {
        display: none;
    }
    .payment-option .icon-pill {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: rgba(102, 126, 234, 0.15);
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
</style>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-credit-card"></i> Thông tin thanh toán</h4>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <!-- Thông tin khách hàng -->
                        <h5 class="mb-3">Thông tin người nhận</h5>
                        <p class="text-muted small mb-3">Vui lòng điền đầy đủ thông tin để chúng tôi có thể giao hàng cho bạn.</p>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($defaultCustomerName); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="customer_phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['phone'] ?? $username); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="customer_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($defaultCustomerEmail ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Địa chỉ nhận hàng <span class="text-danger">*</span></label>
                                <input type="text" name="customer_address" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- Phương thức thanh toán -->
                        <h5 class="mb-3 mt-4">Phương thức thanh toán</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="payment-option <?php echo $selectedPaymentMethod === 'bank' ? '' : 'active'; ?>" data-method="cod">
                                    <input type="radio" name="payment_method" value="cod" <?php echo $selectedPaymentMethod !== 'bank' ? 'checked' : ''; ?>>
                                    <div class="icon-pill"><i class="bi bi-box-seam"></i></div>
                                    <div>
                                        <strong>Thanh toán khi nhận hàng</strong>
                                        <p class="text-muted small mb-0">Trả tiền trực tiếp khi nhận dịch vụ/sản phẩm.</p>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="payment-option <?php echo $selectedPaymentMethod === 'bank' ? 'active' : ''; ?>" data-method="bank">
                                    <input type="radio" name="payment_method" value="bank" <?php echo $selectedPaymentMethod === 'bank' ? 'checked' : ''; ?>>
                                    <div class="icon-pill"><i class="bi bi-bank"></i></div>
                                    <div>
                                        <strong>Thanh toán bằng ngân hàng</strong>
                                        <p class="text-muted small mb-0">Quét mã QR để chuyển khoản nhanh chóng.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Ngày giờ hẹn (chỉ cho dịch vụ) -->
                        <?php 
                        $hasServices = false;
                        foreach($_SESSION['cart'] as $item){
                            if($item['type'] == 'service'){
                                $hasServices = true;
                                break;
                            }
                        }
                        if($hasServices):
                        ?>
                        <h5 class="mb-3 mt-4">Thời gian hẹn dịch vụ</h5>
                        <div class="mb-3">
                            <label class="form-label">Ngày và giờ <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="appointment_date" class="form-control" required 
                                   min="<?php echo date('Y-m-d\TH:i'); ?>">
                            <small class="text-muted">Chỉ áp dụng cho các dịch vụ trong giỏ hàng</small>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="appointment_date" value="<?php echo date('Y-m-d H:i:s'); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Ghi chú thêm về thú cưng hoặc yêu cầu đặc biệt..."></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="cart.php" class="btn btn-secondary">Quay lại</a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Xác nhận thanh toán
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Tóm tắt đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach($_SESSION['cart'] as $item): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <br>
                                    <small class="text-muted">Số lượng: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span class="text-success fw-bold">
                                    <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tổng cộng:</h5>
                        <h4 class="mb-0 text-success">
                            <?php echo number_format($total, 0, ',', '.'); ?> đ
                        </h4>
                    </div>
                    <p id="paymentHint" class="small text-muted mt-3">
                        <?php echo $selectedPaymentMethod === 'bank' 
                            ? 'Quý khách vui lòng quét mã QR để hoàn tất chuyển khoản.' 
                            : 'Quý khách sẽ thanh toán trực tiếp khi nhận dịch vụ/sản phẩm.'; ?>
                    </p>
                    <hr>
                    <div id="qrWrapper" class="text-center qr-payment <?php echo $selectedPaymentMethod === 'bank' ? '' : 'd-none'; ?>">
                        <?php if($qrImageUrl): ?>
                            <h6 class="fw-bold mb-2">Quét QR để thanh toán</h6>
                            <img src="<?php echo htmlspecialchars($qrImageUrl); ?>" alt="QR thanh toán" class="img-fluid mb-2" style="max-width:220px;">
                            <p class="small text-muted mb-1">Mã đơn: <?php echo htmlspecialchars($orderCode); ?></p>
                            <p class="small text-muted mb-0">Cập nhật link QR trong <code>config/payment.php</code> hoặc biến môi trường <code>PETCARE_QR_API</code>.</p>
                        <?php else: ?>
                            <p class="small text-muted mb-0">Chưa cấu hình API QR. Cập nhật <code>config/payment.php</code>.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
function updatePaymentUI(method){
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.toggle('active', option.dataset.method === method);
    });
    const qrWrapper = document.getElementById('qrWrapper');
    const paymentHint = document.getElementById('paymentHint');
    if(qrWrapper){
        qrWrapper.classList.toggle('d-none', method !== 'bank');
    }
    if(paymentHint){
        paymentHint.textContent = method === 'bank' 
            ? 'Quý khách vui lòng quét mã QR để hoàn tất chuyển khoản.'
            : 'Quý khách sẽ thanh toán trực tiếp khi nhận dịch vụ/sản phẩm.';
    }
}
paymentRadios.forEach(radio => {
    radio.addEventListener('change', (e) => updatePaymentUI(e.target.value));
});
if(paymentRadios.length){
    const checked = document.querySelector('input[name="payment_method"]:checked');
    updatePaymentUI(checked ? checked.value : 'cod');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/customer_layout.php';
?>

