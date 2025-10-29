<?php
session_start();
?>

<div class="menu">
    <ul class="list_menu">
        <li><a href="index.php">Trang chủ</a></li>
        <li><a href="index.php?quanly=danhmucsanpham">Sản phẩm</a></li>
        <li><a href="index.php?quanly=giohang">Giỏ hàng</a></li>
        <li><a href="index.php?quanly=lienhe">Liên hệ</a></li>

        <?php if (isset($_SESSION['username'])): ?>
            <li style="float:right; color:#fff; font-weight:bold; margin-right:15px;">
                👋 Xin chào, <?php echo $_SESSION['username']; ?>
            </li>
            <li style="float:right;"><a href="dangxuat.php">Đăng xuất</a></li>
        <?php else: ?>
            <li style="float:right;"><a href="dangky.php">Đăng ký</a></li>
            <li style="float:right;"><a href="dangnhap.php">Đăng nhập</a></li>
        <?php endif; ?>
    </ul>
</div>
