<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config/database.php';

class MFA {
    private $tfa;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->tfa = new RobThree\Auth\TwoFactorAuth('Healthcare System');
    }

    public function createSecret() {
        return $this->tfa->createSecret();
    }

    public function getQRCodeImageAsDataUri($secret, $username) {
        return $this->tfa->getQRCodeImageAsDataUri($username, $secret);
    }

    public function verifyCode($secret, $code) {
        return $this->tfa->verifyCode($secret, $code);
    }

    public function saveSecret($user_id, $secret) {
        $stmt = $this->pdo->prepare('UPDATE users SET two_factor_secret = ? WHERE id = ?');
        return $stmt->execute([$secret, $user_id]);
    }

    public function getSecret($user_id) {
        $stmt = $this->pdo->prepare('SELECT two_factor_secret FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function enableMFA($user_id) {
        $stmt = $this->pdo->prepare('UPDATE users SET mfa_enabled = 1 WHERE id = ?');
        return $stmt->execute([$user_id]);
    }

    public function isMFAEnabled($user_id) {
        $stmt = $this->pdo->prepare('SELECT mfa_enabled FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        return (bool)$stmt->fetchColumn();
    }
} 