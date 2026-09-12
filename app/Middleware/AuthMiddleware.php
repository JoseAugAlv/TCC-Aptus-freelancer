<?php
// app/Middleware/AuthMiddleware.php

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Helpers/Role.php';

class AuthMiddleware
{
    /**
     * Bloqueia o acesso se não houver sessão ou se o papel não estiver
     * entre os permitidos. Super-admin (4) sempre passa.
     *
     * @param int[] $rolesPermitidos Lista de IDs de perfil aceitos.
     */
    public static function handle(array $rolesPermitidos = [])
    {
        if (!Auth::check()) {
            header('Location: /Aptus/login');
            exit;
        }

        $role = (int) Auth::role();

        // Super-admin tem acesso universal
        if ($role === Role::SUPER_ADMIN) {
            return;
        }

        if (!empty($rolesPermitidos) && !in_array($role, array_map('intval', $rolesPermitidos), true)) {
            http_response_code(403);
            echo '<h1>403 - Acesso Negado</h1>';
            echo '<p>Você não tem permissão para acessar esta página.</p>';
            echo '<p><a href="/Aptus/">Voltar ao início</a></p>';
            exit;
        }
    }
}