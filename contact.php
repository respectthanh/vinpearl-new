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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><?php echo $pageTitle; ?></h1>
            <p><?php echo $language === 'vi' ? 'Liên hệ với chúng tôi nếu bạn có bất kỳ câu hỏi nào' : 'Get in touch with us if you have any questions'; ?></p>
        </div>
    </div>

    <!-- Contact Information -->
    <section class="contact-info">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-item">
                    <div class="contact-icon">
                        <img src="assets/images/icons/location.svg" alt="Address">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Địa chỉ' : 'Address'; ?></h3>
                    <p>Vinpearl Resort Nha Trang,<br>Đảo Hòn Tre, Nha Trang,<br>Khánh Hòa, Việt Nam</p>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <img src="assets/images/icons/phone.svg" alt="Phone">
                    </div>
                    <h3><?php echo $language === 'vi' ? 'Điện thoại' : 'Phone'; ?></h3>
                    <p>+84 258 598 9999</p>
                    <p>+84 258 598 8888</p>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <img src="assets/images/icons/email.svg" alt="Email">
                    </div>
                    <h3>Email</h3>
                    <p>info@vinpearl.com</p>
                    <p>reservations@vinpearl.com</p>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <img src="assets/images/icons/clock.svg" alt="Hours">
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
            <div class="form-map-container">
                <div class="contact-form-container">
                    <h2><?php echo $language === 'vi' ? 'Gửi tin nhắn cho chúng tôi' : 'Send Us a Message'; ?></h2>
                    
                    <?php if ($submitted): ?>
                        <div class="alert alert-success">
                            <?php echo $language === 'vi' ? 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi trong thời gian sớm nhất!' : 'Thank you for contacting us. We will respond as soon as possible!'; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" class="contact-form validate">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name"><?php echo $language === 'vi' ? 'Họ tên *' : 'Full Name *'; ?></label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone"><?php echo $language === 'vi' ? 'Số điện thoại' : 'Phone Number'; ?></label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="subject"><?php echo $language === 'vi' ? 'Chủ đề *' : 'Subject *'; ?></label>
                                <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($form_data['subject']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message"><?php echo $language === 'vi' ? 'Tin nhắn *' : 'Message *'; ?></label>
                            <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($form_data['message']); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn"><?php echo $language === 'vi' ? 'Gửi tin nhắn' : 'Send Message'; ?></button>
                        </div>
                    </form>
                </div>
                
                <div class="contact-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3898.740916176312!2d109.21988495223439!3d12.264967685683095!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31706700a58103b1%3A0x9aa0e0844d2f4089!2sVinpearl%20Luxury%20Nha%20Trang!5e0!3m2!1sen!2s!4v1656518712694!5m2!1sen!2s" 
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQs -->
    <section class="faq-section">
        <div class="container">
            <h2><?php echo $language === 'vi' ? 'Câu hỏi thường gặp' : 'Frequently Asked Questions'; ?></h2>
            
            <div class="faq-items">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Làm thế nào để đến khu nghỉ dưỡng?' : 'How do I get to the resort?'; ?></h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Vinpearl Resort Nha Trang nằm trên đảo Hòn Tre. Du khách có thể đến đảo bằng cáp treo hoặc tàu cao tốc từ cảng Nha Trang. Chúng tôi cũng cung cấp dịch vụ đưa đón từ sân bay Cam Ranh.' : 'Vinpearl Resort Nha Trang is located on Hon Tre Island. Guests can reach the island via cable car or speedboat from Nha Trang Port. We also offer airport transfers from Cam Ranh Airport.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Giờ nhận phòng và trả phòng là mấy giờ?' : 'What are the check-in and check-out times?'; ?></h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Giờ nhận phòng là từ 14:00 và giờ trả phòng là trước 12:00 trưa.' : 'Check-in time is from 2:00 PM and check-out time is before 12:00 noon.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Khu nghỉ dưỡng có WiFi miễn phí không?' : 'Does the resort offer free WiFi?'; ?></h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo $language === 'vi' ? 'Có, chúng tôi cung cấp WiFi miễn phí trong tất cả các phòng và khu vực công cộng của khu nghỉ dưỡng.' : 'Yes, we provide complimentary WiFi in all rooms and public areas of the resort.'; ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php echo $language === 'vi' ? 'Khu nghỉ dưỡng có hồ bơi không?' : 'Does the resort have swimming pools?'; ?></h3>
                        <span class="faq-toggle">+</span>
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
</body>
</html> 