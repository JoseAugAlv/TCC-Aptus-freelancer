<?php
require_once __DIR__ . '/../Helpers/Auth.php';

class AuthMiddleware
{
    public static function handle()
    {
        if (!Auth::check()) {
            header('Location: /RecycleWays/login');
            exit;
        }
    }
}

?>