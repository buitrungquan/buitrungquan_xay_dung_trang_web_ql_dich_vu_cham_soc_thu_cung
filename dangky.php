<?php 
include("pages/header.php"); 
include("admincp/config/db_conection.php"); 
?>

<div class="wrapper">
    <h2>Đăng ký tài khoản</h2>
    <form action="" method="POST">
        <input type="text" name="username" placeholder="Tên đăng nhập" required><br>
        <input type="password" name="password" placeholder="Mật khẩu" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="submit" name="dangky" value="Đăng ký">
    </form>

    <?php
    if(isset($_POST['dangky'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        $email = $_POST['email'];

        // Kiểm tra tài khoản tồn tại
        $check = "SELECT * FROM username WHERE username='$username'";
        $result = mysqli_query($mysqli, $check);
        if(mysqli_num_rows($result) > 0) {
            echo "<p style='color:red;'>Tên đăng nhập đã tồn tại!</p>";
        } else {
            $sql = "INSERT INTO username(username, password, email) VALUES('$username', '$password', '$email')";
            if(mysqli_query($mysqli, $sql)) {
                echo "<p style='color:green;'>Đăng ký thành công! <a href='dangnhap.php'>Đăng nhập ngay</a></p>";
            } else {
                echo "<p style='color:red;'>Lỗi khi đăng ký!</p>";
            }
        }
    }
    ?>
</div>

<?php include("pages/footer.php"); ?>
