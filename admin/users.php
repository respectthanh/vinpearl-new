<?php
/**
 * Vinpearl Resort Nha Trang - Admin Users Management
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get filter parameters
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
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
    SELECT * FROM users WHERE 1=1
";

// Add filters to query
$params = [];
$param_types = '';

if (!empty($role_filter)) {
    // Change role filter to use is_admin instead of role
    if ($role_filter === 'admin') {
        $query .= " AND is_admin = 1";
    } elseif ($role_filter === 'user') {
        $query .= " AND is_admin = 0";
    }
}

if (!empty($status_filter)) {
    // Status filter is being removed as is_active column doesn't exist in the users table
    // This filter functionality will be disabled
    $status_filter = '';
}

if (!empty($search_term)) {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $param_types .= 'sss';
    $search_term_param = "%{$search_term}%";
    $params[] = $search_term_param;
    $params[] = $search_term_param;
    $params[] = $search_term_param;
}

// Count total users for pagination
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

// Get users with pagination
$query .= " ORDER BY created_at DESC LIMIT ?, ?";
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
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user stats
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN is_admin = 0 THEN 1 ELSE 0 END) as users,
        COUNT(*) as active,
        0 as inactive
    FROM users
";

$stmt = $conn->prepare($stats_query);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Role labels
$roleLabels = [
    'admin' => $language === 'vi' ? 'Quản trị viên' : 'Admin',
    'user' => $language === 'vi' ? 'Người dùng' : 'User'
];

// Page title
$pageTitle = $language === 'vi' ? 'Quản lý người dùng' : 'Users Management';
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
                        <a href="nearby.php">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $language === 'vi' ? 'Địa điểm gần đó' : 'Nearby Places'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="active">
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
                        <form action="users.php" method="GET" class="d-flex align-center">
                            <?php if(!empty($role_filter)): ?>
                                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role_filter); ?>">
                            <?php endif; ?>
                            <?php if(!empty($status_filter)): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <?php if($language === 'vi'): ?>
                                <input type="hidden" name="lang" value="vi">
                            <?php endif; ?>
                            <input type="text" name="search" class="search-input" placeholder="<?php echo $language === 'vi' ? 'Tìm kiếm người dùng...' : 'Search users...'; ?>" value="<?php echo htmlspecialchars($search_term); ?>">
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
                <!-- User Stats -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Tổng người dùng' : 'Total Users'; ?></h3>
                            <div class="stat-value"><?php echo $stats['total']; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Quản trị viên' : 'Admins'; ?></h3>
                            <div class="stat-value"><?php echo $stats['admins']; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Người dùng' : 'Regular Users'; ?></h3>
                            <div class="stat-value"><?php echo $stats['users']; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Đang hoạt động' : 'Active Users'; ?></h3>
                            <div class="stat-value"><?php echo $stats['active']; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Admin Filters -->
                <div class="admin-filters">
                    <div class="filter-item">
                        <label class="filter-label"><?php echo $language === 'vi' ? 'Vai trò' : 'Role'; ?>:</label>
                        <select onchange="window.location=this.value;">
                            <option value="users.php?<?php echo !empty($status_filter) ? 'status=' . urlencode($status_filter) . '&' : ''; ?><?php echo !empty($search_term) ? 'search=' . urlencode($search_term) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" <?php echo empty($role_filter) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></option>
                            <option value="users.php?role=admin<?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Quản trị viên' : 'Admin'; ?></option>
                            <option value="users.php?role=user<?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Người dùng' : 'User'; ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label class="filter-label"><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?>:</label>
                        <select onchange="window.location=this.value;">
                            <option value="users.php?<?php echo !empty($role_filter) ? 'role=' . urlencode($role_filter) . '&' : ''; ?><?php echo !empty($search_term) ? 'search=' . urlencode($search_term) . '&' : ''; ?><?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" <?php echo empty($status_filter) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?></option>
                            <option value="users.php?status=active<?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Đang hoạt động' : 'Active'; ?></option>
                            <option value="users.php?status=inactive<?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Không hoạt động' : 'Inactive'; ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item ml-auto">
                        <a href="user-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo $language === 'vi' ? 'Thêm người dùng' : 'Add User'; ?>
                        </a>
                    </div>
                </div>
                
                <!-- Users List -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><?php echo $language === 'vi' ? 'Danh sách người dùng' : 'Users List'; ?></h2>
                        <div class="card-header-actions">
                            <span class="results-count"><?php echo $total_count; ?> <?php echo $language === 'vi' ? 'kết quả' : 'results'; ?></span>
                        </div>
                    </div>
                    
                    <div class="admin-card-body">
                        <?php if (empty($users)): ?>
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <h3><?php echo $language === 'vi' ? 'Không có người dùng nào' : 'No users found'; ?></h3>
                                <p><?php echo $language === 'vi' ? 'Không tìm thấy người dùng nào phù hợp với tiêu chí tìm kiếm.' : 'No users matching your search criteria were found.'; ?></p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><?php echo $language === 'vi' ? 'Tên' : 'Name'; ?></th>
                                            <th>Email</th>
                                            <th><?php echo $language === 'vi' ? 'Điện thoại' : 'Phone'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Vai trò' : 'Role'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Ngày tạo' : 'Created'; ?></th>
                                            <th><?php echo $language === 'vi' ? 'Thao tác' : 'Actions'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $user['is_admin'] ? 'badge-info' : 'badge-secondary'; ?>">
                                                        <?php echo $user['is_admin'] ? $roleLabels['admin'] : $roleLabels['user']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-confirmed">
                                                        <?php echo $language === 'vi' ? 'Đang hoạt động' : 'Active'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($user['created_at'], 'M d, Y'); ?></td>
                                                <td>
                                                    <div class="table-actions">
                                                        <a href="user-details.php?id=<?php echo $user['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-info" title="<?php echo $language === 'vi' ? 'Xem' : 'View'; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="user-form.php?id=<?php echo $user['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-warning" title="<?php echo $language === 'vi' ? 'Sửa' : 'Edit'; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($user['id'] !== $currentUser['id']): // Don't allow actions on current user ?>
                                                            <a href="process-user.php?action=deactivate&id=<?php echo $user['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-danger" title="<?php echo $language === 'vi' ? 'Vô hiệu hóa' : 'Deactivate'; ?>">
                                                                <i class="fas fa-user-slash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
