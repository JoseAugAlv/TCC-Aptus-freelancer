<?php
// app/Controllers/ChatController.php

require_once __DIR__ . '/../Models/Mensagem.php';
require_once __DIR__ . '/../Models/Interesse.php';

class ChatController
{
    private $mensagem;
    private $interesse;

    public function __construct()
    {
        $this->mensagem  = new Mensagem();
        $this->interesse = new Interesse();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $conversas = $this->mensagem->getConversasRecentes($usuarioId);

        $tituloPagina = 'Chat - Aptus';
        $cssPagina = 'chat.css';
        require '../app/Views/chat/index.php';
    }

    public function conversa($interesseId = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        if (!$interesseId) { header('Location: /Aptus/chat'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];

        if (!$this->mensagem->usuarioPodeVer($interesseId, $usuarioId)) {
            header('Location: /Aptus/chat');
            exit;
        }

        $interesse = $this->mensagem->getDadosInteresse($interesseId, $usuarioId);
        if (!$interesse) { header('Location: /Aptus/chat'); exit; }

        $outroUsuario = $this->mensagem->getOutroUsuario($interesseId, $usuarioId);
        $mensagens    = $this->mensagem->getByInteresse($interesseId, 100);

        $this->mensagem->marcarLidas($interesseId, $usuarioId);

        $conversas = $this->mensagem->getConversasRecentes($usuarioId);

        $tituloPagina = 'Chat - Aptus';
        $cssPagina = 'chat.css';
        require '../app/Views/chat/conversa.php';
    }

    public function enviar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Login necessario']);
            exit;
        }

        $usuarioId = (int) $_SESSION['usuario']['id'];

        $interesseId = 0;
        $mensagem    = '';

        // 1) Tenta ler JSON primeiro (Content-Type: application/json)
        $rawBody = file_get_contents('php://input');
        if (is_string($rawBody) && $rawBody !== '') {
            $input = json_decode($rawBody, true);
            if (is_array($input)) {
                $interesseId = (int) ($input['interesse_id'] ?? 0);
                $mensagem    = trim((string) ($input['mensagem'] ?? ''));
            }
        }

        // 2) Fallback form-encoded
        if ($interesseId === 0 && !empty($_POST)) {
            $interesseId = (int) ($_POST['interesse_id'] ?? 0);
            $mensagem    = trim((string) ($_POST['mensagem'] ?? ''));
        }

        if ($interesseId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'ID do interesse invalido']);
            exit;
        }

        if ($mensagem === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Mensagem vazia']);
            exit;
        }

        if (mb_strlen($mensagem) > 2000) {
            http_response_code(413);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Mensagem muito longa (max. 2000 caracteres)']);
            exit;
        }

        if (!$this->mensagem->usuarioPodeVer($interesseId, $usuarioId)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Sem permissao']);
            exit;
        }

        $interesse = $this->interesse->findById($interesseId);
        if (!$interesse) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Interesse nao encontrado']);
            exit;
        }

        $destinatarioId = ((int) $interesse['id_contratante'] === $usuarioId)
            ? (int) $interesse['id_freelancer']
            : (int) $interesse['id_contratante'];

        try {
            $resultado = $this->mensagem->enviar(
                $interesseId, $usuarioId, $destinatarioId, $mensagem
            );

            if (!$resultado) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem']);
                exit;
            }

            $pdo        = Database::getConnection();
            $mensagemId = (int) $pdo->lastInsertId();

            $this->criarNotificacao($interesseId, $destinatarioId, $usuarioId, $mensagem, $mensagemId);

            $sql = "SELECT m.*, u.nome AS remetente_nome, u.foto_perfil AS remetente_foto
                    FROM mensagem m
                    JOIN usuario u ON m.id_remetente = u.id_usuario
                    WHERE m.id_mensagem = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$mensagemId]);
            $mensagemEnviada = $stmt->fetch(PDO::FETCH_ASSOC);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success'  => true,
                'message'  => 'Mensagem enviada',
                'mensagem' => $mensagemEnviada,
            ]);
        } catch (Throwable $e) {
            error_log('Erro em ChatController::enviar: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao enviar mensagem. Tente novamente.',
            ]);
        }
        exit;
    }

    public function mensagens()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Login necessario']);
            exit;
        }

        $usuarioId   = (int) $_SESSION['usuario']['id'];
        $interesseId = (int) ($_GET['interesse_id'] ?? 0);
        $ultimoId    = (int) ($_GET['ultimo_id'] ?? 0);

        if ($interesseId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            exit;
        }

        if (!$this->mensagem->usuarioPodeVer($interesseId, $usuarioId)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Sem permissao']);
            exit;
        }

        $sql = "SELECT m.*, u.nome AS remetente_nome, u.foto_perfil AS remetente_foto
                FROM mensagem m
                JOIN usuario u ON m.id_remetente = u.id_usuario
                WHERE m.id_interesse = ? AND m.id_mensagem > ?
                ORDER BY m.data_envio ASC";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute([$interesseId, $ultimoId]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($mensagens)) {
            $this->mensagem->marcarLidas($interesseId, $usuarioId);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'   => true,
            'mensagens' => $mensagens,
            'total'     => count($mensagens),
        ]);
        exit;
    }

    public function marcarLida()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false]);
            exit;
        }

        $usuarioId   = (int) $_SESSION['usuario']['id'];
        $interesseId = (int) ($_POST['interesse_id'] ?? 0);

        if ($interesseId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false]);
            exit;
        }

        $resultado = $this->mensagem->marcarLidas($interesseId, $usuarioId);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $resultado]);
        exit;
    }

    private function criarNotificacao($interesseId, $destinatarioId, $remetenteId, $mensagem, $mensagemId)
    {
        try {
            $pdo  = Database::getConnection();
            $sql  = "SELECT nome FROM usuario WHERE id_usuario = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$remetenteId]);
            $remetente = $stmt->fetch(PDO::FETCH_ASSOC);

            $titulo = 'Nova mensagem de ' . ($remetente['nome'] ?? 'Usuário');
            $texto  = mb_substr($mensagem, 0, 100) . (mb_strlen($mensagem) > 100 ? '...' : '');

            $sql  = "INSERT INTO notificacao
                     (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $destinatarioId,
                $interesseId,
                'nova_mensagem',
                $titulo,
                $texto,
                'mensagem',
                $mensagemId,
            ]);
        } catch (Throwable $e) {
            error_log('Erro ao criar notificação de chat: ' . $e->getMessage());
        }
    }
}