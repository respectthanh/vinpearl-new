<?php
/**
 * Vinpearl Resort Nha Trang - Admin Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Determine language (in a real implementation, this would be more sophisticated)
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Require admin privileges to access this page
requireAdmin('login.php');

// Get some stats for the dashboard
$conn = connectDatabase();
if ($conn) {
    // Get recent bookings
    $recentBookingsStmt = $conn->prepare("
        SELECT rb.id, rb.check_in_date, rb.check_out_date, rb.guests, rb.total_price, rb.status,
               u.full_name as user_name, r.name_en, r.name_vi
        FROM room_bookings rb
        JOIN users u ON rb.user_id = u.id
        JOIN rooms r ON rb.room_id = r.id
        ORDER BY rb.created_at DESC
        LIMIT 5
    ");
    $recentBookingsStmt->execute();
    $recentBookings = $recentBookingsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get pending reviews
    $pendingReviewsStmt = $conn->prepare("
        SELECT r.id, r.title_en, r.title_vi, r.rating, r.type, r.created_at,
               u.full_name as user_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.is_approved = 0
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $pendingReviewsStmt->execute();
    $pendingReviews = $pendingReviewsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get occupancy rate for current month
    $currentMonth = date('Y-m');
    $occupancyStmt = $conn->prepare("
        SELECT COUNT(DISTINCT rb.room_id) * DAY(LAST_DAY(CURRENT_DATE)) as booked_room_days,
               (SELECT COUNT(*) FROM rooms) * DAY(LAST_DAY(CURRENT_DATE)) as total_room_days
        FROM room_bookings rb
        WHERE rb.status IN ('confirmed', 'completed')
        AND DATE_FORMAT(rb.check_in_date, '%Y-%m') <= ?
        AND DATE_FORMAT(rb.check_out_date, '%Y-%m') >= ?
    ");
    $occupancyStmt->bind_param('ss', $currentMonth, $currentMonth);
    $occupancyStmt->execute();
    $occupancyResult = $occupancyStmt->get_result()->fetch_assoc();
    
    $occupancyRate = 0;
    if ($occupancyResult['total_room_days'] > 0) {
        $occupancyRate = round(($occupancyResult['booked_room_days'] / $occupancyResult['total_room_days']) * 100);
    }
    
    // Get revenue for current month
    $revenueStmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN DATE_FORMAT(rb.created_at, '%Y-%m') = ? THEN rb.total_price ELSE 0 END) as room_revenue,
            SUM(CASE WHEN DATE_FORMAT(pb.created_at, '%Y-%m') = ? THEN pb.total_price ELSE 0 END) as package_revenue,
            SUM(CASE WHEN DATE_FORMAT(tb.created_at, '%Y-%m') = ? THEN tb.total_price ELSE 0 END) as tour_revenue
        FROM 
            room_bookings rb,
            package_bookings pb,
            tour_bookings tb
        WHERE rb.status != 'cancelled'
        AND pb.status != 'cancelled'
        AND tb.status != 'cancelled'
    ");
    $revenueStmt->bind_param('sss', $currentMonth, $currentMonth, $currentMonth);
    $revenueStmt->execute();
    $revenueResult = $revenueStmt->get_result()->fetch_assoc();
    
    $totalRevenue = $revenueResult['room_revenue'] + $revenueResult['package_revenue'] + $revenueResult['tour_revenue'];
}

// Page title
$pageTitle = $language === 'vi' ? 'Bảng điều khiển quản trị' : 'Admin Dashboard';
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-page">
    <div class="admin-layout">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <a href="index.php">
                    <img src="../assets/images/logo.png" alt="Vinpearl Resort Nha Trang">
                    <span><?php echo $language === 'vi' ? 'Quản trị' : 'Admin'; ?></span>
                </a>
            </div>
            
            <nav class="admin-nav">
                <ul>
                    <li>
                        <a href="index.php" class="active">
                            <i class="icon-dashboard"></i>
                            <?php echo $language === 'vi' ? 'Bảng điều khiển' : 'Dashboard'; ?>
                        </a>
                    </li>
                    <li>
                        <a href="content.php">
                            <i class="icon-content"></i>
                            <?php echo $language === 'vi' ? 'Quản lý nội dung' : 'Content Management'; ?>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php">
                            <i class="icon-bookings"></i>
                            <?php echo $language === 'vi' ? 'Quản lý đặt phòng' : 'Bookings Management'; ?>
                        </a>
                    </li>
                    <li>
                        <a href="reviews.php">
                            <i class="icon-reviews"></i>
                            <?php echo $language === 'vi' ? 'Quản lý đánh giá' : 'Reviews Management'; ?>
                        </a>
                    </li>
                    <li>
                        <a href="nearby.php">
                            <i class="icon-nearby"></i>
                            <?php echo $language === 'vi' ? 'Địa điểm gần đó' : 'Nearby Places'; ?>
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="icon-users"></i>
                            <?php echo $language === 'vi' ? 'Quản lý người dùng' : 'Users Management'; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <div class="admin-content">
            <!-- Admin Header -->
            <header class="admin-header">
                <div class="admin-header-title">
                    <h1><?php echo $pageTitle; ?></h1>
                </div>
                
                <div class="admin-user">
                    <div class="language-selector">
                        <a href="?lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                        <a href="?lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
                    </div>
                    
                    <div class="admin-user-name">
                        <?php 
                        $currentUser = getCurrentUser();
                        echo htmlspecialchars($currentUser['full_name']); 
                        ?>
                    </div>
                    
                    <a href="../logout.php" class="btn btn-sm"><?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                </div>
            </header>
            
            <main class="admin-main">
                <!-- Stats Cards -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="icon-occupancy"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Tỷ lệ lấp đầy' : 'Occupancy Rate'; ?></h3>
                            <div class="stat-value"><?php echo $occupancyRate; ?>%</div>
                            <div class="stat-period"><?php echo $language === 'vi' ? 'Tháng này' : 'This month'; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="icon-revenue"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Doanh thu' : 'Revenue'; ?></h3>
                            <div class="stat-value"><?php echo formatCurrency($totalRevenue); ?></div>
                            <div class="stat-period"><?php echo $language === 'vi' ? 'Tháng này' : 'This month'; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="icon-bookings"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Đặt phòng' : 'Bookings'; ?></h3>
                            <div class="stat-value"><?php echo count($recentBookings); ?></div>
                            <div class="stat-period"><?php echo $language === 'vi' ? 'Gần đây' : 'Recent'; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="icon-reviews"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Đánh giá đang chờ' : 'Pending Reviews'; ?></h3>
                            <div class="stat-value"><?php echo count($pendingReviews); ?></div>
                            <div class="stat-period"><?php echo $language === 'vi' ? 'Cần phê duyệt' : 'Need approval'; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><?php echo $language === 'vi' ? 'Đặt phòng gần đây' : 'Recent Bookings'; ?></h2>
                        <a href="bookings.php" class="btn btn-sm"><?php echo $language === 'vi' ? 'Xem tất cả' : 'View all'; ?></a>
                    </div>
                    
                    <div class="admin-card-content">
                        <?php if (empty($recentBookings)): ?>
                            <p><?php echo $language === 'vi' ? 'Không có đặt phòng nào gần đây.' : 'No recent bookings.'; ?></p>
                        <?php else: ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th><?php echo $language === 'vi' ? 'Khách hàng' : 'Customer'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Phòng' : 'Room'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Ngày nhận' : 'Check-in'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Ngày trả' : 'Check-out'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Tổng giá' : 'Total Price'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Hành động' : 'Actions'; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentBookings as $booking): ?>
                                        <tr>
                                            <td><?php echo $booking['id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?></td>
                                            <td><?php echo formatDate($booking['check_in_date']); ?></td>
                                            <td><?php echo formatDate($booking['check_out_date']); ?></td>
                                            <td><?php echo formatCurrency($booking['total_price']); ?></td>
                                            <td>
                                                <span class="admin-badge admin-badge-<?php echo $booking['status']; ?>">
                                                    <?php 
                                                    if ($language === 'vi') {
                                                        $statusLabels = [
                                                            'pending' => 'Đang chờ',
                                                            'confirmed' => 'Đã xác nhận',
                                                            'cancelled' => 'Đã hủy',
                                                            'completed' => 'Đã hoàn thành'
                                                        ];
                                                        echo $statusLabels[$booking['status']] ?? $booking['status'];
                                                    } else {
                                                        echo ucfirst($booking['status']);
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="admin-action-buttons">
                                                    <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm admin-btn-view">
                                                        <?php echo $language === 'vi' ? 'Xem' : 'View'; ?>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Pending Reviews -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><?php echo $language === 'vi' ? 'Đánh giá đang chờ duyệt' : 'Pending Reviews'; ?></h2>
                        <a href="reviews.php" class="btn btn-sm"><?php echo $language === 'vi' ? 'Xem tất cả' : 'View all'; ?></a>
                    </div>
                    
                    <div class="admin-card-content">
                        <?php if (empty($pendingReviews)): ?>
                            <p><?php echo $language === 'vi' ? 'Không có đánh giá nào đang chờ duyệt.' : 'No pending reviews.'; ?></p>
                        <?php else: ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th><?php echo $language === 'vi' ? 'Khách hàng' : 'Customer'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Tiêu đề' : 'Title'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Loại' : 'Type'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Đánh giá' : 'Rating'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Ngày' : 'Date'; ?></th>
                                        <th><?php echo $language === 'vi' ? 'Hành động' : 'Actions'; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReviews as $review): ?>
                                        <tr>
                                            <td><?php echo $review['id']; ?></td>
                                            <td><?php echo htmlspecialchars($review['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($review[$language === 'vi' ? 'title_vi' : 'title_en']); ?></td>
                                            <td>
                                                <?php 
                                                if ($language === 'vi') {
                                                    $typeLabels = [
                                                        'room' => 'Phòng',
                                                        'package' => 'Gói',
                                                        'tour' => 'Tour'
                                                    ];
                                                    echo $typeLabels[$review['type']] ?? $review['type'];
                                                } else {
                                                    echo ucfirst($review['type']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="rating-stars">
                                                    <?php 
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        echo $i <= $review['rating'] ? '★' : '☆';
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                            <td><?php echo formatDate($review['created_at']); ?></td>
                                            <td>
                                                <div class="admin-action-buttons">
                                                    <a href="review-details.php?id=<?php echo $review['id']; ?>" class="btn btn-sm admin-btn-view">
                                                        <?php echo $language === 'vi' ? 'Xem' : 'View'; ?>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html> 