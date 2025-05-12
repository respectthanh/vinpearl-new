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

include 'includes/header.php';
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
                    <select id="typeFilter" class="filter-select">
                        <option value="" <?php echo empty($type_filter) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></option>
                        <option value="room" <?php echo $type_filter === 'room' ? 'selected' : ''; ?>><?php echo $typeLabels['room']; ?></option>
                        <option value="package" <?php echo $type_filter === 'package' ? 'selected' : ''; ?>><?php echo $typeLabels['package']; ?></option>
                        <option value="tour" <?php echo $type_filter === 'tour' ? 'selected' : ''; ?>><?php echo $typeLabels['tour']; ?></option>
                    </select>
                </div>

                <div class="filter-group">
                    <span class="filter-label"><i class="fas fa-chart-pie"></i> <?php echo $language === 'vi' ? 'Trạng thái:' : 'Status:'; ?></span>
                    <select id="statusFilter" class="filter-select">
                        <option value="" <?php echo empty($status_filter) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>><?php echo $statusLabels['pending']; ?></option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>><?php echo $statusLabels['confirmed']; ?></option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>><?php echo $statusLabels['cancelled']; ?></option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>><?php echo $statusLabels['completed']; ?></option>
                    </select>
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
                                    <span class="status-badge <?php echo $booking['status']; ?>">
                                        <?php echo $statusLabels[$booking['status']]; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="booking-details">
                                <h3><?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                <div class="booking-info">
                                    <?php if ($booking['booking_type'] === 'room'): ?>
                                        <div class="info-item">
                                            <i class="fas fa-calendar"></i>
                                            <span>
                                                <?php 
                                                echo $language === 'vi' ? 'Check-in: ' : 'Check-in: ';
                                                echo date('M d, Y', strtotime($booking['check_in_date']));
                                                ?>
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-calendar-check"></i>
                                            <span>
                                                <?php 
                                                echo $language === 'vi' ? 'Check-out: ' : 'Check-out: ';
                                                echo date('M d, Y', strtotime($booking['check_out_date']));
                                                ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div class="info-item">
                                            <i class="fas fa-calendar"></i>
                                            <span>
                                                <?php 
                                                echo $language === 'vi' ? 'Ngày: ' : 'Date: ';
                                                echo date('M d, Y', strtotime($booking['tour_date'] ?? $booking['package_date']));
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="info-item">
                                        <i class="fas fa-users"></i>
                                        <span>
                                            <?php 
                                            echo $language === 'vi' ? 'Số người: ' : 'Guests: ';
                                            // Fix undefined array key by using the 'guests' key instead of 'num_guests'
                                            echo isset($booking['guests']) ? $booking['guests'] : '-';
                                            ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <span>
                                            <?php 
                                            echo $language === 'vi' ? 'Đặt lúc: ' : 'Booked on: ';
                                            echo date('M d, Y', strtotime($booking['created_at']));
                                            ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>
                                            <?php 
                                            echo $language === 'vi' ? 'Tổng tiền: ' : 'Total: ';
                                            // Fix undefined array key by using the 'total_price' key instead of 'total_amount'
                                            echo '$' . number_format(isset($booking['total_price']) ? $booking['total_price'] : 0, 2);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="booking-actions">
                                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                        <a href="#" class="btn btn-sm btn-outline cancel-booking" data-type="<?php echo $booking['booking_type']; ?>" data-id="<?php echo $booking['id']; ?>">
                                            <?php echo $language === 'vi' ? 'Hủy đặt chỗ' : 'Cancel Booking'; ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($booking['status'] === 'completed' && !hasReviewed($currentUser['id'], $booking['booking_type'], $booking['booking_type'] === 'room' ? $booking['room_id'] : ($booking['booking_type'] === 'package' ? $booking['package_id'] : $booking['tour_id']))): ?>
                                        <a href="review.php?type=<?php echo $booking['booking_type']; ?>&id=<?php echo $booking['booking_type'] === 'room' ? $booking['room_id'] : ($booking['booking_type'] === 'package' ? $booking['package_id'] : $booking['tour_id']); ?>" class="btn btn-sm btn-outline">
                                            <?php echo $language === 'vi' ? 'Viết đánh giá' : 'Write Review'; ?>
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
            <div class="modal-body">
                <p><?php echo $language === 'vi' ? 'Bạn có chắc chắn muốn hủy đặt chỗ này? Hành động này không thể hoàn tác.' : 'Are you sure you want to cancel this booking? This action cannot be undone.'; ?></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline close-modal"><?php echo $language === 'vi' ? 'Đóng' : 'Close'; ?></button>
                <button id="confirmCancel" class="btn btn-danger"><?php echo $language === 'vi' ? 'Xác nhận hủy' : 'Confirm Cancel'; ?></button>
            </div>
        </div>
    </div>

    <script src="assets/js/bookings.js"></script>
</body>
</html>

<?php include 'includes/footer.php'; ?>
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