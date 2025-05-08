<?php
/**
 * Vinpearl Resort Nha Trang - User Profile Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require login to access this page
requireLogin('login.php?redirect=profile.php');

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get current user
$currentUser = getCurrentUser();

// Initialize variables
$profile_updated = false;
$profile_error = '';
$password_updated = false;
$password_error = '';

// Process profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    
    // Validate form data
    if (empty($full_name)) {
        $profile_error = $language === 'vi' ? 'Họ tên không được để trống' : 'Full name cannot be empty';
    } else {
        // Update user profile
        $result = updateUserProfile($currentUser['id'], $full_name, $phone);
        
        if ($result) {
            $profile_updated = true;
            // Refresh user data
            $currentUser = getCurrentUser();
        } else {
            $profile_error = $language === 'vi' ? 'Có lỗi xảy ra khi cập nhật thông tin' : 'An error occurred while updating your information';
        }
    }
}

// Process password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate form data
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin' : 'Please fill all fields';
    } elseif ($new_password !== $confirm_password) {
        $password_error = $language === 'vi' ? 'Mật khẩu mới không khớp' : 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $password_error = $language === 'vi' ? 'Mật khẩu phải có ít nhất 6 ký tự' : 'Password must be at least 6 characters';
    } else {
        // Change password
        $result = changeUserPassword($currentUser['id'], $current_password, $new_password);
        
        if ($result === true) {
            $password_updated = true;
        } else {
            $password_error = $result; // Error message from the function
            
            // Translate error message
            if ($language === 'vi' && $password_error === 'Current password is incorrect') {
                $password_error = 'Mật khẩu hiện tại không chính xác';
            }
        }
    }
}

// Get user bookings
$conn = connectDatabase();
$room_bookings = [];
$package_bookings = [];
$tour_bookings = [];

if ($conn) {
    // Get room bookings
    $stmt = $conn->prepare("
        SELECT rb.*, r.name_en, r.name_vi, r.image_url
        FROM room_bookings rb
        JOIN rooms r ON rb.room_id = r.id
        WHERE rb.user_id = ?
        ORDER BY rb.created_at DESC
    ");
    $stmt->bind_param("i", $currentUser['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($booking = $result->fetch_assoc()) {
        $room_bookings[] = $booking;
    }
    
    // Get package bookings
    $stmt = $conn->prepare("
        SELECT pb.*, p.name_en, p.name_vi, p.image_url
        FROM package_bookings pb
        JOIN packages p ON pb.package_id = p.id
        WHERE pb.user_id = ?
        ORDER BY pb.created_at DESC
    ");
    $stmt->bind_param("i", $currentUser['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($booking = $result->fetch_assoc()) {
        $package_bookings[] = $booking;
    }
    
    // Get tour bookings
    $stmt = $conn->prepare("
        SELECT tb.*, t.name_en, t.name_vi, t.image_url
        FROM tour_bookings tb
        JOIN tours t ON tb.tour_id = t.id
        WHERE tb.user_id = ?
        ORDER BY tb.created_at DESC
    ");
    $stmt->bind_param("i", $currentUser['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($booking = $result->fetch_assoc()) {
        $tour_bookings[] = $booking;
    }
}

// Get user reviews
$reviews = [];
if ($conn) {
    $stmt = $conn->prepare("
        SELECT r.*, 
               CASE 
                   WHEN r.type = 'room' THEN (SELECT name_en FROM rooms WHERE id = r.item_id)
                   WHEN r.type = 'package' THEN (SELECT name_en FROM packages WHERE id = r.item_id)
                   WHEN r.type = 'tour' THEN (SELECT name_en FROM tours WHERE id = r.item_id)
               END as item_name_en,
               CASE 
                   WHEN r.type = 'room' THEN (SELECT name_vi FROM rooms WHERE id = r.item_id)
                   WHEN r.type = 'package' THEN (SELECT name_vi FROM packages WHERE id = r.item_id)
                   WHEN r.type = 'tour' THEN (SELECT name_vi FROM tours WHERE id = r.item_id)
               END as item_name_vi
        FROM reviews r
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("i", $currentUser['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($review = $result->fetch_assoc()) {
        $reviews[] = $review;
    }
}

// Page title
$pageTitle = $language === 'vi' ? 'Hồ Sơ Cá Nhân' : 'My Profile';

// Active tab (profile, bookings, reviews)
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';
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
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="language-selector">
                    <a href="?<?php echo !empty($activeTab) ? "tab=$activeTab&" : ""; ?>lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                    <a href="?<?php echo !empty($activeTab) ? "tab=$activeTab&" : ""; ?>lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
                </div>
                
                <div class="user-actions">
                    <div class="user-menu">
                        <span>Hello, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <div class="dropdown-menu">
                            <a href="profile.php" class="active"><?php echo $language === 'vi' ? 'Hồ sơ' : 'Profile'; ?></a>
                            <a href="profile.php?tab=bookings"><?php echo $language === 'vi' ? 'Đặt chỗ' : 'My Bookings'; ?></a>
                            <?php if (isAdmin()): ?>
                                <a href="admin/index.php"><?php echo $language === 'vi' ? 'Quản trị' : 'Admin Panel'; ?></a>
                            <?php endif; ?>
                            <a href="logout.php"><?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><?php echo $pageTitle; ?></h1>
            <p><?php echo $language === 'vi' ? 'Quản lý thông tin cá nhân và hoạt động của bạn' : 'Manage your personal information and activities'; ?></p>
        </div>
    </div>

    <!-- Profile Content -->
    <section class="profile-section">
        <div class="container">
            <div class="profile-navigation">
                <ul class="profile-tabs">
                    <li><a href="profile.php?<?php echo $language === 'vi' ? 'lang=vi' : ''; ?>" class="<?php echo $activeTab === 'profile' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Thông tin cá nhân' : 'Personal Info'; ?></a></li>
                    <li><a href="profile.php?tab=bookings<?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="<?php echo $activeTab === 'bookings' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Đặt chỗ của tôi' : 'My Bookings'; ?></a></li>
                    <li><a href="profile.php?tab=reviews<?php echo $language === 'vi' ? '&lang=vi' : ''; ?>" class="<?php echo $activeTab === 'reviews' ? 'active' : ''; ?>"><?php echo $language === 'vi' ? 'Đánh giá của tôi' : 'My Reviews'; ?></a></li>
                </ul>
            </div>
            
            <div class="profile-content">
                <?php if ($activeTab === 'profile'): ?>
                    <!-- Personal Information Tab -->
                    <div class="profile-info">
                        <div class="profile-forms">
                            <div class="profile-form">
                                <h2><?php echo $language === 'vi' ? 'Thông tin cá nhân' : 'Personal Information'; ?></h2>
                                
                                <?php if ($profile_updated): ?>
                                    <div class="alert alert-success">
                                        <?php echo $language === 'vi' ? 'Thông tin cá nhân đã được cập nhật thành công!' : 'Personal information updated successfully!'; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($profile_error)): ?>
                                    <div class="alert alert-error">
                                        <?php echo htmlspecialchars($profile_error); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="post" class="profile-update-form validate">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly disabled>
                                        <small><?php echo $language === 'vi' ? 'Email không thể thay đổi' : 'Email cannot be changed'; ?></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="full_name"><?php echo $language === 'vi' ? 'Họ tên đầy đủ *' : 'Full Name *'; ?></label>
                                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone"><?php echo $language === 'vi' ? 'Số điện thoại' : 'Phone Number'; ?></label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($currentUser['phone']); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label><?php echo $language === 'vi' ? 'Ngày đăng ký' : 'Registration Date'; ?></label>
                                        <input type="text" value="<?php echo formatDate($currentUser['created_at'], 'M d, Y'); ?>" readonly disabled>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label><?php echo $language === 'vi' ? 'Đăng nhập lần cuối' : 'Last Login'; ?></label>
                                        <input type="text" value="<?php echo formatDate($currentUser['last_login'], 'M d, Y H:i'); ?>" readonly disabled>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" name="update_profile" class="btn"><?php echo $language === 'vi' ? 'Cập nhật thông tin' : 'Update Information'; ?></button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="profile-form">
                                <h2><?php echo $language === 'vi' ? 'Thay đổi mật khẩu' : 'Change Password'; ?></h2>
                                
                                <?php if ($password_updated): ?>
                                    <div class="alert alert-success">
                                        <?php echo $language === 'vi' ? 'Mật khẩu đã được thay đổi thành công!' : 'Password changed successfully!'; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($password_error)): ?>
                                    <div class="alert alert-error">
                                        <?php echo htmlspecialchars($password_error); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="post" class="password-change-form validate">
                                    <div class="form-group">
                                        <label for="current_password"><?php echo $language === 'vi' ? 'Mật khẩu hiện tại *' : 'Current Password *'; ?></label>
                                        <input type="password" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password"><?php echo $language === 'vi' ? 'Mật khẩu mới *' : 'New Password *'; ?></label>
                                        <input type="password" id="new_password" name="new_password" required>
                                        <small><?php echo $language === 'vi' ? 'Tối thiểu 6 ký tự' : 'Minimum 6 characters'; ?></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password"><?php echo $language === 'vi' ? 'Xác nhận mật khẩu mới *' : 'Confirm New Password *'; ?></label>
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" name="change_password" class="btn"><?php echo $language === 'vi' ? 'Thay đổi mật khẩu' : 'Change Password'; ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php elseif ($activeTab === 'bookings'): ?>
                    <!-- Bookings Tab -->
                    <div class="profile-bookings">
                        <h2><?php echo $language === 'vi' ? 'Đặt phòng' : 'Room Bookings'; ?></h2>
                        
                        <?php if (empty($room_bookings)): ?>
                            <p><?php echo $language === 'vi' ? 'Bạn chưa có đặt phòng nào.' : 'You don\'t have any room bookings yet.'; ?></p>
                        <?php else: ?>
                            <div class="booking-list">
                                <?php foreach ($room_bookings as $booking): ?>
                                    <div class="booking-item">
                                        <div class="booking-image">
                                            <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                                            <span class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                                <?php 
                                                $statusLabels = [
                                                    'pending' => $language === 'vi' ? 'Đang chờ' : 'Pending',
                                                    'confirmed' => $language === 'vi' ? 'Đã xác nhận' : 'Confirmed',
                                                    'cancelled' => $language === 'vi' ? 'Đã hủy' : 'Cancelled',
                                                    'completed' => $language === 'vi' ? 'Đã hoàn thành' : 'Completed'
                                                ];
                                                echo $statusLabels[$booking['status']] ?? ucfirst($booking['status']);
                                                ?>
                                            </span>
                                        </div>
                                        <div class="booking-details">
                                            <h3><?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                            <div class="booking-info">
                                                <div class="info-item">
                                                    <i class="icon-calendar"></i>
                                                    <span><?php echo $language === 'vi' ? 'Nhận phòng:' : 'Check-in:'; ?> <?php echo formatDate($booking['check_in_date'], 'M d, Y'); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-calendar"></i>
                                                    <span><?php echo $language === 'vi' ? 'Trả phòng:' : 'Check-out:'; ?> <?php echo formatDate($booking['check_out_date'], 'M d, Y'); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-user"></i>
                                                    <span><?php echo $booking['guests']; ?> <?php echo $language === 'vi' ? 'khách' : 'guests'; ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-price"></i>
                                                    <span><?php echo formatCurrency($booking['total_price']); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-date"></i>
                                                    <span><?php echo $language === 'vi' ? 'Đặt ngày:' : 'Booked on:'; ?> <?php echo formatDate($booking['created_at'], 'M d, Y'); ?></span>
                                                </div>
                                            </div>
                                            
                                            <?php if ($booking['status'] === 'completed' && !hasReviewed($currentUser['id'], 'room', $booking['room_id'])): ?>
                                                <div class="booking-actions">
                                                    <a href="write-review.php?type=room&id=<?php echo $booking['room_id']; ?>" class="btn btn-sm">
                                                        <?php echo $language === 'vi' ? 'Viết đánh giá' : 'Write Review'; ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h2><?php echo $language === 'vi' ? 'Đặt gói dịch vụ' : 'Package Bookings'; ?></h2>
                        
                        <?php if (empty($package_bookings)): ?>
                            <p><?php echo $language === 'vi' ? 'Bạn chưa có đặt gói dịch vụ nào.' : 'You don\'t have any package bookings yet.'; ?></p>
                        <?php else: ?>
                            <div class="booking-list">
                                <?php foreach ($package_bookings as $booking): ?>
                                    <div class="booking-item">
                                        <div class="booking-image">
                                            <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                                            <span class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                                <?php 
                                                echo $statusLabels[$booking['status']] ?? ucfirst($booking['status']);
                                                ?>
                                            </span>
                                        </div>
                                        <div class="booking-details">
                                            <h3><?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                            <div class="booking-info">
                                                <div class="info-item">
                                                    <i class="icon-calendar"></i>
                                                    <span><?php echo $language === 'vi' ? 'Ngày bắt đầu:' : 'Start date:'; ?> <?php echo formatDate($booking['start_date'], 'M d, Y'); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-user"></i>
                                                    <span><?php echo $booking['guests']; ?> <?php echo $language === 'vi' ? 'khách' : 'guests'; ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-price"></i>
                                                    <span><?php echo formatCurrency($booking['total_price']); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-date"></i>
                                                    <span><?php echo $language === 'vi' ? 'Đặt ngày:' : 'Booked on:'; ?> <?php echo formatDate($booking['created_at'], 'M d, Y'); ?></span>
                                                </div>
                                            </div>
                                            
                                            <?php if ($booking['status'] === 'completed' && !hasReviewed($currentUser['id'], 'package', $booking['package_id'])): ?>
                                                <div class="booking-actions">
                                                    <a href="write-review.php?type=package&id=<?php echo $booking['package_id']; ?>" class="btn btn-sm">
                                                        <?php echo $language === 'vi' ? 'Viết đánh giá' : 'Write Review'; ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h2><?php echo $language === 'vi' ? 'Đặt tour' : 'Tour Bookings'; ?></h2>
                        
                        <?php if (empty($tour_bookings)): ?>
                            <p><?php echo $language === 'vi' ? 'Bạn chưa có đặt tour nào.' : 'You don\'t have any tour bookings yet.'; ?></p>
                        <?php else: ?>
                            <div class="booking-list">
                                <?php foreach ($tour_bookings as $booking): ?>
                                    <div class="booking-item">
                                        <div class="booking-image">
                                            <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                                            <span class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                                <?php 
                                                echo $statusLabels[$booking['status']] ?? ucfirst($booking['status']);
                                                ?>
                                            </span>
                                        </div>
                                        <div class="booking-details">
                                            <h3><?php echo htmlspecialchars($booking[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                            <div class="booking-info">
                                                <div class="info-item">
                                                    <i class="icon-calendar"></i>
                                                    <span><?php echo $language === 'vi' ? 'Ngày tham gia:' : 'Tour date:'; ?> <?php echo formatDate($booking['tour_date'], 'M d, Y'); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-user"></i>
                                                    <span><?php echo $booking['guests']; ?> <?php echo $language === 'vi' ? 'khách' : 'guests'; ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-price"></i>
                                                    <span><?php echo formatCurrency($booking['total_price']); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="icon-date"></i>
                                                    <span><?php echo $language === 'vi' ? 'Đặt ngày:' : 'Booked on:'; ?> <?php echo formatDate($booking['created_at'], 'M d, Y'); ?></span>
                                                </div>
                                            </div>
                                            
                                            <?php if ($booking['status'] === 'completed' && !hasReviewed($currentUser['id'], 'tour', $booking['tour_id'])): ?>
                                                <div class="booking-actions">
                                                    <a href="write-review.php?type=tour&id=<?php echo $booking['tour_id']; ?>" class="btn btn-sm">
                                                        <?php echo $language === 'vi' ? 'Viết đánh giá' : 'Write Review'; ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($activeTab === 'reviews'): ?>
                    <!-- Reviews Tab -->
                    <div class="profile-reviews">
                        <h2><?php echo $language === 'vi' ? 'Đánh giá của bạn' : 'Your Reviews'; ?></h2>
                        
                        <?php if (empty($reviews)): ?>
                            <p><?php echo $language === 'vi' ? 'Bạn chưa viết đánh giá nào.' : 'You haven\'t written any reviews yet.'; ?></p>
                        <?php else: ?>
                            <div class="reviews-list">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="review-meta">
                                                <h3><?php echo htmlspecialchars($review[$language === 'vi' ? 'title_vi' : 'title_en']); ?></h3>
                                                <div class="review-for">
                                                    <?php 
                                                    $typeLabels = [
                                                        'room' => $language === 'vi' ? 'Phòng' : 'Room',
                                                        'package' => $language === 'vi' ? 'Gói dịch vụ' : 'Package',
                                                        'tour' => $language === 'vi' ? 'Tour' : 'Tour'
                                                    ];
                                                    $typeLabel = $typeLabels[$review['type']] ?? ucfirst($review['type']);
                                                    $itemName = htmlspecialchars($review[$language === 'vi' ? 'item_name_vi' : 'item_name_en']);
                                                    echo $language === 'vi' ? "Đánh giá cho: $typeLabel - $itemName" : "Review for: $typeLabel - $itemName";
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="review-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div class="review-content">
                                            <p><?php echo htmlspecialchars($review[$language === 'vi' ? 'content_vi' : 'content_en']); ?></p>
                                        </div>
                                        <div class="review-footer">
                                            <span class="review-date"><?php echo formatDate($review['created_at'], 'M d, Y'); ?></span>
                                            <?php if (!$review['is_approved']): ?>
                                                <span class="review-pending"><?php echo $language === 'vi' ? 'Đang chờ duyệt' : 'Pending approval'; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
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

<?php
/**
 * Helper function to check if a user has reviewed an item
 */
function hasReviewed($userId, $type, $itemId) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews 
                           WHERE user_id = ? AND type = ? AND item_id = ?");
    $stmt->bind_param("isi", $userId, $type, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['review_count'] > 0;
} 