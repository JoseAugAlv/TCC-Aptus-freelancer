<?php
// app/Controllers/NotificacaoController.php

require_once __DIR__ . '/../Models/Notificacao.php';

class NotificacaoController
{
    private $notificacao;

    public function __construct()
    {
        $this->notificacao = new Notificacao();
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

        $usuarioId = $_SESSION['usuario']['id'];
        
        $naoLidas = $this->notificacao->getNaoLidas($usuarioId);
        $todas = $this->notificacao->getTodas($usuarioId);
        
        $tituloPagina = 'Notificacoes - Aptus';
        $cssPagina = 'notificacoes.css';
        
        require '../app/Views/notificacoes/index.php';
    }

    public function marcarLida()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];
        
        if ($id > 0) {
            $this->notificacao->marcarLida($id, $usuarioId);
        }
        
        header('Location: /Aptus/notificacoes');
        exit;
    }

    public function marcarTodasLidas()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $this->notificacao->marcarTodasLidas($usuarioId);
        
        header('Location: /Aptus/notificacoes');
        exit;
    }

    /**
     * Retorna o contador de notificacoes (AJAX)
     * Rota: GET /notificacoes/contador
     */
    public function contador()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['total' => 0, 'error' => 'Nao logado']);
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $total = $this->notificacao->contarNaoLidas($usuarioId);
        
        header('Content-Type: application/json');
        echo json_encode(['total' => $total]);
        exit;
    }
}