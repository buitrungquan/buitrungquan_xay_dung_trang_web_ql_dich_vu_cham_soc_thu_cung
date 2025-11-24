<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    $_SESSION['error'] = 'Bạn không có quyền truy cập trang này!';
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../config/database.php';

$search = trim($_GET['search'] ?? '');

try {
    $productController = new ProductController($conn);
    $products = $productController->getAll(null, $search ?: null);
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    $products = [];
}

$pageTitle = 'Quản lý Sản phẩm';
ob_start();
?>

<div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-box-seam"></i> Quản lý Sản phẩm</h2>
        <p class="text-muted mb-0">Tìm kiếm theo tên hoặc danh mục để quản lý nhanh chóng</p>
    </div>
    <a href="product_add.php" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle"></i> Thêm sản phẩm
    </a>
</div>

<div class="card mb-4 border-0 shadow-sm product-toolbar">
    <div class="card-body">
        <form class="row g-3 align-items-center" method="GET">
            <div class="col-lg-8">
                <label for="search" class="form-label text-muted mb-1">Tìm kiếm sản phẩm</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-primary border-2"><i class="bi bi-search"></i></span>
                    <input type="text"
                           name="search"
                           id="search"
                           class="form-control border-2"
                           placeholder="Nhập tên sản phẩm, danh mục hoặc từ khóa liên quan..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-lg-4 d-flex flex-column flex-sm-row flex-lg-column align-items-stretch gap-2">
                <?php if($search): ?>
                    <a href="products.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Xóa bộ lọc
                    </a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Tìm kiếm
                </button>
            </div>
        </form>
        <div class="mt-3 text-muted small">
            <?php if($search): ?>
                Đang hiển thị <?php echo count($products); ?> kết quả cho "<strong><?php echo htmlspecialchars($search); ?></strong>"
            <?php else: ?>
                Hiển thị <?php echo count($products); ?> sản phẩm
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Chưa có sản phẩm nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <?php if($product['image']): ?>
                                        <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <?php if($product['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($product['description'], 0, 50)) . '...'; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($product['category']): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong class="text-success"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</strong></td>
                                <td>
                                    <?php if($product['stock'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $product['stock']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Hết hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($product['status'] == 'active'): ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ngừng bán</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="product_delete.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layout.php';
?>

