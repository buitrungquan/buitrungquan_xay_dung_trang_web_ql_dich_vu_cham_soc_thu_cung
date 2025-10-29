<div id="main">
    <?php include("slidebar/sliderbar.php") ?>

    <div class="maincontent">
        <?php 
        if(isset($_GET['quanly'])) {
            $tam = $_GET['quanly'];
        } else {
            $tam = '';
        }

        if($tam == 'danhmucsanpham') {
            include('main/danhmuc.php');
        }elseif($tam == 'giohang') {
            include('main/giohang.php');
        }elseif($tam == 'thamkham') {
            include('main/thamkham.php');
        }elseif($tam == 'lienhe') {
            include('main/lienhe.php');
        }
        else {
            include('main/index.php');
        }
        ?>
    </div>
    <div class="clear"></div>
</div>
