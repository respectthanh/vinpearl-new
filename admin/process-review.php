<?php
/**
 * Vinpearl Resort Nha Trang - Admin Process Review
 * Handles review approval, unapproval, and deletion
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Check required parameters
if (!isset($_GET['action']) || !isset($_GET['id'])) {
    // Set error message
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Thiếu thông tin cần thiết' : 'Missing required information'
    ];
    
    // Redirect to reviews page
    header('Location: reviews.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Get parameters
$action = $_GET['action'];
$id = (int)$_GET['id'];

// Allowed actions
$allowed_actions = ['approve', 'unapprove', 'delete'];
if (!in_array($action, $allowed_actions)) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Hành động không hợp lệ' : 'Invalid action'
    ];
    
    header('Location: reviews.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không thể kết nối đến cơ sở dữ liệu' : 'Could not connect to database'
    ];
    
    header('Location: reviews.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Check if review exists
$check_query = "SELECT * FROM reviews WHERE id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không tìm thấy đánh giá' : 'Review not found'
    ];
    
    header('Location: reviews.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

$review = $result->fetch_assoc();

// Process the action
switch ($action) {
    case 'approve':
        $update_query = "UPDATE reviews SET is_approved = 1, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' ? 'Đã duyệt đánh giá thành công' : 'Review approved successfully'
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Không thể duyệt đánh giá' : 'Could not approve review'
            ];
        }
        break;
        
    case 'unapprove':
        $update_query = "UPDATE reviews SET is_approved = 0, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' ? 'Đã hủy duyệt đánh giá thành công' : 'Review unapproved successfully'
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Không thể hủy duyệt đánh giá' : 'Could not unapprove review'
            ];
        }
        break;
        
    case 'delete':
        $delete_query = "DELETE FROM reviews WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' ? 'Đã xóa đánh giá thành công' : 'Review deleted successfully'
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Không thể xóa đánh giá' : 'Could not delete review'
            ];
        }
        break;
}

// Redirect back to reviews page
header('Location: reviews.php' . ($language === 'vi' ? '?lang=vi' : ''));
exit;
