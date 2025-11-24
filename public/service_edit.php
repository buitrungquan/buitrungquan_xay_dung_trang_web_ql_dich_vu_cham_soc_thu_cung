<?php
session_start();
require_once __DIR__ . '/../app/controllers/ServiceController.php';
require_once __DIR__ . '/../config/database.php';

$serviceController = new ServiceController($conn);
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: services.php");
    exit;
}

$service = $serviceController->getById($id);
if(!$service){
    $_SESSION['error'] = 'Không tìm thấy dịch vụ!';
    header("Location: services.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $image = $service['image']; // Giữ ảnh cũ
    
    // Xử lý upload ảnh mới
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
        // Xóa ảnh cũ nếu có
        if($image && file_exists(__DIR__ . '/uploads/services/' . $image)){
            @unlink(__DIR__ . '/uploads/services/' . $image);
        }
        
        $uploadDir = __DIR__ . '/uploads/services/';
        if(!is_dir($uploadDir)){
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if(in_array(strtolower($fileExtension), $allowedExtensions)){
            $fileName = uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)){
                $image = $fileName;
            }
        }
    }
    
    $data = [
        'name' => $_POST['name'],
        'description' => $_POST['description'] ?? null,
        'price' => $_POST['price'],
        'duration' => $_POST['duration'] ? (int)$_POST['duration'] : null,
        'image' => $image
    ];
    
    if($serviceController->update($id, $data)){
        $_SESSION['success'] = 'Cập nhật dịch vụ thành công!';
        header("Location: services.php");
        exit;
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
    }
}

$pageTitle = 'Sửa Dịch vụ';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> Sửa Dịch vụ</h2>
    <a href="services.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($service['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" min="0" step="1000" 
                           value="<?php echo $service['price']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Thời gian ước tính (phút)</label>
                    <input type="number" name="duration" class="form-control" min="0" 
                           value="<?php echo $service['duration'] ?? ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Ảnh dịch vụ</label>
                <?php if($service['image']): ?>
                    <div class="mb-2">
                        <img src="uploads/services/<?php echo htmlspecialchars($service['image']); ?>" 
                             alt="Ảnh hiện tại" 
                             style="max-width: 300px; border-radius: 5px; display: block;">
                        <small class="text-muted">Ảnh hiện tại</small>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                <small class="text-muted">Chấp nhận: JPG, PNG, GIF (tối đa 5MB). Để trống nếu giữ ảnh cũ.</small>
                <div id="image_preview" class="mt-2" style="display: none;">
                    <img id="preview_img" src="" alt="Preview" style="max-width: 300px; border-radius: 5px;">
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="services.php" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Preview ảnh khi chọn
document.getElementById('image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_img').src = e.target.result;
            document.getElementById('image_preview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layout.php';
?>

