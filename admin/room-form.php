<?php
/**
 * Vinpearl Resort Nha Trang - Admin Room Form
 * Add/Edit rooms
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    die("Database connection failed");
}

// Initialize variables
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$room = [];
$errors = [];

// Room type images as preferred by the user
$roomTypeImages = [
    'standard' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1000&auto=format&fit=crop',
    'deluxe' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1000&auto=format&fit=crop',
    'suite' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=1000&auto=format&fit=crop',
    'villa' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?q=80&w=1000&auto=format&fit=crop',
    'family' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?q=80&w=1000&auto=format&fit=crop'
];

// If editing, get existing room data
if ($is_edit) {
    $query = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Không tìm thấy phòng' : 'Room not found'
        ];
        
        header('Location: rooms.php' . ($language === 'vi' ? '?lang=vi' : ''));
        exit;
    }
    
    $room = $result->fetch_assoc();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name_en = trim($_POST['name_en']);
    $name_vi = trim($_POST['name_vi']);
    $description_en = trim($_POST['description_en']);
    $description_vi = trim($_POST['description_vi']);
    $type = $_POST['type'];
    $price_per_night = (float)$_POST['price_per_night'];
    $capacity = (int)$_POST['capacity'];
    $bed_count = (int)$_POST['bed_count'];
    $room_size = (int)$_POST['room_size'];
    // is_active column doesn't exist yet, so we'll comment this out
    // $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Use type-specific image
    $image_url = $roomTypeImages[$type] ?? $roomTypeImages['standard'];
    
    // Get amenities as JSON
    $amenities = [];
    if (isset($_POST['amenities']) && is_array($_POST['amenities'])) {
        $amenities = $_POST['amenities'];
    }
    $amenities_json = json_encode($amenities);
    
    // Validate required fields
    if (empty($name_en)) {
        $errors[] = $language === 'vi' ? 'Tên tiếng Anh là bắt buộc' : 'English name is required';
    }
    
    if (empty($name_vi)) {
        $errors[] = $language === 'vi' ? 'Tên tiếng Việt là bắt buộc' : 'Vietnamese name is required';
    }
    
    if (empty($description_en)) {
        $errors[] = $language === 'vi' ? 'Mô tả tiếng Anh là bắt buộc' : 'English description is required';
    }
    
    if (empty($description_vi)) {
        $errors[] = $language === 'vi' ? 'Mô tả tiếng Việt là bắt buộc' : 'Vietnamese description is required';
    }
    
    if ($price_per_night <= 0) {
        $errors[] = $language === 'vi' ? 'Giá phải lớn hơn 0' : 'Price must be greater than 0';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        if ($is_edit) {
            // Update existing room
            $query = "
                UPDATE rooms SET
                    name_en = ?,
                    name_vi = ?,
                    description_en = ?,
                    description_vi = ?,
                    type = ?,
                    image_url = ?,
                    price_per_night = ?,
                    capacity = ?,
                    bed_count = ?,
                    room_size = ?,
                    amenities = ?,
                    updated_at = NOW()
                WHERE id = ?
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                'ssssssdiiisi',
                $name_en,
                $name_vi,
                $description_en,
                $description_vi,
                $type,
                $image_url,
                $price_per_night,
                $capacity,
                $bed_count,
                $room_size,
                $amenities_json,
                $id
            );
        } else {
            // Create new room
            $query = "
                INSERT INTO rooms (
                    name_en, name_vi, description_en, description_vi, type,
                    image_url, price_per_night, capacity, bed_count, 
                    room_size, amenities, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                'ssssssdiiis',
                $name_en,
                $name_vi,
                $description_en,
                $description_vi,
                $type,
                $image_url,
                $price_per_night,
                $capacity,
                $bed_count,
                $room_size,
                $amenities_json
            );
        }
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' 
                    ? ($is_edit ? 'Đã cập nhật phòng thành công' : 'Đã thêm phòng mới thành công')
                    : ($is_edit ? 'Room updated successfully' : 'Room added successfully')
            ];
            
            header('Location: rooms.php' . ($language === 'vi' ? '?lang=vi' : ''));
            exit;
        } else {
            $errors[] = $language === 'vi' ? 'Có lỗi xảy ra khi lưu dữ liệu' : 'An error occurred while saving data';
        }
    }
}

// Room types
$roomTypes = [
    'standard' => $language === 'vi' ? 'Phòng Tiêu Chuẩn' : 'Standard Room',
    'deluxe' => $language === 'vi' ? 'Phòng Deluxe' : 'Deluxe Room',
    'suite' => $language === 'vi' ? 'Phòng Suite' : 'Suite',
    'villa' => $language === 'vi' ? 'Biệt Thự' : 'Villa',
    'family' => $language === 'vi' ? 'Phòng Gia Đình' : 'Family Room'
];

// Common amenities
$commonAmenities = [
    'wifi' => $language === 'vi' ? 'Wi-Fi miễn phí' : 'Free Wi-Fi',
    'tv' => $language === 'vi' ? 'TV màn hình phẳng' : 'Flat-screen TV',
    'ac' => $language === 'vi' ? 'Điều hòa' : 'Air conditioning',
    'minibar' => $language === 'vi' ? 'Minibar' : 'Minibar',
    'safe' => $language === 'vi' ? 'Két an toàn' : 'In-room safe',
    'coffee' => $language === 'vi' ? 'Máy pha cà phê' : 'Coffee machine',
    'balcony' => $language === 'vi' ? 'Ban công' : 'Balcony',
    'ocean_view' => $language === 'vi' ? 'Hướng biển' : 'Ocean view',
    'bathtub' => $language === 'vi' ? 'Bồn tắm' : 'Bathtub',
    'shower' => $language === 'vi' ? 'Vòi sen' : 'Shower',
    'toiletries' => $language === 'vi' ? 'Đồ dùng nhà tắm' : 'Toiletries',
    'hairdryer' => $language === 'vi' ? 'Máy sấy tóc' : 'Hair dryer',
    'desk' => $language === 'vi' ? 'Bàn làm việc' : 'Work desk',
    'breakfast' => $language === 'vi' ? 'Bữa sáng' : 'Breakfast included',
    'roomservice' => $language === 'vi' ? 'Dịch vụ phòng' : 'Room service'
];

// Get room amenities
$roomAmenities = [];
if (isset($room['amenities'])) {
    $roomAmenities = json_decode($room['amenities'], true) ?: [];
}

// Page title
$pageTitle = $language === 'vi' 
    ? ($is_edit ? 'Chỉnh sửa phòng' : 'Thêm phòng mới')
    : ($is_edit ? 'Edit Room' : 'Add New Room');
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .room-type-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .room-image-item {
            width: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .room-image-item.selected {
            border-color: var(--admin-primary);
            transform: scale(1.05);
        }
        
        .room-image-item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
        }
        
        .room-image-item .room-type-name {
            padding: 5px;
            font-size: 0.8rem;
            text-align: center;
            background: rgba(0,0,0,0.05);
        }
        
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .amenity-checkbox {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .amenity-checkbox:hover {
            background-color: rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="admin-page">
    <div class="admin-layout">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <a href="index.php">
                    <img src="../assets/images/logo.png" alt="Vinpearl Resort Nha Trang">
                    <span><?php echo $language === 'vi' ? 'Quản trị' : 'Admin'; ?></span>
                </a>
            </div>
            
            <nav class="admin-nav">
                <ul>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span><?php echo $language === 'vi' ? 'Bảng điều khiển' : 'Dashboard'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="content.php" class="active">
                            <i class="fas fa-file-alt"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý nội dung' : 'Content Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php">
                            <i class="fas fa-calendar-check"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý đặt phòng' : 'Bookings Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="reviews.php">
                            <i class="fas fa-star"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý đánh giá' : 'Reviews Management'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="nearby.php">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $language === 'vi' ? 'Địa điểm gần đó' : 'Nearby Places'; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            <span><?php echo $language === 'vi' ? 'Quản lý người dùng' : 'Users Management'; ?></span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <div class="admin-content">
            <!-- Admin Header -->
            <header class="admin-header">
                <div class="admin-header-title">
                    <h1><?php echo $pageTitle; ?></h1>
                    <nav class="admin-breadcrumb">
                        <a href="content.php"><?php echo $language === 'vi' ? 'Quản lý nội dung' : 'Content Management'; ?></a> / 
                        <a href="rooms.php"><?php echo $language === 'vi' ? 'Quản lý phòng' : 'Rooms Management'; ?></a> / 
                        <span><?php echo $pageTitle; ?></span>
                    </nav>
                </div>
                
                <div class="admin-user">
                    <div class="language-selector">
                        <a href="?<?php echo $is_edit ? 'id=' . $id . '&' : ''; ?>lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                        <a href="?<?php echo $is_edit ? 'id=' . $id . '&' : ''; ?>lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
                    </div>
                    
                    <div class="admin-user-name">
                        <?php 
                        $currentUser = getCurrentUser();
                        echo htmlspecialchars($currentUser['full_name']); 
                        ?>
                    </div>
                    
                    <a href="../logout.php" class="btn btn-sm"><?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                </div>
            </header>
            
            <main class="admin-main">
                <!-- Back to rooms link -->
                <div class="mb-3">
                    <a href="rooms.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> <?php echo $language === 'vi' ? 'Quay lại danh sách' : 'Back to list'; ?>
                    </a>
                </div>
                
                <!-- Form errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h4><?php echo $language === 'vi' ? 'Lỗi:' : 'Errors:'; ?></h4>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Room Form -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><?php echo $pageTitle; ?></h2>
                    </div>
                    
                    <div class="admin-card-body">
                        <form action="room-form.php<?php echo $is_edit ? '?id=' . $id : ''; ?><?php echo $language === 'vi' ? ($is_edit ? '&lang=vi' : '?lang=vi') : ''; ?>" method="POST" class="admin-form">
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="name_en" class="form-label"><?php echo $language === 'vi' ? 'Tên phòng tiếng Anh' : 'English Room Name'; ?> *</label>
                                        <input type="text" id="name_en" name="name_en" class="form-control" value="<?php echo isset($room['name_en']) ? htmlspecialchars($room['name_en']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="name_vi" class="form-label"><?php echo $language === 'vi' ? 'Tên phòng tiếng Việt' : 'Vietnamese Room Name'; ?> *</label>
                                        <input type="text" id="name_vi" name="name_vi" class="form-control" value="<?php echo isset($room['name_vi']) ? htmlspecialchars($room['name_vi']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="description_en" class="form-label"><?php echo $language === 'vi' ? 'Mô tả tiếng Anh' : 'English Description'; ?> *</label>
                                        <textarea id="description_en" name="description_en" class="form-control" rows="4" required><?php echo isset($room['description_en']) ? htmlspecialchars($room['description_en']) : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="description_vi" class="form-label"><?php echo $language === 'vi' ? 'Mô tả tiếng Việt' : 'Vietnamese Description'; ?> *</label>
                                        <textarea id="description_vi" name="description_vi" class="form-control" rows="4" required><?php echo isset($room['description_vi']) ? htmlspecialchars($room['description_vi']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="type" class="form-label"><?php echo $language === 'vi' ? 'Loại phòng' : 'Room Type'; ?> *</label>
                                        <select id="type" name="type" class="form-control" required>
                                            <option value="" disabled <?php echo empty($room['type']) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Chọn loại phòng' : 'Select room type'; ?></option>
                                            <?php foreach ($roomTypes as $key => $label): ?>
                                                <option value="<?php echo $key; ?>" <?php echo (isset($room['type']) && $room['type'] === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="price_per_night" class="form-label"><?php echo $language === 'vi' ? 'Giá mỗi đêm (USD)' : 'Price per night (USD)'; ?> *</label>
                                        <input type="number" id="price_per_night" name="price_per_night" class="form-control" step="0.01" min="0.01" value="<?php echo isset($room['price_per_night']) ? $room['price_per_night'] : '0.00'; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><?php echo $language === 'vi' ? 'Hình ảnh theo loại phòng' : 'Room Type Image'; ?></label>
                                <p class="form-text"><?php echo $language === 'vi' ? 'Hình ảnh sẽ được chọn tự động dựa trên loại phòng' : 'The image will be automatically selected based on the room type'; ?></p>
                                
                                <div class="room-type-preview">
                                    <?php foreach ($roomTypeImages as $type => $img): ?>
                                        <div class="room-image-item <?php echo (isset($room['type']) && $room['type'] === $type) ? 'selected' : ''; ?>" data-type="<?php echo $type; ?>">
                                            <img src="<?php echo $img; ?>" alt="<?php echo $roomTypes[$type]; ?>">
                                            <div class="room-type-name"><?php echo $roomTypes[$type]; ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="capacity" class="form-label"><?php echo $language === 'vi' ? 'Sức chứa (người)' : 'Capacity (persons)'; ?> *</label>
                                        <input type="number" id="capacity" name="capacity" class="form-control" min="1" value="<?php echo isset($room['capacity']) ? $room['capacity'] : '2'; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="bed_count" class="form-label"><?php echo $language === 'vi' ? 'Số giường' : 'Number of beds'; ?></label>
                                        <input type="number" id="bed_count" name="bed_count" class="form-control" min="1" value="<?php echo isset($room['bed_count']) ? $room['bed_count'] : '1'; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="room_size" class="form-label"><?php echo $language === 'vi' ? 'Diện tích (m²)' : 'Room size (m²)'; ?></label>
                                        <input type="number" id="room_size" name="room_size" class="form-control" min="1" value="<?php echo isset($room['room_size']) ? $room['room_size'] : '25'; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><?php echo $language === 'vi' ? 'Tiện nghi' : 'Amenities'; ?></label>
                                <div class="amenities-grid">
                                    <?php foreach ($commonAmenities as $key => $label): ?>
                                        <label class="amenity-checkbox">
                                            <input type="checkbox" name="amenities[]" value="<?php echo $key; ?>" <?php echo in_array($key, $roomAmenities) ? 'checked' : ''; ?>>
                                            <?php echo $label; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- is_active checkbox removed since the column doesn't exist yet -->
                            <!--<div class="form-group">
                                <label class="form-label">
                                    <input type="checkbox" name="is_active" checked>
                                    <?php echo $language === 'vi' ? 'Hiển thị phòng này' : 'Show this room'; ?>
                                </label>
                            </div>-->
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?php echo $language === 'vi' ? 'Lưu phòng' : 'Save Room'; ?>
                                </button>
                                
                                <a href="rooms.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
                                    <?php echo $language === 'vi' ? 'Hủy' : 'Cancel'; ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update room type image selection when type changes
            const typeSelect = document.getElementById('type');
            const roomImages = document.querySelectorAll('.room-image-item');
            
            function updateSelectedImage() {
                const selectedType = typeSelect.value;
                
                roomImages.forEach(img => {
                    if (img.getAttribute('data-type') === selectedType) {
                        img.classList.add('selected');
                    } else {
                        img.classList.remove('selected');
                    }
                });
            }
            
            typeSelect.addEventListener('change', updateSelectedImage);
            
            // Allow clicking on room images to select the type
            roomImages.forEach(img => {
                img.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    typeSelect.value = type;
                    updateSelectedImage();
                });
            });
            
            // Initialize
            updateSelectedImage();
        });
    </script>
    <script src="assets/js/admin.js"></script>
</body>
</html>
