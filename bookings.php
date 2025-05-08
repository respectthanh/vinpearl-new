<?php
/**
 * Vinpearl Resort Nha Trang - User Bookings Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require login to access this page
requireLogin('login.php?redirect=bookings.php');

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get current user
$currentUser = getCurrentUser();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

// Get user bookings
$conn = connectDatabase();
$room_bookings = [];
$package_bookings = [];
$tour_bookings = [];
$all_bookings = [];

if ($conn) {
    // Room bookings query
    $room_query = "
        SELECT rb.*, r.name_en, r.name_vi, r.image_url, 'room' as booking_type
        FROM room_bookings rb
        JOIN rooms r ON rb.room_id = r.id
        WHERE rb.user_id = ?
    ";
    
    // Package bookings query
    $package_query = "
        SELECT pb.*, p.name_en, p.name_vi, p.image_url, 'package' as booking_type
        FROM package_bookings pb
        JOIN packages p ON pb.package_id = p.id
        WHERE pb.user_id = ?
    ";
    
    // Tour bookings query
    $tour_query = "
        SELECT tb.*, t.name_en, t.name_vi, t.image_url, 'tour' as booking_type
        FROM tour_bookings tb
        JOIN tours t ON tb.tour_id = t.id
        WHERE tb.user_id = ?
    ";
    
    // Apply status filter if provided
    if (!empty($status_filter)) {
        $room_query .= " AND rb.status = ?";
        $package_query .= " AND pb.status = ?";
        $tour_query .= " AND tb.status = ?";
    }
    
    // Get room bookings
    if (empty($type_filter) || $type_filter === 'room') {
        if (!empty($status_filter)) {
            $stmt = $conn->prepare($room_query);
            $stmt->bind_param("is", $currentUser['id'], $status_filter);
        } else {
            $stmt = $conn->prepare($room_query);
            $stmt->bind_param("i", $currentUser['id']);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($booking = $result->fetch_assoc()) {
            $room_bookings[] = $booking;
            $all_bookings[] = $booking;
        }
    }
    
    // Get package bookings
    if (empty($type_filter) || $type_filter === 'package') {
        if (!empty($status_filter)) {
            $stmt = $conn->prepare($package_query);
            $stmt->bind_param("is", $currentUser['id'], $status_filter);
        } else {
            $stmt = $conn->prepare($package_query);
            $stmt->bind_param("i", $currentUser['id']);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($booking = $result->fetch_assoc()) {
            $package_bookings[] = $booking;
            $all_bookings[] = $booking;
        }
    }
    
    // Get tour bookings
    if (empty($type_filter) || $type_filter === 'tour') {
        if (!empty($status_filter)) {
            $stmt = $conn->prepare($tour_query);
            $stmt->bind_param("is", $currentUser['id'], $status_filter);
        } else {
            $stmt = $conn->prepare($tour_query);
            $stmt->bind_param("i", $currentUser['id']);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($booking = $result->fetch_assoc()) {
            $tour_bookings[] = $booking;
            $all_bookings[] = $booking;
        }
    }
}

// Sort all bookings by created_at in descending order
usort($all_bookings, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Page title
$pageTitle = $language === 'vi' ? 'Đặt chỗ của tôi' : 'My Bookings';

// Status labels
$statusLabels = [
    'pending' => $language === 'vi' ? 'Đang chờ' : 'Pending',
    'confirmed' => $language === 'vi' ? 'Đã xác nhận' : 'Confirmed',
    'cancelled' => $language === 'vi' ? 'Đã hủy' : 'Cancelled',
    'completed' => $language === 'vi' ? 'Đã hoàn thành' : 'Completed'
];

// Type labels
$typeLabels = [
    'room' => $language === 'vi' ? 'Phòng' : 'Room',
    'package' => $language === 'vi' ? 'Gói dịch vụ' : 'Package',
    'tour' => $language === 'vi' ? 'Tour' : 'Tour'
];
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Vinpearl Resort Nha Trang">
                </a>
            </div>
            
            <nav class="main-navigation">
                <ul>
                    <li><a href="index.php"><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a></li>
                    <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                    <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                    <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                    <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="language-selector">
                    <a href="?<?php echo !empty($_GET['status']) ? 'status=' . urlencode($_GET['status']) . '&' : ''; ?><?php echo !empty($_GET['type']) ? 'type=' . urlencode($_GET['type']) . '&' : ''; ?>lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                    <a href="?<?php echo !empty($_GET['status']) ? 'status=' . urlencode($_GET['status']) . '&' : ''; ?><?php echo !empty($_GET['type']) ? 'type=' . urlencode($_GET['type']) . '&' : ''; ?>lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
                </div>
                
                <div class="user-actions">
                    <div class="user-menu">
                        <span>Hello, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <div class="dropdown-menu">
                            <a href="profile.php"><?php echo $language === 'vi' ? 'Hồ sơ' : 'Profile'; ?></a>
                            <a href="bookings.php" class="active"><?php echo $language === 'vi' ? 'Đặt chỗ' : 'My Bookings'; ?></a>
                            <?php if (isAdmin()): ?>
                                <a href="admin/index.php"><?php echo $language === 'vi' ? 'Quản trị' : 'Admin Panel'; ?></a>
                            <?php endif; ?>
                            <a href="logout.php"><?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header bookings-header">
        <div class="container">
            <div class="header-content">
                <div class="header-text">
                    <h1><?php echo $pageTitle; ?></h1>
                    <p><?php echo $language === 'vi' ? 'Quản lý các đặt chỗ của bạn' : 'Manage your bookings'; ?></p>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <i class="fas fa-calendar-check"></i>
                        <span class="stat-number"><?php echo count($all_bookings); ?></span>
                        <span class="stat-label"><?php echo $language === 'vi' ? 'Tổng đặt chỗ' : 'Total Bookings'; ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-clock"></i>
                        <span class="stat-number"><?php echo count(array_filter($all_bookings, function($b) { return $b['status'] === 'pending'; })); ?></span>
                        <span class="stat-label"><?php echo $language === 'vi' ? 'Đang chờ' : 'Pending'; ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-check-circle"></i>
                        <span class="stat-number"><?php echo count(array_filter($all_bookings, function($b) { return $b['status'] === 'confirmed'; })); ?></span>
                        <span class="stat-label"><?php echo $language === 'vi' ? 'Đã xác nhận' : 'Confirmed'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Filter -->
    <section class="bookings-filter">
        <div class="container">
            <div class="filter-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="bookingSearch" placeholder="<?php echo $language === 'vi' ? 'Tìm kiếm đặt chỗ...' : 'Search bookings...'; ?>">
                </div>
                <div class="filter-group">
                    <span class="filter-label"><i class="fas fa-filter"></i> <?php echo $language === 'vi' ? 'Loại:' : 'Type:'; ?></span>
                    <div class="filter-options">
                        <a href="?<?php echo !empty($_GET['status']) ? 'status=' . urlencode($_GET['status']) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" class="filter-btn <?php echo empty($type_filter) ? 'active' : ''; ?>">
                            <?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?>
                        </a>
                        <a href="?type=room<?php echo !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-btn <?php echo $type_filter === 'room' ? 'active' : ''; ?>">
                            <?php echo $typeLabels['room']; ?>
                        </a>
                        <a href="?type=package<?php echo !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-btn <?php echo $type_filter === 'package' ? 'active' : ''; ?>">
                            <?php echo $typeLabels['package']; ?>
                        </a>
                        <a href="?type=tour<?php echo !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-btn <?php echo $type_filter === 'tour' ? 'active' : ''; ?>">
                            <?php echo $typeLabels['tour']; ?>
                        </a>
                    </div>
                </div>
                
                <div class="filter-group">
                    <span class="filter-label"><?php echo $language === 'vi' ? 'Trạng thái:' : 'Status:'; ?></span>
                    <div class="filter-options">
                        <a href="?<?php echo !empty($_GET['type']) ? 'type=' . urlencode($_GET['type']) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" class="filter-btn <?php echo empty($status_filter) ? 'active' : ''; ?>">
                            <?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?>
                        </a>
                        <a href="?status=pending<?php echo !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                            <?php echo $statusLabels['pending']; ?>
                        </a>
                        <a href="?status=confirmed<?php echo !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-btn <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">
                            <?php echo $statusLabels['confirmed']; ?>
                        </a>
                        <a href="?status=completed<?php echo !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-btn <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                            <?php echo $statusLabels['completed']; ?>
                        </a>
                        <a href="?status=cancelled<?php echo !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-btn <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                            <?php echo $statusLabels['cancelled']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bookings List -->
    <section class="bookings-section">
        <div class="container">
            <?php if (empty($all_bookings)): ?>
                <div class="no-bookings">
                    <h2><?php echo $language === 'vi' ? 'Không tìm thấy đặt chỗ nào' : 'No bookings found'; ?></h2>
                    <p>
                        <?php if (!empty($type_filter) || !empty($status_filter)): ?>
                            <?php echo $language === 'vi' ? 'Hãy thử các bộ lọc khác hoặc xem tất cả đặt chỗ của bạn' : 'Try different filters or view all your bookings'; ?>
                        <?php else: ?>
                            <?php echo $language === 'vi' ? 'Bạn chưa có đặt chỗ nào. Hãy khám phá phòng, gói dịch vụ và tour của chúng tôi!' : 'You don\'t have any bookings yet. Explore our rooms, packages and tours!'; ?>
                        <?php endif; ?>
                    </p>
                    <div class="action-btns">
                        <?php if (!empty($type_filter) || !empty($status_filter)): ?>
                            <a href="bookings.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn"><?php echo $language === 'vi' ? 'Xem tất cả đặt chỗ' : 'View all bookings'; ?></a>
                        <?php else: ?>
                            <a href="rooms.php" class="btn"><?php echo $language === 'vi' ? 'Khám phá phòng' : 'Explore rooms'; ?></a>
                            <a href="packages.php" class="btn"><?php echo $language === 'vi' ? 'Khám phá gói dịch vụ' : 'Explore packages'; ?></a>
                            <a href="tours.php" class="btn"><?php echo $language === 'vi' ? 'Khám phá tours' : 'Explore tours'; ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="booking-list">
                    <?php foreach ($all_bookings as $booking): ?>
                        <div class="booking-item">
                            <div class="booking-image">
                                <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                                <div class="booking-badges">
                                    <span class="booking-type-badge">
                                        <?php echo $typeLabels[$booking['booking_type']]; ?>
                                    </span>
                                    <span class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                        <?php echo $statusLabels[$booking['status']] ?? ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="booking-details">
                                <h3><?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                <div class="booking-info">
                                    <?php if ($booking['booking_type'] === 'room'): ?>
                                        <div class="info-item">
                                            <i class="icon-calendar"></i>
                                            <span><?php echo $language === 'vi' ? 'Nhận phòng:' : 'Check-in:'; ?> <?php echo formatDate($booking['check_in_date'], 'M d, Y'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <i class="icon-calendar"></i>
                                            <span><?php echo $language === 'vi' ? 'Trả phòng:' : 'Check-out:'; ?> <?php echo formatDate($booking['check_out_date'], 'M d, Y'); ?></span>
                                        </div>
                                    <?php elseif ($booking['booking_type'] === 'package'): ?>
                                        <div class="info-item">
                                            <i class="icon-calendar"></i>
                                            <span><?php echo $language === 'vi' ? 'Ngày bắt đầu:' : 'Start date:'; ?> <?php echo formatDate($booking['start_date'], 'M d, Y'); ?></span>
                                        </div>
                                    <?php elseif ($booking['booking_type'] === 'tour'): ?>
                                        <div class="info-item">
                                            <i class="icon-calendar"></i>
                                            <span><?php echo $language === 'vi' ? 'Ngày tham gia:' : 'Tour date:'; ?> <?php echo formatDate($booking['tour_date'], 'M d, Y'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-item">
                                        <i class="icon-user"></i>
                                        <span><?php echo $booking['guests']; ?> <?php echo $language === 'vi' ? 'khách' : 'guests'; ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="icon-price"></i>
                                        <span><?php echo formatCurrency($booking['total_price']); ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="icon-date"></i>
                                        <span><?php echo $language === 'vi' ? 'Đặt ngày:' : 'Booked on:'; ?> <?php echo formatDate($booking['created_at'], 'M d, Y'); ?></span>
                                    </div>
                                </div>
                                
                                <div class="booking-actions">
                                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                        <a href="#" class="btn btn-sm btn-outline cancel-booking" data-type="<?php echo $booking['booking_type']; ?>" data-id="<?php echo $booking['booking_type'] === 'room' ? $booking['room_booking_id'] : ($booking['booking_type'] === 'package' ? $booking['package_booking_id'] : $booking['tour_booking_id']); ?>">
                                            <?php echo $language === 'vi' ? 'Hủy đặt chỗ' : 'Cancel Booking'; ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    // Define item ID based on booking type
                                    $itemId = 0;
                                    $itemType = '';
                                    
                                    if ($booking['booking_type'] === 'room') {
                                        $itemId = $booking['room_id'];
                                        $itemType = 'room';
                                    } elseif ($booking['booking_type'] === 'package') {
                                        $itemId = $booking['package_id'];
                                        $itemType = 'package';
                                    } elseif ($booking['booking_type'] === 'tour') {
                                        $itemId = $booking['tour_id'];
                                        $itemType = 'tour';
                                    }
                                    ?>
                                    
                                    <?php if ($booking['status'] === 'completed' && !hasReviewed($currentUser['id'], $itemType, $itemId)): ?>
                                        <a href="write-review.php?type=<?php echo $itemType; ?>&id=<?php echo $itemId; ?>" class="btn btn-sm">
                                            <?php echo $language === 'vi' ? 'Viết đánh giá' : 'Write Review'; ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['booking_type'] === 'room'): ?>
                                        <a href="room-details.php?id=<?php echo $booking['room_id']; ?>" class="btn btn-sm">
                                            <?php echo $language === 'vi' ? 'Xem chi tiết' : 'View Details'; ?>
                                        </a>
                                    <?php elseif ($booking['booking_type'] === 'package'): ?>
                                        <a href="package-details.php?id=<?php echo $booking['package_id']; ?>" class="btn btn-sm">
                                            <?php echo $language === 'vi' ? 'Xem chi tiết' : 'View Details'; ?>
                                        </a>
                                    <?php elseif ($booking['booking_type'] === 'tour'): ?>
                                        <a href="tour-details.php?id=<?php echo $booking['tour_id']; ?>" class="btn btn-sm">
                                            <?php echo $language === 'vi' ? 'Xem chi tiết' : 'View Details'; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Cancel Booking Modal -->
    <div id="cancelBookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo $language === 'vi' ? 'Xác nhận hủy đặt chỗ' : 'Confirm Cancellation'; ?></h3>
                <button class="close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-header">
                <h2><?php echo $language === 'vi' ? 'Xác nhận hủy đặt chỗ' : 'Confirm Cancellation'; ?></h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p><?php echo $language === 'vi' ? 'Bạn có chắc chắn muốn hủy đặt chỗ này không? Hành động này không thể hoàn tác.' : 'Are you sure you want to cancel this booking? This action cannot be undone.'; ?></p>
            </div>
            <div class="modal-footer">
                <form id="cancelBookingForm" method="post" action="cancel-booking.php">
                    <input type="hidden" id="bookingType" name="booking_type" value="">
                    <input type="hidden" id="bookingId" name="booking_id" value="">
                    <button type="submit" class="btn btn-danger"><?php echo $language === 'vi' ? 'Hủy đặt chỗ' : 'Cancel Booking'; ?></button>
                    <button type="button" class="btn btn-outline close-modal"><?php echo $language === 'vi' ? 'Không' : 'No'; ?></button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-columns">
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Về chúng tôi' : 'About Us'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Vinpearl Resort Nha Trang là khu nghỉ dưỡng sang trọng với tầm nhìn tuyệt đẹp ra biển.' : 'Vinpearl Resort Nha Trang is a luxury resort with stunning ocean views.'; ?></p>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact'; ?></h3>
                    <address>
                        <p><?php echo $language === 'vi' ? 'Địa chỉ:' : 'Address:'; ?> Vinpearl Resort Nha Trang, Đảo Hòn Tre, Nha Trang, Việt Nam</p>
                        <p><?php echo $language === 'vi' ? 'Điện thoại:' : 'Phone:'; ?> +84 258 598 9999</p>
                        <p>Email: info@vinpearl.com</p>
                    </address>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Liên kết nhanh' : 'Quick Links'; ?></h3>
                    <ul>
                        <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                        <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                        <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                        <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                        </ul>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Kết nối' : 'Connect'; ?></h3>
                    <div class="social-links">
                        <a href="#" class="social-link"><img src="assets/images/icons/facebook.svg" alt="Facebook"></a>
                        <a href="#" class="social-link"><img src="assets/images/icons/instagram.svg" alt="Instagram"></a>
                        <a href="#" class="social-link"><img src="assets/images/icons/twitter.svg" alt="Twitter"></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Vinpearl Resort Nha Trang. <?php echo $language === 'vi' ? 'Đã đăng ký bản quyền.' : 'All rights reserved.'; ?></p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const modal = document.getElementById('cancelBookingModal');
            const cancelBtns = document.querySelectorAll('.cancel-booking');
            const closeBtns = document.querySelectorAll('.close-modal');
            const bookingTypeField = document.getElementById('bookingType');
            const bookingIdField = document.getElementById('bookingId');
            
            // Open modal when cancel button is clicked
            cancelBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const bookingType = this.getAttribute('data-type');
                    const bookingId = this.getAttribute('data-id');
                    
                    bookingTypeField.value = bookingType;
                    bookingIdField.value = bookingId;
                    
                    modal.style.display = 'block';
                });
            });
            
            // Close modal when close button or outside click
            closeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            });
            
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
    <script src="assets/js/bookings.js"></script>
</body>
</html>

<?php
/**
 * Helper function to check if a user has reviewed an item
 */
function hasReviewed($userId, $type, $itemId) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews 
                           WHERE user_id = ? AND type = ? AND item_id = ?");
    $stmt->bind_param("isi", $userId, $type, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['review_count'] > 0;
} 