<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

$encryption_key = 'my_super_secret_key_123';

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

// جلب الوصفات الطبية
$stmt = $pdo->prepare('SELECT mr.*, u.username as doctor_name FROM medical_records mr JOIN doctors d ON mr.doctor_id = d.id JOIN users u ON d.user_id = u.id WHERE mr.patient_id = ? ORDER BY mr.created_at DESC');
$stmt->execute([$patient_id]);
$records = $stmt->fetchAll();

function decryptField($data, $key) {
    return openssl_decrypt($data, 'AES-128-ECB', $key);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions - Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <h2>My Prescriptions</h2>
        <table class="table table-bordered table-custom">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Prescription</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $rec): ?>
                <tr>
                    <td><?= htmlspecialchars($rec['created_at']) ?></td>
                    <td><?= htmlspecialchars($rec['doctor_name']) ?></td>
                    <td><?= htmlspecialchars(decryptField($rec['prescription'], $encryption_key)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-custom btn-secondary">Back to Dashboard</a>
    </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 