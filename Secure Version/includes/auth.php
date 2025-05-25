<?php
class Auth {
    private static $key = "your-secret-key"; // Change this to a secure key

    public static function generateToken($user) {
        if (!isset($user['id']) || !isset($user['email']) || !isset($user['role'])) {
            throw new Exception('Invalid user data for token generation');
        }

        $token = base64_encode(json_encode([
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + 3600 // Token valid for 1 hour
        ]));
        return $token;
    }

    public static function validateToken($token) {
        try {
            $decoded = json_decode(base64_decode($token), true);
            if (!$decoded || !isset($decoded['id']) || !isset($decoded['role'])) {
                return false;
            }
            if (isset($decoded['exp']) && $decoded['exp'] < time()) {
                return false;
            }
            return $decoded;
        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return false;
        }
    }

    public static function requireAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check session first
        if (isset($_SESSION['user']) && isset($_SESSION['user']['id']) && isset($_SESSION['user']['role'])) {
            return $_SESSION['user'];
        }

        // If no session, check token
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $token = null;

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        } elseif (isset($_SESSION['token'])) {
            $token = $_SESSION['token'];
        }

        if ($token) {
            $decoded = self::validateToken($token);
            if ($decoded) {
                $_SESSION['user'] = $decoded;
                return $decoded;
            }
        }

        // If we get here, neither session nor token is valid
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        } else {
            // Get the current script path
            $current_path = $_SERVER['SCRIPT_NAME'];
            $base_path = '/Info/';
            
            // If we're already in a role-specific directory, go up one level
            if (strpos($current_path, $base_path . 'patient/') !== false ||
                strpos($current_path, $base_path . 'doctor/') !== false ||
                strpos($current_path, $base_path . 'admin/') !== false) {
                header('Location: ../login.php');
            } else {
                header('Location: login.php');
            }
            exit;
        }
    }

    public static function requireRole($role) {
        $user = self::requireAuth();
        if (!isset($user['role']) || $user['role'] !== $role) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                http_response_code(403);
                echo json_encode(['error' => 'Insufficient permissions']);
                exit;
            } else {
                // Get the current script path
                $current_path = $_SERVER['SCRIPT_NAME'];
                $base_path = '/Info/';
                
                // If we're already in a role-specific directory, go up one level
                if (strpos($current_path, $base_path . 'patient/') !== false ||
                    strpos($current_path, $base_path . 'doctor/') !== false ||
                    strpos($current_path, $base_path . 'admin/') !== false) {
                    header('Location: ../login.php');
                } else {
                    header('Location: login.php');
                }
                exit;
            }
        }
        return $user;
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // Helper function to get the client's IP address
    public static function getClientIp() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}
?> 