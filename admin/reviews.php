<?php
/**
 * Vinpearl Resort Nha Trang - Admin Reviews Management
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    die("Database connection failed");
}

// Base query
$query = "
    SELECT r.*, 
           u.full_name as user_name, 
           CASE 
               WHEN r.type = 'room' THEN (SELECT CONCAT(name_en, '|', name_vi) FROM rooms WHERE id = r.item_id)
               WHEN r.type = 'package' THEN (SELECT CONCAT(name_en, '|', name_vi) FROM packages WHERE id = r.item_id)
               WHEN r.type = 'tour' THEN (SELECT CONCAT(name_en, '|', name_vi) FROM tours WHERE id = r.item_id)
           END as item_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE 1=1
";

// Add filters to query
$params = [];
$param_types = '';

if (!empty($status_filter)) {
    if ($status_filter === 'approved') {
        $query .= " AND r.is_approved = 1";
    } elseif ($status_filter === 'pending') {
        $query .= " AND r.is_approved = 0";
    }
}

if (!empty($type_filter)) {
    $query .= " AND r.type = ?";
    $param_types .= 's';
    $params[] = $type_filter;
}

if (!empty($search_term)) {
    $query .= " AND (r.title_en LIKE ? OR r.title_vi LIKE ? OR r.content_en LIKE ? OR r.content_vi LIKE ? OR u.full_name LIKE ?)";
    $param_types .= 'sssss';
    $search_term_param = "%{$search_term}%";
    $params[] = $search_term_param;
    $params[] = $search_term_param;
    $params[] = $search_term_param;
    $params[] = $search_term_param;
    $params[] = $search_term_param;
}

// Count total reviews for pagination
$count_query = str_replace("r.*, u.full_name as user_name, CASE", "COUNT(*) as total", $query);
$count_query = preg_replace('/SELECT.*?FROM/s', 'SELECT COUNT(*) as total FROM', $query);

$stmt = $conn->prepare($count_query);

if (!empty($params)) {
    $bind_params = array($param_types);
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_params);
}

$stmt->execute();
$count_result = $stmt->get_result();
$total_count = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_count / $per_page);

// Get reviews with pagination
$query .= " ORDER BY r.created_at DESC LIMIT ?, ?";
$param_types .= 'ii';
$params[] = $offset;
$params[] = $per_page;

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $bind_params = array($param_types);
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_params);
}

$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Process item names
foreach ($reviews as &$review) {
    if (!empty($review['item_name'])) {
        list($name_en, $name_vi) = explode('|', $review['item_name']);
        $review['item_name_en'] = $name_en;
        $review['item_name_vi'] = $name_vi;
    } else {
        $review['item_name_en'] = 'Unknown';
        $review['item_name_vi'] = 'Không xác định';
    }
}

// Get review stats
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending
    FROM reviews
";

$stmt = $conn->prepare($stats_query);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Type labels
$typeLabels = [
    'room' => $language === 'vi' ? 'Phòng' : 'Room',
    'package' => $language === 'vi' ? 'Gói dịch vụ' : 'Package',
    'tour' => $language === 'vi' ? 'Tour' : 'Tour'
];

// Page title
$pageTitle = $language === 'vi' ? 'Quản lý đánh giá' : 'Reviews Management';
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
                        <a href="bookings.php">
                            <i class="fas fa-calendar-check"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý đặt phòng' : 'Bookings Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="reviews.php" class="active">
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
                    <div class="search-bar">
                        <form action="reviews.php" method="GET" class="d-flex align-center">
                            <?php if(!empty($status_filter)): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <?php if(!empty($type_filter)): ?>
                                <input type="hidden" name="type" value="<?php echo htmlspecialchars($type_filter); ?>">
                            <?php endif; ?>
                            <?php if($language === 'vi'): ?>
                                <input type="hidden" name="lang" value="vi">
                            <?php endif; ?>
                            <input type="text" name="search" class="search-input" placeholder="<?php echo $language === 'vi' ? 'Tìm kiếm đánh giá...' : 'Search reviews...'; ?>" value="<?php echo htmlspecialchars($search_term); ?>">
                            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    
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
                <!-- Review Stats -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Tổng đánh giá' : 'Total Reviews'; ?></h3>
                            <div class="stat-value"><?php echo $stats['total']; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Đã duyệt' : 'Approved'; ?></h3>
                            <div class="stat-value"><?php echo $stats['approved']; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Chờ duyệt' : 'Pending'; ?></h3>
                            <div class="stat-value"><?php echo $stats['pending']; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Admin Filters -->
                <div class="admin-filters">
                    <div class="filter-item">
                        <label class="filter-label"><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?>:</label>
                        <select onchange="window.location=this.value;">
                            <option value="reviews.php?<?php echo !empty($type_filter) ? 'type=' . urlencode($type_filter) . '&' : ''; ?><?php echo !empty($search_term) ? 'search=' . urlencode($search_term) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" <?php echo empty($status_filter) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></option>
                            <option value="reviews.php?status=pending<?php echo !empty($type_filter) ? '&type=' . urlencode($type_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Chờ duyệt' : 'Pending'; ?></option>
                            <option value="reviews.php?status=approved<?php echo !empty($type_filter) ? '&type=' . urlencode($type_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Đã duyệt' : 'Approved'; ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label class="filter-label"><?php echo $language === 'vi' ? 'Loại' : 'Type'; ?>:</label>
                        <select onchange="window.location=this.value;">
                            <option value="reviews.php?<?php echo !empty($status_filter) ? 'status=' . urlencode($status_filter) . '&' : ''; ?><?php echo !empty($search_term) ? 'search=' . urlencode($search_term) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" <?php echo empty($type_filter) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></option>
                            <option value="reviews.php?type=room<?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $type_filter === 'room' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Phòng' : 'Room'; ?></option>
                            <option value="reviews.php?type=package<?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $type_filter === 'package' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Package'; ?></option>
                            <option value="reviews.php?type=tour<?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $type_filter === 'tour' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tour' : 'Tour'; ?></option>
                        </select>
                    </div>
                </div>
                
                <!-- Reviews List -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><?php echo $language === 'vi' ? 'Danh sách đánh giá' : 'Reviews List'; ?></h2>
                        <div class="card-header-actions">
                            <span class="results-count"><?php echo $total_count; ?> <?php echo $language === 'vi' ? 'kết quả' : 'results'; ?></span>
                        </div>
                    </div>
                    
                    <div class="admin-card-body">
                        <?php if (empty($reviews)): ?>
                            <div class="empty-state">
                                <i class="fas fa-comments"></i>
                                <h3><?php echo $language === 'vi' ? 'Không có đánh giá nào' : 'No reviews found'; ?></h3>
                                <p><?php echo $language === 'vi' ? 'Không tìm thấy đánh giá nào phù hợp với tiêu chí tìm kiếm.' : 'No reviews matching your search criteria were found.'; ?></p>
                            </div>
                        <?php else: ?>
                            <div class="reviews-list">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item <?php echo $review['is_approved'] ? 'approved' : 'pending'; ?>">
                                        <div class="review-header">
                                            <div class="review-meta">
                                                <div class="review-rating">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <?php if($i <= $review['rating']): ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                                <div class="review-type">
                                                    <span class="badge"><?php echo $typeLabels[$review['type']]; ?></span>
                                                </div>
                                                <div class="review-status">
                                                    <?php if($review['is_approved']): ?>
                                                        <span class="badge badge-confirmed"><?php echo $language === 'vi' ? 'Đã duyệt' : 'Approved'; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-pending"><?php echo $language === 'vi' ? 'Chờ duyệt' : 'Pending'; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="review-actions">
                                                <?php if(!$review['is_approved']): ?>
                                                    <a href="process-review.php?action=approve&id=<?php echo $review['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> <?php echo $language === 'vi' ? 'Duyệt' : 'Approve'; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="process-review.php?action=unapprove&id=<?php echo $review['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-undo"></i> <?php echo $language === 'vi' ? 'Hủy duyệt' : 'Unapprove'; ?>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="process-review.php?action=delete&id=<?php echo $review['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-danger delete-btn">
                                                    <i class="fas fa-trash"></i> <?php echo $language === 'vi' ? 'Xóa' : 'Delete'; ?>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="review-content">
                                            <h3><?php echo htmlspecialchars($review[$language === 'vi' ? 'title_vi' : 'title_en']); ?></h3>
                                            <p class="review-text"><?php echo nl2br(htmlspecialchars($review[$language === 'vi' ? 'content_vi' : 'content_en'])); ?></p>
                                        </div>
                                        
                                        <div class="review-footer">
                                            <div class="review-item-info">
                                                <strong><?php echo $language === 'vi' ? 'Đánh giá cho' : 'Review for'; ?>:</strong> 
                                                <?php echo htmlspecialchars($review[$language === 'vi' ? 'item_name_vi' : 'item_name_en']); ?>
                                            </div>
                                            <div class="review-user-info">
                                                <strong><?php echo $language === 'vi' ? 'Người đánh giá' : 'Reviewer'; ?>:</strong> 
                                                <?php echo htmlspecialchars($review['user_name']); ?>
                                            </div>
                                            <div class="review-date">
                                                <strong><?php echo $language === 'vi' ? 'Ngày đánh giá' : 'Review Date'; ?>:</strong> 
                                                <?php echo formatDate($review['created_at'], 'M d, Y'); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <div class="page-item">
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">&laquo; <?php echo $language === 'vi' ? 'Trước' : 'Previous'; ?></a>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <div class="page-item">
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            </div>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <div class="page-item">
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link"><?php echo $language === 'vi' ? 'Tiếp' : 'Next'; ?> &raquo;</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>
