<?php
session_start();
require_once 'config/database.php';
require_once 'includes/mfa.php';

// Check if user is coming from signup process
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$mfa = new MFA($pdo);
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$name = $_SESSION['name'];

// If MFA is already enabled, redirect to dashboard
if ($mfa->isMFAEnabled($user_id)) {
    header('Location: patient/dashboard.php');
    exit();
}

$error = '';
$success = false;

// Generate new secret if not exists
$secret = $mfa->getSecret($user_id);
if (!$secret) {
    $secret = $mfa->createSecret();
    $mfa->saveSecret($user_id, $secret);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    
    if (empty($code)) {
        $error = 'Please enter the verification code.';
    } else {
        if ($mfa->verifyCode($secret, $code)) {
            // Enable MFA for the user
            if ($mfa->enableMFA($user_id)) {
                $success = true;
                // Redirect to dashboard after 2 seconds
                header('Refresh: 2; URL=patient/dashboard.php');
            } else {
                $error = 'Failed to enable MFA. Please try again.';
            }
        } else {
            $error = 'Invalid verification code. Please try again.';
        }
    }
}

// Get QR code for display
$qrCode = $mfa->getQRCodeImageAsDataUri($secret, $email);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup MFA - Healthcare Management System</title>
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

    <!-- MFA Setup Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card-custom p-4" data-aos="fade-up">
                        <div class="text-center mb-4">
                            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                            <h2 class="section-title">Setup Two-Factor Authentication</h2>
                            <p class="text-muted">Scan the QR code with your authenticator app</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-custom">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-custom">
                                MFA has been successfully enabled! Redirecting to dashboard...
                            </div>
                        <?php else: ?>
                            <div class="text-center mb-4">
                                <img src="<?php echo $qrCode; ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                                <p class="text-muted">Scan this QR code with your authenticator app</p>
                                <p class="text-muted small">Or enter this code manually: <code><?php echo $secret; ?></code></p>
                            </div>

                            <form method="POST" action="mfa_setup.php">
                                <div class="mb-3">
                                    <label class="form-label">Verification Code</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="text" class="form-control" name="code" placeholder="Enter 6-digit code" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-custom btn-primary w-100">
                                    <i class="fas fa-check me-2"></i>Verify & Enable MFA
                                </button>
                            </form>
                        <?php endif; ?>
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