<?php
/**
 * Vinpearl Resort Nha Trang - Cancel Booking Handler
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require login to access this page
requireLogin();

// Initialize response
$response = [
    'success' => false,
    'message' => 'Unknown error occurred'
];

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $booking_type = isset($_POST['booking_type']) ? $_POST['booking_type'] : '';
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    
    // Validate booking type
    $valid_types = ['room', 'package', 'tour'];
    if (!in_array($booking_type, $valid_types) || $booking_id <= 0) {
        $response['message'] = 'Invalid booking information';
    } else {
        // Get current user
        $currentUser = getCurrentUser();
        
        // Attempt to cancel the booking
        $result = cancelBooking($booking_type, $booking_id, $currentUser['id']);
        
        if ($result === true) {
            $response['success'] = true;
            $response['message'] = 'Booking cancelled successfully';
        } else {
            $response['message'] = $result; // Error message from the function
        }
    }
}

// Determine language for redirects
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';
$lang_param = $language === 'vi' ? '?lang=vi' : '';

// If this is an AJAX request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Otherwise redirect to the bookings page with a message
$redirect_url = 'bookings.php' . $lang_param;
if ($response['success']) {
    $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'message=cancel_success';
} else {
    $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'message=cancel_error';
}

header('Location: ' . $redirect_url);
exit;

/**
 * Cancel a booking
 * 
 * @param string $booking_type The type of booking (room, package, tour)
 * @param int $booking_id The ID of the booking
 * @param int $user_id The ID of the user making the request
 * @return bool|string True on success, error message on failure
 */
function cancelBooking($booking_type, $booking_id, $user_id) {
    $conn = connectDatabase();
    if (!$conn) {
        return 'Database connection failed';
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Verify user owns the booking
        $table_name = '';
        $id_field = '';
        
        switch ($booking_type) {
            case 'room':
                $table_name = 'room_bookings';
                $id_field = 'room_booking_id';
                break;
            case 'package':
                $table_name = 'package_bookings';
                $id_field = 'package_booking_id';
                break;
            case 'tour':
                $table_name = 'tour_bookings';
                $id_field = 'tour_booking_id';
                break;
            default:
                throw new Exception('Invalid booking type');
        }
        
        // Verify user ownership
        $stmt = $conn->prepare("SELECT status FROM $table_name WHERE $id_field = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Booking not found or unauthorized');
        }
        
        $booking = $result->fetch_assoc();
        
        // Check if booking is in a state that can be cancelled
        if ($booking['status'] !== 'pending' && $booking['status'] !== 'confirmed') {
            throw new Exception('Booking cannot be cancelled in its current state');
        }
        
        // Update booking status
        $stmt = $conn->prepare("UPDATE $table_name SET status = 'cancelled' WHERE $id_field = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('Failed to update booking status');
        }
        
        // Add cancellation record (in a real app, you might want to track more details)
        $stmt = $conn->prepare("INSERT INTO cancellations (user_id, booking_type, booking_id, reason, cancelled_at) 
                              VALUES (?, ?, ?, 'User cancelled booking', NOW())");
        $stmt->bind_param("isi", $user_id, $booking_type, $booking_id);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        return true;
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        return $e->getMessage();
    }
} 