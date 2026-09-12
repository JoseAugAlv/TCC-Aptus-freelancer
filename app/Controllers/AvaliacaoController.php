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
        $this->anuncio   = new Anuncio();
    }

    public function criar($interesseId = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        if (!$interesseId) { header('Location: /Aptus/interesses/ativos'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $interesse = $this->interesse->findById($interesseId);

        if (!$interesse) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Interesse não encontrado.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        if ((int) $interesse['id_contratante'] !== $usuarioId
            && (int) $interesse['id_freelancer'] !== $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para avaliar este serviço.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        if (!in_array($interesse['situacao'], ['ativo', 'concluido'], true)) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Apenas serviços ativos ou concluídos podem ser avaliados.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        if ($this->interesse->usuarioJaAvaliou($interesseId, $usuarioId)) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Você já avaliou este serviço.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        $anuncio = $this->anuncio->findById($interesse['id_anuncio']);

        $tituloPagina = 'Avaliar Serviço - Aptus';
        $cssPagina = 'avaliacoes.css';
        require '../app/Views/avaliacoes/criar.php';
    }

    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseId = (int) ($_POST['interesse_id'] ?? 0);
        $nota        = (int) ($_POST['nota'] ?? 0);
        $comentario  = trim($_POST['comentario'] ?? '');
        $usuarioId   = (int) $_SESSION['usuario']['id'];

        if ($interesseId <= 0 || $nota < 1 || $nota > 5) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Dados inválidos.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $interesse = $this->interesse->findById($interesseId);

            if (!$interesse) {
                throw new RuntimeException('Interesse não encontrado.');
            }

            if ((int) $interesse['id_contratante'] !== $usuarioId
                && (int) $interesse['id_freelancer'] !== $usuarioId) {
                throw new RuntimeException('Sem permissão.');
            }

            if ($this->interesse->usuarioJaAvaliou($interesseId, $usuarioId)) {
                throw new RuntimeException('Avaliação duplicada.');
            }

            if (!in_array($interesse['situacao'], ['ativo', 'concluido'], true)) {
                throw new RuntimeException('Situação inválida para avaliar.');
            }

            $avaliadoId = ((int) $interesse['id_contratante'] === $usuarioId)
                ? (int) $interesse['id_freelancer']
                : (int) $interesse['id_contratante'];

            $dados = [
                'id_interesse' => $interesseId,
                'id_avaliador' => $usuarioId,
                'id_avaliado'  => $avaliadoId,
                'nota'         => $nota,
                'comentario'   => $comentario,
            ];

            $this->avaliacao->create($dados);
            $avaliacaoId = (int) $pdo->lastInsertId();
            $this->avaliacao->recalcularNotaMedia($avaliadoId);

            // Notificação (agora com o ID correto da avaliação)
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $avaliadoId,
                $interesseId,
                'nova_avaliacao',
                'Você recebeu uma nova avaliação!',
                'O usuário ' . $_SESSION['usuario']['nome'] . " avaliou o serviço com nota {$nota} estrelas.",
                'avaliacao',
                $avaliacaoId,
            ]);

            $pdo->commit();

            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Avaliação enviada com sucesso!'];

            $origem = $_POST['origem'] ?? 'ativos';
            if ($origem === 'detalhes') {
                header('Location: /Aptus/interesses/detalhes/' . $interesseId);
            } else {
                header('Location: /Aptus/interesses/ativos');
            }
            exit;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // [FIX-MED-02] Detalhe técnico só no log
            error_log('Erro em AvaliacaoController::salvar: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível enviar a avaliação. Tente novamente.'];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }
    }

    public function responder($avaliacaoId = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        if (!$avaliacaoId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'ID da avaliação não informado.'];
            header('Location: /Aptus/interesses/recebidos'); exit;
        }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $avaliacao = $this->avaliacao->findById($avaliacaoId);

        if (!$avaliacao) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Avaliação não encontrada.'];
            header('Location: /Aptus/interesses/recebidos'); exit;
        }

        if ((int) $avaliacao['id_avaliado'] !== $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para responder esta avaliação.'];
            header('Location: /Aptus/interesses/recebidos'); exit;
        }

        if (!empty($avaliacao['resposta_avaliado'])) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Esta avaliação já foi respondida.'];
            header('Location: /Aptus/interesses/detalhes/' . $avaliacao['id_interesse']); exit;
        }

        $tituloPagina = 'Responder Avaliação - Aptus';
        $cssPagina = 'avaliacoes.css';
        require '../app/Views/avaliacoes/responder.php';
    }

    public function salvarResposta()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $avaliacaoId = (int) ($_POST['avaliacao_id'] ?? 0);
        $resposta    = trim($_POST['resposta'] ?? '');
        $usuarioId   = (int) $_SESSION['usuario']['id'];

        if ($avaliacaoId <= 0 || $resposta === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Resposta inválida.'];
            header('Location: /Aptus/interesses/recebidos'); exit;
        }

        $avaliacao = $this->avaliacao->findById($avaliacaoId);
        if (!$avaliacao || (int) $avaliacao['id_avaliado'] !== $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para responder esta avaliação.'];
            header('Location: /Aptus/interesses/recebidos'); exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $this->avaliacao->responder($avaliacaoId, $resposta);

            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $avaliacao['id_avaliador'],
                $avaliacao['id_interesse'],
                'resposta_avaliacao',
                'Sua avaliação foi respondida!',
                'O usuário ' . $_SESSION['usuario']['nome'] . ' respondeu ao seu comentário.',
                'avaliacao',
                $avaliacaoId,
            ]);

            $pdo->commit();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Resposta enviada com sucesso!'];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Erro em AvaliacaoController::salvarResposta: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível enviar a resposta. Tente novamente.'];
        }

        header('Location: /Aptus/interesses/detalhes/' . $avaliacao['id_interesse']);
        exit;
    }
}