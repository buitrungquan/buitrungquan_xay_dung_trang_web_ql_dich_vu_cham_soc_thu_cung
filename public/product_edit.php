<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../config/database.php';

$productController = new ProductController($conn);
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: products.php");
    exit;
}

$product = $productController->getById($id);
if(!$product){
    $_SESSION['error'] = 'Không tìm thấy sản phẩm!';
    header("Location: products.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $image = $product['image']; // Giữ ảnh cũ
    $uploadError = null;
    
    // Xử lý upload ảnh mới
    if(isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE){
        // Kiểm tra lỗi upload
        if($_FILES['image']['error'] !== UPLOAD_ERR_OK){
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File vượt quá kích thước tối đa cho phép (upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File vượt quá kích thước tối đa trong form (MAX_FILE_SIZE)',
                UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
                UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm để lưu file',
                UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file vào đĩa',
                UPLOAD_ERR_EXTENSION => 'Upload bị dừng bởi extension PHP'
            ];
            $uploadError = $errorMessages[$_FILES['image']['error']] ?? 'Lỗi upload không xác định';
        } else {
            // Kiểm tra kích thước file (5MB = 5 * 1024 * 1024 bytes)
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            if($_FILES['image']['size'] > $maxFileSize){
                $uploadError = 'File quá lớn! Kích thước tối đa là 5MB.';
            } else {
                $uploadDir = __DIR__ . '/uploads/products/';
                if(!is_dir($uploadDir)){
                    if(!mkdir($uploadDir, 0777, true)){
                        $uploadError = 'Không thể tạo thư mục upload!';
                    }
                }
                
                if(!$uploadError){
                    $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if(in_array($fileExtension, $allowedExtensions)){
                        // Kiểm tra MIME type để bảo mật hơn
                        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                        $fileMimeType = mime_content_type($_FILES['image']['tmp_name']);
                        
                        if(in_array($fileMimeType, $allowedMimeTypes)){
                            // Xóa ảnh cũ nếu có
                            if($image && file_exists(__DIR__ . '/uploads/products/' . $image)){
                                @unlink(__DIR__ . '/uploads/products/' . $image);
                            }
                            
                            $fileName = uniqid('product_', true) . '.' . $fileExtension;
                            $targetPath = $uploadDir . $fileName;
                            
                            if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)){
                                $image = $fileName;
                            } else {
                                $uploadError = 'Không thể di chuyển file đã upload!';
                            }
                        } else {
                            $uploadError = 'Định dạng file không hợp lệ! Chỉ chấp nhận: JPG, PNG, GIF, WEBP';
                        }
                    } else {
                        $uploadError = 'Định dạng file không được phép! Chỉ chấp nhận: JPG, PNG, GIF, WEBP';
                    }
                }
            }
        }
        
        if($uploadError){
            $_SESSION['error'] = 'Lỗi upload ảnh: ' . $uploadError;
        }
    }
    
    // Chỉ cập nhật sản phẩm nếu không có lỗi upload
    if(!$uploadError){
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? null,
            'price' => $_POST['price'],
            'category' => $_POST['category'] ?? null,
            'stock' => $_POST['stock'] ? (int)$_POST['stock'] : 0,
            'image' => $image,
            'status' => $_POST['status'] ?? 'active'
        ];
        
        if($productController->update($id, $data)){
            $_SESSION['success'] = 'Cập nhật sản phẩm thành công!';
            header("Location: products.php");
            exit;
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
        }
    }
}

$pageTitle = 'Sửa Sản phẩm';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> Sửa Sản phẩm</h2>
    <a href="products.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" min="0" step="1000" 
                           value="<?php echo $product['price']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Danh mục</label>
                    <select name="category" class="form-select">
                        <option value="">-- Chọn --</option>
                        <option value="food" <?php echo $product['category'] == 'food' ? 'selected' : ''; ?>>Thức ăn</option>
                        <option value="toy" <?php echo $product['category'] == 'toy' ? 'selected' : ''; ?>>Đồ chơi</option>
                        <option value="accessory" <?php echo $product['category'] == 'accessory' ? 'selected' : ''; ?>>Phụ kiện</option>
                        <option value="medicine" <?php echo $product['category'] == 'medicine' ? 'selected' : ''; ?>>Thuốc</option>
                        <option value="other" <?php echo $product['category'] == 'other' ? 'selected' : ''; ?>>Khác</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Số lượng tồn kho</label>
                    <input type="number" name="stock" class="form-control" min="0" value="<?php echo $product['stock']; ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                        <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Ngừng bán</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ảnh sản phẩm</label>
                    <?php if($product['image']): ?>
                        <div class="mb-2">
                            <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="Ảnh hiện tại" 
                                 style="max-width: 300px; border-radius: 5px; display: block;">
                            <small class="text-muted">Ảnh hiện tại</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small class="text-muted">Chấp nhận: JPG, PNG, GIF, WEBP (tối đa 5MB). Để trống nếu giữ ảnh cũ.</small>
                    <div id="image_preview" class="mt-2" style="display: none;">
                        <img id="preview_img" src="" alt="Preview" style="max-width: 300px; border-radius: 5px;">
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="products.php" class="btn btn-secondary">Hủy</a>
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

