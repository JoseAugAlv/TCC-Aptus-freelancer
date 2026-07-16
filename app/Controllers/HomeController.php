<?php
// app/Controllers/HomeController.php

require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Categoria.php';
require_once __DIR__ . '/../Models/BuscaLog.php';
require_once __DIR__ . '/../Models/Notificacao.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Config/database.php';

class HomeController
{
    private $anuncio;
    private $categoria;
    private $buscaLog;
    private $usuario;

    public function __construct()
    {
        $this->anuncio = new Anuncio();
        $this->categoria = new Categoria();
        $this->buscaLog = new BuscaLog();
        $this->usuario = new Usuario();
    }

    /**
     * Página inicial - exibe anúncios em destaque e categorias populares
     */
    public function index()
    {
        $tituloPagina = 'Aptus - Conectando Talentos';
        $cssPagina = 'home.css';
        
        // Buscar anúncios em destaque (mais visualizados)
        $anunciosDestaque = $this->anuncio->getDestaques(6);
        
        // Buscar categorias populares (com mais anúncios)
        $categoriasPopulares = $this->categoria->getPopulares(8);
        
        // Totais para estatísticas
        $totalAnuncios = $this->anuncio->getTotal();
        $totalUsuarios = $this->usuario->getTotalAtivos();  // <-- USANDO O MODEL
        
        // Usuário logado e notificações
        $usuario = $_SESSION['usuario'] ?? null;
        $totalNotificacoes = 0;
        
        if ($usuario) {
            $notificacao = new Notificacao();
            $totalNotificacoes = $notificacao->contarNaoLidas($usuario['id']);
        }
        
        // Flash messages
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require '../app/Views/home/index.php';
    }

    /**
     * Busca de anúncios com filtros (RF09/RF10)
     */
    public function buscar()
    {
        $tituloPagina = 'Busca - Aptus';
        $cssPagina = 'home.css';
        
        $termo = trim($_GET['q'] ?? '');
        $categoriaId = (int)($_GET['categoria'] ?? 0);
        $avaliacaoMin = (float)($_GET['avaliacao'] ?? 0);
        $ordenar = $_GET['ordenar'] ?? 'recentes';
        
        $resultados = [];
        $termoBuscado = $termo;
        
        // Se tiver termo de busca, pesquisa
        if (!empty($termo) || $categoriaId > 0) {
            $resultados = $this->anuncio->buscar($termo, $categoriaId, $avaliacaoMin, $ordenar);
            
            // Registrar busca para relatórios (RF19)
            $usuarioId = $_SESSION['usuario']['id'] ?? null;
            $this->buscaLog->registrar($usuarioId, $termo, $categoriaId);
        }
        
        // Buscar categorias para o filtro
        $categorias = $this->categoria->getAll();
        
        // Usuário logado
        $usuario = $_SESSION['usuario'] ?? null;
        $totalNotificacoes = 0;
        
        if ($usuario) {
            $notificacao = new Notificacao();
            $totalNotificacoes = $notificacao->contarNaoLidas($usuario['id']);
        }

        require '../app/Views/home/buscar.php';
    }

    /**
     * Página Sobre
     */
    public function sobre()
    {
        $tituloPagina = 'Sobre - Aptus';
        $cssPagina = 'sobre.css';
        
        $usuario = $_SESSION['usuario'] ?? null;
        $totalNotificacoes = 0;
        
        if ($usuario) {
            $notificacao = new Notificacao();
            $totalNotificacoes = $notificacao->contarNaoLidas($usuario['id']);
        }

        require '../app/Views/sobre/index.php';
    }

    /**
     * Página Contato
     */
    public function contato()
    {
        $tituloPagina = 'Contato - Aptus';
        $cssPagina = 'contato.css';
        
        $usuario = $_SESSION['usuario'] ?? null;
        $totalNotificacoes = 0;
        
        if ($usuario) {
            $notificacao = new Notificacao();
            $totalNotificacoes = $notificacao->contarNaoLidas($usuario['id']);
        }

        require '../app/Views/contato/index.php';
    }
}