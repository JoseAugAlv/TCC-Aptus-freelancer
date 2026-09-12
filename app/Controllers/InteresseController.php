<?php
// app/Controllers/InteresseController.php

require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Interesse.php';
require_once __DIR__ . '/../Core/Mailer.php';

class InteresseController
{
    private $anuncio;
    private $usuario;

    public function __construct()
    {
        $this->anuncio = new Anuncio();
        $this->usuario = new Usuario();
    }

    public function cancelar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $id        = (int) ($_POST['id'] ?? 0);
        $usuarioId = (int) $_SESSION['usuario']['id'];

        $model     = new Interesse();
        $interesse = $model->findById($id);

        if ($interesse
            && ((int) $interesse['id_contratante'] === $usuarioId
                || (int) $interesse['id_freelancer'] === $usuarioId)) {
            $model->cancelar($id);
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Interesse cancelado.'];
        }

        header('Location: /Aptus/interesses/meus');
        exit;
    }

    public function recebidos()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $model      = new Interesse();
        $interesses = $model->getPendentesByFreelancer((int) $_SESSION['usuario']['id']);

        $tituloPagina = 'Interesses Recebidos - Aptus';
        $cssPagina    = 'recebidos.css';

        require '../app/Views/interesses/recebidos.php';
    }

    public function criar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $anuncioId       = (int) ($_GET['anuncio'] ?? 0);
        $contratanteId   = (int) $_SESSION['usuario']['id'];
        $contratanteNome = $_SESSION['usuario']['nome'];
        $mensagem        = $_POST['mensagem'] ?? 'Olá! Tenho interesse no seu serviço.';

        if ($anuncioId <= 0) { header('Location: /Aptus/anuncios'); exit; }

        $anuncio = $this->anuncio->findById($anuncioId);
        if (!$anuncio) { header('Location: /Aptus/anuncios'); exit; }

        $freelancerId = (int) $anuncio['id_usuario'];

        if ($contratanteId === $freelancerId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não pode ter interesse no seu próprio anúncio.'];
            header('Location: /Aptus/anuncios/' . $anuncio['slug']);
            exit;
        }

        $pdo  = Database::getConnection();
        $sql  = "SELECT id_interesse, situacao FROM interesse
                 WHERE id_anuncio = ? AND id_contratante = ? AND situacao IN ('pendente', 'ativo', 'concluido')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$anuncioId, $contratanteId]);
        $interesseExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($interesseExistente) {
            $mensagemFlash = match ($interesseExistente['situacao']) {
                'pendente'  => 'Você já enviou uma proposta para este serviço. Aguarde a resposta do freelancer.',
                'ativo'     => 'Esta proposta já foi aceita. Aguarde a conclusão do serviço.',
                'concluido' => 'Este serviço já foi concluído.',
                default     => 'Você já enviou uma proposta para este serviço.',
            };
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => $mensagemFlash];
            header('Location: /Aptus/anuncios/' . $anuncio['slug']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $interesseModel = new Interesse();

            $dados = [
                'id_anuncio'       => $anuncioId,
                'id_contratante'   => $contratanteId,
                'id_freelancer'    => $freelancerId,
                'mensagem_inicial' => $mensagem,
            ];

            $interesseId = $interesseModel->create($dados);

            $sql  = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem)
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $freelancerId,
                $interesseId,
                'novo_interesse_pendente',
                'Nova proposta de serviço!',
                "O usuário {$contratanteNome} enviou uma proposta para o serviço '{$anuncio['titulo']}'. Aguardando sua resposta.",
            ]);

            $pdo->commit();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Proposta enviada! Aguarde a resposta do freelancer.'];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Erro em InteresseController::criar: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao enviar proposta. Tente novamente.'];
        }

        header('Location: /Aptus/anuncios/' . $anuncio['slug']);
        exit;
    }

    public function aceitar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseId = (int) ($_POST['id'] ?? 0);
        $usuarioId   = (int) $_SESSION['usuario']['id'];

        if ($interesseId <= 0) { header('Location: /Aptus/interesses/pendentes'); exit; }

        $interesseModel = new Interesse();
        $interesse      = $interesseModel->findById($interesseId);

        if (!$interesse || (int) $interesse['id_freelancer'] !== $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para esta ação.'];
            header('Location: /Aptus/interesses/pendentes'); exit;
        }

        if ($interesse['situacao'] !== 'pendente') {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Esta proposta já foi respondida.'];
            header('Location: /Aptus/interesses/pendentes'); exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();
            $interesseModel->aceitar($interesseId);

            $sql  = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem)
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $interesse['id_contratante'],
                $interesseId,
                'proposta_aceita',
                'Proposta aceita!',
                "O freelancer aceitou sua proposta para o serviço '{$interesse['anuncio_titulo']}'. Combine os detalhes pelo chat.",
            ]);

            $pdo->commit();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Proposta aceita! O cliente foi notificado.'];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Erro em InteresseController::aceitar: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao aceitar proposta. Tente novamente.'];
        }

        header('Location: /Aptus/interesses/pendentes');
        exit;
    }

    public function recusar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseId = (int) ($_POST['id'] ?? 0);
        $usuarioId   = (int) $_SESSION['usuario']['id'];

        if ($interesseId <= 0) { header('Location: /Aptus/interesses/pendentes'); exit; }

        $interesseModel = new Interesse();
        $interesse      = $interesseModel->findById($interesseId);

        if (!$interesse || (int) $interesse['id_freelancer'] !== $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para esta ação.'];
            header('Location: /Aptus/interesses/pendentes'); exit;
        }

        if ($interesse['situacao'] !== 'pendente') {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Esta proposta já foi respondida.'];
            header('Location: /Aptus/interesses/pendentes'); exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();
            $interesseModel->recusar($interesseId);

            $sql  = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem)
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $interesse['id_contratante'],
                $interesseId,
                'proposta_recusada',
                'Proposta recusada',
                "O freelancer recusou sua proposta para o serviço '{$interesse['anuncio_titulo']}'.",
            ]);

            $pdo->commit();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Proposta recusada.'];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Erro em InteresseController::recusar: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao recusar proposta. Tente novamente.'];
        }

        header('Location: /Aptus/interesses/pendentes');
        exit;
    }

    public function pendentes()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseModel = new Interesse();
        $interesses     = $interesseModel->getPendentesByFreelancer((int) $_SESSION['usuario']['id']);

        $tituloPagina = 'Propostas Pendentes - Aptus';
        $cssPagina    = 'pendentes.css';

        require '../app/Views/interesses/pendentes.php';
    }

    public function ativos()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseModel = new Interesse();
        $usuarioId      = (int) $_SESSION['usuario']['id'];
        $role           = (int) $_SESSION['usuario']['role'];

        if ($role === 3) {
            $comoContratante = $interesseModel->getAtivosByContratante($usuarioId);
            $comoFreelancer  = $interesseModel->getAtivosByFreelancer($usuarioId);
            $interesses      = array_merge($comoContratante, $comoFreelancer);
            usort($interesses, fn ($a, $b) => strtotime($b['data_interesse']) - strtotime($a['data_interesse']));
        } else {
            $pdo = Database::getConnection();
            $sql = "SELECT i.*, a.titulo AS anuncio_titulo, c.nome AS contratante_nome, f.nome AS freelancer_nome
                    FROM interesse i
                    JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                    JOIN usuario c ON i.id_contratante = c.id_usuario
                    JOIN usuario f ON i.id_freelancer = f.id_usuario
                    WHERE i.situacao = 'ativo'
                    ORDER BY i.data_interesse DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $interesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Pré-carrega "usuário já avaliou" em 1 query só (evita N+1)
        $ids = array_column($interesses, 'id_interesse');
        $usuarioJaAvaliou = [];

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "SELECT id_interesse FROM avaliacao
                    WHERE id_avaliador = ? AND id_interesse IN ($placeholders)";
            $stmt = Database::getConnection()->prepare($sql);
            $stmt->execute(array_merge([$usuarioId], $ids));

            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $idInt) {
                $usuarioJaAvaliou[(int) $idInt] = true;
            }
        }

        foreach ($interesses as &$int) {
            $int['usuario_ja_avaliou'] = isset($usuarioJaAvaliou[(int) $int['id_interesse']]);
        }
        unset($int);

        $tituloPagina = 'Serviços Ativos - Aptus';
        $cssPagina    = 'ativos.css';

        require '../app/Views/interesses/ativos.php';
    }

    public function meus()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $pdo       = Database::getConnection();

        $sql = "SELECT i.*,
                       a.titulo AS anuncio_titulo, a.preco AS anuncio_preco, a.slug AS anuncio_slug,
                       f.nome AS freelancer_nome, f.foto_perfil AS freelancer_foto,
                       cp.situacao_final
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                LEFT JOIN confirmacao_pagamento cp ON i.id_interesse = cp.id_interesse
                WHERE i.id_contratante = ?
                ORDER BY i.data_interesse DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $interesses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tituloPagina = 'Meus Interesses - Aptus';
        $cssPagina    = 'interesses.css';

        require '../app/Views/interesses/meus.php';
    }

    public function detalhes($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseId = (int) ($id ?? $_GET['id'] ?? 0);
        if ($interesseId <= 0) { header('Location: /Aptus/interesses/meus'); exit; }

        $pdo = Database::getConnection();
        $sql = "SELECT i.*,
                       a.titulo AS anuncio_titulo, a.preco AS anuncio_preco, a.descricao AS anuncio_descricao,
                       c.nome AS contratante_nome, c.email AS contratante_email, c.telefone AS contratante_telefone,
                       f.nome AS freelancer_nome, f.email AS freelancer_email, f.telefone AS freelancer_telefone,
                       cp.confirmado_contratante, cp.confirmado_freelancer, cp.situacao_final,
                       cp.valor_informado_contratante, cp.valor_informado_freelancer,
                       cp.forma_pagamento_contratante, cp.observacao_contratante
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                LEFT JOIN confirmacao_pagamento cp ON i.id_interesse = cp.id_interesse
                WHERE i.id_interesse = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$interesseId]);
        $interesse = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$interesse) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Interesse não encontrado.'];
            header('Location: /Aptus/interesses/meus'); exit;
        }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        if ((int) $interesse['id_contratante'] !== $usuarioId
            && (int) $interesse['id_freelancer'] !== $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para ver este interesse.'];
            header('Location: /Aptus/interesses/meus'); exit;
        }

        $tituloPagina = 'Detalhes do Interesse - Aptus';
        $cssPagina    = 'detalhes.css';

        require_once __DIR__ . '/../Models/Avaliacao.php';
        $avaliacaoModel = new Avaliacao();
        $jaAvaliou      = false;
        $avaliacaoData  = null;

        if (isset($interesse['situacao']) && $interesse['situacao'] === 'concluido') {
            $jaAvaliou = $avaliacaoModel->exists($interesse['id_interesse']);
            if ($jaAvaliou) {
                $avaliacaoData = $avaliacaoModel->findByInteresse($interesse['id_interesse']);
            }
        }

        require '../app/Views/interesses/detalhes.php';
    }

    public function confirmarExecucao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $interesseId = (int) ($_POST['id'] ?? 0);
        $usuarioId   = (int) $_SESSION['usuario']['id'];

        if ($interesseId <= 0) { header('Location: /Aptus/interesses/ativos'); exit; }

        $interesseModel = new Interesse();
        $interesse      = $interesseModel->findById($interesseId);

        if (!$interesse || !$interesseModel->pertence($interesseId, $usuarioId)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para esta ação.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        if ($interesse['situacao'] !== 'ativo') {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Este serviço não está ativo.'];
            header('Location: /Aptus/interesses/ativos'); exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $isContratante = ((int) $interesse['id_contratante'] === $usuarioId);
            $isFreelancer  = ((int) $interesse['id_freelancer'] === $usuarioId);

            if (!$interesseModel->usuarioJaAvaliou($interesseId, $usuarioId)) {
                $pdo->rollBack();
                $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Você precisa avaliar o serviço antes de confirmar a execução.'];
                header('Location: /Aptus/avaliacoes/criar/' . $interesseId);
                exit;
            }

            if ($isContratante) {
                $resultado = $interesseModel->confirmarExecucaoCliente($interesseId, $usuarioId);
            } elseif ($isFreelancer) {
                $resultado = $interesseModel->confirmarExecucaoFreelancer($interesseId, $usuarioId);
            } else {
                throw new RuntimeException('Usuário não faz parte deste interesse.');
            }

            if (!$resultado) {
                throw new RuntimeException('Falha ao gravar confirmação.');
            }

            $concluido = $interesseModel->verificarEConcluir($interesseId);

            $sql  = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem)
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            if ($concluido) {
                $titulo   = 'Serviço concluído!';
                $mensagem = "O serviço '{$interesse['anuncio_titulo']}' foi concluído. Ambos confirmaram a execução.";
                $stmt->execute([$interesse['id_contratante'], $interesseId, 'servico_concluido', $titulo, $mensagem]);
                $stmt->execute([$interesse['id_freelancer'],  $interesseId, 'servico_concluido', $titulo, $mensagem]);
            } else {
                $quem    = $isContratante ? 'Cliente' : 'Freelancer';
                $outroId = $isContratante ? (int) $interesse['id_freelancer'] : (int) $interesse['id_contratante'];
                $stmt->execute([
                    $outroId,
                    $interesseId,
                    'confirmacao_execucao',
                    "{$quem} confirmou a execução do serviço",
                    "O {$quem} confirmou que o serviço '{$interesse['anuncio_titulo']}' foi executado. Aguarde a confirmação da outra parte.",
                ]);
            }

            $pdo->commit();

            $_SESSION['flash'] = $concluido
                ? ['tipo' => 'sucesso', 'mensagem' => 'Serviço concluído com sucesso!']
                : ['tipo' => 'sucesso', 'mensagem' => 'Confirmação registrada. Aguarde a confirmação da outra parte.'];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Erro em InteresseController::confirmarExecucao: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível confirmar a execução. Tente novamente.'];
        }

        header('Location: /Aptus/interesses/ativos');
        exit;
    }
}