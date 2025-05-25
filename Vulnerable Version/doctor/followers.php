<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];
$stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
$stmt->execute([$user['id']]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];
// Followers (who follows me)
$stmt = $pdo->prepare('SELECT d.id, u.username, d.specialization FROM doctor_followers f JOIN doctors d ON f.follower_id = d.id JOIN users u ON d.user_id = u.id WHERE f.doctor_id = ?');
$stmt->execute([$doctor_id]);
$followers = $stmt->fetchAll();
// Following (who I follow)
$stmt = $pdo->prepare('SELECT d.id, u.username, d.specialization FROM doctor_followers f JOIN doctors d ON f.doctor_id = d.id JOIN users u ON d.user_id = u.id WHERE f.follower_id = ?');
$stmt->execute([$doctor_id]);
$following = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Followers & Following</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f4f6fa; font-family: 'Poppins', sans-serif; }
        .followers-container { max-width: 700px; margin: 40px auto; }
        .section-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; color: #2C3E50; }
        .doctor-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 8px #0001; margin-bottom: 16px; padding: 18px 20px; display: flex; align-items: center; }
        .doctor-avatar { width: 44px; height: 44px; border-radius: 50%; background: #e3e3e3; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-right: 14px; }
        .doctor-info { flex: 1; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare Doctor
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="social_feed.php">Social Feed</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="followers-container">
        <div class="mb-5">
            <div class="section-title"><i class="fas fa-users text-primary me-2"></i> My Followers</div>
            <?php if (empty($followers)): ?>
                <div class="alert alert-info">You have no followers yet.</div>
            <?php else: ?>
                <?php foreach ($followers as $doc): ?>
                    <div class="doctor-card">
                        <div class="doctor-avatar"><i class="fas fa-user-md"></i></div>
                        <div class="doctor-info">
                            <b><?php echo htmlspecialchars($doc['username']); ?></b> <span class="text-muted">(<?php echo htmlspecialchars($doc['specialization']); ?>)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div>
            <div class="section-title"><i class="fas fa-user-friends text-primary me-2"></i> I'm Following</div>
            <?php if (empty($following)): ?>
                <div class="alert alert-info">You are not following anyone yet.</div>
            <?php else: ?>
                <?php foreach ($following as $doc): ?>
                    <div class="doctor-card">
                        <div class="doctor-avatar"><i class="fas fa-user-md"></i></div>
                        <div class="doctor-info">
                            <b><?php echo htmlspecialchars($doc['username']); ?></b> <span class="text-muted">(<?php echo htmlspecialchars($doc['specialization']); ?>)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 