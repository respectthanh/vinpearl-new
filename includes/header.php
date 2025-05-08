<?php
/**
 * Header Template - Vinpearl Resort Nha Trang
 */

// Get current user if logged in
$currentUser = isset($currentUser) ? $currentUser : getCurrentUser();

// Get language parameter
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';
$lang = $language; // For compatibility with existing code

// Page-specific variables, default to empty if not set
$pageTitle = isset($pageTitle) ? $pageTitle : '';
?>

<header class="site-header">
    <div class="container">
        <div class="logo">
            <a href="index.php">
                <img src="assets/images/logo.png" alt="Vinpearl Resort Nha Trang">
            </a>
        </div>
        
        <nav class="main-navigation">
            <ul>
                <li><a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a></li>
                <li><a href="rooms.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'rooms.php') ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                <li><a href="packages.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'packages.php') ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                <li><a href="tours.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'tours.php') ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                <li><a href="nearby.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'nearby.php') ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
            </ul>
        </nav>
        
        <div class="header-actions">
            <div class="language-selector">
                <a href="?<?php echo http_build_query(array_merge($_GET, ['lang' => 'en'])); ?>" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                <a href="?<?php echo http_build_query(array_merge($_GET, ['lang' => 'vi'])); ?>" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
            </div>
            
            <div class="user-actions">
                <?php if (isLoggedIn()): ?>
                    <div class="user-menu">
                        <span>Hello, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <div class="user-dropdown">
                            <a href="profile.php"><?php echo $language === 'vi' ? 'Hồ sơ' : 'Profile'; ?></a>
                            <a href="bookings.php"><?php echo $language === 'vi' ? 'Đặt chỗ' : 'Bookings'; ?></a>
                            <a href="logout.php"><?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                        </div>
                    </div>
                <?php else: ?>
                    <div>
                        <a href="login.php" class="btn btn-outline-sm"><?php echo $language === 'vi' ? 'Đăng nhập' : 'Login'; ?></a>
                        <a href="register.php" class="btn btn-sm"><?php echo $language === 'vi' ? 'Đăng ký' : 'Register'; ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>