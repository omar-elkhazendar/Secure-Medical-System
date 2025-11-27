<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();
// حماية الصفحة
// Check if user is logged in and has the patient role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    // If not logged in or not a patient, redirect to login page
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];

// جلب بيانات المريض
$stmt = $pdo->prepare('SELECT id FROM patients WHERE user_id = ?');
$stmt->execute([$user['id']]);
$patient_id = $stmt->fetchColumn();
if (!$patient_id) {
    echo '<div class="alert alert-danger">Patient profile not found.</div>';
    exit;
}

// Check current appointment count for this patient
$stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND appointment_date >= NOW()');
$stmt->execute([$patient_id]);
$current_appointment_count = $stmt->fetchColumn();
$appointment_limit = 3;

// حجز موعد جديد
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctor_id'], $_POST['appointment_date'], $_POST['appointment_time'])) {
    
    // Check if user has reached the appointment limit
    if ($current_appointment_count >= $appointment_limit) {
        $error = 'You have reached the maximum limit of ' . $appointment_limit . ' active appointments. Please cancel some appointments before booking new ones.';
    } else {
        $doctor_id = (int)$_POST['doctor_id'];
        $date = $_POST['appointment_date'];
        $time = $_POST['appointment_time'];
        $datetime = $date . ' ' . $time;
        
        // تحقق من صحة البيانات
        if (!$doctor_id || !$date || !$time) {
            $error = 'All fields are required.';
        } else {
            // Validate date is not in the past
            $appointment_timestamp = strtotime($datetime);
            $current_timestamp = time();
            
            if ($appointment_timestamp < $current_timestamp) {
                $error = 'Cannot book appointments in the past.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO appointments (patient_id, doctor_id, appointment_date) VALUES (?, ?, ?)');
                $stmt->execute([$patient_id, $doctor_id, $datetime]);
                $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
                    ->execute([$user['id'], 'book_appointment', 'DoctorID:'.$doctor_id.' Date:'.$datetime, Auth::getClientIp()]);
                // Redirect after booking
                header('Location: appointments.php?success=1');
                exit;
            }
        }
    }
}

// إلغاء موعد
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $app_id = (int)$_GET['cancel'];
    $stmt = $pdo->prepare('DELETE FROM appointments WHERE id=? AND patient_id=?');
    $stmt->execute([$app_id, $patient_id]);
    $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
        ->execute([$user['id'], 'cancel_appointment', 'AppointmentID:'.$app_id, Auth::getClientIp()]);
    header('Location: appointments.php');
    exit;
}

// جلب الأطباء
$doctors = $pdo->query('SELECT d.id, u.username, d.specialization FROM doctors d JOIN users u ON d.user_id = u.id')->fetchAll();

// جلب المواعيد القادمة
$stmt = $pdo->prepare('SELECT a.*, u.username as doctor_name, d.specialization FROM appointments a JOIN doctors d ON a.doctor_id = d.id JOIN users u ON d.user_id = u.id WHERE a.patient_id = ? AND a.appointment_date >= NOW() ORDER BY a.appointment_date ASC');
$stmt->execute([$patient_id]);
$appointments = $stmt->fetchAll();

date_default_timezone_set('Asia/Riyadh'); // Set to your local timezone

// After the redirect, show a success message if present
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .appointment-limit {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .limit-reached {
            background: linear-gradient(135deg, #ff4757, #ff3838);
        }
        .limit-ok {
            background: linear-gradient(135deg, #2ed573, #1e90ff);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare Patient
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="prescriptions.php">Prescriptions</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <section class="py-5">
    <div class="container mt-4">
        <h2>Book Appointment</h2>
        
        <!-- Appointment Limit Display -->
        <div class="appointment-limit <?php echo ($current_appointment_count >= $appointment_limit) ? 'limit-reached' : 'limit-ok'; ?>">
            <i class="fas fa-calendar-check me-2"></i>
            Appointments: <?php echo $current_appointment_count; ?> / <?php echo $appointment_limit; ?>
            <?php if ($current_appointment_count >= $appointment_limit): ?>
                - <span class="badge bg-warning">Limit Reached</span>
            <?php else: ?>
                - <span class="badge bg-success">Available</span>
            <?php endif; ?>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">Appointment booked successfully.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="post" class="mb-4">
            <div class="mb-3">
                <label for="doctor_id" class="form-label">Doctor</label>
                <select name="doctor_id" id="doctor_id" class="form-control" required <?php echo ($current_appointment_count >= $appointment_limit) ? 'disabled' : ''; ?>>
                    <option value="">Select Doctor</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['username']) ?> (<?= htmlspecialchars($doc['specialization']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="appointment_date" class="form-label">Date</label>
                <input type="date" name="appointment_date" id="appointment_date" class="form-control" required <?php echo ($current_appointment_count >= $appointment_limit) ? 'disabled' : ''; ?> min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-3">
                <label for="appointment_time" class="form-label">Time</label>
                <input type="time" name="appointment_time" id="appointment_time" class="form-control" required <?php echo ($current_appointment_count >= $appointment_limit) ? 'disabled' : ''; ?>>
            </div>
            <button type="submit" class="btn btn-custom btn-primary" <?php echo ($current_appointment_count >= $appointment_limit) ? 'disabled' : ''; ?>>Book Appointment</button>
            
            <?php if ($current_appointment_count >= $appointment_limit): ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    You have reached the maximum limit of <?php echo $appointment_limit; ?> appointments. Please cancel some appointments to book new ones.
                </div>
            <?php endif; ?>
        </form>
        
        <h4>Upcoming Appointments</h4>
        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">No upcoming appointments.</div>
        <?php else: ?>
            <table class="table table-bordered table-custom">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($app['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($app['specialization']) ?></td>
                        <td><span class="badge bg-primary">Scheduled</span></td>
                        <td><a href="?cancel=<?= $app['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this appointment?')">Cancel</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="dashboard.php" class="btn btn-custom btn-secondary">Back to Dashboard</a>
    </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum date to today
        document.getElementById('appointment_date').min = new Date().toISOString().split('T')[0];
        
        // Real-time appointment count update
        function updateAppointmentCount() {
            // This could be enhanced with AJAX to get real-time count
            console.log('Appointment count updated');
        }
    </script>
</body>
</html>