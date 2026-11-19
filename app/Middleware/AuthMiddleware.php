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
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $role = (int) Auth::role();

        // Super-admin tem acesso universal
        if ($role === Role::SUPER_ADMIN) {
            return;
        }

        if (!empty($rolesPermitidos) && !in_array($role, array_map('intval', $rolesPermitidos), true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }
}