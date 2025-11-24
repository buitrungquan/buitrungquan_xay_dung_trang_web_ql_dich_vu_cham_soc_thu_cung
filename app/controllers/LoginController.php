<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';

class LoginController {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function login($username, $password, $remember = false){
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Ghi nhớ đăng nhập bằng cookie
            if($remember){
                // Tạo token ngẫu nhiên để bảo mật
                $token = bin2hex(random_bytes(32));
                $expire = time() + (30 * 24 * 60 * 60); // 30 ngày
                
                // Lưu token vào cookie
                setcookie('remember_token', $token, $expire, '/', '', false, true);
                setcookie('remember_username', $username, $expire, '/', '', false, true);
                
                // Lưu token vào database (nếu có bảng remember_tokens)
                // Hoặc có thể lưu vào session với thời gian hết hạn
                $_SESSION['remember_token'] = $token;
            }
            
            // Phân quyền: admin vào dashboard, user vào trang chủ
            if($user['role'] == 'admin'){
                header("Location: dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            // Đăng nhập thất bại
            $_SESSION['error'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
            header("Location: ../public/login.php");
            exit;
        }
    }
    
    public function checkRememberMe(){
        // Kiểm tra cookie remember me
        if(isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_username'])){
            $username = $_COOKIE['remember_username'];
            $token = $_COOKIE['remember_token'];
            
            // Kiểm tra token trong session
            if(isset($_SESSION['remember_token']) && $_SESSION['remember_token'] === $token){
                // Tự động đăng nhập
                $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($user){
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    return true;
                }
            }
        }
        return false;
    }

    public function logout(){
        // Xóa tất cả session
        $_SESSION = array();
        
        // Xóa session cookie nếu có
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Hủy session
        session_destroy();
        
        // Redirect về trang login
        header("Location: login.php");
        exit;
    }
}
