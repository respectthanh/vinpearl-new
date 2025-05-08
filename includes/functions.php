<?php
/**
 * Utility Functions
 * 
 * This file contains helper functions used throughout the website.
 */

require_once 'config.php';

/**
 * Format currency values
 * 
 * @param float  $amount   Amount to format
 * @param string $currency Currency code (default: USD)
 * 
 * @return string Formatted currency string
 */
function formatCurrency($amount, $currency = 'USD') {
    $symbols = [
        'USD' => '$',
        'VND' => '₫'
    ];
    
    $symbol = $symbols[$currency] ?? '';
    
    if ($currency === 'VND') {
        // Format VND with dot separators and no decimals
        return $symbol . number_format($amount, 0, ',', '.');
    } else {
        // Format other currencies with 2 decimal places
        return $symbol . number_format($amount, 2, '.', ',');
    }
}

/**
 * Format date in the preferred format
 * 
 * @param string $date   Date string
 * @param string $format PHP date format (default: Y-m-d)
 * 
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

/**
 * Get all rooms with optional filtering
 * 
 * @param array $filters Array of filter options
 * @param string $lang   Language code ('en' or 'vi')
 * 
 * @return array Array of room data
 */
function getRooms($filters = [], $lang = DEFAULT_LANGUAGE) {
    $conn = connectDatabase();
    if (!$conn) {
        return [];
    }
    
    $sql = "SELECT r.*, 
           (SELECT image_url FROM room_images WHERE room_id = r.id LIMIT 1) as additional_image 
           FROM rooms r WHERE is_available = 1";
    
    // Add filters if provided
    $params = [];
    $types = "";
    
    if (!empty($filters['capacity'])) {
        $sql .= " AND capacity >= ?";
        $params[] = $filters['capacity'];
        $types .= "i";
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND price_per_night <= ?";
        $params[] = $filters['max_price'];
        $types .= "d";
    }
    
    $sql .= " ORDER BY price_per_night ASC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $rooms = [];
    
    while ($room = $result->fetch_assoc()) {
        // Get room images
        $imagesStmt = $conn->prepare("SELECT image_url FROM room_images WHERE room_id = ?");
        $imagesStmt->bind_param("i", $room['id']);
        $imagesStmt->execute();
        $imagesResult = $imagesStmt->get_result();
        
        $images = [];
        while ($image = $imagesResult->fetch_assoc()) {
            $images[] = $image['image_url'];
        }
        
        $room['images'] = $images;
        $room['amenities'] = json_decode($room['amenities'], true);
        $rooms[] = $room;
    }
    
    return $rooms;
}

/**
 * Get all packages
 * 
 * @param string $lang Language code ('en' or 'vi')
 * 
 * @return array Array of package data
 */
function getPackages($lang = DEFAULT_LANGUAGE) {
    $conn = connectDatabase();
    if (!$conn) {
        return [];
    }
    
    $stmt = $conn->prepare("SELECT * FROM packages ORDER BY price ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $packages = [];
    
    while ($package = $result->fetch_assoc()) {
        $packages[] = $package;
    }
    
    return $packages;
}

/**
 * Get all tours
 * 
 * @param string $lang Language code ('en' or 'vi')
 * 
 * @return array Array of tour data
 */
function getTours($lang = DEFAULT_LANGUAGE) {
    $conn = connectDatabase();
    if (!$conn) {
        return [];
    }
    
    $stmt = $conn->prepare("SELECT * FROM tours ORDER BY price_per_person ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $tours = [];
    
    while ($tour = $result->fetch_assoc()) {
        $tours[] = $tour;
    }
    
    return $tours;
}

/**
 * Get tour by ID
 * 
 * @param int    $tourId Tour ID
 * @param string $lang   Language code ('en' or 'vi')
 * 
 * @return array|boolean Tour data if found, false otherwise
 */
function getTourById($tourId, $lang = DEFAULT_LANGUAGE) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->bind_param("i", $tourId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    return $result->fetch_assoc();
}

/**
 * Create a tour booking
 * 
 * @param array $bookingData Booking data
 * 
 * @return int|boolean Booking ID if successful, false if failed
 */
function createTourBooking($bookingData) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    $stmt = $conn->prepare("INSERT INTO tour_bookings (user_id, tour_id, tour_date, guests, total_price, status) 
                          VALUES (?, ?, ?, ?, ?, 'pending')");
    
    $stmt->bind_param(
        "iisid",
        $bookingData['user_id'],
        $bookingData['tour_id'],
        $bookingData['tour_date'],
        $bookingData['guests'],
        $bookingData['total_price']
    );
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    } else {
        return false;
    }
}

/**
 * Check tour availability for the given date
 *
 * @param int    $tourId    Tour ID
 * @param string $tourDate  Tour date (Y-m-d format)
 * @param int    $guestCount Number of guests to check
 *
 * @return boolean True if tour is available, false otherwise
 */
function isTourAvailable($tourId, $tourDate, $guestCount = 1) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    // First, check if the tour exists and get max capacity
    $tourStmt = $conn->prepare("SELECT id, max_people FROM tours WHERE id = ?");
    $tourStmt->bind_param("i", $tourId);
    $tourStmt->execute();
    $tourResult = $tourStmt->get_result();
    
    if ($tourResult->num_rows === 0) {
        return false;
    }
    
    $tourData = $tourResult->fetch_assoc();
    $maxPeople = $tourData['max_people'];
    
    // Next, check how many guests are already booked for this date
    $bookingStmt = $conn->prepare("SELECT SUM(guests) AS total_guests 
                                  FROM tour_bookings 
                                  WHERE tour_id = ? 
                                  AND tour_date = ? 
                                  AND status != 'cancelled'");
    $bookingStmt->bind_param("is", $tourId, $tourDate);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();
    $bookingData = $bookingResult->fetch_assoc();
    
    $currentGuests = $bookingData['total_guests'] ?? 0;
    
    // Check if adding the new guests would exceed max capacity
    return ($currentGuests + $guestCount <= $maxPeople);
}

/**
 * Get nearby places
 * 
 * @param string $category Category filter (optional)
 * @param string $lang     Language code ('en' or 'vi')
 * 
 * @return array Array of nearby places
 */
function getNearbyPlaces($category = null, $lang = DEFAULT_LANGUAGE) {
    $conn = connectDatabase();
    if (!$conn) {
        return [];
    }
    
    $sql = "SELECT * FROM nearby_places";
    $params = [];
    $types = "";
    
    if ($category) {
        $sql .= " WHERE category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    $sql .= " ORDER BY distance_km ASC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $places = [];
    
    while ($place = $result->fetch_assoc()) {
        $places[] = $place;
    }
    
    return $places;
}

/**
 * Get approved reviews
 * 
 * @param string $type   Type of review (room, package, tour)
 * @param int    $itemId ID of the item being reviewed
 * @param string $lang   Language code ('en' or 'vi')
 * 
 * @return array Array of reviews
 */
function getReviews($type, $itemId, $lang = DEFAULT_LANGUAGE) {
    $conn = connectDatabase();
    if (!$conn) {
        return [];
    }
    
    $stmt = $conn->prepare("SELECT r.*, u.full_name as user_name 
                           FROM reviews r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.type = ? AND r.item_id = ? AND r.is_approved = 1 AND r.is_hidden = 0
                           ORDER BY r.created_at DESC");
    $stmt->bind_param("si", $type, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    
    while ($review = $result->fetch_assoc()) {
        $reviews[] = $review;
    }
    
    return $reviews;
}

/**
 * Get active promotional banners
 * 
 * @return array Array of promotional banners
 */
function getPromotionalBanners() {
    $conn = connectDatabase();
    if (!$conn) {
        return [];
    }
    
    $currentDate = date('Y-m-d');
    
    $stmt = $conn->prepare("SELECT * FROM promotional_banners 
                           WHERE is_active = 1 
                           AND (start_date IS NULL OR start_date <= ?) 
                           AND (end_date IS NULL OR end_date >= ?)
                           ORDER BY position ASC");
    $stmt->bind_param("ss", $currentDate, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $banners = [];
    
    while ($banner = $result->fetch_assoc()) {
        $banners[] = $banner;
    }
    
    return $banners;
}

/**
 * Check if a room is available for the given dates
 * 
 * @param int    $roomId      Room ID
 * @param string $checkInDate Check-in date (Y-m-d format)
 * @param string $checkOutDate Check-out date (Y-m-d format)
 * 
 * @return boolean True if room is available, false otherwise
 */
function isRoomAvailable($roomId, $checkInDate, $checkOutDate) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    // Check if room exists and is available
    $roomStmt = $conn->prepare("SELECT is_available FROM rooms WHERE id = ?");
    $roomStmt->bind_param("i", $roomId);
    $roomStmt->execute();
    $roomResult = $roomStmt->get_result();
    
    if ($roomResult->num_rows === 0 || !$roomResult->fetch_assoc()['is_available']) {
        return false;
    }
    
    // Check for overlapping bookings
    $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM room_bookings 
                          WHERE room_id = ? AND status != 'cancelled'
                          AND ((check_in_date <= ? AND check_out_date > ?) 
                          OR (check_in_date < ? AND check_out_date >= ?)
                          OR (check_in_date >= ? AND check_in_date < ?))");
    
    $stmt->bind_param("issssss", $roomId, $checkOutDate, $checkInDate, $checkOutDate, $checkInDate, $checkInDate, $checkOutDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['booking_count'] === 0;
}

/**
 * Create a room booking
 * 
 * @param array $bookingData Booking data
 * 
 * @return int|boolean Booking ID if successful, false if failed
 */
function createRoomBooking($bookingData) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    // Check room availability first
    if (!isRoomAvailable($bookingData['room_id'], $bookingData['check_in_date'], $bookingData['check_out_date'])) {
        return false;
    }
    
    $stmt = $conn->prepare("INSERT INTO room_bookings (user_id, room_id, check_in_date, check_out_date, guests, total_price, status) 
                          VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    
    $stmt->bind_param(
        "iissid",
        $bookingData['user_id'],
        $bookingData['room_id'],
        $bookingData['check_in_date'],
        $bookingData['check_out_date'],
        $bookingData['guests'],
        $bookingData['total_price']
    );
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    } else {
        return false;
    }
}

/**
 * Generate a page title
 * 
 * @param string $title    Page-specific title
 * @param string $language Language code
 * 
 * @return string Full page title
 */
function generatePageTitle($title, $language = DEFAULT_LANGUAGE) {
    $siteName = $language === 'vi' ? 'Vinpearl Resort Nha Trang' : 'Vinpearl Resort Nha Trang';
    
    if (empty($title)) {
        return $siteName;
    }
    
    return $title . ' - ' . $siteName;
}

/**
 * Calculate booking duration in days
 * 
 * @param string $checkInDate  Check-in date
 * @param string $checkOutDate Check-out date
 * 
 * @return int Number of days
 */
function calculateBookingDuration($checkInDate, $checkOutDate) {
    $checkIn = new DateTime($checkInDate);
    $checkOut = new DateTime($checkOutDate);
    $interval = $checkIn->diff($checkOut);
    
    return $interval->days;
}