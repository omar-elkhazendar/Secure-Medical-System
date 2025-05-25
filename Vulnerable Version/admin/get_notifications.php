<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();

// Check if user is logged in and has the admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get new notifications
$notifications = $pdo->query("
    SELECT * FROM (
        SELECT 'appointment' as type, appointment_date as date, 
               CONCAT('New appointment scheduled for ', DATE_FORMAT(appointment_date, '%Y-%m-%d %H:%i')) as message
        FROM appointments 
        WHERE appointment_date >= NOW()
        AND appointment_date <= DATE_ADD(NOW(), INTERVAL 1 HOUR)
        UNION ALL
        SELECT 'user' as type, created_at as date,
               CONCAT('New user registered: ', username) as message
        FROM users
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ) as combined
    ORDER BY date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($notifications); 