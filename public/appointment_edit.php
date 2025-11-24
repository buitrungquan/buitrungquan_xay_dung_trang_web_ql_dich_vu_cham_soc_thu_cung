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
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: appointments.php");
    exit;
}

$appointment = $appointmentController->getById($id);
if(!$appointment){
    $_SESSION['error'] = 'Không tìm thấy lịch hẹn!';
    header("Location: appointments.php");
    exit;
}

$customers = $customerController->getAll();
$pets = $petController->getByCustomerId($appointment['customer_id']);
$services = $serviceController->getAll();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = [
        'customer_id' => $_POST['customer_id'],
        'pet_id' => $_POST['pet_id'],
        'service_id' => $_POST['service_id'],
        'appointment_date' => $_POST['appointment_date'],
        'status' => $_POST['status'],
        'notes' => $_POST['notes'] ?? null
    ];
    
    if($appointmentController->update($id, $data)){
        $_SESSION['success'] = 'Cập nhật lịch hẹn thành công!';
        header("Location: appointments.php");
        exit;
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
    }
}

$pageTitle = 'Sửa Lịch hẹn';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> Sửa Lịch hẹn</h2>
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
                        <option value="<?php echo $customer['id']; ?>" 
                                <?php echo $customer['id'] == $appointment['customer_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['name'] . ' - ' . $customer['phone']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Thú cưng <span class="text-danger">*</span></label>
                <select name="pet_id" id="pet_id" class="form-select" required>
                    <?php foreach($pets as $pet): ?>
                        <option value="<?php echo $pet['id']; ?>" 
                                <?php echo $pet['id'] == $appointment['pet_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pet['name'] . ' (' . $pet['type'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Dịch vụ <span class="text-danger">*</span></label>
                <select name="service_id" class="form-select" required>
                    <option value="">-- Chọn dịch vụ --</option>
                    <?php foreach($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>" 
                                <?php echo $service['id'] == $appointment['service_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($service['name'] . ' - ' . number_format($service['price'], 0, ',', '.') . ' đ'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày và giờ <span class="text-danger">*</span></label>
                <input type="datetime-local" name="appointment_date" class="form-control" 
                       value="<?php echo date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="pending" <?php echo $appointment['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                    <option value="in_progress" <?php echo $appointment['status'] == 'in_progress' ? 'selected' : ''; ?>>Đang thực hiện</option>
                    <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="canceled" <?php echo $appointment['status'] == 'canceled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ghi chú</label>
                <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($appointment['notes'] ?? ''); ?></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="appointments.php" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Cập nhật
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

