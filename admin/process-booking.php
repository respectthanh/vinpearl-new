<?php
/**
 * Vinpearl Resort Nha Trang - Admin Process Booking
 * Handles booking confirmation, cancellation and completion
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Check required parameters
if (!isset($_GET['action']) || !isset($_GET['type']) || !isset($_GET['id'])) {
    // Set error message
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Thiếu thông tin cần thiết' : 'Missing required information'
    ];
    
    // Redirect to bookings page
    header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Get parameters
$action = $_GET['action'];
$type = $_GET['type'];
$id = (int)$_GET['id'];

// Allowed actions
$allowed_actions = ['confirm', 'cancel', 'complete'];
if (!in_array($action, $allowed_actions)) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Hành động không hợp lệ' : 'Invalid action'
    ];
    
    header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không thể kết nối đến cơ sở dữ liệu' : 'Could not connect to database'
    ];
    
    header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

// Determine table and id field based on booking type
switch ($type) {
    case 'room':
        $table = 'room_bookings';
        $id_field = 'id';
        break;
    case 'package':
        $table = 'package_bookings';
        $id_field = 'id';
        break;
    case 'tour':
        $table = 'tour_bookings';
        $id_field = 'id';
        break;
    default:
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Loại đặt chỗ không hợp lệ' : 'Invalid booking type'
        ];
        
        header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
        exit;
}

// Check if booking exists
$check_query = "SELECT * FROM $table WHERE $id_field = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Không tìm thấy đặt chỗ' : 'Booking not found'
    ];
    
    header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
    exit;
}

$booking = $result->fetch_assoc();

// Determine status based on action
$new_status = '';
switch ($action) {
    case 'confirm':
        $new_status = 'confirmed';
        break;
    case 'cancel':
        $new_status = 'cancelled';
        break;
    case 'complete':
        $new_status = 'completed';
        break;
}

// Update booking status
$update_query = "UPDATE $table SET status = ? WHERE $id_field = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param('si', $new_status, $id);

if ($stmt->execute()) {
    // Success message
    $action_text = '';
    switch ($action) {
        case 'confirm':
            $action_text = $language === 'vi' ? 'xác nhận' : 'confirmed';
            break;
        case 'cancel':
            $action_text = $language === 'vi' ? 'hủy' : 'cancelled';
            break;
        case 'complete':
            $action_text = $language === 'vi' ? 'hoàn thành' : 'completed';
            break;
    }
    
    $_SESSION['admin_message'] = [
        'type' => 'success',
        'message' => $language === 'vi' 
            ? "Đã $action_text đặt chỗ thành công" 
            : "Booking successfully $action_text"
    ];
    
    // Send notification email
    $user_query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param('i', $booking['user_id']);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        
        // Email subject
        $subject = '';
        switch ($action) {
            case 'confirm':
                $subject = $language === 'vi' ? 'Đặt chỗ của bạn đã được xác nhận' : 'Your booking has been confirmed';
                break;
            case 'cancel':
                $subject = $language === 'vi' ? 'Đặt chỗ của bạn đã bị hủy' : 'Your booking has been cancelled';
                break;
            case 'complete':
                $subject = $language === 'vi' ? 'Đặt chỗ của bạn đã hoàn thành' : 'Your booking has been completed';
                break;
        }
        
        // Email message
        $message = "Dear " . $user['full_name'] . ",\n\n";
        $message .= $language === 'vi' 
            ? "Đặt chỗ của bạn đã được $action_text.\n\n" 
            : "Your booking has been $action_text.\n\n";
        $message .= "Booking ID: " . $id . "\n";
        $message .= "Booking Type: " . ucfirst($type) . "\n";
        $message .= "Status: " . ucfirst($new_status) . "\n\n";
        $message .= $language === 'vi' 
            ? "Trân trọng,\nVinpearl Resort Nha Trang" 
            : "Best regards,\nVinpearl Resort Nha Trang";
        
        // Set mail headers
        $headers = "From: noreply@vinpearl.com\r\n";
        $headers .= "Reply-To: support@vinpearl.com\r\n";
        
        // Send email
        // mail($user['email'], $subject, $message, $headers); // Uncomment in production
    }
} else {
    // Error message
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' 
            ? 'Không thể cập nhật trạng thái đặt chỗ' 
            : 'Could not update booking status'
    ];
}

// Redirect back to bookings page
header('Location: bookings.php' . ($language === 'vi' ? '?lang=vi' : ''));
exit;
