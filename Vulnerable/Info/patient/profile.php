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
$stmt = $pdo->prepare('SELECT u.username, u.email, p.date_of_birth, p.gender, p.blood_type FROM users u JOIN patients p ON u.id = p.user_id WHERE u.id = ?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();
if (!$profile) {
    echo '<div class="alert alert-danger">Patient profile not found.</div>';
    exit;
}

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $blood_type = $_POST['blood_type'];
    if (!$username || !$email || !$date_of_birth || !$gender || !$blood_type) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // تحديث users
        $stmt = $pdo->prepare('UPDATE users SET username=?, email=? WHERE id=?');
        $stmt->execute([$username, $email, $user['id']]);
        // تحديث patients
        $stmt = $pdo->prepare('UPDATE patients SET date_of_birth=?, gender=?, blood_type=? WHERE user_id=?');
        $stmt->execute([$date_of_birth, $gender, $blood_type, $user['id']]);
        $success = true;
        // تحديث البيانات المعروضة
        $profile = [
            'username' => $username,
            'email' => $email,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
            'blood_type' => $blood_type
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Patient</title>
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
        <h2>My Profile</h2>
        <?php if ($success): ?>
            <div class="alert alert-success alert-custom">Profile updated successfully.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($profile['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profile['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($profile['date_of_birth']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="male" <?= $profile['gender']=='male'?'selected':'' ?>>Male</option>
                    <option value="female" <?= $profile['gender']=='female'?'selected':'' ?>>Female</option>
                    <option value="other" <?= $profile['gender']=='other'?'selected':'' ?>>Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Blood Type</label>
                <input type="text" name="blood_type" class="form-control" value="<?= htmlspecialchars($profile['blood_type']) ?>" required>
            </div>
            <button type="submit" class="btn btn-custom btn-primary">Update</button>
            <a href="dashboard.php" class="btn btn-custom btn-secondary">Back to Dashboard</a>
        </form>
    </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 