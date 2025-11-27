<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

$encryption_key = 'my_super_secret_key_123';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$patient_id = $_SESSION['user']['id'];

// Fetch medical records
$sql = 'SELECT mr.*, u.username as doctor_name 
                      FROM medical_records mr 
                      JOIN doctors d ON mr.doctor_id = d.id 
                      JOIN users u ON d.user_id = u.id 
                      WHERE mr.patient_id = ? 
                      ORDER BY mr.created_at DESC';

$stmt = $pdo->prepare($sql);

$stmt->execute([$patient_id]);

$medical_records = $stmt->fetchAll();

// Fetch files for the logged-in patient
$stmt = $pdo->prepare('SELECT id, file_name, file_type, uploaded_at FROM patient_files WHERE patient_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([$patient_id]);
$patient_files = $stmt->fetchAll();

function decryptField($data, $key) {
    return openssl_decrypt($data, 'AES-128-ECB', $key);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Records - Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
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
                    <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link active" href="patient_records.php">My Records</a></li>
                    <li class="nav-item"><a class="nav-link" href="prescriptions.php">Prescriptions</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="py-5">
        <div class="container mt-4">
            <h2>My Medical Records</h2>
            
            <!-- Medical Records Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Diagnoses & Prescriptions</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($medical_records)): ?>
                        <div class="alert alert-info">No medical records found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doctor</th>
                                        <th>Diagnosis</th>
                                        <th>Prescription</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medical_records as $record): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($record['created_at']) ?></td>
                                            <td><?= htmlspecialchars($record['doctor_name']) ?></td>
                                            <td><?= htmlspecialchars(decryptField($record['diagnosis'], $encryption_key)) ?></td>
                                            <td><?= htmlspecialchars(decryptField($record['prescription'], $encryption_key)) ?></td>
                                            <td>
                                                <a href="download_prescription.php?record_id=<?= $record['id'] ?>" 
                                                   class="btn btn-sm btn-success" >
                                                    <i class="fas fa-download"></i> Download Prescription
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Medical Files Section -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Medical Files & Lab Results</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($patient_files)): ?>
                        <div class="alert alert-info">No medical files found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>File Type</th>
                                        <th>Uploaded At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patient_files as $file): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($file['file_name']) ?></td>
                                            <td><?= htmlspecialchars($file['file_type']) ?></td>
                                            <td><?= htmlspecialchars($file['uploaded_at']) ?></td>
                                            <td>
                                                <a href="../uploads/patient_files/<?= urlencode($file['file_name']) ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   target="_blank">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 