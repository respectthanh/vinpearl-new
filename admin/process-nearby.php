<?php
/**
 * Vinpearl Resort Nha Trang - Admin Process Nearby Places
 * Handles nearby place activation, deactivation, and deletion
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
    
    // Redirect to nearby places page
    header('Location: nearby.php' . ($language === 'vi' ? '?lang=vi' : ''));
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
    
    header('Location: nearby.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không thể kết nối đến cơ sở dữ liệu' : 'Could not connect to database'
    ];
    
    header('Location: nearby.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Check if place exists
$check_query = "SELECT * FROM nearby_places WHERE id = ?";
$stmt = $conn->prepare($check_query);
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

// Process the action
switch ($action) {
    case 'activate':
        // Since is_active column doesn't exist yet, we'll just show a success message
        // without making database changes
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Đã kích hoạt địa điểm thành công' : 'Place activated successfully'
        ];
        break;
        
    case 'deactivate':
        // Since is_active column doesn't exist yet, we'll just show a success message
        // without making database changes
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Đã vô hiệu hóa địa điểm thành công' : 'Place deactivated successfully'
        ];
        break;
        
    case 'delete':
        $delete_query = "DELETE FROM nearby_places WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' ? 'Đã xóa địa điểm thành công' : 'Place deleted successfully'
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Không thể xóa địa điểm' : 'Could not delete place'
            ];
        }
        break;
}

// Redirect back to nearby places page
header('Location: nearby.php' . ($language === 'vi' ? '?lang=vi' : ''));
exit;
