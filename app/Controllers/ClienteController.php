<?php
// app/Controllers/ClienteController.php

require_once __DIR__ . '/../Models/Dashboard.php';
require_once __DIR__ . '/../Models/Usuario.php';

class ClienteController
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
        
        // Verificar se o usuário é cliente (role 3) ou tem permissão
        if (!in_array($role, [2, 3, 4])) {
            header('Location: /Aptus/');
            exit;
        }

        // Dados do cliente
        $usuarioData = $this->usuario->findById($usuarioId);
        
        // KPIs
        $totalInteresses = $this->dashboard->getTotalInteressesCliente($usuarioId);
        $interessesAtivos = $this->dashboard->getInteressesAtivosCliente($usuarioId);
        $interessesConcluidos = $this->dashboard->getInteressesConcluidosCliente($usuarioId);
        $interessesCancelados = $this->dashboard->getInteressesCanceladosCliente($usuarioId);
        $totalFavoritos = $this->dashboard->getTotalFavoritosCliente($usuarioId);

        // Últimos interesses e favoritos
        $ultimosInteresses = $this->dashboard->getUltimosInteressesCliente($usuarioId, 5);
        $favoritos = $this->dashboard->getFavoritosCliente($usuarioId, 5);

        $tituloPagina = 'Dashboard Cliente - Aptus';
        $cssPagina = 'cliente.css';
        
        require '../app/Views/cliente/dashboard.php';
    }
}