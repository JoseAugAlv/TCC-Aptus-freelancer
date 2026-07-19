<?php
// app/Controllers/FavoritoController.php

require_once __DIR__ . '/../Models/Favorito.php';
require_once __DIR__ . '/../Models/Anuncio.php';

class FavoritoController
{
    private $favorito;
    private $anuncio;

    public function __construct()
    {
        $this->favorito = new Favorito();
        $this->anuncio = new Anuncio();
    }

    /**
     * Lista os favoritos do usuario logado
     * Rota: GET /favoritos
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

        $usuarioId = $_SESSION['usuario']['id'];
        $favoritos = $this->favorito->getByUsuario($usuarioId);
        
        $tituloPagina = 'Meus Favoritos - Aptus';
        $cssPagina = 'favoritos.css';
        
        require '../app/Views/favoritos/index.php';
    }

    /**
     * Adiciona um anuncio aos favoritos (AJAX)
     * Rota: POST /favoritos/adicionar
     */
    public function adicionar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login necessario']);
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $anuncioId = (int)($_POST['anuncio_id'] ?? 0);

        if ($anuncioId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID do anuncio invalido']);
            exit;
        }

        // Verificar se o anuncio existe
        $anuncio = $this->anuncio->findById($anuncioId);
        if (!$anuncio) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Anuncio nao encontrado']);
            exit;
        }

        // Verificar se o usuario nao e o dono do anuncio
        if ($anuncio['id_usuario'] == $usuarioId) {
            echo json_encode(['success' => false, 'message' => 'Voce nao pode favoritar seu proprio anuncio']);
            exit;
        }

        $resultado = $this->favorito->adicionar($usuarioId, $anuncioId);

        if ($resultado) {
            $total = $this->favorito->contarPorAnuncio($anuncioId);
            echo json_encode([
                'success' => true, 
                'message' => 'Adicionado aos favoritos',
                'total' => $total,
                'favoritado' => true
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Este anuncio ja esta nos seus favoritos'
            ]);
        }
        exit;
    }

    /**
     * Remove um anuncio dos favoritos (AJAX)
     * Rota: POST /favoritos/remover
     */
    public function remover()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login necessario']);
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $anuncioId = (int)($_POST['anuncio_id'] ?? 0);

        if ($anuncioId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID do anuncio invalido']);
            exit;
        }

        $resultado = $this->favorito->remover($usuarioId, $anuncioId);

        if ($resultado) {
            $total = $this->favorito->contarPorAnuncio($anuncioId);
            echo json_encode([
                'success' => true, 
                'message' => 'Removido dos favoritos',
                'total' => $total,
                'favoritado' => false
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao remover dos favoritos'
            ]);
        }
        exit;
    }

    /**
     * Verifica se um anuncio esta nos favoritos (AJAX)
     * Rota: GET /favoritos/verificar
     */
    public function verificar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'favoritado' => false]);
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $anuncioId = (int)($_GET['anuncio_id'] ?? 0);

        if ($anuncioId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'favoritado' => false]);
            exit;
        }

        $favoritado = $this->favorito->existe($usuarioId, $anuncioId);
        $total = $this->favorito->contarPorAnuncio($anuncioId);

        echo json_encode([
            'success' => true,
            'favoritado' => $favoritado,
            'total' => $total
        ]);
        exit;
    }
}