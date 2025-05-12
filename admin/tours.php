<?php
/**
 * Vinpearl Resort Nha Trang - Admin Tours Management
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

// Pagination parameters
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
// Remove type filter since type column doesn't exist
$type_filter = '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// Build query conditions
$where_conditions = [];
if (!empty($search)) {
    $where_conditions[] = "(name_en LIKE '%$search%' OR name_vi LIKE '%$search%' OR description_en LIKE '%$search%' OR description_vi LIKE '%$search%')";
}

// Remove type filter condition
if (!empty($status_filter)) {
    $where_conditions[] = "status = '$status_filter'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";

// Get tours with pagination
$tours_query = "SELECT * FROM tours $where_clause ORDER BY id DESC LIMIT $offset, $per_page";
$tours_result = $conn->query($tours_query);
$tours = [];

if ($tours_result && $tours_result->num_rows > 0) {
    while ($row = $tours_result->fetch_assoc()) {
        $tours[] = $row;
    }
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM tours $where_clause";
$count_result = $conn->query($count_query);
$total_tours = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_tours / $per_page);

// Remove tour types query since the type column doesn't exist
$tour_types = [];

// Check for flash messages
$admin_message = isset($_SESSION['admin_message']) ? $_SESSION['admin_message'] : null;
unset($_SESSION['admin_message']); // Clear the message after retrieving

// Page title
$pageTitle = $language === 'vi' ? 'Quản lý tour' : 'Tours Management';
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
                        <span><?php echo $language === 'vi' ? 'Quản lý tour' : 'Tours Management'; ?></span>
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
                <?php if ($admin_message): ?>
                    <div class="alert alert-<?php echo $admin_message['type']; ?>">
                        <?php echo $admin_message['message']; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Tour Stats -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Tổng tour' : 'Total Tours'; ?></h3>
                            <div class="stat-value"><?php echo $total_tours; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Filter -->
                <div class="admin-controls mb-4">
                    <form action="tours.php" method="GET" class="admin-search" id="search-form">
                        <?php if($language === 'vi'): ?>
                            <input type="hidden" name="lang" value="vi">
                        <?php endif; ?>
                        
                        <input type="text" name="search" placeholder="<?php echo $language === 'vi' ? 'Tìm kiếm tour...' : 'Search tours...'; ?>" value="<?php echo htmlspecialchars($search); ?>">
                        
                        <select name="status" class="admin-form-select">
                            <option value=""><?php echo $language === 'vi' ? 'Tất cả trạng thái' : 'All statuses'; ?></option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>
                                <?php echo $language === 'vi' ? 'Đang hoạt động' : 'Active'; ?>
                            </option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>
                                <?php echo $language === 'vi' ? 'Không hoạt động' : 'Inactive'; ?>
                            </option>
                        </select>
                        
                        <button type="submit" class="btn btn-primary">
                            <?php echo $language === 'vi' ? 'Tìm kiếm' : 'Search'; ?>
                        </button>
                        
                        <a href="tour-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-success ml-2">
                            <i class="fas fa-plus"></i> <?php echo $language === 'vi' ? 'Thêm tour mới' : 'Add New Tour'; ?>
                        </a>
                    </form>
                </div>
                
                <?php if (empty($tours)): ?>
                    <div class="alert alert-info">
                        <?php echo $language === 'vi' ? 'Không có tour nào được tìm thấy.' : 'No tours found.'; ?>
                    </div>
                <?php else: ?>
                    <!-- Tours Table -->
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th><?php echo $language === 'vi' ? 'ID' : 'ID'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Hình ảnh' : 'Image'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Tên' : 'Name'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Giá' : 'Price'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Thời gian' : 'Duration'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Thao tác' : 'Actions'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tours as $tour): ?>
                                    <tr>
                                        <td><?php echo $tour['id']; ?></td>
                                        <td>
                                            <?php if (!empty($tour['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($tour['image_url']); ?>" alt="Tour Image" class="admin-table-image">
                                            <?php else: ?>
                                                <div class="admin-table-no-image">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($language === 'vi' ? $tour['name_vi'] : $tour['name_en']); ?>
                                        </td>
                                        <td><?php echo formatCurrency($tour['price_per_person']); ?></td>
                                        <td><?php echo $tour['duration']; ?> <?php echo $language === 'vi' ? 'giờ' : 'hours'; ?></td>
                                        <td>
                                            <span class="badge <?php echo $tour['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $tour['status'] === 'active' ? 
                                                    ($language === 'vi' ? 'Đang hoạt động' : 'Active') : 
                                                    ($language === 'vi' ? 'Không hoạt động' : 'Inactive'); 
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="admin-action-buttons">
                                                <a href="tour-form.php?id=<?php echo $tour['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> <?php echo $language === 'vi' ? 'Sửa' : 'Edit'; ?>
                                                </a>
                                                <a href="process-tour.php?action=delete&id=<?php echo $tour['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-danger delete-btn" data-confirm="<?php echo $language === 'vi' ? 'Bạn có chắc chắn muốn xóa tour này?' : 'Are you sure you want to delete this tour?'; ?>">
                                                    <i class="fas fa-trash"></i> <?php echo $language === 'vi' ? 'Xóa' : 'Delete'; ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>