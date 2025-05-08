<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Get current user
$currentUser = getCurrentUser();

// Get and decode JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['type']) || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$type = $input['type'];
$id = $input['id'];

// Connect to database
$conn = connectDatabase();

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Prepare query based on booking type
switch ($type) {
    case 'room':
        $table = 'room_bookings';
        $id_field = 'room_booking_id';
        break;
    case 'package':
        $table = 'package_bookings';
        $id_field = 'package_booking_id';
        break;
    case 'tour':
        $table = 'tour_bookings';
        $id_field = 'tour_booking_id';
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid booking type'
        ]);
        exit;
}

// Check if booking exists and belongs to current user
$query = "SELECT * FROM $table WHERE $id_field = ? AND user_id = ? AND (status = 'pending' OR status = 'confirmed')";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $id, $currentUser['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Booking not found or cannot be cancelled'
    ]);
    exit;
}

// Update booking status to cancelled
$update_query = "UPDATE $table SET status = 'cancelled', updated_at = NOW() WHERE $id_field = ? AND user_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param('ii', $id, $currentUser['id']);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to cancel booking'
    ]);
}

$conn->close();
