<?php
session_start();
require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../config/database.php';

$petController = new PetController($conn);
$customerController = new CustomerController($conn);
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: pets.php");
    exit;
}

$pet = $petController->getById($id);
if(!$pet){
    $_SESSION['error'] = 'Không tìm thấy thú cưng!';
    header("Location: pets.php");
    exit;
}

$customers = $customerController->getAll();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $photo = $pet['photo']; // Giữ ảnh cũ
    
    // Xử lý upload ảnh mới
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK){
        // Xóa ảnh cũ nếu có
        if($photo && file_exists(__DIR__ . '/uploads/' . $photo)){
            unlink(__DIR__ . '/uploads/' . $photo);
        }
        
        $uploadDir = __DIR__ . '/uploads/';
        if(!is_dir($uploadDir)){
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)){
            $photo = $fileName;
        }
    }
    
    $data = [
        'customer_id' => $_POST['customer_id'],
        'name' => $_POST['name'],
        'type' => $_POST['type'],
        'breed' => $_POST['breed'] ?? null,
        'age' => $_POST['age'] ? (int)$_POST['age'] : null,
        'gender' => $_POST['gender'] ?? null,
        'photo' => $photo,
        'notes' => $_POST['notes'] ?? null
    ];
    
    if($petController->update($id, $data)){
        $_SESSION['success'] = 'Cập nhật thú cưng thành công!';
        header("Location: pets.php");
        exit;
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
    }
}

$pageTitle = 'Sửa Thú cưng';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> Sửa Thú cưng</h2>
    <a href="pets.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if($pet['photo']): ?>
            <div class="mb-3">
                <label class="form-label">Ảnh hiện tại</label><br>
                <img src="uploads/<?php echo htmlspecialchars($pet['photo']); ?>" 
                     alt="<?php echo htmlspecialchars($pet['name']); ?>" 
                     style="max-width: 200px; border-radius: 5px;">
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Chủ sở hữu <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- Chọn khách hàng --</option>
                    <?php foreach($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>" 
                                <?php echo $customer['id'] == $pet['customer_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['name'] . ' - ' . $customer['phone']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tên thú cưng <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loại <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="dog" <?php echo $pet['type'] == 'dog' ? 'selected' : ''; ?>>Chó</option>
                        <option value="cat" <?php echo $pet['type'] == 'cat' ? 'selected' : ''; ?>>Mèo</option>
                        <option value="bird" <?php echo $pet['type'] == 'bird' ? 'selected' : ''; ?>>Chim</option>
                        <option value="rabbit" <?php echo $pet['type'] == 'rabbit' ? 'selected' : ''; ?>>Thỏ</option>
                        <option value="other" <?php echo $pet['type'] == 'other' ? 'selected' : ''; ?>>Khác</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giống</label>
                    <input type="text" name="breed" class="form-control" value="<?php echo htmlspecialchars($pet['breed'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tuổi</label>
                    <input type="number" name="age" class="form-control" min="0" value="<?php echo $pet['age'] ?? ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giới tính</label>
                    <select name="gender" class="form-select">
                        <option value="">-- Chọn --</option>
                        <option value="male" <?php echo $pet['gender'] == 'male' ? 'selected' : ''; ?>>Đực</option>
                        <option value="female" <?php echo $pet['gender'] == 'female' ? 'selected' : ''; ?>>Cái</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Ảnh mới (để trống nếu giữ ảnh cũ)</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Ghi chú</label>
                <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($pet['notes'] ?? ''); ?></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="pets.php" class="btn btn-secondary">Hủy</a>
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

