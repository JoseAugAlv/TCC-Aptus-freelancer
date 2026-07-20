<?php
// app/Controllers/HomeController.php

require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Categoria.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/BuscaLog.php';

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
        
        $anunciosDestaque = $this->anuncio->getDestaques(6);
        $categoriasPopulares = $this->categoria->getPopulares(8);
        $totalAnuncios = $this->anuncio->getTotal();
        $totalUsuarios = $this->usuario->getTotalAtivos();

        $usuario = $_SESSION['usuario'] ?? null;
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require '../app/Views/home/index.php';
    }

    /**
     * Busca avançada com filtros
     */
    public function buscar()
    {
        // Capturar filtros
        $termo = trim($_GET['q'] ?? '');
        $categoriaId = (int)($_GET['categoria'] ?? 0);
        $avaliacao = (float)($_GET['avaliacao'] ?? 0);
        $precoMin = (float)($_GET['preco_min'] ?? 0);
        $precoMax = (float)($_GET['preco_max'] ?? 0);
        $ordenar = $_GET['ordenar'] ?? 'recentes';

        // Validar valores
        if ($precoMax > 0 && $precoMin > $precoMax) {
            $temp = $precoMin;
            $precoMin = $precoMax;
            $precoMax = $temp;
        }

        // Montar filtros
        $filtros = [
            'termo' => $termo,
            'categoria' => $categoriaId,
            'avaliacao' => $avaliacao,
            'preco_min' => $precoMin,
            'preco_max' => $precoMax,
            'ordenar' => $ordenar
        ];

        // Buscar resultados
        $resultados = $this->anuncio->buscarAvancado($filtros);

        // Registrar busca no log (se houver termo)
        if (!empty($termo)) {
            $buscaLog = new BuscaLog();
            $usuarioId = $_SESSION['usuario']['id'] ?? null;
            $buscaLog->registrar($usuarioId, $termo, $categoriaId > 0 ? $categoriaId : null);
        }

        // Buscar categorias para o filtro
        $categorias = $this->categoria->getAll();

        $tituloPagina = 'Busca Avancada - Aptus';
        $cssPagina = 'home.css';

        // Passar variaveis para a view
        $termoBuscado = $termo;
        $filtroCategoria = $categoriaId;
        $filtroAvaliacao = $avaliacao;
        $filtroPrecoMin = $precoMin;
        $filtroPrecoMax = $precoMax;

        require '../app/Views/home/buscar.php';
    }
}