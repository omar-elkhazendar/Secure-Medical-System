<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'info_db');

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=info_db;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    die('Could not connect to the database. Please try again later.');
}

// Function to log activity
function logActivity($pdo, $user_id, $action, $details = null, $ip_address = null) {
    try {
        // Use provided IP address if available, otherwise fallback to REMOTE_ADDR
        $ip_to_log = $ip_address ?? $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $details, $ip_to_log]);
        return true;
    } catch (PDOException $e) {
        error_log("Activity Logging Error: " . $e->getMessage());
        return false;
    }
}
?> 