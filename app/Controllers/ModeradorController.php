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
        $this->anuncio   = new Anuncio();
    }

    private function verificaPermissao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4])) {
            header('Location: /Aptus/');
            exit;
        }
    }

    public function index()
    {
        $this->verificaPermissao();

        $anunciosPendentes  = count($this->moderador->getAnunciosPendentes());
        $denunciasPendentes = count($this->moderador->getDenunciasPendentes());
        $disputasPendentes  = count($this->moderador->getDisputasPendentes());
        $totalUsuarios      = count($this->moderador->getUsuarios());
        $totalCategorias    = count($this->moderador->getCategorias());

        require '../app/Views/moderator/index.php';
    }

    /**
     * Lista TODOS os anúncios com filtro por status
     */
    public function anuncios()
    {
        $this->verificaPermissao();

        $status   = $_GET['status'] ?? null;
        $anuncios = $this->moderador->getTodosAnuncios($status);

        require '../app/Views/moderator/anuncios.php';
    }

    public function aprovarAnuncio()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { header('Location: /Aptus/moderator/anuncios'); exit; }

        $this->anuncio->aprovar($id);
        $this->notificarFreelancer($id, 'aprovado');

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio aprovado com sucesso!'];
        header('Location: /Aptus/moderator/anuncios');
        exit;
    }

    public function rejeitarAnuncio()
    {
        $this->verificaPermissao();

        $id     = (int) ($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Conteúdo inadequado');
        if ($id <= 0) { header('Location: /Aptus/moderator/anuncios'); exit; }

        $this->anuncio->rejeitar($id, $motivo);
        $this->notificarFreelancer($id, 'rejeitado', $motivo);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio rejeitado com sucesso!'];
        header('Location: /Aptus/moderator/anuncios');
        exit;
    }

    /**
     * NOVO: suspender anúncio (pausar sem rejeitar)
     */
    public function suspenderAnuncio()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { header('Location: /Aptus/moderator/anuncios'); exit; }

        $this->moderador->suspenderAnuncio($id);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio suspenso.'];
        header('Location: /Aptus/moderator/anuncios');
        exit;
    }

    private function notificarFreelancer($anuncioId, $status, $motivo = null)
    {
        try {
            $pdo  = Database::getConnection();
            $sql  = "SELECT a.id_usuario, a.titulo FROM anuncio_servico a WHERE a.id_anuncio = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$anuncioId]);
            $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$anuncio) return;

            $titulo   = $status === 'aprovado' ? 'Seu anúncio foi aprovado!' : 'Seu anúncio foi rejeitado';
            $mensagem = $status === 'aprovado'
                ? "Seu anúncio '{$anuncio['titulo']}' foi aprovado e está disponível para visualização."
                : "Seu anúncio '{$anuncio['titulo']}' foi rejeitado. Motivo: " . ($motivo ?? 'Não informado');

            $sql  = "INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $anuncio['id_usuario'], 'moderacao_anuncio', $titulo, $mensagem,
                'anuncio_servico', $anuncioId
            ]);
        } catch (Exception $e) {
            error_log('Erro ao notificar freelancer: ' . $e->getMessage());
        }
    }

    public function denuncias()
    {
        $this->verificaPermissao();
        $denuncias = $this->moderador->getDenunciasPendentes();
        require '../app/Views/moderator/denuncias.php';
    }

    public function disputas()
    {
        $this->verificaPermissao();
        $disputas = $this->moderador->getDisputasPendentes();
        require '../app/Views/moderator/disputas.php';
    }

    public function usuarios()
    {
        $this->verificaPermissao();
        $usuarios = $this->moderador->getUsuarios();
        require '../app/Views/moderator/usuarios.php';
    }

    public function categorias()
    {
        $this->verificaPermissao();
        $categorias = $this->moderador->getCategorias();
        require '../app/Views/moderator/categorias.php';
    }
}