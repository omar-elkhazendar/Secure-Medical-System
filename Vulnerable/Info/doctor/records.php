<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
$encryption_key = 'my_super_secret_key_123';

session_start();
$headers = function_exists('getallheaders') ? getallheaders() : [];
if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
} elseif (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];
} else {
    http_response_code(401);
    echo json_encode(['error' => 'No token provided']);
    exit;
}
$user = Auth::validateToken($token);
if (!$user || $user['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient permissions']);
    exit;
}
// Get doctor id
$stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
$stmt->execute([$user['user_id']]);
$doctor_id = $stmt->fetchColumn();
if (!$doctor_id) {
    echo '<div class="alert alert-danger">Doctor profile not found.</div>';
    exit;
}
// Get all medical records for this doctor
$result = $pdo->query("SELECT mr.*, p.id as patient_id, u.username as patient_name FROM medical_records mr JOIN patients p ON mr.patient_id = p.id JOIN users u ON p.user_id = u.id WHERE mr.doctor_id = '$doctor_id' ORDER BY mr.created_at DESC");
$records = $result->fetchAll();
function decryptField($data, $key) {
    return openssl_decrypt($data, 'AES-128-ECB', $key);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>All Medical Records</h2>
        <table class="table table-bordered">
            <thead><tr><th>Date</th><th>Patient</th><th>Diagnosis</th><th>Prescription</th></tr></thead>
            <tbody>
                <?php foreach ($records as $rec): ?>
                <tr>
                    <td><?= $rec['created_at'] ?></td>
                    <td><?= $rec['patient_name'] ?></td>
                    <td><?= decryptField($rec['diagnosis'], $encryption_key) ?></td>
                    <td><?= decryptField($rec['prescription'], $encryption_key) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 