<?php
/**
 * Vinpearl Resort Nha Trang - Admin Packages Management
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

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search parameter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "AND (name_en LIKE '%$search%' OR name_vi LIKE '%$search%' OR description_en LIKE '%$search%' OR description_vi LIKE '%$search%')";
}

// Filter parameters
// Note: We're removing type filtering as the column doesn't exist in the database schema
$type_filter = '';
$type_condition = '';

// Note: Status column doesn't exist in packages table according to schema
// We'll comment this out for now, you may need to add a status column to your packages table
// $status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
// $status_condition = !empty($status_filter) ? "AND status = '$status_filter'" : '';
$status_filter = '';
$status_condition = '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) as count FROM packages WHERE 1=1 $search_condition";
$count_result = $conn->query($count_query);
$total_packages = $count_result->fetch_assoc()['count'];
$total_pages = ceil($total_packages / $limit);

// Get packages with pagination
$query = "SELECT * FROM packages 
          WHERE 1=1 $search_condition
          ORDER BY id DESC
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// We'll remove the types query since the column doesn't exist
$types_result = false;

// Since status doesn't exist either, we'll set empty status counts
$status_counts = [];
// $status_query = "SELECT status, COUNT(*) as count FROM packages GROUP BY status";
// $status_result = $conn->query($status_query);

// if ($status_result) {
//     while ($row = $status_result->fetch_assoc()) {
//         $status_counts[$row['status']] = $row['count'];
//     }
// }

// Check for flash messages
$admin_message = isset($_SESSION['admin_message']) ? $_SESSION['admin_message'] : null;
unset($_SESSION['admin_message']); // Clear the message after retrieving

// Page title
$pageTitle = $language === 'vi' ? 'Quản lý gói dịch vụ' : 'Packages Management';
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
                    <img src="../assets/images/logo.svg" alt="Vinpearl Resort Nha Trang">
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
                        <span><?php echo $language === 'vi' ? 'Quản lý gói dịch vụ' : 'Packages Management'; ?></span>
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
                <!-- Notification Messages -->
                <?php if ($admin_message): ?>
                <div class="alert alert-<?php echo $admin_message['type']; ?>">
                    <?php echo $admin_message['message']; ?>
                </div>
                <?php endif; ?>
                
                <!-- Filters and Actions -->
                <div class="admin-actions-bar">
                    <div class="admin-search">
                        <form action="packages.php" method="GET" id="search-form">
                            <?php if($language === 'vi'): ?>
                                <input type="hidden" name="lang" value="vi">
                            <?php endif; ?>
                            <input type="text" name="search" id="admin-search" placeholder="<?php echo $language === 'vi' ? 'Tìm kiếm gói dịch vụ...' : 'Search packages...'; ?>" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    
                    <div class="admin-filters">
                        <a href="package-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-success">
                            <i class="fas fa-plus"></i> <?php echo $language === 'vi' ? 'Thêm gói dịch vụ' : 'Add Package'; ?>
                        </a>
                    </div>
                </div>
                
                <!-- Packages Table -->
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th><?php echo $language === 'vi' ? 'ID' : 'ID'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Tên gói dịch vụ' : 'Package Name'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Thời gian' : 'Duration'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Giá (VND)' : 'Price (VND)'; ?></th>
                                    <th><?php echo $language === 'vi' ? 'Thao tác' : 'Actions'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($package = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $package['id']; ?></td>
                                        <td>
                                            <div class="package-info">
                                                <?php if (!empty($package['image_url'])): ?>
                                                    <div class="package-image">
                                                        <img src="<?php echo htmlspecialchars($package['image_url']); ?>" alt="Package">
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <?php echo htmlspecialchars($language === 'vi' ? $package['name_vi'] : $package['name_en']); ?>
                                                    <div class="package-dates">
                                                        <?php if (!empty($package['valid_from']) && !empty($package['valid_to'])): ?>
                                                            <small><?php echo $language === 'vi' ? 'Hiệu lực' : 'Valid'; ?>: 
                                                                <?php echo formatDate($package['valid_from']); ?> -
                                                                <?php echo formatDate($package['valid_to']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $package['duration']; ?> <?php echo $language === 'vi' ? 'ngày' : 'days'; ?></td>
                                        <td><?php echo number_format($package['price'], 0, ',', '.'); ?></td>
                                        <td>
                                            <div class="admin-action-buttons">
                                                <a href="package-form.php?id=<?php echo $package['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> <?php echo $language === 'vi' ? 'Sửa' : 'Edit'; ?>
                                                </a>
                                                <a href="process-package.php?action=delete&id=<?php echo $package['id']; ?><?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="btn btn-sm btn-danger delete-btn" data-confirm="<?php echo $language === 'vi' ? 'Bạn có chắc chắn muốn xóa gói này?' : 'Are you sure you want to delete this package?'; ?>">
                                                    <i class="fas fa-trash-alt"></i> <?php echo $language === 'vi' ? 'Xóa' : 'Delete'; ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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
                <?php else: ?>
                    <div class="admin-no-results">
                        <i class="fas fa-info-circle"></i>
                        <p><?php echo $language === 'vi' ? 'Không tìm thấy gói dịch vụ nào.' : 'No packages found.'; ?></p>
                        <a href="package-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-primary">
                            <?php echo $language === 'vi' ? 'Thêm gói dịch vụ mới' : 'Add New Package'; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
    
    <style>
        .package-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .package-image {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .package-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .package-dates {
            font-size: 0.8rem;
            color: var(--admin-text-light);
        }
    </style>
</body>
</html>