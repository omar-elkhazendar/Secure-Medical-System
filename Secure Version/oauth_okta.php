<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';

// Auth0 OAuth2 Configuration
$client_id = '2euHFCsaE9SUSZTH3Fih83PIurKNJ1WK';
$client_secret = 'k12opOCH-yELLJr8YA-HBzG3plIKgq8aFZ8OYoeH4D6-yFnSCHusYaMW9-kMesGv';
$redirect_uri = 'https://localhost/Info/oauth_okta.php';
$auth_url = 'https://dev-duqhbvtx5bmw8n3e.us.auth0.com/authorize';
$token_url = 'https://dev-duqhbvtx5bmw8n3e.us.auth0.com/oauth/token';
$userinfo_url = 'https://dev-duqhbvtx5bmw8n3e.us.auth0.com/userinfo';
$api_identifier = 'https://api.healthcare.local';

if (!isset($_GET['code'])) {
    // Step 1: Redirect to Auth0's OAuth server
    $auth_url = $auth_url . "?" . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => 'okta_' . bin2hex(random_bytes(8)),
        'audience' => $api_identifier
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
        $context = stream_context_create($options);
        $result = file_get_contents($token_url, false, $context);
        $token = json_decode($result, true);

        if (!isset($token['access_token'])) {
            throw new Exception('No access token received from Auth0');
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
            throw new Exception('No email received from Auth0');
        }

        // Check if user exists in DB, if not, create them, then log in and redirect
        $email = $user['email'];
        $name = $user['name'] ?? $user['preferred_username'] ?? explode('@', $email)[0];
        $picture = $user['picture'] ?? '';
        $okta_id = $user['sub'];

        // Try to find user by email or Auth0 ID
        $stmt = $pdo->prepare('SELECT u.*, p.id as patient_id, d.id as doctor_id 
                              FROM users u 
                              LEFT JOIN patients p ON u.id = p.user_id 
                              LEFT JOIN doctors d ON u.id = d.user_id 
                              WHERE u.email = ? OR u.okta_id = ?');
        $stmt->execute([$email, $okta_id]);
        $db_user = $stmt->fetch();

        if (!$db_user) {
            // Generate a username from email
            $username = explode('@', $email)[0];
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Insert new user with role 'patient'
                $stmt = $pdo->prepare('INSERT INTO users (username, email, role, is_active, oauth_provider, okta_id, okta_avatar) VALUES (?, ?, ?, 1, ?, ?, ?)');
                $stmt->execute([$username, $email, 'patient', 'okta', $okta_id, $picture]);
                $user_id = $pdo->lastInsertId();

                // Create patient profile
                $stmt = $pdo->prepare('INSERT INTO patients (user_id) VALUES (?)');
                $stmt->execute([$user_id]);

                // Log the activity
                logActivity($pdo, $user_id, 'signup', 'User signed up via Auth0 OAuth');
                
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            $user_id = $db_user['id'];
            
            // Update Auth0-specific fields if they've changed
            if ($db_user['okta_id'] !== $okta_id || $db_user['okta_avatar'] !== $picture) {
                $stmt = $pdo->prepare('UPDATE users SET okta_id = ?, okta_avatar = ? WHERE id = ?');
                $stmt->execute([$okta_id, $picture, $user_id]);
            }
            
            // If user exists but doesn't have a patient profile, create one
            if (!$db_user['patient_id']) {
                $stmt = $pdo->prepare('INSERT INTO patients (user_id) VALUES (?)');
                $stmt->execute([$user_id]);
            }

            // Force role to be patient
            $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->execute(['patient', $user_id]);
        }

        // Prepare user data for session
        $user_data = [
            'id' => $user_id,
            'email' => $email,
            'username' => $db_user ? $db_user['username'] : $username,
            'name' => $name,
            'role' => 'patient', // Force role to be patient
            'avatar' => $picture,
            'oauth_provider' => 'okta'
        ];

        // Set session
        $_SESSION['user'] = $user_data;

        // Log the login
        logActivity($pdo, $user_id, 'login', 'User logged in via Auth0 OAuth');

        // Redirect directly to patient dashboard
        header('Location: https://localhost/Info/patient/dashboard.php');
        exit;
    } catch (Exception $e) {
        error_log('Auth0 OAuth Error: ' . $e->getMessage());
        $_SESSION['error'] = 'Authentication failed. Please try again.';
        header('Location: https://localhost/Info/login.php');
        exit;
    }
} 