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

// Include header
include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo $heroTitle ?: ($language === 'vi' ? 'Chào mừng đến với Vinpearl Resort Nha Trang' : 'Welcome to Vinpearl Resort Nha Trang'); ?></h1>
                <p><?php echo $heroSubtitle ?: ($language === 'vi' ? 'Trải nghiệm sự sang trọng bên bờ biển Nha Trang xinh đẹp' : 'Experience luxury by the beautiful Nha Trang beach'); ?></p>
                
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
                            <label for="adults"><?php echo $language === 'vi' ? 'Người lớn' : 'Adults'; ?></label>
                            <select id="adults" name="adults" class="form-control">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="children"><?php echo $language === 'vi' ? 'Trẻ em' : 'Children'; ?></label>
                            <select id="children" name="children" class="form-control">
                                <?php for ($i = 0; $i <= 3; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary search-btn">
                            <?php echo $language === 'vi' ? 'Tìm phòng' : 'Search Rooms'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Promotional Section -->
    <section class="promotional-section">
        <div class="container">
            <div class="section-heading text-center">
                <span class="section-subtitle"><?php echo $language === 'vi' ? 'Ưu đãi đặc biệt' : 'Special Offers'; ?></span>
                <h2 class="section-title"><?php echo $language === 'vi' ? 'Khuyến mãi hấp dẫn' : 'Exclusive Promotions'; ?></h2>
            </div>
            
            <div class="promotional-carousel">
                <?php
                // Mocked promotional banner data
                $mockPromotions = [
                    [
                        'title_en' => 'Summer Special',
                        'title_vi' => 'Ưu Đãi Mùa Hè',
                        'description_en' => 'Enjoy 20% off on all room bookings during summer months',
                        'description_vi' => 'Giảm 20% cho tất cả các đặt phòng trong những tháng hè',
                        'image_url' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?q=80&w=1600&auto=format&fit=crop',
                        'link_url' => 'promotions/summer-special'
                    ],
                    [
                        'title_en' => 'Honeymoon Package',
                        'title_vi' => 'Gói Trăng Mật',
                        'description_en' => 'Special honeymoon package with romantic dinner and spa treatments',
                        'description_vi' => 'Gói trăng mật đặc biệt với bữa tối lãng mạn và các liệu pháp spa',
                        'image_url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?q=80&w=1600&auto=format&fit=crop',
                        'link_url' => 'packages/honeymoon'
                    ],
                    [
                        'title_en' => 'Family Fun',
                        'title_vi' => 'Vui Chơi Gia Đình',
                        'description_en' => 'Create unforgettable memories with our special family package including activities for children',
                        'description_vi' => 'Tạo những kỷ niệm khó quên với gói gia đình đặc biệt của chúng tôi bao gồm các hoạt động cho trẻ em',
                        'image_url' => 'https://images.unsplash.com/photo-1551524358-f34c0214781d?q=80&w=1600&auto=format&fit=crop',
                        'link_url' => 'packages/family'
                    ]
                ];

                foreach ($mockPromotions as $promotion): 
                ?>
                <div class="promo-card">
                    <div class="promo-image">
                        <img src="<?php echo htmlspecialchars($promotion['image_url']); ?>" alt="<?php echo htmlspecialchars($promotion[$language === 'vi' ? 'title_vi' : 'title_en']); ?>">
                    </div>
                    <div class="promo-content">
                        <span class="promo-tag"><?php echo $language === 'vi' ? 'Ưu đãi đặc biệt' : 'Special Offer'; ?></span>
                        <h3><?php echo htmlspecialchars($promotion[$language === 'vi' ? 'title_vi' : 'title_en']); ?></h3>
                        <p><?php echo htmlspecialchars($promotion[$language === 'vi' ? 'description_vi' : 'description_en']); ?></p>
                        <a href="<?php echo htmlspecialchars($promotion['link_url']); ?>" class="btn btn-primary">
                            <?php echo $language === 'vi' ? 'Xem thêm' : 'View Details'; ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-container">
                <div class="about-content">
                    <span class="section-subtitle"><?php echo $language === 'vi' ? 'Về chúng tôi' : 'About Us'; ?></span>
                    <h2><?php echo $aboutTitle ?: ($language === 'vi' ? 'Khu nghỉ dưỡng sang trọng bậc nhất tại Nha Trang' : 'The Premier Luxury Resort in Nha Trang'); ?></h2>
                    
                    <p><?php echo $aboutContent ?: ($language === 'vi' ? 'Vinpearl Resort Nha Trang là biểu tượng của sự sang trọng và tinh tế, tọa lạc tại một trong những bãi biển đẹp nhất Việt Nam. Với kiến trúc hiện đại kết hợp nét đẹp truyền thống Việt Nam, khu nghỉ dưỡng của chúng tôi mang đến trải nghiệm đẳng cấp 5 sao với dịch vụ chuyên nghiệp, tiện nghi hiện đại và ẩm thực đẳng cấp quốc tế.' : 'Vinpearl Resort Nha Trang is the epitome of luxury and sophistication, situated on one of Vietnam\'s most beautiful beaches. With modern architecture blended with traditional Vietnamese elegance, our resort offers a 5-star experience with professional service, modern amenities and world-class cuisine.'); ?></p>
                    
                    <a href="#" class="btn btn-primary"><?php echo $language === 'vi' ? 'Tìm hiểu thêm' : 'Learn More'; ?></a>
                    
                    <div class="about-stats">
                        <div class="stat-item">
                            <div class="stat-value">15+</div>
                            <div class="stat-label"><?php echo $language === 'vi' ? 'Năm kinh nghiệm' : 'Years of Excellence'; ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">200+</div>
                            <div class="stat-label"><?php echo $language === 'vi' ? 'Phòng & Suite' : 'Rooms & Suites'; ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">5</div>
                            <div class="stat-label"><?php echo $language === 'vi' ? 'Nhà hàng' : 'Restaurants'; ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">98%</div>
                            <div class="stat-label"><?php echo $language === 'vi' ? 'Khách hài lòng' : 'Happy Guests'; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="about-images">
                    <div class="main-image">
                        <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Vinpearl Resort Overview" class="img-fluid rounded shadow">
                    </div>
                    <div class="small-images">
                        <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1480&q=80" alt="Resort Pool" class="img-fluid rounded shadow">
                        <img src="https://images.unsplash.com/photo-1590001155093-a3c66ab0c3ff?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1480&q=80" alt="Resort Beach" class="img-fluid rounded shadow">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Rooms -->
    <section class="featured-rooms">
        <div class="container">
            <div class="section-heading text-center">
                <span class="section-subtitle"><?php echo $language === 'vi' ? 'Chỗ ở sang trọng' : 'Luxury Accommodations'; ?></span>
                <h2 class="section-title"><?php echo $language === 'vi' ? 'Phòng nổi bật' : 'Featured Rooms'; ?></h2>
            </div>
            
            <div class="row">
                <?php
                // Mocked featured rooms data
                $mockRooms = [
                    [
                        'id' => 1,
                        'name_en' => 'Deluxe Ocean View',
                        'name_vi' => 'Phòng Deluxe Hướng Biển',
                        'description_en' => 'Spacious room with breathtaking ocean views, modern amenities, and a private balcony.',
                        'description_vi' => 'Phòng rộng rãi với tầm nhìn tuyệt đẹp ra biển, tiện nghi hiện đại và ban công riêng.',
                        'price_per_night' => 150.0,
                        'capacity' => 2,
                        'room_size' => '40 m²',
                        'bed_type' => 'King',
                        'amenities' => ['Air conditioning', 'Flat-screen TV', 'Free WiFi', 'Minibar', 'Safe'],
                        'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=1600&auto=format&fit=crop'
                    ],
                    [
                        'id' => 2,
                        'name_en' => 'Premium Garden Suite',
                        'name_vi' => 'Phòng Suite Hướng Vườn',
                        'description_en' => 'Elegant suite with garden views, separate living area, and exclusive amenities.',
                        'description_vi' => 'Phòng suite sang trọng với tầm nhìn ra vườn, khu vực sinh hoạt riêng biệt và tiện nghi độc quyền.',
                        'price_per_night' => 250.0,
                        'capacity' => 2,
                        'room_size' => '60 m²',
                        'bed_type' => 'King',
                        'amenities' => ['Air conditioning', 'Flat-screen TV', 'Free WiFi', 'Minibar', 'Safe', 'Bathtub', 'Coffee machine'],
                        'image_url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?q=80&w=1600&auto=format&fit=crop'
                    ],
                    [
                        'id' => 3,
                        'name_en' => 'Family Beach Villa',
                        'name_vi' => 'Biệt Thự Bãi Biển Gia Đình',
                        'description_en' => 'Spacious villa perfect for families, with direct beach access and a private pool.',
                        'description_vi' => 'Biệt thự rộng rãi hoàn hảo cho gia đình, với lối đi trực tiếp ra bãi biển và hồ bơi riêng.',
                        'price_per_night' => 450.0,
                        'capacity' => 4,
                        'room_size' => '120 m²',
                        'bed_type' => '2 Kings',
                        'amenities' => ['Air conditioning', 'Flat-screen TV', 'Free WiFi', 'Minibar', 'Safe', 'Private pool', 'Kitchen', 'Dining area', 'Beach access'],
                        'image_url' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?q=80&w=1600&auto=format&fit=crop'
                    ]
                ];
                
                foreach ($mockRooms as $room): 
                ?>
                <div class="col-md-4">
                    <div class="room-card">
                        <div class="room-image">
                            <img src="<?php echo $room['image_url']; ?>" alt="<?php echo htmlspecialchars($room[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                        </div>
                        <div class="room-details">
                            <h3><a href="room-details.php?id=<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room[$language === 'vi' ? 'name_vi' : 'name_en']); ?></a></h3>
                            <p class="room-description"><?php echo htmlspecialchars(substr($room[$language === 'vi' ? 'description_vi' : 'description_en'], 0, 100) . '...'); ?></p>
                            
                            <div class="room-amenities">
                                <?php 
                                $displayAmenities = array_slice($room['amenities'], 0, 4);
                                foreach ($displayAmenities as $amenity): 
                                ?>
                                    <span class="amenity-tag"><?php echo htmlspecialchars($amenity); ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="room-features">
                                <span><i class="fas fa-user"></i> <?php echo $room['capacity']; ?> <?php echo $language === 'vi' ? 'khách' : 'guests'; ?></span>
                                <span><i class="fas fa-vector-square"></i> <?php echo $room['room_size']; ?></span>
                                <span><i class="fas fa-bed"></i> <?php echo $room['bed_type']; ?></span>
                            </div>
                            
                            <div class="room-price">
                                <div class="price-tag">
                                    <span class="price"><?php echo formatCurrency($room['price_per_night']); ?></span>
                                    <span class="price-period">/ <?php echo $language === 'vi' ? 'đêm' : 'night'; ?></span>
                                </div>
                                <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn btn-sm btn-outline"><?php echo $language === 'vi' ? 'Chi tiết' : 'Details'; ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="view-all-rooms">
                <a href="rooms.php" class="btn btn-primary"><?php echo $language === 'vi' ? 'Xem tất cả phòng' : 'View All Rooms'; ?></a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <div class="section-heading text-center">
                <span class="section-subtitle"><?php echo $language === 'vi' ? 'Dịch vụ của chúng tôi' : 'Our Services'; ?></span>
                <h2 class="section-title"><?php echo $language === 'vi' ? 'Trải nghiệm đẳng cấp' : 'Premium Experiences'; ?></h2>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/3898/3898495.png" alt="Spa & Wellness">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Spa & Làm đẹp' : 'Spa & Wellness'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Thư giãn với các liệu pháp spa đẳng cấp thế giới trong không gian yên bình.' : 'Relax with world-class spa treatments in a peaceful sanctuary.'; ?></p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/782/782569.png" alt="Fine Dining">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Ẩm thực tinh tế' : 'Fine Dining'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Khám phá ẩm thực đa dạng với các nhà hàng phục vụ món ăn địa phương và quốc tế.' : 'Explore diverse cuisines with restaurants serving local and international dishes.'; ?></p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/2784/2784593.png" alt="Swimming Pools">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Hồ bơi vô cực' : 'Infinity Pools'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Đắm mình trong làn nước xanh mát với tầm nhìn tuyệt đẹp ra biển.' : 'Immerse yourself in cool blue waters with stunning ocean views.'; ?></p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/2503/2503508.png" alt="Activities">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Hoạt động giải trí' : 'Recreational Activities'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Tham gia các hoạt động thú vị từ thể thao dưới nước đến tour du lịch văn hóa.' : 'Engage in exciting activities from water sports to cultural tours.'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-heading text-center">
                <span class="section-subtitle"><?php echo $language === 'vi' ? 'Đánh giá từ khách hàng' : 'Guest Reviews'; ?></span>
                <h2 class="section-title"><?php echo $language === 'vi' ? 'Khách hàng nói gì về chúng tôi' : 'What Our Guests Say'; ?></h2>
            </div>
            
            <div class="testimonial-slider">
                <div class="testimonial-slide">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="testimonial-quote">
                        <?php echo $language === 'vi' ? 'Kỳ nghỉ tuyệt vời nhất mà tôi từng có! Phòng sang trọng, nhân viên thân thiện và tầm nhìn ra biển thật đẹp. Tôi sẽ quay lại vào năm sau với gia đình.' : 'The best vacation I have ever had! Luxurious rooms, friendly staff and beautiful ocean views. I will be back next year with my family.'; ?>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson">
                        </div>
                        <div class="author-details">
                            <h4>Sarah Johnson</h4>
                            <p class="author-info"><?php echo $language === 'vi' ? 'Khách thường xuyên' : 'Regular Guest'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-slide">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="testimonial-quote">
                        <?php echo $language === 'vi' ? 'Dịch vụ 5 sao thực sự! Nhà hàng của resort phục vụ những món ăn ngon nhất mà tôi từng thưởng thức ở Việt Nam. Spa cũng thật tuyệt vời.' : 'Truly 5-star service! The resort\'s restaurant serves the best food I\'ve tasted in Vietnam. The spa was also amazing.'; ?>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="David Chen">
                        </div>
                        <div class="author-details">
                            <h4>David Chen</h4>
                            <p class="author-info"><?php echo $language === 'vi' ? 'Du khách doanh nhân' : 'Business Traveler'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-slide">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="testimonial-quote">
                        <?php echo $language === 'vi' ? 'Chúng tôi đã tổ chức đám cưới tại Vinpearl và đó là quyết định đúng đắn nhất. Địa điểm tuyệt đẹp, dịch vụ chuyên nghiệp và ký ức không thể nào quên!' : 'We held our wedding at Vinpearl and it was the best decision. Beautiful venue, professional service and unforgettable memories!'; ?>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Emily & Michael">
                        </div>
                        <div class="author-details">
                            <h4>Emily & Michael</h4>
                            <p class="author-info"><?php echo $language === 'vi' ? 'Khách đám cưới' : 'Wedding Couple'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" style="background-image: url('https://images.unsplash.com/photo-1540541338287-41700207dee6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
        <div class="container">
            <div class="cta-content">
                <h2><?php echo $language === 'vi' ? 'Đặt kỳ nghỉ hoàn hảo của bạn ngay hôm nay' : 'Book Your Perfect Getaway Today'; ?></h2>
                <p><?php echo $language === 'vi' ? 'Trải nghiệm kỳ nghỉ đáng nhớ tại Vinpearl Resort Nha Trang với các gói ưu đãi đặc biệt của chúng tôi.' : 'Experience a memorable vacation at Vinpearl Resort Nha Trang with our special packages.'; ?></p>
                <a href="packages.php" class="btn btn-lg btn-primary"><?php echo $language === 'vi' ? 'Khám phá các gói nghỉ dưỡng' : 'Explore Our Packages'; ?></a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Initialize animations for room cards
        document.addEventListener('DOMContentLoaded', function() {
            const roomCards = document.querySelectorAll('.room-card');
            roomCards.forEach(card => {
                card.classList.add('animate');
            });
        });
    </script>
</body>
</html>