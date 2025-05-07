<?php
/**
 * Vinpearl Resort Nha Trang - Nearby Attractions Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get category filter from URL if exists
$category = isset($_GET['category']) ? $_GET['category'] : null;

// Get nearby places based on category filter
$places = getNearbyPlaces($category, $language);

// Get all available categories for filter
$conn = connectDatabase();
$categories = [];
if ($conn) {
    $stmt = $conn->prepare("SELECT DISTINCT category FROM nearby_places ORDER BY category");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Get current user if logged in
$currentUser = getCurrentUser();

// Page title
$pageTitle = $language === 'vi' ? 'Điểm Tham Quan Lân Cận' : 'Nearby Attractions';
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
                    <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                    <li><a href="nearby.php" class="active"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                    <li><a href="contact.php"><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact'; ?></a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="language-selector">
                    <a href="?<?php echo !empty($category) ? "category=$category&" : ""; ?>lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                    <a href="?<?php echo !empty($category) ? "category=$category&" : ""; ?>lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
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
            <p><?php echo $language === 'vi' ? 'Khám phá những điểm đến thú vị xung quanh khu nghỉ dưỡng của chúng tôi' : 'Discover interesting places to visit around our resort'; ?></p>
        </div>
    </div>

    <!-- Category Filter -->
    <section class="category-filter">
        <div class="container">
            <div class="filter-tabs">
                <a href="nearby.php?lang=<?php echo $language; ?>" class="filter-tab <?php echo empty($category) ? 'active' : ''; ?>">
                    <?php echo $language === 'vi' ? 'Tất cả' : 'All'; ?>
                </a>
                
                <?php foreach ($categories as $cat): ?>
                    <a href="nearby.php?category=<?php echo urlencode($cat); ?>&lang=<?php echo $language; ?>" 
                       class="filter-tab <?php echo $category === $cat ? 'active' : ''; ?>">
                        <?php 
                        // Translate category names
                        $categoryTranslations = [
                            'attraction' => $language === 'vi' ? 'Điểm tham quan' : 'Attractions',
                            'restaurant' => $language === 'vi' ? 'Nhà hàng' : 'Restaurants',
                            'shopping' => $language === 'vi' ? 'Mua sắm' : 'Shopping',
                            'cafe' => $language === 'vi' ? 'Quán cà phê' : 'Cafes'
                        ];
                        
                        echo isset($categoryTranslations[$cat]) ? $categoryTranslations[$cat] : ucfirst($cat);
                        ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Nearby Places List -->
    <section class="nearby-list">
        <div class="container">
            <?php if (empty($places)): ?>
                <div class="no-results">
                    <h2><?php echo $language === 'vi' ? 'Không có địa điểm nào được tìm thấy' : 'No places found'; ?></h2>
                    <p><?php echo $language === 'vi' ? 'Hãy thử chọn danh mục khác' : 'Try selecting a different category'; ?></p>
                </div>
            <?php else: ?>
                <div class="place-cards">
                    <?php foreach ($places as $place): ?>
                        <div class="place-card">
                            <div class="place-image">
                                <img src="<?php echo htmlspecialchars($place['image_url']); ?>" alt="<?php echo htmlspecialchars($place[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                                <?php
                                $categoryClass = '';
                                $categoryLabel = '';
                                
                                switch ($place['category']) {
                                    case 'attraction':
                                        $categoryClass = 'category-attraction';
                                        $categoryLabel = $language === 'vi' ? 'Điểm tham quan' : 'Attraction';
                                        break;
                                    case 'restaurant':
                                        $categoryClass = 'category-restaurant';
                                        $categoryLabel = $language === 'vi' ? 'Nhà hàng' : 'Restaurant';
                                        break;
                                    case 'shopping':
                                        $categoryClass = 'category-shopping';
                                        $categoryLabel = $language === 'vi' ? 'Mua sắm' : 'Shopping';
                                        break;
                                    case 'cafe':
                                        $categoryClass = 'category-cafe';
                                        $categoryLabel = $language === 'vi' ? 'Quán cà phê' : 'Cafe';
                                        break;
                                    default:
                                        $categoryClass = '';
                                        $categoryLabel = ucfirst($place['category']);
                                }
                                ?>
                                <span class="place-category <?php echo $categoryClass; ?>"><?php echo $categoryLabel; ?></span>
                            </div>
                            <div class="place-details">
                                <h3><?php echo htmlspecialchars($place[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                <p class="place-description"><?php echo htmlspecialchars($place[$language === 'vi' ? 'description_vi' : 'description_en']); ?></p>
                                <div class="place-info">
                                    <div class="info-item">
                                        <i class="icon-location"></i>
                                        <span><?php echo htmlspecialchars($place['address']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="icon-distance"></i>
                                        <span><?php echo $place['distance_km']; ?> km <?php echo $language === 'vi' ? 'từ khu nghỉ' : 'from resort'; ?></span>
                                    </div>
                                    <?php if (!empty($place['contact_phone'])): ?>
                                        <div class="info-item">
                                            <i class="icon-phone"></i>
                                            <span><?php echo htmlspecialchars($place['contact_phone']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($place['opening_hours'])): ?>
                                        <div class="info-item">
                                            <i class="icon-clock"></i>
                                            <span><?php echo htmlspecialchars($place['opening_hours']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($place['price_level'])): ?>
                                        <div class="info-item">
                                            <i class="icon-price"></i>
                                            <span><?php echo str_repeat('$', strlen($place['price_level'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="place-actions">
                                    <?php if (!empty($place['website_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($place['website_url']); ?>" class="btn btn-sm" target="_blank">
                                            <?php echo $language === 'vi' ? 'Trang web' : 'Website'; ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($place['booking_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($place['booking_url']); ?>" class="btn btn-sm btn-outline" target="_blank">
                                            <?php echo $language === 'vi' ? 'Đặt chỗ' : 'Book Now'; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2><?php echo $language === 'vi' ? 'Vị trí trên bản đồ' : 'Map Location'; ?></h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3898.740916176312!2d109.21988495223439!3d12.264967685683095!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31706700a58103b1%3A0x9aa0e0844d2f4089!2sVinpearl%20Luxury%20Nha%20Trang!5e0!3m2!1sen!2s!4v1656518712694!5m2!1sen!2s" 
                        width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <!-- Transportation Tips -->
    <section class="transportation-tips">
        <div class="container">
            <h2><?php echo $language === 'vi' ? 'Gợi ý di chuyển' : 'Transportation Tips'; ?></h2>
            <div class="tips-grid">
                <div class="tip-item">
                    <div class="tip-icon">
                        <img src="assets/images/icons/taxi.svg" alt="Taxi">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Taxi' : 'Taxi'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Có sẵn tại lễ tân hoặc gọi trực tiếp. Chi phí khoảng 10,000đ - 15,000đ/km.' : 'Available at reception or by direct call. Cost approximately 10,000đ - 15,000đ/km.'; ?></p>
                </div>
                
                <div class="tip-item">
                    <div class="tip-icon">
                        <img src="assets/images/icons/shuttle.svg" alt="Shuttle">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Xe đưa đón' : 'Shuttle Service'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Khu nghỉ cung cấp dịch vụ xe đưa đón miễn phí đến trung tâm thành phố theo lịch trình.' : 'The resort provides complimentary scheduled shuttle service to the city center.'; ?></p>
                </div>
                
                <div class="tip-item">
                    <div class="tip-icon">
                        <img src="assets/images/icons/motorbike.svg" alt="Motorbike">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Thuê xe máy' : 'Motorbike Rental'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Có thể thuê xe máy với giá khoảng 150,000đ - 200,000đ/ngày. Yêu cầu bằng lái xe quốc tế.' : 'Motorbikes can be rented for approximately 150,000đ - 200,000đ/day. International driving license required.'; ?></p>
                </div>
                
                <div class="tip-item">
                    <div class="tip-icon">
                        <img src="assets/images/icons/car.svg" alt="Car">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Thuê xe hơi' : 'Car Rental'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Dịch vụ thuê xe có hoặc không có tài xế. Đặt trước với lễ tân để được giá tốt nhất.' : 'Car rental services with or without driver available. Book in advance at reception for best rates.'; ?></p>
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