<?php
/**
 * Database Configuration File
 * 
 * This file contains the database connection settings and other configurations
 * for the Vinpearl Resort website.
 */

// Define website base URL (no trailing slash)
define('BASE_URL', 'http://localhost');

// Define default language
define('DEFAULT_LANGUAGE', 'en'); // 'en' for English, 'vi' for Vietnamese

// Define database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'vinpearl_resort');
define('DB_USER', 'root');         // Change this to your MySQL username
define('DB_PASS', '');             // Change this to your MySQL password

/**
 * Establish database connection
 * 
 * @return mysqli|false Returns a database connection or false on failure
 */
function connectDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    
    // Set charset to ensure proper character handling
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Get language-specific content from database
 * 
 * @param string $page    Page identifier
 * @param string $section Section identifier
 * @param string $key     Content key
 * @param string $lang    Language code ('en' or 'vi')
 * 
 * @return string|null Returns content string or null if not found
 */
function getContent($page, $section, $key, $lang = DEFAULT_LANGUAGE) {
    $conn = connectDatabase();
    if (!$conn) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT content_" . $lang . " FROM content 
                           WHERE page = ? AND section = ? AND key_name = ? LIMIT 1");
    $stmt->bind_param("sss", $page, $section, $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['content_' . $lang];
    }
    
    // If content not found, log it and return null
    error_log("Content not found: page=$page, section=$section, key=$key, lang=$lang");
    return null;
}

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $input Input string to sanitize
 * @return string Sanitized string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Set default timezone
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Vietnam timezone 