<?php
// app/Controllers/AdminController.php

require_once __DIR__ . '/../Models/Dashboard.php';

class AdminController
{
    private $dashboard;

    public function __construct()
    {
        $this->dashboard = new Dashboard();
    }

    public function index()
    {
        header('Location: /Aptus/admin/dashboard');
        exit;
    }

    /**
     * NOVO: rota /master → redireciona Master (role 4) pro dashboard
     */
    public function master()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        if ((int) $_SESSION['usuario']['role'] !== 4) {
            http_response_code(403);
            echo '<h1>403 - Acesso restrito ao Master</h1>';
            echo '<p><a href="/Aptus/">Voltar ao início</a></p>';
            exit;
        }

        header('Location: /Aptus/admin/dashboard');
        exit;
    }

    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo "<p>Perfis permitidos: Admin (1) ou Master (4)</p>";
            echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
            exit;
        }

        $totalUsuarios       = $this->dashboard->getTotalUsuarios();
        $totalAnuncios       = $this->dashboard->getTotalAnuncios();
        $totalInteresses     = $this->dashboard->getTotalInteresses();
        $totalCategorias     = $this->dashboard->getTotalCategorias();
        $totalFreelancers    = $this->dashboard->getTotalFreelancers();
        $anunciosPendentes   = $this->dashboard->getAnunciosPendentes();
        $denunciasPendentes  = $this->dashboard->getDenunciasPendentes();

        $totaisPorTipo    = $this->dashboard->getTotalUsuariosPorTipo();
        $ultimosUsuarios  = $this->dashboard->getUltimosUsuarios(5);
        $ultimosAnuncios  = $this->dashboard->getUltimosAnuncios(5);

        $labels = array_keys($totaisPorTipo);
        $data   = array_values($totaisPorTipo);

        $tituloPagina = 'Dashboard - Admin';
        $cssPagina    = 'admin.css';

        require '../app/Views/admin/dashboard.php';
    }
}