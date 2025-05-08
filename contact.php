<?php
/**
 * Vinpearl Resort Nha Trang - Contact Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get current user if logged in
$currentUser = getCurrentUser();

// Initialize variables
$submitted = false;
$error = '';
$form_data = [
    'name' => $currentUser ? $currentUser['full_name'] : '',
    'email' => $currentUser ? $currentUser['email'] : '',
    'phone' => $currentUser ? $currentUser['phone'] : '',
    'subject' => '',
    'message' => ''
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $form_data = [
        'name' => isset($_POST['name']) ? trim($_POST['name']) : '',
        'email' => isset($_POST['email']) ? trim($_POST['email']) : '',
        'phone' => isset($_POST['phone']) ? trim($_POST['phone']) : '',
        'subject' => isset($_POST['subject']) ? trim($_POST['subject']) : '',
        'message' => isset($_POST['message']) ? trim($_POST['message']) : ''
    ];
    
    // Validate form data
    if (empty($form_data['name']) || empty($form_data['email']) || empty($form_data['subject']) || empty($form_data['message'])) {
        $error = $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin bắt buộc' : 'Please fill all required fields';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = $language === 'vi' ? 'Email không hợp lệ' : 'Invalid email address';
    } else {
        // In a real implementation, this would send an email or save to database
        // For this demo, we'll just mark the form as submitted
        $submitted = true;
        
        // Reset form data after successful submission
        $form_data = [
            'name' => $currentUser ? $currentUser['full_name'] : '',
            'email' => $currentUser ? $currentUser['email'] : '',
            'phone' => $currentUser ? $currentUser['phone'] : '',
            'subject' => '',
            'message' => ''
        ];
    }
}

// Page title
$pageTitle = $language === 'vi' ? 'Liên Hệ' : 'Contact Us';
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/contact-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                    <li><a href="contact.php" class="active"><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact'; ?></a></li>
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

    <!-- Hero Section -->
    <section class="contact-hero">
        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Vinpearl Resort" class="contact-hero-bg">
        <div class="contact-hero-overlay"></div>
        <div class="contact-hero-content">
            <h1 class="animated"><?php echo $pageTitle; ?></h1>
            <p class="animated delay-1"><?php echo $language === 'vi' ? 'Liên hệ với chúng tôi nếu bạn có bất kỳ câu hỏi nào hoặc muốn đặt trước dịch vụ tại khu nghỉ dưỡng sang trọng của chúng tôi' : 'Get in touch with us if you have any questions or want to pre-book services at our luxury resort'; ?></p>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="contact-info">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-item animated delay-1">
                    <div class="contact-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/484/484167.png" alt="Address">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Địa chỉ' : 'Address'; ?></h3>
                    <p>Vinpearl Resort Nha Trang,<br>Đảo Hòn Tre, Nha Trang,<br>Khánh Hòa, Việt Nam</p>
                </div>
                
                <div class="contact-item animated delay-2">
                    <div class="contact-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/3059/3059502.png" alt="Phone">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Điện thoại' : 'Phone'; ?></h3>
                    <p>+84 258 598 9999</p>
                    <p>+84 258 598 8888</p>
                </div>
                
                <div class="contact-item animated delay-3">
                    <div class="contact-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/732/732200.png" alt="Email">
                    </div>
                    <h3>Email</h3>
                    <p>info@vinpearl.com</p>
                    <p>reservations@vinpearl.com</p>
                </div>
                
                <div class="contact-item animated delay-4">
                    <div class="contact-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/2784/2784459.png" alt="Hours">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Giờ làm việc' : 'Business Hours'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Lễ tân: 24/7' : 'Reception: 24/7'; ?></p>
                    <p><?php echo $language === 'vi' ? 'Đặt phòng: 8:00 - 20:00' : 'Reservations: 8:00 AM - 8:00 PM'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="contact-form-section">
        <div class="container">
            <div class="section-header animated">
                <h2><?php echo $language === 'vi' ? 'Liên hệ với chúng tôi' : 'Get in Touch'; ?></h2>
                <p><?php echo $language === 'vi' ? 'Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn' : 'We\'re here to listen and assist you'; ?></p>
            </div>
            
            <div class="form-map-container">
                <div class="contact-form-container animated delay-1">
                    <h2><?php echo $language === 'vi' ? 'Gửi tin nhắn cho chúng tôi' : 'Send Us a Message'; ?></h2>
                    
                    <?php if ($submitted): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $language === 'vi' ? 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi trong thời gian sớm nhất!' : 'Thank you for contacting us. We will respond as soon as possible!'; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" class="contact-form validate">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name"><?php echo $language === 'vi' ? 'Họ tên *' : 'Full Name *'; ?></label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name']); ?>" placeholder="<?php echo $language === 'vi' ? 'Nhập họ tên của bạn' : 'Enter your full name'; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" placeholder="<?php echo $language === 'vi' ? 'Nhập email của bạn' : 'Enter your email address'; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><?php echo $language === 'vi' ? 'Số điện thoại' : 'Phone Number'; ?></label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone']); ?>" placeholder="<?php echo $language === 'vi' ? 'Nhập số điện thoại của bạn' : 'Enter your phone number'; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject"><?php echo $language === 'vi' ? 'Tiêu đề *' : 'Subject *'; ?></label>
                            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($form_data['subject']); ?>" placeholder="<?php echo $language === 'vi' ? 'Nhập tiêu đề' : 'Enter subject'; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message"><?php echo $language === 'vi' ? 'Tin nhắn *' : 'Message *'; ?></label>
                            <textarea id="message" name="message" rows="3" placeholder="<?php echo $language === 'vi' ? 'Nhập tin nhắn của bạn' : 'Enter your message'; ?>" required><?php echo htmlspecialchars($form_data['message']); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-paper-plane"></i> <?php echo $language === 'vi' ? 'Gửi tin nhắn' : 'Send Message'; ?>
                        </button>
                    </form>
                </div>
                
                <div class="contact-map animated delay-2">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3899.481784258144!2d109.22574961482016!3d12.20985699133043!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3170677e32588351%3A0xc02a74f38efe8146!2sVinpearl%20Resort%20Nha%20Trang!5e0!3m2!1sen!2s!4v1625723529123!5m2!1sen!2s" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    <section class="faq-section">
        <div class="container">
            <div class="section-header animated">
                <h2><?php echo $language === 'vi' ? 'Câu hỏi thường gặp' : 'Frequently Asked Questions'; ?></h2>
                <p><?php echo $language === 'vi' ? 'Một số câu hỏi phổ biến từ khách của chúng tôi' : 'Some common questions from our guests'; ?></p>
            </div>
            
            <div class="faq-items animated delay-1">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Làm thế nào để đặt phòng?' : 'How can I make a reservation?'; ?></h3>
                        <div class="icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Bạn có thể đặt phòng trực tuyến thông qua trang web của chúng tôi, gọi điện thoại trực tiếp hoặc gửi email cho bộ phận đặt phòng của chúng tôi. Chúng tôi cũng chấp nhận đặt phòng thông qua các đại lý du lịch trực tuyến lớn.' : 'You can book a room online through our website, call us directly, or email our reservations department. We also accept bookings through major online travel agencies.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Thời gian nhận phòng và trả phòng là khi nào?' : 'What are the check-in and check-out times?'; ?></h3>
                        <div class="icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Thời gian nhận phòng là 14:00 và thời gian trả phòng là 12:00 trưa. Nhận phòng sớm và trả phòng muộn có thể được sắp xếp tùy thuộc vào tình trạng sẵn có và có thể phát sinh phí bổ sung.' : 'Check-in time is 2:00 PM and check-out time is 12:00 noon. Early check-in and late check-out can be arranged depending on availability and may incur additional charges.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Khu nghỉ dưỡng có cung cấp dịch vụ đưa đón sân bay không?' : 'Does the resort offer airport transfers?'; ?></h3>
                        <div class="icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Có, chúng tôi cung cấp dịch vụ đưa đón sân bay với một khoản phí bổ sung. Vui lòng thông báo trước cho chúng tôi về thông tin chuyến bay của bạn ít nhất 24 giờ trước khi đến.' : 'Yes, we offer airport transfers for an additional fee. Please notify us in advance with your flight details at least 24 hours prior to arrival.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Trẻ em có được tính phí không?' : 'Are children charged?'; ?></h3>
                        <div class="icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Trẻ em dưới 5 tuổi được ở miễn phí khi dùng chung giường với người lớn. Trẻ em từ 6-11 tuổi sẽ được tính phí ở mức giảm giá. Trẻ em từ 12 tuổi trở lên được tính như người lớn.' : 'Children under 5 years old stay free when sharing a bed with adults. Children 6-11 years old will be charged at a discounted rate. Children 12 and older are charged as adults.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Khu nghỉ dưỡng có nhà hàng không?' : 'Does the resort have restaurants?'; ?></h3>
                        <div class="icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Có, khu nghỉ dưỡng của chúng tôi có nhiều nhà hàng phục vụ ẩm thực địa phương và quốc tế. Bạn có thể tìm thấy chi tiết về nhà hàng của chúng tôi trong phần Ẩm thực trên trang web.' : 'Yes, our resort has multiple restaurants serving local and international cuisine. You can find details about our restaurants in the Dining section of the website.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Khu nghỉ dưỡng có hồ bơi không?' : 'Does the resort have swimming pools?'; ?></h3>
                        <div class="icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Có, khu nghỉ dưỡng có hồ bơi vô cực nhìn ra biển và một số hồ bơi khác rải rác khắp khu vực. Một số biệt thự còn có hồ bơi riêng.' : 'Yes, the resort features an infinity pool overlooking the ocean and several other pools scattered throughout the property. Some villas also have private pools.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Khu nghỉ dưỡng có dịch vụ đưa đón sân bay không?' : 'Does the resort offer airport transfers?'; ?></h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Có, chúng tôi cung cấp dịch vụ đưa đón sân bay có phụ phí. Vui lòng liên hệ với chúng tôi trước ít nhất 24 giờ để sắp xếp.' : 'Yes, we provide airport transfer services for an additional fee. Please contact us at least 24 hours in advance to arrange this service.'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="contact-cta">
        <div class="container">
            <h2 class="animated"><?php echo $language === 'vi' ? 'Hãy trải nghiệm kỳ nghỉ đáng nhớ tại Vinpearl' : 'Experience an Unforgettable Stay at Vinpearl'; ?></h2>
            <p class="animated delay-1"><?php echo $language === 'vi' ? 'Khám phá vẻ đẹp của Nha Trang và đắm mình trong sự sang trọng tại khu nghỉ dưỡng đẳng cấp của chúng tôi' : 'Discover the beauty of Nha Trang and immerse yourself in luxury at our premium resort'; ?></p>
            
            <a href="rooms.php" class="btn btn-lg animated delay-2"><?php echo $language === 'vi' ? 'Đặt phòng ngay' : 'Book Your Stay Now'; ?></a>
            
            <div class="contact-social animated delay-3">
                <a href="#" aria-label="Facebook">
                    <img src="https://cdn-icons-png.flaticon.com/512/145/145802.png" alt="Facebook">
                </a>
                <a href="#" aria-label="Instagram">
                    <img src="https://cdn-icons-png.flaticon.com/512/174/174855.png" alt="Instagram">
                </a>
                <a href="#" aria-label="Twitter">
                    <img src="https://cdn-icons-png.flaticon.com/512/145/145812.png" alt="Twitter">
                </a>
                <a href="#" aria-label="YouTube">
                    <img src="https://cdn-icons-png.flaticon.com/512/174/174883.png" alt="YouTube">
                </a>
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
    <script>
        // FAQ toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', function() {
                    // Toggle this FAQ item
                    item.classList.toggle('active');
                    
                    // Update toggle indicator
                    const toggle = this.querySelector('.faq-toggle');
                    toggle.textContent = item.classList.contains('active') ? '-' : '+';
                });
            });
        });
    </script>
    <!-- JavaScript -->
    <script src="assets/js/contact.js"></script>
</body>
</html> 