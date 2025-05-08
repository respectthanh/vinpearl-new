<?php
/**
 * Vinpearl Resort Nha Trang - Tours Page
 * Enhanced modern design
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get all tours (in a real application, this would come from the database)
// For this enhancement demo, we'll use hardcoded tours with online images
$tours = [
    [
        'id' => 1,
        'name_en' => 'Island Hopping Adventure',
        'name_vi' => 'Phiêu Lưu Khám Phá Đảo',
        'description_en' => 'Explore the stunning islands around Nha Trang with our professional guides. Swim, snorkel, and enjoy fresh seafood lunch.',
        'description_vi' => 'Khám phá những hòn đảo tuyệt đẹp quanh Nha Trang với đội ngũ hướng dẫn viên chuyên nghiệp. Bơi, lặn và thưởng thức bữa trưa hải sản tươi ngon.',
        'category_en' => 'Adventure',
        'category_vi' => 'Phiêu Lưu',
        'duration' => '8 hours',
        'max_people' => 12,
        'price_per_person' => 89,
        'meeting_point_en' => 'Resort Lobby',
        'meeting_point_vi' => 'Sảnh Resort',
        'includes' => 'Boat ride, Snorkeling gear, Lunch, Drinks',
        'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1950&q=80'
    ],
    [
        'id' => 2,
        'name_en' => 'City Cultural Tour',
        'name_vi' => 'Tour Văn Hóa Thành Phố',
        'description_en' => 'Discover Nha Trang\'s cultural heritage with visits to ancient temples, historic sites, and local craft villages.',
        'description_vi' => 'Khám phá di sản văn hóa của Nha Trang với các chuyến thăm đến đền chùa cổ, các di tích lịch sử và làng nghề thủ công.',
        'category_en' => 'Cultural',
        'category_vi' => 'Văn Hóa',
        'duration' => '6 hours',
        'max_people' => 15,
        'price_per_person' => 65,
        'meeting_point_en' => 'Resort Lobby',
        'meeting_point_vi' => 'Sảnh Resort',
        'includes' => 'Transportation, Entrance fees, Local guide, Lunch',
        'image_url' => 'https://images.pexels.com/photos/6143369/pexels-photo-6143369.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'
    ],
    [
        'id' => 3,
        'name_en' => 'Sunset Sailing Cruise',
        'name_vi' => 'Du Thuyền Hoàng Hôn',
        'description_en' => 'Enjoy a breathtaking sunset on our luxury catamaran. Includes cocktails, light dinner, and the opportunity to spot marine life.',
        'description_vi' => 'Tận hưởng cảnh hoàng hôn tuyệt đẹp trên du thuyền sang trọng. Bao gồm cocktail, bữa tối nhẹ và cơ hội ngắm nhìn sinh vật biển.',
        'category_en' => 'Relaxation',
        'category_vi' => 'Thư Giãn',
        'duration' => '3 hours',
        'max_people' => 20,
        'price_per_person' => 120,
        'meeting_point_en' => 'Resort Marina',
        'meeting_point_vi' => 'Bến Du Thuyền Resort',
        'includes' => 'Luxury catamaran, Cocktails, Dinner, Live music',
        'image_url' => 'https://images.pexels.com/photos/163236/luxury-yacht-boat-speed-water-163236.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'
    ],
    [
        'id' => 4,
        'name_en' => 'Countryside Bike Tour',
        'name_vi' => 'Tour Xe Đạp Vùng Quê',
        'description_en' => 'Cycle through the picturesque countryside of Nha Trang, visit local villages, rice fields, and experience authentic rural life.',
        'description_vi' => 'Đạp xe qua những vùng quê đẹp như tranh của Nha Trang, thăm các làng quê, ruộng lúa và trải nghiệm cuộc sống nông thôn đích thực.',
        'category_en' => 'Adventure',
        'category_vi' => 'Phiêu Lưu',
        'duration' => '5 hours',
        'max_people' => 10,
        'price_per_person' => 55,
        'meeting_point_en' => 'Resort Lobby',
        'meeting_point_vi' => 'Sảnh Resort',
        'includes' => 'Bike rental, Safety gear, Water, Local snacks',
        'image_url' => 'https://images.pexels.com/photos/100582/pexels-photo-100582.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'
    ],
    [
        'id' => 5,
        'name_en' => 'Cooking Class & Market Tour',
        'name_vi' => 'Lớp Học Nấu Ăn & Tour Chợ',
        'description_en' => 'Learn the art of Vietnamese cuisine with a local chef. Visit the market to select fresh ingredients and prepare traditional dishes.',
        'description_vi' => 'Học nghệ thuật ẩm thực Việt Nam với đầu bếp địa phương. Thăm chợ để chọn nguyên liệu tươi và chế biến các món ăn truyền thống.',
        'category_en' => 'Food',
        'category_vi' => 'Ẩm Thực',
        'duration' => '4 hours',
        'max_people' => 8,
        'price_per_person' => 75,
        'meeting_point_en' => 'Resort Restaurant',
        'meeting_point_vi' => 'Nhà Hàng Resort',
        'includes' => 'Market visit, Ingredients, Cooking tools, Recipe book',
        'image_url' => 'https://images.unsplash.com/photo-1569420067112-b57b4f024595?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1950&q=80'
    ],
    [
        'id' => 6,
        'name_en' => 'Mud Bath Spa Experience',
        'name_vi' => 'Trải Nghiệm Tắm Bùn Khoáng',
        'description_en' => 'Relax in the famous Nha Trang mineral mud baths. Includes spa treatments, mineral water swimming, and private relaxation areas.',
        'description_vi' => 'Thư giãn trong các bồn tắm bùn khoáng nổi tiếng của Nha Trang. Bao gồm các liệu pháp spa, bơi nước khoáng và khu thư giãn riêng.',
        'category_en' => 'Wellness',
        'category_vi' => 'Sức Khỏe',
        'duration' => '4 hours',
        'max_people' => 10,
        'price_per_person' => 95,
        'meeting_point_en' => 'Resort Spa',
        'meeting_point_vi' => 'Spa Resort',
        'includes' => 'Mud bath, Hot mineral bath, Jacuzzi, Massage',
        'image_url' => 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1950&q=80'
    ]
];

// Get current user if logged in
$currentUser = getCurrentUser();

// Page title
$pageTitle = $language === 'vi' ? 'Tours & Hoạt Động' : 'Tours & Activities';

// Include header
include 'includes/header.php';
?>

    <!-- Hero Banner -->
    <section class="tours-hero">
        <div class="tours-hero-content">
            <h1><?php echo $pageTitle; ?></h1>
            <p><?php echo $language === 'vi' ? 'Khám phá Nha Trang với các tour độc đáo và đáng nhớ' : 'Discover Nha Trang with unique and memorable tours'; ?></p>
        </div>
    </section>

    <!-- Tour Filter -->
    <div class="container">
        <div class="tour-filters">
            <form class="filter-form">
                <div class="filter-group">
                    <label class="filter-label"><?php echo $language === 'vi' ? 'Loại tour' : 'Tour Type'; ?></label>
                    <select class="filter-select">
                        <option value=""><?php echo $language === 'vi' ? 'Tất cả các loại' : 'All Types'; ?></option>
                        <option value="adventure"><?php echo $language === 'vi' ? 'Phiêu lưu' : 'Adventure'; ?></option>
                        <option value="cultural"><?php echo $language === 'vi' ? 'Văn hóa' : 'Cultural'; ?></option>
                        <option value="sightseeing"><?php echo $language === 'vi' ? 'Tham quan' : 'Sightseeing'; ?></option>
                        <option value="food"><?php echo $language === 'vi' ? 'Ẩm thực' : 'Food'; ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label"><?php echo $language === 'vi' ? 'Thời gian' : 'Duration'; ?></label>
                    <select class="filter-select">
                        <option value=""><?php echo $language === 'vi' ? 'Bất kỳ thời gian' : 'Any duration'; ?></option>
                        <option value="half-day"><?php echo $language === 'vi' ? 'Nửa ngày' : 'Half day'; ?></option>
                        <option value="full-day"><?php echo $language === 'vi' ? 'Cả ngày' : 'Full day'; ?></option>
                        <option value="multi-day"><?php echo $language === 'vi' ? 'Nhiều ngày' : 'Multi-day'; ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label"><?php echo $language === 'vi' ? 'Giá' : 'Price Range'; ?></label>
                    <select class="filter-select">
                        <option value=""><?php echo $language === 'vi' ? 'Tất cả mức giá' : 'All prices'; ?></option>
                        <option value="budget"><?php echo $language === 'vi' ? 'Tiết kiệm' : 'Budget'; ?></option>
                        <option value="mid-range"><?php echo $language === 'vi' ? 'Trung bình' : 'Mid-range'; ?></option>
                        <option value="luxury"><?php echo $language === 'vi' ? 'Cao cấp' : 'Luxury'; ?></option>
                    </select>
                </div>
                
                <button type="submit" class="filter-button">
                    <i class="fas fa-search"></i> <?php echo $language === 'vi' ? 'Tìm kiếm' : 'Search'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Tours List -->
    <section class="tours-container">
        <div class="container">
            <div class="tours-header">
                <h2><?php echo $language === 'vi' ? 'Khám phá các tour của chúng tôi' : 'Explore our Tours'; ?></h2>
                <p><?php echo $language === 'vi' ? 'Trải nghiệm tốt nhất tại Nha Trang với các tour được thiết kế tỉ mỉ của chúng tôi' : 'Experience the best of Nha Trang with our carefully crafted tours'; ?></p>
            </div>

            <?php if (empty($tours)): ?>
                <div class="no-results">
                    <h2><?php echo $language === 'vi' ? 'Hiện không có tour nào' : 'No tours available at the moment'; ?></h2>
                    <p><?php echo $language === 'vi' ? 'Vui lòng quay lại sau' : 'Please check back later'; ?></p>
                </div>
            <?php else: ?>
                <div class="tour-grid">
                    <?php foreach ($tours as $tour): ?>
                        <div class="tour-card">
                            <?php if (!empty($tour['category'])): ?>
                                <span class="tour-badge"><?php echo htmlspecialchars($tour[$language === 'vi' ? 'category_vi' : 'category_en']); ?></span>
                            <?php endif; ?>
                            
                            <div class="tour-image">
                                <img src="<?php echo htmlspecialchars($tour['image_url']); ?>" alt="<?php echo htmlspecialchars($tour[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                            </div>
                            
                            <div class="tour-details">
                                <h3><?php echo htmlspecialchars($tour[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                
                                <p class="tour-description">
                                    <?php 
                                        $description = $tour[$language === 'vi' ? 'description_vi' : 'description_en'];
                                        echo htmlspecialchars(strlen($description) > 120 ? substr($description, 0, 120).'...' : $description); 
                                    ?>
                                </p>
                                
                                <div class="tour-meta">
                                    <span class="tour-meta-item">
                                        <i class="fas fa-clock"></i>
                                        <?php echo htmlspecialchars($tour['duration']); ?>
                                    </span>
                                    
                                    <span class="tour-meta-item">
                                        <i class="fas fa-users"></i>
                                        <?php echo $language === 'vi' ? 'Tối đa' : 'Max'; ?> <?php echo htmlspecialchars($tour['max_people']); ?> <?php echo $language === 'vi' ? 'người' : 'people'; ?>
                                    </span>
                                    
                                    <span class="tour-meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($tour[$language === 'vi' ? 'meeting_point_vi' : 'meeting_point_en']); ?>
                                    </span>
                                </div>
                                
                                <div class="tour-features">
                                    <?php if (!empty($tour['includes'])): ?>
                                        <?php $includes = explode(',', $tour['includes']); ?>
                                        <?php foreach(array_slice($includes, 0, 3) as $include): ?>
                                            <span class="tour-feature">
                                                <i class="fas fa-check-circle"></i>
                                                <?php echo htmlspecialchars(trim($include)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="tour-footer">
                                    <div class="tour-price">
                                        <span class="price-value"><?php echo formatCurrency($tour['price_per_person']); ?></span>
                                        <span class="price-label"><?php echo $language === 'vi' ? '/ người' : '/ person'; ?></span>
                                    </div>
                                    
                                    <a href="tour-details.php?id=<?php echo $tour['id']; ?>" class="tour-button">
                                        <?php echo $language === 'vi' ? 'Chi tiết' : 'View Details'; ?>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
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
            <div class="benefits-header">
                <h2><?php echo $language === 'vi' ? 'Tại sao chọn tour của chúng tôi' : 'Why Choose Our Tours'; ?></h2>
                <p><?php echo $language === 'vi' ? 'Chúng tôi cam kết mang đến cho bạn những trải nghiệm tuyệt vời nhất' : 'We are committed to providing you with the best experiences'; ?></p>
            </div>
            
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

    <!-- Call to Action -->
    <section class="tours-cta">
        <div class="container">
            <div class="cta-content">
                <h2><?php echo $language === 'vi' ? 'Sẵn sàng khám phá Nha Trang?' : 'Ready to explore Nha Trang?'; ?></h2>
                <p><?php echo $language === 'vi' ? 'Đặt tour ngay hôm nay và tận hưởng kì nghỉ đáng nhớ!' : 'Book your tour today and enjoy an unforgettable vacation!'; ?></p>
                <div class="cta-buttons">
                    <a href="#" class="cta-primary"><?php echo $language === 'vi' ? 'Đặt ngay' : 'Book Now'; ?></a>
                    <a href="contact.php" class="cta-secondary"><?php echo $language === 'vi' ? 'Liên hệ chúng tôi' : 'Contact Us'; ?></a>
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
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Vinpearl Resort Nha Trang. <?php echo $language === 'vi' ? 'Đã đăng ký bản quyền.' : 'All rights reserved.'; ?></p>
            </div>
        </div>
    </footer>

    <!-- Back to top button -->
    <a href="#" class="back-to-top">
        <i class="fas fa-chevron-up"></i>
    </a>

    <script src="assets/js/script.js"></script>
    <script>
    // Simple script for back to top button
    const backToTopButton = document.querySelector('.back-to-top');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('active');
        } else {
            backToTopButton.classList.remove('active');
        }
    });
    
    backToTopButton.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    </script>
</body>
</html>