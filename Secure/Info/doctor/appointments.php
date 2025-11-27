<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();
// Check if user is logged in and has the doctor role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    // If not logged in or not a doctor, redirect to login page
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];
// Get doctor id
$stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
$stmt->execute([$user['id']]);
$doctor_id = $stmt->fetchColumn();
if (!$doctor_id) {
    echo '<div class="alert alert-danger">Doctor profile not found.</div>';
    exit;
}
// Handle status update POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['status'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $status = trim($_POST['status']);

    // Validate status against allowed values
    $allowed_statuses = ['scheduled', 'completed', 'cancelled'];
    if (in_array($status, $allowed_statuses)) {
        // Prepare and execute the update statement
        $stmt_update = $pdo->prepare('UPDATE appointments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND doctor_id = ?');
        if ($stmt_update->execute([$status, $appointment_id, $doctor_id])) {
            $_SESSION['success_message'] = 'Appointment status updated successfully!';
            // Log activity
            $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
                ->execute([$user['id'], 'update_appointment_status', 'AppointmentID:' . $appointment_id . ' Status:' . $status, $_SERVER['REMOTE_ADDR']]);
        } else {
            $_SESSION['error_message'] = 'Error updating appointment status.';
             // Log activity
            $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
                ->execute([$user['id'], 'update_appointment_status_failed', 'AppointmentID:' . $appointment_id . ' Status:' . $status . ' Error:' . $stmt_update->errorInfo()[2], $_SERVER['REMOTE_ADDR']]);
        }
    } else {
        $_SESSION['error_message'] = 'Invalid status provided.';
         // Log activity for invalid status attempt
        $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
            ->execute([$user['id'], 'update_appointment_status_invalid', 'AppointmentID:' . $appointment_id . ' Attempted Status:' . $status, $_SERVER['REMOTE_ADDR']]);
    }

    // Redirect back to the appointments page
    header('Location: appointments.php');
    exit;
}

// Fetch appointments for this doctor with filtering
$where = "WHERE a.doctor_id = ?";
$params = [$doctor_id];

// Status filter
$filter_status = $_GET['status'] ?? '';
if (!empty($filter_status) && $filter_status !== 'All Status') {
     // Validate filter status against allowed values (including 'All Status' check)
    $allowed_filter_statuses = ['scheduled', 'completed', 'cancelled', 'All Status'];
     if (in_array($filter_status, $allowed_filter_statuses)) {
         $where .= " AND a.status = ?";
         $params[] = $filter_status;
     } else {
          // Handle invalid status in filter - perhaps ignore filter or show error
         // For now, ignoring invalid filter status
     }
}

// Date filter
$filter_date = $_GET['date'] ?? '';
if (!empty($filter_date)) {
    // Validate date format (basic check)
    if (DateTime::createFromFormat('m/d/Y', $filter_date) !== false) {
        $date_obj = DateTime::createFromFormat('m/d/Y', $filter_date);
        $formatted_date = $date_obj->format('Y-m-d');
        $where .= " AND DATE(a.appointment_date) = ?";
        $params[] = $formatted_date;
    } else {
        // Handle invalid date format - perhaps show an error
        $_SESSION['error_message'] = 'Invalid date format for filtering.';
    }
}

$sql = "SELECT a.*, u.username as patient_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN users u ON p.user_id = u.id
        $where
        ORDER BY a.appointment_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .appointment-card {
            background: linear-gradient(135deg, #ADD8E6 0%, #87CEEB 100%); /* Light blue gradient */
            color: #333;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .appointment-card h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .table-custom {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            overflow: hidden;
        }
        .table-custom th {
            background: #4CAF50;
            color: white;
            font-weight: 500;
            padding: 12px;
        }
        .table-custom td {
            padding: 12px;
            vertical-align: middle;
        }
        .badge {
            padding: 8px 12px;
            border-radius: 50px;
            font-weight: 500;
        }
        .btn-custom {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .filter-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .filter-section select, .filter-section input {
            border-radius: 50px;
            padding: 8px 15px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare Doctor
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="my_patients.php">My Patients</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Sign Out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="appointment-card">
                <h3><i class="fas fa-calendar-check me-2"></i>Appointments</h3>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>
                 <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <!-- Filter Section -->
                <div class="filter-section mb-4">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="statusFilter" class="form-label visually-hidden">Filter by Status</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="All Status">All Status</option>
                                <option value="scheduled" <?= ($filter_status === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                                <option value="completed" <?= ($filter_status === 'completed') ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= ($filter_status === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="dateFilter" class="form-label visually-hidden">Filter by Date</label>
                            <input type="date" class="form-control" id="dateFilter" name="date" value="<?= htmlspecialchars($filter_date) ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-custom btn-primary">Filter</button>
                            <a href="appointments.php" class="btn btn-custom btn-light">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Action</th>
                </tr>
                        </thead>
            <tbody>
                            <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No appointments found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                                        <td><?= htmlspecialchars(date('M d, Y', strtotime($appointment['appointment_date']))) ?></td>
                                        <td><?= htmlspecialchars(date('h:i A', strtotime($appointment['appointment_date']))) ?></td>
                                        <td><?= htmlspecialchars(ucfirst($appointment['status'])) ?></td>
                                        <td>
                                             <form action="appointments.php" method="post" class="d-inline-block">
                                                 <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                                 <select name="status" class="form-select form-select-sm d-inline-block w-auto me-1">
                                                     <option value="scheduled" <?= ($appointment['status'] === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                                                     <option value="completed" <?= ($appointment['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                                                     <option value="cancelled" <?= ($appointment['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                                 </select>
                                                 <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
                                             </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
            </tbody>
        </table>
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
                    <p>Doctor appointments management system.</p>
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
</body>
</html> 