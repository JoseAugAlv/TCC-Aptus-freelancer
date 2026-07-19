<?php
// app/Controllers/ModeradorController.php

require_once __DIR__ . '/../Models/Moderador.php';

class ModeradorController
{
    private $moderador;

    public function __construct()
    {
        $this->moderador = new Moderador();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        
        if (!in_array($role, [1, 2, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
            exit;
        }
        
        $anunciosPendentes = count($this->moderador->getAnunciosPendentes());
        $denunciasPendentes = count($this->moderador->getDenunciasPendentes());
        $disputasPendentes = count($this->moderador->getDisputasPendentes());
        $totalUsuarios = count($this->moderador->getUsuarios());
        $totalCategorias = count($this->moderador->getCategorias());
        
        $tituloPagina = 'Moderação - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/index.php';
    }

    public function anuncios()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        
        if (!in_array($role, [1, 2, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
            exit;
        }
        
        $anuncios = $this->moderador->getAnunciosPendentes();
        
        $tituloPagina = 'Moderar Anúncios - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/anuncios.php';
    }

    public function denuncias()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        
        if (!in_array($role, [1, 2, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
            exit;
        }
        
        $denuncias = $this->moderador->getDenunciasPendentes();
        
        $tituloPagina = 'Denúncias - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/denuncias.php';
    }

    public function disputas()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        
        if (!in_array($role, [1, 2, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
            exit;
        }
        
        $disputas = $this->moderador->getDisputasPendentes();
        
        $tituloPagina = 'Disputas - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/disputas.php';
    }

    public function usuarios()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        
        if (!in_array($role, [1, 2, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
            exit;
        }
        
        $usuarios = $this->moderador->getUsuarios();
        
        $tituloPagina = 'Usuários - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/usuarios.php';
    }

    public function categorias()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        
        if (!in_array($role, [1, 2, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
            exit;
        }
        
        $categorias = $this->moderador->getCategorias();
        
        $tituloPagina = 'Categorias - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/categorias.php';
    }
}