<?php
/**
 * Packages Page - Vinpearl Resort Nha Trang
 * 
 * Displays vacation packages with descriptions, pricing, and booking options.
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get selected language (defaults to English)
$lang = isset($_GET['lang']) ? $_GET['lang'] : DEFAULT_LANGUAGE;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($lang == 'en') ? 'Vacation Packages - Vinpearl Resort Nha Trang' : 'Gói Kỳ Nghỉ - Vinpearl Resort Nha Trang'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/packages/packages-hero.jpg');">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo ($lang == 'en') ? 'Vacation Packages' : 'Gói Kỳ Nghỉ'; ?></h1>
                <p><?php echo ($lang == 'en') ? 'Experience the ultimate luxury with our carefully curated packages' : 'Trải nghiệm sự sang trọng tối đa với các gói được chúng tôi thiết kế cẩn thận'; ?></p>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title"><?php echo ($lang == 'en') ? 'Our Vacation Packages' : 'Các Gói Kỳ Nghỉ Của Chúng Tôi'; ?></h2>
            <p class="section-subtitle">
                <?php echo ($lang == 'en') ? 'Choose one of our special packages designed to make your stay unforgettable' : 'Chọn một trong những gói đặc biệt của chúng tôi được thiết kế để làm cho kỳ nghỉ của bạn khó quên'; ?>
            </p>

            <!-- Package Filter -->
            <div class="filter-container">
                <ul class="filter-list">
                    <li><button class="filter-btn active" data-category="all" onclick="filterItems('all', 'packages-container')"><?php echo ($lang == 'en') ? 'All Packages' : 'Tất Cả Gói'; ?></button></li>
                    <li><button class="filter-btn" data-category="romantic" onclick="filterItems('romantic', 'packages-container')"><?php echo ($lang == 'en') ? 'Romantic' : 'Lãng Mạn'; ?></button></li>
                    <li><button class="filter-btn" data-category="family" onclick="filterItems('family', 'packages-container')"><?php echo ($lang == 'en') ? 'Family' : 'Gia Đình'; ?></button></li>
                    <li><button class="filter-btn" data-category="wellness" onclick="filterItems('wellness', 'packages-container')"><?php echo ($lang == 'en') ? 'Wellness' : 'Sức Khỏe'; ?></button></li>
                    <li><button class="filter-btn" data-category="adventure" onclick="filterItems('adventure', 'packages-container')"><?php echo ($lang == 'en') ? 'Adventure' : 'Phiêu Lưu'; ?></button></li>
                </ul>
            </div>

            <!-- Packages Grid -->
            <div class="row" id="packages-container">
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
                        
                        // Display package
                        ?>
                        <div class="col-12 col-md-6 col-lg-4 filterable-item" data-category="<?php echo $category; ?>">
                            <div class="card package-card">
                                <div class="card-img package-img">
                                    <img src="<?php echo $package['image_url']; ?>" alt="<?php echo $package['name_' . $lang]; ?>">
                                </div>
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo $package['name_' . $lang]; ?></h3>
                                    <p class="card-text"><?php echo $package['description_' . $lang]; ?></p>
                                    
                                    <h4><?php echo ($lang == 'en') ? 'Package Includes:' : 'Gói Bao Gồm:'; ?></h4>
                                    <ul class="package-features">
                                        <?php
                                        // Split includes text by commas and create list items
                                        $includes = explode(',', $package['includes_text_' . $lang]);
                                        foreach ($includes as $include) {
                                            echo '<li>' . trim($include) . '</li>';
                                        }
                                        ?>
                                    </ul>
                                    
                                    <div class="package-details">
                                        <div class="package-detail">
                                            <div class="detail-value"><?php echo $package['duration']; ?></div>
                                            <div class="detail-label"><?php echo ($lang == 'en') ? 'Days' : 'Ngày'; ?></div>
                                        </div>
                                        <div class="package-detail">
                                            <div class="detail-value">
                                                <i class="fas fa-star text-warning"></i> 
                                                <i class="fas fa-star text-warning"></i> 
                                                <i class="fas fa-star text-warning"></i> 
                                                <i class="fas fa-star text-warning"></i> 
                                                <i class="fas fa-star-half-alt text-warning"></i>
                                            </div>
                                            <div class="detail-label"><?php echo ($lang == 'en') ? 'Rating' : 'Đánh Giá'; ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="price">
                                        $<?php echo number_format($package['price'], 2); ?>
                                        <span class="price-suffix">/ <?php echo ($lang == 'en') ? 'package' : 'gói'; ?></span>
                                    </div>
                                    <a href="package-details.php?id=<?php echo $package['id']; ?>&lang=<?php echo $lang; ?>" class="btn btn-primary">
                                        <?php echo ($lang == 'en') ? 'View Details' : 'Xem Chi Tiết'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // No packages found
                    echo '<div class="col-12 text-center">';
                    echo ($lang == 'en') ? 
                        '<p>No packages available at the moment. Please check back later.</p>' : 
                        '<p>Hiện tại không có gói nào. Vui lòng quay lại sau.</p>';
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
                    <img src="assets/images/packages/special-offer.jpg" alt="Special Offer" class="img-fluid rounded">
                </div>
                <div class="col-12 col-md-6">
                    <div class="special-offer-content">
                        <h2><?php echo ($lang == 'en') ? 'Special Summer Offer' : 'Ưu Đãi Mùa Hè Đặc Biệt'; ?></h2>
                        <p class="lead">
                            <?php echo ($lang == 'en') ? 'Book any package before August 31st and get 20% off!' : 'Đặt bất kỳ gói nào trước ngày 31 tháng 8 và được giảm giá 20%!'; ?>
                        </p>
                        <p>
                            <?php echo ($lang == 'en') ? 
                                'Take advantage of our limited-time summer promotion. Enjoy our beautiful resort with special savings when you book any vacation package for stays between June 1st and September 30th.' : 
                                'Tận dụng chương trình khuyến mãi mùa hè có thời hạn của chúng tôi. Tận hưởng khu nghỉ dưỡng xinh đẹp của chúng tôi với các khoản tiết kiệm đặc biệt khi bạn đặt bất kỳ gói kỳ nghỉ nào cho kỳ nghỉ từ ngày 1 tháng 6 đến ngày 30 tháng 9.'; ?>
                        </p>
                        <ul class="offer-features">
                            <li>
                                <i class="fas fa-check-circle text-success"></i> 
                                <?php echo ($lang == 'en') ? '20% discount on all packages' : 'Giảm giá 20% cho tất cả các gói'; ?>
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success"></i> 
                                <?php echo ($lang == 'en') ? 'Free upgrade when available' : 'Nâng cấp miễn phí khi có sẵn'; ?>
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success"></i> 
                                <?php echo ($lang == 'en') ? 'Complimentary bottle of wine' : 'Tặng một chai rượu vang'; ?>
                            </li>
                        </ul>
                        <p>
                            <strong><?php echo ($lang == 'en') ? 'Promo Code:' : 'Mã Khuyến Mãi:'; ?></strong> SUMMER2025
                        </p>
                        <a href="#" class="btn btn-accent btn-lg"><?php echo ($lang == 'en') ? 'Book Now' : 'Đặt Ngay'; ?></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title"><?php echo ($lang == 'en') ? 'Guest Experiences' : 'Trải Nghiệm Của Khách'; ?></h2>
            <p class="section-subtitle">
                <?php echo ($lang == 'en') ? 'Hear what our guests say about their package experiences' : 'Nghe những gì khách của chúng tôi nói về trải nghiệm gói của họ'; ?>
            </p>
            
            <div class="row">
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
                        <div class="col-12 col-md-4">
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
                                <h3 class="testimonial-title"><?php echo $review['title_' . $lang]; ?></h3>
                                <p class="testimonial-text"><?php echo $review['content_' . $lang]; ?></p>
                                <div class="testimonial-author">
                                    <div class="author-avatar">
                                        <!-- Default avatar image -->
                                        <img src="assets/images/avatar-placeholder.jpg" alt="<?php echo $review['full_name']; ?>">
                                    </div>
                                    <div class="author-info">
                                        <h4><?php echo $review['full_name']; ?></h4>
                                        <p><?php echo date('F Y', strtotime($review['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // No reviews found
                    echo '<div class="col-12 text-center">';
                    echo ($lang == 'en') ? 
                        '<p>No reviews available yet. Be the first to review our packages!</p>' : 
                        '<p>Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá các gói của chúng tôi!</p>';
                    echo '</div>';
                }
                
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title"><?php echo ($lang == 'en') ? 'Frequently Asked Questions' : 'Câu Hỏi Thường Gặp'; ?></h2>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($lang == 'en') ? 'What is included in the vacation packages?' : 'Gói kỳ nghỉ bao gồm những gì?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($lang == 'en') ? 
                            'Our vacation packages typically include accommodations, select meals, and various activities depending on the package type. Specific inclusions are listed in the package details.' : 
                            'Các gói kỳ nghỉ của chúng tôi thường bao gồm chỗ ở, các bữa ăn được chọn và các hoạt động khác nhau tùy thuộc vào loại gói. Các khoản bao gồm cụ thể được liệt kê trong chi tiết gói.'; ?>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($lang == 'en') ? 'Can I customize a package to my preferences?' : 'Tôi có thể tùy chỉnh một gói theo sở thích của tôi không?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($lang == 'en') ? 
                            'Yes, we offer customization options for most of our packages. Please contact our concierge team to discuss your specific requirements.' : 
                            'Có, chúng tôi cung cấp các tùy chọn tùy chỉnh cho hầu hết các gói của chúng tôi. Vui lòng liên hệ với đội ngũ concierge của chúng tôi để thảo luận về các yêu cầu cụ thể của bạn.'; ?>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($lang == 'en') ? 'What is the cancellation policy for packages?' : 'Chính sách hủy đối với các gói là gì?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($lang == 'en') ? 
                            'Packages can be cancelled up to 14 days before the scheduled arrival date for a full refund. Cancellations made between 7-14 days before arrival receive a 50% refund. Cancellations within 7 days of arrival are non-refundable.' : 
                            'Các gói có thể được hủy tối đa 14 ngày trước ngày đến dự kiến để được hoàn tiền đầy đủ. Việc hủy bỏ được thực hiện trong khoảng 7-14 ngày trước khi đến sẽ nhận được khoản hoàn trả 50%. Việc hủy bỏ trong vòng 7 ngày kể từ khi đến sẽ không được hoàn tiền.'; ?>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($lang == 'en') ? 'Are airport transfers included in the packages?' : 'Dịch vụ đưa đón sân bay có được bao gồm trong các gói không?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($lang == 'en') ? 
                            'Airport transfers are included in our premium packages. For standard packages, transfers can be added for an additional fee. Please check the package details for specific information.' : 
                            'Dịch vụ đưa đón sân bay được bao gồm trong các gói cao cấp của chúng tôi. Đối với các gói tiêu chuẩn, việc đưa đón có thể được thêm vào với một khoản phí bổ sung. Vui lòng kiểm tra chi tiết gói để biết thông tin cụ thể.'; ?>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <?php echo ($lang == 'en') ? 'Can I add extra nights to my package?' : 'Tôi có thể thêm đêm vào gói của tôi không?'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo ($lang == 'en') ? 
                            'Yes, additional nights can be added to most packages. The rate for extra nights will be based on the current room rate for your selected accommodation.' : 
                            'Có, có thể thêm đêm bổ sung vào hầu hết các gói. Tỷ lệ cho các đêm bổ sung sẽ dựa trên giá phòng hiện tại cho chỗ ở được chọn của bạn.'; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        // Toggle FAQ answers
        document.querySelectorAll('.faq-question').forEach(question => {
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
    </script>
</body>
</html> 