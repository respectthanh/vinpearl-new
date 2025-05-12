<?php
/**
 * Vinpearl Resort Nha Trang - Admin Package Form
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

// Check if we're editing an existing package
$is_edit = isset($_GET['id']) && is_numeric($_GET['id']);
$id = $is_edit ? (int)$_GET['id'] : 0;
$package = null;

// For editing, get the package data
if ($is_edit) {
    $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $package = $result->fetch_assoc();
    } else {
        // Redirect if package not found
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Gói dịch vụ không tồn tại.' : 'Package not found.'
        ];
        header('Location: packages.php' . ($language === 'vi' ? '?lang=vi' : ''));
        exit;
    }
    
    $stmt->close();
}

// Define empty package data for a new package
$default_package = [
    'name_en' => '',
    'name_vi' => '',
    'description_en' => '',
    'description_vi' => '',
    'category' => 'standard',
    'price' => 0,
    'duration' => 1,
    'includes_en' => '',
    'includes_vi' => '',
    'image_url' => '',
    'status' => 'active'
];

// Use the fetched data or defaults
$package_data = $is_edit ? $package : $default_package;

// Get all available categories
$categories_query = "SELECT DISTINCT category FROM packages ORDER BY category";
$categories_result = $conn->query($categories_query);
$categories = [];

if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
} else {
    // Default categories if none exist
    $categories = ['standard', 'premium', 'family', 'honeymoon', 'spa', 'adventure'];
}

// Page title
$pageTitle = $is_edit 
    ? ($language === 'vi' ? 'Chỉnh sửa gói dịch vụ' : 'Edit Package')
    : ($language === 'vi' ? 'Thêm gói dịch vụ mới' : 'Add New Package');
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
        
        .image-preview {
            margin-top: 10px;
            max-width: 300px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .image-preview img {
            width: 100%;
            height: auto;
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
                        <a href="packages.php"><?php echo $language === 'vi' ? 'Quản lý gói dịch vụ' : 'Packages Management'; ?></a> / 
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
                <form action="process-package.php" method="POST" class="admin-form" enctype="multipart/form-data">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="action" value="update">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>
                    
                    <?php if ($language === 'vi'): ?>
                        <input type="hidden" name="lang" value="vi">
                    <?php endif; ?>
                    
                    <!-- Package Details -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h3><?php echo $language === 'vi' ? 'Thông tin cơ bản' : 'Basic Information'; ?></h3>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="name_en"><?php echo $language === 'vi' ? 'Tên (Tiếng Anh)' : 'Name (English)'; ?> *</label>
                                    <input type="text" id="name_en" name="name_en" class="admin-form-input" value="<?php echo htmlspecialchars($package_data['name_en']); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="name_vi"><?php echo $language === 'vi' ? 'Tên (Tiếng Việt)' : 'Name (Vietnamese)'; ?> *</label>
                                    <input type="text" id="name_vi" name="name_vi" class="admin-form-input" value="<?php echo htmlspecialchars($package_data['name_vi']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="category"><?php echo $language === 'vi' ? 'Danh mục' : 'Category'; ?> *</label>
                                    <select id="category" name="category" class="admin-form-select" required>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $package_data['category'] === $category ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($category); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <!-- Category Preview Images -->
                                    <div class="category-preview">
                                        <?php foreach ($categories as $category): ?>
                                            <div class="category-image-item" data-category="<?php echo htmlspecialchars($category); ?>">
                                                <img src="../assets/images/packages/<?php echo $category; ?>.jpg" alt="<?php echo ucfirst($category); ?> Package">
                                                <div class="category-name"><?php echo ucfirst($category); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="status"><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?></label>
                                    <select id="status" name="status" class="admin-form-select">
                                        <option value="active" <?php echo $package_data['status'] === 'active' ? 'selected' : ''; ?>>
                                            <?php echo $language === 'vi' ? 'Đang hoạt động' : 'Active'; ?>
                                        </option>
                                        <option value="inactive" <?php echo $package_data['status'] === 'inactive' ? 'selected' : ''; ?>>
                                            <?php echo $language === 'vi' ? 'Không hoạt động' : 'Inactive'; ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="price"><?php echo $language === 'vi' ? 'Giá' : 'Price'; ?> *</label>
                                    <input type="number" id="price" name="price" class="admin-form-input" value="<?php echo htmlspecialchars($package_data['price']); ?>" min="0" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="duration"><?php echo $language === 'vi' ? 'Thời gian (ngày)' : 'Duration (days)'; ?> *</label>
                                    <input type="number" id="duration" name="duration" class="admin-form-input" value="<?php echo htmlspecialchars($package_data['duration']); ?>" min="1" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="image_url"><?php echo $language === 'vi' ? 'Hình ảnh URL' : 'Image URL'; ?></label>
                                    <input type="text" id="image_url" name="image_url" class="admin-form-input" value="<?php echo htmlspecialchars($package_data['image_url']); ?>" placeholder="https://...">
                                    
                                    <?php if (!empty($package_data['image_url'])): ?>
                                        <div class="image-preview">
                                            <img src="<?php echo htmlspecialchars($package_data['image_url']); ?>" alt="Package Image Preview">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Package Descriptions -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h3><?php echo $language === 'vi' ? 'Mô tả' : 'Descriptions'; ?></h3>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="description_en"><?php echo $language === 'vi' ? 'Mô tả (Tiếng Anh)' : 'Description (English)'; ?> *</label>
                                    <textarea id="description_en" name="description_en" class="admin-form-textarea" rows="5" required><?php echo htmlspecialchars($package_data['description_en']); ?></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="description_vi"><?php echo $language === 'vi' ? 'Mô tả (Tiếng Việt)' : 'Description (Vietnamese)'; ?> *</label>
                                    <textarea id="description_vi" name="description_vi" class="admin-form-textarea" rows="5" required><?php echo htmlspecialchars($package_data['description_vi']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="includes_en"><?php echo $language === 'vi' ? 'Chi tiết gói (Tiếng Anh)' : 'Package Includes (English)'; ?></label>
                                    <textarea id="includes_en" name="includes_en" class="admin-form-textarea" rows="5" placeholder="- Accommodation for X nights&#10;- Daily breakfast&#10;- Airport transfer&#10;- Spa treatment"><?php echo htmlspecialchars($package_data['includes_en']); ?></textarea>
                                    <small class="form-text text-muted"><?php echo $language === 'vi' ? 'Liệt kê mỗi mục trên một dòng, bắt đầu bằng dấu gạch ngang (-)' : 'List each item on a new line, starting with a hyphen (-)'; ?></small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="includes_vi"><?php echo $language === 'vi' ? 'Chi tiết gói (Tiếng Việt)' : 'Package Includes (Vietnamese)'; ?></label>
                                    <textarea id="includes_vi" name="includes_vi" class="admin-form-textarea" rows="5" placeholder="- Phòng nghỉ X đêm&#10;- Bữa sáng hàng ngày&#10;- Đưa đón sân bay&#10;- Dịch vụ spa"><?php echo htmlspecialchars($package_data['includes_vi']); ?></textarea>
                                    <small class="form-text text-muted"><?php echo $language === 'vi' ? 'Liệt kê mỗi mục trên một dòng, bắt đầu bằng dấu gạch ngang (-)' : 'List each item on a new line, starting with a hyphen (-)'; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-lg btn-primary">
                            <?php echo $is_edit 
                                ? ($language === 'vi' ? 'Cập nhật gói dịch vụ' : 'Update Package')
                                : ($language === 'vi' ? 'Thêm gói dịch vụ' : 'Add Package'); 
                            ?>
                        </button>
                        <a href="packages.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-lg btn-outline">
                            <?php echo $language === 'vi' ? 'Hủy bỏ' : 'Cancel'; ?>
                        </a>
                    </div>
                </form>
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
            
            // Image URL preview
            const imageUrlInput = document.getElementById('image_url');
            const imagePreviewContainer = document.querySelector('.image-preview') || document.createElement('div');
            
            if (!document.querySelector('.image-preview')) {
                imagePreviewContainer.className = 'image-preview';
                imageUrlInput.parentNode.appendChild(imagePreviewContainer);
            }
            
            imageUrlInput.addEventListener('input', function() {
                const imageUrl = this.value.trim();
                
                if (imageUrl) {
                    imagePreviewContainer.innerHTML = `<img src="${imageUrl}" alt="Package Image Preview">`;
                    imagePreviewContainer.style.display = 'block';
                } else {
                    imagePreviewContainer.style.display = 'none';
                }
            });
        });
    </script>
    <script src="assets/js/admin.js"></script>
</body>
</html>