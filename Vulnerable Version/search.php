<?php
require_once 'config/database.php';

$results = [];
$error = '';

if (isset($_GET['q'])) {
    $search = $_GET['q'];
    
    // Vulnerable to SQL Injection - no prepared statements
    $query = "SELECT * FROM users WHERE username LIKE '%$search%' OR email LIKE '%$search%'";
    try {
        $results = $pdo->query($query)->fetchAll();
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Users</title>
</head>
<body>
    <h1>Search Users</h1>
    
    <form method="GET">
        <input type="text" name="q" value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>">
        <button type="submit">Search</button>
    </form>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($results): ?>
        <div class="results">
            <?php foreach ($results as $user): ?>
                <div class="user">
                    <h3><?php echo $user['username']; ?></h3>
                    <p><?php echo $user['email']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html> 