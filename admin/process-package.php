<?php
/**
 * Vinpearl Resort Nha Trang - Process Package Operations
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language and return URL
$language = isset($_REQUEST['lang']) && $_REQUEST['lang'] === 'vi' ? 'vi' : 'en';
$return_url = "packages.php" . ($language === 'vi' ? '?lang=vi' : '');

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Kết nối cơ sở dữ liệu thất bại' : 'Database connection failed'
    ];
    header('Location: ' . $return_url);
    exit;
}

// Get action type
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Process based on action type
switch ($action) {
    case 'create':
        createPackage($conn, $language, $return_url);
        break;
        
    case 'update':
        updatePackage($conn, $language, $return_url);
        break;
        
    case 'delete':
        deletePackage($conn, $language, $return_url);
        break;
        
    default:
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Thao tác không hợp lệ' : 'Invalid action'
        ];
        header('Location: ' . $return_url);
        exit;
}

/**
 * Create a new package
 */
function createPackage($conn, $language, $return_url) {
    // Validate required fields
    if (empty($_POST['name_en']) || empty($_POST['name_vi']) || empty($_POST['description_en']) || 
        empty($_POST['description_vi']) || empty($_POST['price'])) {
        
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin bắt buộc' : 'Please fill in all required fields'
        ];
        header('Location: package-form.php' . ($language === 'vi' ? '?lang=vi' : ''));
        exit;
    }
    
    // Get form data
    $name_en = $conn->real_escape_string($_POST['name_en']);
    $name_vi = $conn->real_escape_string($_POST['name_vi']);
    $description_en = $conn->real_escape_string($_POST['description_en']);
    $description_vi = $conn->real_escape_string($_POST['description_vi']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    $includes_en = $conn->real_escape_string($_POST['includes_en']);
    $includes_vi = $conn->real_escape_string($_POST['includes_vi']);
    $image_url = $conn->real_escape_string($_POST['image_url']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Insert into database
    $query = "INSERT INTO packages (name_en, name_vi, description_en, description_vi, category, price, duration, 
               includes_en, includes_vi, image_url, status, created_at, updated_at) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssssidsss", 
        $name_en, $name_vi, $description_en, $description_vi, 
        $category, $price, $duration, $includes_en, $includes_vi, $image_url, $status
    );
    
    if ($stmt->execute()) {
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Gói dịch vụ đã được thêm thành công' : 'Package added successfully'
        ];
    } else {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Lỗi khi thêm gói dịch vụ: ' . $stmt->error : 'Error adding package: ' . $stmt->error
        ];
    }
    
    $stmt->close();
    header('Location: ' . $return_url);
    exit;
}

/**
 * Update an existing package
 */
function updatePackage($conn, $language, $return_url) {
    // Validate package ID
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'ID gói dịch vụ không hợp lệ' : 'Invalid package ID'
        ];
        header('Location: ' . $return_url);
        exit;
    }
    
    $id = (int)$_POST['id'];
    
    // Validate required fields
    if (empty($_POST['name_en']) || empty($_POST['name_vi']) || empty($_POST['description_en']) || 
        empty($_POST['description_vi']) || empty($_POST['price'])) {
        
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin bắt buộc' : 'Please fill in all required fields'
        ];
        header('Location: package-form.php?id=' . $id . ($language === 'vi' ? '&lang=vi' : ''));
        exit;
    }
    
    // Get form data
    $name_en = $conn->real_escape_string($_POST['name_en']);
    $name_vi = $conn->real_escape_string($_POST['name_vi']);
    $description_en = $conn->real_escape_string($_POST['description_en']);
    $description_vi = $conn->real_escape_string($_POST['description_vi']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    $includes_en = $conn->real_escape_string($_POST['includes_en']);
    $includes_vi = $conn->real_escape_string($_POST['includes_vi']);
    $image_url = $conn->real_escape_string($_POST['image_url']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Update in database
    $query = "UPDATE packages SET 
              name_en = ?, name_vi = ?, description_en = ?, description_vi = ?, 
              category = ?, price = ?, duration = ?, includes_en = ?, includes_vi = ?, 
              image_url = ?, status = ?, updated_at = NOW() 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssssdissssi", 
        $name_en, $name_vi, $description_en, $description_vi, 
        $category, $price, $duration, $includes_en, $includes_vi, 
        $image_url, $status, $id
    );
    
    if ($stmt->execute()) {
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Gói dịch vụ đã được cập nhật thành công' : 'Package updated successfully'
        ];
    } else {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Lỗi khi cập nhật gói dịch vụ: ' . $stmt->error : 'Error updating package: ' . $stmt->error
        ];
    }
    
    $stmt->close();
    header('Location: ' . $return_url);
    exit;
}

/**
 * Delete a package
 */
function deletePackage($conn, $language, $return_url) {
    // Validate package ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'ID gói dịch vụ không hợp lệ' : 'Invalid package ID'
        ];
        header('Location: ' . $return_url);
        exit;
    }
    
    $id = (int)$_GET['id'];
    
    // Check if the package is currently booked
    $check_query = "SELECT COUNT(*) as count FROM package_bookings WHERE package_id = ? AND status != 'cancelled'";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' 
                ? 'Không thể xóa gói dịch vụ này vì có đơn đặt liên quan. Hãy đặt trạng thái thành "Không hoạt động" thay vì xóa.'
                : 'Cannot delete this package as it has related bookings. Set status to "Inactive" instead of deleting.'
        ];
        header('Location: ' . $return_url);
        exit;
    }
    
    // Delete the package
    $delete_query = "DELETE FROM packages WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Gói dịch vụ đã được xóa thành công' : 'Package deleted successfully'
        ];
    } else {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Lỗi khi xóa gói dịch vụ: ' . $stmt->error : 'Error deleting package: ' . $stmt->error
        ];
    }
    
    $stmt->close();
    header('Location: ' . $return_url);
    exit;
}