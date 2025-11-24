<?php
session_start();
require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../config/database.php';

$customerController = new CustomerController($conn);
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: customers.php");
    exit;
}

$customer = $customerController->getById($id);
if(!$customer){
    $_SESSION['error'] = 'Không tìm thấy khách hàng!';
    header("Location: customers.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'] ?? null,
        'phone' => $_POST['phone'],
        'address' => $_POST['address'] ?? null
    ];
    
    if($customerController->update($id, $data)){
        $_SESSION['success'] = 'Cập nhật khách hàng thành công!';
        header("Location: customers.php");
        exit;
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
    }
}

$pageTitle = 'Sửa Khách hàng';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> Sửa Khách hàng</h2>
    <a href="customers.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Tên khách hàng <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Địa chỉ</label>
                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="customers.php" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layout.php';
?>

