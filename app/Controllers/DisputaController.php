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
        $this->disputa    = new Disputa();
        $this->interesse  = new Interesse();
        $this->confirmacao = new ConfirmacaoPagamento();
    }

    public function criar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseId = (int) ($_GET['interesse_id'] ?? 0);
        if ($interesseId <= 0) { header('Location: /Aptus/interesses/ativos'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];

        if (!$this->disputa->podeAbrir($interesseId, $usuarioId)) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Não é possível abrir disputa para este serviço.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        if ($this->disputa->existsAtiva($interesseId)) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Já existe uma disputa ativa para este serviço.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        $interesse = $this->interesse->findById($interesseId);
        $motivos   = $this->disputa->getMotivos();

        $tituloPagina = 'Abrir Disputa - Aptus';
        $cssPagina = 'disputas.css';
        require '../app/Views/disputas/criar.php';
    }

    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseId = (int) ($_POST['interesse_id'] ?? 0);
        $motivo      = trim($_POST['motivo'] ?? '');
        $descricao   = trim($_POST['descricao'] ?? '');
        $usuarioId   = (int) $_SESSION['usuario']['id'];

        if ($interesseId <= 0 || $motivo === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos obrigatórios.'];
            header('Location: /Aptus/disputas/criar?interesse_id=' . $interesseId); exit;
        }

        if (!$this->disputa->podeAbrir($interesseId, $usuarioId)) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Não é possível abrir disputa para este serviço.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        if ($this->disputa->existsAtiva($interesseId)) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Já existe uma disputa ativa para este serviço.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $dados = [
                'id_interesse'  => $interesseId,
                'id_aberto_por' => $usuarioId,
                'motivo'        => $motivo,
                'descricao'     => $descricao,
            ];

            $this->disputa->create($dados);
            $disputaId = (int) $pdo->lastInsertId();

            // Notificar moderadores
            $sql = "SELECT id_usuario FROM usuario WHERE id_perfil IN (1, 2, 4)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $moderadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sqlNotif = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtNotif = $pdo->prepare($sqlNotif);

            foreach ($moderadores as $moderador) {
                $stmtNotif->execute([
                    $moderador['id_usuario'],
                    $interesseId,
                    'nova_disputa',
                    'Nova disputa aguardando análise',
                    'O usuário ' . $_SESSION['usuario']['nome'] . ' abriu uma disputa para o serviço.',
                    'disputa',
                    $disputaId,
                ]);
            }

            // Notificar a outra parte
            $interesse = $this->interesse->findById($interesseId);
            $outroId = ((int) $interesse['id_contratante'] === $usuarioId)
                ? (int) $interesse['id_freelancer']
                : (int) $interesse['id_contratante'];

            $stmtNotif->execute([
                $outroId,
                $interesseId,
                'disputa_aberta',
                'Disputa aberta',
                'O usuário ' . $_SESSION['usuario']['nome'] . ' abriu uma disputa para o serviço. Aguarde a análise do moderador.',
                'disputa',
                $disputaId,
            ]);

            $pdo->commit();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Disputa aberta com sucesso! Um moderador irá analisar.'];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Erro em DisputaController::salvar: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível abrir a disputa. Tente novamente.'];
        }

        header('Location: /Aptus/interesses/ativos');
        exit;
    }

    public function detalhes($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        if (!$id) { header('Location: /Aptus/interesses/ativos'); exit; }

        $disputa = $this->disputa->findById($id);
        if (!$disputa) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Disputa não encontrada.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $role      = (int) $_SESSION['usuario']['role'];

        $podeVer = ((int) $disputa['id_aberto_por'] === $usuarioId
                 || (int) $disputa['id_contratante'] === $usuarioId
                 || (int) $disputa['id_freelancer'] === $usuarioId
                 || in_array($role, [1, 2, 4], true));

        if (!$podeVer) { header('Location: /Aptus/interesses/ativos'); exit; }

        $tituloPagina = 'Detalhes da Disputa - Aptus';
        $cssPagina = 'disputas.css';
        require '../app/Views/disputas/detalhes.php';
    }

    public function listar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4], true)) { header('Location: /Aptus/'); exit; }

        $disputas       = $this->disputa->getPendentes();
        $totalPendentes = $this->disputa->countPendentes();

        $tituloPagina = 'Disputas - Moderação';
        $cssPagina = 'moderador.css';
        require '../app/Views/moderator/disputas.php';
    }

    public function aprovar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4], true)) { header('Location: /Aptus/'); exit; }

        $id          = (int) ($_POST['id'] ?? 0);
        $resposta    = trim($_POST['resposta'] ?? '');
        $moderadorId = (int) $_SESSION['usuario']['id'];

        if ($id <= 0) { header('Location: /Aptus/moderator/disputas'); exit; }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $this->disputa->aprovar($id, $moderadorId, $resposta);

            $disputa = $this->disputa->findById($id);

            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            $titulo   = 'Disputa aprovada';
            $mensagem = 'A disputa foi aprovada. ' . ($resposta ? 'Resposta: ' . $resposta : '');

            $stmt->execute([$disputa['id_contratante'], $disputa['id_interesse'], 'disputa_aprovada', $titulo, $mensagem, 'disputa', $id]);
            $stmt->execute([$disputa['id_freelancer'],  $disputa['id_interesse'], 'disputa_aprovada', $titulo, $mensagem, 'disputa', $id]);

            $pdo->commit();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Disputa aprovada com sucesso.'];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Erro em DisputaController::aprovar: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível aprovar a disputa. Tente novamente.'];
        }

        header('Location: /Aptus/moderator/disputas');
        exit;
    }

    public function rejeitar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4], true)) { header('Location: /Aptus/'); exit; }

        $id          = (int) ($_POST['id'] ?? 0);
        $resposta    = trim($_POST['resposta'] ?? '');
        $moderadorId = (int) $_SESSION['usuario']['id'];

        if ($id <= 0) { header('Location: /Aptus/moderator/disputas'); exit; }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $this->disputa->rejeitar($id, $moderadorId, $resposta);

            $disputa = $this->disputa->findById($id);

            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            $titulo   = 'Disputa rejeitada';
            $mensagem = 'A disputa foi rejeitada. ' . ($resposta ? 'Motivo: ' . $resposta : '');

            $stmt->execute([$disputa['id_contratante'], $disputa['id_interesse'], 'disputa_rejeitada', $titulo, $mensagem, 'disputa', $id]);
            $stmt->execute([$disputa['id_freelancer'],  $disputa['id_interesse'], 'disputa_rejeitada', $titulo, $mensagem, 'disputa', $id]);

            $pdo->commit();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Disputa rejeitada com sucesso.'];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Erro em DisputaController::rejeitar: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível rejeitar a disputa. Tente novamente.'];
        }

        header('Location: /Aptus/moderator/disputas');
        exit;
    }
}