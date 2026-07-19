<?php
// app/Controllers/AvaliacaoController.php

require_once __DIR__ . '/../Models/Avaliacao.php';
require_once __DIR__ . '/../Models/Interesse.php';
require_once __DIR__ . '/../Models/Anuncio.php';

class AvaliacaoController
{
    private $avaliacao;
    private $interesse;
    private $anuncio;

    public function __construct()
    {
        $this->avaliacao = new Avaliacao();
        $this->interesse = new Interesse();
        $this->anuncio = new Anuncio();
    }

    /**
     * Página para criar avaliação
     * Rota: /avaliacoes/criar/{id}
     */
    public function criar($interesseId = null)
    {
        // Ativar debug
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        if (!$interesseId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'ID do interesse não informado.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        
        // Buscar dados do interesse
        $interesse = $this->interesse->findById($interesseId);
        
        if (!$interesse) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Interesse não encontrado.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }
        
        // Verificar se o usuário é o contratante
        if ($interesse['id_contratante'] != $usuarioId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Você não tem permissão para avaliar este serviço.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }
        
        // Verificar se o interesse está concluído
        if ($interesse['situacao'] != 'concluido') {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Apenas serviços concluídos podem ser avaliados.'
            ];
            header('Location: /Aptus/interesses/detalhes/' . $interesseId);
            exit;
        }
        
        // Verificar se já existe avaliação
        if ($this->avaliacao->exists($interesseId)) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Este serviço já foi avaliado.'
            ];
            header('Location: /Aptus/interesses/detalhes/' . $interesseId);
            exit;
        }

        // Buscar dados do anúncio
        $anuncio = $this->anuncio->findById($interesse['id_anuncio']);

        $tituloPagina = 'Avaliar Serviço - Aptus';
        $cssPagina = 'avaliacoes.css';
        
        require '../app/Views/avaliacoes/criar.php';
    }

    /**
     * Salva uma nova avaliação
     * Rota: POST /avaliacoes/salvar
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
        $nota = (int)($_POST['nota'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');
        $usuarioId = $_SESSION['usuario']['id'];

        if ($interesseId <= 0 || $nota < 1 || $nota > 5) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Dados inválidos.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }

        try {
            // Buscar interesse
            $interesse = $this->interesse->findById($interesseId);
            
            if (!$interesse) {
                throw new Exception('Interesse não encontrado.');
            }
            
            if ($interesse['id_contratante'] != $usuarioId) {
                throw new Exception('Você não tem permissão para avaliar este serviço.');
            }

            // Verificar se já existe avaliação
            if ($this->avaliacao->exists($interesseId)) {
                throw new Exception('Este serviço já foi avaliado.');
            }

            // Verificar se o interesse está concluído
            if ($interesse['situacao'] != 'concluido') {
                throw new Exception('Apenas serviços concluídos podem ser avaliados.');
            }

            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Criar avaliação
            $dados = [
                'id_interesse' => $interesseId,
                'id_avaliador' => $usuarioId,
                'id_avaliado' => $interesse['id_freelancer'],
                'nota' => $nota,
                'comentario' => $comentario
            ];
            
            $this->avaliacao->create($dados);
            
            // Recalcular nota média do freelancer
            $this->avaliacao->recalcularNotaMedia($interesse['id_freelancer']);
            
            // Criar notificação para o freelancer
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $interesse['id_freelancer'],
                $interesseId,
                'nova_avaliacao',
                'Você recebeu uma nova avaliação!',
                "O cliente " . $_SESSION['usuario']['nome'] . " avaliou seu serviço com nota {$nota} estrelas.",
                'avaliacao',
                $pdo->lastInsertId()
            ]);

            $pdo->commit();

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Avaliação enviada com sucesso!'
            ];

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao enviar avaliação: ' . $e->getMessage()
            ];
        }

        header('Location: /Aptus/interesses/detalhes/' . $interesseId);
        exit;
    }

    /**
     * Página para responder avaliação (freelancer)
     * Rota: /avaliacoes/responder/{id}
     */
    public function responder($avaliacaoId = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        if (!$avaliacaoId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'ID da avaliação não informado.'
            ];
            header('Location: /Aptus/interesses/recebidos');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        
        // Buscar avaliação
        $avaliacao = $this->avaliacao->findByInteresse($avaliacaoId);
        
        if (!$avaliacao) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Avaliação não encontrada.'
            ];
            header('Location: /Aptus/interesses/recebidos');
            exit;
        }
        
        // Verificar se o usuário é o avaliado (freelancer)
        if ($avaliacao['id_avaliado'] != $usuarioId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Você não tem permissão para responder esta avaliação.'
            ];
            header('Location: /Aptus/interesses/recebidos');
            exit;
        }
        
        // Verificar se já existe resposta
        if (!empty($avaliacao['resposta_avaliado'])) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Esta avaliação já foi respondida.'
            ];
            header('Location: /Aptus/interesses/detalhes/' . $avaliacao['id_interesse']);
            exit;
        }

        $tituloPagina = 'Responder Avaliação - Aptus';
        $cssPagina = 'avaliacoes.css';
        
        require '../app/Views/avaliacoes/responder.php';
    }

    /**
     * Salva a resposta da avaliação
     * Rota: POST /avaliacoes/salvar-resposta
     */
    public function salvarResposta()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $avaliacaoId = (int)($_POST['avaliacao_id'] ?? 0);
        $resposta = trim($_POST['resposta'] ?? '');
        $usuarioId = $_SESSION['usuario']['id'];

        if ($avaliacaoId <= 0 || empty($resposta)) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Resposta inválida.'
            ];
            header('Location: /Aptus/interesses/recebidos');
            exit;
        }

        try {
            // Buscar avaliação
            $avaliacao = $this->avaliacao->findByInteresse($avaliacaoId);
            
            if (!$avaliacao || $avaliacao['id_avaliado'] != $usuarioId) {
                throw new Exception('Você não tem permissão para responder esta avaliação.');
            }

            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Salvar resposta
            $this->avaliacao->responder($avaliacaoId, $resposta);
            
            // Criar notificação para o avaliador
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $avaliacao['id_avaliador'],
                $avaliacao['id_interesse'],
                'resposta_avaliacao',
                'Sua avaliação foi respondida!',
                "O freelancer " . $_SESSION['usuario']['nome'] . " respondeu ao seu comentário.",
                'avaliacao',
                $avaliacaoId
            ]);

            $pdo->commit();

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Resposta enviada com sucesso!'
            ];

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao enviar resposta: ' . $e->getMessage()
            ];
        }

        header('Location: /Aptus/interesses/detalhes/' . $avaliacao['id_interesse']);
        exit;
    }
}