<?php
/**
 * Vinpearl Resort Nha Trang - Room Details Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get room ID from URL
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no room ID provided, redirect to rooms listing
if ($room_id <= 0) {
    header('Location: rooms.php');
    exit;
}

// Get room details
$conn = connectDatabase();
if (!$conn) {
    // Handle database connection error
    die("Database connection failed");
}

$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Room not found, redirect to rooms listing
    header('Location: rooms.php');
    exit;
}

$room = $result->fetch_assoc();
$room['amenities'] = json_decode($room['amenities'], true);

// Get room images
$imagesStmt = $conn->prepare("SELECT image_url FROM room_images WHERE room_id = ?");
$imagesStmt->bind_param("i", $room_id);
$imagesStmt->execute();
$imagesResult = $imagesStmt->get_result();

$images = [];
while ($image = $imagesResult->fetch_assoc()) {
    $images[] = $image['image_url'];
}

// If no additional images, use the main image
if (empty($images)) {
    $images[] = $room['image_url'];
}

// Get room reviews
$reviews = getReviews('room', $room_id, $language);

// Get current user if logged in
$currentUser = getCurrentUser();

// Booking form processing
$bookingError = '';
$bookingSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_room'])) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        // Redirect to login page with return URL
        header('Location: login.php?redirect=' . urlencode("room-details.php?id=$room_id"));
        exit;
    }
    
    $check_in_date = isset($_POST['check_in_date']) ? $_POST['check_in_date'] : '';
    $check_out_date = isset($_POST['check_out_date']) ? $_POST['check_out_date'] : '';
    $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
    $total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0;
    
    // Validate booking data
    if (empty($check_in_date) || empty($check_out_date)) {
        $bookingError = $language === 'vi' ? 'Vui lòng chọn ngày nhận phòng và trả phòng' : 'Please select check-in and check-out dates';
    } elseif ($guests <= 0 || $guests > $room['capacity']) {
        $bookingError = $language === 'vi' ? 'Số lượng khách không hợp lệ' : 'Invalid number of guests';
    } elseif ($total_price <= 0) {
        $bookingError = $language === 'vi' ? 'Tổng giá không hợp lệ' : 'Invalid total price';
    } else {
        // Check if room is available for the selected dates
        if (isRoomAvailable($room_id, $check_in_date, $check_out_date)) {
            // Create booking
            $bookingData = [
                'user_id' => $currentUser['id'],
                'room_id' => $room_id,
                'check_in_date' => $check_in_date,
                'check_out_date' => $check_out_date,
                'guests' => $guests,
                'total_price' => $total_price
            ];
            
            $bookingId = createRoomBooking($bookingData);
            
            if ($bookingId) {
                $bookingSuccess = $language === 'vi' ? 'Đặt phòng thành công! Mã đặt phòng của bạn là: ' : 'Booking successful! Your booking ID is: ';
                $bookingSuccess .= $bookingId;
            } else {
                $bookingError = $language === 'vi' ? 'Có lỗi xảy ra khi đặt phòng. Vui lòng thử lại.' : 'An error occurred during booking. Please try again.';
            }
        } else {
            $bookingError = $language === 'vi' ? 'Phòng không còn trống trong thời gian bạn chọn. Vui lòng chọn ngày khác.' : 'Room is not available for the selected dates. Please choose different dates.';
        }
    }
}

// Page title
$pageTitle = $language === 'vi' ? $room['name_vi'] : $room['name_en'];
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/room-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <li><a href="rooms.php" class="active"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                    <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                    <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                    <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="language-selector">
                    <a href="?id=<?php echo $room_id; ?>&lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                    <a href="?id=<?php echo $room_id; ?>&lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
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

    <!-- Room Details -->
    <section class="room-details">
        <div class="container">
            <div class="room-header">
                <div class="room-title">
                    <h1><?php echo htmlspecialchars($room[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h1>
                </div>
                <div class="room-price">
                    <span class="price"><?php echo formatCurrency($room['price_per_night']); ?></span>
                    <span class="per-night"><?php echo $language === 'vi' ? '/ đêm' : '/ night'; ?></span>
                </div>
            </div>
            
            <!-- Room Gallery -->
            <div class="room-gallery">
                <div class="room-slider">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="room-slide <?php echo $index === 0 ? 'active' : ''; ?>" <?php echo $index > 0 ? 'style="display: none;"' : ''; ?>>
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($room[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="gallery-nav">
                    <button class="prev-slide" aria-label="Previous image">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="next-slide" aria-label="Next image">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="gallery-dots">
                    <?php foreach ($images as $index => $image): ?>
                        <button class="gallery-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>" aria-label="Go to image <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="room-content">
                <div class="room-info">
                    <div class="room-description">
                        <h2><?php echo $language === 'vi' ? 'Chi tiết phòng' : 'Room Details'; ?></h2>
                        <p><?php echo htmlspecialchars($room[$language === 'vi' ? 'description_vi' : 'description_en']); ?></p>
                        
                        <div class="room-specs">
                            <div class="spec-item">
                                <h3><?php echo $language === 'vi' ? 'Sức chứa' : 'Capacity'; ?></h3>
                                <p><?php echo $room['capacity']; ?> <?php echo $language === 'vi' ? 'Người' : 'Guests'; ?></p>
                            </div>
                            <div class="spec-item">
                                <h3><?php echo $language === 'vi' ? 'Kích thước' : 'Size'; ?></h3>
                                <p><?php echo htmlspecialchars($room['room_size']); ?></p>
                            </div>
                            <div class="spec-item">
                                <h3><?php echo $language === 'vi' ? 'Giường' : 'Bed'; ?></h3>
                                <p><?php echo htmlspecialchars($room['bed_type']); ?></p>
                            </div>
                        </div>
                        
                        <h2><?php echo $language === 'vi' ? 'Tiện nghi' : 'Amenities'; ?></h2>
                        <ul class="amenities-list">
                            <?php foreach ($room['amenities'] as $amenity): ?>
                                <li>
                                    <i class="icon-check"></i>
                                    <?php echo htmlspecialchars($amenity); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Booking Form -->
                    <div class="booking-form-container">
                        <h2><?php echo $language === 'vi' ? 'Đặt phòng' : 'Book This Room'; ?></h2>
                        
                        <?php if (!empty($bookingError)): ?>
                            <div class="alert alert-error">
                                <?php echo htmlspecialchars($bookingError); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($bookingSuccess)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($bookingSuccess); ?>
                            </div>
                        <?php else: ?>
                            <form method="post" id="booking-form" class="booking-form validate">
                                <div class="form-group">
                                    <label for="check_in_date"><?php echo $language === 'vi' ? 'Ngày nhận phòng' : 'Check-in Date'; ?></label>
                                    <input type="date" id="check_in_date" name="check_in_date" class="future-date" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="check_out_date"><?php echo $language === 'vi' ? 'Ngày trả phòng' : 'Check-out Date'; ?></label>
                                    <input type="date" id="check_out_date" name="check_out_date" class="future-date" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="guests"><?php echo $language === 'vi' ? 'Số khách' : 'Number of Guests'; ?></label>
                                    <select id="guests" name="guests" required>
                                        <?php for ($i = 1; $i <= $room['capacity']; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="pricing-summary">
                                    <div class="price-row">
                                        <span><?php echo $language === 'vi' ? 'Giá mỗi đêm' : 'Price per night'; ?></span>
                                        <span id="room-price" data-price="<?php echo $room['price_per_night']; ?>">
                                            <?php echo formatCurrency($room['price_per_night']); ?>
                                        </span>
                                    </div>
                                    <div class="price-row">
                                        <span><?php echo $language === 'vi' ? 'Tổng cộng' : 'Total'; ?></span>
                                        <span id="total-price">0.00</span>
                                    </div>
                                    <input type="hidden" name="total_price" id="total_price" value="0">
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="book_room" class="btn"><?php echo $language === 'vi' ? 'Đặt ngay' : 'Book Now'; ?></button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="reviews-section">
                <h2><?php echo $language === 'vi' ? 'Đánh giá' : 'Reviews'; ?></h2>
                
                <?php if (empty($reviews)): ?>
                    <p><?php echo $language === 'vi' ? 'Chưa có đánh giá nào cho phòng này.' : 'No reviews for this room yet.'; ?></p>
                <?php else: ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="review-meta">
                                        <span class="reviewer-name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                        <span class="review-date"><?php echo formatDate($review['created_at'], 'M d, Y'); ?></span>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <h3 class="review-title"><?php echo htmlspecialchars($review[$language === 'vi' ? 'title_vi' : 'title_en']); ?></h3>
                                <div class="review-content"><?php echo htmlspecialchars($review[$language === 'vi' ? 'content_vi' : 'content_en']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <div class="write-review">
                        <a href="write-review.php?type=room&id=<?php echo $room_id; ?>" class="btn btn-outline">
                            <?php echo $language === 'vi' ? 'Viết đánh giá' : 'Write a Review'; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Room gallery slider functionality
        const slides = document.querySelectorAll('.room-slide');
        const dots = document.querySelectorAll('.gallery-dot');
        const prevButton = document.querySelector('.prev-slide');
        const nextButton = document.querySelector('.next-slide');
        let currentSlide = 0;
        const totalSlides = slides.length;
        
        // Function to show a specific slide
        function showSlide(index) {
            // Hide all slides
            slides.forEach(slide => slide.style.display = 'none');
            dots.forEach(dot => dot.classList.remove('active'));
            
            // Show the selected slide
            slides[index].style.display = 'block';
            dots[index].classList.add('active');
            currentSlide = index;
        }
        
        // Event listeners for navigation buttons
        if (prevButton && nextButton && totalSlides > 1) {
            prevButton.addEventListener('click', () => {
                let index = currentSlide - 1;
                if (index < 0) index = totalSlides - 1;
                showSlide(index);
            });
            
            nextButton.addEventListener('click', () => {
                let index = currentSlide + 1;
                if (index >= totalSlides) index = 0;
                showSlide(index);
            });
            
            // Event listeners for dot navigation
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    showSlide(index);
                });
            });
        } else {
            // Hide navigation if only one image
            if (prevButton && nextButton) {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
            }
        }
        
        // Initialize booking form date validation
        const checkInDate = document.getElementById('check_in_date');
        const checkOutDate = document.getElementById('check_out_date');
        
        if (checkInDate && checkOutDate) {
            // Set min dates to today and tomorrow
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            checkInDate.min = formatDate(today);
            checkOutDate.min = formatDate(tomorrow);
            
            // Update checkout min date when checkin changes
            checkInDate.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const nextDay = new Date(selectedDate);
                nextDay.setDate(nextDay.getDate() + 1);
                
                checkOutDate.min = formatDate(nextDay);
                
                // If checkout date is before new minimum, update it
                if (checkOutDate.value && new Date(checkOutDate.value) <= selectedDate) {
                    checkOutDate.value = formatDate(nextDay);
                }
                
                updateTotalPrice();
            });
            
            // Update total price calculation
            checkOutDate.addEventListener('change', updateTotalPrice);
            
            function updateTotalPrice() {
                if (checkInDate.value && checkOutDate.value) {
                    // Calculate number of nights
                    const checkIn = new Date(checkInDate.value);
                    const checkOut = new Date(checkOutDate.value);
                    const nights = Math.floor((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                    
                    if (nights > 0) {
                        // Get room price from data attribute
                        const roomPrice = parseFloat(document.getElementById('room-price').dataset.price);
                        const totalPrice = roomPrice * nights;
                        
                        // Update displayed total price
                        document.getElementById('total-price').textContent = totalPrice.toFixed(2);
                        
                        // Update hidden total price input
                        const totalPriceInput = document.getElementById('total_price');
                        if (totalPriceInput) {
                            totalPriceInput.value = totalPrice.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>