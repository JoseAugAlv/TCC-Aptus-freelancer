<?php
// app/Helpers/Role.php

class Role
{
    const ADMIN       = 1;
    const AUXILIAR    = 2; // Moderador
    const USUARIO     = 3; // Cliente e/ou Freelancer
    const SUPER_ADMIN = 4; // Master com acesso universal

    /**
     * Verifica se o usuário logado possui um dos papéis informados.
     * Super-admin (4) tem acesso universal (bypass).
     *
     * @param int|array $required Papel único ou lista de papéis aceitos.
     */
    public static function can($required)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = (int) ($_SESSION['usuario']['role'] ?? 0);

        if ($role === self::SUPER_ADMIN) {
            return true;
        }

        $requiredList = is_array($required) ? $required : [$required];
        $requiredList = array_map('intval', $requiredList);

        return in_array($role, $requiredList, true);
    }
}