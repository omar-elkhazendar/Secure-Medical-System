<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}
$user_id = $_SESSION['user']['id'];
$messages = $pdo->prepare("SELECT * FROM admin_messages WHERE receiver_id = ? AND receiver_role = 'patient' ORDER BY created_at DESC");
$messages->execute([$user_id]);
$messages = $messages->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">Messages from Admin</h2>
    <?php if (empty($messages)): ?>
        <div class="alert alert-info">No messages yet.</div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($messages as $msg): ?>
                <li class="list-group-item">
                    <div><strong>Message:</strong> <?php echo htmlspecialchars($msg['message']); ?></div>
                    <div class="text-muted small">Sent at: <?php echo $msg['created_at']; ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html> 