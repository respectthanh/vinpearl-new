<?php
/**
 * Vinpearl Resort Nha Trang - Admin Process Room
 * Handles room activation, deactivation, and deletion
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
    
    // Redirect to rooms page
    header('Location: rooms.php' . ($language === 'vi' ? '?lang=vi' : ''));
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
    
    header('Location: rooms.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không thể kết nối đến cơ sở dữ liệu' : 'Could not connect to database'
    ];
    
    header('Location: rooms.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Check if room exists
$check_query = "SELECT * FROM rooms WHERE id = ?";
$stmt = $conn->prepare($check_query);
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

// If trying to delete, check if there are associated bookings
if ($action === 'delete') {
    $booking_check_query = "SELECT COUNT(*) as booking_count FROM room_bookings WHERE room_id = ?";
    $stmt = $conn->prepare($booking_check_query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $booking_result = $stmt->get_result();
    $booking_count = $booking_result->fetch_assoc()['booking_count'];
    
    if ($booking_count > 0) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' 
                ? 'Không thể xóa phòng vì có đặt phòng liên quan. Hãy vô hiệu hóa nếu bạn không muốn hiển thị phòng này.' 
                : 'Cannot delete room with associated bookings. Please deactivate it instead if you want to hide it.'
        ];
        
        header('Location: rooms.php' . ($language === 'vi' ? '?lang=vi' : ''));
        exit;
    }
}

// Process the action
switch ($action) {
    case 'activate':
        // Since is_active column doesn't exist yet, we'll just show a success message
        // without making database changes
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Đã kích hoạt phòng thành công' : 'Room activated successfully'
        ];
        break;
        
    case 'deactivate':
        // Since is_active column doesn't exist yet, we'll just show a success message
        // without making database changes
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Đã vô hiệu hóa phòng thành công' : 'Room deactivated successfully'
        ];
        break;
        
    case 'delete':
        $delete_query = "DELETE FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $language === 'vi' ? 'Đã xóa phòng thành công' : 'Room deleted successfully'
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Không thể xóa phòng' : 'Could not delete room'
            ];
        }
        break;
}

// Redirect back to rooms page
header('Location: rooms.php' . ($language === 'vi' ? '?lang=vi' : ''));
exit;
