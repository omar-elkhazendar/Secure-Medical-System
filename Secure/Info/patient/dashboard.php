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
$stmt = $pdo->prepare('SELECT u.username, u.email, p.date_of_birth, p.gender, p.blood_type, p.id as patient_id FROM users u JOIN patients p ON u.id = p.user_id WHERE u.id = ?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();
if (!$profile) {
    echo '<div class="alert alert-danger">Patient profile not found.</div>';
    exit;
}
$patient_id = $profile['patient_id'];

// عدد المواعيد القادمة
$stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND appointment_date >= NOW()');
$stmt->execute([$patient_id]);
$upcoming_count = $stmt->fetchColumn();

// آخر وصفة طبية
$stmt = $pdo->prepare('SELECT mr.*, u.username as doctor_name FROM medical_records mr JOIN doctors d ON mr.doctor_id = d.id JOIN users u ON d.user_id = u.id WHERE mr.patient_id = ? ORDER BY mr.created_at DESC LIMIT 1');
$stmt->execute([$patient_id]);
$last_prescription = $stmt->fetch();
function decryptField($data, $key) {
    return openssl_decrypt($data, 'AES-128-ECB', $key);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Healthcare Management System</title>
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
            /* min-height: 220px; */
            height: 420px; /* Increased fixed height slightly more */
            display: flex;
            flex-direction: column;
            justify-content: flex-start; /* Align content from the top */
            align-items: flex-start;
            padding: 25px;
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            /* flex-grow: 1; */ /* Not needed with fixed height */
            overflow-y: auto; /* Add scroll for overflow */
        }
        .dashboard-card.bg1 {
            background: linear-gradient(135deg, #4A0E8C 0%, #7B24C1 100%);
            color: #fff;
        }
        .dashboard-card.bg2 {
            background: linear-gradient(135deg, #00796B 0%, #4DB6AC 100%);
            color: #fff;
        }
        .dashboard-card.bg3 {
            background: linear-gradient(135deg, #D32F2F 0%, #EF5350 100%);
            color: #fff;
        }
        .dashboard-card .dashboard-icon {
            font-size: 2.8rem;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.8);
        }
        .dashboard-card .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #fff;
        }
        .dashboard-card .card-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #fff;
        }
        .dashboard-card .btn-custom {
            margin-top: auto;
            align-self: flex-start;
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 12px 25px; /* Increased padding */
            font-size: 1rem; /* Ensure readable font size */
        }
        .dashboard-card .btn-custom:hover {
            background-color: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }
        .dashboard-card div:not(.dashboard-icon):not(.card-title):not(.card-value) {
            font-size: 1rem;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.9);
        }
        @media (max-width: 991px) {
            .dashboard-card { height: 380px; /* Adjusted height for medium screens */ padding: 20px; }
            .dashboard-card .dashboard-icon { font-size: 2.2rem; margin-bottom: 15px;}
            .dashboard-card .card-title { font-size: 1.1rem; margin-bottom: 8px;}
            .dashboard-card .card-value { font-size: 2rem; margin-bottom: 10px;}
            .dashboard-card .btn-custom { padding: 10px 20px; font-size: 0.9rem; width: 180px;}
            .dashboard-card div:not(.dashboard-icon):not(.card-title):not(.card-value) { font-size: 0.9rem; margin-bottom: 5px;}
        }
        @media (max-width: 767px) {
            .dashboard-card { height: auto; /* Allow height to adjust on small screens */ min-height: 220px; margin-bottom: 20px; overflow-y: visible; }
            .dashboard-card .btn-custom { margin-top: 15px; width: 100%; padding: 10px 20px; text-align: center;}
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
             <!-- Add toggle button for responsive design if needed -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="prescriptions.php">Prescriptions</a></li>
                    <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="patient_records.php">My Records</a></li>
                    <?php if (isset($user)): ?>
                        <li class="nav-item d-flex align-items-center">
                            <?php if (!empty($user['picture'])): ?>
                                <img src="<?= htmlspecialchars($user['picture']) ?>" alt="User Avatar" class="rounded-circle me-2" style="width: 30px; height: 30px;">
                            <?php endif; ?>
                            <span class="navbar-text me-3 text-black">Hello, <?= htmlspecialchars($user['username'] ?? $user['name']) ?></span>
                             <a class="nav-link btn btn-outline-light" href="../logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex align-items-center mb-4">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="User Avatar" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                <?php endif; ?>
                <h2 class="mb-0">Welcome, <?= htmlspecialchars($profile['username']) ?></h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card-custom dashboard-card bg1 shadow animate-fadeInUp">
                        <div class="dashboard-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="card-title">Upcoming Appointments</div>
                        <div class="card-value"><?= (int)$upcoming_count ?></div>
                        <a href="appointments.php" class="btn btn-custom btn-light mt-auto"><i class="fas fa-arrow-right me-2"></i>View Appointments</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom dashboard-card bg2 shadow animate-fadeInUp">
                        <div class="dashboard-icon"><i class="fas fa-prescription-bottle-alt"></i></div>
                        <div class="card-title">Last Prescription</div>
                        <?php if ($last_prescription): ?>
                            <div><b>Doctor:</b> <?= htmlspecialchars($last_prescription['doctor_name']) ?></div>
                            <div><b>Date:</b> <?= htmlspecialchars($last_prescription['created_at']) ?></div>
                            <div><b>Prescription:</b> <?= htmlspecialchars(decryptField($last_prescription['prescription'], $encryption_key)) ?></div>
                        <?php else: ?>
                            <div>No prescriptions yet.</div>
                        <?php endif; ?>
                        <a href="prescriptions.php" class="btn btn-custom btn-light mt-auto"><i class="fas fa-arrow-right me-2"></i>View All</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom dashboard-card bg3 shadow animate-fadeInUp">
                        <div class="dashboard-icon"><i class="fas fa-user"></i></div>
                        <div class="card-title">Profile</div>
                        <div><b>Email:</b> <?= htmlspecialchars($profile['email']) ?></div>
                        <div><b>DOB:</b> <?= htmlspecialchars($profile['date_of_birth'] ?? 'Not set') ?></div>
                        <div><b>Gender:</b> <?= htmlspecialchars($profile['gender'] ?? 'Not set') ?></div>
                        <div><b>Blood Type:</b> <?= htmlspecialchars($profile['blood_type'] ?? 'Not set') ?></div>
                        <a href="profile.php" class="btn btn-custom btn-light mt-auto"><i class="fas fa-edit me-2"></i>Edit Profile</a>
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
                    <p>Patient dashboard for healthcare management system.</p>
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