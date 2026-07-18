<?php
// app/Controllers/AnuncioController.php

require_once __DIR__ . '/../Models/Anuncio.php';

class AnuncioController
{
    private $anuncio;

    public function __construct()
    {
        $this->anuncio = new Anuncio();
    }

    public function index()
    {
        $tituloPagina = 'Serviços - Aptus';
        $cssPagina = 'anuncios.css';
        
        // Buscar anúncios do banco de dados
        $anuncios = $this->anuncio->getAll();
        $totalAnuncios = count($anuncios);
        
        require '../app/Views/anuncios/index.php';
    }

    public function detalhes($slug = null)
    {
        $tituloPagina = 'Detalhes do Serviço - Aptus';
        $cssPagina = 'anuncios.css';
        
        // Por enquanto, apenas redireciona para a lista
        header('Location: /Aptus/anuncios');
        exit;
    }

    public function criar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $tituloPagina = 'Criar Anúncio - Aptus';
        $cssPagina = 'anuncios.css';
        
        require '../app/Views/anuncios/criar.php';
    }
}