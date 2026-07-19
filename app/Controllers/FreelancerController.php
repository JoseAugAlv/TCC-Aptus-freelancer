<?php
// app/Controllers/FreelancerController.php

require_once __DIR__ . '/../Models/Dashboard.php';
require_once __DIR__ . '/../Models/Usuario.php';

class FreelancerController
{
    private $dashboard;
    private $usuario;

    public function __construct()
    {
        $this->dashboard = new Dashboard();
        $this->usuario = new Usuario();
    }

    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $role = $_SESSION['usuario']['role'];
        
        // Verificar se o usuário é freelancer (role 3) ou tem permissão
        if (!in_array($role, [2, 3, 4])) {
            header('Location: /Aptus/');
            exit;
        }

        // Dados do freelancer
        $usuarioData = $this->usuario->findById($usuarioId);
        
        // KPIs
        $totalServicos = $this->dashboard->getTotalServicosFreelancer($usuarioId);
        $servicosAtivos = $this->dashboard->getServicosAtivosFreelancer($usuarioId);
        $servicosPausados = $this->dashboard->getServicosPausadosFreelancer($usuarioId);
        $servicosPendentes = $this->dashboard->getServicosPendentesFreelancer($usuarioId);
        $interessesRecebidos = $this->dashboard->getInteressesRecebidosFreelancer($usuarioId);
        $interessesConcluidos = $this->dashboard->getInteressesConcluidosFreelancer($usuarioId);
        $avaliacao = $this->dashboard->getNotaMediaFreelancer($usuarioId);

        // Últimos serviços e interesses
        $ultimosServicos = $this->dashboard->getUltimosServicosFreelancer($usuarioId, 5);
        $ultimosInteresses = $this->dashboard->getUltimosInteressesFreelancer($usuarioId, 5);

        $tituloPagina = 'Dashboard Freelancer - Aptus';
        $cssPagina = 'freelancer.css';
        
        require '../app/Views/freelancer/dashboard.php';
    }
}