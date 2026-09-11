<?php
require_once __DIR__ . '/../Core/Auth.php';
class AuthMiddleware {
    public static function handle(array $rolesPermitidos = []) {
        if (!Auth::check()) {
            header('Location: /Aptus/login');
            exit;
        }
        if (!empty($rolesPermitidos) && !in_array(Auth::role(), $rolesPermitidos, true)) {
            http_response_code(403);
            exit('Acesso negado.');
        }
    }
}
