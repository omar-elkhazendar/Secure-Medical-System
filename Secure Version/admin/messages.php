<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$filter = isset($_GET['role']) ? $_GET['role'] : '';
$sql = "SELECT m.*, u.username as receiver_name FROM admin_messages m JOIN users u ON m.receiver_id = u.id";
$params = [];
if ($filter === 'doctor' || $filter === 'patient') {
    $sql .= " WHERE m.receiver_role = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY m.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sent Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <a href="dashboard.php" class="btn btn-primary mb-3">Back to Dashboard</a>
    <h2 class="mb-4">Messages Sent to Doctors & Patients</h2>
    <div class="mb-3">
        <a href="messages.php" class="btn btn-outline-primary btn-sm <?php if($filter=='') echo 'active'; ?>">All</a>
        <a href="messages.php?role=doctor" class="btn btn-outline-primary btn-sm <?php if($filter=='doctor') echo 'active'; ?>">Doctors</a>
        <a href="messages.php?role=patient" class="btn btn-outline-primary btn-sm <?php if($filter=='patient') echo 'active'; ?>">Patients</a>
        <a href="send_message.php" class="btn btn-success btn-sm float-end">Send New Message</a>
    </div>
    <?php if (empty($messages)): ?>
        <div class="alert alert-info">No messages sent yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>To</th>
                        <th>Role</th>
                        <th>Message</th>
                        <th>Sent At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($msg['receiver_name']); ?></td>
                            <td><?php echo ucfirst($msg['receiver_role']); ?></td>
                            <td><?php echo htmlspecialchars($msg['message']); ?></td>
                            <td><?php echo $msg['created_at']; ?></td>
                            <td>
                                <a href="edit_message.php?id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_message.php?id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html> 