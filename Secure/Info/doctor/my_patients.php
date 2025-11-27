<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();
// Check if user is logged in and has the doctor role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    // If not logged in or not a doctor, redirect to login page
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];

// Get doctor id
$stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
$stmt->execute([$user['id']]);
$doctor_id = $stmt->fetchColumn();
if (!$doctor_id) {
    echo '<div class="alert alert-danger">Doctor profile not found.</div>';
    exit;
}

// Fetch patients with appointments for this doctor
$where = "WHERE a.doctor_id = ?";
$params = [$doctor_id];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where .= " AND (u.username LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search;
}

$sql = "SELECT DISTINCT u.id as user_id, p.id as patient_id, u.username as patient_name, u.email,
       (SELECT MAX(appointment_date) FROM appointments
        WHERE patient_id = p.id AND doctor_id = ? AND status IN ('scheduled', 'completed')) as last_appointment_time
       FROM patients p
       JOIN users u ON p.user_id = u.id
       JOIN appointments a ON p.id = a.patient_id
       JOIN doctors d ON a.doctor_id = d.id
       $where
       ORDER BY u.username";

$stmt = $pdo->prepare($sql);

// Adjust parameters based on whether search is active
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $stmt->execute([$doctor_id, $search, $doctor_id]);
} else {
    $stmt->execute([$doctor_id, $doctor_id]);
}

$patients = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Patients with Appointments - Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .patients-card {
            background: linear-gradient(135deg, #ADD8E6 0%, #87CEEB 100%); /* Light blue gradient */
            color: #333;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .patients-card h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .table-custom {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            overflow: hidden;
        }
        .table-custom th {
            background: #4CAF50;
            color: white;
            font-weight: 500;
            padding: 12px;
        }
        .table-custom td {
            padding: 12px;
            vertical-align: middle;
        }
        .btn-custom {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .search-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .search-section input {
            border-radius: 50px;
            padding: 8px 15px;
            border: 1px solid #ddd;
        }
        .patient-info {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .patient-info h5 {
            color: #333;
            margin-bottom: 10px;
        }
        .patient-info p {
            margin-bottom: 5px;
            color: #666;
        }
    </style>
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

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="patients-card">
                <h3><i class="fas fa-users me-2"></i>My Patients with Appointments</h3>

                <!-- Search Section -->
                <div class="search-section mb-4">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Search by patient name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-custom btn-primary">Search</button>
                            <a href="my_patients.php" class="btn btn-custom btn-light">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Email</th>
                                <th>Last Appointment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($patients)):
                                echo '<tr><td colspan="4" class="text-center">No patients with appointments found.</td></tr>';
                            else:
                                foreach($patients as $row) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['patient_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    // Display last appointment date/time if available
                                    echo "<td>" . ($row['last_appointment_time'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($row['last_appointment_time']))) : 'N/A') . "</td>";
                                    echo "<td>
                                            <a href='view_patient.php?id=" . $row['patient_id'] . "' class='btn btn-custom btn-success btn-sm'>
                                                <i class='fas fa-stethoscope me-1'></i> Add Diagnosis
                                            </a>
                                        </td>";
                                    echo "</tr>";
                                }
                            endif;
                            ?>
                        </tbody>
                    </table>
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
                    <p>Doctor patient management system.</p>
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