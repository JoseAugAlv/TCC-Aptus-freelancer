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
     * Pagina para criar avaliacao
     * Rota: GET /avaliacoes/criar/{id}
     */
    public function criar($interesseId = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        if (!$interesseId) {
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        
        // Buscar dados do interesse
        $interesse = $this->interesse->findById($interesseId);
        
        if (!$interesse) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Interesse nao encontrado.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }
        
        // Verificar se o usuario faz parte do interesse (cliente OU freelancer)
        if ($interesse['id_contratante'] != $usuarioId && $interesse['id_freelancer'] != $usuarioId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Voce nao tem permissao para avaliar este servico.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }
        
        // Verificar se o interesse esta ativo ou concluido
        if (!in_array($interesse['situacao'], ['ativo', 'concluido'])) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Apenas servicos ativos ou concluidos podem ser avaliados.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }
        
        // Verificar se o usuario ja avaliou
        require_once __DIR__ . '/../Models/Interesse.php';
        $interesseModel = new Interesse();
        if ($interesseModel->usuarioJaAvaliou($interesseId, $usuarioId)) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Voce ja avaliou este servico.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        // Buscar dados do anuncio
        $anuncio = $this->anuncio->findById($interesse['id_anuncio']);

        $tituloPagina = 'Avaliar Servico - Aptus';
        $cssPagina = 'avaliacoes.css';
        
        require '../app/Views/avaliacoes/criar.php';
    }

    /**
     * Salva uma nova avaliacao
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
                'mensagem' => 'Dados invalidos.'
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Buscar interesse
            $interesse = $this->interesse->findById($interesseId);
            
            if (!$interesse) {
                throw new Exception('Interesse nao encontrado.');
            }
            
            // Verificar se o usuario faz parte do interesse
            if ($interesse['id_contratante'] != $usuarioId && $interesse['id_freelancer'] != $usuarioId) {
                throw new Exception('Voce nao tem permissao para avaliar este servico.');
            }

            // Verificar se ja existe avaliacao deste usuario
            require_once __DIR__ . '/../Models/Interesse.php';
            $interesseModel = new Interesse();
            if ($interesseModel->usuarioJaAvaliou($interesseId, $usuarioId)) {
                throw new Exception('Voce ja avaliou este servico.');
            }

            // Verificar se o interesse esta ativo ou concluido
            if (!in_array($interesse['situacao'], ['ativo', 'concluido'])) {
                throw new Exception('Apenas servicos ativos ou concluidos podem ser avaliados.');
            }

            // Determinar quem esta sendo avaliado (o outro usuario)
            $avaliadoId = ($interesse['id_contratante'] == $usuarioId) 
                ? $interesse['id_freelancer'] 
                : $interesse['id_contratante'];

            // Criar avaliacao
            $dados = [
                'id_interesse' => $interesseId,
                'id_avaliador' => $usuarioId,
                'id_avaliado' => $avaliadoId,
                'nota' => $nota,
                'comentario' => $comentario
            ];
            
            $this->avaliacao->create($dados);
            
            // Recalcular nota media do avaliado
            $this->avaliacao->recalcularNotaMedia($avaliadoId);
            
            // Criar notificacao para o avaliado
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $avaliadoId,
                $interesseId,
                'nova_avaliacao',
                'Voce recebeu uma nova avaliacao!',
                "O usuario " . $_SESSION['usuario']['nome'] . " avaliou o servico com nota {$nota} estrelas.",
                'avaliacao',
                $pdo->lastInsertId()
            ]);

            $pdo->commit();

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Avaliacao enviada com sucesso!'
            ];

            // Redirecionar de volta para a pagina de origem
            $origem = $_POST['origem'] ?? 'ativos';
            if ($origem === 'detalhes') {
                header('Location: /Aptus/interesses/detalhes/' . $interesseId);
            } else {
                header('Location: /Aptus/interesses/ativos');
            }
            exit;

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao enviar avaliacao: ' . $e->getMessage()
            ];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }
    }

    /**
     * Pagina para responder avaliacao (freelancer)
     * Rota: GET /avaliacoes/responder/{id}
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
                'mensagem' => 'ID da avaliacao nao informado.'
            ];
            header('Location: /Aptus/interesses/recebidos');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        
        // Buscar avaliacao
        $avaliacao = $this->avaliacao->findByInteresse($avaliacaoId);
        
        if (!$avaliacao) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Avaliacao nao encontrada.'
            ];
            header('Location: /Aptus/interesses/recebidos');
            exit;
        }
        
        // Verificar se o usuario e o avaliado
        if ($avaliacao['id_avaliado'] != $usuarioId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Voce nao tem permissao para responder esta avaliacao.'
            ];
            header('Location: /Aptus/interesses/recebidos');
            exit;
        }
        
        // Verificar se ja existe resposta
        if (!empty($avaliacao['resposta_avaliado'])) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Esta avaliacao ja foi respondida.'
            ];
            header('Location: /Aptus/interesses/detalhes/' . $avaliacao['id_interesse']);
            exit;
        }

        $tituloPagina = 'Responder Avaliacao - Aptus';
        $cssPagina = 'avaliacoes.css';
        
        require '../app/Views/avaliacoes/responder.php';
    }

    /**
     * Salva a resposta da avaliacao
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
                'mensagem' => 'Resposta invalida.'
            ];
            header('Location: /Aptus/interesses/recebidos');
            exit;
        }

        try {
            // Buscar avaliacao
            $avaliacao = $this->avaliacao->findByInteresse($avaliacaoId);
            
            if (!$avaliacao || $avaliacao['id_avaliado'] != $usuarioId) {
                throw new Exception('Voce nao tem permissao para responder esta avaliacao.');
            }

            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Salvar resposta
            $this->avaliacao->responder($avaliacaoId, $resposta);
            
            // Criar notificacao para o avaliador
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $avaliacao['id_avaliador'],
                $avaliacao['id_interesse'],
                'resposta_avaliacao',
                'Sua avaliacao foi respondida!',
                "O usuario " . $_SESSION['usuario']['nome'] . " respondeu ao seu comentario.",
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