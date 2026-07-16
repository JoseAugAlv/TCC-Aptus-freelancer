<?php
class Role
{
    public static function can($required)
    {
        $role = $_SESSION['usuario']['papel'] ?? null;

        $hierarchy = [
            'colaborador' => 1,
            'auxiliar' => 2,
            'admin' => 3
        ];

        return ($hierarchy[$role] ?? 0) >= $hierarchy[$required];
    }
}

?>