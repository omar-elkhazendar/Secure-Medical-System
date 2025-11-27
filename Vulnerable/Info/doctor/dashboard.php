<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

$encryption_key = 'my_super_secret_key_123'; // Define the encryption key

session_start();

// Custom token/session handling
// Check if user is logged in and has the doctor role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    // If not logged in or not a doctor, redirect to login page
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $specialization = $_POST['specialization'];
    $license_number = $_POST['license_number'];
    $email = $_POST['email'];
    
    try {
        // Update users table
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $user['id']]);
        
        // Update doctors table
        $stmt = $pdo->prepare("UPDATE doctors SET specialization = ?, license_number = ? WHERE user_id = ?");
        $stmt->execute([$specialization, $license_number, $user['id']]);
        
        // Refresh the page to show updated data
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}

// Get doctor's information
$stmt = $pdo->prepare("
    SELECT d.*, u.username, u.email 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    WHERE d.user_id = ?
");
$stmt->execute([$user['id']]);
$doctor = $stmt->fetch();

// Get today's appointments
$stmt = $pdo->prepare("
    SELECT DISTINCT a.id, a.appointment_date, a.status, p.id as patient_id, u.username as patient_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE a.doctor_id = ? AND DATE(a.appointment_date) = CURDATE()
    ORDER BY a.appointment_date ASC
");
$stmt->execute([$doctor['id']]);
$today_appointments = $stmt->fetchAll();

// Get recent medical records
$stmt = $pdo->prepare("
    SELECT mr.*, p.id as patient_id, u.username as patient_name
    FROM medical_records mr
    JOIN patients p ON mr.patient_id = p.id 
    JOIN users u ON p.user_id = u.id
    WHERE mr.doctor_id = ?
    ORDER BY mr.created_at DESC
    LIMIT 5
");
$stmt->execute([$doctor['id']]);
$recent_records = $stmt->fetchAll();


function decryptField($data, $key) {
    return openssl_decrypt($data, 'AES-128-ECB', $key);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        .dashboard-card {
            min-height: 220px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
        }
        .dashboard-card.bg1 {
            background: linear-gradient(135deg, #ADD8E6 0%, #87CEEB 100%); /* Light blue gradient */
            color: #333;
        }
        .dashboard-card.bg2 {
            background: linear-gradient(135deg, #ADD8E6 0%, #6495ED 100%); /* Softer blue gradient */
            color: #fff;
        }
        .dashboard-card.bg3 {
            background: linear-gradient(135deg, #4169E1 0%, #0000CD 100%); /* Blue gradient */
            color: #fff;
        }
        .dashboard-card .btn-custom {
            margin-top: 15px;
        }
        .dashboard-card h5, .dashboard-card h2, .dashboard-card .card-title {
            color: #fff;
        }
         .dashboard-card.bg1 h5, .dashboard-card.bg1 h2, .dashboard-card.bg1 .card-title {
             color: #333;
         }
        .dashboard-card .card-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        .dashboard-card .card-value {
            font-size: 2.2rem;
            font-weight: 700;
        }
         .row.g-4 {
             margin-right: 0;
             margin-left: 0;
         }
         .col-md-4, .col-md-8 {
             padding-right: 10px;
             padding-left: 10px;
         }
         @media (max-width: 991px) {
             .dashboard-card { min-height: 180px; }
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
                    <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Social Feed Button -->
    <div class="container mt-3" style="text-align: right;">
        <a href="social_feed.php" class="btn btn-info btn-sm" style="border-radius: 20px; font-weight: 500;">
            <i class="fas fa-rss me-1"></i> Social Feed
        </a>
    </div>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Doctor Profile Card -->
                <div class="col-md-4">
                    <div class="card-custom dashboard-card bg1 shadow animate-fadeInUp">
                        <div class="dashboard-icon"><i class="fas fa-user-md"></i></div>
                        <div class="card-title">Doctor Profile</div>
                         <?php if (isset($error)): ?>
                             <div class="alert alert-danger"><?php echo $error; ?></div>
                         <?php endif; ?>
                        <form method="POST" action="" id="profileForm" style="display: none;">
                            <div class="mb-2">
                                <label class="form-label"><b>Name:</b></label>
                                <input type="text" class="form-control form-control-sm" name="username" value="<?php echo htmlspecialchars($doctor['username']); ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label"><b>Specialization:</b></label>
                                <input type="text" class="form-control form-control-sm" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label"><b>License:</b></label>
                                <input type="text" class="form-control form-control-sm" name="license_number" value="<?php echo htmlspecialchars($doctor['license_number']); ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label"><b>Email:</b></label>
                                <input type="email" class="form-control form-control-sm" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                            </div>
                            <div class="mt-3">
                                <button type="submit" name="update_profile" class="btn btn-custom btn-light me-2">
                                    <i class="fas fa-save me-2"></i>Save
                                </button>
                                <button type="button" class="btn btn-custom btn-light" onclick="toggleProfileEdit()">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                            </div>
                        </form>
                         <div id="profileView">
                            <div><b>Name:</b> <?php echo htmlspecialchars($doctor['username']); ?></div>
                            <div><b>Specialization:</b> <?php echo htmlspecialchars($doctor['specialization']); ?></div>
                            <div><b>License:</b> <?php echo htmlspecialchars($doctor['license_number']); ?></div>
                            <div><b>Email:</b> <?php echo htmlspecialchars($doctor['email']); ?></div>
                            <button onclick="toggleProfileEdit()" class="btn btn-custom btn-light mt-3">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </button>
                         </div>
                    </div>
                </div>

                <!-- Today's Appointments Card -->
                <div class="col-md-8">
                    <div class="card-custom dashboard-card bg2 shadow animate-fadeInUp mb-4">
                        <div class="dashboard-icon"><i class="fas fa-calendar-day"></i></div>
                        <div class="card-title">Today's Appointments</div>
                        <?php if (empty($today_appointments)): ?>
                            <div class="text-white">No appointments scheduled for today.</div>
                        <?php else: ?>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Patient</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $shown = [];
                                        foreach ($today_appointments as $appointment): 
                                            $key = $appointment['patient_id'] . '_' . $appointment['appointment_date'];
                                            if (in_array($key, $shown)) continue;
                                            $shown[] = $key;
                                        ?>
                                        <tr>
                                            <td><?php echo date('H:i', strtotime($appointment['appointment_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $appointment['status'] === 'scheduled' ? 'primary' : ($appointment['status'] === 'completed' ? 'success' : 'danger'); ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Medical Records Card -->
                    <div class="card-custom dashboard-card bg3 shadow animate-fadeInUp">
                        <div class="dashboard-icon"><i class="fas fa-notes-medical"></i></div>
                        <div class="card-title">Recent Medical Records</div>
                        <?php if (empty($recent_records)): ?>
                            <div class="text-white">No recent medical records.</div>
                        <?php else: ?>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th>Date</th>
                                            <th>Diagnosis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_records as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['patient_name']); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($record['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars(substr(decryptField($record['diagnosis'], $encryption_key), 0, 50)) . '...'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- My Patients with Appointments Button -->
                    <div class="text-center mt-3">
                         <a href="my_patients.php" class="btn btn-lg btn-success">
                             <i class="fas fa-stethoscope me-2"></i> My Patients with Appointments
                         </a>
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
                    <p>Doctor dashboard for healthcare management system.</p>
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
     <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
    function toggleProfileEdit() {
        const form = document.getElementById('profileForm');
        const view = document.getElementById('profileView');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            view.style.display = 'none';
        } else {
            form.style.display = 'none';
            view.style.display = 'block';
        }
    }
    </script>
</body>
</html> 