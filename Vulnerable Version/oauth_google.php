<?php
// Google OAuth2 login without Composer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Google OAuth2 Configuration
$client_id = '1051911702218-71fuf0jpaj3of8hp2147hqqi07cc6d2p.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'http://localhost/Info/oauth_google.php';
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth';
$token_url = 'https://oauth2.googleapis.com/token';
$userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';

require_once 'config/database.php';
require_once 'includes/auth.php';

if (!isset($_GET['code'])) {
    // Step 1: Redirect to Google's OAuth 2.0 server
    $auth_url = $auth_url . "?" . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => 'google_' . bin2hex(random_bytes(8)),
        'access_type' => 'online',
        'prompt' => 'select_account'
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
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ];
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($token_url, false, $context);
        $token = json_decode($result, true);

        if (!isset($token['access_token'])) {
            throw new Exception('No access token received from Google');
        }

        // Step 3: Get user info
        $opts = [
            'http' => [
                'header' => "Authorization: Bearer " . $token['access_token'] . "\r\n"
            ]
        ];
        $userinfo = file_get_contents($userinfo_url, false, stream_context_create($opts));
        $user = json_decode($userinfo, true);

        if (!isset($user['email'])) {
            throw new Exception('No email received from Google');
        }

        // Check if user exists in DB, if not, create them, then log in and redirect
        $email = $user['email'];
        $name = $user['name'];
        $picture = $user['picture'] ?? '';
        $google_id = $user['id'];

        // Try to find user by email or Google ID
        $stmt = $pdo->prepare('SELECT u.*, p.id as patient_id, d.id as doctor_id 
                              FROM users u 
                              LEFT JOIN patients p ON u.id = p.user_id 
                              LEFT JOIN doctors d ON u.id = d.user_id 
                              WHERE u.email = ? OR u.google_id = ?');
        $stmt->execute([$email, $google_id]);
        $db_user = $stmt->fetch();

        if (!$db_user) {
            // Generate a username from email
            $username = explode('@', $email)[0];
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Insert new user with role 'patient'
                $stmt = $pdo->prepare('INSERT INTO users (username, email, role, is_active, oauth_provider, google_id, google_avatar) VALUES (?, ?, ?, 1, ?, ?, ?)');
                $stmt->execute([$username, $email, 'patient', 'google', $google_id, $picture]);
                $user_id = $pdo->lastInsertId();

                // Create patient profile
                $stmt = $pdo->prepare('INSERT INTO patients (user_id) VALUES (?)');
                $stmt->execute([$user_id]);

                // Log the activity
                logActivity($pdo, $user_id, 'signup', 'User signed up via Google OAuth');
                
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            $user_id = $db_user['id'];
            
            // Update Google-specific fields if they've changed
            if ($db_user['google_id'] !== $google_id || $db_user['google_avatar'] !== $picture) {
                $stmt = $pdo->prepare('UPDATE users SET google_id = ?, google_avatar = ? WHERE id = ?');
                $stmt->execute([$google_id, $picture, $user_id]);
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
            'oauth_provider' => 'google'
        ];

        // Set session
        $_SESSION['user'] = $user_data;

        // Log the login
        logActivity($pdo, $user_id, 'login', 'User logged in via Google OAuth');

        // Redirect directly to patient dashboard
        header('Location: patient/dashboard.php');
        exit;
    } catch (Exception $e) {
        error_log('Google OAuth Error: ' . $e->getMessage());
        $_SESSION['error'] = 'Authentication failed. Please try again.';
        header('Location: login.php');
        exit;
    }
} 