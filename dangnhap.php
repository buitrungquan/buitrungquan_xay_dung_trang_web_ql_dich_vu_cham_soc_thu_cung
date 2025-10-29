<?php
session_start();
include("admincp/config/db_conection.php"); // đảm bảo file này đúng đường dẫn
$conn = getDbConnection();    // Hàm bạn đã viết

if (!$conn) {
    die("Lỗi kết nối CSDL");
}

if (isset($_POST['dangnhap'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Dùng đúng tên bảng và cột trong ảnh bạn gửi
    $sql = "SELECT * FROM username WHERE email='$email' AND password='$password' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Lỗi truy vấn: " . mysqli_error($conn));
    }

    $count = mysqli_num_rows($result);
    if ($count > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        echo "<script>alert('Đăng nhập thành công!'); window.location='index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Sai email hoặc mật khẩu!');</script>";
    }
}
?>

<!-- Giao diện form -->
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng nhập</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f2f2f2;
}
.container {
    width: 360px;
    margin: 80px auto;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 8px rgba(0,0,0,0.2);
}
input {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    width: 100%;
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #0056b3;
}
</style>
</head>
<body>
<div class="container">
    <h2>Đăng nhập tài khoản</h2>
    <form method="POST" action="">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Mật khẩu:</label>
        <input type="password" name="password" required>

        <button type="submit" name="dangnhap">Đăng nhập</button>
    </form>
</div>
</body>
</html>
