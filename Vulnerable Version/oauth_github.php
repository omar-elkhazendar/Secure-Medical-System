<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';

// GitHub OAuth2 Configuration
$client_id = 'Ov23licZkmwoAS9qM95v';
$client_secret = '993db233c4339896b281805b3b0c6cc0cfe73b28';
$redirect_uri = 'http://localhost/Info/oauth_github.php';
$auth_url = 'https://github.com/login/oauth/authorize';
$token_url = 'https://github.com/login/oauth/access_token';
$userinfo_url = 'https://api.github.com/user';

if (!isset($_GET['code'])) {
    $auth_url = "https://github.com/login/oauth/authorize?" . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'scope' => 'user:email',
        'state' => 'github_' . bin2hex(random_bytes(8))
    ]);
    $_SESSION['oauth2state'] = $_GET['state'] ?? null;
    header('Location: ' . $auth_url);
    exit;
} else {
    try {
        // Step 2: Exchange code for access token
        $data = [
            'code' => $_GET['code'],
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri
        ];
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($token_url, false, $context);
        $token = json_decode($result, true);

        if (!isset($token['access_token'])) {
            throw new Exception('No access token received from GitHub');
        }

        // Step 3: Get user info
        $opts = [
            'http' => [
                'header' => "Authorization: token " . $token['access_token'] . "\r\nUser-Agent: PHP GitHub OAuth\r\n"
            ]
        ];
        $userinfo = file_get_contents($userinfo_url, false, stream_context_create($opts));
        $user = json_decode($userinfo, true);

        // Get user email
        $opts = [
            'http' => [
                'header' => "Authorization: token " . $token['access_token'] . "\r\nUser-Agent: PHP GitHub OAuth\r\n"
            ]
        ];
        $emails = file_get_contents('https://api.github.com/user/emails', false, stream_context_create($opts));
        $email_list = json_decode($emails, true);

        $email = '';
        if (is_array($email_list)) {
            foreach ($email_list as $email_data) {
                if (isset($email_data['primary']) && $email_data['primary']) {
                    $email = $email_data['email'];
                    break;
                }
            }
        }
        if (empty($email) && isset($user['email'])) {
            $email = $user['email'];
        }
        if (empty($email)) {
            $email = $user['login'] . '@github.com';
        }

        if (empty($email)) {
            throw new Exception('No email received from GitHub');
        }

        // Check if user exists in DB, if not, create them, then log in and redirect
        $name = $user['name'] ?? $user['login'];
        $picture = $user['avatar_url'] ?? '';
        $github_id = $user['id'];

        // Try to find user by email or GitHub ID
        $stmt = $pdo->prepare('SELECT u.*, p.id as patient_id, d.id as doctor_id 
                              FROM users u 
                              LEFT JOIN patients p ON u.id = p.user_id 
                              LEFT JOIN doctors d ON u.id = d.user_id 
                              WHERE u.email = ? OR u.github_id = ?');
        $stmt->execute([$email, $github_id]);
        $db_user = $stmt->fetch();

        if (!$db_user) {
            // Generate a username from email
            $username = explode('@', $email)[0];
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Insert new user with role 'patient'
                $stmt = $pdo->prepare('INSERT INTO users (username, email, role, is_active, oauth_provider, github_id, github_avatar) VALUES (?, ?, ?, 1, ?, ?, ?)');
                $stmt->execute([$username, $email, 'patient', 'github', $github_id, $picture]);
                $user_id = $pdo->lastInsertId();

                // Create patient profile
                $stmt = $pdo->prepare('INSERT INTO patients (user_id) VALUES (?)');
                $stmt->execute([$user_id]);

                // Log the activity
                logActivity($pdo, $user_id, 'signup', 'User signed up via GitHub OAuth');
                
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            $user_id = $db_user['id'];
            
            // Update GitHub-specific fields if they've changed
            if ($db_user['github_id'] !== $github_id || $db_user['github_avatar'] !== $picture) {
                $stmt = $pdo->prepare('UPDATE users SET github_id = ?, github_avatar = ? WHERE id = ?');
                $stmt->execute([$github_id, $picture, $user_id]);
            }
            
            // If user exists but doesn't have a patient profile, create one
            if (!$db_user['patient_id']) {
                $stmt = $pdo->prepare('INSERT INTO patients (user_id) VALUES (?)');
                $stmt->execute([$user_id]);
            }
        }

        // Prepare user data for session
        $user_data = [
            'id' => $user_id,
            'email' => $email,
            'username' => $db_user ? $db_user['username'] : $username,
            'name' => $name,
            'role' => 'patient',
            'avatar' => $picture,
            'oauth_provider' => 'github'
        ];

        // Set session
        $_SESSION['user'] = $user_data;

        // Log the login
        logActivity($pdo, $user_id, 'login', 'User logged in via GitHub OAuth');

        // Redirect directly to patient dashboard
        header('Location: patient/dashboard.php');
        exit;
    } catch (Exception $e) {
        error_log('GitHub OAuth Error: ' . $e->getMessage());
        $_SESSION['error'] = 'Authentication failed. Please try again.';
        header('Location: login.php');
        exit;
    }
} 