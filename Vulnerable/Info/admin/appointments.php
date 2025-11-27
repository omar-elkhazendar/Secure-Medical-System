<?php
// admin/appointments.php
require_once '../config/database.php';
require_once '../includes/auth.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
// جلب المواعيد مع أسماء المرضى والأطباء
$stmt = $pdo->query('SELECT a.*, 
    up.username AS patient_name, 
    ud.username AS doctor_name
FROM appointments a
JOIN patients p ON a.patient_id = p.id
JOIN users up ON p.user_id = up.id
JOIN doctors d ON a.doctor_id = d.id
JOIN users ud ON d.user_id = ud.id
ORDER BY a.appointment_date DESC');
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4">Appointments</h2>
        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">No appointments found.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['patient_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($a['doctor_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($a['appointment_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars(ucfirst($a['status'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($a['notes'] ?? '') ?></td>
                        <td><?= htmlspecialchars($a['created_at'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <a href="dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 