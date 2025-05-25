<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// إعداد مفتاح التشفير (يفضل وضعه في ملف إعدادات منفصل)
$encryption_key = 'my_super_secret_key_123';

session_start();

// Check if user is logged in and has the doctor role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    // If not logged in or not a doctor, redirect to login page
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];

// جلب بيانات المريض
$patient_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$patient_id) {
    // Redirect back to my_patients if no patient ID is provided
    header('Location: my_patients.php');
    exit;
}

$stmt = $pdo->prepare('SELECT p.*, u.username, u.email FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?');
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    // Redirect back to my_patients if patient not found
    $_SESSION['error_message'] = 'Patient not found.'; // Optional: Add an error message
    header('Location: my_patients.php');
    exit;
}

// Get the logged-in doctor's ID
$doctor_user_id = $user['id']; // Corrected to use user id from session
$stmt_doctor_id = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
$stmt_doctor_id->execute([$doctor_user_id]);
$doctor_id = $stmt_doctor_id->fetchColumn();

if (!$doctor_id) {
     // Redirect if doctor profile not found (should not happen if user role is doctor)
    header('Location: dashboard.php');
    exit;
}

// Check for an active appointment between the doctor and the patient
// Ensure this check doesn't redirect, just controls form visibility
$stmt_appointment = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND patient_id = ? AND status IN ('scheduled', 'completed')");
$stmt_appointment->execute([$doctor_id, $patient['id']]);
$has_appointment = $stmt_appointment->fetchColumn() > 0;

// Handle Edit request
$edit_record_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$record_to_edit = null;
if ($edit_record_id > 0) {
    $stmt_edit = $pdo->prepare('SELECT * FROM medical_records WHERE id = ? AND doctor_id = ? AND patient_id = ?');
    $stmt_edit->execute([$edit_record_id, $doctor_id, $patient['id']]);
    $record_to_edit = $stmt_edit->fetch();
    // If the record exists and belongs to this doctor and patient, it will be loaded
}

// Handle POST requests (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['diagnosis'], $_POST['prescription'])) {
    // Only process if the doctor has an appointment with the patient OR is editing an existing record
    if ($has_appointment || ($record_to_edit && $record_to_edit['id'] === $edit_record_id)) {
        $diagnosis = openssl_encrypt(trim($_POST['diagnosis']), 'AES-128-ECB', $encryption_key);
        $prescription = openssl_encrypt(trim($_POST['prescription']), 'AES-128-ECB', $encryption_key);

        if ($edit_record_id > 0 && $record_to_edit) {
            // Handle Edit submission
            $stmt_update = $pdo->prepare('UPDATE medical_records SET diagnosis = ?, prescription = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND doctor_id = ? AND patient_id = ?');
            if ($stmt_update->execute([$diagnosis, $prescription, $edit_record_id, $doctor_id, $patient['id']])) {
                 $_SESSION['success_message'] = 'Record updated successfully!';
                 $action_details = 'Updated RecordID:' . $edit_record_id . ' for PatientID:' . $patient['id'];
            } else {
                 $_SESSION['error_message'] = 'Error updating record.';
                 $action_details = 'Failed to update RecordID:' . $edit_record_id . ' for PatientID:' . $patient['id'];
            }
            $action_type = 'update_record';

        } else if ($has_appointment) {
             // Handle Add submission (only if has_appointment is true)
            $stmt_insert = $pdo->prepare('INSERT INTO medical_records (patient_id, doctor_id, diagnosis, prescription, notes) VALUES (?, ?, ?, ?, ?)');
             if ($stmt_insert->execute([$patient['id'], $doctor_id, $diagnosis, $prescription, ''])) {
                 $_SESSION['success_message'] = 'Record added successfully!';
                 $action_details = 'Added new record for PatientID:' . $patient['id'];
             } else {
                 $_SESSION['error_message'] = 'Error adding record.';
                 $action_details = 'Failed to add record for PatientID:' . $patient['id'];
             }
            $action_type = 'add_record';
        } else {
             // This case should theoretically not be reached due to the outer if condition
             $_SESSION['error_message'] = 'Operation not allowed.';
             $action_type = 'operation_denied';
             $action_details = 'Attempted operation without appointment or valid edit context for PatientID:' . $patient['id'];
        }

        // Log the activity
        $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
            ->execute([$user['id'], $action_type, $action_details, $_SERVER['REMOTE_ADDR']]);

        // Redirect back to the same patient view page after submission
        header('Location: view_patient.php?id=' . $patient['id']);
        exit;

    } else {
        // Optional: Add an error message if trying to add/edit without permission
        $_SESSION['error_message'] = 'Cannot perform this action without a scheduled or completed appointment (for add) or a valid record to edit.';
        header('Location: view_patient.php?id=' . $patient['id']);
        exit;
    }
}

// حذف سجل
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $record_id_to_delete = (int)$_GET['delete'];
     // Ensure the record belongs to this doctor and patient before deleting
    $stmt_check = $pdo->prepare('SELECT id FROM medical_records WHERE id = ? AND doctor_id = ? AND patient_id = ?');
    $stmt_check->execute([$record_id_to_delete, $doctor_id, $patient['id']]);

    if ($stmt_check->fetch()) {
        $stmt = $pdo->prepare('DELETE FROM medical_records WHERE id=?');
        if ($stmt->execute([$record_id_to_delete])) {
             $_SESSION['success_message'] = 'Record deleted successfully!';
              $action_details = 'Deleted RecordID:' . $record_id_to_delete . ' for PatientID:' . $patient['id'];
               $action_type = 'delete_record';
        } else {
             $_SESSION['error_message'] = 'Error deleting record.';
             $action_details = 'Failed to delete RecordID:' . $record_id_to_delete . ' for PatientID:' . $patient['id'];
             $action_type = 'delete_record_failed';
        }
         $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
             ->execute([$user['id'], $action_type, $action_details, $_SERVER['REMOTE_ADDR']]);

    } else {
        $_SESSION['error_message'] = 'Record not found or does not belong to you.';
         $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
             ->execute([$user['id'], 'delete_record_denied', 'Attempted to delete unauthorized RecordID:' . $record_id_to_delete . ' for PatientID:' . $patient['id'], $_SERVER['REMOTE_ADDR']]);
    }

    header('Location: view_patient.php?id=' . $patient['id']);
    exit;
}

// جلب السجلات الطبية
$stmt = $pdo->prepare('SELECT mr.*, u.username as doctor_name FROM medical_records mr JOIN doctors d ON mr.doctor_id = d.id JOIN users u ON d.user_id = u.id WHERE mr.patient_id = ? ORDER BY mr.created_at DESC');
// Note: patient_id in medical_records table should likely reference patients.id
$stmt->execute([$patient['id']]);
$records = $stmt->fetchAll();

// جلب ملفات المريض
// Fetch files for the patient (using patient_id from patients table)
$stmt = $pdo->prepare('SELECT id, file_name, file_type, uploaded_at FROM patient_files WHERE patient_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([$patient['id']]); // Corrected to use patient ID from patients table
$patient_files = $stmt->fetchAll();

function getDoctorId($pdo, $user_id) {
// This function might not be needed anymore as doctor_id is fetched at the start
    $stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function decryptField($data, $key) {
    return openssl_decrypt($data, 'AES-128-ECB', $key);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records - Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Patient: <?= htmlspecialchars($patient['username'] ?? '') ?></h2>
                <p class="text-muted">
                    Email: <?= htmlspecialchars($patient['email'] ?? '') ?> | 
                    DOB: <?= htmlspecialchars($patient['date_of_birth'] ?? '') ?> | 
                    Gender: <?= htmlspecialchars($patient['gender'] ?? '') ?> | 
                    Blood Type: <?= htmlspecialchars($patient['blood_type'] ?? '') ?>
                </p>
            </div>
        </div>

        <div class="row">
            <!-- Add Diagnosis Form -->
            <div class="col-md-4">
                <?php if ($has_appointment || $record_to_edit): // Show form if has appointment or is editing ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0"><?= $record_to_edit ? 'Edit Diagnosis and Prescription' : 'Add Diagnosis and Prescription' ?></h4>
                    </div>
                    <div class="card-body">
                         <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                        <?php endif; ?>
                         <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                        <?php endif; ?>
                        <form method="post">
                             <?php if ($record_to_edit): // Include hidden input for record ID if editing ?>
                                 <input type="hidden" name="record_id_to_edit" value="<?= $record_to_edit['id'] ?>">
                             <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Diagnosis</label>
                                <textarea name="diagnosis" class="form-control" rows="4" required><?= $record_to_edit ? htmlspecialchars(decryptField($record_to_edit['diagnosis'], $encryption_key) ?? '') : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Prescription</label>
                                <textarea name="prescription" class="form-control" rows="4" required><?= $record_to_edit ? htmlspecialchars(decryptField($record_to_edit['prescription'], $encryption_key) ?? '') : '' ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><?= $record_to_edit ? 'Update Record' : 'Add Record' ?></button>
                        </form>
                    </div>
                </div>
                <?php else: // Hide form if no appointment and not editing ?>
                <div class="alert alert-warning mb-4" role="alert">
                    You can only add a diagnosis for patients with whom you have a scheduled or completed appointment.
                </div>
                <?php endif; ?>
            </div>

            <!-- Medical Records -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Medical Records</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($records)): ?>
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
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $rec): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rec['created_at'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($rec['doctor_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars(decryptField($rec['diagnosis'], $encryption_key) ?? '') ?></td>
                                            <td><?= htmlspecialchars(decryptField($rec['prescription'], $encryption_key) ?? '') ?></td>
                                            <td>
                                                <a href="?id=<?= $patient['id'] ?>&edit=<?= $rec['id'] ?>" 
                                                   class="btn btn-warning btn-sm me-1">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?id=<?= $patient['id'] ?>&delete=<?= $rec['id'] ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Are you sure you want to delete this record?')">
                                                    <i class="fas fa-trash"></i> Delete
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

                <!-- Patient Files -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Patient Files & Lab Results</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($patient_files)): ?>
                            <div class="alert alert-info">No files found for this patient.</div>
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
                                        <?php foreach ($patient_files as $file): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($file['file_name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($file['file_type'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($file['uploaded_at'] ?? '') ?></td>
                                                <td>
                                                    <a href="../download_file.php?file=patient_files/<?= urlencode($file['file_name']) ?>"
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

        <div class="mt-4">
            <a href="my_patients.php" class="btn btn-secondary">Back to My Patients</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">HealthCare doctor dashboard for healthcare management system.</span>
             <div class="social-icons mt-2">
                <a href="#" class="text-dark me-2"><i class="fab fa-facebook"></i></a>
                <a href="#" class="text-dark me-2"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-dark me-2"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-dark"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html> 