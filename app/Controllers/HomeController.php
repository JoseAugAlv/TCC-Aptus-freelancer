<?php
// app/Controllers/HomeController.php

require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Categoria.php';
require_once __DIR__ . '/../Models/Usuario.php';

class HomeController
{
    private $anuncio;
    private $categoria;
    private $usuario;

    public function __construct()
    {
        $this->anuncio = new Anuncio();
        $this->categoria = new Categoria();
        $this->usuario = new Usuario();
    }

    public function index()
    {
        $tituloPagina = 'Aptus - Conectando Talentos';
        $cssPagina = 'home.css';
        
        // Buscar anúncios em destaque
        $anunciosDestaque = $this->anuncio->getDestaques(6);
        
        // Buscar categorias populares
        $categoriasPopulares = $this->categoria->getPopulares(8);
        
        // Totais para estatísticas
        $totalAnuncios = $this->anuncio->getTotal();
        $totalUsuarios = $this->usuario->getTotalAtivos();  // <-- MÉTODO EXISTE AGORA
        
        $usuario = $_SESSION['usuario'] ?? null;
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require '../app/Views/home/index.php';
    }
}