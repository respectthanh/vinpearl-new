<?php
/**
 * Vinpearl Resort Nha Trang - Write Review Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require login to access this page
requireLogin('login.php?redirect=write-review.php');

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get current user
$currentUser = getCurrentUser();

// Get review type and item ID from URL
$type = isset($_GET['type']) ? $_GET['type'] : '';
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate review type
$valid_types = ['room', 'package', 'tour'];
if (!in_array($type, $valid_types) || $item_id <= 0) {
    header('Location: profile.php?tab=bookings');
    exit;
}

// Check if the user has booked this item and hasn't already reviewed it
$conn = connectDatabase();
if (!$conn) {
    die("Database connection failed");
}

$canReview = false;
$hasBooked = false;

// Check if the user has booked this item
switch ($type) {
    case 'room':
        $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM room_bookings 
                               WHERE user_id = ? AND room_id = ? AND status = 'completed'");
        $stmt->bind_param("ii", $currentUser['id'], $item_id);
        break;
    case 'package':
        $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM package_bookings 
                               WHERE user_id = ? AND package_id = ? AND status = 'completed'");
        $stmt->bind_param("ii", $currentUser['id'], $item_id);
        break;
    case 'tour':
        $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM tour_bookings 
                               WHERE user_id = ? AND tour_id = ? AND status = 'completed'");
        $stmt->bind_param("ii", $currentUser['id'], $item_id);
        break;
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$hasBooked = $row['booking_count'] > 0;

// Check if the user has already reviewed this item
$stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews 
                       WHERE user_id = ? AND type = ? AND item_id = ?");
$stmt->bind_param("isi", $currentUser['id'], $type, $item_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$hasReviewed = $row['review_count'] > 0;

$canReview = $hasBooked && !$hasReviewed;

// If user cannot review, redirect back to profile
if (!$canReview) {
    header('Location: profile.php?tab=bookings');
    exit;
}

// Get item details
$item = null;
switch ($type) {
    case 'room':
        $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        break;
    case 'package':
        $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        break;
    case 'tour':
        $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        break;
}

$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    header('Location: profile.php?tab=bookings');
    exit;
}

// Initialize variables
$review_submitted = false;
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $title_en = isset($_POST['title_en']) ? trim($_POST['title_en']) : '';
    $title_vi = isset($_POST['title_vi']) ? trim($_POST['title_vi']) : '';
    $content_en = isset($_POST['content_en']) ? trim($_POST['content_en']) : '';
    $content_vi = isset($_POST['content_vi']) ? trim($_POST['content_vi']) : '';
    
    // Validate form data
    if ($rating < 1 || $rating > 5) {
        $error = $language === 'vi' ? 'Vui lòng chọn số sao đánh giá' : 'Please select a rating';
    } elseif (empty($title_en) || empty($content_en)) {
        $error = $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin đánh giá bằng tiếng Anh' : 'Please fill in all review fields in English';
    } else {
        // If Vietnamese fields are empty, use English content
        if (empty($title_vi)) {
            $title_vi = $title_en;
        }
        if (empty($content_vi)) {
            $content_vi = $content_en;
        }
        
        // Insert review
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, type, item_id, rating, title_en, title_vi, content_en, content_vi, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isiissss", $currentUser['id'], $type, $item_id, $rating, $title_en, $title_vi, $content_en, $content_vi);
        $result = $stmt->execute();
        
        if ($result) {
            $review_submitted = true;
        } else {
            $error = $language === 'vi' ? 'Có lỗi xảy ra khi lưu đánh giá' : 'An error occurred while saving your review';
        }
    }
}

// Set page title based on review type
$typeLabels = [
    'room' => $language === 'vi' ? 'phòng' : 'room',
    'package' => $language === 'vi' ? 'gói dịch vụ' : 'package',
    'tour' => $language === 'vi' ? 'tour' : 'tour'
];
$typeLabel = $typeLabels[$type] ?? '';
$pageTitle = $language === 'vi' ? 'Viết Đánh Giá cho ' . ucfirst($typeLabel) : 'Write Review for ' . ucfirst($typeLabel);

// Item name based on language
$itemName = $language === 'vi' ? $item['name_vi'] : $item['name_en'];
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .rating-input {
            display: inline-flex;
            flex-direction: row-reverse;
            margin-bottom: 20px;
        }
        .rating-input input {
            display: none;
        }
        .rating-input label {
            cursor: pointer;
            font-size: 30px;
            color: #ddd;
            padding: 0 5px;
        }
        .rating-input label:hover,
        .rating-input label:hover ~ label,
        .rating-input input:checked ~ label {
            color: #ffcc00;
        }
        .language-tabs {
            display: flex;
            margin-bottom: 20px;
        }
        .language-tab {
            padding: 10px 20px;
            margin-right: 5px;
            cursor: pointer;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
        }
        .language-tab.active {
            background-color: #f5f5f5;
        }
        .language-content {
            display: none;
            padding: 20px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .language-content.active {
            display: block;
        }
    </style>
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
                    <a href="?type=<?php echo urlencode($type); ?>&id=<?php echo $item_id; ?>&lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                    <a href="?type=<?php echo urlencode($type); ?>&id=<?php echo $item_id; ?>&lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
                </div>
                
                <div class="user-actions">
                    <div class="user-menu">
                        <span>Hello, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <div class="dropdown-menu">
                            <a href="profile.php"><?php echo $language === 'vi' ? 'Hồ sơ' : 'Profile'; ?></a>
                            <a href="profile.php?tab=bookings" class="active"><?php echo $language === 'vi' ? 'Đặt chỗ' : 'My Bookings'; ?></a>
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
            <p>
                <?php echo $language === 'vi' 
                    ? 'Chia sẻ trải nghiệm của bạn về ' . htmlspecialchars($itemName)
                    : 'Share your experience with ' . htmlspecialchars($itemName); 
                ?>
            </p>
        </div>
    </div>

    <!-- Review Form -->
    <section class="write-review-section">
        <div class="container">
            <?php if ($review_submitted): ?>
                <div class="success-message">
                    <h2><?php echo $language === 'vi' ? 'Cảm ơn bạn đã đánh giá!' : 'Thank you for your review!'; ?></h2>
                    <p><?php echo $language === 'vi' 
                        ? 'Đánh giá của bạn đã được gửi và đang chờ phê duyệt.'
                        : 'Your review has been submitted and is pending approval.'; 
                    ?></p>
                    <div class="action-btns">
                        <a href="profile.php?tab=reviews" class="btn"><?php echo $language === 'vi' ? 'Xem đánh giá của tôi' : 'View my reviews'; ?></a>
                        <a href="profile.php?tab=bookings" class="btn btn-outline"><?php echo $language === 'vi' ? 'Quay lại đặt chỗ' : 'Back to bookings'; ?></a>
                    </div>
                </div>
            <?php else: ?>
                <div class="review-form-container">
                    <div class="item-preview">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($itemName); ?>">
                        <h3><?php echo htmlspecialchars($itemName); ?></h3>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" class="review-form">
                        <div class="form-group">
                            <label><?php echo $language === 'vi' ? 'Đánh giá của bạn' : 'Your Rating'; ?> *</label>
                            <div class="rating-input">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5">★</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4">★</label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3">★</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2">★</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1">★</label>
                            </div>
                        </div>
                        
                        <div class="language-tabs">
                            <div class="language-tab active" data-target="english-content">
                                <?php echo $language === 'vi' ? 'Tiếng Anh (Bắt buộc)' : 'English (Required)'; ?>
                            </div>
                            <div class="language-tab" data-target="vietnamese-content">
                                <?php echo $language === 'vi' ? 'Tiếng Việt (Tùy chọn)' : 'Vietnamese (Optional)'; ?>
                            </div>
                        </div>
                        
                        <div id="english-content" class="language-content active">
                            <div class="form-group">
                                <label for="title_en"><?php echo $language === 'vi' ? 'Tiêu đề (Tiếng Anh)' : 'Title (English)'; ?> *</label>
                                <input type="text" id="title_en" name="title_en" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="content_en"><?php echo $language === 'vi' ? 'Nội dung đánh giá (Tiếng Anh)' : 'Review Content (English)'; ?> *</label>
                                <textarea id="content_en" name="content_en" rows="5" required></textarea>
                            </div>
                        </div>
                        
                        <div id="vietnamese-content" class="language-content">
                            <div class="form-group">
                                <label for="title_vi"><?php echo $language === 'vi' ? 'Tiêu đề (Tiếng Việt)' : 'Title (Vietnamese)'; ?></label>
                                <input type="text" id="title_vi" name="title_vi">
                            </div>
                            
                            <div class="form-group">
                                <label for="content_vi"><?php echo $language === 'vi' ? 'Nội dung đánh giá (Tiếng Việt)' : 'Review Content (Vietnamese)'; ?></label>
                                <textarea id="content_vi" name="content_vi" rows="5"></textarea>
                            </div>
                        </div>
                        
                        <div class="review-policy">
                            <p>
                                <?php echo $language === 'vi' 
                                    ? 'Bằng cách gửi đánh giá này, bạn xác nhận rằng đây là trải nghiệm thực tế của bạn và tuân theo các nguyên tắc đánh giá của chúng tôi.'
                                    : 'By submitting this review, you confirm that this is your actual experience and that you agree to our review guidelines.'; 
                                ?>
                            </p>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn"><?php echo $language === 'vi' ? 'Gửi đánh giá' : 'Submit Review'; ?></button>
                            <a href="profile.php?tab=bookings" class="btn btn-outline"><?php echo $language === 'vi' ? 'Hủy' : 'Cancel'; ?></a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
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
    <script>
        // Language tabs functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.language-tab');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and content
                    document.querySelectorAll('.language-tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.language-content').forEach(c => c.classList.remove('active'));
                    
                    // Add active class to this tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const target = this.getAttribute('data-target');
                    document.getElementById(target).classList.add('active');
                });
            });
        });
    </script>
</body>
</html> 