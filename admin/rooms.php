<?php
/**
 * Vinpearl Resort Nha Trang - Admin Rooms Management
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    die("Database connection failed");
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Filtering
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "
    SELECT r.*, 
           COUNT(DISTINCT rb.id) as booking_count
    FROM rooms r
    LEFT JOIN room_bookings rb ON r.id = rb.room_id
";

// Where conditions
$where_conditions = [];
$params = [];
$param_types = '';

// Since we don't have is_active column yet, we'll comment this out
// and implement it when the column exists
/*
if ($filter_status !== '') {
    $where_conditions[] = "r.is_active = ?";
    $params[] = $filter_status === 'active' ? 1 : 0;
    $param_types .= 'i';
}
*/

if ($search_term !== '') {
    $where_conditions[] = "(r.name_en LIKE ? OR r.name_vi LIKE ? OR r.type LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

// Add where clause if needed
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Group by and order
$query .= " GROUP BY r.id ORDER BY r.id DESC";

// Count total results for pagination
$count_query = "SELECT COUNT(*) as total FROM rooms";
if (!empty($where_conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $where_conditions);
}

$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result->fetch_assoc()['total'];

$total_pages = ceil($total_items / $limit);

// Add pagination to query
$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

// Execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$rooms = $result->fetch_all(MYSQLI_ASSOC);

// Stats
$total_rooms_query = "SELECT COUNT(*) as total FROM rooms";
// Since is_active column doesn't exist yet, assume all rooms are active

$total_rooms = $conn->query($total_rooms_query)->fetch_assoc()['total'];
$total_active = $total_rooms; // Assume all rooms are active for now

// Check for flash messages
$admin_message = isset($_SESSION['admin_message']) ? $_SESSION['admin_message'] : null;
unset($_SESSION['admin_message']); // Clear the message after retrieving

// Page title
$pageTitle = $language === 'vi' ? 'Quản lý phòng' : 'Rooms Management';
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
                        <a href="content.php" class="active">
                            <i class="fas fa-file-alt"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý nội dung' : 'Content Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php">
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
                    <nav class="admin-breadcrumb">
                        <a href="content.php"><?php echo $language === 'vi' ? 'Quản lý nội dung' : 'Content Management'; ?></a> / 
                        <span><?php echo $language === 'vi' ? 'Quản lý phòng' : 'Rooms Management'; ?></span>
                    </nav>
                </div>
                
                <div class="admin-user">
                    <div class="language-selector">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['lang' => 'en'])); ?>" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['lang' => 'vi'])); ?>" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
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
                <!-- Admin message display -->
                <?php if ($admin_message): ?>
                    <div class="alert alert-<?php echo $admin_message['type']; ?>">
                        <?php echo $admin_message['message']; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Rooms Stats -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Tổng số phòng' : 'Total Rooms'; ?></h3>
                            <div class="stat-value"><?php echo $total_rooms; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Phòng đang hiển thị' : 'Active Rooms'; ?></h3>
                            <div class="stat-value"><?php echo $total_active; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Rooms Tools -->
                <div class="admin-tools">
                    <div class="admin-tools-left">
                        <a href="room-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo $language === 'vi' ? 'Thêm phòng mới' : 'Add New Room'; ?>
                        </a>
                    </div>
                    
                    <div class="admin-tools-right">
                        <form action="" method="GET" class="admin-filter-form">
                            <?php if ($language === 'vi'): ?>
                                <input type="hidden" name="lang" value="vi">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value=""><?php echo $language === 'vi' ? 'Tất cả trạng thái' : 'All statuses'; ?></option>
                                        <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Đang hiển thị' : 'Active'; ?></option>
                                        <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Đã ẩn' : 'Inactive'; ?></option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="<?php echo $language === 'vi' ? 'Tìm phòng...' : 'Search rooms...'; ?>" class="form-control">
                                </div>
                                
                                <button type="submit" class="btn btn-outline">
                                    <i class="fas fa-filter"></i> <?php echo $language === 'vi' ? 'Lọc' : 'Filter'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Rooms Listing -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>
                            <?php echo $language === 'vi' ? 'Danh sách phòng' : 'Room List'; ?>
                            <?php if ($filter_status || $search_term): ?>
                                <span class="filtered-tag">
                                    <?php echo $language === 'vi' ? '(Đã lọc)' : '(Filtered)'; ?>
                                </span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    
                    <div class="admin-card-body">
                        <?php if (count($rooms) > 0): ?>
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo $language === 'vi' ? 'Ảnh' : 'Image'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Tên phòng' : 'Room Name'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Loại' : 'Type'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Giá' : 'Price'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Sức chứa' : 'Capacity'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Lượt đặt' : 'Bookings'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Thao tác' : 'Actions'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rooms as $room): ?>
                                            <tr>
                                                <td class="table-image">
                                                    <img src="<?php echo $room['image_url'] ? htmlspecialchars($room['image_url']) : '../assets/images/room-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($room['name_' . $language]); ?>">
                                                </td>
                                                <td>
                                                    <div class="room-name">
                                                        <?php echo htmlspecialchars($room['name_' . $language]); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($room['type']); ?></td>
                                                <td><?php echo number_format($room['price_per_night'], 2); ?> $</td>
                                                <td>
                                                    <i class="fas fa-user"></i> <?php echo $room['capacity']; ?>
                                                    <?php if ($room['bed_count'] > 0): ?>
                                                       | <i class="fas fa-bed"></i> <?php echo $room['bed_count']; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge"><?php echo $room['booking_count']; ?></span>
                                                </td>
                                                <td>
                                                    <?php /* Since is_active doesn't exist yet, assume all rooms are active */ ?>
                                                    <span class="status-badge active">
                                                        <i class="fas fa-check-circle"></i>
                                                        <?php echo $language === 'vi' ? 'Hiển thị' : 'Active'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="table-actions">
                                                        <a href="room-form.php?id=<?php echo $room['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-outline" title="<?php echo $language === 'vi' ? 'Chỉnh sửa' : 'Edit'; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <?php /* Since is_active doesn't exist yet, we'll only show the deactivate option */ ?>
                                                <a href="process-room.php?action=deactivate&id=<?php echo $room['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-outline" title="<?php echo $language === 'vi' ? 'Ẩn phòng' : 'Deactivate'; ?>">
                                                    <i class="fas fa-eye-slash"></i>
                                                </a>
                                                        
                                                        <?php if ($room['booking_count'] == 0): ?>
                                                            <a href="#" class="btn btn-sm btn-outline delete-btn" 
                                                               data-id="<?php echo $room['id']; ?>" 
                                                               data-name="<?php echo htmlspecialchars($room['name_' . $language]); ?>" 
                                                               title="<?php echo $language === 'vi' ? 'Xóa' : 'Delete'; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="admin-pagination">
                                    <?php
                                    $get_params = $_GET;
                                    unset($get_params['page']);
                                    $query_string = http_build_query($get_params);
                                    $base_url = '?' . $query_string . (empty($query_string) ? '' : '&') . 'page=';
                                    ?>
                                    
                                    <?php if ($page > 1): ?>
                                        <a href="<?php echo $base_url . ($page - 1); ?>" class="pagination-item">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $start_page + 4);
                                    
                                    if ($end_page - $start_page < 4 && $start_page > 1) {
                                        $start_page = max(1, $end_page - 4);
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <a href="<?php echo $base_url . $i; ?>" class="pagination-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="<?php echo $base_url . ($page + 1); ?>" class="pagination-item">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="no-results">
                                <i class="fas fa-bed"></i>
                                <p><?php echo $language === 'vi' ? 'Không tìm thấy phòng nào.' : 'No rooms found.'; ?></p>
                                <?php if ($filter_status || $search_term): ?>
                                    <a href="rooms.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
                                        <?php echo $language === 'vi' ? 'Xem tất cả phòng' : 'View all rooms'; ?>
                                    </a>
                                <?php else: ?>
                                    <a href="room-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-primary">
                                        <?php echo $language === 'vi' ? 'Thêm phòng đầu tiên' : 'Add your first room'; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="admin-modal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h2><?php echo $language === 'vi' ? 'Xác nhận xóa' : 'Confirm Deletion'; ?></h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="admin-modal-body">
                <p><?php echo $language === 'vi' ? 'Bạn có chắc chắn muốn xóa phòng' : 'Are you sure you want to delete room'; ?> "<span id="roomToDelete"></span>"?</p>
                <p class="warning"><?php echo $language === 'vi' ? 'Hành động này không thể hoàn tác.' : 'This action cannot be undone.'; ?></p>
            </div>
            <div class="admin-modal-footer">
                <a href="#" id="confirmDelete" class="btn btn-danger">
                    <?php echo $language === 'vi' ? 'Xóa phòng' : 'Delete Room'; ?>
                </a>
                <button class="btn btn-outline close-modal">
                    <?php echo $language === 'vi' ? 'Hủy' : 'Cancel'; ?>
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const modal = document.getElementById('deleteModal');
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const closeModalButtons = document.querySelectorAll('.close-modal');
            const roomToDeleteSpan = document.getElementById('roomToDelete');
            const confirmDeleteButton = document.getElementById('confirmDelete');
            
            // Open modal on delete button click
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const roomId = this.getAttribute('data-id');
                    const roomName = this.getAttribute('data-name');
                    
                    roomToDeleteSpan.textContent = roomName;
                    confirmDeleteButton.href = `process-room.php?action=delete&id=${roomId}<?php echo $language === 'vi' ? '&lang=vi' : ''; ?>`;
                    
                    modal.classList.add('show');
                });
            });
            
            // Close modal on close button click
            closeModalButtons.forEach(button => {
                button.addEventListener('click', function() {
                    modal.classList.remove('show');
                });
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
