<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
// جلب جميع الأطباء والمرضى
$doctors = $pdo->query("SELECT id, username FROM users WHERE role = 'doctor'")->fetchAll(PDO::FETCH_ASSOC);
$patients = $pdo->query("SELECT id, username FROM users WHERE role = 'patient'")->fetchAll(PDO::FETCH_ASSOC);
$message_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $receiver_role = $_POST['receiver_role'];
    $message = trim($_POST['message']);
    $sender_id = $_SESSION['user']['id'];
    if ($receiver_id && $receiver_role && $message) {
        $stmt = $pdo->prepare("INSERT INTO admin_messages (sender_id, receiver_id, receiver_role, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sender_id, $receiver_id, $receiver_role, $message]);
        $message_sent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .main-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(44,62,80,0.08);
            padding: 32px 28px;
            max-width: 520px;
            margin: 0 auto;
        }
        h2.mb-4 {
            font-weight: 800;
            color: #222a5c;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(90deg, #283eec 0%, #1e62d0 100%);
            border: none;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .btn-primary:hover { background: #1e62d0; }
        .btn-back {
            background: #fff;
            color: #283eec;
            border: 1px solid #283eec;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .btn-back:hover {
            background: #283eec;
            color: #fff;
        }
        .alert-dismissible .btn-close {
            position: absolute;
            top: 10px;
            right: 16px;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <a href="dashboard.php" class="btn btn-back mb-3"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
    <h2 class="mb-4">Send Message to Doctor or Patient</h2>
    <?php if ($message_sent): ?>
        <div class="alert alert-success alert-dismissible fade show position-relative" role="alert">
            Message sent successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <form method="post" class="main-card mt-3">
        <div class="mb-3">
            <label for="receiver_role" class="form-label">Send To</label>
            <select name="receiver_role" id="receiver_role" class="form-select" required onchange="updateReceivers()">
                <option value="">Choose...</option>
                <option value="doctor">Doctor</option>
                <option value="patient">Patient</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="receiver_id" class="form-label">User</label>
            <select name="receiver_id" id="receiver_id" class="form-select" required>
                <option value="">Select user...</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/4a2e1c7e3b.js" crossorigin="anonymous"></script>
<script>
    const doctors = <?php echo json_encode($doctors); ?>;
    const patients = <?php echo json_encode($patients); ?>;
    function updateReceivers() {
        const role = document.getElementById('receiver_role').value;
        const receiverSelect = document.getElementById('receiver_id');
        receiverSelect.innerHTML = '<option value="">Select user...</option>';
        let users = [];
        if (role === 'doctor') users = doctors;
        if (role === 'patient') users = patients;
        users.forEach(user => {
            const opt = document.createElement('option');
            opt.value = user.id;
            opt.textContent = user.username;
            receiverSelect.appendChild(opt);
        });
    }
</script>
</body>
</html> 