<?php
/**
 * Vinpearl Resort Nha Trang - Homepage
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language (in a real implementation, this would be more sophisticated)
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get content for the homepage
$heroTitle = getContent('home', 'hero', 'title', $language);
$heroSubtitle = getContent('home', 'hero', 'subtitle', $language);
$aboutTitle = getContent('home', 'about', 'title', $language);
$aboutContent = getContent('home', 'about', 'content', $language);

// Get featured rooms
$featuredRooms = getRooms([], $language);
$featuredRooms = array_slice($featuredRooms, 0, 3); // Get only 3 rooms for featured section

// Get promotional banners
$banners = getPromotionalBanners();

// Get current user if logged in
$currentUser = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($language == 'en') ? 'Vinpearl Resort Nha Trang - Luxury Beach Resort' : 'Vinpearl Resort Nha Trang - Khu Nghỉ Dưỡng Biển Sang Trọng'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home-styles.css">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo/logo.svg" alt="Vinpearl Resort Nha Trang">
                </a>
            </div>
            
            <nav class="main-navigation">
                <ul>
                    <li><a href="index.php" class="active"><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a></li>
                    <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                    <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                    <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
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

    <!-- Hero Banner -->
    <section class="hero" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('assets/images/hero/hero-bg.jpg');">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo $heroTitle ?: 'Welcome to Vinpearl Resort Nha Trang'; ?></h1>
                <p><?php echo $heroSubtitle ?: 'Experience luxury by the beautiful Nha Trang beach'; ?></p>
                
                <!-- Booking Search Form -->
                <div class="booking-search-container">
                    <form action="rooms.php" method="get" class="booking-search-form">
                        <div class="form-group">
                            <label for="check_in_date"><?php echo $language === 'vi' ? 'Nhận phòng' : 'Check In'; ?></label>
                            <input type="date" id="check_in_date" name="check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="check_out_date"><?php echo $language === 'vi' ? 'Trả phòng' : 'Check Out'; ?></label>
                            <input type="date" id="check_out_date" name="check_out" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="guests"><?php echo $language === 'vi' ? 'Khách' : 'Guests'; ?></label>
                            <select id="guests" name="guests" class="form-control">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="room_type"><?php echo $language === 'vi' ? 'Loại phòng' : 'Room Type'; ?></label>
                            <select id="room_type" name="room_type" class="form-control">
                                <option value=""><?php echo $language === 'vi' ? 'Tất cả loại phòng' : 'All Room Types'; ?></option>
                                <option value="deluxe"><?php echo $language === 'vi' ? 'Phòng Deluxe' : 'Deluxe Room'; ?></option>
                                <option value="suite"><?php echo $language === 'vi' ? 'Phòng Suite' : 'Suite'; ?></option>
                                <option value="villa"><?php echo $language === 'vi' ? 'Biệt thự' : 'Villa'; ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> <?php echo $language === 'vi' ? 'Tìm Phòng' : 'Search Rooms'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Promotional Banners -->
    <?php if (!empty($banners)): ?>
    <section class="section">
        <div class="container">
            <div class="promotional-carousel">
                <?php foreach ($banners as $banner): ?>
                <div class="promo-card">
                    <div class="promo-image">
                        <img src="<?php echo htmlspecialchars($banner['image_url']); ?>" alt="<?php echo htmlspecialchars($banner[$language === 'vi' ? 'title_vi' : 'title_en']); ?>">
                    </div>
                    <div class="promo-content">
                        <span class="promo-tag"><?php echo $language === 'vi' ? 'Ưu đãi đặc biệt' : 'Special Offer'; ?></span>
                        <h3><?php echo htmlspecialchars($banner[$language === 'vi' ? 'title_vi' : 'title_en']); ?></h3>
                        <p><?php echo htmlspecialchars($banner[$language === 'vi' ? 'description_vi' : 'description_en']); ?></p>
                        <?php if (!empty($banner['link_url'])): ?>
                        <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" class="btn btn-accent">
                            <?php echo $language === 'vi' ? 'Xem chi tiết' : 'Learn More'; ?> <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- About Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="about-content">
                        <div class="section-heading">
                            <span class="section-subtitle"><?php echo $language === 'vi' ? 'Khám phá' : 'Discover'; ?></span>
                            <h2 class="section-title"><?php echo $aboutTitle ?: 'About Our Resort'; ?></h2>
                        </div>
                        <p><?php echo $aboutContent ?: 'Vinpearl Resort Nha Trang offers the perfect blend of luxury and natural beauty. Located on a private beach, our resort features spacious rooms with stunning views, world-class dining options, and a range of activities for all ages.'; ?></p>
                        
                        <div class="resort-highlights">
                            <div class="highlight-item">
                                <i class="fas fa-umbrella-beach"></i>
                                <h4><?php echo $language === 'vi' ? 'Bãi biển riêng' : 'Private Beach'; ?></h4>
                                <p><?php echo $language === 'vi' ? 'Tận hưởng bãi biển riêng của chúng tôi' : 'Enjoy our exclusive private beach'; ?></p>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-concierge-bell"></i>
                                <h4><?php echo $language === 'vi' ? 'Phục vụ 24/7' : '24/7 Service'; ?></h4>
                                <p><?php echo $language === 'vi' ? 'Đội ngũ nhân viên phục vụ 24/7' : '24/7 staff service available'; ?></p>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-utensils"></i>
                                <h4><?php echo $language === 'vi' ? 'Ẩm thực đẳng cấp' : 'Fine Dining'; ?></h4>
                                <p><?php echo $language === 'vi' ? 'Thưởng thức ẩm thực đẳng cấp 5 sao' : 'Enjoy 5-star dining experiences'; ?></p>
                            </div>
                        </div>
                        
                        <a href="contact.php" class="btn btn-primary mt-4">
                            <?php echo $language === 'vi' ? 'Liên hệ với chúng tôi' : 'Contact Us'; ?> <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="about-image-gallery">
                        <div class="main-image">
                            <img src="assets/images/about/resort-main.jpg" alt="Vinpearl Resort Overview" class="img-fluid rounded shadow">
                        </div>
                        <div class="small-images">
                            <img src="assets/images/about/resort-pool.jpg" alt="Resort Pool" class="img-fluid rounded shadow">
                            <img src="assets/images/about/resort-beach.jpg" alt="Resort Beach" class="img-fluid rounded shadow">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Rooms -->
    <section class="section">
        <div class="container">
            <div class="section-heading text-center">
                <span class="section-subtitle"><?php echo $language === 'vi' ? 'Chỗ ở sang trọng' : 'Luxury Accommodations'; ?></span>
                <h2 class="section-title"><?php echo $language === 'vi' ? 'Phòng nổi bật' : 'Featured Rooms'; ?></h2>
                <p class="section-desc"><?php echo $language === 'vi' ? 'Khám phá phòng nghỉ sang trọng, thoải mái và hiện đại tại Vinpearl Resort' : 'Discover our luxurious, comfortable and elegantly designed accommodations at Vinpearl Resort'; ?></p>
            </div>
            
            <div class="room-cards">
                <?php foreach ($featuredRooms as $room): ?>
                <div class="room-card">
                    <div class="room-image">
                        <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="<?php echo htmlspecialchars($room[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                        <div class="room-overlay">
                            <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn btn-outline-light"><?php echo $language === 'vi' ? 'Xem chi tiết' : 'View Details'; ?></a>
                        </div>
                        <div class="room-badge"><?php echo $language === 'vi' ? 'Phổ biến' : 'Popular'; ?></div>
                    </div>
                    <div class="room-details">
                        <h3><a href="room-details.php?id=<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room[$language === 'vi' ? 'name_vi' : 'name_en']); ?></a></h3>
                        <p class="room-description"><?php echo htmlspecialchars(substr($room[$language === 'vi' ? 'description_vi' : 'description_en'], 0, 100) . '...'); ?></p>
                        
                        <div class="room-amenities">
                            <?php 
                            $amenities = json_decode($room['amenities'], true);
                            $displayAmenities = array_slice($amenities, 0, 4);
                            foreach ($displayAmenities as $amenity): 
                            ?>
                                <span class="amenity-tag"><?php echo htmlspecialchars($amenity); ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="room-features">
                            <span><i class="fas fa-user"></i> <?php echo $room['capacity']; ?> <?php echo $language === 'vi' ? 'Người' : 'Guests'; ?></span>
                            <span><i class="fas fa-bed"></i> <?php echo htmlspecialchars($room['bed_type']); ?></span>
                            <span><i class="fas fa-ruler-combined"></i> <?php echo htmlspecialchars($room['room_size']); ?></span>
                        </div>
                        
                        <div class="room-price-container">
                            <div class="room-price">
                                <span class="price"><?php echo formatCurrency($room['price_per_night']); ?></span>
                                <span class="per-night"><?php echo $language === 'vi' ? '/ đêm' : '/ night'; ?></span>
                            </div>
                            <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn btn-sm btn-primary"><?php echo $language === 'vi' ? 'Đặt Ngay' : 'Book Now'; ?></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="rooms.php" class="btn btn-outline"><?php echo $language === 'vi' ? 'Xem tất cả phòng' : 'View All Rooms'; ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Services & Amenities -->
    <section class="services-amenities">
        <div class="container">
            <h2><?php echo $language === 'vi' ? 'Dịch vụ & Tiện nghi' : 'Services & Amenities'; ?></h2>
            
            <div class="services-grid">
                <div class="service-item">
                    <div class="service-icon">
                        <img src="assets/images/icons/pool.svg" alt="Swimming Pool">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Hồ bơi' : 'Swimming Pool'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Thư giãn tại hồ bơi vô cực nhìn ra biển' : 'Relax in our infinity pool overlooking the ocean'; ?></p>
                </div>
                
                <div class="service-item">
                    <div class="service-icon">
                        <img src="assets/images/icons/restaurant.svg" alt="Restaurant">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Nhà hàng' : 'Restaurant'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Thưởng thức ẩm thực địa phương và quốc tế' : 'Enjoy local and international cuisine'; ?></p>
                </div>
                
                <div class="service-item">
                    <div class="service-icon">
                        <img src="assets/images/icons/spa.svg" alt="Spa">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Spa' : 'Spa'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Tái tạo năng lượng với các liệu pháp spa của chúng tôi' : 'Rejuvenate with our spa treatments'; ?></p>
                </div>
                
                <div class="service-item">
                    <div class="service-icon">
                        <img src="assets/images/icons/beach.svg" alt="Private Beach">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Bãi biển riêng' : 'Private Beach'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Truy cập trực tiếp đến bãi biển riêng' : 'Direct access to private beach'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section testimonials-section">
        <div class="container">
            <div class="section-heading text-center">
                <span class="section-subtitle"><?php echo $language === 'vi' ? 'Ý kiến khách hàng' : 'Guest Reviews'; ?></span>
                <h2 class="section-title"><?php echo $language === 'vi' ? 'Khách hàng nói gì về chúng tôi' : 'What Our Guests Say'; ?></h2>
            </div>
            
            <div class="testimonial-carousel">
                <?php
                // Get reviews from database
                $conn = connectDatabase();
                $query = "SELECT r.*, u.full_name 
                        FROM reviews r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.is_approved = 1 AND r.is_hidden = 0 
                        ORDER BY r.created_at DESC LIMIT 6";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0):
                    while ($review = $result->fetch_assoc()):
                ?>
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $review['rating']): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 == $review['rating']): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="testimonial-content">
                        <p class="testimonial-text">"<?php echo htmlspecialchars($review[$language === 'vi' ? 'content_vi' : 'content_en']); ?>"</p>
                    </div>
                    
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="assets/images/avatar-placeholder.jpg" alt="<?php echo htmlspecialchars($review['full_name']); ?>">
                        </div>
                        <div class="author-details">
                            <h4><?php echo htmlspecialchars($review['full_name']); ?></h4>
                            <p class="author-info">
                                <?php 
                                    if ($review['type'] === 'room') {
                                        echo $language === 'vi' ? 'Khách lưu trú' : 'Hotel Guest';
                                    } elseif ($review['type'] === 'package') {
                                        echo $language === 'vi' ? 'Gói kỳ nghỉ' : 'Package Guest';
                                    } else {
                                        echo $language === 'vi' ? 'Khách tour' : 'Tour Guest';
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                    $conn->close();
                else:
                ?>
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="testimonial-content">
                        <p class="testimonial-text">"<?php echo $language === 'vi' ? 'Kỳ nghỉ tuyệt vời tại Vinpearl Resort! Phòng tuyệt đẹp, nhân viên thân thiện và dịch vụ hoàn hảo. Chúng tôi sẽ quay lại vào năm tới.' : 'Amazing vacation at Vinpearl Resort! Beautiful rooms, friendly staff, and perfect service. We will be back next year.'; ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="assets/images/avatar-placeholder.jpg" alt="John Doe">
                        </div>
                        <div class="author-details">
                            <h4>John Doe</h4>
                            <p class="author-info"><?php echo $language === 'vi' ? 'Khách lưu trú' : 'Hotel Guest'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="testimonial-content">
                        <p class="testimonial-text">"<?php echo $language === 'vi' ? 'Trải nghiệm thư giãn tuyệt vời. Hồ bơi vô cực và bãi biển riêng là điểm nhấn của kỳ nghỉ. Đội ngũ nhân viên rất chuyên nghiệp.' : 'Wonderful relaxing experience. The infinity pool and private beach were the highlights. Staff was very professional.'; ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="assets/images/avatar-placeholder.jpg" alt="Jane Smith">
                        </div>
                        <div class="author-details">
                            <h4>Jane Smith</h4>
                            <p class="author-info"><?php echo $language === 'vi' ? 'Khách lưu trú' : 'Hotel Guest'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="testimonial-content">
                        <p class="testimonial-text">"<?php echo $language === 'vi' ? 'Gói gia đình hoàn hảo cho kỳ nghỉ của chúng tôi. Các hoạt động rất phong phú, đặc biệt là các tour đảo rất vui. Nhà hàng rất ngon.' : 'Perfect family package for our vacation. Great activities, especially the island tour was fun. The restaurant was excellent.'; ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="assets/images/avatar-placeholder.jpg" alt="Nguyen Van A">
                        </div>
                        <div class="author-details">
                            <h4>Nguyen Van A</h4>
                            <p class="author-info"><?php echo $language === 'vi' ? 'Gói kỳ nghỉ' : 'Package Guest'; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/hero/cta-bg.jpg');">
        <div class="container">
            <div class="cta-content">
                <h2><?php echo $language === 'vi' ? 'Đặt kỳ nghỉ mơ ước của bạn ngay hôm nay' : 'Book Your Dream Vacation Today'; ?></h2>
                <p><?php echo $language === 'vi' ? 'Trải nghiệm dịch vụ đẳng cấp thế giới và khung cảnh tuyệt đẹp tại Vinpearl Resort Nha Trang' : 'Experience world-class service and breathtaking views at Vinpearl Resort Nha Trang'; ?></p>
                <div class="cta-buttons">
                    <a href="rooms.php" class="btn btn-primary btn-lg"><?php echo $language === 'vi' ? 'Đặt ngay' : 'Book Now'; ?></a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg"><?php echo $language === 'vi' ? 'Liên hệ chúng tôi' : 'Contact Us'; ?></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="footer-widget">
                            <div class="footer-logo">
                                <img src="assets/images/logo/logo.svg" alt="Vinpearl Resort Nha Trang">
                            </div>
                            <p><?php echo $language === 'vi' ? 'Vinpearl Resort Nha Trang là khu nghỉ dưỡng sang trọng với tầm nhìn tuyệt đẹp ra biển và dịch vụ đẳng cấp 5 sao.' : 'Vinpearl Resort Nha Trang is a luxury resort with stunning ocean views and 5-star service.'; ?></p>
                            <div class="footer-social">
                                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                                <a href="#" aria-label="TripAdvisor"><i class="fab fa-tripadvisor"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <div class="footer-widget">
                            <h3 class="footer-widget-title"><?php echo $language === 'vi' ? 'Liên kết' : 'Quick Links'; ?></h3>
                            <ul class="footer-links">
                                <li><a href="index.php"><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a></li>
                                <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                                <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                                <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                                <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                                <li><a href="contact.php"><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact'; ?></a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget">
                            <h3 class="footer-widget-title"><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact Info'; ?></h3>
                            <ul class="footer-contact">
                                <li>
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo $language === 'vi' ? 'Địa chỉ' : 'Address'; ?>:</span>
                                    <p>Vinpearl Resort Nha Trang, Đảo Hòn Tre, Nha Trang, Việt Nam</p>
                                </li>
                                <li>
                                    <i class="fas fa-phone-alt"></i>
                                    <span><?php echo $language === 'vi' ? 'Điện thoại' : 'Phone'; ?>:</span>
                                    <p>+84 258 598 9999</p>
                                </li>
                                <li>
                                    <i class="fas fa-envelope"></i>
                                    <span>Email:</span>
                                    <p>info@vinpearl.com</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget">
                            <h3 class="footer-widget-title"><?php echo $language === 'vi' ? 'Bản tin' : 'Newsletter'; ?></h3>
                            <p><?php echo $language === 'vi' ? 'Đăng ký để nhận thông tin về ưu đãi và sự kiện mới nhất' : 'Subscribe to get updates about our offers and events'; ?></p>
                            <form class="footer-newsletter">
                                <input type="email" placeholder="<?php echo $language === 'vi' ? 'Email của bạn' : 'Your Email'; ?>" required>
                                <button type="submit"><i class="fas fa-paper-plane"></i></button>
                            </form>
                            <div class="payment-methods">
                                <span><?php echo $language === 'vi' ? 'Chúng tôi chấp nhận' : 'We Accept'; ?>:</span>
                                <div class="payment-icons">
                                    <i class="fab fa-cc-visa"></i>
                                    <i class="fab fa-cc-mastercard"></i>
                                    <i class="fab fa-cc-amex"></i>
                                    <i class="fab fa-cc-paypal"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p class="copyright">&copy; <?php echo date('Y'); ?> Vinpearl Resort Nha Trang. <?php echo $language === 'vi' ? 'Đã đăng ký bản quyền.' : 'All rights reserved.'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <ul class="footer-bottom-links">
                            <li><a href="#"><?php echo $language === 'vi' ? 'Điều khoản sử dụng' : 'Terms of Service'; ?></a></li>
                            <li><a href="#"><?php echo $language === 'vi' ? 'Chính sách bảo mật' : 'Privacy Policy'; ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" aria-label="Back to Top"><i class="fas fa-chevron-up"></i></a>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html> 