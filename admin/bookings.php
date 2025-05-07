<?php
/**
 * Vinpearl Resort Nha Trang - Admin Bookings Management
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get current user
$currentUser = getCurrentUser();

// Connect to the database
$conn = connectDatabase();
if (!$conn) {
    die("Database connection failed");
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get bookings with filters applied
$bookings = [];
$total_bookings = 0;

// Build query parts based on filters
$search_condition = '';
if (!empty($search_term)) {
    $search_term = "%{$search_term}%";
    $search_condition = " AND (u.full_name LIKE ? OR u.email LIKE ?)";
}

// Count total bookings (for pagination)
$count_query_parts = [];

// Room bookings count
$count_room_query = "SELECT COUNT(*) as count FROM room_bookings rb 
                      JOIN users u ON rb.user_id = u.id
                      JOIN rooms r ON rb.room_id = r.id
                      WHERE 1=1";
if (!empty($status_filter)) {
    $count_room_query .= " AND rb.status = ?";
}
if (!empty($search_term)) {
    $count_room_query .= $search_condition;
}
$count_query_parts[] = $count_room_query;

// Package bookings count
$count_package_query = "SELECT COUNT(*) as count FROM package_bookings pb 
                         JOIN users u ON pb.user_id = u.id
                         JOIN packages p ON pb.package_id = p.id
                         WHERE 1=1";
if (!empty($status_filter)) {
    $count_package_query .= " AND pb.status = ?";
}
if (!empty($search_term)) {
    $count_package_query .= $search_condition;
}
$count_query_parts[] = $count_package_query;

// Tour bookings count
$count_tour_query = "SELECT COUNT(*) as count FROM tour_bookings tb 
                      JOIN users u ON tb.user_id = u.id
                      JOIN tours t ON tb.tour_id = t.id
                      WHERE 1=1";
if (!empty($status_filter)) {
    $count_tour_query .= " AND tb.status = ?";
}
if (!empty($search_term)) {
    $count_tour_query .= $search_condition;
}
$count_query_parts[] = $count_tour_query;

// Apply type filter to count query
if (!empty($type_filter)) {
    switch ($type_filter) {
        case 'room':
            $count_query_parts = [$count_query_parts[0]];
            break;
        case 'package':
            $count_query_parts = [$count_query_parts[1]];
            break;
        case 'tour':
            $count_query_parts = [$count_query_parts[2]];
            break;
    }
}

// Execute count queries
foreach ($count_query_parts as $idx => $count_query) {
    $stmt = $conn->prepare($count_query);
    
    // Bind parameters based on filters
    $param_idx = 1;
    $param_types = '';
    $params = [];
    
    if (!empty($status_filter)) {
        $param_types .= 's';
        $params[] = $status_filter;
    }
    
    if (!empty($search_term)) {
        $param_types .= 'ss';
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (!empty($params)) {
        $ref_params = [];
        $ref_params[] = &$param_types;
        
        foreach ($params as $key => $value) {
            $ref_params[] = &$params[$key];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $ref_params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $count_data = $result->fetch_assoc();
    $total_bookings += $count_data['count'];
}

// Calculate pagination
$total_pages = ceil($total_bookings / $per_page);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
}

// Prepare query parts for data retrieval
$query_parts = [];

// Room bookings query
$room_query = "SELECT rb.*, r.name_en, r.name_vi, r.image_url, u.full_name as user_name, u.email as user_email, 'room' as booking_type
               FROM room_bookings rb 
               JOIN users u ON rb.user_id = u.id
               JOIN rooms r ON rb.room_id = r.id
               WHERE 1=1";
if (!empty($status_filter)) {
    $room_query .= " AND rb.status = ?";
}
if (!empty($search_term)) {
    $room_query .= $search_condition;
}
$query_parts[] = $room_query;

// Package bookings query
$package_query = "SELECT pb.*, p.name_en, p.name_vi, p.image_url, u.full_name as user_name, u.email as user_email, 'package' as booking_type
                  FROM package_bookings pb 
                  JOIN users u ON pb.user_id = u.id
                  JOIN packages p ON pb.package_id = p.id
                  WHERE 1=1";
if (!empty($status_filter)) {
    $package_query .= " AND pb.status = ?";
}
if (!empty($search_term)) {
    $package_query .= $search_condition;
}
$query_parts[] = $package_query;

// Tour bookings query
$tour_query = "SELECT tb.*, t.name_en, t.name_vi, t.image_url, u.full_name as user_name, u.email as user_email, 'tour' as booking_type
               FROM tour_bookings tb 
               JOIN users u ON tb.user_id = u.id
               JOIN tours t ON tb.tour_id = t.id
               WHERE 1=1";
if (!empty($status_filter)) {
    $tour_query .= " AND tb.status = ?";
}
if (!empty($search_term)) {
    $tour_query .= $search_condition;
}
$query_parts[] = $tour_query;

// Apply type filter
if (!empty($type_filter)) {
    switch ($type_filter) {
        case 'room':
            $query_parts = [$query_parts[0]];
            break;
        case 'package':
            $query_parts = [$query_parts[1]];
            break;
        case 'tour':
            $query_parts = [$query_parts[2]];
            break;
    }
}

// Create UNION query with ordering and pagination
$main_query = implode(" UNION ", $query_parts);
$main_query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

// Execute the main query
$stmt = $conn->prepare($main_query);

// Bind parameters based on filters
$param_idx = 1;
$param_types = '';
$params = [];

// Add parameters for each subquery
foreach ($query_parts as $idx => $query) {
    if (!empty($status_filter)) {
        $param_types .= 's';
        $params[] = $status_filter;
    }
    
    if (!empty($search_term)) {
        $param_types .= 'ss';
        $params[] = $search_term;
        $params[] = $search_term;
    }
}

// Add pagination parameters
$param_types .= 'ii';
$params[] = $per_page;
$params[] = $offset;

// Bind all parameters
if (!empty($params)) {
    $ref_params = [];
    $ref_params[] = &$param_types;
    
    foreach ($params as $key => $value) {
        $ref_params[] = &$params[$key];
    }
    
    call_user_func_array([$stmt, 'bind_param'], $ref_params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($booking = $result->fetch_assoc()) {
    $bookings[] = $booking;
}

// Get booking statistics
$stats = [];

// Room booking stats
$stmt = $conn->prepare("SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
                        FROM room_bookings");
$stmt->execute();
$room_stats = $stmt->get_result()->fetch_assoc();
$stats['room'] = $room_stats;

// Package booking stats
$stmt = $conn->prepare("SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
                        FROM package_bookings");
$stmt->execute();
$package_stats = $stmt->get_result()->fetch_assoc();
$stats['package'] = $package_stats;

// Tour booking stats
$stmt = $conn->prepare("SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
                        FROM tour_bookings");
$stmt->execute();
$tour_stats = $stmt->get_result()->fetch_assoc();
$stats['tour'] = $tour_stats;

// Calculate totals
$stats['total'] = [
    'total' => $stats['room']['total'] + $stats['package']['total'] + $stats['tour']['total'],
    'confirmed' => $stats['room']['confirmed'] + $stats['package']['confirmed'] + $stats['tour']['confirmed'],
    'pending' => $stats['room']['pending'] + $stats['package']['pending'] + $stats['tour']['pending'],
    'completed' => $stats['room']['completed'] + $stats['package']['completed'] + $stats['tour']['completed'],
    'cancelled' => $stats['room']['cancelled'] + $stats['package']['cancelled'] + $stats['tour']['cancelled']
];

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
$pageTitle = $language === 'vi' ? 'Quản Lý Đặt Chỗ' : 'Manage Bookings';

// Active page for navigation
$activePage = 'bookings';
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Vinpearl Resort Nha Trang</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Vinpearl Admin</h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Bảng điều khiển' : 'Dashboard'; ?></a></li>
                    <li><a href="rooms.php" class="<?php echo $activePage === 'rooms' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                    <li><a href="packages.php" class="<?php echo $activePage === 'packages' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                    <li><a href="tours.php" class="<?php echo $activePage === 'tours' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                    <li><a href="bookings.php" class="<?php echo $activePage === 'bookings' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Đặt chỗ' : 'Bookings'; ?></a></li>
                    <li><a href="users.php" class="<?php echo $activePage === 'users' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Người dùng' : 'Users'; ?></a></li>
                    <li><a href="reviews.php" class="<?php echo $activePage === 'reviews' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Đánh giá' : 'Reviews'; ?></a></li>
                    <li><a href="nearby.php" class="<?php echo $activePage === 'nearby' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby Places'; ?></a></li>
                    <li><a href="content.php" class="<?php echo $activePage === 'content' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Nội dung' : 'Content'; ?></a></li>
                    <li><a href="settings.php" class="<?php echo $activePage === 'settings' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Cài đặt' : 'Settings'; ?></a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-search">
                    <form action="bookings.php" method="GET">
                        <?php if (!empty($status_filter)): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                        <?php endif; ?>
                        <?php if (!empty($type_filter)): ?>
                            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type_filter); ?>">
                        <?php endif; ?>
                        <?php if ($language === 'vi'): ?>
                            <input type="hidden" name="lang" value="vi">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="<?php echo $language === 'vi' ? 'Tìm theo tên khách hàng hoặc email...' : 'Search by customer name or email...'; ?>" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit"><i class="icon-search"></i></button>
                    </form>
                </div>
                
                <div class="header-actions">
                    <div class="language-selector">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['lang' => 'en'])); ?>" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['lang' => 'vi'])); ?>" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
                    </div>
                    
                    <div class="user-menu">
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <div class="dropdown-menu">
                            <a href="../profile.php"><?php echo $language === 'vi' ? 'Hồ sơ' : 'Profile'; ?></a>
                            <a href="../logout.php"><?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="admin-content">
                <div class="content-header">
                    <h1><?php echo $pageTitle; ?></h1>
                </div>
                
                <!-- Booking Stats -->
                <div class="booking-stats">
                    <div class="stat-tabs">
                        <a href="bookings.php?<?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" class="stat-tab <?php echo empty($status_filter) && empty($type_filter) ? 'active' : ''; ?>">
                            <span class="stat-label"><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></span>
                            <span class="stat-number"><?php echo $stats['total']['total']; ?></span>
                        </a>
                        
                        <a href="bookings.php?status=pending<?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="stat-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                            <span class="stat-label"><?php echo $language === 'vi' ? 'Đang chờ' : 'Pending'; ?></span>
                            <span class="stat-number"><?php echo $stats['total']['pending']; ?></span>
                        </a>
                        
                        <a href="bookings.php?status=confirmed<?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="stat-tab <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">
                            <span class="stat-label"><?php echo $language === 'vi' ? 'Đã xác nhận' : 'Confirmed'; ?></span>
                            <span class="stat-number"><?php echo $stats['total']['confirmed']; ?></span>
                        </a>
                        
                        <a href="bookings.php?status=completed<?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="stat-tab <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                            <span class="stat-label"><?php echo $language === 'vi' ? 'Đã hoàn thành' : 'Completed'; ?></span>
                            <span class="stat-number"><?php echo $stats['total']['completed']; ?></span>
                        </a>
                        
                        <a href="bookings.php?status=cancelled<?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="stat-tab <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                            <span class="stat-label"><?php echo $language === 'vi' ? 'Đã hủy' : 'Cancelled'; ?></span>
                            <span class="stat-number"><?php echo $stats['total']['cancelled']; ?></span>
                        </a>
                    </div>
                </div>
                
                <!-- Booking Type Filter -->
                <div class="filter-tabs">
                    <a href="bookings.php?<?php echo !empty($status_filter) ? 'status=' . urlencode($status_filter) . '&' : ''; ?><?php echo !empty($search_term) ? 'search=' . urlencode($search_term) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" class="filter-tab <?php echo empty($type_filter) ? 'active' : ''; ?>">
                        <?php echo $language === 'vi' ? 'Tất cả các loại' : 'All Types'; ?>
                    </a>
                    
                    <a href="bookings.php?type=room<?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-tab <?php echo $type_filter === 'room' ? 'active' : ''; ?>">
                        <?php echo $typeLabels['room']; ?> <span class="filter-count">(<?php echo $stats['room']['total']; ?>)</span>
                    </a>
                    
                    <a href="bookings.php?type=package<?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-tab <?php echo $type_filter === 'package' ? 'active' : ''; ?>">
                        <?php echo $typeLabels['package']; ?> <span class="filter-count">(<?php echo $stats['package']['total']; ?>)</span>
                    </a>
                    
                    <a href="bookings.php?type=tour<?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="filter-tab <?php echo $type_filter === 'tour' ? 'active' : ''; ?>">
                        <?php echo $typeLabels['tour']; ?> <span class="filter-count">(<?php echo $stats['tour']['total']; ?>)</span>
                    </a>
                </div>
                
                <!-- Bookings Table -->
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $language === 'vi' ? 'ID' : 'ID'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Loại' : 'Type'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Tên dịch vụ' : 'Service Name'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Khách hàng' : 'Customer'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Ngày tạo' : 'Created Date'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Ngày đến' : 'Arrival Date'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Số khách' : 'Guests'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Giá' : 'Price'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?></th>
                                <th><?php echo $language === 'vi' ? 'Thao tác' : 'Actions'; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="10" class="no-data"><?php echo $language === 'vi' ? 'Không tìm thấy đặt chỗ nào' : 'No bookings found'; ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            // Determine the ID field based on booking type
                                            switch ($booking['booking_type']) {
                                                case 'room':
                                                    echo $booking['room_booking_id'];
                                                    break;
                                                case 'package':
                                                    echo $booking['package_booking_id'];
                                                    break;
                                                case 'tour':
                                                    echo $booking['tour_booking_id'];
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $typeLabels[$booking['booking_type']]; ?></td>
                                        <td><?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?></td>
                                        <td>
                                            <div class="user-info">
                                                <span><?php echo htmlspecialchars($booking['user_name']); ?></span>
                                                <small><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo formatDate($booking['created_at'], 'M d, Y'); ?></td>
                                        <td>
                                            <?php 
                                            // Display arrival date based on booking type
                                            switch ($booking['booking_type']) {
                                                case 'room':
                                                    echo formatDate($booking['check_in_date'], 'M d, Y');
                                                    break;
                                                case 'package':
                                                    echo formatDate($booking['start_date'], 'M d, Y');
                                                    break;
                                                case 'tour':
                                                    echo formatDate($booking['tour_date'], 'M d, Y');
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $booking['guests']; ?></td>
                                        <td><?php echo formatCurrency($booking['total_price']); ?></td>
                                        <td><span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo $statusLabels[$booking['status']]; ?></span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="booking-details.php?type=<?php echo $booking['booking_type']; ?>&id=<?php echo ($booking['booking_type'] === 'room' ? $booking['room_booking_id'] : ($booking['booking_type'] === 'package' ? $booking['package_booking_id'] : $booking['tour_booking_id'])); ?>" class="btn btn-sm btn-icon" title="<?php echo $language === 'vi' ? 'Xem' : 'View'; ?>"><i class="icon-eye"></i></a>
                                                
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <a href="booking-confirm.php?type=<?php echo $booking['booking_type']; ?>&id=<?php echo ($booking['booking_type'] === 'room' ? $booking['room_booking_id'] : ($booking['booking_type'] === 'package' ? $booking['package_booking_id'] : $booking['tour_booking_id'])); ?>" class="btn btn-sm btn-icon btn-success" title="<?php echo $language === 'vi' ? 'Xác nhận' : 'Confirm'; ?>"><i class="icon-check"></i></a>
                                                <?php endif; ?>
                                                
                                                <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                                    <a href="booking-cancel.php?type=<?php echo $booking['booking_type']; ?>&id=<?php echo ($booking['booking_type'] === 'room' ? $booking['room_booking_id'] : ($booking['booking_type'] === 'package' ? $booking['package_booking_id'] : $booking['tour_booking_id'])); ?>" class="btn btn-sm btn-icon btn-danger" title="<?php echo $language === 'vi' ? 'Hủy' : 'Cancel'; ?>"><i class="icon-cancel"></i></a>
                                                <?php endif; ?>
                                                
                                                <?php if ($booking['status'] === 'confirmed'): ?>
                                                    <a href="booking-complete.php?type=<?php echo $booking['booking_type']; ?>&id=<?php echo ($booking['booking_type'] === 'room' ? $booking['room_booking_id'] : ($booking['booking_type'] === 'package' ? $booking['package_booking_id'] : $booking['tour_booking_id'])); ?>" class="btn btn-sm btn-icon btn-success" title="<?php echo $language === 'vi' ? 'Hoàn thành' : 'Complete'; ?>"><i class="icon-done"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        // Previous page link
                        if ($page > 1) {
                            echo '<a href="?page=' . ($page - 1) . 
                                 (!empty($type_filter) ? '&type=' . urlencode($type_filter) : '') . 
                                 (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . 
                                 (!empty($search_term) ? '&search=' . urlencode($search_term) : '') . 
                                 ($language === 'vi' ? '&lang=vi' : '') . 
                                 '" class="page-link">&laquo; ' . ($language === 'vi' ? 'Trước' : 'Prev') . '</a>';
                        }
                        
                        // Page numbers
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<a href="?page=' . $i . 
                                 (!empty($type_filter) ? '&type=' . urlencode($type_filter) : '') . 
                                 (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . 
                                 (!empty($search_term) ? '&search=' . urlencode($search_term) : '') . 
                                 ($language === 'vi' ? '&lang=vi' : '') . 
                                 '" class="page-link ' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
                        }
                        
                        // Next page link
                        if ($page < $total_pages) {
                            echo '<a href="?page=' . ($page + 1) . 
                                 (!empty($type_filter) ? '&type=' . urlencode($type_filter) : '') . 
                                 (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . 
                                 (!empty($search_term) ? '&search=' . urlencode($search_term) : '') . 
                                 ($language === 'vi' ? '&lang=vi' : '') . 
                                 '" class="page-link">' . ($language === 'vi' ? 'Tiếp' : 'Next') . ' &raquo;</a>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html> 