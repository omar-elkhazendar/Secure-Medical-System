<?php
require_once 'config/database.php';

// Vulnerable to XSS - no input sanitization
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user']['id'];
    
    // Vulnerable query - no prepared statements
    $query = "INSERT INTO comments (user_id, comment) VALUES ($user_id, '$comment')";
    $pdo->query($query);
}

// Fetch comments - vulnerable to XSS
$query = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC";
$comments = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comments</title>
</head>
<body>
    <h1>Comments</h1>
    
    <form method="POST">
        <textarea name="comment" required></textarea>
        <button type="submit">Post Comment</button>
    </form>
    
    <div class="comments">
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <strong><?php echo $comment['username']; ?></strong>
                <p><?php echo $comment['comment']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html> 