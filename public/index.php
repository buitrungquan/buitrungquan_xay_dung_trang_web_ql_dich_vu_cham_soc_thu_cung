<?php
/**
 * Trang chủ - Trang trưng bày dịch vụ cho khách hàng
 */
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/controllers/ServiceController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../config/database.php';

$serviceController = new ServiceController($conn);
$productController = new ProductController($conn);
$services = [];
$products = [];
$productsPerPage = 8;
$servicesPerPage = 6;
$currentProductPage = isset($_GET['product_page']) ? max(1, (int)$_GET['product_page']) : 1;
$currentServicePage = isset($_GET['service_page']) ? max(1, (int)$_GET['service_page']) : 1;
$productSearch = trim($_GET['product_search'] ?? '');
$totalProducts = 0;
$totalProductPages = 1;
$productOffset = 0;
$totalServices = 0;
$totalServicePages = 1;
$serviceOffset = 0;
try {
    if($productSearch){
        $products = $productController->getAll('active', $productSearch);
        $totalProducts = count($products);
        $totalProductPages = 1;
    } else {
        $totalProducts = $productController->countAll('active');
        $totalProductPages = max(1, (int)ceil($totalProducts / $productsPerPage));
        if($currentProductPage > $totalProductPages){
            $currentProductPage = $totalProductPages;
        }
        $productOffset = ($currentProductPage - 1) * $productsPerPage;
        $products = $productController->getPaginated('active', $productsPerPage, $productOffset);
    }

    $totalServices = $serviceController->countAll();
    $totalServicePages = max(1, (int)ceil($totalServices / $servicesPerPage));
    if($currentServicePage > $totalServicePages){
        $currentServicePage = $totalServicePages;
    }
    $serviceOffset = ($currentServicePage - 1) * $servicesPerPage;
    $services = $serviceController->getPaginated($servicesPerPage, $serviceOffset);
} catch (Exception $e) {
    // Nếu chưa có dữ liệu, để mảng rỗng
}

$pageTitle = 'Trang chủ - Pet Care';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --accent: #ffd166;
            --muted: #4a4e69;
            --bg-soft: #f9f7ff;
            --card-radius: 22px;
        }

        body {
            background: var(--bg-soft);
        }

        .navbar-custom {
            background: #fff;
            box-shadow: 0 8px 30px rgba(108, 99, 255, 0.15);
        }

        .hero-section {
            background: radial-gradient(circle at top left, #d8dcff 0%, #f7ecff 40%, #fefefe 100%);
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before,
        .hero-section::after {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            z-index: 0;
        }
        .hero-section::before {
            top: -120px;
            left: -60px;
        }
        .hero-section::after {
            bottom: -140px;
            right: -40px;
        }

        .hero-section .display-4 {
            color: #1f1f3d;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-heading .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(102, 126, 234, 0.15);
            color: var(--primary);
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 999px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.8rem;
        }

        .section-heading h2 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .section-heading p {
            color: var(--muted);
            max-width: 640px;
            margin: 0 auto;
        }

        .collection-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 209, 102, 0.2);
            color: #c27803;
            padding: 6px 14px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .product-section,
        .services-section {
            padding: 90px 0;
            position: relative;
        }

        .product-section {
            background: #fff;
        }

        .services-section {
            background: linear-gradient(180deg, #fef8ff 0%, #f2f0ff 100%);
        }

        .product-card,
        .service-card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            background: #fff;
        }

        .product-card:hover,
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 35px 70px rgba(15, 23, 42, 0.12);
        }

        .product-card img,
        .service-card img {
            height: 230px;
            object-fit: cover;
            width: 100%;
        }

        .card-body-soft {
            padding: 24px;
        }

        .card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .inventory-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
        }

        .inventory-pill.in-stock {
            background: rgba(56, 161, 105, 0.12);
            color: #2f855a;
        }

        .inventory-pill.out-stock {
            background: rgba(229, 62, 62, 0.12);
            color: #c53030;
        }

        .price-tag {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a6b3d;
        }

        .btn-soft {
            border-radius: 999px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .btn-gradient {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: #fff;
            border: none;
        }

        .btn-gradient:hover {
            color: #fff;
            filter: brightness(1.05);
        }

        .service-features {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0;
        }

        .service-features span {
            background: rgba(102, 126, 234, 0.15);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .pagination .page-link {
            border-radius: 12px;
            margin: 0 4px;
            color: var(--primary);
            border: none;
            min-width: 44px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.35);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-heart-pulse text-primary"></i> Pet Care
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Dịch vụ</a>
                    </li>
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'user'): ?>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="cart.php">
                                <i class="bi bi-cart"></i> Giỏ hàng
                                <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                        <?php echo count($_SESSION['cart']); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customer_appointments.php">Lịch hẹn của tôi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customer_profile.php">Tài khoản</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Đăng xuất</a>
                        </li>
                    <?php elseif(isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Quản lý</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Đăng xuất</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <i class="bi bi-heart-pulse" style="font-size: 5rem; margin-bottom: 30px; display: block;"></i>
                    <h1 class="display-4 fw-bold mb-4">Chăm sóc thú cưng chuyên nghiệp</h1>
                    <p class="lead mb-4">Dịch vụ tắm, cắt tỉa, khám sức khỏe và nhiều hơn nữa cho thú cưng yêu quý của bạn</p>
                    <a href="#services" class="btn btn-light btn-lg">
                        <i class="bi bi-arrow-down"></i> Xem dịch vụ
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <?php if(!empty($products) || $productSearch): ?>
    <section id="products" class="product-section">
        <div class="container">
            <div class="section-heading">
                <div class="eyebrow">
                    <i class="bi bi-stars"></i> Bộ sưu tập mới
                </div>
                <h2>Sản phẩm cho thú cưng</h2>
                <p>Thức ăn, phụ kiện, đồ chơi và dinh dưỡng được tuyển chọn dành cho bé cưng của bạn.</p>
            </div>
            
            <!-- Search Form -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <form method="GET" id="productSearchForm" class="row g-3 align-items-end" onsubmit="return handleSearch(event);">
                        <div class="col-md-8">
                            <label for="product_search" class="form-label text-muted mb-1">Tìm kiếm sản phẩm</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-primary border-2"><i class="bi bi-search"></i></span>
                                <input type="text"
                                       name="product_search"
                                       id="product_search"
                                       class="form-control border-2"
                                       placeholder="Nhập tên sản phẩm, danh mục hoặc từ khóa..."
                                       value="<?php echo htmlspecialchars($productSearch); ?>"
                                       autocomplete="off">
                                <input type="hidden" name="product_page" value="1">
                                <input type="hidden" name="service_page" value="<?php echo $currentServicePage; ?>">
                            </div>
                            <div id="searchSuggestions" class="position-relative" style="display: none; z-index: 1000;">
                                <div class="card shadow-lg border-0 position-absolute w-100 mt-1" style="max-width: 600px;">
                                    <div class="card-body p-0">
                                        <div id="suggestionsList" class="list-group list-group-flush"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <?php if($productSearch): ?>
                                <a href="?product_page=1&service_page=<?php echo $currentServicePage; ?>#products" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Xóa
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </form>
                    <?php if($productSearch): ?>
                        <div class="mt-2 text-muted small">
                            Tìm thấy <strong><?php echo count($products); ?></strong> kết quả cho "<strong><?php echo htmlspecialchars($productSearch); ?></strong>"
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach($products as $product): ?>
                    <div class="col-sm-6 col-lg-3">
                        <div class="product-card h-100">
                            <?php if($product['image']): ?>
                                <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <img src="assets/images/service-placeholder.svg" class="card-img-top" alt="Pet product placeholder" loading="lazy">
                            <?php endif; ?>
                            <div class="card-body card-body-soft d-flex flex-column">
                                <div class="card-meta">
                                    <span class="collection-pill">
                                        <i class="bi bi-bag-heart"></i>
                                        <?php echo htmlspecialchars($product['category'] ?: 'General'); ?>
                                    </span>
                                    <span class="inventory-pill <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                                        <i class="bi bi-circle-fill" style="font-size: 0.6rem;"></i>
                                        <?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Tạm hết'; ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <?php if($product['description']): ?>
                                    <p class="text-muted small flex-grow-1">
                                        <?php echo htmlspecialchars(mb_strimwidth($product['description'], 0, 90, '...')); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-muted small flex-grow-1">Sản phẩm thiết yếu dành cho thú cưng.</p>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-end">
                                    <span class="price-tag"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</span>
                                    <a href="cart.php?add_product=<?php echo $product['id']; ?>" 
                                       class="btn btn-soft btn-gradient <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>">
                                        <i class="bi bi-cart-plus"></i> Giỏ hàng
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($totalProductPages > 1 && !$productSearch): ?>
                <nav class="mt-4" aria-label="Pagination sản phẩm">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $currentProductPage <= 1 ? 'disabled' : ''; ?>">
                            <?php 
                                $prevQuery = $_GET;
                                $prevQuery['product_page'] = max(1, $currentProductPage - 1);
                                $prevQuery['service_page'] = $currentServicePage;
                                $prevUrl = '?' . http_build_query($prevQuery) . '#products';
                            ?>
                            <a class="page-link" href="<?php echo $currentProductPage > 1 ? $prevUrl : '#'; ?>" tabindex="-1">Trước</a>
                        </li>
                        <?php for($page = 1; $page <= $totalProductPages; $page++): ?>
                            <?php 
                                $pageQuery = $_GET;
                                $pageQuery['product_page'] = $page;
                                $pageQuery['service_page'] = $currentServicePage;
                                $pageUrl = '?' . http_build_query($pageQuery) . '#products';
                            ?>
                            <li class="page-item <?php echo $page == $currentProductPage ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $page; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $currentProductPage >= $totalProductPages ? 'disabled' : ''; ?>">
                            <?php 
                                $nextQuery = $_GET;
                                $nextQuery['product_page'] = min($totalProductPages, $currentProductPage + 1);
                                $nextQuery['service_page'] = $currentServicePage;
                                $nextUrl = '?' . http_build_query($nextQuery) . '#products';
                            ?>
                            <a class="page-link" href="<?php echo $currentProductPage < $totalProductPages ? $nextUrl : '#'; ?>">Sau</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-heading">
                <div class="eyebrow">
                    <i class="bi bi-scissors"></i> Dịch vụ chuyên nghiệp
                </div>
                <h2>Dịch vụ của chúng tôi</h2>
                <p>Đội ngũ groomer & bác sĩ thú y tận tâm mang đến trải nghiệm spa, chăm sóc sức khỏe cho thú cưng.</p>
            </div>
            
            <div class="row g-4">
                <?php if(empty($services)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle"></i>
                            <h5>Chưa có dịch vụ nào</h5>
                            <p class="mb-0">Vui lòng đăng nhập với tài khoản admin để thêm dịch vụ, hoặc quay lại sau!</p>
                            <?php if(!isset($_SESSION['user_id'])): ?>
                                <a href="login.php" class="btn btn-primary mt-3">Đăng nhập Admin</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($services as $service): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="service-card h-100 d-flex flex-column">
                                <?php if($service['image']): ?>
                                    <img src="uploads/services/<?php echo htmlspecialchars($service['image']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($service['name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <img src="assets/images/service-placeholder.svg" 
                                         class="card-img-top" 
                                         alt="Pet Care Service Placeholder"
                                         loading="lazy">
                                <?php endif; ?>
                                <div class="card-body card-body-soft d-flex flex-column">
                                    <div class="card-meta">
                                        <span class="collection-pill">
                                            <i class="bi bi-shield-check"></i> An toàn
                                        </span>
                                        <?php if($service['duration']): ?>
                                            <span class="inventory-pill in-stock">
                                                <i class="bi bi-clock-history"></i>
                                                <?php echo $service['duration']; ?> phút
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($service['name']); ?></h4>
                                    <p class="text-muted flex-grow-1"><?php echo htmlspecialchars($service['description'] ?: 'Dịch vụ chăm sóc toàn diện cho thú cưng.'); ?></p>
                                    <div class="service-features">
                                        <span><i class="bi bi-droplet-half"></i> Spa</span>
                                        <span><i class="bi bi-heart"></i> Yêu thương</span>
                                        <span><i class="bi bi-stars"></i> Chuyên gia</span>
                                    </div>
                                    <div class="d-grid gap-2 mt-auto pt-3">
                                        <span class="price-tag"><?php echo number_format($service['price'], 0, ',', '.'); ?> đ</span>
                                        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'user'): ?>
                                            <a href="book_service.php?service_id=<?php echo $service['id']; ?>" class="btn btn-gradient btn-soft">
                                                <i class="bi bi-calendar-check"></i> Đặt lịch ngay
                                            </a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-gradient btn-soft">
                                                <i class="bi bi-box-arrow-in-right"></i> Đăng nhập để đặt lịch
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if($totalServicePages > 1): ?>
                <nav class="mt-4" aria-label="Pagination dịch vụ">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $currentServicePage <= 1 ? 'disabled' : ''; ?>">
                            <?php 
                                $svcPrevQuery = $_GET;
                                $svcPrevQuery['service_page'] = max(1, $currentServicePage - 1);
                                $svcPrevQuery['product_page'] = $currentProductPage;
                                $svcPrevUrl = '?' . http_build_query($svcPrevQuery) . '#services';
                            ?>
                            <a class="page-link" href="<?php echo $currentServicePage > 1 ? $svcPrevUrl : '#'; ?>" tabindex="-1">Trước</a>
                        </li>
                        <?php for($page = 1; $page <= $totalServicePages; $page++): ?>
                            <?php 
                                $svcPageQuery = $_GET;
                                $svcPageQuery['service_page'] = $page;
                                $svcPageQuery['product_page'] = $currentProductPage;
                                $svcPageUrl = '?' . http_build_query($svcPageQuery) . '#services';
                            ?>
                            <li class="page-item <?php echo $page == $currentServicePage ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $svcPageUrl; ?>"><?php echo $page; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $currentServicePage >= $totalServicePages ? 'disabled' : ''; ?>">
                            <?php 
                                $svcNextQuery = $_GET;
                                $svcNextQuery['service_page'] = min($totalServicePages, $currentServicePage + 1);
                                $svcNextQuery['product_page'] = $currentProductPage;
                                $svcNextUrl = '?' . http_build_query($svcNextQuery) . '#services';
                            ?>
                            <a class="page-link" href="<?php echo $currentServicePage < $totalServicePages ? $svcNextUrl : '#'; ?>">Sau</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Pet Care Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tìm kiếm mượt mà với debounce và AJAX
        (function() {
            const searchInput = document.getElementById('product_search');
            const searchForm = document.getElementById('productSearchForm');
            const suggestionsDiv = document.getElementById('searchSuggestions');
            const suggestionsList = document.getElementById('suggestionsList');
            let searchTimeout;
            let allProducts = [];
            let isSearching = false;

            // Lấy danh sách sản phẩm khi trang load (cho suggestions)
            <?php if(!$productSearch): ?>
            const productsData = <?php echo json_encode($products); ?>;
            if(productsData && productsData.length > 0) {
                allProducts = productsData;
            }
            <?php endif; ?>

            // Hàm xử lý submit form - có thể dùng AJAX
            window.handleSearch = function(e) {
                const query = searchInput.value.trim();
                if(query.length < 1) {
                    e.preventDefault();
                    return false;
                }
                // Cho phép submit bình thường nếu người dùng nhấn Enter hoặc nút tìm kiếm
                return true;
            };

            // Debounce search
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if(query.length < 2) {
                    suggestionsDiv.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    // Tìm kiếm local trước
                    const filtered = allProducts.filter(p => 
                        p.name.toLowerCase().includes(query.toLowerCase()) ||
                        (p.category && p.category.toLowerCase().includes(query.toLowerCase()))
                    ).slice(0, 5);

                    if(filtered.length > 0) {
                        showSuggestions(filtered, query);
                    } else {
                        // Nếu không có kết quả local, có thể gọi AJAX
                        suggestionsDiv.style.display = 'none';
                    }
                }, 300);
            });

            function showSuggestions(products, query) {
                suggestionsList.innerHTML = '';
                products.forEach(product => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${highlightMatch(product.name, query)}</strong>
                                ${product.category ? '<br><small class="text-muted">' + product.category + '</small>' : ''}
                            </div>
                            <span class="text-success">${formatPrice(product.price)} đ</span>
                        </div>
                    `;
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        searchInput.value = product.name;
                        searchForm.submit();
                    });
                    suggestionsList.appendChild(item);
                });
                suggestionsDiv.style.display = 'block';
            }

            function highlightMatch(text, query) {
                const regex = new RegExp(`(${query})`, 'gi');
                return text.replace(regex, '<mark>$1</mark>');
            }

            function formatPrice(price) {
                return new Intl.NumberFormat('vi-VN').format(price);
            }

            // Ẩn suggestions khi click ra ngoài
            document.addEventListener('click', function(e) {
                if(!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                    suggestionsDiv.style.display = 'none';
                }
            });

            // Enter để submit (cho phép submit bình thường)
            searchInput.addEventListener('keydown', function(e) {
                if(e.key === 'Enter') {
                    suggestionsDiv.style.display = 'none';
                    // Cho phép form submit bình thường
                }
            });

            // Click vào suggestion sẽ submit form
            document.addEventListener('click', function(e) {
                if(e.target.closest('#suggestionsList a')) {
                    e.preventDefault();
                    const clickedItem = e.target.closest('a');
                    const productName = clickedItem.querySelector('strong').textContent.replace(/<[^>]*>/g, '');
                    searchInput.value = productName;
                    suggestionsDiv.style.display = 'none';
                    searchForm.submit();
                }
            });
        })();
    </script>
</body>
</html>
