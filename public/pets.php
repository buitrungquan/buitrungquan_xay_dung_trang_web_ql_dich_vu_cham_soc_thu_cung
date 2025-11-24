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

require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../config/database.php';

try {
    $petController = new PetController($conn);
    $pets = $petController->getAll();
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    $pets = [];
}

$pageTitle = 'Quản lý Thú cưng';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-heart"></i> Quản lý Thú cưng</h2>
    <a href="pet_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Thêm thú cưng
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
                        <th>Tên</th>
                        <th>Loại</th>
                        <th>Giống</th>
                        <th>Tuổi</th>
                        <th>Giới tính</th>
                        <th>Chủ sở hữu</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pets)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Chưa có thú cưng nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($pets as $pet): ?>
                            <tr>
                                <td><?php echo $pet['id']; ?></td>
                                <td>
                                    <?php if($pet['photo']): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($pet['photo']); ?>" 
                                             alt="<?php echo htmlspecialchars($pet['name']); ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($pet['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pet['type']); ?></td>
                                <td><?php echo htmlspecialchars($pet['breed'] ?? '-'); ?></td>
                                <td><?php echo $pet['age'] ? $pet['age'] . ' tuổi' : '-'; ?></td>
                                <td><?php echo htmlspecialchars($pet['gender'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($pet['customer_name']); ?></td>
                                <td>
                                    <a href="pet_edit.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="pet_delete.php?id=<?php echo $pet['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn có chắc muốn xóa thú cưng này?');">
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

