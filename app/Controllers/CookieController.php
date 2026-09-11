<?php
class CookieController {
    public function index() {
        $tituloPagina = 'Consentimento de Cookies — Aptus';
        $cssPagina = 'cookies.css';
        require '../app/Views/cookies/index.php';
    }
}
