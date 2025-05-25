<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])) {
    $role = $_SESSION['user']['role'];
    $redirect_path = '';
    
    switch($role) {
        case 'admin':
            $redirect_path = 'admin/dashboard.php';
            break;
        case 'doctor':
            $redirect_path = 'doctor/dashboard.php';
            break;
        case 'patient':
            $redirect_path = 'patient/dashboard.php';
            break;
        default:
            // If role is not recognized, clear session and redirect to login
            session_unset();
            session_destroy();
            header('Location: login.php');
            exit;
    }
    
    // Check if user has the required profile
    if ($role === 'doctor') {
        $stmt = $pdo->prepare('SELECT d.id FROM doctors d WHERE d.user_id = ?');
        $stmt->execute([$_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            session_unset();
            session_destroy();
            $_SESSION['error'] = "Doctor profile not found. Please contact administrator.";
            header('Location: login.php');
            exit;
        }
    } elseif ($role === 'patient') {
        $stmt = $pdo->prepare('SELECT p.id FROM patients p WHERE p.user_id = ?');
        $stmt->execute([$_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            // Create patient profile if it doesn't exist
            $stmt = $pdo->prepare('INSERT INTO patients (user_id) VALUES (?)');
            $stmt->execute([$_SESSION['user']['id']]);
        }
    }
    
    header('Location: ' . $redirect_path);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        // Get user from database using email
        $stmt = $pdo->prepare("SELECT u.*, d.id as doctor_id, d.specialization, p.id as patient_id 
                              FROM users u 
                              LEFT JOIN doctors d ON u.id = d.user_id 
                              LEFT JOIN patients p ON u.id = p.user_id 
                              WHERE u.email = ? AND u.is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // For doctors, verify they have a doctor profile
            if ($user['role'] === 'doctor' && !$user['doctor_id']) {
                $error = "Doctor profile not found. Please contact administrator.";
            } 
            // For patients, verify they have a patient profile
            else if ($user['role'] === 'patient' && !$user['patient_id']) {
                // Create patient profile if it doesn't exist
                $stmt = $pdo->prepare('INSERT INTO patients (user_id) VALUES (?)');
                $stmt->execute([$user['id']]);
            }
            
            if (empty($error)) {
                // Prepare user data for session
                $user_data = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'name' => $user['name'] ?? $user['username']
                ];
                
                // Set session
                $_SESSION['user'] = $user_data;
                
                // Log the successful login
                logActivity($pdo, $user['id'], 'login', 'Successful login', Auth::getClientIp());
                
                // Redirect based on role
                switch($user['role']) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'doctor':
                        header('Location: doctor/dashboard.php');
                        break;
                    case 'patient':
                        header('Location: patient/dashboard.php');
                        break;
                    default:
                        $error = "Invalid user role";
                        break;
                }
                if (!empty($error)) {
                    session_unset();
                    session_destroy();
                } else {
                    exit;
                }
            }
        } else {
            // Log failed login attempt
            if ($user) {
                logActivity($pdo, $user['id'], 'failed_login', 'Invalid password', Auth::getClientIp());
            }
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = "An error occurred. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Add custom styles here */
        body {
            background-image: url('uploads/stethoscope-copy-space.jpg'); /* Corrected image path */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        /* You might want to add a background to the card for better readability */
        .card-custom {
             background-color: rgba(255, 255, 255, 0.95); /* Semi-transparent white background */
             z-index: 1; /* Ensure card is above background image */
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare
            </a>
            <!-- Add toggle button for responsive design if needed -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li> <!-- Assuming anchor links -->
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li> <!-- Assuming anchor links -->
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li> <!-- Assuming anchor links -->
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <!-- Add image here, outside the card -->
                    <!-- Remove the previous stethoscope image -->
                    <!-- <img src="../uploads/stethoscope-copy-space.jpg" alt="Stethoscope" class="img-fluid mb-3" style="width: 100%; height: auto;">-->
                    <div class="card-custom p-4" data-aos="fade-up">
                        <div class="text-center mb-4">
                            <!-- Remove image from here -->
                            <i class="fas fa-user-circle fa-3x text-primary mb-3" style="display: none;"></i> <!-- Keep hidden icon -->
                            <h2 class="section-title">Login</h2>
                            <p class="text-muted">Welcome back! Please login to your account.</p>
                        </div>
                        <!-- Social Login Icon Buttons -->
                        <div class="mb-4 text-center">
                            <div class="d-flex justify-content-center align-items-center gap-3">
                                <a href="oauth_google.php" class="btn btn-light border rounded-circle p-3 shadow-sm" title="Sign in with Google" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fab fa-google fa-lg text-danger"></i>
                                </a>
                                <a href="oauth_github.php" class="btn btn-light border rounded-circle p-3 shadow-sm" title="Sign in with GitHub" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fab fa-github fa-lg text-dark"></i>
                                </a>
                                <a href="oauth_okta.php" class="btn btn-light border rounded-circle p-3 shadow-sm" title="Sign in with Okta" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-shield fa-lg text-primary"></i>
                                </a>
                            </div>
                            <div class="my-2 text-muted small">Sign in with</div>
                        </div>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
                                    <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            
                            <button type="submit" class="btn btn-custom btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                            
                            <div class="text-center">
                                <p class="mb-0">Don't have an account? 
                                    <a href="signup.php" class="text-primary text-decoration-none">Sign Up</a>
                                </p>
                            </div>
                        </form>
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
                    <p>Your trusted healthcare management solution.</p>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html> 