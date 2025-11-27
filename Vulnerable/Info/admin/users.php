<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();
// حماية الصفحة
// Check if user is logged in and has the admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // If not logged in or not an admin, redirect to login page
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];

// Handle create new user
$create_user_success = false;
$create_user_error = '';
if (isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    if ($username && $email && $password && $role) {
        // Check if username or email already exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $create_user_error = 'Username or email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
            if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                $user_id = $pdo->lastInsertId();
                // If the user is a doctor, create a doctor profile
                if ($role === 'doctor') {
                    $stmt = $pdo->prepare('INSERT INTO doctors (user_id, specialization, license_number) VALUES (?, ?, ?)');
                    $stmt->execute([$user_id, 'General Medicine', 'DOC' . str_pad($user_id, 6, '0', STR_PAD_LEFT)]);
                }
                $create_user_success = true;
            } else {
                $create_user_error = 'Failed to create user.';
            }
        }
    } else {
        $create_user_error = 'All fields are required.';
    }
}

// تفعيل/تعطيل/حذف مستخدم أو تغيير دوره
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $action = $_POST['action'];
    $target_id = (int)$_POST['user_id'];
    if ($action === 'activate') {
        $stmt = $pdo->prepare('UPDATE users SET is_active=1 WHERE id=?');
        $stmt->execute([$target_id]);
        $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
            ->execute([$user['id'], 'activate_user', 'UserID:'.$target_id, Auth::getClientIp()]);
    } elseif ($action === 'deactivate') {
        $stmt = $pdo->prepare('UPDATE users SET is_active=0 WHERE id=?');
        $stmt->execute([$target_id]);
        $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
            ->execute([$user['id'], 'deactivate_user', 'UserID:'.$target_id, Auth::getClientIp()]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
        $stmt->execute([$target_id]);
        $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
            ->execute([$user['id'], 'delete_user', 'UserID:'.$target_id, Auth::getClientIp()]);
    } elseif ($action === 'change_role' && isset($_POST['new_role'])) {
        $new_role = $_POST['new_role'];
        if (in_array($new_role, ['admin','doctor','patient'])) {
            $stmt = $pdo->prepare('UPDATE users SET role=? WHERE id=?');
            $stmt->execute([$new_role, $target_id]);
            
            // If changing to doctor role, create or update doctor profile
            if ($new_role === 'doctor') {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM doctors WHERE user_id = ?');
                $stmt->execute([$target_id]);
                if ($stmt->fetchColumn() == 0) {
                    // إذا لم يوجد، أضف صف جديد
                    $stmt = $pdo->prepare('INSERT INTO doctors (user_id, specialization, license_number) VALUES (?, ?, ?)');
                    $stmt->execute([$target_id, 'General Medicine', 'DOC' . str_pad($target_id, 6, '0', STR_PAD_LEFT)]);
                } else {
                    // إذا كان موجود، حدث بياناته الافتراضية
                    $stmt = $pdo->prepare('UPDATE doctors SET specialization = ?, license_number = ? WHERE user_id = ?');
                    $stmt->execute(['General Medicine', 'DOC' . str_pad($target_id, 6, '0', STR_PAD_LEFT), $target_id]);
                }
            }
            
            $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
                ->execute([$user['id'], 'change_role', 'UserID:'.$target_id.' to '.$new_role, Auth::getClientIp()]);
        }
    }
    header('Location: users.php');
    exit;
}

// جلب كل المستخدمين
$stmt = $pdo->query('SELECT * FROM users ORDER BY id ASC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="all_users.php">All Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <section class="py-5">
    <div class="container mt-4">
        <h2>User Management</h2>
        <!-- Create New User Button -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createUserModal">
            + Create New User
        </button>
        <?php if ($create_user_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                User created successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($create_user_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($create_user_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <table class="table table-bordered mt-3 table-custom">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <form method="post" style="display:inline-block;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="change_role">
                            <select name="new_role" onchange="this.form.submit()" class="form-select form-select-sm">
                                <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                <option value="doctor" <?= $u['role']=='doctor'?'selected':'' ?>>Doctor</option>
                                <option value="patient" <?= $u['role']=='patient'?'selected':'' ?>>Patient</option>
                            </select>
                        </form>
                    </td>
                    <td><?= $u['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <form method="post" style="display:inline-block;"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="deactivate"><button class="btn btn-custom btn-warning btn-sm">Deactivate</button></form>
                        <?php else: ?>
                            <form method="post" style="display:inline-block;"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="activate"><button class="btn btn-custom btn-success btn-sm">Activate</button></form>
                        <?php endif; ?>
                        <form method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure?');"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-custom btn-danger btn-sm">Delete</button></form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-custom btn-secondary">Back to Dashboard</a>
    </div>
    </section>
    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="users.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-control" required>
                  <option value="admin">Admin</option>
                  <option value="doctor">Doctor</option>
                  <option value="patient">Patient</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" name="create_user" class="btn btn-primary">Create</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 