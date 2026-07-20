<?php
// app/Controllers/RelatorioController.php

require_once __DIR__ . '/../Models/BuscaLog.php';
require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Interesse.php';

class RelatorioController
{
    private $buscaLog;
    private $anuncio;
    private $usuario;
    private $interesse;

    public function __construct()
    {
        $this->buscaLog = new BuscaLog();
        $this->anuncio = new Anuncio();
        $this->usuario = new Usuario();
        $this->interesse = new Interesse();
    }

    /**
     * Pagina principal de relatorios
     * Rota: GET /relatorios
     */
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
            header('Location: /Aptus/');
            exit;
        }

        // Dados para o dashboard de relatorios
        $totalBuscas = $this->buscaLog->getTotalBuscas(30);
        $totalBuscasSemana = $this->buscaLog->getTotalBuscas(7);
        $totalAnuncios = $this->anuncio->getTotal();
        $totalUsuarios = $this->usuario->getTotalAtivos();
        $totalInteresses = $this->interesse->countAtivosByFreelancer(0); // Pegar total de interesses ativos
        $totalInteressesConcluidos = $this->interesse->countConcluidosByFreelancer(0);
        
        // Para o total de interesses, vamos buscar do banco
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE situacao = 'ativo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $totalInteressesAtivos = $stmt->fetchColumn();
        
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE situacao = 'concluido'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $totalInteressesConcluidos = $stmt->fetchColumn();

        // Categorias mais buscadas
        $categoriasMaisBuscadas = $this->buscaLog->getCategoriasMaisBuscadas(10);
        
        // Termos mais buscados
        $termosMaisBuscados = $this->buscaLog->getTermosMaisBuscados(10);
        
        // Buscas por dia (ultimos 7 dias)
        $buscasPorDia = $this->buscaLog->getBuscasPorDia(7);
        
        // Usuarios que mais buscam
        $usuariosMaisBuscam = $this->buscaLog->getUsuariosMaisBuscam(5);
        
        // Buscas sem categoria
        $buscasSemCategoria = $this->buscaLog->getBuscasSemCategoria();
        
        $tituloPagina = 'Relatorios - Aptus';
        $cssPagina = 'relatorios.css';
        
        require '../app/Views/relatorios/index.php';
    }

    /**
     * Relatorio de categorias em PDF
     * Rota: GET /relatorios/categorias-pdf
     */
    public function categoriasPdf()
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
            header('Location: /Aptus/');
            exit;
        }

        require_once __DIR__ . '/../Helpers/pdfHelper.php';
        
        $categorias = $this->buscaLog->getCategoriasMaisBuscadas(20);
        
        $html = '<h1>Relatorio de Categorias Mais Buscadas</h1>';
        $html .= '<p>Data: ' . date('d/m/Y H:i') . '</p>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">';
        $html .= '<thead><tr><th>Posicao</th><th>Categoria</th><th>Total de Buscas</th></tr></thead>';
        $html .= '<tbody>';
        
        $posicao = 1;
        foreach ($categorias as $cat) {
            $html .= '<tr>';
            $html .= '<td>' . $posicao . '</td>';
            $html .= '<td>' . htmlspecialchars($cat['nome'] ?? 'N/A') . '</td>';
            $html .= '<td>' . $cat['total_buscas'] . '</td>';
            $html .= '</tr>';
            $posicao++;
        }
        
        $html .= '</tbody></table>';
        $html .= '<p>Total de categorias listadas: ' . count($categorias) . '</p>';
        
        PdfHelper::gerar($html, 'relatorio_categorias.pdf');
    }

    /**
     * Relatorio de termos em PDF
     * Rota: GET /relatorios/termos-pdf
     */
    public function termosPdf()
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
            header('Location: /Aptus/');
            exit;
        }

        require_once __DIR__ . '/../Helpers/pdfHelper.php';
        
        $termos = $this->buscaLog->getTermosMaisBuscados(20);
        
        $html = '<h1>Relatorio de Termos Mais Buscados</h1>';
        $html .= '<p>Data: ' . date('d/m/Y H:i') . '</p>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">';
        $html .= '<thead><tr><th>Posicao</th><th>Termo</th><th>Total de Buscas</th></tr></thead>';
        $html .= '<tbody>';
        
        $posicao = 1;
        foreach ($termos as $termo) {
            $html .= '<tr>';
            $html .= '<td>' . $posicao . '</td>';
            $html .= '<td>' . htmlspecialchars($termo['termo_buscado'] ?? 'N/A') . '</td>';
            $html .= '<td>' . $termo['total'] . '</td>';
            $html .= '</tr>';
            $posicao++;
        }
        
        $html .= '</tbody></table>';
        $html .= '<p>Total de termos listados: ' . count($termos) . '</p>';
        
        PdfHelper::gerar($html, 'relatorio_termos.pdf');
    }
}