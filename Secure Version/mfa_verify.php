<?php
session_start();
require_once 'config/database.php';
require_once 'includes/mfa.php';

// Check if user needs MFA verification
if (!isset($_SESSION['user_id']) || !isset($_SESSION['mfa_required'])) {
    header('Location: login.php');
    exit();
}

$mfa = new MFA($pdo);
$user_id = $_SESSION['user_id'];
$error = '';

// Get user's MFA secret
$secret = $mfa->getSecret($user_id);
if (!$secret) {
    // If no secret found, redirect to login
    unset($_SESSION['user_id']);
    unset($_SESSION['mfa_required']);
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    
    if (empty($code)) {
        $error = 'Please enter the verification code.';
    } else {
        if ($mfa->verifyCode($secret, $code)) {
            // MFA verification successful
            unset($_SESSION['mfa_required']);
            
            // Get user data
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            header('Location: patient/dashboard.php');
            exit();
        } else {
            $error = 'Invalid verification code. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA Verification - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare
            </a>
        </div>
    </nav>

    <!-- MFA Verification Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card-custom p-4" data-aos="fade-up">
                        <div class="text-center mb-4">
                            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                            <h2 class="section-title">Two-Factor Authentication</h2>
                            <p class="text-muted">Enter the verification code from your authenticator app</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-custom">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="mfa_verify.php">
                            <div class="mb-3">
                                <label class="form-label">Verification Code</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="text" class="form-control" name="code" placeholder="Enter 6-digit code" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-custom btn-primary w-100">
                                <i class="fas fa-check me-2"></i>Verify Code
                            </button>
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
    </script>
</body>
</html> 