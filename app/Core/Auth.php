<?php

class Auth
{
    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['usuario']);
    }

    public static function user()
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function role()
    {
        return $_SESSION['usuario']['perfil'] ?? null;
    }
}