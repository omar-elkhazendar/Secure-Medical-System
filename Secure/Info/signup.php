<?php
require_once 'config/database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $date_of_birth = $_POST['date_of_birth'];
    $blood_type = $_POST['blood_type'];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($date_of_birth) || empty($blood_type)) {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    
    // Password validation
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    if (!preg_match($password_pattern, $password)) {
        $errors[] = 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
    }
    
    // Password confirmation
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Check if username or email exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Username or email already exists.';
    }

    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Insert into users with hashed password, role is always 'patient'
            $stmt = $pdo->prepare('INSERT INTO users (username, password, email, role, is_active) VALUES (?, ?, ?, "patient", 1)');
            $stmt->execute([$username, $hashed_password, $email]);
            $user_id = $pdo->lastInsertId();
            
            // Insert into patients
            $stmt = $pdo->prepare('INSERT INTO patients (user_id, date_of_birth, blood_type) VALUES (?, ?, ?)');
            $stmt->execute([$user_id, $date_of_birth, $blood_type]);
            
            // Log the activity
            logActivity($pdo, $user_id, 'signup', 'User signed up via manual registration');
            
            $pdo->commit();
            
            // Set session data for MFA setup
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $username;
            
            // Redirect to MFA setup
            header('Location: mfa_setup.php');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Registration failed. Please try again.';
            error_log("Registration Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-image: url('uploads/stethoscope-copy-space.jpg'); /* Corrected image path */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .card-custom {
             background-color: rgba(255, 255, 255, 0.95);
             z-index: 1;
        }
        .password-requirements {
            font-size: 0.95rem;
            color: #6c757d;
            margin-top: 10px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
        }
        .requirement {
            margin: 6px 0;
            display: flex;
            align-items: center;
            transition: color 0.3s;
        }
        .requirement i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .requirement.valid {
            color: var(--success-color);
        }
        .requirement.invalid {
            color: var(--danger-color);
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

    <!-- Sign Up Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card-custom p-4" data-aos="fade-up">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus fa-3x text-primary mb-3" style="display: none;"></i>
                            <h2 class="section-title">Create Account</h2>
                            <p class="text-muted">Join our healthcare community today.</p>
                        </div>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-custom">
                                <?php foreach ($errors as $error) echo '<div>' . $error . '</div>'; ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="signup.php">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="username" placeholder="Enter your username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" placeholder="Enter your email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Create a password" required autocomplete="new-password">
                                </div>
                                <div class="password-requirements mt-2" id="password-requirements">
                                    <div class="requirement" id="req-length"><i class="fas fa-times"></i> At least 8 characters</div>
                                    <div class="requirement" id="req-uppercase"><i class="fas fa-times"></i> One uppercase letter</div>
                                    <div class="requirement" id="req-lowercase"><i class="fas fa-times"></i> One lowercase letter</div>
                                    <div class="requirement" id="req-number"><i class="fas fa-times"></i> One number</div>
                                    <div class="requirement" id="req-special"><i class="fas fa-times"></i> One special character</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date of Birth</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control" name="date_of_birth" required value="<?= isset($date_of_birth) ? htmlspecialchars($date_of_birth) : '' ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Blood Type</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tint"></i></span>
                                    <input type="text" class="form-control" name="blood_type" placeholder="Enter your blood type" required value="<?= isset($blood_type) ? htmlspecialchars($blood_type) : '' ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-custom btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                            <div class="text-center">
                                <p class="mb-0">Already have an account? 
                                    <a href="login.php" class="text-primary text-decoration-none">Login</a>
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
        // Password requirements live validation
        const passwordInput = document.getElementById('password');
        const reqLength = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-uppercase');
        const reqLower = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');
        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            // Length
            if(val.length >= 8) {
                reqLength.classList.add('valid');
                reqLength.classList.remove('invalid');
                reqLength.querySelector('i').className = 'fas fa-check';
            } else {
                reqLength.classList.remove('valid');
                reqLength.classList.add('invalid');
                reqLength.querySelector('i').className = 'fas fa-times';
            }
            // Uppercase
            if(/[A-Z]/.test(val)) {
                reqUpper.classList.add('valid');
                reqUpper.classList.remove('invalid');
                reqUpper.querySelector('i').className = 'fas fa-check';
            } else {
                reqUpper.classList.remove('valid');
                reqUpper.classList.add('invalid');
                reqUpper.querySelector('i').className = 'fas fa-times';
            }
            // Lowercase
            if(/[a-z]/.test(val)) {
                reqLower.classList.add('valid');
                reqLower.classList.remove('invalid');
                reqLower.querySelector('i').className = 'fas fa-check';
            } else {
                reqLower.classList.remove('valid');
                reqLower.classList.add('invalid');
                reqLower.querySelector('i').className = 'fas fa-times';
            }
            // Number
            if(/[0-9]/.test(val)) {
                reqNumber.classList.add('valid');
                reqNumber.classList.remove('invalid');
                reqNumber.querySelector('i').className = 'fas fa-check';
            } else {
                reqNumber.classList.remove('valid');
                reqNumber.classList.add('invalid');
                reqNumber.querySelector('i').className = 'fas fa-times';
            }
            // Special
            if (/[!@#$%^&*(),.?\":{}|<>\[\]\\\-_+]/.test(val)) {
                reqSpecial.classList.add('valid');
                reqSpecial.classList.remove('invalid');
                reqSpecial.querySelector('i').className = 'fas fa-check';
            } else {
                reqSpecial.classList.remove('valid');
                reqSpecial.classList.add('invalid');
                reqSpecial.querySelector('i').className = 'fas fa-times';
            }
        });
    </script>
</body>
</html> 