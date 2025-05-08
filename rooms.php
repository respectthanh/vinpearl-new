<?php
/**
 * Vinpearl Resort Nha Trang - Rooms Page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language (in a real implementation, this would be more sophisticated)
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Process filter form
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['capacity']) && is_numeric($_GET['capacity'])) {
        $filters['capacity'] = (int)$_GET['capacity'];
    }
    
    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $filters['max_price'] = (float)$_GET['max_price'];
    }
}

// Get all rooms with applied filters
$rooms = getRooms($filters, $language);

// Get current user if logged in
$currentUser = getCurrentUser();

// Page title
$pageTitle = $language === 'vi' ? 'Phòng & Suites' : 'Rooms & Suites';

// Include header
include 'includes/header.php';
?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><?php echo $pageTitle; ?></h1>
            <p><?php echo $language === 'vi' ? 'Khám phá các lựa chọn lưu trú sang trọng của chúng tôi' : 'Explore our luxurious accommodation options'; ?></p>
        </div>
    </div>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="container">
            <form action="rooms.php" method="get" class="filter-form">
                <input type="hidden" name="lang" value="<?php echo $language; ?>">
                
                <div class="filter-group">
                    <label for="capacity" class="filter-label"><?php echo $language === 'vi' ? 'Số khách' : 'Guests'; ?></label>
                    <select id="capacity" name="capacity" class="filter-input">
                        <option value=""><?php echo $language === 'vi' ? 'Tất cả' : 'Any'; ?></option>
                        <option value="1" <?php echo isset($filters['capacity']) && $filters['capacity'] == 1 ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo isset($filters['capacity']) && $filters['capacity'] == 2 ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo isset($filters['capacity']) && $filters['capacity'] == 3 ? 'selected' : ''; ?>>3</option>
                        <option value="4" <?php echo isset($filters['capacity']) && $filters['capacity'] == 4 ? 'selected' : ''; ?>>4+</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="max_price" class="filter-label"><?php echo $language === 'vi' ? 'Giá tối đa' : 'Max Price'; ?></label>
                    <select id="max_price" name="max_price" class="filter-input">
                        <option value=""><?php echo $language === 'vi' ? 'Tất cả' : 'Any'; ?></option>
                        <option value="150" <?php echo isset($filters['max_price']) && $filters['max_price'] == 150 ? 'selected' : ''; ?>>$150</option>
                        <option value="250" <?php echo isset($filters['max_price']) && $filters['max_price'] == 250 ? 'selected' : ''; ?>>$250</option>
                        <option value="350" <?php echo isset($filters['max_price']) && $filters['max_price'] == 350 ? 'selected' : ''; ?>>$350</option>
                        <option value="500" <?php echo isset($filters['max_price']) && $filters['max_price'] == 500 ? 'selected' : ''; ?>>$500</option>
                    </select>
                </div>
                
                <div class="filter-group filter-actions">
                    <button type="submit" class="btn"><?php echo $language === 'vi' ? 'Lọc' : 'Filter'; ?></button>
                    <a href="rooms.php?lang=<?php echo $language; ?>" class="btn btn-outline"><?php echo $language === 'vi' ? 'Đặt lại' : 'Reset'; ?></a>
                </div>
            </form>
        </div>
    </section>

    <!-- Rooms List -->
    <section class="rooms-list">
        <div class="container">
            <?php if (empty($rooms)): ?>
                <div class="no-results">
                    <h2><?php echo $language === 'vi' ? 'Không có phòng nào phù hợp với bộ lọc của bạn' : 'No rooms match your filters'; ?></h2>
                    <p><?php echo $language === 'vi' ? 'Vui lòng thử lại với các bộ lọc khác' : 'Please try again with different filters'; ?></p>
                </div>
            <?php else: ?>
                <div class="room-cards">
                    <?php foreach ($rooms as $room): ?>
                        <div class="room-card">
                            <div class="room-image">
                                <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="<?php echo htmlspecialchars($room[$language === 'vi' ? 'name_vi' : 'name_en']); ?>">
                            </div>
                            <div class="room-details">
                                <h3><?php echo htmlspecialchars($room[$language === 'vi' ? 'name_vi' : 'name_en']); ?></h3>
                                <p class="room-description"><?php echo htmlspecialchars(substr($room[$language === 'vi' ? 'description_vi' : 'description_en'], 0, 100) . '...'); ?></p>
                                <div class="room-features">
                                    <span><i class="icon-user"></i> <?php echo $room['capacity']; ?> <?php echo $language === 'vi' ? 'Người' : 'Guests'; ?></span>
                                    <span><i class="icon-bed"></i> <?php echo htmlspecialchars($room['bed_type']); ?></span>
                                    <span><i class="icon-resize"></i> <?php echo htmlspecialchars($room['room_size']); ?></span>
                                </div>
                                <div class="room-price">
                                    <span class="price"><?php echo formatCurrency($room['price_per_night']); ?></span>
                                    <span class="per-night"><?php echo $language === 'vi' ? '/ đêm' : '/ night'; ?></span>
                                </div>
                                <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn"><?php echo $language === 'vi' ? 'Chi tiết' : 'View Details'; ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>