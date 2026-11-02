<?php
// app/Helpers/FlashHelper.php

class FlashHelper
{
    public static function set(string $tipo, string $mensagem): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    public static function consume(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['flash'])) return null;
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    public static function render(): string
    {
        $flash = self::consume();
        if (!$flash) return '';

        $tipo = htmlspecialchars($flash['tipo'] ?? 'info', ENT_QUOTES, 'UTF-8');
        $msg  = htmlspecialchars($flash['mensagem'] ?? '', ENT_QUOTES, 'UTF-8');

        return "<div class=\"flash-{$tipo}\" style=\"padding:14px 18px; margin:12px 0; border-radius:10px;
                font-weight:600; border-left:5px solid #006577; background:#e0f2f1; color:#004d40;\">
                {$msg}
                </div>";
    }
}