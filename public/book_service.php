<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user'){
    $_SESSION['error'] = 'Vui lòng đăng nhập để đặt lịch!';
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/ServiceController.php';
require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../config/database.php';

$serviceController = new ServiceController($conn);
$customerController = new CustomerController($conn);
$petController = new PetController($conn);
$appointmentController = new AppointmentController($conn);

$serviceId = $_GET['service_id'] ?? null;
if(!$serviceId){
    $_SESSION['error'] = 'Không tìm thấy dịch vụ!';
    header("Location: index.php");
    exit;
}

$service = $serviceController->getById($serviceId);
if(!$service){
    $_SESSION['error'] = 'Dịch vụ không tồn tại!';
    header("Location: index.php");
    exit;
}

// Lấy thông tin khách hàng từ username
$username = $_SESSION['username'];
$customer = null;
try {
    // Tìm customer theo email hoặc phone (giả sử username = email hoặc phone)
    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = :email OR phone = :phone LIMIT 1");
    $stmt->execute(['email' => $username, 'phone' => $username]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Nếu không tìm thấy, sẽ tạo mới
}

$pets = [];
if($customer){
    $pets = $petController->getByCustomerId($customer['id']);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Tạo hoặc lấy customer
    if(!$customer){
        $customerData = [
            'name' => $_POST['customer_name'],
            'email' => $_POST['customer_email'] ?? null,
            'phone' => $_POST['customer_phone'],
            'address' => $_POST['customer_address'] ?? null
        ];
        $customerController->create($customerData);
        $customerId = $conn->lastInsertId();
    } else {
        $customerId = $customer['id'];
    }
    
    // Xử lý upload ảnh thú cưng
    $photo = null;
    if(isset($_FILES['pet_photo']) && $_FILES['pet_photo']['error'] === UPLOAD_ERR_OK){
        $uploadDir = __DIR__ . '/uploads/';
        if(!is_dir($uploadDir)){
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['pet_photo']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if(in_array(strtolower($fileExtension), $allowedExtensions)){
            $fileName = uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if(move_uploaded_file($_FILES['pet_photo']['tmp_name'], $targetPath)){
                $photo = $fileName;
            }
        }
    }
    
    // Tạo pet nếu chưa có
    $petId = $_POST['pet_id'] ?? null;
    if(!$petId && $_POST['pet_name']){
        $petData = [
            'customer_id' => $customerId,
            'name' => $_POST['pet_name'],
            'type' => $_POST['pet_type'],
            'breed' => $_POST['pet_breed'] ?? null,
            'age' => $_POST['pet_age'] ? (int)$_POST['pet_age'] : null,
            'gender' => $_POST['pet_gender'] ?? null,
            'photo' => $photo
        ];
        $petController->create($petData);
        $petId = $conn->lastInsertId();
    }
    
    // Tạo appointment
    $appointmentData = [
        'customer_id' => $customerId,
        'pet_id' => $petId,
        'service_id' => $serviceId,
        'appointment_date' => $_POST['appointment_date'],
        'status' => 'pending',
        'notes' => $_POST['notes'] ?? null
    ];
    
    if($appointmentController->create($appointmentData)){
        $_SESSION['success'] = 'Đặt lịch thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.';
        header("Location: customer_appointments.php");
        exit;
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi đặt lịch!';
    }
}

$pageTitle = 'Đặt lịch dịch vụ';
ob_start();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-calendar-check"></i> Đặt lịch dịch vụ</h4>
                </div>
                <div class="card-body p-4">
                    <!-- Thông tin dịch vụ -->
                    <div class="alert alert-info">
                        <h5><i class="bi bi-bag-check"></i> <?php echo htmlspecialchars($service['name']); ?></h5>
                        <p class="mb-1"><?php echo htmlspecialchars($service['description'] ?? 'Dịch vụ chăm sóc thú cưng'); ?></p>
                        <strong class="text-success">Giá: <?php echo number_format($service['price'], 0, ',', '.'); ?> đ</strong>
                        <?php if($service['duration']): ?>
                            <span class="text-muted"> | Thời gian: <?php echo $service['duration']; ?> phút</span>
                        <?php endif; ?>
                    </div>

                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <!-- Thông tin khách hàng -->
                        <h5 class="mb-3">Thông tin khách hàng</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="customer_phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['phone'] ?? $username); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="customer_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['email'] ?? $username); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" name="customer_address" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Thông tin thú cưng -->
                        <h5 class="mb-3 mt-4">Thông tin thú cưng</h5>
                        <?php if(!empty($pets)): ?>
                            <div class="mb-3">
                                <label class="form-label">Chọn thú cưng</label>
                                <select name="pet_id" id="pet_select" class="form-select" onchange="togglePetForm()">
                                    <option value="">-- Chọn thú cưng --</option>
                                    <?php foreach($pets as $pet): ?>
                                        <option value="<?php echo $pet['id']; ?>">
                                            <?php echo htmlspecialchars($pet['name'] . ' (' . $pet['type'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="new">+ Thêm thú cưng mới</option>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div id="pet_form" style="display: <?php echo empty($pets) ? 'block' : 'none'; ?>;">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tên thú cưng <span class="text-danger">*</span></label>
                                    <input type="text" name="pet_name" id="pet_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Loại <span class="text-danger">*</span></label>
                                    <select name="pet_type" id="pet_type" class="form-select" required>
                                        <option value="">-- Chọn --</option>
                                        <option value="dog">Chó</option>
                                        <option value="cat">Mèo</option>
                                        <option value="bird">Chim</option>
                                        <option value="rabbit">Thỏ</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Giống</label>
                                    <input type="text" name="pet_breed" id="pet_breed" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tuổi</label>
                                    <input type="number" name="pet_age" id="pet_age" class="form-control" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Giới tính</label>
                                    <select name="pet_gender" id="pet_gender" class="form-select">
                                        <option value="">-- Chọn --</option>
                                        <option value="male">Đực</option>
                                        <option value="female">Cái</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh thú cưng</label>
                                <input type="file" name="pet_photo" id="pet_photo" class="form-control" accept="image/*">
                                <small class="text-muted">Chấp nhận: JPG, PNG, GIF (tối đa 5MB)</small>
                                <div id="photo_preview" class="mt-2" style="display: none;">
                                    <img id="preview_img" src="" alt="Preview" style="max-width: 200px; border-radius: 5px;">
                                </div>
                            </div>
                        </div>

                        <!-- Ngày giờ hẹn -->
                        <h5 class="mb-3 mt-4">Thời gian hẹn</h5>
                        <div class="mb-3">
                            <label class="form-label">Ngày và giờ <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="appointment_date" class="form-control" required 
                                   min="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Ghi chú thêm về thú cưng hoặc yêu cầu đặc biệt..."></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php" class="btn btn-secondary">Hủy</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Xác nhận đặt lịch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePetForm() {
    const select = document.getElementById('pet_select');
    const form = document.getElementById('pet_form');
    if(select.value === 'new' || select.value === ''){
        form.style.display = 'block';
        document.getElementById('pet_name').required = true;
    } else {
        form.style.display = 'none';
        document.getElementById('pet_name').required = false;
    }
}

// Preview ảnh khi chọn
document.getElementById('pet_photo')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_img').src = e.target.result;
            document.getElementById('photo_preview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/customer_layout.php';
?>

