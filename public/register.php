<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $role = 'user'; // Mặc định là user
    
    // Validation
    if(empty($username) || empty($password)){
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif(strlen($username) < 3){
        $error = 'Tên đăng nhập phải có ít nhất 3 ký tự!';
    } elseif(strlen($password) < 6){
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif($password !== $confirm_password){
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Kiểm tra username đã tồn tại chưa
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if($stmt->fetch()){
                $error = 'Tên đăng nhập đã tồn tại!';
            } else {
                // Tạo tài khoản mới
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
                if($stmt->execute([
                    'username' => $username,
                    'password' => $password, // Lưu plain text (nên hash trong production)
                    'role' => $role
                ])){
                    $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                    header("Location: login.php");
                    exit;
                } else {
                    $error = 'Có lỗi xảy ra khi đăng ký!';
                }
            }
        } catch (PDOException $e) {
            // Xử lý lỗi SQL cụ thể
            if(strpos($e->getMessage(), 'role') !== false){
                $error = 'Lỗi cấu hình database. Vui lòng chạy file fix_users_table.sql trong phpMyAdmin!';
            } else {
                $error = 'Lỗi: ' . $e->getMessage();
            }
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Pet Care Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <div class="register-card">
            <div class="register-visual">
                <div class="brand-pill">
                    <i class="bi bi-heart-pulse"></i>
                    Pet Care Hub
                </div>
                <h1>Trung tâm quản lý thú cưng hiện đại</h1>
                <p>Đặt lịch, quản lý khách hàng và sản phẩm chỉ với vài thao tác đơn giản.</p>
                <ul class="register-highlights">
                    <li><i class="bi bi-check-circle"></i> Đồng bộ lịch chăm sóc theo thời gian thực</li>
                    <li><i class="bi bi-check-circle"></i> Quản lý kho sản phẩm & dịch vụ tập trung</li>
                    <li><i class="bi bi-check-circle"></i> Bảo mật đa lớp và tự động sao lưu</li>
                </ul>
            </div>

            <div class="register-form-area">
                <div class="register-header">
                    <span class="eyebrow">Tạo tài khoản</span>
                    <h2>Chào mừng đến Pet Care</h2>
                    <p>Hoàn tất các thông tin bên dưới để bắt đầu quản lý chỉ trong 1 phút.</p>
                </div>
                
                <div class="register-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="register-form">
                        <div class="form-group">
                            <label for="username">
                                <i class="bi bi-person"></i> Tên đăng nhập <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control" 
                                   placeholder="Ví dụ: petcare.hanoi"
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                   required 
                                   autofocus>
                            <i class="bi bi-person input-icon"></i>
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="bi bi-lock"></i> Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Tối thiểu 6 ký tự, ưu tiên chữ & số"
                                   required>
                            <i class="bi bi-lock-fill input-icon"></i>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="bi bi-lock-fill"></i> Xác nhận mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-control" 
                                   placeholder="Nhập lại giống mật khẩu bên trên"
                                   required>
                            <i class="bi bi-lock-fill input-icon"></i>
                        </div>

                        <button type="submit" class="btn-register">
                            <i class="bi bi-person-plus"></i> Đăng ký ngay
                        </button>
                    </form>

                    <div class="login-link">
                        <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-left"></i> Quay lại trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

