<?php
// app/Controllers/InteresseController.php

require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Usuario.php';
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

    /**
     * Cria um novo interesse (pendente)
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

        $anuncioId = (int)($_GET['anuncio'] ?? 0);
        $contratanteId = $_SESSION['usuario']['id'];
        $contratanteNome = $_SESSION['usuario']['nome'];
        $mensagem = $_POST['mensagem'] ?? "Olá! Tenho interesse no seu serviço.";

        if ($anuncioId <= 0) {
            header('Location: /Aptus/anuncios');
            exit;
        }

        $anuncio = $this->anuncio->findById($anuncioId);
        
        if (!$anuncio) {
            header('Location: /Aptus/anuncios');
            exit;
        }

        $freelancerId = $anuncio['id_usuario'];
        $freelancerNome = $anuncio['freelancer_nome'];

        if ($contratanteId == $freelancerId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Você não pode ter interesse no seu próprio anúncio.'
            ];
            header('Location: /Aptus/anuncios/' . $anuncio['slug']);
            exit;
        }

        $pdo = Database::getConnection();
        
        // Verificar se já existe interesse ativo
        $sql = "SELECT id_interesse, situacao FROM interesse 
                WHERE id_anuncio = ? AND id_contratante = ? AND situacao IN ('pendente', 'ativo', 'concluido')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$anuncioId, $contratanteId]);
        $interesseExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($interesseExistente) {
            $mensagemFlash = match($interesseExistente['situacao']) {
                'pendente' => 'Você já enviou uma proposta para este serviço. Aguarde a resposta do freelancer.',
                'ativo' => 'Esta proposta já foi aceita. Aguarde a conclusão do serviço.',
                'concluido' => 'Este serviço já foi concluído.',
                default => 'Você já enviou uma proposta para este serviço.'
            };
            
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => $mensagemFlash];
            header('Location: /Aptus/anuncios/' . $anuncio['slug']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            require_once __DIR__ . '/../Models/Interesse.php';
            $interesseModel = new Interesse();

            $dados = [
                'id_anuncio' => $anuncioId,
                'id_contratante' => $contratanteId,
                'id_freelancer' => $freelancerId,
                'mensagem_inicial' => $mensagem
            ];
            
            $interesseId = $interesseModel->create($dados);

            // Notificar freelancer
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $freelancerId,
                $interesseId,
                'novo_interesse_pendente',
                'Nova proposta de serviço!',
                "O usuário {$contratanteNome} enviou uma proposta para o serviço '{$anuncio['titulo']}'. Aguardando sua resposta."
            ]);

            $pdo->commit();

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Proposta enviada! Aguarde a resposta do freelancer.'
            ];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao enviar proposta. Tente novamente.'
            ];
        }

        header('Location: /Aptus/anuncios/' . $anuncio['slug']);
        exit;
    }

    /**
     * Aceita uma proposta (freelancer)
     */
    public function aceitar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $interesseId = (int)($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if ($interesseId <= 0) {
            header('Location: /Aptus/interesses/pendentes');
            exit;
        }

        require_once __DIR__ . '/../Models/Interesse.php';
        $interesseModel = new Interesse();

        $interesse = $interesseModel->findById($interesseId);
        if (!$interesse || $interesse['id_freelancer'] != $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para esta ação.'];
            header('Location: /Aptus/interesses/pendentes');
            exit;
        }

        if ($interesse['situacao'] != 'pendente') {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Esta proposta já foi respondida.'];
            header('Location: /Aptus/interesses/pendentes');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            $interesseModel->aceitar($interesseId);

            // Notificar contratante
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $interesse['id_contratante'],
                $interesseId,
                'proposta_aceita',
                'Proposta aceita!',
                "O freelancer aceitou sua proposta para o serviço '{$interesse['anuncio_titulo']}'. Combine os detalhes pelo chat."
            ]);

            $pdo->commit();

            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Proposta aceita! O cliente foi notificado.'];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao aceitar proposta.'];
        }

        header('Location: /Aptus/interesses/pendentes');
        exit;
    }

    /**
     * Recusa uma proposta (freelancer)
     */
    public function recusar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $interesseId = (int)($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if ($interesseId <= 0) {
            header('Location: /Aptus/interesses/pendentes');
            exit;
        }

        require_once __DIR__ . '/../Models/Interesse.php';
        $interesseModel = new Interesse();

        $interesse = $interesseModel->findById($interesseId);
        if (!$interesse || $interesse['id_freelancer'] != $usuarioId) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para esta ação.'];
            header('Location: /Aptus/interesses/pendentes');
            exit;
        }

        if ($interesse['situacao'] != 'pendente') {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Esta proposta já foi respondida.'];
            header('Location: /Aptus/interesses/pendentes');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            $interesseModel->recusar($interesseId);

            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $interesse['id_contratante'],
                $interesseId,
                'proposta_recusada',
                'Proposta recusada',
                "O freelancer recusou sua proposta para o serviço '{$interesse['anuncio_titulo']}'."
            ]);

            $pdo->commit();

            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Proposta recusada.'];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao recusar proposta.'];
        }

        header('Location: /Aptus/interesses/pendentes');
        exit;
    }

    /**
     * Lista propostas pendentes (freelancer)
     */
    public function pendentes()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        require_once __DIR__ . '/../Models/Interesse.php';
        $interesseModel = new Interesse();

        $usuarioId = $_SESSION['usuario']['id'];
        $interesses = $interesseModel->getPendentesByFreelancer($usuarioId);
        
        $tituloPagina = 'Propostas Pendentes - Aptus';
        $cssPagina = 'interesses.css';
        
        require '../app/Views/interesses/pendentes.php';
    }

    /**
     * Lista interesses ativos (contratante e freelancer)
     */
    public function ativos()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        require_once __DIR__ . '/../Models/Interesse.php';
        $interesseModel = new Interesse();

        $usuarioId = $_SESSION['usuario']['id'];
        $role = $_SESSION['usuario']['role'];
        
        if ($role == 3) {
            // Usuario comum - ver os dois lados
            $comoContratante = $interesseModel->getAtivosByContratante($usuarioId);
            $comoFreelancer = $interesseModel->getAtivosByFreelancer($usuarioId);
            $interesses = array_merge($comoContratante, $comoFreelancer);
            usort($interesses, function($a, $b) {
                return strtotime($b['data_interesse']) - strtotime($a['data_interesse']);
            });
        } else {
            // Admin/moderador - ve tudo
            $pdo = Database::getConnection();
            $sql = "SELECT i.*, 
                           a.titulo as anuncio_titulo,
                           c.nome as contratante_nome,
                           f.nome as freelancer_nome
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
        
        $tituloPagina = 'Serviços Ativos - Aptus';
        $cssPagina = 'interesses.css';
        
        require '../app/Views/interesses/ativos.php';
    }

    /**
     * Lista interesses enviados pelo contratante (meus interesses)
     * Rota: GET /interesses/meus
     */
    public function meus()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        
        $pdo = Database::getConnection();
        $sql = "SELECT i.*, 
                    a.titulo as anuncio_titulo, a.preco as anuncio_preco, a.slug as anuncio_slug,
                    f.nome as freelancer_nome, f.foto_perfil as freelancer_foto,
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
        $cssPagina = 'interesses.css';
        
        require '../app/Views/interesses/meus.php';
    }

    /**
     * Detalhes de um interesse especifico
     * Rota: GET /interesses/detalhes/{id}
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

        $interesseId = (int)($id ?? $_GET['id'] ?? 0);
        
        if ($interesseId <= 0) {
            header('Location: /Aptus/interesses/meus');
            exit;
        }

        $pdo = Database::getConnection();
        $sql = "SELECT i.*, 
                    a.titulo as anuncio_titulo, a.preco as anuncio_preco, a.descricao as anuncio_descricao,
                    c.nome as contratante_nome, c.email as contratante_email, c.telefone as contratante_telefone,
                    f.nome as freelancer_nome, f.email as freelancer_email, f.telefone as freelancer_telefone,
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
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Interesse nao encontrado.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }
        
        // Verificar se o usuario e parte do interesse
        $usuarioId = $_SESSION['usuario']['id'];
        if ($interesse['id_contratante'] != $usuarioId && $interesse['id_freelancer'] != $usuarioId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Voce nao tem permissao para ver este interesse.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }
        
        $tituloPagina = 'Detalhes do Interesse - Aptus';
        $cssPagina = 'interesses.css';
        
        require '../app/Views/interesses/detalhes.php';
    }

    /**
     * Confirma execucao do servico
     * Ambos precisam avaliar antes de confirmar
     */
    public function confirmarExecucao()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $interesseId = (int)($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if ($interesseId <= 0) {
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        require_once __DIR__ . '/../Models/Interesse.php';
        require_once __DIR__ . '/../Models/Avaliacao.php';
        
        $interesseModel = new Interesse();
        $avaliacaoModel = new Avaliacao();

        $interesse = $interesseModel->findById($interesseId);
        if (!$interesse || !$interesseModel->pertence($interesseId, $usuarioId)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Voce nao tem permissao para esta acao.'];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        if ($interesse['situacao'] != 'ativo') {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Este servico nao esta ativo.'];
            header('Location: /Aptus/interesses/ativos');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            $isContratante = ($interesse['id_contratante'] == $usuarioId);
            $isFreelancer = ($interesse['id_freelancer'] == $usuarioId);

            // Verificar se o usuario ja avaliou
            $usuarioJaAvaliou = $interesseModel->usuarioJaAvaliou($interesseId, $usuarioId);
            
            if (!$usuarioJaAvaliou) {
                $pdo->rollBack();
                $quem = $isContratante ? 'cliente' : 'freelancer';
                $_SESSION['flash'] = [
                    'tipo' => 'aviso',
                    'mensagem' => 'Voce precisa avaliar o servico antes de confirmar a execucao.'
                ];
                header('Location: /Aptus/avaliacoes/criar/' . $interesseId);
                exit;
            }

            if ($isContratante) {
                $resultado = $interesseModel->confirmarExecucaoCliente($interesseId, $usuarioId);
            } elseif ($isFreelancer) {
                $resultado = $interesseModel->confirmarExecucaoFreelancer($interesseId, $usuarioId);
            } else {
                throw new Exception('Usuario nao faz parte deste interesse.');
            }

            if (!$resultado) {
                throw new Exception('Erro ao confirmar execucao.');
            }

            // Verificar se ambos confirmaram
            $concluido = $interesseModel->verificarEConcluir($interesseId);

            // Notificacoes
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($concluido) {
                $titulo = 'Servico concluido!';
                $mensagem = "O servico '{$interesse['anuncio_titulo']}' foi concluido. Ambos confirmaram a execucao.";
                $stmt->execute([$interesse['id_contratante'], $interesseId, 'servico_concluido', $titulo, $mensagem]);
                $stmt->execute([$interesse['id_freelancer'], $interesseId, 'servico_concluido', $titulo, $mensagem]);
            } else {
                $quem = $isContratante ? 'Cliente' : 'Freelancer';
                $outroId = $isContratante ? $interesse['id_freelancer'] : $interesse['id_contratante'];
                $stmt->execute([
                    $outroId,
                    $interesseId,
                    'confirmacao_execucao',
                    "{$quem} confirmou a execucao do servico",
                    "O {$quem} confirmou que o servico '{$interesse['anuncio_titulo']}' foi executado. Aguarde a confirmacao da outra parte."
                ]);
            }

            $pdo->commit();

            if ($concluido) {
                $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Servico concluido com sucesso!'];
            } else {
                $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Confirmacao registrada. Aguarde a confirmacao da outra parte.'];
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao confirmar execucao: ' . $e->getMessage()];
        }

        header('Location: /Aptus/interesses/ativos');
        exit;
    }




}