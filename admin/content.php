<?php
/**
 * Vinpearl Resort Nha Trang - Admin Content Management
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

// Get counts for different content types
$content_stats = [];

// Get room stats
$rooms_query = "SELECT COUNT(*) as total FROM rooms";
$result = $conn->query($rooms_query);
$rooms_stats = $result->fetch_assoc();
$content_stats['rooms'] = [
    'total' => $rooms_stats['total'],
    'active' => $rooms_stats['total'] // For now, assume all are active
];

// Get package stats
$packages_query = "SELECT COUNT(*) as total FROM packages";
$result = $conn->query($packages_query);
$packages_stats = $result->fetch_assoc();
$content_stats['packages'] = [
    'total' => $packages_stats['total'],
    'active' => $packages_stats['total'] // For now, assume all are active
];

// Get tour stats
$tours_query = "SELECT COUNT(*) as total FROM tours";
$result = $conn->query($tours_query);
$tours_stats = $result->fetch_assoc();
$content_stats['tours'] = [
    'total' => $tours_stats['total'],
    'active' => $tours_stats['total'] // For now, assume all are active
];

// Page title
$pageTitle = $language === 'vi' ? 'Quản lý nội dung' : 'Content Management';
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
                </div>
                
                <div class="admin-user">
                    <div class="language-selector">
                        <a href="?lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                        <a href="?lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
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
                <!-- Content Stats -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></h3>
                            <div class="stat-value"><?php echo $content_stats['rooms']['total']; ?></div>
                            <div class="stat-period"><?php echo $content_stats['rooms']['active']; ?> <?php echo $language === 'vi' ? 'hiển thị' : 'active'; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></h3>
                            <div class="stat-value"><?php echo $content_stats['packages']['total']; ?></div>
                            <div class="stat-period"><?php echo $content_stats['packages']['active']; ?> <?php echo $language === 'vi' ? 'hiển thị' : 'active'; ?></div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></h3>
                            <div class="stat-value"><?php echo $content_stats['tours']['total']; ?></div>
                            <div class="stat-period"><?php echo $content_stats['tours']['active']; ?> <?php echo $language === 'vi' ? 'hiển thị' : 'active'; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Content Management Cards -->
                <div class="content-modules-grid">
                    <!-- Rooms Management Card -->
                    <div class="content-module-card">
                        <div class="module-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="module-info">
                            <h3><?php echo $language === 'vi' ? 'Quản lý phòng' : 'Rooms Management'; ?></h3>
                            <p><?php echo $language === 'vi' ? 'Thêm, sửa, xóa và quản lý các loại phòng của khách sạn.' : 'Add, edit, delete and manage hotel room types.'; ?></p>
                            <div class="module-actions">
                                <a href="rooms.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-primary">
                                    <?php echo $language === 'vi' ? 'Quản lý phòng' : 'Manage Rooms'; ?>
                                </a>
                                <a href="room-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
                                    <i class="fas fa-plus"></i> <?php echo $language === 'vi' ? 'Thêm phòng' : 'Add Room'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Packages Management Card -->
                    <div class="content-module-card">
                        <div class="module-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="module-info">
                            <h3><?php echo $language === 'vi' ? 'Quản lý gói dịch vụ' : 'Packages Management'; ?></h3>
                            <p><?php echo $language === 'vi' ? 'Quản lý các gói dịch vụ đặc biệt, ưu đãi và trải nghiệm tại khách sạn.' : 'Manage special packages, deals and experiences at the hotel.'; ?></p>
                            <div class="module-actions">
                                <a href="packages.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-primary">
                                    <?php echo $language === 'vi' ? 'Quản lý gói dịch vụ' : 'Manage Packages'; ?>
                                </a>
                                <a href="package-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
                                    <i class="fas fa-plus"></i> <?php echo $language === 'vi' ? 'Thêm gói dịch vụ' : 'Add Package'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tours Management Card -->
                    <div class="content-module-card">
                        <div class="module-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <div class="module-info">
                            <h3><?php echo $language === 'vi' ? 'Quản lý tours' : 'Tours Management'; ?></h3>
                            <p><?php echo $language === 'vi' ? 'Quản lý các tour du lịch, điểm tham quan và hoạt động khám phá.' : 'Manage tours, sightseeing excursions and exploratory activities.'; ?></p>
                            <div class="module-actions">
                                <a href="tours.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-primary">
                                    <?php echo $language === 'vi' ? 'Quản lý tours' : 'Manage Tours'; ?>
                                </a>
                                <a href="tour-form.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
                                    <i class="fas fa-plus"></i> <?php echo $language === 'vi' ? 'Thêm tour' : 'Add Tour'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
    
    <style>
        .content-modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .content-module-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--admin-shadow);
            display: flex;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .content-module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .module-icon {
            width: 100px;
            background-color: var(--admin-light);
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--admin-primary);
            font-size: 2.5rem;
        }
        
        .module-info {
            flex: 1;
            padding: 20px;
        }
        
        .module-info h3 {
            color: var(--admin-primary);
            margin: 0 0 10px 0;
        }
        
        .module-info p {
            color: var(--admin-text-light);
            margin: 0 0 15px 0;
            font-size: 0.9rem;
        }
        
        .module-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .content-modules-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
