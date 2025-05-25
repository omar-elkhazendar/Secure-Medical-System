<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;

session_start();
// حماية الصفحة
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

// جلب secret الحالي
$stmt = $pdo->prepare('SELECT two_factor_secret FROM users WHERE id = ?');
$stmt->execute([$user['user_id']]);
$current_secret = $stmt->fetchColumn();

$tfa = new TwoFactorAuth('Medical System');
$qrCodeUrl = '';
$success = false;
$error = '';

// تفعيل 2FA
if (isset($_POST['enable_2fa'])) {
    $secret = $tfa->createSecret();
    $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($user['username'], $secret);
    $_SESSION['pending_2fa_secret'] = $secret;
}
// حفظ secret بعد التحقق من الكود
if (isset($_POST['verify_2fa']) && isset($_SESSION['pending_2fa_secret'])) {
    $code = $_POST['code'];
    $secret = $_SESSION['pending_2fa_secret'];
    if ($tfa->verifyCode($secret, $code)) {
        $stmt = $pdo->prepare('UPDATE users SET two_factor_secret = ? WHERE id = ?');
        $stmt->execute([$secret, $user['user_id']]);
        unset($_SESSION['pending_2fa_secret']);
        $success = true;
        $current_secret = $secret;
    } else {
        $error = 'Invalid code. Please try again.';
        $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($user['username'], $secret);
    }
}
// تعطيل 2FA
if (isset($_POST['disable_2fa'])) {
    $stmt = $pdo->prepare('UPDATE users SET two_factor_secret = NULL WHERE id = ?');
    $stmt->execute([$user['user_id']]);
    $current_secret = null;
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Settings - Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Two-Factor Authentication (2FA)</h2>
        <?php if ($success): ?>
            <div class="alert alert-success">Operation successful.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($current_secret): ?>
            <div class="alert alert-info">2FA is enabled for your account.</div>
            <form method="post">
                <button type="submit" name="disable_2fa" class="btn btn-danger">Disable 2FA</button>
            </form>
        <?php elseif (isset($_SESSION['pending_2fa_secret']) && $qrCodeUrl): ?>
            <div class="mb-3">
                <p>Scan this QR code with Google Authenticator, then enter the code below:</p>
                <img src="<?= $qrCodeUrl ?>" alt="QR Code">
            </div>
            <form method="post">
                <div class="mb-2">
                    <label>Enter Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <button type="submit" name="verify_2fa" class="btn btn-success">Verify & Enable</button>
            </form>
        <?php else: ?>
            <form method="post">
                <button type="submit" name="enable_2fa" class="btn btn-primary">Enable 2FA with Google Authenticator</button>
            </form>
        <?php endif; ?>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 