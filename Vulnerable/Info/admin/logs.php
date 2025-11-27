<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();
// حماية الصفحة
// Check if user is logged in and has the admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // If not logged in or not an admin, redirect to login page
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];

// تصدير اللوجات
if (isset($_GET['export']) && in_array($_GET['export'], ['csv','log'])) {
    $format = $_GET['export'];
    $stmt = $pdo->query('SELECT al.*, u.username FROM activity_logs al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC');
    $logs = $stmt->fetchAll();
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="logs.csv"');
        echo "User,Action,Details,IP,Time\n";
        foreach ($logs as $log) {
            echo '"'.str_replace('"','""',$log['username']).'","'.str_replace('"','""',$log['action']).'","'.str_replace('"','""',$log['details']).'","'.str_replace('"','""',$log['ip_address']).'","'.$log['created_at']."\n";
        }
        exit;
    } else {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="logs.log"');
        foreach ($logs as $log) {
            echo '['.$log['created_at'].'] ['.$log['username'].'] ['.$log['ip_address'].'] '.$log['action'].' - '.$log['details']."\n";
        }
        exit;
    }
}

// عرض أحدث 100 لوج فقط
$stmt = $pdo->query('SELECT al.*, u.username FROM activity_logs al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 100');
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <section class="py-5">
    <div class="container mt-4">
        <h2>System Logs</h2>
        <div class="mb-3">
            <a href="logs.php?export=csv" class="btn btn-custom btn-success btn-sm">Export CSV</a>
            <a href="logs.php?export=log" class="btn btn-custom btn-secondary btn-sm">Export LOG</a>
            <a href="dashboard.php" class="btn btn-custom btn-outline-dark btn-sm">Back to Dashboard</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-custom">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['username']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                        <td><?= htmlspecialchars($log['ip_address']) ?></td>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 