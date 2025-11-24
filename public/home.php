<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu đã đăng nhập, chuyển đến dashboard phù hợp
if(isset($_SESSION['user_id'])){
    if($_SESSION['role'] == 'admin'){
        header("Location: dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../config/database.php';

$featuredProducts = [];
try {
    $productController = new ProductController($conn);
    $featuredProducts = array_slice($productController->getAll('active'), 0, 4);
} catch (Exception $e) {
    $featuredProducts = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Care Management - Hệ thống quản lý dịch vụ chăm sóc thú cưng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .hero-section p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out;
        }
        
        .feature-card {
            padding: 40px 20px;
            text-align: center;
            border-radius: 15px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .cta-buttons {
            margin-top: 50px;
        }
        
        .btn-home {
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            margin: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .features-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .section-title p {
            font-size: 1.1rem;
            color: #666;
        }

        .product-section {
            padding: 80px 0;
        }

        .product-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }

        .product-image {
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .price-tag {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
        }

        .badge-category {
            background: rgba(102, 126, 234, 0.15);
            color: #4c51bf;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <i class="bi bi-heart-pulse" style="font-size: 5rem; margin-bottom: 30px; display: block; animation: pulse 2s infinite;"></i>
                    <h1>Pet Care Management</h1>
                    <p>Hệ thống quản lý dịch vụ chăm sóc thú cưng chuyên nghiệp</p>
                    <div class="cta-buttons">
                        <a href="login.php" class="btn btn-light btn-home">
                            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                        </a>
                        <a href="register.php" class="btn btn-outline-light btn-home">
                            <i class="bi bi-person-plus"></i> Đăng ký ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Showcase -->
    <section class="product-section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Sản phẩm nổi bật cho thú cưng</h2>
                <p>Bổ sung dinh dưỡng, phụ kiện và đồ chơi chất lượng cao</p>
            </div>

            <?php if(empty($featuredProducts)): ?>
                <div class="alert alert-warning text-center">
                    <i class="bi bi-info-circle"></i>
                    Hiện chưa có sản phẩm nào được hiển thị. Vui lòng quay lại sau nhé!
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($featuredProducts as $product): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="product-card h-100">
                                <?php if(!empty($product['image'])): ?>
                                    <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="product-image">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-light product-image">
                                        <i class="bi bi-basket2" style="font-size: 3rem; color: #b5b5b5;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="p-4 d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <?php if(!empty($product['category'])): ?>
                                            <span class="badge badge-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(!empty($product['description'])): ?>
                                        <p class="text-muted small flex-grow-1"><?php echo htmlspecialchars(mb_strimwidth($product['description'], 0, 80, '...')); ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="price-tag"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</span>
                                        <?php if((int)$product['stock'] > 0): ?>
                                            <small class="text-success">Còn hàng</small>
                                        <?php else: ?>
                                            <small class="text-danger">Hết hàng</small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo (int)$product['stock'] > 0 ? 'cart.php?add_product=' . $product['id'] : '#'; ?>"
                                       class="btn btn-primary w-100 mt-3 <?php echo (int)$product['stock'] <= 0 ? 'disabled' : ''; ?>">
                                        <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-5">
                    <a href="index.php#products" class="btn btn-outline-primary btn-lg">
                        Khám phá thêm sản phẩm
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-title">
                <h2>Tính năng nổi bật</h2>
                <p>Quản lý toàn diện dịch vụ chăm sóc thú cưng của bạn</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>Quản lý Khách hàng</h4>
                        <p>Quản lý thông tin khách hàng một cách dễ dàng và hiệu quả</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h4>Quản lý Thú cưng</h4>
                        <p>Theo dõi thông tin chi tiết của từng thú cưng, bao gồm ảnh và lịch sử</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <h4>Quản lý Dịch vụ</h4>
                        <p>Quản lý các dịch vụ như tắm, cắt tỉa, khám sức khỏe với giá cả rõ ràng</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h4>Lịch hẹn</h4>
                        <p>Đặt lịch hẹn và theo dõi trạng thái dịch vụ một cách trực quan</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h4>Hóa đơn</h4>
                        <p>Tạo và quản lý hóa đơn, theo dõi thanh toán tự động</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4>Thống kê</h4>
                        <p>Dashboard với các thống kê tổng quan về doanh thu và hoạt động</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2025 Pet Care Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    </style>
</body>
</html>

