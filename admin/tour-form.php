<?php
/**
 * Vinpearl Resort Nha Trang - Admin Tour Form
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

// Check if we're editing an existing tour
$is_edit = isset($_GET['id']) && is_numeric($_GET['id']);
$id = $is_edit ? (int)$_GET['id'] : 0;
$tour = null;

// For editing, get the tour data
if ($is_edit) {
    $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tour = $result->fetch_assoc();
    } else {
        // Redirect if tour not found
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Tour không tồn tại.' : 'Tour not found.'
        ];
        header('Location: tours.php' . ($language === 'vi' ? '?lang=vi' : ''));
        exit;
    }
    
    $stmt->close();
}

// Define empty tour data for a new tour
$default_tour = [
    'name_en' => '',
    'name_vi' => '',
    'description_en' => '',
    'description_vi' => '',
    'type' => 'city',
    'price' => 0,
    'duration' => 4,
    'includes_en' => '',
    'includes_vi' => '',
    'itinerary_en' => '',
    'itinerary_vi' => '',
    'location' => '',
    'max_participants' => 10,
    'image_url' => '',
    'status' => 'active'
];

// Use the fetched data or defaults
$tour_data = $is_edit ? $tour : $default_tour;

// Define hardcoded tour types instead of querying a non-existent column
$tour_types = ['city', 'beach', 'cultural', 'adventure', 'nature', 'boat', 'food'];

// Page title
$pageTitle = $is_edit 
    ? ($language === 'vi' ? 'Chỉnh sửa tour' : 'Edit Tour')
    : ($language === 'vi' ? 'Thêm tour mới' : 'Add New Tour');
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
        .type-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .type-image-item {
            width: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .type-image-item.selected {
            border-color: var(--admin-primary);
            transform: scale(1.05);
        }
        
        .type-image-item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
        }
        
        .type-image-item .type-name {
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
                        <a href="tours.php"><?php echo $language === 'vi' ? 'Quản lý tour' : 'Tours Management'; ?></a> / 
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
                <form action="process-tour.php" method="POST" class="admin-form" enctype="multipart/form-data">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="action" value="update">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>
                    
                    <?php if ($language === 'vi'): ?>
                        <input type="hidden" name="lang" value="vi">
                    <?php endif; ?>
                    
                    <!-- Tour Details -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h3><?php echo $language === 'vi' ? 'Thông tin cơ bản' : 'Basic Information'; ?></h3>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="name_en"><?php echo $language === 'vi' ? 'Tên (Tiếng Anh)' : 'Name (English)'; ?> *</label>
                                    <input type="text" id="name_en" name="name_en" class="admin-form-input" value="<?php echo htmlspecialchars($tour_data['name_en']); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="name_vi"><?php echo $language === 'vi' ? 'Tên (Tiếng Việt)' : 'Name (Vietnamese)'; ?> *</label>
                                    <input type="text" id="name_vi" name="name_vi" class="admin-form-input" value="<?php echo htmlspecialchars($tour_data['name_vi']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="type"><?php echo $language === 'vi' ? 'Loại tour' : 'Tour Type'; ?> *</label>
                                    <select id="type" name="type" class="admin-form-select" required>
                                        <?php foreach ($tour_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $tour_data['type'] === $type ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <!-- Tour Type Preview Images -->
                                    <div class="type-preview">
                                        <?php foreach ($tour_types as $type): ?>
                                            <div class="type-image-item" data-type="<?php echo htmlspecialchars($type); ?>">
                                                <img src="../assets/images/tours/<?php echo $type; ?>.jpg" alt="<?php echo ucfirst($type); ?> Tour">
                                                <div class="type-name"><?php echo ucfirst($type); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="status"><?php echo $language === 'vi' ? 'Trạng thái' : 'Status'; ?></label>
                                    <select id="status" name="status" class="admin-form-select">
                                        <option value="active" <?php echo $tour_data['status'] === 'active' ? 'selected' : ''; ?>>
                                            <?php echo $language === 'vi' ? 'Đang hoạt động' : 'Active'; ?>
                                        </option>
                                        <option value="inactive" <?php echo $tour_data['status'] === 'inactive' ? 'selected' : ''; ?>>
                                            <?php echo $language === 'vi' ? 'Không hoạt động' : 'Inactive'; ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="price"><?php echo $language === 'vi' ? 'Giá' : 'Price'; ?> *</label>
                                    <input type="number" id="price" name="price" class="admin-form-input" value="<?php echo htmlspecialchars($tour_data['price']); ?>" min="0" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="duration"><?php echo $language === 'vi' ? 'Thời gian (giờ)' : 'Duration (hours)'; ?> *</label>
                                    <input type="number" id="duration" name="duration" class="admin-form-input" value="<?php echo htmlspecialchars($tour_data['duration']); ?>" min="1" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="max_participants"><?php echo $language === 'vi' ? 'Số người tối đa' : 'Max Participants'; ?> *</label>
                                    <input type="number" id="max_participants" name="max_participants" class="admin-form-input" value="<?php echo htmlspecialchars($tour_data['max_participants']); ?>" min="1" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="location"><?php echo $language === 'vi' ? 'Vị trí' : 'Location'; ?> *</label>
                                    <input type="text" id="location" name="location" class="admin-form-input" value="<?php echo htmlspecialchars($tour_data['location']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="image_url"><?php echo $language === 'vi' ? 'Hình ảnh URL' : 'Image URL'; ?></label>
                                    <input type="text" id="image_url" name="image_url" class="admin-form-input" value="<?php echo htmlspecialchars($tour_data['image_url']); ?>" placeholder="https://...">
                                    
                                    <?php if (!empty($tour_data['image_url'])): ?>
                                        <div class="image-preview">
                                            <img src="<?php echo htmlspecialchars($tour_data['image_url']); ?>" alt="Tour Image Preview">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tour Descriptions -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h3><?php echo $language === 'vi' ? 'Mô tả' : 'Descriptions'; ?></h3>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="description_en"><?php echo $language === 'vi' ? 'Mô tả (Tiếng Anh)' : 'Description (English)'; ?> *</label>
                                    <textarea id="description_en" name="description_en" class="admin-form-textarea" rows="5" required><?php echo htmlspecialchars($tour_data['description_en']); ?></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="description_vi"><?php echo $language === 'vi' ? 'Mô tả (Tiếng Việt)' : 'Description (Vietnamese)'; ?> *</label>
                                    <textarea id="description_vi" name="description_vi" class="admin-form-textarea" rows="5" required><?php echo htmlspecialchars($tour_data['description_vi']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="includes_en"><?php echo $language === 'vi' ? 'Bao gồm (Tiếng Anh)' : 'Includes (English)'; ?></label>
                                    <textarea id="includes_en" name="includes_en" class="admin-form-textarea" rows="5" placeholder="- Transportation&#10;- English-speaking guide&#10;- Entrance fees&#10;- Lunch"><?php echo htmlspecialchars($tour_data['includes_en']); ?></textarea>
                                    <small class="form-text text-muted"><?php echo $language === 'vi' ? 'Liệt kê mỗi mục trên một dòng, bắt đầu bằng dấu gạch ngang (-)' : 'List each item on a new line, starting with a hyphen (-)'; ?></small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="includes_vi"><?php echo $language === 'vi' ? 'Bao gồm (Tiếng Việt)' : 'Includes (Vietnamese)'; ?></label>
                                    <textarea id="includes_vi" name="includes_vi" class="admin-form-textarea" rows="5" placeholder="- Phương tiện đưa đón&#10;- Hướng dẫn viên nói tiếng Anh&#10;- Phí vào cửa&#10;- Bữa trưa"><?php echo htmlspecialchars($tour_data['includes_vi']); ?></textarea>
                                    <small class="form-text text-muted"><?php echo $language === 'vi' ? 'Liệt kê mỗi mục trên một dòng, bắt đầu bằng dấu gạch ngang (-)' : 'List each item on a new line, starting with a hyphen (-)'; ?></small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="itinerary_en"><?php echo $language === 'vi' ? 'Lịch trình (Tiếng Anh)' : 'Itinerary (English)'; ?></label>
                                    <textarea id="itinerary_en" name="itinerary_en" class="admin-form-textarea" rows="5" placeholder="8:00 AM: Hotel pickup&#10;9:00 AM: Visit first attraction&#10;12:00 PM: Lunch&#10;2:00 PM: Visit second attraction&#10;5:00 PM: Return to hotel"><?php echo htmlspecialchars($tour_data['itinerary_en']); ?></textarea>
                                    <small class="form-text text-muted"><?php echo $language === 'vi' ? 'Liệt kê mỗi điểm dừng trên một dòng' : 'List each stop on a new line'; ?></small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="itinerary_vi"><?php echo $language === 'vi' ? 'Lịch trình (Tiếng Việt)' : 'Itinerary (Vietnamese)'; ?></label>
                                    <textarea id="itinerary_vi" name="itinerary_vi" class="admin-form-textarea" rows="5" placeholder="8:00: Đón tại khách sạn&#10;9:00: Tham quan điểm đến đầu tiên&#10;12:00: Ăn trưa&#10;14:00: Tham quan điểm đến thứ hai&#10;17:00: Trở về khách sạn"><?php echo htmlspecialchars($tour_data['itinerary_vi']); ?></textarea>
                                    <small class="form-text text-muted"><?php echo $language === 'vi' ? 'Liệt kê mỗi điểm dừng trên một dòng' : 'List each stop on a new line'; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-lg btn-primary">
                            <?php echo $is_edit 
                                ? ($language === 'vi' ? 'Cập nhật tour' : 'Update Tour')
                                : ($language === 'vi' ? 'Thêm tour' : 'Add Tour'); 
                            ?>
                        </button>
                        <a href="tours.php<?php echo $language === 'vi' ? '?lang=vi' : ''; ?>" class="btn btn-lg btn-outline">
                            <?php echo $language === 'vi' ? 'Hủy bỏ' : 'Cancel'; ?>
                        </a>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update type image selection when type changes
            const typeSelect = document.getElementById('type');
            const typeImages = document.querySelectorAll('.type-image-item');
            
            function updateSelectedImage() {
                const selectedType = typeSelect.value;
                
                typeImages.forEach(img => {
                    if (img.getAttribute('data-type') === selectedType) {
                        img.classList.add('selected');
                    } else {
                        img.classList.remove('selected');
                    }
                });
            }
            
            typeSelect.addEventListener('change', updateSelectedImage);
            
            // Allow clicking on type images to select the type
            typeImages.forEach(img => {
                img.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    typeSelect.value = type;
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
                    imagePreviewContainer.innerHTML = `<img src="${imageUrl}" alt="Tour Image Preview">`;
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