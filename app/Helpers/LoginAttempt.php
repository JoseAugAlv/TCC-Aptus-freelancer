<?php
require_once __DIR__ . '/../Models/Configuracao.php';
class LoginAttempt {
    public static function check($email) {
        $key = 'login_attempt_' . md5($email);
        $attempts = isset($_SESSION[$key]) ? (int)$_SESSION[$key] : 0;
        $max = 5;
        try {
            $configs = new Configuracao();
            $val = $configs->get('tentativas_login');
            if ($val !== null) $max = (int)$val;
        } catch (Exception $e) {
            $max = 5;
        }
        if ($attempts >= $max) return false;
        return true;
    }
    public static function increment($email) {
        $key = 'login_attempt_' . md5($email);
        if (!isset($_SESSION[$key])) $_SESSION[$key] = 0;
        $_SESSION[$key]++;
    }
    public static function reset($email) {
        $key = 'login_attempt_' . md5($email);
        unset($_SESSION[$key]);
    }
}
