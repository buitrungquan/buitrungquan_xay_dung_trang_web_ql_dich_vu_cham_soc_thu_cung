<?php
session_start();
require_once __DIR__ . '/../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../app/controllers/ServiceController.php';
require_once __DIR__ . '/../config/database.php';

$appointmentController = new AppointmentController($conn);
$customerController = new CustomerController($conn);
$petController = new PetController($conn);
$serviceController = new ServiceController($conn);

$customers = $customerController->getAll();
$services = $serviceController->getAll();

// Lấy danh sách thú cưng khi chọn khách hàng
$pets = [];
if(isset($_GET['customer_id']) && $_GET['customer_id']){
    $pets = $petController->getByCustomerId($_GET['customer_id']);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = [
        'customer_id' => $_POST['customer_id'],
        'pet_id' => $_POST['pet_id'],
        'service_id' => $_POST['service_id'],
        'appointment_date' => $_POST['appointment_date'],
        'status' => $_POST['status'] ?? 'pending',
        'notes' => $_POST['notes'] ?? null
    ];
    
    if($appointmentController->create($data)){
        $_SESSION['success'] = 'Đặt lịch hẹn thành công!';
        header("Location: appointments.php");
        exit;
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi đặt lịch hẹn!';
    }
}

$pageTitle = 'Đặt Lịch hẹn';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-plus"></i> Đặt Lịch hẹn</h2>
    <a href="appointments.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" id="appointmentForm">
            <div class="mb-3">
                <label class="form-label">Khách hàng <span class="text-danger">*</span></label>
                <select name="customer_id" id="customer_id" class="form-select" required 
                        onchange="loadPets(this.value)">
                    <option value="">-- Chọn khách hàng --</option>
                    <?php foreach($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>">
                            <?php echo htmlspecialchars($customer['name'] . ' - ' . $customer['phone']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Thú cưng <span class="text-danger">*</span></label>
                <select name="pet_id" id="pet_id" class="form-select" required>
                    <option value="">-- Chọn khách hàng trước --</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Dịch vụ <span class="text-danger">*</span></label>
                <select name="service_id" class="form-select" required>
                    <option value="">-- Chọn dịch vụ --</option>
                    <?php foreach($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['price']; ?>">
                            <?php echo htmlspecialchars($service['name'] . ' - ' . number_format($service['price'], 0, ',', '.') . ' đ'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày và giờ <span class="text-danger">*</span></label>
                <input type="datetime-local" name="appointment_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="pending">Chờ xử lý</option>
                    <option value="in_progress">Đang thực hiện</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="canceled">Đã hủy</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ghi chú</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="appointments.php" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function loadPets(customerId) {
    if(!customerId) {
        document.getElementById('pet_id').innerHTML = '<option value="">-- Chọn khách hàng trước --</option>';
        return;
    }
    
    fetch('get_pets.php?customer_id=' + customerId)
        .then(response => response.json())
        .then(data => {
            let html = '<option value="">-- Chọn thú cưng --</option>';
            data.forEach(pet => {
                html += `<option value="${pet.id}">${pet.name} (${pet.type})</option>`;
            });
            document.getElementById('pet_id').innerHTML = html;
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layout.php';
?>

