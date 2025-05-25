<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('Location: messages.php');
    exit;
}
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM admin_messages WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$message) {
    echo '<div class="alert alert-danger">Message not found.</div>';
    exit;
}
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_message = trim($_POST['message']);
    if ($new_message) {
        $stmt = $pdo->prepare("UPDATE admin_messages SET message = ? WHERE id = ?");
        $stmt->execute([$new_message, $id]);
        $success = true;
        $message['message'] = $new_message;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <a href="dashboard.php" class="btn btn-primary mb-3">Back to Dashboard</a>
    <h2 class="mb-4">Edit Message</h2>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Message updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <form method="post" class="card p-4 shadow-sm" style="max-width:500px;">
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea name="message" id="message" class="form-control" rows="4" required><?php echo htmlspecialchars($message['message']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="messages.php" class="btn btn-secondary ms-2">Back</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 