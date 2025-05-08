<?php
/**
 * Vinpearl Resort Nha Trang - Admin Nearby Places Management
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get filter parameters
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
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
    SELECT * FROM nearby_places WHERE 1=1
";

// Add filters to query
$params = [];
$param_types = '';

if (!empty($category_filter)) {
    $query .= " AND category = ?";
    $param_types .= 's';
    $params[] = $category_filter;
}

if (!empty($status_filter)) {
    // Since is_active column doesn't exist yet, we'll comment this out
    /*
    if ($status_filter === 'active') {
        $query .= " AND is_active = 1";
    } elseif ($status_filter === 'inactive') {
        $query .= " AND is_active = 0";
    }
    */
    // Add a notice that filter isn't working yet
    $status_filter = '';
}

if (!empty($search_term)) {
    $query .= " AND (name_en LIKE ? OR name_vi LIKE ? OR description_en LIKE ? OR description_vi LIKE ?)";
    $param_types .= 'ssss';
    $search_term_param = "%{$search_term}%";
    $params[] = $search_term_param;
    $params[] = $search_term_param;
    $params[] = $search_term_param;
    $params[] = $search_term_param;
}

// Count total places for pagination
$count_query = str_replace("*", "COUNT(*) as total", $query);

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

// Get places with pagination
$query .= " ORDER BY name_en ASC LIMIT ?, ?";
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
$places = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get category stats
$category_query = "
    SELECT 
        category,
        COUNT(*) as count
    FROM nearby_places
    GROUP BY category
";

$stmt = $conn->prepare($category_query);
$stmt->execute();
$categories_result = $stmt->get_result();
$categories = [];

while ($row = $categories_result->fetch_assoc()) {
    $categories[$row['category']] = $row['count'];
}

// Get all status stats
$status_query = "
    SELECT 
        COUNT(*) as total,
        COUNT(*) as active,
        0 as inactive
    FROM nearby_places
";

// Since the is_active column doesn't exist yet, we'll just count all places as active
// This is a temporary solution until the column is added to the schema

$stmt = $conn->prepare($status_query);
$stmt->execute();
$status_result = $stmt->get_result();
$status_stats = $status_result->fetch_assoc();

// Category labels
$categoryLabels = [
    'attraction' => $language === 'vi' ? 'Điểm tham quan' : 'Attraction',
    'restaurant' => $language === 'vi' ? 'Nhà hàng' : 'Restaurant',
    'shopping' => $language === 'vi' ? 'Mua sắm' : 'Shopping',
    'beach' => $language === 'vi' ? 'Bãi biển' : 'Beach',
    'nature' => $language === 'vi' ? 'Thiên nhiên' : 'Nature',
    'entertainment' => $language === 'vi' ? 'Giải trí' : 'Entertainment'
];

// Page title
$pageTitle = $language === 'vi' ? 'Quản lý địa điểm gần đó' : 'Nearby Places Management';
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
                        <a href="reviews.php">
                            <i class="fas fa-star"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý đánh giá' : 'Reviews Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="nearby.php" class="active">
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
                        <form action="nearby.php" method="GET" class="d-flex align-center">
                            <?php if(!empty($category_filter)): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                            <?php endif; ?>
                            <?php if(!empty($status_filter)): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <?php if($language === 'vi'): ?>
                                <input type="hidden" name="lang" value="vi">
                            <?php endif; ?>
                            <input type="text" name="search" class="search-input" placeholder="<?php echo $language === 'vi' ? 'Tìm kiếm địa điểm...' : 'Search places...'; ?>" value="<?php echo htmlspecialchars($search_term); ?>">
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
                <!-- Place Type Stats -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Tổng địa điểm' : 'Total Places'; ?></h3>
                            <div class="stat-value"><?php echo $status_stats['total']; ?></div>
                        </div>
                    </div>
                    
                    <?php foreach ($categoryLabels as $key => $label): ?>
                        <div class="admin-stat-card">
                            <div class="stat-icon">
                                <i class="<?php
                                    switch ($key) {
                                        case 'attraction': echo 'fas fa-monument'; break;
                                        case 'restaurant': echo 'fas fa-utensils'; break;
                                        case 'shopping': echo 'fas fa-shopping-bag'; break;
                                        case 'beach': echo 'fas fa-umbrella-beach'; break;
                                        case 'nature': echo 'fas fa-leaf'; break;
                                        case 'entertainment': echo 'fas fa-ticket-alt'; break;
                                        default: echo 'fas fa-map-marker-alt';
                                    }
                                ?>"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $label; ?></h3>
                                <div class="stat-value"><?php echo isset($categories[$key]) ? $categories[$key] : 0; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Admin Filters -->
                <div class="admin-filters">
                    <div class="filter-item">
                        <label class="filter-label"><?php echo $language === 'vi' ? 'Loại' : 'Category'; ?>:</label>
                        <select onchange="window.location=this.value;">
                            <option value="nearby.php?<?php echo !empty($status_filter) ? 'status=' . urlencode($status_filter) . '&' : ''; ?><?php echo !empty($search_term) ? 'search=' . urlencode($search_term) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" <?php echo empty($category_filter) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></option>
                            <?php foreach ($categoryLabels as $key => $label): ?>
                                <option value="nearby.php?category=<?php echo $key; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $category_filter === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label class="filter-label"><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?>:</label>
                        <select onchange="window.location=this.value;">
                            <option value="nearby.php?<?php echo !empty($category_filter) ? 'category=' . urlencode($category_filter) . '&' : ''; ?><?php echo !empty($search_term) ? 'search=' . urlencode($search_term) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" <?php echo empty($status_filter) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></option>
                            <option value="nearby.php?status=active<?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Đang hiển thị' : 'Active'; ?></option>
                            <option value="nearby.php?status=inactive<?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Đã ẩn' : 'Inactive'; ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item ml-auto">
                        <a href="nearby-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo $language === 'vi' ? 'Thêm địa điểm mới' : 'Add New Place'; ?>
                        </a>
                    </div>
                </div>
                
                <!-- Places List -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><?php echo $language === 'vi' ? 'Danh sách địa điểm' : 'Places List'; ?></h2>
                        <div class="card-header-actions">
                            <span class="results-count"><?php echo $total_count; ?> <?php echo $language === 'vi' ? 'kết quả' : 'results'; ?></span>
                        </div>
                    </div>
                    
                    <div class="admin-card-body">
                        <?php if (empty($places)): ?>
                            <div class="empty-state">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3><?php echo $language === 'vi' ? 'Không có địa điểm nào' : 'No places found'; ?></h3>
                                <p><?php echo $language === 'vi' ? 'Không tìm thấy địa điểm nào phù hợp với tiêu chí tìm kiếm.' : 'No places matching your search criteria were found.'; ?></p>
                            </div>
                        <?php else: ?>
                            <div class="places-grid">
                                <?php foreach ($places as $place): ?>
                                    <div class="place-card active"> <!-- Assuming all places are active by default -->
                                        <div class="place-image">
                                            <img src="<?php echo htmlspecialchars($place['image_url']); ?>" alt="<?php echo htmlspecialchars($place[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                                            <div class="place-category">
                                                <span class="badge">
                                                    <i class="<?php
                                                        switch ($place['category']) {
                                                            case 'attraction': echo 'fas fa-monument'; break;
                                                            case 'restaurant': echo 'fas fa-utensils'; break;
                                                            case 'shopping': echo 'fas fa-shopping-bag'; break;
                                                            case 'beach': echo 'fas fa-umbrella-beach'; break;
                                                            case 'nature': echo 'fas fa-leaf'; break;
                                                            case 'entertainment': echo 'fas fa-ticket-alt'; break;
                                                            default: echo 'fas fa-map-marker-alt';
                                                        }
                                                    ?>"></i>
                                                    <?php echo $categoryLabels[$place['category']]; ?>
                                                </span>
                                            </div>
                                            <?php /* Temporarily commented out until is_active column exists
                                            <?php if (!$place['is_active']): ?>
                                                <div class="place-inactive-badge">
                                                    <span class="badge badge-cancelled"><?php echo $language === 'vi' ? 'Đã ẩn' : 'Inactive'; ?></span>
                                                </div>
                                            <?php endif; ?>
                                            */ ?>
                                        </div>
                                        
                                        <div class="place-content">
                                            <h3><?php echo htmlspecialchars($place[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                            <p class="place-address"><?php echo htmlspecialchars($place['address']); ?></p>
                                            <div class="place-distance">
                                                <i class="fas fa-walking"></i>
                                                <?php echo $place['distance']; ?> <?php echo $language === 'vi' ? 'km từ khách sạn' : 'km from hotel'; ?>
                                            </div>
                                            
                                            <div class="place-actions">
                                                <a href="nearby-details.php?id=<?php echo $place['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-info" title="<?php echo $language === 'vi' ? 'Xem' : 'View'; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="nearby-form.php?id=<?php echo $place['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-warning" title="<?php echo $language === 'vi' ? 'Sửa' : 'Edit'; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php /* Temporarily commenting out until is_active column exists
                                                <?php if ($place['is_active']): ?>
                                                    <a href="process-nearby.php?action=deactivate&id=<?php echo $place['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-danger" title="<?php echo $language === 'vi' ? 'Ẩn' : 'Deactivate'; ?>">
                                                        <i class="fas fa-eye-slash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="process-nearby.php?action=activate&id=<?php echo $place['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-success" title="<?php echo $language === 'vi' ? 'Hiển thị' : 'Activate'; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                                */ ?>
                                                <!-- Since is_active column doesn't exist, show both buttons for demonstration -->
                                                <a href="process-nearby.php?action=deactivate&id=<?php echo $place['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-danger" title="<?php echo $language === 'vi' ? 'Ẩn' : 'Deactivate'; ?>">
                                                    <i class="fas fa-eye-slash"></i>
                                                </a>
                                                <a href="process-nearby.php?action=delete&id=<?php echo $place['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-danger delete-btn" title="<?php echo $language === 'vi' ? 'Xóa' : 'Delete'; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
