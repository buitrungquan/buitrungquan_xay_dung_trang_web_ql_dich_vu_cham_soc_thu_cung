<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra remember me cookie
require_once __DIR__ . '/../app/controllers/LoginController.php';
require_once __DIR__ . '/../config/database.php';

$loginController = new LoginController($conn);
if($loginController->checkRememberMe()){
    // Đã tự động đăng nhập từ cookie
    if($_SESSION['role'] == 'admin'){
        header("Location: dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

// Kiểm tra đã đăng nhập chưa
if(isset($_SESSION['user_id'])){
    if($_SESSION['role'] == 'admin'){
        header("Location: dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

// Lấy username từ cookie nếu có
$savedUsername = $_COOKIE['remember_username'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Pet Care Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="auth-body">
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="auth-wrapper container">
        <div class="auth-showcase">
            <div class="brand-pill">
                <i class="bi bi-heart-pulse"></i>
                Pet Care Management
            </div>
            <h1>Chăm sóc thú cưng toàn diện trong một nền tảng</h1>
            <p>Đặt lịch, quản lý hồ sơ, theo dõi hóa đơn và bán sản phẩm dành riêng cho thú cưng của bạn.</p>
            <ul class="auth-feature-list">
                <li><i class="bi bi-check-circle-fill"></i> Xử lý lịch hẹn tức thì</li>
                <li><i class="bi bi-check-circle-fill"></i> Theo dõi dịch vụ và sản phẩm trong giỏ hàng</li>
                <li><i class="bi bi-check-circle-fill"></i> Bảo mật dữ liệu khách hàng</li>
            </ul>
            <div class="auth-stats">
                <div class="stat-card">
                    <strong>+2.5K</strong>
                    <span>Ca chăm sóc thành công</span>
                </div>
                <div class="stat-card">
                    <strong>24/7</strong>
                    <span>Giám sát sức khỏe thú cưng</span>
                </div>
            </div>
        </div>

        <div class="auth-card">
            <div class="auth-card-header">
                <h2>Chào mừng trở lại</h2>
                <p>Đăng nhập để quản lý dịch vụ và sản phẩm.</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="bi bi-person"></i> Tên đăng nhập
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Nhập tên đăng nhập"
                           value="<?php echo htmlspecialchars($savedUsername); ?>"
                           required 
                           autofocus>
                    <i class="bi bi-person input-icon"></i>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i> Mật khẩu
                    </label>
                    <div class="password-field">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Nhập mật khẩu"
                               required>
                        <button type="button" class="toggle-password" aria-label="Hiển thị mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                        <label class="form-check-label" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                    <a class="forgot-link" href="mailto:support@petcare.vn">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                </button>
            </form>

            <div class="register-link">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                <a href="index.php" class="btn btn-outline-secondary mt-3 w-100">
                    <i class="bi bi-arrow-left"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password');
        if(toggleBtn && passwordInput){
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleBtn.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            });
        }
    </script>
</body>
</html>
