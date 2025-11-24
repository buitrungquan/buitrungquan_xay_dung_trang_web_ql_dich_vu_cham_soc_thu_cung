<?php
// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Chỉ admin mới được vào dashboard
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    $_SESSION['error'] = 'Bạn không có quyền truy cập trang này!';
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../app/controllers/PetController.php';
require_once __DIR__ . '/../app/controllers/ServiceController.php';
require_once __DIR__ . '/../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../app/controllers/InvoiceController.php';
require_once __DIR__ . '/../config/database.php';

try {
    $customerController = new CustomerController($conn);
    $petController = new PetController($conn);
    $serviceController = new ServiceController($conn);
    $appointmentController = new AppointmentController($conn);
    $invoiceController = new InvoiceController($conn);

    // Lấy thống kê
    $totalCustomers = count($customerController->getAll());
    $totalPets = count($petController->getAll());
    $totalServices = count($serviceController->getAll());
    $appointments = $appointmentController->getAll();
    $totalAppointments = count($appointments);
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi: " . $e->getMessage() . ". Vui lòng kiểm tra database!";
    $totalCustomers = 0;
    $totalPets = 0;
    $totalServices = 0;
    $appointments = [];
    $totalAppointments = 0;
}

// Đếm lịch hẹn theo trạng thái
$pendingAppointments = 0;
$inProgressAppointments = 0;
$completedAppointments = 0;
foreach($appointments as $apt){
    if($apt['status'] == 'pending') $pendingAppointments++;
    if($apt['status'] == 'in_progress') $inProgressAppointments++;
    if($apt['status'] == 'completed') $completedAppointments++;
}

// Thống kê hóa đơn
try {
    $invoices = $invoiceController->getAll();
    $totalInvoices = count($invoices);
    $paidInvoices = 0;
    $unpaidInvoices = 0;
    $totalRevenue = 0;
    foreach($invoices as $invoice){
        if($invoice['payment_status'] == 'paid'){
            $paidInvoices++;
            $totalRevenue += $invoice['total_amount'];
        } else {
            $unpaidInvoices++;
        }
    }
} catch (Exception $e) {
    $invoices = [];
    $totalInvoices = 0;
    $paidInvoices = 0;
    $unpaidInvoices = 0;
    $totalRevenue = 0;
}

// Lịch hẹn sắp tới (7 ngày tới)
$upcomingAppointments = [];
$today = date('Y-m-d');
$nextWeek = date('Y-m-d', strtotime('+7 days'));
foreach($appointments as $apt){
    $aptDate = date('Y-m-d', strtotime($apt['appointment_date']));
    if($aptDate >= $today && $aptDate <= $nextWeek && $apt['status'] != 'canceled'){
        $upcomingAppointments[] = $apt;
    }
}
usort($upcomingAppointments, function($a, $b){
    return strtotime($a['appointment_date']) - strtotime($b['appointment_date']);
});
$upcomingAppointments = array_slice($upcomingAppointments, 0, 5); // Lấy 5 lịch hẹn gần nhất

$pageTitle = 'Dashboard';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
    <p class="text-muted mb-0">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
</div>

<!-- Thống kê tổng quan -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Khách hàng</h6>
                        <h2 class="mb-0"><?php echo $totalCustomers; ?></h2>
                    </div>
                    <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Thú cưng</h6>
                        <h2 class="mb-0"><?php echo $totalPets; ?></h2>
                    </div>
                    <i class="bi bi-heart" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Dịch vụ</h6>
                        <h2 class="mb-0"><?php echo $totalServices; ?></h2>
                    </div>
                    <i class="bi bi-bag-check" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Lịch hẹn</h6>
                        <h2 class="mb-0"><?php echo $totalAppointments; ?></h2>
                    </div>
                    <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Trạng thái Lịch hẹn</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge bg-warning">Chờ xử lý: <?php echo $pendingAppointments; ?></span>
                </div>
                <div class="mb-2">
                    <span class="badge bg-info">Đang thực hiện: <?php echo $inProgressAppointments; ?></span>
                </div>
                <div class="mb-2">
                    <span class="badge bg-success">Hoàn thành: <?php echo $completedAppointments; ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Thống kê Hóa đơn</h5>
            </div>
            <div class="card-body">
                <p><strong>Tổng hóa đơn:</strong> <?php echo $totalInvoices; ?></p>
                <p><strong>Đã thanh toán:</strong> <span class="text-success"><?php echo $paidInvoices; ?></span></p>
                <p><strong>Chưa thanh toán:</strong> <span class="text-warning"><?php echo $unpaidInvoices; ?></span></p>
                <hr>
                <h5 class="text-success">Tổng doanh thu: <?php echo number_format($totalRevenue, 0, ',', '.'); ?> đ</h5>
            </div>
        </div>
    </div>
</div>

<!-- Lịch hẹn sắp tới -->
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Lịch hẹn sắp tới (7 ngày tới)</h5>
    </div>
    <div class="card-body">
        <?php if(empty($upcomingAppointments)): ?>
            <p class="text-muted">Không có lịch hẹn nào trong 7 ngày tới</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Ngày giờ</th>
                            <th>Khách hàng</th>
                            <th>Thú cưng</th>
                            <th>Dịch vụ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($upcomingAppointments as $apt): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($apt['appointment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($apt['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($apt['pet_name']); ?></td>
                                <td><?php echo htmlspecialchars($apt['service_name']); ?></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'completed' => 'success',
                                        'canceled' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ xử lý',
                                        'in_progress' => 'Đang thực hiện',
                                        'completed' => 'Hoàn thành',
                                        'canceled' => 'Đã hủy'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $statusColors[$apt['status']]; ?>">
                                        <?php echo $statusLabels[$apt['status']]; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layout.php';
?>
