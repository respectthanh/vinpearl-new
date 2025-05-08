<?php
/**
 * Vinpearl Resort Nha Trang - Registration Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language (in a real implementation, this would be more sophisticated)
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// If user is already logged in, redirect to homepage
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$form_data = [
    'email' => '',
    'full_name' => '',
    'phone' => ''
];

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'email' => isset($_POST['email']) ? trim($_POST['email']) : '',
        'full_name' => isset($_POST['full_name']) ? trim($_POST['full_name']) : '',
        'phone' => isset($_POST['phone']) ? trim($_POST['phone']) : ''
    ];
    
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    
    // Validate form data
    if (empty($form_data['email']) || empty($form_data['full_name']) || empty($password)) {
        $error = $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin bắt buộc' : 'Please fill all required fields';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = $language === 'vi' ? 'Email không hợp lệ' : 'Invalid email address';
    } elseif ($password !== $password_confirm) {
        $error = $language === 'vi' ? 'Mật khẩu xác nhận không khớp' : 'Password confirmation does not match';
    } elseif (strlen($password) < 6) {
        $error = $language === 'vi' ? 'Mật khẩu phải có ít nhất 6 ký tự' : 'Password must be at least 6 characters long';
    } else {
        // Attempt registration
        $result = register($form_data['email'], $password, $form_data['full_name'], $form_data['phone']);
        
        if (is_array($result) && !isset($result['error'])) {
            // Registration successful
            $success = $language === 'vi' ? 'Đăng ký thành công! Giờ đây bạn có thể đăng nhập.' : 'Registration successful! You can now login.';
            $form_data = [
                'email' => '',
                'full_name' => '',
                'phone' => ''
            ];
        } else {
            // Registration failed
            $error = isset($result['error']) ? $result['error'] : 'Registration failed';
            
            // Translate error messages
            if ($language === 'vi' && $error === 'Email already registered') {
                $error = 'Email đã được đăng ký';
            }
        }
    }
}

// Page title
$pageTitle = $language === 'vi' ? 'Đăng ký' : 'Register';

// Include header
include 'includes/header.php';
?>

    <!-- Registration Form -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <h1><?php echo $pageTitle; ?></h1>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <p><a href="login.php"><?php echo $language === 'vi' ? 'Đăng nhập ngay' : 'Login now'; ?></a></p>
                </div>
                <?php else: ?>
                <form method="post" class="auth-form validate">
                    <div class="form-group">
                        <label for="email"><?php echo $language === 'vi' ? 'Email *' : 'Email *'; ?></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name"><?php echo $language === 'vi' ? 'Họ tên đầy đủ *' : 'Full Name *'; ?></label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($form_data['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><?php echo $language === 'vi' ? 'Số điện thoại' : 'Phone Number'; ?></label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><?php echo $language === 'vi' ? 'Mật khẩu *' : 'Password *'; ?></label>
                        <input type="password" id="password" name="password" required>
                        <small><?php echo $language === 'vi' ? 'Tối thiểu 6 ký tự' : 'Minimum 6 characters'; ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm"><?php echo $language === 'vi' ? 'Xác nhận mật khẩu *' : 'Confirm Password *'; ?></label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">
                                <?php echo $language === 'vi' ? 'Tôi đồng ý với' : 'I agree to the'; ?> 
                                <a href="terms.php"><?php echo $language === 'vi' ? 'Điều khoản sử dụng' : 'Terms of Service'; ?></a>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $language === 'vi' ? 'Đăng ký' : 'Register'; ?></button>
                    </div>
                    
                    <div class="auth-links">
                        <p><?php echo $language === 'vi' ? 'Đã có tài khoản?' : 'Already have an account?'; ?> 
                           <a href="login.php"><?php echo $language === 'vi' ? 'Đăng nhập' : 'Log in'; ?></a>
                        </p>
                    </div>
                </form>
                <?php endif; ?>
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