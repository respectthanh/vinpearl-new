<?php
/**
 * Vinpearl Resort Nha Trang - Login Page
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
$email = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate form data
    if (empty($email) || empty($password)) {
        $error = $language === 'vi' ? 'Vui lòng nhập email và mật khẩu' : 'Please enter email and password';
    } else {
        // Attempt login
        $loginSuccessful = login($email, $password);
        
        if ($loginSuccessful) {
            // Redirect after successful login
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = $language === 'vi' ? 'Email hoặc mật khẩu không chính xác' : 'Invalid email or password';
        }
    }
}

// Page title
$pageTitle = $language === 'vi' ? 'Đăng nhập' : 'Login';

// Include header
include 'includes/header.php';
?>

    <!-- Login Form -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <h1><?php echo $pageTitle; ?></h1>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="post" class="auth-form validate">
                    <div class="form-group">
                        <label for="email"><?php echo $language === 'vi' ? 'Email' : 'Email'; ?></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><?php echo $language === 'vi' ? 'Mật khẩu' : 'Password'; ?></label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember"><?php echo $language === 'vi' ? 'Ghi nhớ đăng nhập' : 'Remember me'; ?></label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $language === 'vi' ? 'Đăng nhập' : 'Log In'; ?></button>
                    </div>
                    
                    <div class="auth-links">
                        <a href="forgot-password.php"><?php echo $language === 'vi' ? 'Quên mật khẩu?' : 'Forgot Password?'; ?></a>
                        <p><?php echo $language === 'vi' ? 'Chưa có tài khoản?' : 'Don\'t have an account?'; ?> 
                           <a href="register.php"><?php echo $language === 'vi' ? 'Đăng ký ngay' : 'Sign up now'; ?></a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>