<?php
/**
 * Authentication System
 * 
 * This file contains functions for user authentication, registration,
 * and session management.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

/**
 * Check if a user is logged in
 * 
 * @return boolean True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin
 * 
 * @return boolean True if user is an admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Authenticate a user with email and password
 * 
 * @param string $email    User's email
 * @param string $password User's password (plain text)
 * 
 * @return boolean True if authentication successful, false otherwise
 */
function login($email, $password) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, email, password_hash, full_name, is_admin FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Verify password (using MD5 for demonstration - use better methods in production!)
        if (md5($password) === $user['password_hash']) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            
            // Update last login time
            $updateStmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            return true;
        }
    }
    
    return false;
}

/**
 * Register a new user
 * 
 * @param string $email    User's email
 * @param string $password User's password (plain text)
 * @param string $fullName User's full name
 * @param string $phone    User's phone number (optional)
 * 
 * @return array|boolean Array with user data if successful, false if failed
 */
function register($email, $password, $fullName, $phone = '') {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Email already registered
        return ['error' => 'Email already registered'];
    }
    
    // Hash the password (using MD5 for demonstration - use better methods in production!)
    $passwordHash = md5($password);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, full_name, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $passwordHash, $fullName, $phone);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        return [
            'id' => $userId,
            'email' => $email,
            'full_name' => $fullName
        ];
    } else {
        return false;
    }
}

/**
 * Log out the current user
 */
function logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Get current user information
 * 
 * @return array|null User information array or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = connectDatabase();
    if (!$conn) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, email, full_name, phone, is_admin, created_at, last_login 
                           FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        return $user;
    }
    
    return null;
}

/**
 * Require user to be logged in, redirect if not
 * 
 * @param string $redirectUrl URL to redirect to if not logged in
 */
function requireLogin($redirectUrl = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectUrl");
        exit;
    }
}

/**
 * Require user to be an admin, redirect if not
 * 
 * @param string $redirectUrl URL to redirect to if not an admin
 */
function requireAdmin($redirectUrl = 'index.php') {
    if (!isAdmin()) {
        header("Location: $redirectUrl");
        exit;
    }
}

/**
 * Update user profile information
 * 
 * @param integer $userId   User ID
 * @param string  $fullName New full name
 * @param string  $phone    New phone number
 * 
 * @return boolean True if successful, false otherwise
 */
function updateUserProfile($userId, $fullName, $phone) {
    $conn = connectDatabase();
    if (!$conn) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $fullName, $phone, $userId);
    
    return $stmt->execute();
}

/**
 * Change user password
 * 
 * @param integer $userId      User ID
 * @param string  $oldPassword Current password
 * @param string  $newPassword New password
 * 
 * @return boolean|string True if successful, error message if failed
 */
function changeUserPassword($userId, $oldPassword, $newPassword) {
    $conn = connectDatabase();
    if (!$conn) {
        return "Database connection error";
    }
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Verify old password (using MD5 for demonstration)
        if (md5($oldPassword) !== $user['password_hash']) {
            return "Current password is incorrect";
        }
        
        // Update password
        $newPasswordHash = md5($newPassword);
        $updateStmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newPasswordHash, $userId);
        
        if ($updateStmt->execute()) {
            return true;
        } else {
            return "Failed to update password";
        }
    }
    
    return "User not found";
} 