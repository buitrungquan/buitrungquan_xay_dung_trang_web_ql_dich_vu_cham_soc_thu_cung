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

require_once __DIR__ . '/../app/controllers/ServiceController.php';
require_once __DIR__ . '/../config/database.php';

try {
    $serviceController = new ServiceController($conn);
    $services = $serviceController->getAll();
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    $services = [];
}

$servicePlaceholder = 'assets/images/service-placeholder.svg';

$pageTitle = 'Quản lý Dịch vụ';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bag-check"></i> Quản lý Dịch vụ</h2>
    <a href="service_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Thêm dịch vụ
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên dịch vụ</th>
                            <th>Mô tả</th>
                            <th>Giá</th>
                            <th>Thời gian (phút)</th>
                            <th>Thao tác</th>
                        </tr>
                </thead>
                <tbody>
                    <?php if(empty($services)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Chưa có dịch vụ nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($services as $service): ?>
                            <tr>
                                <td><?php echo $service['id']; ?></td>
                                <td>
                                    <?php 
                                        $imageSrc = !empty($service['image']) 
                                            ? 'uploads/services/' . $service['image'] 
                                            : $servicePlaceholder;
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imageSrc); ?>" 
                                         alt="<?php echo htmlspecialchars($service['name']); ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                </td>
                                <td><strong><?php echo htmlspecialchars($service['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($service['description'] ?? '-'); ?></td>
                                <td><strong class="text-success"><?php echo number_format($service['price'], 0, ',', '.'); ?> đ</strong></td>
                                <td><?php echo $service['duration'] ? $service['duration'] . ' phút' : '-'; ?></td>
                                <td>
                                    <a href="service_edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="service_delete.php?id=<?php echo $service['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn có chắc muốn xóa dịch vụ này?');">
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

