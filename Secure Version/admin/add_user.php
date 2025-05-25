<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');

    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($role)) {
        $message = 'Please fill in all fields.';
        $message_type = 'danger';
    } elseif ($password !== $confirm_password) {
        $message = 'Password and Confirm Password do not match.';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
        $message_type = 'danger';
    } elseif (!in_array($role, ['doctor', 'admin', 'patient'])) {
        $message = 'Invalid role selected.';
        $message_type = 'danger';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $message = 'Username or Email already exists.';
                $message_type = 'danger';
            } else {
                // Insert into users table
                $pdo->beginTransaction();

                $stmt = $pdo->prepare('INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, $hashed_password, $email, $role]);
                $user_id = $pdo->lastInsertId();

                // If role is doctor or patient, insert into respective table
                if ($role === 'doctor') {
                    $stmt = $pdo->prepare('INSERT INTO doctors (user_id) VALUES (?)');
                    $stmt->execute([$user_id]);
                } elseif ($role === 'patient') {
                     $stmt = $pdo->prepare('INSERT INTO patients (user_id) VALUES (?)');
                     $stmt->execute([$user_id]);
                }

                $pdo->commit();

                $message = 'User added successfully!';
                $message_type = 'success';
                 // Log activity
                $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
                    ->execute([$_SESSION['user']['id'], 'add_user', 'Added user: ' . $username . ' with role: ' . $role . ' and email: ' . $email, $_SERVER['REMOTE_ADDR']]);
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Error adding user: ' . $e->getMessage();
            $message_type = 'danger';
             // Log activity for failed attempt
            $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
                ->execute([$_SESSION['user']['id'], 'add_user_failed', 'Attempted to add user: ' . $username . ' with role: ' . $role . ' and email: ' . $email . ' Error: ' . $e->getMessage(), $_SERVER['REMOTE_ADDR']]);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Healthcare Management System</title>
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
                HealthCare Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="patients.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="upload_patient_file.php">Upload Patient File</a></li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMessages" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Messages
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMessages">
                            <li><a class="dropdown-item" href="messages.php">View Messages</a></li>
                            <li><a class="dropdown-item" href="send_message.php">Send Message</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
                     <li class="nav-item active"><a class="nav-link" href="add_user.php">Add User</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <h2>Add New User</h2>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>

            <div class="card mt-4">
                <div class="card-body">
                    <form action="add_user.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="doctor">Doctor</option>
                                <option value="admin">Admin</option>
                                <option value="patient">Patient</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </form>
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
                    <p>Admin dashboard for managing the system.</p>
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