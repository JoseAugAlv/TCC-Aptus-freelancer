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

    public function dashboard()
    {
        // Verificar sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar se o usuário está logado
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        
        // Verificar permissão: Admin (1) ou Master (4)
        if (!in_array($role, [1, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo "<p>Seu perfil: " . $role . "</p>";
            echo "<p>Perfis permitidos: Admin (1) ou Master (4)</p>";
            echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
            exit;
        }
        
        // Buscar dados do dashboard
        $totalUsuarios = $this->dashboard->getTotalUsuarios();
        $totalAnuncios = $this->dashboard->getTotalAnuncios();
        $totalInteresses = $this->dashboard->getTotalInteresses();
        $totalCategorias = $this->dashboard->getTotalCategorias();
        $totalFreelancers = $this->dashboard->getTotalFreelancers();
        $anunciosPendentes = $this->dashboard->getAnunciosPendentes();
        $denunciasPendentes = $this->dashboard->getDenunciasPendentes();
        
        $totaisPorTipo = $this->dashboard->getTotalUsuariosPorTipo();
        $ultimosUsuarios = $this->dashboard->getUltimosUsuarios(5);
        $ultimosAnuncios = $this->dashboard->getUltimosAnuncios(5);
        
        $labels = array_keys($totaisPorTipo);
        $data = array_values($totaisPorTipo);
        
        $tituloPagina = 'Dashboard - Admin';
        $cssPagina = 'admin.css';
        
        require '../app/Views/admin/dashboard.php';
    }

    public function configuracoes()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        
        if (!in_array($role, [1, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }
        
        // Redirecionar para o novo controller
        header('Location: /Aptus/admin/configuracoes');
        exit;
    }

}