<?php
/**
 * Packages Page - Vinpearl Resort Nha Trang
 * 
 * Displays vacation packages with descriptions, pricing, and booking options.
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get selected language (defaults to English)
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';
$lang = $language; // For compatibility

// Get current user if logged in
$currentUser = getCurrentUser();

// Process booking submission
$bookingSuccess = false;
$bookingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_package'])) {
    if (!isLoggedIn()) {
        // Redirect to login page with return URL
        header('Location: login.php?redirect=' . urlencode("packages.php"));
        exit;
    }
    
    $package_id = isset($_POST['package_id']) ? (int)$_POST['package_id'] : 0;
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
    $total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0;
    $special_requests = isset($_POST['special_requests']) ? $_POST['special_requests'] : '';
    
    // Validate booking data
    if (empty($start_date)) {
        $bookingError = $language === 'vi' ? 'Vui lòng chọn ngày bắt đầu' : 'Please select a start date';
    } elseif ($guests <= 0) {
        $bookingError = $language === 'vi' ? 'Số lượng khách không hợp lệ' : 'Invalid number of guests';
    } elseif ($total_price <= 0) {
        $bookingError = $language === 'vi' ? 'Tổng giá không hợp lệ' : 'Invalid total price';
    } else {
        // Create booking
        $bookingData = [
            'user_id' => $currentUser['id'],
            'package_id' => $package_id,
            'start_date' => $start_date,
            'guests' => $guests,
            'total_price' => $total_price,
            'special_requests' => $special_requests
        ];
        
        $conn = connectDatabase();
        if ($conn) {
            $stmt = $conn->prepare("
                INSERT INTO package_bookings 
                (user_id, package_id, start_date, guests, total_price, special_requests, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->bind_param("iisids", 
                $bookingData['user_id'], 
                $bookingData['package_id'], 
                $bookingData['start_date'], 
                $bookingData['guests'], 
                $bookingData['total_price'], 
                $bookingData['special_requests']
            );
            
            if ($stmt->execute()) {
                $bookingSuccess = true;
            } else {
                $bookingError = $language === 'vi' ? 'Không thể tạo đặt phòng. Vui lòng thử lại.' : 'Unable to create booking. Please try again.';
            }
            
            $conn->close();
        } else {
            $bookingError = $language === 'vi' ? 'Không thể kết nối đến cơ sở dữ liệu' : 'Could not connect to database';
        }
    }
}

// Define page title
$pageTitle = $language === 'vi' ? 'Gói Kỳ Nghỉ - Vinpearl Resort Nha Trang' : 'Vacation Packages - Vinpearl Resort Nha Trang';
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Fonts and Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/packages-styles.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero packages-hero" style="background-image: linear-gradient(rgba(255, 255, 255, 0.5), rgba(109, 103, 103, 0.5)), url('https://images.unsplash.com/photo-1540541338287-41700207dee6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo ($language === 'vi') ? 'Gói Kỳ Nghỉ' : 'Vacation Packages'; ?></h1>
                <p><?php echo ($language === 'vi') ? 'Trải nghiệm sự sang trọng tối đa với các gói được chúng tôi thiết kế cẩn thận' : 'Experience the ultimate luxury with our carefully curated packages'; ?></p>
            </div>
        </div>
    </section>

    <!-- Booking Success Message -->
    <?php if ($bookingSuccess): ?>
    <div class="success-message">
        <div class="container">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $language === 'vi' ? 'Đặt gói thành công! Vui lòng kiểm tra trang đặt chỗ của bạn để biết chi tiết.' : 'Package booked successfully! Please check your bookings page for details.'; ?>
                <a href="bookings.php" class="alert-link"><?php echo $language === 'vi' ? 'Xem đặt chỗ' : 'View Bookings'; ?></a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (!empty($bookingError)): ?>
    <div class="error-message">
        <div class="container">
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $bookingError; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Packages Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title"><?php echo ($language === 'vi') ? 'Các Gói Kỳ Nghỉ Của Chúng Tôi' : 'Our Vacation Packages'; ?></h2>
            <p class="section-subtitle">
                <?php echo ($language === 'vi') ? 'Chọn một trong những gói đặc biệt của chúng tôi được thiết kế để làm cho kỳ nghỉ của bạn khó quên' : 'Choose one of our special packages designed to make your stay unforgettable'; ?>
            </p>

            <!-- Package Filter -->
            <div class="filter-container">
                <ul class="filter-list">
                    <li><button class="filter-btn active" data-category="all"><?php echo ($language === 'vi') ? 'Tất Cả Gói' : 'All Packages'; ?></button></li>
                    <li><button class="filter-btn" data-category="romantic"><?php echo ($language === 'vi') ? 'Lãng Mạn' : 'Romantic'; ?></button></li>
                    <li><button class="filter-btn" data-category="family"><?php echo ($language === 'vi') ? 'Gia Đình' : 'Family'; ?></button></li>
                    <li><button class="filter-btn" data-category="wellness"><?php echo ($language === 'vi') ? 'Sức Khỏe' : 'Wellness'; ?></button></li>
                    <li><button class="filter-btn" data-category="adventure"><?php echo ($language === 'vi') ? 'Phiêu Lưu' : 'Adventure'; ?></button></li>
                </ul>
            </div>

            <!-- Packages Grid -->
            <div class="packages-grid" id="packages-container">
                <?php
                // Get packages from database
                $conn = connectDatabase();
                $query = "SELECT * FROM packages";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($package = $result->fetch_assoc()) {
                        // Determine package category based on name (this would ideally be a database field)
                        $category = "romantic"; // Default
                        if (stripos($package['name_en'], 'family') !== false) {
                            $category = "family";
                        } elseif (stripos($package['name_en'], 'wellness') !== false) {
                            $category = "wellness";
                        } elseif (stripos($package['name_en'], 'adventure') !== false) {
                            $category = "adventure";
                        }
                        
                        // Find a badge based on package properties (could be from database)
                        $badgeText = "";
                        if ($package['price'] < 200) {
                            $badgeText = $language === 'vi' ? "Giá tốt" : "Best Value";
                        } elseif (strpos(strtolower($package['name_en']), 'premium') !== false) {
                            $badgeText = $language === 'vi' ? "Cao cấp" : "Premium";
                        } elseif (strpos(strtolower($package['name_en']), 'special') !== false) {
                            $badgeText = $language === 'vi' ? "Đặc biệt" : "Special";
                        }
                        
                        // Ensure package has a valid image URL, otherwise use a placeholder
                        $imageUrl = !empty($package['image_url']) ? $package['image_url'] : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80';
                        
                        // Display package
                        ?>
                        <div class="package-card filterable-item" data-category="<?php echo $category; ?>" data-id="<?php echo $package['id']; ?>" data-price="<?php echo $package['price']; ?>">
                            <div class="card-img package-img">
                                <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($package['name_' . $language]); ?>">
                                <?php if (!empty($badgeText)): ?>
                                    <div class="package-badge"><?php echo $badgeText; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($package['name_' . $language]); ?></h3>
                                <p class="card-text"><?php echo htmlspecialchars(substr($package['description_' . $language], 0, 120) . '...'); ?></p>
                                
                                <h4><?php echo ($language === 'vi') ? 'Gói Bao Gồm:' : 'Package Includes:'; ?></h4>
                                <ul class="package-features">
                                    <?php
                                    // Split includes text by commas and create list items
                                    $includes = explode(',', $package['includes_text_' . $language]);
                                    $includes = array_slice($includes, 0, 4); // Limit to 4 items for card display
                                    foreach ($includes as $include) {
                                        echo '<li>' . htmlspecialchars(trim($include)) . '</li>';
                                    }
                                    ?>
                                </ul>
                                
                                <div class="package-details">
                                    <div class="package-detail">
                                        <div class="detail-value"><?php echo $package['duration']; ?></div>
                                        <div class="detail-label"><?php echo ($language === 'vi') ? 'Ngày' : 'Days'; ?></div>
                                    </div>
                                    <div class="package-detail">
                                        <div class="detail-value">
                                            <?php 
                                            // Display 4.5 stars for visual appeal
                                            for ($i = 1; $i <= 4; $i++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                        <div class="detail-label"><?php echo ($language === 'vi') ? 'Đánh Giá' : 'Rating'; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="price">
                                    $<?php echo number_format($package['price'], 0); ?>
                                    <span class="price-suffix">/ <?php echo ($language === 'vi') ? 'gói' : 'package'; ?></span>
                                </div>
                                <button class="btn btn-primary book-now-btn" data-id="<?php echo $package['id']; ?>" data-name="<?php echo htmlspecialchars($package['name_' . $language]); ?>" data-price="<?php echo $package['price']; ?>">
                                    <?php echo ($language === 'vi') ? 'Đặt Ngay' : 'Book Now'; ?>
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // No packages found
                    echo '<div class="no-packages-message">';
                    echo ($language === 'vi') ? 
                        '<p>Hiện tại không có gói nào. Vui lòng quay lại sau.</p>' : 
                        '<p>No packages available at the moment. Please check back later.</p>';
                    echo '</div>';
                }
                
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <!-- Special Offer Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-6">
                    <img src="https://images.unsplash.com/photo-1602002418082-a4443e081dd1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1074&q=80" alt="Special Offer" class="img-fluid rounded">
                </div>
                <div class="col-12 col-md-6">
                    <div class="special-offer-content">
                        <h2><?php echo ($language === 'vi') ? 'Ưu Đãi Mùa Hè Đặc Biệt' : 'Special Summer Offer'; ?></h2>
                        <p class="lead">
                            <?php echo ($language === 'vi') ? 'Đặt bất kỳ gói nào trước ngày 31 tháng 8 và được giảm giá 20%!' : 'Book any package before August 31st and get 20% off!'; ?>
                        </p>
                        <p>
                            <?php echo ($language === 'vi') ? 
                                'Tận dụng chương trình khuyến mãi mùa hè có thời hạn của chúng tôi. Tận hưởng khu nghỉ dưỡng xinh đẹp của chúng tôi với các khoản tiết kiệm đặc biệt khi bạn đặt bất kỳ gói kỳ nghỉ nào cho kỳ nghỉ từ ngày 1 tháng 6 đến ngày 30 tháng 9.' : 
                                'Take advantage of our limited-time summer promotion. Enjoy our beautiful resort with special savings when you book any vacation package for stays between June 1st and September 30th.'; ?>
                        </p>
                        <ul class="offer-features">
                            <li>
                                <i class="fas fa-check-circle text-success"></i> 
                                <?php echo ($language === 'vi') ? '20% giảm giá cho tất cả các gói' : '20% discount on all packages'; ?>
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success"></i> 
                                <?php echo ($language === 'vi') ? 'Nâng cấp miễn phí khi có sẵn' : 'Free upgrade when available'; ?>
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success"></i> 
                                <?php echo ($language === 'vi') ? 'Tặng một chai rượu vang' : 'Complimentary bottle of wine'; ?>
                            </li>
                        </ul>
                        <p>
                            <strong><?php echo ($language === 'vi') ? 'Mã Khuyến Mãi:' : 'Promo Code:'; ?></strong> SUMMER2025
                        </p>
                        <button class="btn btn-accent btn-lg promotion-book-btn">
                            <?php echo ($language === 'vi') ? 'Đặt Ngay' : 'Book Now'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title"><?php echo ($language === 'vi') ? 'Trải Nghiệm Của Khách' : 'Guest Experiences'; ?></h2>
            <p class="section-subtitle">
                <?php echo ($language === 'vi') ? 'Nghe những gì khách của chúng tôi nói về trải nghiệm gói của họ' : 'Hear what our guests say about their package experiences'; ?>
            </p>
            
            <div class="testimonials-grid">
                <?php
                // Get package reviews from database
                $conn = connectDatabase();
                $query = "SELECT r.*, u.full_name 
                          FROM reviews r 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.type = 'package' AND r.is_approved = 1 AND r.is_hidden = 0 
                          ORDER BY r.created_at DESC LIMIT 3";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($review = $result->fetch_assoc()) {
                        ?>
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <?php
                                // Display stars based on rating
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $review['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i - 0.5 == $review['rating']) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <h3 class="testimonial-title"><?php echo htmlspecialchars($review['title_' . $language]); ?></h3>
                            <p class="testimonial-text">"<?php echo htmlspecialchars($review['content_' . $language]); ?>"</p>
                            <div class="testimonial-author">
                                <div class="author-avatar">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($review['full_name']); ?>&background=random" alt="<?php echo htmlspecialchars($review['full_name']); ?>">
                                </div>
                                <div class="author-info">
                                    <h4><?php echo htmlspecialchars($review['full_name']); ?></h4>
                                    <p><?php echo date('F Y', strtotime($review['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // No reviews found - Display testimonial placeholders for visual appeal
                    $testimonials = [
                        [
                            'rating' => 5,
                            'title' => $language === 'vi' ? 'Kỳ nghỉ hoàn hảo' : 'Perfect Vacation',
                            'content' => $language === 'vi' ? 
                                'Gói dịch vụ của Vinpearl thật đáng kinh ngạc. Dịch vụ hoàn hảo, nhân viên thân thiện và chuyên nghiệp. Chúng tôi đã có thời gian thư giãn tuyệt vời.' : 
                                'Vinpearl\'s package was amazing. The service was perfect, staff friendly and professional. We had such a relaxing time.',
                            'name' => 'John Smith',
                            'date' => 'April 2025'
                        ],
                        [
                            'rating' => 5,
                            'title' => $language === 'vi' ? 'Trải nghiệm tuyệt vời' : 'Wonderful Experience',
                            'content' => $language === 'vi' ? 
                                'Chúng tôi đã đặt gói Gia đình và đó là một kỳ nghỉ tuyệt vời cho chúng tôi. Các hoạt động được tổ chức rất tốt và nhân viên rất chú ý đến từng chi tiết.' : 
                                'We booked the Family package and it was a wonderful holiday for us. The activities were well organized and the staff paid attention to every detail.',
                            'name' => 'Sarah Johnson',
                            'date' => 'March 2025'
                        ],
                        [
                            'rating' => 4.5,
                            'title' => $language === 'vi' ? 'Đáng giá từng xu' : 'Worth Every Penny',
                            'content' => $language === 'vi' ? 
                                'Tuy gói có giá hơi cao nhưng nó hoàn toàn đáng giá. Chất lượng dịch vụ và tiện nghi thật ấn tượng. Chắc chắn sẽ quay lại.' : 
                                'Although the package was a bit pricey, it was totally worth it. The quality of service and amenities were impressive. Will definitely return.',
                            'name' => 'Michael Chen',
                            'date' => 'May 2025'
                        ]
                    ];
                    
                    foreach ($testimonials as $testimonial) {
                        ?>
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $testimonial['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i - 0.5 == $testimonial['rating']) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <h3 class="testimonial-title"><?php echo $testimonial['title']; ?></h3>
                            <p class="testimonial-text">"<?php echo $testimonial['content']; ?>"</p>
                            <div class="testimonial-author">
                                <div class="author-avatar">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($testimonial['name']); ?>&background=random" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                                </div>
                                <div class="author-info">
                                    <h4><?php echo htmlspecialchars($testimonial['name']); ?></h4>
                                    <p><?php echo $testimonial['date']; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                
                if (isset($conn) && $conn) {
                    $conn->close();
                }
                ?>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title"><?php echo ($language === 'vi') ? 'Câu Hỏi Thường Gặp' : 'Frequently Asked Questions'; ?></h2>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($language === 'vi') ? 'Gói kỳ nghỉ bao gồm những gì?' : 'What is included in the vacation packages?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($language === 'vi') ? 
                            'Các gói kỳ nghỉ của chúng tôi thường bao gồm chỗ ở, các bữa ăn được chọn và các hoạt động khác nhau tùy thuộc vào loại gói. Các khoản bao gồm cụ thể được liệt kê trong chi tiết gói.' : 
                            'Our vacation packages typically include accommodations, select meals, and various activities depending on the package type. Specific inclusions are listed in the package details.'; ?>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($language === 'vi') ? 'Tôi có thể tùy chỉnh một gói theo sở thích của tôi không?' : 'Can I customize a package to my preferences?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($language === 'vi') ? 
                            'Có, chúng tôi cung cấp các tùy chọn tùy chỉnh cho hầu hết các gói của chúng tôi. Vui lòng liên hệ với đội ngũ concierge của chúng tôi để thảo luận về các yêu cầu cụ thể của bạn.' : 
                            'Yes, we offer customization options for most of our packages. Please contact our concierge team to discuss your specific requirements.'; ?>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($language === 'vi') ? 'Chính sách hủy đối với các gói là gì?' : 'What is the cancellation policy for packages?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($language === 'vi') ? 
                            'Các gói có thể được hủy tối đa 14 ngày trước ngày đến dự kiến để được hoàn tiền đầy đủ. Việc hủy bỏ được thực hiện trong khoảng 7-14 ngày trước khi đến sẽ nhận được khoản hoàn trả 50%. Việc hủy bỏ trong vòng 7 ngày kể từ khi đến sẽ không được hoàn tiền.' : 
                            'Packages can be cancelled up to 14 days before the scheduled arrival date for a full refund. Cancellations made between 7-14 days before arrival receive a 50% refund. Cancellations within 7 days of arrival are non-refundable.'; ?>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($language === 'vi') ? 'Dịch vụ đưa đón sân bay có được bao gồm trong các gói không?' : 'Are airport transfers included in the packages?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($language === 'vi') ? 
                            'Dịch vụ đưa đón sân bay được bao gồm trong các gói cao cấp của chúng tôi. Đối với các gói tiêu chuẩn, việc đưa đón có thể được thêm vào với một khoản phí bổ sung. Vui lòng kiểm tra chi tiết gói để biết thông tin cụ thể.' : 
                            'Airport transfers are included in our premium packages. For standard packages, transfers can be added for an additional fee. Please check the package details for specific information.'; ?>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($language === 'vi') ? 'Tôi có thể thêm đêm vào gói của tôi không?' : 'Can I add extra nights to my package?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($language === 'vi') ? 
                            'Có, có thể thêm đêm bổ sung vào hầu hết các gói. Tỷ lệ cho các đêm bổ sung sẽ dựa trên giá phòng hiện tại cho chỗ ở được chọn của bạn.' : 
                            'Yes, additional nights can be added to most packages. The rate for extra nights will be based on the current room rate for your selected accommodation.'; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Modal -->
    <div class="booking-modal" id="bookingModal">
        <div class="booking-modal-content">
            <button class="close-modal" id="closeModal"><i class="fas fa-times"></i></button>
            
            <form action="packages.php" method="post" id="packageBookingForm">
                <input type="hidden" name="book_package" value="1">
                <input type="hidden" name="package_id" id="modalPackageId">
                <input type="hidden" name="total_price" id="modalTotalPrice">
                
                <div class="booking-form">
                    <h3 id="modalPackageTitle"><?php echo ($language === 'vi') ? 'Đặt Gói Kỳ Nghỉ' : 'Book Vacation Package'; ?></h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date"><?php echo ($language === 'vi') ? 'Ngày Bắt Đầu' : 'Start Date'; ?></label>
                            <input type="date" id="start_date" name="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="guests"><?php echo ($language === 'vi') ? 'Số Khách' : 'Number of Guests'; ?></label>
                            <select id="guests" name="guests" class="form-control" required>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_requests"><?php echo ($language === 'vi') ? 'Yêu Cầu Đặc Biệt' : 'Special Requests'; ?></label>
                        <textarea id="special_requests" name="special_requests" class="form-control" rows="4" placeholder="<?php echo ($language === 'vi') ? 'Các yêu cầu đặc biệt hoặc thông tin bổ sung...' : 'Any special requests or additional information...'; ?>"></textarea>
                    </div>
                    
                    <div class="booking-summary">
                        <div class="summary-row">
                            <span><?php echo ($language === 'vi') ? 'Gói' : 'Package'; ?>:</span>
                            <span id="summaryPackageName">-</span>
                        </div>
                        <div class="summary-row">
                            <span><?php echo ($language === 'vi') ? 'Giá Gói' : 'Package Price'; ?>:</span>
                            <span id="summaryPackagePrice">$0.00</span>
                        </div>
                        <div class="summary-row summary-total">
                            <span><?php echo ($language === 'vi') ? 'Tổng Cộng' : 'Total'; ?>:</span>
                            <span id="summaryTotalPrice">$0.00</span>
                        </div>
                    </div>
                    
                    <?php if (!isLoggedIn()): ?>
                        <div class="login-prompt">
                            <p>
                                <?php echo ($language === 'vi') ? 
                                    'Vui lòng <a href="login.php?redirect=packages.php">đăng nhập</a> hoặc <a href="register.php?redirect=packages.php">đăng ký</a> để tiến hành đặt chỗ.' : 
                                    'Please <a href="login.php?redirect=packages.php">login</a> or <a href="register.php?redirect=packages.php">register</a> to proceed with booking.'; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <button type="submit" class="btn btn-accent btn-lg" id="confirmBookingBtn">
                            <?php echo ($language === 'vi') ? 'Xác Nhận Đặt Chỗ' : 'Confirm Booking'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Package filtering functionality
            const filterButtons = document.querySelectorAll('.filter-btn');
            const filterItems = document.querySelectorAll('.filterable-item');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    // Get filter category
                    const category = button.getAttribute('data-category');
                    
                    // Show/hide items based on category
                    filterItems.forEach(item => {
                        if (category === 'all' || item.getAttribute('data-category') === category) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
            
            // Toggle FAQ answers
            const faqQuestions = document.querySelectorAll('.faq-question');
            faqQuestions.forEach(question => {
                question.addEventListener('click', () => {
                    const faqItem = question.parentElement;
                    faqItem.classList.toggle('active');
                    
                    // Toggle icon
                    const icon = question.querySelector('i');
                    if (faqItem.classList.contains('active')) {
                        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                    } else {
                        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });
            });
            
            // Booking Modal Functionality
            const modal = document.getElementById('bookingModal');
            const bookButtons = document.querySelectorAll('.book-now-btn');
            const promotionBookBtn = document.querySelector('.promotion-book-btn');
            const closeModalBtn = document.getElementById('closeModal');
            const packageIdInput = document.getElementById('modalPackageId');
            const packageTitleEl = document.getElementById('modalPackageTitle');
            const summaryPackageNameEl = document.getElementById('summaryPackageName');
            const summaryPackagePriceEl = document.getElementById('summaryPackagePrice');
            const summaryTotalPriceEl = document.getElementById('summaryTotalPrice');
            const totalPriceInput = document.getElementById('modalTotalPrice');
            
            // Open modal with specific package details
            bookButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const packageId = button.getAttribute('data-id');
                    const packageName = button.getAttribute('data-name');
                    const packagePrice = parseFloat(button.getAttribute('data-price'));
                    
                    // Set modal values
                    packageIdInput.value = packageId;
                    packageTitleEl.textContent = '<?php echo ($language === 'vi') ? 'Đặt Gói: ' : 'Book Package: '; ?>' + packageName;
                    summaryPackageNameEl.textContent = packageName;
                    summaryPackagePriceEl.textContent = '$' + packagePrice.toFixed(2);
                    summaryTotalPriceEl.textContent = '$' + packagePrice.toFixed(2);
                    totalPriceInput.value = packagePrice;
                    
                    // Open modal
                    modal.classList.add('open');
                });
            });
            
            // Open modal with first package on promotion button click
            if (promotionBookBtn) {
                promotionBookBtn.addEventListener('click', () => {
                    const firstPackageBtn = document.querySelector('.book-now-btn');
                    if (firstPackageBtn) {
                        firstPackageBtn.click();
                    }
                });
            }
            
            // Close modal
            closeModalBtn.addEventListener('click', () => {
                modal.classList.remove('open');
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('open');
                }
            });
            
            // Update price when guests change
            const guestsSelect = document.getElementById('guests');
            if (guestsSelect) {
                guestsSelect.addEventListener('change', updatePrice);
            }
            
            function updatePrice() {
                const basePrice = parseFloat(summaryPackagePriceEl.textContent.replace('$', ''));
                const guests = parseInt(guestsSelect.value);
                let totalPrice = basePrice;
                
                // Add $100 per additional guest over 2
                if (guests > 2) {
                    totalPrice += (guests - 2) * 100;
                }
                
                // Update summary and hidden input
                summaryTotalPriceEl.textContent = '$' + totalPrice.toFixed(2);
                totalPriceInput.value = totalPrice;
            }
        });
    </script>
</body>
</html>