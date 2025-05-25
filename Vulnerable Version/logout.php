<?php
require_once 'config/database.php';
session_start();

if (isset($_SESSION['user'])) {
    // Log the logout activity
    logActivity($pdo, $_SESSION['user']['id'], 'logout', 'User logged out');
    
    // Clear all session data
    session_unset();
    session_destroy();
}

// Redirect to login page
header('Location: login.php');
exit;
?> 