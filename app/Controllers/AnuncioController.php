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
        
        $anuncios = $this->anuncio->getAll();
        $totalAnuncios = count($anuncios);
        
        require '../app/Views/anuncios/index.php';
    }

    public function show($slug)
    {
        // Buscar anúncio pelo slug
        $anuncio = $this->anuncio->findBySlug($slug);
        
        if (!$anuncio) {
            header('Location: /Aptus/anuncios');
            exit;
        }
        
        // Incrementar visualizações
        $this->anuncio->incrementarVisualizacao($anuncio['id_anuncio']);
        
        // Buscar fotos adicionais
        $fotos = $this->anuncio->getFotos($anuncio['id_anuncio']);
        
        // Verificar se o usuário já enviou interesse
        $usuarioInteressado = false;
        if (isset($_SESSION['usuario'])) {
            $usuarioInteressado = $this->anuncio->hasInteresse(
                $anuncio['id_anuncio'], 
                $_SESSION['usuario']['id']
            );
        }
        
        $tituloPagina = $anuncio['titulo'] . ' - Aptus';
        $cssPagina = 'anuncios.css';
        
        require '../app/Views/anuncios/show.php';
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