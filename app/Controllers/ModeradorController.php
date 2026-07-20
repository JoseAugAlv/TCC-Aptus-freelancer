<?php
// app/Controllers/ModeradorController.php

require_once __DIR__ . '/../Models/Moderador.php';
require_once __DIR__ . '/../Models/Anuncio.php';

class ModeradorController
{
    private $moderador;
    private $anuncio;

    public function __construct()
    {
        $this->moderador = new Moderador();
        $this->anuncio = new Anuncio();
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
        
        $role = (int) $_SESSION['usuario']['role'];
        
        if (!in_array($role, [1, 2, 4])) {
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }
        
        $anunciosPendentes = count($this->moderador->getAnunciosPendentes());
        $denunciasPendentes = count($this->moderador->getDenunciasPendentes());
        $disputasPendentes = count($this->moderador->getDisputasPendentes());
        $totalUsuarios = count($this->moderador->getUsuarios());
        $totalCategorias = count($this->moderador->getCategorias());
        
        $tituloPagina = 'Moderacao - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/index.php';
    }

    public function anuncios()
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
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }
        
        $anuncios = $this->moderador->getAnunciosPendentes();
        
        $tituloPagina = 'Moderar Anuncios - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/anuncios.php';
    }

    /**
     * Aprova um anúncio
     * Rota: POST /moderator/anuncios/aprovar
     */
    public function aprovarAnuncio()
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
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            header('Location: /Aptus/moderator/anuncios');
            exit;
        }

        // Aprovar anúncio
        $this->anuncio->aprovar($id);
        
        // Notificar o freelancer
        $this->notificarFreelancer($id, 'aprovado');
        
        $_SESSION['flash'] = [
            'tipo' => 'sucesso',
            'mensagem' => 'Anuncio aprovado com sucesso!'
        ];
        
        header('Location: /Aptus/moderator/anuncios');
        exit;
    }

    /**
     * Rejeita um anúncio
     * Rota: POST /moderator/anuncios/rejeitar
     */
    public function rejeitarAnuncio()
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
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Conteudo inadequado');
        
        if ($id <= 0) {
            header('Location: /Aptus/moderator/anuncios');
            exit;
        }

        // Rejeitar anúncio
        $this->anuncio->rejeitar($id, $motivo);
        
        // Notificar o freelancer
        $this->notificarFreelancer($id, 'rejeitado', $motivo);
        
        $_SESSION['flash'] = [
            'tipo' => 'sucesso',
            'mensagem' => 'Anuncio rejeitado com sucesso!'
        ];
        
        header('Location: /Aptus/moderator/anuncios');
        exit;
    }

    /**
     * Notifica o freelancer sobre a moderação
     */
    private function notificarFreelancer($anuncioId, $status, $motivo = null)
    {
        try {
            $pdo = Database::getConnection();
            
            // Buscar dados do anúncio
            $sql = "SELECT a.id_usuario, a.titulo FROM anuncio_servico a WHERE a.id_anuncio = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$anuncioId]);
            $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$anuncio) return;
            
            $tituloNotificacao = $status === 'aprovado' 
                ? 'Seu anuncio foi aprovado!' 
                : 'Seu anuncio foi rejeitado';
            
            $mensagemNotificacao = $status === 'aprovado'
                ? "Seu anuncio '{$anuncio['titulo']}' foi aprovado e esta disponivel para visualizacao."
                : "Seu anuncio '{$anuncio['titulo']}' foi rejeitado. Motivo: " . ($motivo ?? 'Nao informado');
            
            $sql = "INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $anuncio['id_usuario'],
                'moderacao_anuncio',
                $tituloNotificacao,
                $mensagemNotificacao,
                'anuncio_servico',
                $anuncioId
            ]);
            
        } catch (Exception $e) {
            error_log('Erro ao notificar freelancer: ' . $e->getMessage());
        }
    }

    public function denuncias()
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
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }
        
        $denuncias = $this->moderador->getDenunciasPendentes();
        
        $tituloPagina = 'Denuncias - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/denuncias.php';
    }

    public function disputas()
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
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }
        
        $disputas = $this->moderador->getDisputasPendentes();
        
        $tituloPagina = 'Disputas - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/disputas.php';
    }

    public function usuarios()
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
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }
        
        $usuarios = $this->moderador->getUsuarios();
        
        $tituloPagina = 'Usuarios - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/usuarios.php';
    }

    public function categorias()
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
            echo "<h1>403 - Acesso Negado</h1>";
            echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
            exit;
        }
        
        $categorias = $this->moderador->getCategorias();
        
        $tituloPagina = 'Categorias - Aptus';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/categorias.php';
    }
}