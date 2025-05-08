<?php
/**
 * Vinpearl Resort Nha Trang - Admin Booking Details
 * View detailed information for a specific booking
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Check if booking ID and type are provided
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    // Set error message
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Thiếu thông tin đặt chỗ' : 'Missing booking information'
    ];
    
    // Redirect to bookings page
    header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Get booking ID and type
$booking_id = (int)$_GET['id'];
$booking_type = $_GET['type'];

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không thể kết nối đến cơ sở dữ liệu' : 'Could not connect to database'
    ];
    
    header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Determine table and id field based on booking type
$table = '';
$id_field = '';
$item_id_field = '';
$item_table = '';
$name_field = '';

switch ($booking_type) {
    case 'room':
        $table = 'room_bookings';
        $id_field = 'room_booking_id';
        $item_id_field = 'room_id';
        $item_table = 'rooms';
        $name_field = 'name';
        break;
        
    case 'package':
        $table = 'package_bookings';
        $id_field = 'package_booking_id';
        $item_id_field = 'package_id';
        $item_table = 'packages';
        $name_field = 'name';
        break;
        
    case 'tour':
        $table = 'tour_bookings';
        $id_field = 'tour_booking_id';
        $item_id_field = 'tour_id';
        $item_table = 'tours';
        $name_field = 'name';
        break;
        
    default:
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Loại đặt chỗ không hợp lệ' : 'Invalid booking type'
        ];
        
        header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
        exit;
}

// Get booking details
$query = "
    SELECT b.*, 
           u.full_name as user_name, u.email as user_email, u.phone as user_phone,
           i.{$name_field}_en as name_en, i.{$name_field}_vi as name_vi, i.image_url
    FROM {$table} b
    JOIN users u ON b.user_id = u.id
    JOIN {$item_table} i ON b.{$item_id_field} = i.id
    WHERE b.{$id_field} = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không tìm thấy đặt chỗ' : 'Booking not found'
    ];
    
    header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

$booking = $result->fetch_assoc();

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

// Page title
$pageTitle = $language === 'vi' ? 'Chi tiết đặt chỗ' : 'Booking Details';
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <a href="index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span><?php echo $language === 'vi' ? 'Bảng điều khiển' : 'Dashboard'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="content.php">
                            <i class="fas fa-file-alt"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý nội dung' : 'Content Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php" class="active">
                            <i class="fas fa-calendar-check"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý đặt phòng' : 'Bookings Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="reviews.php">
                            <i class="fas fa-star"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý đánh giá' : 'Reviews Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="nearby.php">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $language === 'vi' ? 'Địa điểm gần đó' : 'Nearby Places'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý người dùng' : 'Users Management'; ?></span>
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
                        <a href="?type=<?php echo $booking_type; ?>&id=<?php echo $booking_id; ?>&lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                        <a href="?type=<?php echo $booking_type; ?>&id=<?php echo $booking_id; ?>&lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
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
                <!-- Back to bookings link -->
                <div class="mb-3">
                    <a href="bookings.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> <?php echo $language === 'vi' ? 'Quay lại danh sách' : 'Back to list'; ?>
                    </a>
                </div>
                
                <!-- Booking Details -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>
                            <?php echo $language === 'vi' ? 'Chi tiết đặt chỗ #' : 'Booking Details #'; ?><?php echo $booking_id; ?>
                            <span class="badge badge-<?php echo $booking['status']; ?>"><?php echo $statusLabels[$booking['status']]; ?></span>
                        </h2>
                        
                        <div class="card-header-actions">
                            <?php if ($booking['status'] === 'pending'): ?>
                                <a href="process-booking.php?action=confirm&type=<?php echo $booking_type; ?>&id=<?php echo $booking_id; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-success">
                                    <i class="fas fa-check"></i> <?php echo $language === 'vi' ? 'Xác nhận' : 'Confirm'; ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                <a href="process-booking.php?action=cancel&type=<?php echo $booking_type; ?>&id=<?php echo $booking_id; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-danger delete-btn">
                                    <i class="fas fa-times"></i> <?php echo $language === 'vi' ? 'Hủy' : 'Cancel'; ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] === 'confirmed'): ?>
                                <a href="process-booking.php?action=complete&type=<?php echo $booking_type; ?>&id=<?php echo $booking_id; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-success">
                                    <i class="fas fa-check-double"></i> <?php echo $language === 'vi' ? 'Hoàn thành' : 'Complete'; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="admin-card-body">
                        <div class="booking-details-grid">
                            <!-- Booking Information -->
                            <div class="booking-info-section">
                                <h3><?php echo $language === 'vi' ? 'Thông tin đặt chỗ' : 'Booking Information'; ?></h3>
                                
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'ID' : 'ID'; ?>:</span>
                                        <span class="info-value"><?php echo $booking_id; ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Loại' : 'Type'; ?>:</span>
                                        <span class="info-value"><?php echo $typeLabels[$booking_type]; ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?>:</span>
                                        <span class="info-value">
                                            <span class="badge badge-<?php echo $booking['status']; ?>"><?php echo $statusLabels[$booking['status']]; ?></span>
                                        </span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Ngày tạo' : 'Created Date'; ?>:</span>
                                        <span class="info-value"><?php echo formatDate($booking['created_at'], 'M d, Y H:i'); ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Cập nhật lần cuối' : 'Last Updated'; ?>:</span>
                                        <span class="info-value"><?php echo formatDate($booking['updated_at'], 'M d, Y H:i'); ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Tổng tiền' : 'Total Price'; ?>:</span>
                                        <span class="info-value"><?php echo formatCurrency($booking['total_price']); ?></span>
                                    </div>
                                    
                                    <?php if ($booking_type === 'room'): ?>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo $language === 'vi' ? 'Ngày nhận phòng' : 'Check-in Date'; ?>:</span>
                                            <span class="info-value"><?php echo formatDate($booking['check_in_date'], 'M d, Y'); ?></span>
                                        </div>
                                        
                                        <div class="info-item">
                                            <span class="info-label"><?php echo $language === 'vi' ? 'Ngày trả phòng' : 'Check-out Date'; ?>:</span>
                                            <span class="info-value"><?php echo formatDate($booking['check_out_date'], 'M d, Y'); ?></span>
                                        </div>
                                        
                                        <div class="info-item">
                                            <span class="info-label"><?php echo $language === 'vi' ? 'Số đêm' : 'Number of Nights'; ?>:</span>
                                            <span class="info-value">
                                                <?php 
                                                $check_in = new DateTime($booking['check_in_date']);
                                                $check_out = new DateTime($booking['check_out_date']);
                                                $nights = $check_in->diff($check_out)->days;
                                                echo $nights;
                                                ?>
                                            </span>
                                        </div>
                                    <?php elseif ($booking_type === 'package'): ?>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo $language === 'vi' ? 'Ngày bắt đầu' : 'Start Date'; ?>:</span>
                                            <span class="info-value"><?php echo formatDate($booking['start_date'], 'M d, Y'); ?></span>
                                        </div>
                                    <?php elseif ($booking_type === 'tour'): ?>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo $language === 'vi' ? 'Ngày tham gia' : 'Tour Date'; ?>:</span>
                                            <span class="info-value"><?php echo formatDate($booking['tour_date'], 'M d, Y'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Số khách' : 'Number of Guests'; ?>:</span>
                                        <span class="info-value"><?php echo $booking['guests']; ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Ghi chú đặc biệt' : 'Special Requests'; ?>:</span>
                                        <span class="info-value">
                                            <?php echo !empty($booking['special_requests']) ? nl2br(htmlspecialchars($booking['special_requests'])) : '<em>' . ($language === 'vi' ? 'Không có' : 'None') . '</em>'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Information -->
                            <div class="customer-info-section">
                                <h3><?php echo $language === 'vi' ? 'Thông tin khách hàng' : 'Customer Information'; ?></h3>
                                
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Tên' : 'Name'; ?>:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($booking['user_name']); ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Email' : 'Email'; ?>:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($booking['user_email']); ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label"><?php echo $language === 'vi' ? 'Điện thoại' : 'Phone'; ?>:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($booking['user_phone']); ?></span>
                                    </div>
                                    
                                    <div class="info-item customer-actions">
                                        <a href="users.php?id=<?php echo $booking['user_id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-outline">
                                            <i class="fas fa-user"></i> <?php echo $language === 'vi' ? 'Xem hồ sơ' : 'View Profile'; ?>
                                        </a>
                                        
                                        <a href="mailto:<?php echo htmlspecialchars($booking['user_email']); ?>" class="btn btn-sm btn-outline">
                                            <i class="fas fa-envelope"></i> <?php echo $language === 'vi' ? 'Gửi email' : 'Send Email'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Item Information -->
                            <div class="item-info-section">
                                <h3>
                                    <?php 
                                    switch ($booking_type) {
                                        case 'room':
                                            echo $language === 'vi' ? 'Thông tin phòng' : 'Room Information';
                                            break;
                                        case 'package':
                                            echo $language === 'vi' ? 'Thông tin gói dịch vụ' : 'Package Information';
                                            break;
                                        case 'tour':
                                            echo $language === 'vi' ? 'Thông tin tour' : 'Tour Information';
                                            break;
                                    }
                                    ?>
                                </h3>
                                
                                <div class="item-details">
                                    <div class="item-image">
                                        <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                                    </div>
                                    
                                    <div class="item-info">
                                        <h4><?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h4>
                                        
                                        <div class="info-grid">
                                            <div class="info-item">
                                                <span class="info-label"><?php echo $language === 'vi' ? 'ID' : 'ID'; ?>:</span>
                                                <span class="info-value"><?php echo $booking[$item_id_field]; ?></span>
                                            </div>
                                            
                                            <?php if ($booking_type === 'room'): ?>
                                                <div class="info-item">
                                                    <span class="info-label"><?php echo $language === 'vi' ? 'Giá mỗi đêm' : 'Price per Night'; ?>:</span>
                                                    <span class="info-value"><?php echo formatCurrency($booking['price_per_night']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <a href="<?php 
                                            switch ($booking_type) {
                                                case 'room':
                                                    echo 'rooms.php?action=edit&id=' . $booking['room_id'];
                                                    break;
                                                case 'package':
                                                    echo 'packages.php?action=edit&id=' . $booking['package_id'];
                                                    break;
                                                case 'tour':
                                                    echo 'tours.php?action=edit&id=' . $booking['tour_id'];
                                                    break;
                                            }
                                            echo $language === 'vi' ? '&lang=vi' : '';
                                        ?>" class="btn btn-sm btn-outline">
                                            <i class="fas fa-edit"></i> <?php echo $language === 'vi' ? 'Chỉnh sửa' : 'Edit'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Booking History -->
                <?php 
                // Get booking history (in a real implementation, we would have a booking_history table)
                // For now, we'll just display the current status
                ?>
                <div class="admin-card mt-4">
                    <div class="admin-card-header">
                        <h2><?php echo $language === 'vi' ? 'Lịch sử đặt chỗ' : 'Booking History'; ?></h2>
                    </div>
                    
                    <div class="admin-card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker done">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4><?php echo $language === 'vi' ? 'Đặt chỗ đã tạo' : 'Booking Created'; ?></h4>
                                    <p><?php echo formatDate($booking['created_at'], 'M d, Y H:i'); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($booking['status'] !== 'pending'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker <?php echo in_array($booking['status'], ['confirmed', 'completed']) ? 'done' : 'cancelled'; ?>">
                                        <?php if (in_array($booking['status'], ['confirmed', 'completed'])): ?>
                                            <i class="fas fa-check"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>
                                            <?php 
                                            if ($booking['status'] === 'confirmed') {
                                                echo $language === 'vi' ? 'Đặt chỗ đã xác nhận' : 'Booking Confirmed';
                                            } elseif ($booking['status'] === 'cancelled') {
                                                echo $language === 'vi' ? 'Đặt chỗ đã hủy' : 'Booking Cancelled';
                                            } elseif ($booking['status'] === 'completed') {
                                                echo $language === 'vi' ? 'Đặt chỗ đã xác nhận' : 'Booking Confirmed';
                                            }
                                            ?>
                                        </h4>
                                        <p><?php echo formatDate($booking['updated_at'], 'M d, Y H:i'); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] === 'completed'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker done">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4><?php echo $language === 'vi' ? 'Đặt chỗ đã hoàn thành' : 'Booking Completed'; ?></h4>
                                        <p><?php echo formatDate($booking['updated_at'], 'M d, Y H:i'); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>
