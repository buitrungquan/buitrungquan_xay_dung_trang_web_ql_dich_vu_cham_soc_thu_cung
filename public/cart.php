<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../config/database.php';

$productController = new ProductController($conn);

// Khởi tạo giỏ hàng nếu chưa có
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

// Dịch vụ không thể thêm vào giỏ hàng, chỉ có thể đặt lịch trực tiếp

// Xử lý thêm sản phẩm vào giỏ hàng
if(isset($_GET['add_product']) && $_GET['add_product']){
    $productId = (int)$_GET['add_product'];
    $product = $productController->getById($productId);
    
    if($product && $product['stock'] > 0){
        // Kiểm tra đã có trong giỏ chưa
        $exists = false;
        foreach($_SESSION['cart'] as $key => $item){
            if($item['id'] == $productId && $item['type'] == 'product'){
                $_SESSION['cart'][$key]['quantity']++;
                $exists = true;
                break;
            }
        }
        
        if(!$exists){
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'type' => 'product',
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
        }
        $_SESSION['success'] = 'Đã thêm vào giỏ hàng!';
    } else {
        $_SESSION['error'] = 'Sản phẩm hết hàng!';
    }
    header("Location: cart.php");
    exit;
}

// Xử lý xóa khỏi giỏ hàng
if(isset($_GET['remove']) && isset($_GET['type'])){
    $itemId = (int)$_GET['remove'];
    $itemType = $_GET['type'];
    foreach($_SESSION['cart'] as $key => $item){
        if($item['id'] == $itemId && $item['type'] == $itemType){
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
            $_SESSION['success'] = 'Đã xóa khỏi giỏ hàng!';
            break;
        }
    }
    header("Location: cart.php");
    exit;
}

// Xử lý cập nhật số lượng
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])){
    if(isset($_POST['quantity']) && is_array($_POST['quantity'])){
        foreach($_POST['quantity'] as $key => $quantity){
            $quantity = (int)$quantity;
            if(isset($_SESSION['cart'][$key])){
                // Kiểm tra stock nếu là sản phẩm
                if($_SESSION['cart'][$key]['type'] == 'product'){
                    $product = $productController->getById($_SESSION['cart'][$key]['id']);
                    if($product && $quantity > $product['stock']){
                        $_SESSION['error'] = 'Số lượng sản phẩm "' . htmlspecialchars($product['name']) . '" vượt quá tồn kho (' . $product['stock'] . ')';
                        $quantity = min($quantity, $product['stock']);
                    }
                }
                
                if($quantity > 0){
                    $_SESSION['cart'][$key]['quantity'] = $quantity;
                } else {
                    unset($_SESSION['cart'][$key]);
                }
            }
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
    if(!isset($_SESSION['error'])){
        $_SESSION['success'] = 'Đã cập nhật giỏ hàng!';
    }
    header("Location: cart.php");
    exit;
}

// Tính tổng tiền - đảm bảo tính đúng
$total = 0;
foreach($_SESSION['cart'] as $item){
    $itemPrice = (float)$item['price'];
    $itemQuantity = (int)$item['quantity'];
    $itemSubtotal = $itemPrice * $itemQuantity;
    $total += $itemSubtotal;
}

$pageTitle = 'Giỏ hàng';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Pet Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-heart-pulse text-primary"></i> Pet Care
            </a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                </a>
                <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'user'): ?>
                    <a href="customer_appointments.php" class="btn btn-outline-secondary">Tài khoản</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4"><i class="bi bi-cart"></i> Giỏ hàng của bạn</h2>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(empty($_SESSION['cart'])): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">Giỏ hàng trống</h4>
                    <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng!</p>
                    <a href="index.php" class="btn btn-primary mt-3">
                        <i class="bi bi-arrow-left"></i> Quay lại trang chủ
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm/Dịch vụ</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($_SESSION['cart'] as $key => $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                <br><small class="text-muted">
                                                    <?php echo $item['type'] == 'product' ? 'Sản phẩm' : 'Dịch vụ'; ?>
                                                </small>
                                            </td>
                                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?> đ</td>
                                            <td>
                                                <?php 
                                                $maxQuantity = 999;
                                                if($item['type'] == 'product'){
                                                    $product = $productController->getById($item['id']);
                                                    if($product){
                                                        $maxQuantity = $product['stock'];
                                                    }
                                                }
                                                ?>
                                                <input type="number" 
                                                       name="quantity[<?php echo $key; ?>]" 
                                                       id="quantity_<?php echo $key; ?>"
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" 
                                                       max="<?php echo $maxQuantity; ?>"
                                                       class="form-control quantity-input" 
                                                       style="width: 80px;"
                                                       data-price="<?php echo $item['price']; ?>"
                                                       data-key="<?php echo $key; ?>"
                                                       title="<?php echo $item['type'] == 'product' ? 'Tồn kho: ' . $maxQuantity : ''; ?>">
                                                <?php if($item['type'] == 'product' && $maxQuantity < 10): ?>
                                                    <small class="text-muted d-block">Còn: <?php echo $maxQuantity; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong class="text-success subtotal" 
                                                        id="subtotal_<?php echo $key; ?>"
                                                        data-price="<?php echo $item['price']; ?>"
                                                        data-quantity="<?php echo $item['quantity']; ?>">
                                                    <?php 
                                                    $subtotal = (float)$item['price'] * (int)$item['quantity'];
                                                    echo number_format($subtotal, 0, ',', '.'); 
                                                    ?> đ
                                                </strong>
                                            </td>
                                            <td>
                                                <a href="cart.php?remove=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Xóa khỏi giỏ hàng?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                        <td><strong class="text-success" id="cart-total" style="font-size: 1.2rem;">
                                            <?php echo number_format($total, 0, ',', '.'); ?> đ
                                        </strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" name="update_cart" class="btn btn-warning">
                        <i class="bi bi-arrow-clockwise"></i> Cập nhật giỏ hàng
                    </button>
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'user'): ?>
                        <a href="checkout.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-credit-card"></i> Thanh toán
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập để thanh toán
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tính toán giỏ hàng tự động với JavaScript
        (function() {
            // Hàm format số tiền
            function formatPrice(price) {
                return new Intl.NumberFormat('vi-VN').format(Math.round(price)) + ' đ';
            }

            // Hàm tính tổng cộng
            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.subtotal').forEach(function(subtotalEl) {
                    const price = parseFloat(subtotalEl.getAttribute('data-price'));
                    const quantity = parseInt(subtotalEl.getAttribute('data-quantity'));
                    total += price * quantity;
                });
                
                const totalEl = document.getElementById('cart-total');
                if(totalEl) {
                    totalEl.textContent = formatPrice(total);
                }
                
                return total;
            }

            // Hàm cập nhật thành tiền cho một item
            function updateSubtotal(input) {
                const key = input.getAttribute('data-key');
                const price = parseFloat(input.getAttribute('data-price'));
                const quantity = parseInt(input.value) || 0;
                
                // Validate số lượng
                const max = parseInt(input.getAttribute('max'));
                if(quantity > max) {
                    input.value = max;
                    quantity = max;
                    alert('Số lượng không được vượt quá tồn kho (' + max + ')');
                }
                
                if(quantity < 1) {
                    input.value = 1;
                    quantity = 1;
                }
                
                // Cập nhật thành tiền
                const subtotalEl = document.getElementById('subtotal_' + key);
                if(subtotalEl) {
                    const subtotal = price * quantity;
                    subtotalEl.textContent = formatPrice(subtotal);
                    subtotalEl.setAttribute('data-quantity', quantity);
                }
                
                // Tính lại tổng cộng
                calculateTotal();
            }

            // Lắng nghe sự kiện thay đổi số lượng
            document.querySelectorAll('.quantity-input').forEach(function(input) {
                // Khi thay đổi số lượng
                input.addEventListener('input', function() {
                    updateSubtotal(this);
                });
                
                // Khi blur (rời khỏi ô input)
                input.addEventListener('blur', function() {
                    const quantity = parseInt(this.value) || 1;
                    if(quantity < 1) {
                        this.value = 1;
                        updateSubtotal(this);
                    }
                });
                
                // Khi nhấn phím
                input.addEventListener('keyup', function(e) {
                    if(e.key === 'Enter') {
                        this.blur();
                    }
                });
            });

            // Tính tổng ban đầu khi trang load
            calculateTotal();

            // Thêm hiệu ứng khi thay đổi số lượng
            document.querySelectorAll('.quantity-input').forEach(function(input) {
                input.addEventListener('change', function() {
                    const subtotalEl = document.getElementById('subtotal_' + this.getAttribute('data-key'));
                    if(subtotalEl) {
                        subtotalEl.style.transition = 'all 0.3s ease';
                        subtotalEl.style.transform = 'scale(1.1)';
                        setTimeout(function() {
                            subtotalEl.style.transform = 'scale(1)';
                        }, 300);
                    }
                });
            });

            // Hiển thị thông báo khi cập nhật
            const form = document.querySelector('form[method="POST"]');
            if(form) {
                form.addEventListener('submit', function(e) {
                    // Kiểm tra lại tất cả số lượng trước khi submit
                    let hasError = false;
                    document.querySelectorAll('.quantity-input').forEach(function(input) {
                        const quantity = parseInt(input.value) || 0;
                        const max = parseInt(input.getAttribute('max'));
                        
                        if(quantity < 1) {
                            input.value = 1;
                            updateSubtotal(input);
                        }
                        
                        if(quantity > max) {
                            input.value = max;
                            updateSubtotal(input);
                            hasError = true;
                        }
                    });
                    
                    if(hasError) {
                        e.preventDefault();
                        alert('Đã điều chỉnh số lượng theo tồn kho. Vui lòng kiểm tra lại!');
                        return false;
                    }
                });
            }
        })();
    </script>
</body>
</html>

