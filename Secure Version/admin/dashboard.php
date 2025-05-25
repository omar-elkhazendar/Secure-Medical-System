<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();

// Custom token/session handling
// Check if user is logged in and has the admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // If not logged in or not an admin, redirect to login page
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];

// Get system statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_doctors' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetchColumn(),
    'total_patients' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn(),
    'total_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn()
];

// Get monthly statistics for charts
$monthly_stats = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count,
        role
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), role
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Get appointment statistics
$appointment_stats = $pdo->query("
    SELECT 
        DATE_FORMAT(appointment_date, '%Y-%m-%d') as date,
        COUNT(*) as count
    FROM appointments 
    WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE_FORMAT(appointment_date, '%Y-%m-%d')
    ORDER BY date ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Get recent activity logs
$stmt = $pdo->query("
    SELECT al.*, u.username 
    FROM activity_logs al 
    JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$recent_logs = $stmt->fetchAll();

// Get notifications
$notifications = $pdo->query("
    SELECT * FROM (
        SELECT 'appointment' as type, appointment_date as date, 
               CONCAT('New appointment scheduled for ', DATE_FORMAT(appointment_date, '%Y-%m-%d %H:%i')) as message
        FROM appointments 
        WHERE appointment_date >= NOW()
        UNION ALL
        SELECT 'user' as type, created_at as date,
               CONCAT('New user registered: ', username) as message
        FROM users
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ) as combined
    ORDER BY date DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: #283eec;
            --secondary-color: #00c6fb;
            --accent-color: #f7971e;
            --gradient-users: linear-gradient(135deg, #283eec 0%, #1e62d0 100%);
            --gradient-doctors: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
            --gradient-patients: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --gradient-appointments: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
        }
        .dashboard-card {
            border-radius: 18px;
            padding: 24px 20px;
            color: #fff;
            font-weight: 500;
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.08);
            transition: transform 0.2s;
            min-width: 200px;
        }
        .dashboard-card.bg1 { background: var(--gradient-users); }
        .dashboard-card.bg2 { background: var(--gradient-doctors); }
        .dashboard-card.bg3 { background: var(--gradient-patients); }
        .dashboard-card.bg4 { background: var(--gradient-appointments); color: #333; }
        .dashboard-card .dashboard-icon { font-size: 2.5rem; margin-bottom: 10px; }
        .dashboard-card .card-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; }
        .dashboard-card .card-value { font-size: 2.2rem; font-weight: bold; }
        .dashboard-card:hover { transform: translateY(-6px) scale(1.03); }
        h2.mb-4 {
            font-weight: 800;
            color: #222a5c;
            letter-spacing: 0.5px;
        }
        .card.shadow, .card.shadow.animate-fadeInUp {
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.08);
        }
        .table thead th {
            background: #222a5c;
            color: #fff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
        }
        .table tbody tr {
            background: #fff;
            color: #222a5c;
            font-weight: 500;
        }
        .table {
            border-radius: 14px;
            overflow: hidden;
        }
        .card-header.bg-white h5 {
            color: #222a5c;
            font-weight: 700;
        }
        .mb-0 i {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="patients.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="upload_patient_file.php">Upload Patient File</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMessages" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Messages
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMessages">
                            <li><a class="dropdown-item" href="messages.php">View Messages</a></li>
                            <li><a class="dropdown-item" href="send_message.php">Send Message</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_user.php">Add User</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if (count($notifications) > 0): ?>
                            <span class="notification-badge"><?php echo count($notifications); ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                            <h6 class="dropdown-header">Notifications</h6>
                            <?php foreach ($notifications as $notification): ?>
                            <a class="dropdown-item notification-item" href="#">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-<?php echo $notification['type'] === 'appointment' ? 'calendar' : 'user'; ?> me-2"></i>
                                    <div>
                                        <small class="text-muted"><?php echo date('M d, H:i', strtotime($notification['date'])); ?></small>
                                        <p class="mb-0"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <h2 class="mb-4">Dashboard Overview</h2>
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card bg1 shadow animate-fadeInUp" style="cursor:pointer;" onclick="window.location.href='users.php';">
                        <div class="dashboard-icon"><i class="fas fa-users"></i></div>
                        <div class="card-title">Total Users</div>
                        <div class="card-value"><?php echo $stats['total_users']; ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card bg2 shadow animate-fadeInUp" style="cursor:pointer;" onclick="window.location.href='doctors.php';">
                        <div class="dashboard-icon"><i class="fas fa-user-md"></i></div>
                        <div class="card-title">Doctors</div>
                        <div class="card-value"><?php echo $stats['total_doctors']; ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card bg3 shadow animate-fadeInUp" style="cursor:pointer;" onclick="window.location.href='patients.php';">
                        <div class="dashboard-icon"><i class="fas fa-user-injured"></i></div>
                        <div class="card-title">Patients</div>
                        <div class="card-value"><?php echo $stats['total_patients']; ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card bg4 shadow animate-fadeInUp" style="cursor:pointer;" onclick="window.location.href='appointments.php';">
                        <div class="dashboard-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="card-title">Appointments</div>
                        <div class="card-value"><?php echo $stats['total_appointments']; ?></div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow animate-fadeInUp">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>User Growth</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="userGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow animate-fadeInUp">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Appointments Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="appointmentsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow animate-fadeInUp">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['username']); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td>
                                        <?php
                                        $ip = $log['ip_address'] === '::1' ? '127.0.0.1' : $log['ip_address'];
                                        echo htmlspecialchars($ip);
                                        ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-heartbeat text-primary me-2"></i>HealthCare</h5>
                    <p>Admin dashboard for healthcare management system.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Prepare data for charts
        const monthlyData = <?php echo json_encode($monthly_stats); ?>;
        const appointmentData = <?php echo json_encode($appointment_stats); ?>;

        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: [...new Set(monthlyData.map(item => item.month))],
                datasets: [
                    {
                        label: 'Doctors',
                        data: monthlyData.filter(item => item.role === 'doctor').map(item => item.count),
                        borderColor: '#2196f3',
                        tension: 0.4
                    },
                    {
                        label: 'Patients',
                        data: monthlyData.filter(item => item.role === 'patient').map(item => item.count),
                        borderColor: '#00bfa5',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Appointments Chart
        const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
        new Chart(appointmentsCtx, {
            type: 'bar',
            data: {
                labels: appointmentData.map(item => item.date),
                datasets: [{
                    label: 'Appointments',
                    data: appointmentData.map(item => item.count),
                    backgroundColor: '#ff9800',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Enable notifications
        if ('Notification' in window) {
            Notification.requestPermission();
        }

        // Check for new notifications every minute
        setInterval(() => {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        new Notification('New Notification', {
                            body: data[0].message
                        });
                    }
                });
        }, 60000);
    </script>
</body>
</html> 