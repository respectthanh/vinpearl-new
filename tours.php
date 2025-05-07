<?php
/**
 * Vinpearl Resort Nha Trang - Tours Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get all tours
$tours = getTours($language);

// Get current user if logged in
$currentUser = getCurrentUser();

// Page title
$pageTitle = $language === 'vi' ? 'Tours & Hoạt Động' : 'Tours & Activities';
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Vinpearl Resort Nha Trang">
                </a>
            </div>
            
            <nav class="main-navigation">
                <ul>
                    <li><a href="index.php"><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a></li>
                    <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                    <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                    <li><a href="tours.php" class="active"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                    <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                    <li><a href="contact.php"><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact'; ?></a></li>
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
                            <span>Hello, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                            <div class="dropdown-menu">
                                <a href="profile.php"><?php echo $language === 'vi' ? 'Hồ sơ' : 'Profile'; ?></a>
                                <a href="bookings.php"><?php echo $language === 'vi' ? 'Đặt chỗ' : 'My Bookings'; ?></a>
                                <?php if (isAdmin()): ?>
                                    <a href="admin/index.php"><?php echo $language === 'vi' ? 'Quản trị' : 'Admin Panel'; ?></a>
                                <?php endif; ?>
                                <a href="logout.php"><?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-sm"><?php echo $language === 'vi' ? 'Đăng nhập' : 'Login'; ?></a>
                        <a href="register.php" class="btn btn-sm btn-outline"><?php echo $language === 'vi' ? 'Đăng ký' : 'Register'; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><?php echo $pageTitle; ?></h1>
            <p><?php echo $language === 'vi' ? 'Khám phá Nha Trang với các tour đặc sắc của chúng tôi' : 'Explore Nha Trang with our exciting tours'; ?></p>
        </div>
    </div>

    <!-- Tours List -->
    <section class="tours-list">
        <div class="container">
            <?php if (empty($tours)): ?>
                <div class="no-results">
                    <h2><?php echo $language === 'vi' ? 'Hiện không có tour nào' : 'No tours available at the moment'; ?></h2>
                    <p><?php echo $language === 'vi' ? 'Vui lòng quay lại sau' : 'Please check back later'; ?></p>
                </div>
            <?php else: ?>
                <div class="tour-cards">
                    <?php foreach ($tours as $tour): ?>
                        <div class="tour-card">
                            <div class="tour-image">
                                <img src="<?php echo htmlspecialchars($tour['image_url']); ?>" alt="<?php echo htmlspecialchars($tour[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                            </div>
                            <div class="tour-details">
                                <h3><?php echo htmlspecialchars($tour[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                <p class="tour-description"><?php echo htmlspecialchars($tour[$language === 'vi' ? 'description_vi' : 'description_en']); ?></p>
                                <div class="tour-features">
                                    <span><i class="icon-clock"></i> <?php echo htmlspecialchars($tour['duration']); ?></span>
                                    <span><i class="icon-location"></i> <?php echo $language === 'vi' ? 'Điểm đón:' : 'Meeting point:'; ?> <?php echo htmlspecialchars($tour[$language === 'vi' ? 'meeting_point_vi' : 'meeting_point_en']); ?></span>
                                </div>
                                <div class="tour-price">
                                    <span class="price"><?php echo formatCurrency($tour['price_per_person']); ?></span>
                                    <span class="per-person"><?php echo $language === 'vi' ? '/ người' : '/ person'; ?></span>
                                </div>
                                <a href="tour-details.php?id=<?php echo $tour['id']; ?>" class="btn"><?php echo $language === 'vi' ? 'Chi tiết' : 'View Details'; ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Tour Benefits -->
    <section class="tour-benefits">
        <div class="container">
            <h2><?php echo $language === 'vi' ? 'Trải nghiệm với chất lượng đảm bảo' : 'Experience with quality guaranteed'; ?></h2>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <img src="assets/images/icons/guide.svg" alt="Expert Guides">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Hướng dẫn viên chuyên nghiệp' : 'Expert Guides'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Đội ngũ hướng dẫn viên nhiệt tình, am hiểu và giàu kinh nghiệm' : 'Enthusiastic, knowledgeable, and experienced guide team'; ?></p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <img src="assets/images/icons/group.svg" alt="Small Groups">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Nhóm nhỏ' : 'Small Groups'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Trải nghiệm cá nhân hóa hơn với các nhóm nhỏ' : 'More personalized experience with small groups'; ?></p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <img src="assets/images/icons/support.svg" alt="24/7 Support">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Hỗ trợ 24/7' : '24/7 Support'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Luôn sẵn sàng hỗ trợ khi bạn cần' : 'Always ready to help when you need'; ?></p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <img src="assets/images/icons/booking.svg" alt="Easy Booking">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Đặt tour dễ dàng' : 'Easy Booking'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Đặt tour nhanh chóng và tiện lợi' : 'Quick and convenient tour booking'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-columns">
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Về chúng tôi' : 'About Us'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Vinpearl Resort Nha Trang là khu nghỉ dưỡng sang trọng với tầm nhìn tuyệt đẹp ra biển.' : 'Vinpearl Resort Nha Trang is a luxury resort with stunning ocean views.'; ?></p>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact'; ?></h3>
                    <address>
                        <p><?php echo $language === 'vi' ? 'Địa chỉ:' : 'Address:'; ?> Vinpearl Resort Nha Trang, Đảo Hòn Tre, Nha Trang, Việt Nam</p>
                        <p><?php echo $language === 'vi' ? 'Điện thoại:' : 'Phone:'; ?> +84 258 598 9999</p>
                        <p>Email: info@vinpearl.com</p>
                    </address>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Liên kết nhanh' : 'Quick Links'; ?></h3>
                    <ul>
                        <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                        <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                        <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                        <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                        <li><a href="contact.php"><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact'; ?></a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Kết nối' : 'Connect'; ?></h3>
                    <div class="social-links">
                        <a href="#" class="social-link"><img src="assets/images/icons/facebook.svg" alt="Facebook"></a>
                        <a href="#" class="social-link"><img src="assets/images/icons/instagram.svg" alt="Instagram"></a>
                        <a href="#" class="social-link"><img src="assets/images/icons/twitter.svg" alt="Twitter"></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Vinpearl Resort Nha Trang. <?php echo $language === 'vi' ? 'Đã đăng ký bản quyền.' : 'All rights reserved.'; ?></p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html> 