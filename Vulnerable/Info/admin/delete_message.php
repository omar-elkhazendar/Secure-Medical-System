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
$stmt = $pdo->prepare("DELETE FROM admin_messages WHERE id = ?");
$stmt->execute([$id]);
header('Location: messages.php');
exit; 