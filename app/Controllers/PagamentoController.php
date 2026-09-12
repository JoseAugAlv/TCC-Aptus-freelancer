<?php
// app/Controllers/PagamentoController.php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Interesse.php';
require_once __DIR__ . '/../Models/ConfirmacaoPagamento.php';

class PagamentoController
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Lista os interesses em que o usuário é parte (contratante ou freelancer).
     * Antes: carregava TODOS os interesses e o form mandava id=1 por padrão.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];

        $sql = "SELECT i.*,
                       a.titulo  AS anuncio_titulo,
                       a.preco   AS anuncio_preco,
                       c.nome    AS contratante_nome,
                       f.nome    AS freelancer_nome,
                       cp.confirmado_contratante,
                       cp.confirmado_freelancer,
                       cp.situacao_final
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                LEFT JOIN confirmacao_pagamento cp ON i.id_interesse = cp.id_interesse
                WHERE (i.id_contratante = ? OR i.id_freelancer = ?)
                  AND i.situacao IN ('ativo', 'concluido')
                ORDER BY i.data_interesse DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId, $usuarioId]);
        $interesses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tituloPagina = 'Pagamentos - Aptus';
        $cssPagina = 'pagamentos.css';
        require '../app/Views/pagamentos/index.php';
    }

    /**
     * Tela de confirmação. Agora exige ?id= e ?papel= e valida a propriedade.
     */
    public function confirmar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioId   = (int) $_SESSION['usuario']['id'];
        $interesseId = (int) ($_GET['id'] ?? 0);
        $papel       = $_GET['papel'] ?? '';

        if ($interesseId <= 0 || !in_array($papel, ['contratante', 'freelancer'], true)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Parâmetros inválidos.'];
            header('Location: /Aptus/pagamentos'); exit;
        }

        $interesseModel = new Interesse();
        $interesse      = $interesseModel->findById($interesseId);

        $papelValido = false;
        if ($interesse) {
            if ($papel === 'contratante' && (int) $interesse['id_contratante'] === $usuarioId) {
                $papelValido = true;
            } elseif ($papel === 'freelancer' && (int) $interesse['id_freelancer'] === $usuarioId) {
                $papelValido = true;
            }
        }

        if (!$interesse || !$papelValido) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para confirmar este pagamento.'];
            header('Location: /Aptus/pagamentos'); exit;
        }

        $confirmacaoModel = new ConfirmacaoPagamento();
        $confirmacao      = $confirmacaoModel->getByInteresse($interesseId);

        $tituloPagina = 'Confirmar Pagamento - Aptus';
        $cssPagina = 'pagamentos.css';
        require '../app/Views/pagamentos/confirmar.php';
    }

    /**
     * [FIX-CRIT-01] Verifica propriedade, coleta valor/forma/data e usa o Model.
     */
    public function confirmarContratante()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $id        = (int) ($_POST['interesse_id'] ?? 0);
        $valor     = (float) ($_POST['valor'] ?? 0);
        $forma     = trim($_POST['forma_pagamento'] ?? '');
        $data      = $_POST['data_pagamento'] ?? date('Y-m-d');
        $obs       = trim($_POST['observacao'] ?? '') ?: null;

        $interesseModel = new Interesse();
        $interesse      = $interesseModel->findById($id);

        // [FIX-CRIT-01] Verificação de propriedade — bloqueava a falha
        if (!$interesse || (int) $interesse['id_contratante'] !== $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para confirmar este pagamento.'];
            header('Location: /Aptus/pagamentos'); exit;
        }

        if ($valor <= 0) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Informe o valor pago.'];
            header('Location: /Aptus/pagamentos/confirmar?id=' . $id . '&papel=contratante'); exit;
        }

        try {
            $confirmacao = new ConfirmacaoPagamento();
            $existente   = $confirmacao->getByInteresse($id);
            if (!$existente) {
                $confirmacao->create(['id_interesse' => $id]);
            }
            $confirmacao->confirmarContratante($id, $valor, $forma, $data, $obs);
            $confirmacao->verificarEAtualizarSituacao($id);

            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Pagamento registrado como contratante.'];
        } catch (Exception $e) {
            error_log('Erro ao confirmar pagamento (contratante): ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível registrar o pagamento. Tente novamente.'];
        }

        header('Location: /Aptus/pagamentos/confirmar?id=' . $id . '&papel=contratante'); exit;
    }

    /**
     * [FIX-CRIT-01] Verifica propriedade, coleta valor/data e usa o Model.
     */
    public function confirmarFreelancer()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $id        = (int) ($_POST['interesse_id'] ?? 0);
        $valor     = (float) ($_POST['valor'] ?? 0);
        $data      = $_POST['data_pagamento'] ?? date('Y-m-d');
        $obs       = trim($_POST['observacao'] ?? '') ?: null;

        $interesseModel = new Interesse();
        $interesse      = $interesseModel->findById($id);

        // [FIX-CRIT-01] Verificação de propriedade
        if (!$interesse || (int) $interesse['id_freelancer'] !== $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para confirmar este pagamento.'];
            header('Location: /Aptus/pagamentos'); exit;
        }

        if ($valor <= 0) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Informe o valor recebido.'];
            header('Location: /Aptus/pagamentos/confirmar?id=' . $id . '&papel=freelancer'); exit;
        }

        try {
            $confirmacao = new ConfirmacaoPagamento();
            $existente   = $confirmacao->getByInteresse($id);
            if (!$existente) {
                $confirmacao->create(['id_interesse' => $id]);
            }
            // Assinatura do Model: confirmarFreelancer($interesseId, $valor, $dataRecebimento, $observacao)
            $confirmacao->confirmarFreelancer($id, $valor, $data, $obs);
            $confirmacao->verificarEAtualizarSituacao($id);

            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Pagamento registrado como freelancer.'];
        } catch (Exception $e) {
            error_log('Erro ao confirmar pagamento (freelancer): ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível registrar o pagamento. Tente novamente.'];
        }

        header('Location: /Aptus/pagamentos/confirmar?id=' . $id . '&papel=freelancer'); exit;
    }
}