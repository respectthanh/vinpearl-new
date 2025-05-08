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

// Include header
include 'includes/header.php';
?>

    <!-- Hero Banner -->
    <section class="nearby-hero">
        <div class="nearby-hero-content">
            <h1><?php echo $pageTitle; ?></h1>
            <p><?php echo $language === 'vi' ? 'Khám phá những điểm đến thú vị, điểm tham quan hấp dẫn, nhà hàng nổi tiếng xung quanh Vinpearl Nha Trang' : 'Explore fascinating destinations, attractions, and renowned restaurants around Vinpearl Nha Trang'; ?></p>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="category-filter">
        <div class="container">
            <div class="filter-tabs" data-aos="fade-up" data-aos-duration="800">
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
            <div class="nearby-header">
                <h2><?php echo $language === 'vi' ? 'Khám phá địa điểm lân cận' : 'Explore Nearby Attractions'; ?></h2>
                <p><?php echo $language === 'vi' ? 'Trải nghiệm tốt nhất tại Nha Trang với những địa điểm tuyệt vời xung quanh khu nghỉ dưỡng của chúng tôi' : 'Experience the best of Nha Trang with amazing places around our resort'; ?></p>
            </div>
            
            <?php if (empty($places)): ?>
                <div class="no-results">
                    <h2><?php echo $language === 'vi' ? 'Không có địa điểm nào được tìm thấy' : 'No places found'; ?></h2>
                    <p><?php echo $language === 'vi' ? 'Hãy thử chọn danh mục khác' : 'Try selecting a different category'; ?></p>
                </div>
            <?php else: ?>
                <div class="place-cards">
                    <?php 
                    // Direct images for each category
                    $categoryImages = [
                        'attraction' => 'https://images.unsplash.com/photo-1569949381669-ecf31ae8e613?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
                        'restaurant' => 'https://images.unsplash.com/photo-1552566626-52f8b828add9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
                        'cafe' => 'https://images.unsplash.com/photo-1521017432531-fbd92d768814?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
                        'shopping' => 'https://images.unsplash.com/photo-1534452203293-494d7ddbf7e0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1172&q=80'
                    ];
                    
                    foreach ($places as $index => $place): ?>
                        <div class="place-card">
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
                            <div class="place-image">
                                <?php
                                // Use direct category images from internet
                                $imageUrl = isset($categoryImages[$place['category']]) ? 
                                    $categoryImages[$place['category']] : 
                                    'https://images.unsplash.com/photo-1540541338287-41700207dee6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80';
                                
                                // Create link to website if available, otherwise make it a div
                                if (!empty($place['website'])) {
                                    echo '<a href="' . htmlspecialchars($place['website']) . '" target="_blank" class="place-image-link">';
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($place[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                                <span class="place-category <?php echo $categoryClass; ?>"><?php echo $categoryLabel; ?></span>
                                <?php
                                if (!empty($place['website'])) {
                                    echo '</a>';
                                }
                                ?>
                            </div>
                            <div class="place-details">
                                <h3>
                                    <?php if (!empty($place['website'])): ?>
                                        <a href="<?php echo htmlspecialchars($place['website']); ?>" target="_blank" class="place-title-link">
                                            <?php echo htmlspecialchars($place[$language === 'vi' ? 'name_vi' : 'name_en']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($place[$language === 'vi' ? 'name_vi' : 'name_en']); ?>
                                    <?php endif; ?>
                                </h3>
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
                                    <?php if (!empty($place['website'])): ?>
                                        <div class="info-item">
                                            <i class="icon-website"></i>
                                            <a href="<?php echo htmlspecialchars($place['website']); ?>" target="_blank"><?php echo $language === 'vi' ? 'Trang web' : 'Website'; ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="place-actions">
                                    <?php if (!empty($place['website'])): ?>
                                        <a href="<?php echo htmlspecialchars($place['website']); ?>" class="btn btn-sm" target="_blank">
                                            <?php echo $language === 'vi' ? 'Trang web' : 'Website'; ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($place['address'])): ?>
                                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($place['address']); ?>" class="btn btn-sm btn-outline" target="_blank">
                                            <?php echo $language === 'vi' ? 'Xem trên bản đồ' : 'View on Map'; ?>
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
            <h2><?php echo $language === 'vi' ? 'Vị trí trên bản đồ' : 'Location Map'; ?></h2>
            <div class="map-container">
                <div class="map-overlay">
                    <h3><?php echo $language === 'vi' ? 'Khu vực Vinpearl Nha Trang' : 'Vinpearl Nha Trang Area'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Khám phá những địa điểm thú vị xung quanh khu nghỉ' : 'Explore interesting places around the resort'; ?></p>
                </div>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3898.740916176312!2d109.21988495223439!3d12.264967685683095!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31706700a58103b1%3A0x9aa0e0844d2f4089!2sVinpearl%20Luxury%20Nha%20Trang!5e0!3m2!1sen!2s!4v1656518712694!5m2!1sen!2s" 
                        width="100%" height="500" style="border:0; border-radius: var(--border-radius-lg);" allowfullscreen="" loading="lazy" 
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
                        <img src="https://cdn-icons-png.flaticon.com/512/2087/2087422.png" alt="Taxi">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Taxi' : 'Taxi'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Có sẵn tại lễ tân hoặc gọi trực tiếp. Chi phí khoảng 10,000đ - 15,000đ/km.' : 'Available at reception or by direct call. Cost approximately 10,000đ - 15,000đ/km.'; ?></p>
                </div>
                
                <div class="tip-item">
                    <div class="tip-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/3097/3097180.png" alt="Shuttle">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Xe đưa đón' : 'Shuttle Service'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Khu nghỉ cung cấp dịch vụ xe đưa đón miễn phí đến trung tâm thành phố theo lịch trình.' : 'The resort provides complimentary scheduled shuttle service to the city center.'; ?></p>
                </div>
                
                <div class="tip-item">
                    <div class="tip-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/1986/1986937.png" alt="Motorbike">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Thuê xe máy' : 'Motorbike Rental'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Có thể thuê xe máy với giá khoảng 150,000đ - 200,000đ/ngày. Yêu cầu bằng lái xe quốc tế.' : 'Motorbikes can be rented for approximately 150,000đ - 200,000đ/day. International driving license required.'; ?></p>
                </div>
                
                <div class="tip-item">
                    <div class="tip-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/3097/3097136.png" alt="Car">
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