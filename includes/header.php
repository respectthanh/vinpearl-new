<?php
/**
 * Header Template - Vinpearl Resort Nha Trang
 */

// Get current user if logged in
$currentUser = getCurrentUser();

// Get language parameter
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';
$lang = $language; // For compatibility with existing code

// Page-specific variables, default to empty if not set
$pageTitle = isset($pageTitle) ? $pageTitle : '';
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (basename($_SERVER['PHP_SELF']) == 'login.php' || basename($_SERVER['PHP_SELF']) == 'register.php' || basename($_SERVER['PHP_SELF']) == 'forgot-password.php'): ?>
    <link rel="stylesheet" href="assets/css/auth-styles.css">
    <?php endif; ?>
    <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
    <link rel="stylesheet" href="assets/css/home-styles.css">
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="/../assets/images/logo.svg" alt="Vinpearl Resort Nha Trang">
                </a>
            </div>
            
            <nav class="main-navigation">
                <ul>
                    <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a></li>
                    <li><a href="rooms.php" <?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                    <li><a href="packages.php" <?php echo basename($_SERVER['PHP_SELF']) == 'packages.php' ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                    <li><a href="tours.php" <?php echo basename($_SERVER['PHP_SELF']) == 'tours.php' ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                    <li><a href="nearby.php" <?php echo basename($_SERVER['PHP_SELF']) == 'nearby.php' ? 'class="active"' : ''; ?>><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="language-selector">
                    <a href="?lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                    <a href="?lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
                </div>
                
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                            <div class="dropdown-menu">
                                <a href="profile.php"><i class="fas fa-user"></i> <?php echo $language === 'vi' ? 'Hồ sơ' : 'Profile'; ?></a>
                                <a href="bookings.php"><i class="fas fa-calendar-check"></i> <?php echo $language === 'vi' ? 'Đặt chỗ' : 'My Bookings'; ?></a>
                                <?php if (isAdmin()): ?>
                                    <a href="admin/index.php"><i class="fas fa-cog"></i> <?php echo $language === 'vi' ? 'Quản trị' : 'Admin Panel'; ?></a>
                                <?php endif; ?>
                                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-sm"><i class="fas fa-sign-in-alt"></i> <?php echo $language === 'vi' ? 'Đăng nhập' : 'Login'; ?></a>
                        <a href="register.php" class="btn btn-sm btn-outline"><i class="fas fa-user-plus"></i> <?php echo $language === 'vi' ? 'Đăng ký' : 'Register'; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
</body>
</html>