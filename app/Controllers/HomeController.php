<?php
// app/Controllers/HomeController.php

require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Categoria.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/BuscaLog.php';
require_once __DIR__ . '/../Helpers/MoneyHelper.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';

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
        $termo = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $categoriaId = UrlId::query('categoria');
        $avaliacao = (float)($_GET['avaliacao'] ?? 0);
        $precoMin = MoneyHelper::parse($_GET['preco_min'] ?? 0);
        $precoMax = MoneyHelper::parse($_GET['preco_max'] ?? 0);
        $ordenar = in_array($_GET['ordenar'] ?? '', ['recentes', 'avaliacao', 'preco_asc', 'preco_desc', 'visualizacoes'], true)
            ? $_GET['ordenar'] : 'recentes';

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

        // Paginação
        $porPagina      = 12;
        $totalResultados = $this->anuncio->contarAvancado($filtros);
        $totalPaginas   = max(1, (int) ceil($totalResultados / $porPagina));
        $paginaAtual    = min(max(1, (int) ($_GET['pagina'] ?? 1)), $totalPaginas);

        $filtros['limite'] = $porPagina;
        $filtros['offset'] = ($paginaAtual - 1) * $porPagina;

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

        $tituloPagina = 'Busca Avançada - Aptus';
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