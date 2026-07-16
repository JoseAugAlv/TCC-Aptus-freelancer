<?php
// app/Controllers/LogController.php

require_once __DIR__ . '/../Models/LogSistema.php';

class LogController
{
    private $logSistema;

    public function __construct()
    {
        $this->logSistema = new LogSistema();
    }

    public function index()
    {
        // Verificar se está logado e é Master (role 5)
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['role'] != 5) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Você não tem permissão para acessar esta página.'
            ];
            header('Location: /RecycleWays/');
            exit;
        }

        // Buscar logs com paginação
        $pagina = (int) ($_GET['pagina'] ?? 1);
        $limite = 20;
        $offset = ($pagina - 1) * $limite;

        // Buscar logs
        $logs = $this->logSistema->getWithPagination($limite, $offset);
        $total = $this->logSistema->getTotal();
        $totalPaginas = ceil($total / $limite);

        // Buscar tabelas e ações para filtros
        $tabelas = $this->logSistema->getTabelas();
        $acoes = $this->logSistema->getAcoes();

        // Paginação HTML
        $paginationHtml = $this->renderPagination($pagina, $totalPaginas, '/RecycleWays/logs');

        require '../app/Views/logs/index.php';
    }

    private function renderPagination($paginaAtual, $totalPaginas, $baseUrl)
    {
        if ($totalPaginas <= 1) {
            return '';
        }

        $html = '<div class="pagination">';
        
        // Botão Anterior
        if ($paginaAtual > 1) {
            $html .= '<a href="' . $baseUrl . '?pagina=' . ($paginaAtual - 1) . '">«</a>';
        }

        // Números das páginas
        for ($i = 1; $i <= $totalPaginas; $i++) {
            if ($i == $paginaAtual) {
                $html .= '<span class="active">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $baseUrl . '?pagina=' . $i . '">' . $i . '</a>';
            }
        }

        // Botão Próximo
        if ($paginaAtual < $totalPaginas) {
            $html .= '<a href="' . $baseUrl . '?pagina=' . ($paginaAtual + 1) . '">»</a>';
        }

        $html .= '</div>';
        return $html;
    }
}