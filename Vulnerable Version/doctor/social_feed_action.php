<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user = $_SESSION['user'];
$stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
$stmt->execute([$user['id']]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

$action = $_POST['action'] ?? '';
if ($action === 'like') {
    $post_id = intval($_POST['post_id']);
    // Check if already liked
    $stmt = $pdo->prepare('SELECT id FROM post_likes WHERE post_id = ? AND doctor_id = ?');
    $stmt->execute([$post_id, $doctor_id]);
    $like = $stmt->fetch();
    if ($like) {
        // Unlike
        $stmt = $pdo->prepare('DELETE FROM post_likes WHERE id = ?');
        $stmt->execute([$like['id']]);
        $liked = false;
    } else {
        // Like
        $stmt = $pdo->prepare('INSERT INTO post_likes (post_id, doctor_id) VALUES (?, ?)');
        $stmt->execute([$post_id, $doctor_id]);
        $liked = true;
    }
    // Return new like count
    $stmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM post_likes WHERE post_id = ?');
    $stmt->execute([$post_id]);
    $count = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);
    exit;
}
if ($action === 'comment') {
    $post_id = intval($_POST['post_id']);
    $comment = trim($_POST['comment'] ?? '');
    if ($comment !== '') {
        $stmt = $pdo->prepare('INSERT INTO comments (post_id, doctor_id, comment) VALUES (?, ?, ?)');
        $stmt->execute([$post_id, $doctor_id, $comment]);
        // Get comment info
        $comment_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT c.*, u.username, d.specialization FROM comments c JOIN doctors d ON c.doctor_id = d.id JOIN users u ON d.user_id = u.id WHERE c.id = ?');
        $stmt->execute([$comment_id]);
        $row = $stmt->fetch();
        echo json_encode(['success' => true, 'comment' => [
            'id' => $row['id'],
            'username' => $row['username'],
            'specialization' => $row['specialization'],
            'comment' => $row['comment'],
            'created_at' => $row['created_at'],
            'doctor_id' => $row['doctor_id'],
        ]]);
        exit;
    }
    echo json_encode(['error' => 'Empty comment']);
    exit;
}
if (
    isset(
        $_POST['action'],
        $_POST['post_id'],
        $_POST['content']
    ) && $_POST['action'] === 'edit_post'
) {
    $post_id = intval($_POST['post_id']);
    $content = trim($_POST['content']);
    // Ensure the post belongs to the current doctor
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND doctor_id = ?');
    $stmt->execute([$post_id, $doctor_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare('UPDATE posts SET content = ? WHERE id = ?');
        $stmt->execute([$content, $post_id]);
        echo json_encode(['success' => true, 'content' => $content]);
        exit;
    }
    echo json_encode(['error' => 'Unauthorized or not found']);
    exit;
}
if (
    isset($_POST['action'], $_POST['post_id']) && $_POST['action'] === 'delete_post'
) {
    $post_id = intval($_POST['post_id']);
    // Ensure the post belongs to the current doctor
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND doctor_id = ?');
    $stmt->execute([$post_id, $doctor_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$post_id]);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['error' => 'Unauthorized or not found']);
    exit;
}
if (
    isset($_POST['action'], $_POST['comment_id']) && $_POST['action'] === 'delete_comment'
) {
    $comment_id = intval($_POST['comment_id']);
    // Ensure the comment belongs to the current doctor
    $stmt = $pdo->prepare('SELECT * FROM comments WHERE id = ? AND doctor_id = ?');
    $stmt->execute([$comment_id, $doctor_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
        $stmt->execute([$comment_id]);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['error' => 'Unauthorized or not found']);
    exit;
}
// Follow/Unfollow
if (
    isset($_POST['action'], $_POST['target_doctor_id']) && in_array($_POST['action'], ['follow', 'unfollow'])
) {
    $target_doctor_id = intval($_POST['target_doctor_id']);
    if ($_POST['action'] === 'follow') {
        $stmt = $pdo->prepare('INSERT IGNORE INTO doctor_followers (doctor_id, follower_id) VALUES (?, ?)');
        $stmt->execute([$target_doctor_id, $doctor_id]);
    } else {
        $stmt = $pdo->prepare('DELETE FROM doctor_followers WHERE doctor_id = ? AND follower_id = ?');
        $stmt->execute([$target_doctor_id, $doctor_id]);
    }
    // Return new followers count
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM doctor_followers WHERE doctor_id = ?');
    $stmt->execute([$target_doctor_id]);
    $followers_count = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'followers_count' => $followers_count]);
    exit;
}
echo json_encode(['error' => 'Invalid action']); 