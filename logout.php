<?php
/**
 * Vinpearl Resort Nha Trang - Logout Script
 */

require_once 'includes/auth.php';

// Log the user out
logout();

// Redirect to the homepage
header('Location: index.php');
exit; 