<?php
/**
 * Vinpearl Resort Nha Trang - Admin Tour Processing
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login to access this page
requireAdmin();

// Determine language
$language = isset($_POST['lang']) || isset($_GET['lang']) ? 'vi' : 'en';
$redirect_base = 'tours.php' . ($language === 'vi' ? '?lang=vi' : '');

// Connect to database
$conn = connectDatabase();
if (!$conn) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => $language === 'vi' ? 'Kết nối cơ sở dữ liệu thất bại.' : 'Database connection failed.'
    ];
    header('Location: ' . $redirect_base);
    exit;
}

// Get action from POST or GET
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// Process based on action
switch ($action) {
    case 'create':
        createTour($conn, $language);
        break;
    
    case 'update':
        updateTour($conn, $language);
        break;
    
    case 'delete':
        deleteTour($conn, $language);
        break;
    
    default:
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Hành động không hợp lệ.' : 'Invalid action.'
        ];
        header('Location: ' . $redirect_base);
        exit;
}

/**
 * Create a new tour
 */
function createTour($conn, $language) {
    // Validate required fields
    $required_fields = ['name_en', 'name_vi', 'description_en', 'description_vi', 'type', 'price', 'duration', 'max_participants', 'location'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin bắt buộc.' : 'Please fill in all required fields.'
            ];
            header('Location: tour-form.php' . ($language === 'vi' ? '?lang=vi' : ''));
            exit;
        }
    }
    
    // Prepare data for insertion
    $name_en = $conn->real_escape_string($_POST['name_en']);
    $name_vi = $conn->real_escape_string($_POST['name_vi']);
    $description_en = $conn->real_escape_string($_POST['description_en']);
    $description_vi = $conn->real_escape_string($_POST['description_vi']);
    $type = $conn->real_escape_string($_POST['type']);
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration'];
    $includes_en = isset($_POST['includes_en']) ? $conn->real_escape_string($_POST['includes_en']) : '';
    $includes_vi = isset($_POST['includes_vi']) ? $conn->real_escape_string($_POST['includes_vi']) : '';
    $itinerary_en = isset($_POST['itinerary_en']) ? $conn->real_escape_string($_POST['itinerary_en']) : '';
    $itinerary_vi = isset($_POST['itinerary_vi']) ? $conn->real_escape_string($_POST['itinerary_vi']) : '';
    $location = $conn->real_escape_string($_POST['location']);
    $max_participants = (int)$_POST['max_participants'];
    $image_url = isset($_POST['image_url']) ? $conn->real_escape_string($_POST['image_url']) : '';
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'active';
    
    // Insert into database
    $query = "INSERT INTO tours (
        name_en, name_vi, description_en, description_vi, type, price, duration,
        includes_en, includes_vi, itinerary_en, itinerary_vi, location, max_participants,
        image_url, status, created_at, updated_at
    ) VALUES (
        '$name_en', '$name_vi', '$description_en', '$description_vi', '$type', $price, $duration,
        '$includes_en', '$includes_vi', '$itinerary_en', '$itinerary_vi', '$location', $max_participants,
        '$image_url', '$status', NOW(), NOW()
    )";
    
    if ($conn->query($query)) {
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Thêm tour mới thành công.' : 'Tour added successfully.'
        ];
        header('Location: ' . $redirect_base);
    } else {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Đã xảy ra lỗi: ' . $conn->error : 'An error occurred: ' . $conn->error
        ];
        header('Location: tour-form.php' . ($language === 'vi' ? '?lang=vi' : ''));
    }
    
    exit;
}

/**
 * Update an existing tour
 */
function updateTour($conn, $language) {
    // Check if ID is provided
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'ID tour không hợp lệ.' : 'Invalid tour ID.'
        ];
        header('Location: ' . $redirect_base);
        exit;
    }
    
    $tour_id = (int)$_POST['id'];
    
    // Validate required fields
    $required_fields = ['name_en', 'name_vi', 'description_en', 'description_vi', 'type', 'price', 'duration', 'max_participants', 'location'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => $language === 'vi' ? 'Vui lòng điền đầy đủ thông tin bắt buộc.' : 'Please fill in all required fields.'
            ];
            header('Location: tour-form.php?id=' . $tour_id . ($language === 'vi' ? '&lang=vi' : ''));
            exit;
        }
    }
    
    // Prepare data for updating
    $name_en = $conn->real_escape_string($_POST['name_en']);
    $name_vi = $conn->real_escape_string($_POST['name_vi']);
    $description_en = $conn->real_escape_string($_POST['description_en']);
    $description_vi = $conn->real_escape_string($_POST['description_vi']);
    $type = $conn->real_escape_string($_POST['type']);
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration'];
    $includes_en = isset($_POST['includes_en']) ? $conn->real_escape_string($_POST['includes_en']) : '';
    $includes_vi = isset($_POST['includes_vi']) ? $conn->real_escape_string($_POST['includes_vi']) : '';
    $itinerary_en = isset($_POST['itinerary_en']) ? $conn->real_escape_string($_POST['itinerary_en']) : '';
    $itinerary_vi = isset($_POST['itinerary_vi']) ? $conn->real_escape_string($_POST['itinerary_vi']) : '';
    $location = $conn->real_escape_string($_POST['location']);
    $max_participants = (int)$_POST['max_participants'];
    $image_url = isset($_POST['image_url']) ? $conn->real_escape_string($_POST['image_url']) : '';
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'active';
    
    // Update database
    $query = "UPDATE tours SET
        name_en = '$name_en',
        name_vi = '$name_vi',
        description_en = '$description_en',
        description_vi = '$description_vi',
        type = '$type',
        price = $price,
        duration = $duration,
        includes_en = '$includes_en',
        includes_vi = '$includes_vi',
        itinerary_en = '$itinerary_en',
        itinerary_vi = '$itinerary_vi',
        location = '$location',
        max_participants = $max_participants,
        image_url = '$image_url',
        status = '$status',
        updated_at = NOW()
    WHERE id = $tour_id";
    
    if ($conn->query($query)) {
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Cập nhật tour thành công.' : 'Tour updated successfully.'
        ];
        header('Location: ' . $redirect_base);
    } else {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Đã xảy ra lỗi: ' . $conn->error : 'An error occurred: ' . $conn->error
        ];
        header('Location: tour-form.php?id=' . $tour_id . ($language === 'vi' ? '&lang=vi' : ''));
    }
    
    exit;
}

/**
 * Delete a tour
 */
function deleteTour($conn, $language) {
    // Check if ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'ID tour không hợp lệ.' : 'Invalid tour ID.'
        ];
        header('Location: ' . $redirect_base);
        exit;
    }
    
    $tour_id = (int)$_GET['id'];
    
    // Check if tour exists and can be deleted
    $check_query = "SELECT COUNT(*) as count FROM tour_bookings WHERE tour_id = $tour_id";
    $result = $conn->query($check_query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        // Tour has bookings, so we shouldn't delete it
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Không thể xóa tour này vì đã có người đặt.' : 'Cannot delete this tour as it has bookings.'
        ];
        header('Location: ' . $redirect_base);
        exit;
    }
    
    // Delete tour
    $query = "DELETE FROM tours WHERE id = $tour_id";
    
    if ($conn->query($query)) {
        $_SESSION['admin_message'] = [
            'type' => 'success',
            'message' => $language === 'vi' ? 'Xóa tour thành công.' : 'Tour deleted successfully.'
        ];
    } else {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => $language === 'vi' ? 'Đã xảy ra lỗi: ' . $conn->error : 'An error occurred: ' . $conn->error
        ];
    }
    
    header('Location: ' . $redirect_base);
    exit;
}

// Close database connection
$conn->close();
?>