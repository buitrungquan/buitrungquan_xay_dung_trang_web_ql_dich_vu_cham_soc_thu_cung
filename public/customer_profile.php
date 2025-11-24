<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user'){
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../config/database.php';

$username = $_SESSION['username'];
$customer = null;
$pets = [];

try {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = :email OR phone = :phone LIMIT 1");
    $stmt->execute(['email' => $username, 'phone' => $username]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($customer){
        $petController = new PetController($conn);
        $pets = $petController->getByCustomerId($customer['id']);
    }
} catch (Exception $e) {
    // Nếu không tìm thấy
}

$pageTitle = 'Tài khoản của tôi';
ob_start();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person"></i> Thông tin tài khoản</h4>
                </div>
                <div class="card-body p-4">
                    <!-- Thông tin tài khoản -->
                    <h5 class="mb-3"><i class="bi bi-person-circle"></i> Thông tin đăng nhập</h5>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên đăng nhập</label>
                        <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                    
                    <?php if($customer): ?>
                        <hr>
                        <h5 class="mb-3"><i class="bi bi-person"></i> Thông tin khách hàng</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ tên</label>
                            <p><?php echo htmlspecialchars($customer['name']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p><?php echo htmlspecialchars($customer['email'] ?? '-'); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <p><?php echo htmlspecialchars($customer['phone']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <p><?php echo htmlspecialchars($customer['address'] ?? '-'); ?></p>
                        </div>
                        
                        <?php if(!empty($pets)): ?>
                            <hr>
                            <h5 class="mb-3"><i class="bi bi-heart"></i> Thú cưng của tôi (<?php echo count($pets); ?>)</h5>
                            <div class="row g-3">
                                <?php foreach($pets as $pet): ?>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <?php if($pet['photo']): ?>
                                                        <img src="uploads/<?php echo htmlspecialchars($pet['photo']); ?>" 
                                                             alt="<?php echo htmlspecialchars($pet['name']); ?>" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; margin-right: 15px;">
                                                    <?php else: ?>
                                                        <i class="bi bi-heart" style="font-size: 3rem; color: #ccc; margin-right: 15px;"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($pet['name']); ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($pet['type']); ?>
                                                            <?php if($pet['breed']): ?>
                                                                - <?php echo htmlspecialchars($pet['breed']); ?>
                                                            <?php endif; ?>
                                                            <?php if($pet['age']): ?>
                                                                (<?php echo $pet['age']; ?> tuổi)
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <hr>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Chưa có thú cưng nào. Thêm thú cưng khi đặt lịch.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <hr>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Chưa có thông tin khách hàng. Thông tin sẽ được tạo khi bạn đặt lịch lần đầu.
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Về trang chủ
                        </a>
                        <a href="logout.php" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/customer_layout.php';
?>

