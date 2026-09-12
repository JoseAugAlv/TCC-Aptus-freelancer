<?php
// app/Middleware/CsrfMiddleware.php

class CsrfMiddleware
{
    public static function generateToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field()
    {
        $token = self::generateToken();
        return '<input type="hidden" name="_csrf_token" value="'
             . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $tokenSessao = $_SESSION['csrf_token'] ?? null;

        // Aceita body (form) OU header X-CSRF-Token (AJAX).
        // O PHP converte "X-CSRF-Token: abc" em $_SERVER['HTTP_X_CSRF_TOKEN'].
        $tokenRequisicao = $_POST['_csrf_token']
                        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
                        ?? null;

        $isAjax = (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false)
               || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
               || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');

        $tokenValido = $tokenSessao
                    && $tokenRequisicao
                    && hash_equals($tokenSessao, $tokenRequisicao);

        if (!$tokenValido) {
            error_log('CSRF inválido - IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

            http_response_code(403);

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'erro'    => 'Token CSRF inválido',
                    'message' => 'Token CSRF inválido',
                ]);
                exit;
            }

            $_SESSION['flash'] = [
                'tipo'     => 'erro',
                'mensagem' => 'Token de segurança inválido. Tente novamente.',
            ];

            $referer = $_SERVER['HTTP_REFERER'] ?? '/Aptus/login';
            header('Location: ' . $referer);
            exit;
        }
    }
}