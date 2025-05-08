<?php
/**
 * Vinpearl Resort Nha Trang - Admin Nearby Place Form
 * Add/Edit nearby places
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
$place = [];
$errors = [];

// Category-specific images as preferred by the user
$categoryImages = [
    'attraction' => 'https://images.unsplash.com/photo-1564661408674-27a170407be5?q=80&w=1000&auto=format&fit=crop',
    'restaurant' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1000&auto=format&fit=crop',
    'shopping' => 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?q=80&w=1000&auto=format&fit=crop',
    'beach' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=1000&auto=format&fit=crop',
    'nature' => 'https://images.unsplash.com/photo-1540206351-d6465b3ac5c1?q=80&w=1000&auto=format&fit=crop',
    'entertainment' => 'https://images.unsplash.com/photo-1603190287605-e6ade32fa852?q=80&w=1000&auto=format&fit=crop'
];

// If editing, get existing place data
if ($is_edit) {
    $query = "SELECT * FROM nearby_places WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Không tìm thấy địa điểm' : 'Place not found'
        ];
        
        header('Location: nearby.php' . ($language === 'vi' ? '?lang=vi' : ''));
        exit;
    }
    
    $place = $result->fetch_assoc();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name_en = trim($_POST['name_en']);
    $name_vi = trim($_POST['name_vi']);
    $description_en = trim($_POST['description_en']);
    $description_vi = trim($_POST['description_vi']);
    $address = trim($_POST['address']);
    $category = $_POST['category'];
    $distance = (float)$_POST['distance'];
    $latitude = (float)$_POST['latitude'];
    $longitude = (float)$_POST['longitude'];
    // is_active column doesn't exist yet, so we'll comment this out
    // $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Use category-specific image from our preferred set
    $image_url = $categoryImages[$category];
    
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
    
    if (empty($address)) {
        $errors[] = $language === 'vi' ? 'Địa chỉ là bắt buộc' : 'Address is required';
    }
    
    if ($distance <= 0) {
        $errors[] = $language === 'vi' ? 'Khoảng cách phải lớn hơn 0' : 'Distance must be greater than 0';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        if ($is_edit) {
            // Update existing place
            $query = "
                UPDATE nearby_places SET
                    name_en = ?,
                    name_vi = ?,
                    description_en = ?,
                    description_vi = ?,
                    address = ?,
                    category = ?,
                    image_url = ?,
                    distance = ?,
                    latitude = ?,
                    longitude = ?,
                    updated_at = NOW()
                WHERE id = ?
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                'sssssssdddi',
                $name_en,
                $name_vi,
                $description_en,
                $description_vi,
                $address,
                $category,
                $image_url,
                $distance,
                $latitude,
                $longitude,
                $id
            );
        } else {
            // Create new place
            $query = "
                INSERT INTO nearby_places (
                    name_en, name_vi, description_en, description_vi,
                    address, category, image_url, distance,
                    latitude, longitude, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                'sssssssddd',
                $name_en,
                $name_vi,
                $description_en,
                $description_vi,
                $address,
                $category,
                $image_url,
                $distance,
                $latitude,
                $longitude
            );
        }
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' 
                    ? ($is_edit ? 'Đã cập nhật địa điểm thành công' : 'Đã thêm địa điểm mới thành công')
                    : ($is_edit ? 'Place updated successfully' : 'Place added successfully')
            ];
            
            header('Location: nearby.php' . ($language === 'vi' ? '?lang=vi' : ''));
            exit;
        } else {
            $errors[] = $language === 'vi' ? 'Có lỗi xảy ra khi lưu dữ liệu' : 'An error occurred while saving data';
        }
    }
}

// Category labels
$categoryLabels = [
    'attraction' => $language === 'vi' ? 'Điểm tham quan' : 'Attraction',
    'restaurant' => $language === 'vi' ? 'Nhà hàng' : 'Restaurant',
    'shopping' => $language === 'vi' ? 'Mua sắm' : 'Shopping',
    'beach' => $language === 'vi' ? 'Bãi biển' : 'Beach',
    'nature' => $language === 'vi' ? 'Thiên nhiên' : 'Nature',
    'entertainment' => $language === 'vi' ? 'Giải trí' : 'Entertainment'
];

// Page title
$pageTitle = $language === 'vi' 
    ? ($is_edit ? 'Chỉnh sửa địa điểm' : 'Thêm địa điểm mới')
    : ($is_edit ? 'Edit Place' : 'Add New Place');
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
        .category-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .category-image-item {
            width: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .category-image-item.selected {
            border-color: var(--admin-primary);
            transform: scale(1.05);
        }
        
        .category-image-item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
        }
        
        .category-image-item .category-name {
            padding: 5px;
            font-size: 0.8rem;
            text-align: center;
            background: rgba(0,0,0,0.05);
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
                        <a href="content.php">
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
                        <a href="nearby.php" class="active">
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
                <!-- Back to nearby places link -->
                <div class="mb-3">
                    <a href="nearby.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
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
                
                <!-- Place Form -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><?php echo $pageTitle; ?></h2>
                    </div>
                    
                    <div class="admin-card-body">
                        <form action="nearby-form.php<?php echo $is_edit ? '?id=' . $id : ''; ?><?php echo $language === 'vi' ? ($is_edit ? '&lang=vi' : '?lang=vi') : ''; ?>" method="POST" class="admin-form">
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="name_en" class="form-label"><?php echo $language === 'vi' ? 'Tên tiếng Anh' : 'English Name'; ?> *</label>
                                        <input type="text" id="name_en" name="name_en" class="form-control" value="<?php echo isset($place['name_en']) ? htmlspecialchars($place['name_en']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="name_vi" class="form-label"><?php echo $language === 'vi' ? 'Tên tiếng Việt' : 'Vietnamese Name'; ?> *</label>
                                        <input type="text" id="name_vi" name="name_vi" class="form-control" value="<?php echo isset($place['name_vi']) ? htmlspecialchars($place['name_vi']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="description_en" class="form-label"><?php echo $language === 'vi' ? 'Mô tả tiếng Anh' : 'English Description'; ?> *</label>
                                        <textarea id="description_en" name="description_en" class="form-control" rows="4" required><?php echo isset($place['description_en']) ? htmlspecialchars($place['description_en']) : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="description_vi" class="form-label"><?php echo $language === 'vi' ? 'Mô tả tiếng Việt' : 'Vietnamese Description'; ?> *</label>
                                        <textarea id="description_vi" name="description_vi" class="form-control" rows="4" required><?php echo isset($place['description_vi']) ? htmlspecialchars($place['description_vi']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address" class="form-label"><?php echo $language === 'vi' ? 'Địa chỉ' : 'Address'; ?> *</label>
                                <input type="text" id="address" name="address" class="form-control" value="<?php echo isset($place['address']) ? htmlspecialchars($place['address']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="category" class="form-label"><?php echo $language === 'vi' ? 'Loại địa điểm' : 'Category'; ?> *</label>
                                        <select id="category" name="category" class="form-control" required>
                                            <option value="" disabled <?php echo empty($place['category']) ? 'selected' : ''; ?>><?php echo $language === 'vi' ? 'Chọn loại' : 'Select category'; ?></option>
                                            <?php foreach ($categoryLabels as $key => $label): ?>
                                                <option value="<?php echo $key; ?>" <?php echo (isset($place['category']) && $place['category'] === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="distance" class="form-label"><?php echo $language === 'vi' ? 'Khoảng cách (km)' : 'Distance (km)'; ?> *</label>
                                        <input type="number" id="distance" name="distance" class="form-control" step="0.1" min="0.1" value="<?php echo isset($place['distance']) ? $place['distance'] : '1.0'; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><?php echo $language === 'vi' ? 'Hình ảnh theo loại' : 'Category Image'; ?></label>
                                <p class="form-text"><?php echo $language === 'vi' ? 'Hình ảnh sẽ được chọn tự động dựa trên loại địa điểm' : 'The image will be automatically selected based on the place category'; ?></p>
                                
                                <div class="category-preview">
                                    <?php foreach ($categoryImages as $cat => $img): ?>
                                        <div class="category-image-item <?php echo (isset($place['category']) && $place['category'] === $cat) ? 'selected' : ''; ?>" data-category="<?php echo $cat; ?>">
                                            <img src="<?php echo $img; ?>" alt="<?php echo $categoryLabels[$cat]; ?>">
                                            <div class="category-name"><?php echo $categoryLabels[$cat]; ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="latitude" class="form-label"><?php echo $language === 'vi' ? 'Vĩ độ' : 'Latitude'; ?></label>
                                        <input type="number" id="latitude" name="latitude" class="form-control" step="0.000001" value="<?php echo isset($place['latitude']) ? $place['latitude'] : '12.235422'; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="longitude" class="form-label"><?php echo $language === 'vi' ? 'Kinh độ' : 'Longitude'; ?></label>
                                        <input type="number" id="longitude" name="longitude" class="form-control" step="0.000001" value="<?php echo isset($place['longitude']) ? $place['longitude'] : '109.196684'; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- is_active checkbox removed since the column doesn't exist yet -->
                            <!--<div class="form-group">
                                <label class="form-label">
                                    <input type="checkbox" name="is_active" checked>
                                    <?php echo $language === 'vi' ? 'Hiển thị địa điểm này' : 'Show this place'; ?>
                                </label>
                            </div>-->
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?php echo $language === 'vi' ? 'Lưu địa điểm' : 'Save Place'; ?>
                                </button>
                                
                                <a href="nearby.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-outline">
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
            // Update category image selection when category changes
            const categorySelect = document.getElementById('category');
            const categoryImages = document.querySelectorAll('.category-image-item');
            
            function updateSelectedImage() {
                const selectedCategory = categorySelect.value;
                
                categoryImages.forEach(img => {
                    if (img.getAttribute('data-category') === selectedCategory) {
                        img.classList.add('selected');
                    } else {
                        img.classList.remove('selected');
                    }
                });
            }
            
            categorySelect.addEventListener('change', updateSelectedImage);
            
            // Allow clicking on category images to select the category
            categoryImages.forEach(img => {
                img.addEventListener('click', function() {
                    const category = this.getAttribute('data-category');
                    categorySelect.value = category;
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
