<?php
/**
 * Vinpearl Resort Nha Trang - Register Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language
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
    'phone' => '',
];

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $form_data = [
        'email' => isset($_POST['email']) ? trim($_POST['email']) : '',
        'full_name' => isset($_POST['full_name']) ? trim($_POST['full_name']) : '',
        'phone' => isset($_POST['phone']) ? trim($_POST['phone']) : '',
    ];
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate form data
    if (empty($form_data['email']) || empty($password) || empty($form_data['full_name'])) {
        $error = $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin bắt buộc' : 'Please fill in all required fields';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = $language === 'vi' ? 'Địa chỉ email không hợp lệ' : 'Invalid email address';
    } elseif (strlen($password) < 6) {
        $error = $language === 'vi' ? 'Mật khẩu phải có ít nhất 6 ký tự' : 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = $language === 'vi' ? 'Mật khẩu xác nhận không khớp' : 'Password confirmation does not match';
    } else {
        // Check if email already exists
        if (emailExists($form_data['email'])) {
            $error = $language === 'vi' ? 'Email đã được sử dụng, vui lòng chọn email khác' : 'Email already in use, please choose another email';
        } else {
            // Register user
            $registered = registerUser($form_data['email'], $password, $form_data['full_name'], $form_data['phone']);
            
            if ($registered) {
                $success = $language === 'vi' ? 'Đăng ký thành công! Vui lòng đăng nhập.' : 'Registration successful! Please login.';
                $form_data = [
                    'email' => '',
                    'full_name' => '',
                    'phone' => '',
                ];
            } else {
                $error = $language === 'vi' ? 'Đã xảy ra lỗi, vui lòng thử lại sau' : 'An error occurred, please try again later';
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
                        <i class="fas fa-envelope"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name"><?php echo $language === 'vi' ? 'Họ và tên *' : 'Full Name *'; ?></label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($form_data['full_name']); ?>" required>
                        <i class="fas fa-user"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><?php echo $language === 'vi' ? 'Số điện thoại' : 'Phone Number'; ?></label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone']); ?>">
                        <i class="fas fa-phone"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><?php echo $language === 'vi' ? 'Mật khẩu *' : 'Password *'; ?></label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <i class="fas fa-lock password-toggle"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><?php echo $language === 'vi' ? 'Xác nhận mật khẩu *' : 'Confirm Password *'; ?></label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        <i class="fas fa-lock password-toggle"></i>
                    </div>
                    
                    <div class="form-group remember-me">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            <?php echo $language === 'vi' 
                                ? 'Tôi đồng ý với <a href="#">Điều khoản</a> và <a href="#">Chính sách bảo mật</a>' 
                                : 'I agree to the <a href="#">Terms</a> and <a href="#">Privacy Policy</a>'; ?>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $language === 'vi' ? 'Đăng ký' : 'Register'; ?></button>
                    </div>
                    
                    <div class="auth-links">
                        <p><?php echo $language === 'vi' ? 'Đã có tài khoản?' : 'Already have an account?'; ?> 
                           <a href="login.php"><?php echo $language === 'vi' ? 'Đăng nhập' : 'Login here'; ?></a>
                        </p>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add password visibility toggle functionality
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.classList.remove('fa-lock');
                this.classList.add('fa-lock-open');
            } else {
                passwordInput.type = 'password';
                this.classList.remove('fa-lock-open');
                this.classList.add('fa-lock');
            }
        });
    });
});
</script>
</body>
</html>