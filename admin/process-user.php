<?php
/**
 * Vinpearl Resort Nha Trang - Admin Process User
 * Handles user activation, deactivation, and deletion
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
    
    // Redirect to users page
    header('Location: users.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Get parameters
$action = $_GET['action'];
$id = (int)$_GET['id'];

// Allowed actions
$allowed_actions = ['activate', 'deactivate', 'delete'];
if (!in_array($action, $allowed_actions)) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Hành động không hợp lệ' : 'Invalid action'
    ];
    
    header('Location: users.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không thể kết nối đến cơ sở dữ liệu' : 'Could not connect to database'
    ];
    
    header('Location: users.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Check if user exists and is not current user
$current_user = getCurrentUser();

// Cannot modify yourself
if ($id === $current_user['id']) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không thể thay đổi trạng thái tài khoản của chính bạn' : 'You cannot change your own account status'
    ];
    
    header('Location: users.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Check if user exists
$check_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không tìm thấy người dùng' : 'User not found'
    ];
    
    header('Location: users.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

$user = $result->fetch_assoc();

// Process the action
switch ($action) {
    case 'activate':
        $update_query = "UPDATE users SET is_active = 1, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' ? 'Đã kích hoạt người dùng thành công' : 'User activated successfully'
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Không thể kích hoạt người dùng' : 'Could not activate user'
            ];
        }
        break;
        
    case 'deactivate':
        $update_query = "UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' ? 'Đã vô hiệu hóa người dùng thành công' : 'User deactivated successfully'
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Không thể vô hiệu hóa người dùng' : 'Could not deactivate user'
            ];
        }
        break;
        
    case 'delete':
        // Check if user has any bookings, reviews, etc.
        $has_bookings_query = "
            SELECT 
                (SELECT COUNT(*) FROM room_bookings WHERE user_id = ?) +
                (SELECT COUNT(*) FROM package_bookings WHERE user_id = ?) +
                (SELECT COUNT(*) FROM tour_bookings WHERE user_id = ?) as total_bookings
        ";
        $stmt = $conn->prepare($has_bookings_query);
        $stmt->bind_param('iii', $id, $id, $id);
        $stmt->execute();
        $bookings_result = $stmt->get_result();
        $bookings_count = $bookings_result->fetch_assoc()['total_bookings'];
        
        $has_reviews_query = "SELECT COUNT(*) as total_reviews FROM reviews WHERE user_id = ?";
        $stmt = $conn->prepare($has_reviews_query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $reviews_result = $stmt->get_result();
        $reviews_count = $reviews_result->fetch_assoc()['total_reviews'];
        
        if ($bookings_count > 0 || $reviews_count > 0) {
            // User has associated data, cannot delete
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' 
                    ? 'Không thể xóa người dùng vì họ có liên kết với dữ liệu (đặt chỗ, đánh giá, ...). Hãy vô hiệu hóa tài khoản thay vì xóa.'
                    : 'Cannot delete user because they have associated data (bookings, reviews, ...). Please deactivate the account instead.'
            ];
        } else {
            // User can be safely deleted
            $delete_query = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                $_SESSION['admin_message'] = [
                    'type' => 'success',
                    'message' => $language === 'vi' ? 'Đã xóa người dùng thành công' : 'User deleted successfully'
                ];
            } else {
                $_SESSION['admin_message'] = [
                    'type' => 'error',
                    'message' => $language === 'vi' ? 'Không thể xóa người dùng' : 'Could not delete user'
                ];
            }
        }
        break;
}

// Redirect back to users page
header('Location: users.php' . ($language === 'vi' ? '?lang=vi' : ''));
exit;
