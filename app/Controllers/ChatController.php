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
        $this->mensagem = new Mensagem();
        $this->interesse = new Interesse();
    }

    /**
     * Pagina principal do chat - lista conversas
     * Rota: GET /chat
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
        $conversas = $this->mensagem->getConversasRecentes($usuarioId);
        
        $tituloPagina = 'Chat - Aptus';
        $cssPagina = 'chat.css';
        
        require '../app/Views/chat/index.php';
    }

    /**
     * Abre uma conversa especifica
     * Rota: GET /chat/{id}
     */
    public function conversa($interesseId = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        if (!$interesseId) {
            header('Location: /Aptus/chat');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        
        // Verificar se o usuario tem permissao
        if (!$this->mensagem->usuarioPodeVer($interesseId, $usuarioId)) {
            header('Location: /Aptus/chat');
            exit;
        }

        // Buscar dados do interesse
        $interesse = $this->mensagem->getDadosInteresse($interesseId, $usuarioId);
        if (!$interesse) {
            header('Location: /Aptus/chat');
            exit;
        }

        // Buscar o outro usuario
        $outroUsuario = $this->mensagem->getOutroUsuario($interesseId, $usuarioId);
        
        // Buscar mensagens
        $mensagens = $this->mensagem->getByInteresse($interesseId, 100);
        
        // Marcar mensagens como lidas
        $this->mensagem->marcarLidas($interesseId, $usuarioId);

        // Buscar conversas recentes para a sidebar
        $conversas = $this->mensagem->getConversasRecentes($usuarioId);
        
        $tituloPagina = 'Chat - Aptus';
        $cssPagina = 'chat.css';
        
        require '../app/Views/chat/conversa.php';
    }

    /**
     * Envia uma nova mensagem (AJAX)
     * Rota: POST /chat/enviar
     */
    public function enviar()
    {
        // Ativar debug
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login necessario']);
            exit;
        }

        // DEBUG: Ver o que está chegando
        error_log("=== CHAT ENVIAR ===");
        error_log("POST: " . print_r($_POST, true));
        error_log("RAW INPUT: " . file_get_contents('php://input'));

        $usuarioId = $_SESSION['usuario']['id'];
        
        // Tentar pegar de diferentes formas
        $interesseId = isset($_POST['interesse_id']) ? (int)$_POST['interesse_id'] : 0;
        $mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
        
        // Se veio como JSON (Content-Type: application/json)
        if (empty($mensagem) && empty($_POST)) {
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input) {
                $interesseId = isset($input['interesse_id']) ? (int)$input['interesse_id'] : 0;
                $mensagem = isset($input['mensagem']) ? trim($input['mensagem']) : '';
                error_log("Dados do JSON: " . print_r($input, true));
            }
        }

        error_log("interesseId: $interesseId");
        error_log("mensagem: '$mensagem'");
        error_log("usuarioId: $usuarioId");

        if ($interesseId <= 0) {
            error_log("ERRO: interesse_id invalido");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID do interesse invalido']);
            exit;
        }

        if (empty($mensagem)) {
            error_log("ERRO: mensagem vazia");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mensagem vazia']);
            exit;
        }

        // Verificar permissao
        if (!$this->mensagem->usuarioPodeVer($interesseId, $usuarioId)) {
            error_log("ERRO: Usuario sem permissao");
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sem permissao']);
            exit;
        }

        // Buscar dados do interesse para saber o destinatario
        $interesse = $this->interesse->findById($interesseId);
        if (!$interesse) {
            error_log("ERRO: Interesse nao encontrado - ID: $interesseId");
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Interesse nao encontrado']);
            exit;
        }

        // Determinar destinatario
        $destinatarioId = ($interesse['id_contratante'] == $usuarioId) 
            ? $interesse['id_freelancer'] 
            : $interesse['id_contratante'];

        error_log("destinatarioId: $destinatarioId");

        // Enviar mensagem
        $resultado = $this->mensagem->enviar($interesseId, $usuarioId, $destinatarioId, $mensagem);

        if ($resultado) {
            // Pegar o ID da mensagem inserida
            $pdo = Database::getConnection();
            $mensagemId = $pdo->lastInsertId();
            error_log("Mensagem inserida ID: $mensagemId");
            
            // Criar notificacao para o destinatario
            $this->criarNotificacao($interesseId, $destinatarioId, $usuarioId, $mensagem);
            
            // Buscar a mensagem enviada com os dados do remetente
            $sql = "SELECT m.*, u.nome as remetente_nome, u.foto_perfil as remetente_foto
                    FROM mensagem m
                    JOIN usuario u ON m.id_remetente = u.id_usuario
                    WHERE m.id_mensagem = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$mensagemId]);
            $mensagemEnviada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Mensagem enviada: " . print_r($mensagemEnviada, true));
            
            echo json_encode([
                'success' => true,
                'message' => 'Mensagem enviada',
                'mensagem' => $mensagemEnviada
            ]);
        } else {
            error_log("ERRO: Falha ao enviar mensagem no banco");
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao enviar mensagem no banco'
            ]);
        }
        exit;
    }

    /**
     * Busca novas mensagens (AJAX)
     * Rota: GET /chat/mensagens
     */
    public function mensagens()
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
        $interesseId = (int)($_GET['interesse_id'] ?? 0);
        $ultimoId = (int)($_GET['ultimo_id'] ?? 0);

        if ($interesseId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            exit;
        }

        // Verificar permissao
        if (!$this->mensagem->usuarioPodeVer($interesseId, $usuarioId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sem permissao']);
            exit;
        }

        // Buscar novas mensagens
        $sql = "SELECT m.*, u.nome as remetente_nome, u.foto_perfil as remetente_foto
                FROM mensagem m
                JOIN usuario u ON m.id_remetente = u.id_usuario
                WHERE m.id_interesse = ? AND m.id_mensagem > ?
                ORDER BY m.data_envio ASC";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute([$interesseId, $ultimoId]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Marcar como lidas as mensagens recebidas
        if (!empty($mensagens)) {
            $this->mensagem->marcarLidas($interesseId, $usuarioId);
        }

        echo json_encode([
            'success' => true,
            'mensagens' => $mensagens,
            'total' => count($mensagens)
        ]);
        exit;
    }

    /**
     * Marca mensagens como lidas (AJAX)
     * Rota: POST /chat/marcar-lida
     */
    public function marcarLida()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $interesseId = (int)($_POST['interesse_id'] ?? 0);

        if ($interesseId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false]);
            exit;
        }

        $resultado = $this->mensagem->marcarLidas($interesseId, $usuarioId);
        echo json_encode(['success' => $resultado]);
        exit;
    }

    /**
     * Cria notificacao para nova mensagem
     */
    private function criarNotificacao($interesseId, $destinatarioId, $remetenteId, $mensagem)
    {
        try {
            $pdo = Database::getConnection();
            
            // Buscar nome do remetente
            $sql = "SELECT nome FROM usuario WHERE id_usuario = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$remetenteId]);
            $remetente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $titulo = 'Nova mensagem de ' . ($remetente['nome'] ?? 'Usuario');
            $texto = substr($mensagem, 0, 100) . (strlen($mensagem) > 100 ? '...' : '');
            
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $destinatarioId,
                $interesseId,
                'nova_mensagem',
                $titulo,
                $texto,
                'mensagem',
                $pdo->lastInsertId()
            ]);
        } catch (Exception $e) {
            error_log('Erro ao criar notificacao: ' . $e->getMessage());
        }
    }
}