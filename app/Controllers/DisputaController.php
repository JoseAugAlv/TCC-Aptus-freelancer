<?php
// app/Controllers/DisputaController.php

require_once __DIR__ . '/../Models/Disputa.php';
require_once __DIR__ . '/../Models/Interesse.php';
require_once __DIR__ . '/../Models/ConfirmacaoPagamento.php';

class DisputaController
{
    private $disputa;
    private $interesse;
    private $confirmacao;

    public function __construct()
    {
        $this->disputa = new Disputa();
        $this->interesse = new Interesse();
        $this->confirmacao = new ConfirmacaoPagamento();
    }

    /**
     * Pagina para criar uma disputa
     * Rota: GET /disputas/criar
     */
    public function criar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $interesseId = (int)($_GET['interesse_id'] ?? 0);
        
        if ($interesseId <= 0) {
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        
        // Verificar se pode abrir disputa
        if (!$this->disputa->podeAbrir($interesseId, $usuarioId)) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Nao e possivel abrir disputa para este servico.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        // Verificar se ja existe disputa ativa
        if ($this->disputa->existsAtiva($interesseId)) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Ja existe uma disputa ativa para este servico.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        $interesse = $this->interesse->findById($interesseId);
        $motivos = $this->disputa->getMotivos();
        
        $tituloPagina = 'Abrir Disputa - Aptus';
        $cssPagina = 'disputas.css';
        
        require '../app/Views/disputas/criar.php';
    }

    /**
     * Salva uma nova disputa
     * Rota: POST /disputas/salvar
     */
    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $interesseId = (int)($_POST['interesse_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $usuarioId = $_SESSION['usuario']['id'];

        if ($interesseId <= 0 || empty($motivo)) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Preencha todos os campos obrigatorios.'
            ];
            header('Location: /Aptus/disputas/criar?interesse_id=' . $interesseId);
            exit;
        }

        // Verificar se pode abrir disputa
        if (!$this->disputa->podeAbrir($interesseId, $usuarioId)) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Nao e possivel abrir disputa para este servico.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        // Verificar se ja existe disputa ativa
        if ($this->disputa->existsAtiva($interesseId)) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Ja existe uma disputa ativa para este servico.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Criar disputa
            $dados = [
                'id_interesse' => $interesseId,
                'id_aberto_por' => $usuarioId,
                'motivo' => $motivo,
                'descricao' => $descricao
            ];
            
            $this->disputa->create($dados);
            $disputaId = $pdo->lastInsertId();

            // Notificar moderadores
            $sql = "SELECT id_usuario FROM usuario WHERE id_perfil IN (1, 2, 4)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $moderadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($moderadores as $moderador) {
                $stmt->execute([
                    $moderador['id_usuario'],
                    $interesseId,
                    'nova_disputa',
                    'Nova disputa aguardando analise',
                    "O usuario " . $_SESSION['usuario']['nome'] . " abriu uma disputa para o servico.",
                    'disputa',
                    $disputaId
                ]);
            }

            // Notificar o outro usuario
            $interesse = $this->interesse->findById($interesseId);
            $outroId = ($interesse['id_contratante'] == $usuarioId) 
                ? $interesse['id_freelancer'] 
                : $interesse['id_contratante'];
            
            $stmt->execute([
                $outroId,
                $interesseId,
                'disputa_aberta',
                'Disputa aberta',
                "O usuario " . $_SESSION['usuario']['nome'] . " abriu uma disputa para o servico. Aguarde a analise do moderador.",
                'disputa',
                $disputaId
            ]);

            $pdo->commit();

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Disputa aberta com sucesso! Um moderador ira analisar.'
            ];

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao abrir disputa: ' . $e->getMessage()
            ];
        }

        header('Location: /Aptus/interesses/ativos');
        exit;
    }

    /**
     * Detalhes de uma disputa
     * Rota: GET /disputas/detalhes/{id}
     */
    public function detalhes($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        if (!$id) {
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        $disputa = $this->disputa->findById($id);
        
        if (!$disputa) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Disputa nao encontrada.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        // Verificar se o usuario tem permissao
        $usuarioId = $_SESSION['usuario']['id'];
        $role = $_SESSION['usuario']['role'];
        
        $podeVer = ($disputa['id_aberto_por'] == $usuarioId || 
                   $disputa['id_contratante'] == $usuarioId || 
                   $disputa['id_freelancer'] == $usuarioId || 
                   in_array($role, [1, 2, 4]));

        if (!$podeVer) {
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        $tituloPagina = 'Detalhes da Disputa - Aptus';
        $cssPagina = 'disputas.css';
        
        require '../app/Views/disputas/detalhes.php';
    }

    /**
     * Lista disputas (moderador)
     * Rota: GET /moderator/disputas
     */
    public function listar()
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

        $disputas = $this->disputa->getPendentes();
        $totalPendentes = $this->disputa->countPendentes();
        
        $tituloPagina = 'Disputas - Moderacao';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/disputas.php';
    }

    /**
     * Aprova disputa (moderador)
     * Rota: POST /moderator/disputas/aprovar
     */
    public function aprovar()
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

        $id = (int)($_POST['id'] ?? 0);
        $resposta = trim($_POST['resposta'] ?? '');
        $moderadorId = $_SESSION['usuario']['id'];

        if ($id <= 0) {
            header('Location: /Aptus/moderator/disputas');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Aprovar disputa
            $this->disputa->aprovar($id, $moderadorId, $resposta);

            // Buscar dados da disputa
            $disputa = $this->disputa->findById($id);

            // Notificar usuarios
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            $titulo = 'Disputa aprovada';
            $mensagem = "A disputa foi aprovada. " . ($resposta ? "Resposta: " . $resposta : "");
            
            $stmt->execute([$disputa['id_contratante'], $disputa['id_interesse'], 'disputa_aprovada', $titulo, $mensagem, 'disputa', $id]);
            $stmt->execute([$disputa['id_freelancer'], $disputa['id_interesse'], 'disputa_aprovada', $titulo, $mensagem, 'disputa', $id]);

            $pdo->commit();

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Disputa aprovada com sucesso.'
            ];

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao aprovar disputa: ' . $e->getMessage()
            ];
        }

        header('Location: /Aptus/moderator/disputas');
        exit;
    }

    /**
     * Rejeita disputa (moderador)
     * Rota: POST /moderator/disputas/rejeitar
     */
    public function rejeitar()
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

        $id = (int)($_POST['id'] ?? 0);
        $resposta = trim($_POST['resposta'] ?? '');
        $moderadorId = $_SESSION['usuario']['id'];

        if ($id <= 0) {
            header('Location: /Aptus/moderator/disputas');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Rejeitar disputa
            $this->disputa->rejeitar($id, $moderadorId, $resposta);

            // Buscar dados da disputa
            $disputa = $this->disputa->findById($id);

            // Notificar usuarios
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            $titulo = 'Disputa rejeitada';
            $mensagem = "A disputa foi rejeitada. " . ($resposta ? "Motivo: " . $resposta : "");
            
            $stmt->execute([$disputa['id_contratante'], $disputa['id_interesse'], 'disputa_rejeitada', $titulo, $mensagem, 'disputa', $id]);
            $stmt->execute([$disputa['id_freelancer'], $disputa['id_interesse'], 'disputa_rejeitada', $titulo, $mensagem, 'disputa', $id]);

            $pdo->commit();

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Disputa rejeitada com sucesso.'
            ];

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao rejeitar disputa: ' . $e->getMessage()
            ];
        }

        header('Location: /Aptus/moderator/disputas');
        exit;
    }
}