<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    $_SESSION['error_message'] = 'Invalid patient ID.';
    header('Location: patients.php');
    exit;
}

// Fetch patient details to display on the page
$stmt = $pdo->prepare('SELECT u.username, u.email FROM users u WHERE u.id = ? AND u.role = "patient"');
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    $_SESSION['error_message'] = 'Patient not found.';
    header('Location: patients.php');
    exit;
}

$upload_dir = '../uploads/patient_files/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['patient_file']) && $_FILES['patient_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['patient_file']['name']);
        $file_name = trim($file_name); // Trim whitespace
        $file_path = $upload_dir . $file_name;
        $file_type = $_POST['file_type'] ?? 'other';

        // Move the uploaded file
        if (move_uploaded_file($_FILES['patient_file']['tmp_name'], $file_path)) {
            // Insert file info into database
            $stmt = $pdo->prepare('INSERT INTO patient_files (patient_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?)');
            if ($stmt->execute([$patient_id, $file_name, $file_path, $file_type])) {
                $message = '<div class="alert alert-success">File uploaded successfully!</div>';
                
                // Log the activity
                $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)');
                $stmt->execute([
                    $_SESSION['user']['id'],
                    'upload_file',
                    'PatientID:' . $patient_id . ' File:' . $file_name,
                    $_SERVER['REMOTE_ADDR']
                ]);
            } else {
                $message = '<div class="alert alert-danger">Error saving file info to database.</div>';
                unlink($file_path); // Delete the uploaded file if database insertion fails
            }
        } else {
            $message = '<div class="alert alert-danger">Error uploading file.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">No file uploaded or upload error.</div>';
    }
}

// Fetch existing files for this patient
$stmt = $pdo->prepare('SELECT id, file_name, file_type, uploaded_at FROM patient_files WHERE patient_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([$patient_id]);
$existing_files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File for <?= htmlspecialchars($patient['username']) ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="patients.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="py-5">
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Upload File for Patient: <?= htmlspecialchars($patient['username']) ?></h4>
                        </div>
                        <div class="card-body">
                            <?= $message ?>
                            <form action="upload_patient_file.php?patient_id=<?= htmlspecialchars($patient_id) ?>" method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="patient_file" class="form-label">Select File</label>
                                    <input class="form-control" type="file" id="patient_file" name="patient_file" required>
                                </div>
                                <div class="mb-3">
                                    <label for="file_type" class="form-label">File Type</label>
                                    <select class="form-select" id="file_type" name="file_type" required>
                                        <option value="">Select type</option>
                                        <option value="lab_result">Lab Result</option>
                                        <option value="scan">Scan</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload</button>
                                <a href="patients.php" class="btn btn-secondary">Back to Patients</a>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Existing Files</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($existing_files)): ?>
                                <div class="alert alert-info">No files uploaded yet.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Type</th>
                                                <th>Uploaded</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($existing_files as $file): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($file['file_name']) ?></td>
                                                    <td><?= htmlspecialchars($file['file_type']) ?></td>
                                                    <td><?= htmlspecialchars($file['uploaded_at']) ?></td>
                                                    <td>
                                                        <a href="../uploads/patient_files/<?= urlencode($file['file_name']) ?>" 
                                                           class="btn btn-primary btn-sm" 
                                                           target="_blank">
                                                            <i class="fas fa-download"></i> View
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
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 