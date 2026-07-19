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
     * Cria um novo interesse (POST)
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

        // Buscar dados do anúncio
        $anuncio = $this->anuncio->findById($anuncioId);
        
        if (!$anuncio) {
            header('Location: /Aptus/anuncios');
            exit;
        }

        $freelancerId = $anuncio['id_usuario'];
        $freelancerNome = $anuncio['freelancer_nome'];

        // Verificar se o contratante é o dono do anúncio
        if ($contratanteId == $freelancerId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Você não pode ter interesse no seu próprio anúncio.'
            ];
            header('Location: /Aptus/anuncios/' . $anuncio['slug']);
            exit;
        }

        // Verificar se já existe interesse ativo
        $pdo = Database::getConnection();
        $sql = "SELECT id_interesse, situacao FROM interesse 
                WHERE id_anuncio = ? AND id_contratante = ? AND situacao IN ('ativo', 'concluido')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$anuncioId, $contratanteId]);
        $interesseExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($interesseExistente) {
            $mensagemFlash = $interesseExistente['situacao'] === 'concluido' 
                ? 'Este serviço já foi concluído.' 
                : 'Você já enviou uma proposta para este serviço.';
            
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => $mensagemFlash
            ];
            header('Location: /Aptus/anuncios/' . $anuncio['slug']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Inserir interesse
            $sql = "INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $anuncioId,
                $contratanteId,
                $freelancerId,
                $mensagem
            ]);
            
            $interesseId = $pdo->lastInsertId();

            // Criar notificação para o freelancer
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $freelancerId,
                $interesseId,
                'novo_interesse',
                'Novo interesse no seu serviço!',
                "O usuário {$contratanteNome} demonstrou interesse no seu serviço '{$anuncio['titulo']}'."
            ]);

            $pdo->commit();

            // Enviar e-mail para o freelancer
            $this->enviarEmailInteresse($anuncio, $contratanteNome, $freelancerNome);

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Interesse registrado! O freelancer foi notificado.'
            ];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao registrar interesse. Tente novamente.'
            ];
        }

        header('Location: /Aptus/anuncios/' . $anuncio['slug']);
        exit;
    }

    /**
     * Lista interesses recebidos pelo freelancer
     */
    public function recebidos()
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
                       a.titulo as anuncio_titulo, a.preco as anuncio_preco,
                       c.nome as contratante_nome, c.foto_perfil as contratante_foto,
                       cp.situacao_final
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                LEFT JOIN confirmacao_pagamento cp ON i.id_interesse = cp.id_interesse
                WHERE i.id_freelancer = ?
                ORDER BY i.data_interesse DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $interesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $tituloPagina = 'Interesses Recebidos - Aptus';
        $cssPagina = 'interesses.css';
        
        require '../app/Views/interesses/recebidos.php';
    }

    /**
     * Detalhes de um interesse específico
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
            header('Location: /Aptus/interesses/meus');
            exit;
        }
        
        // Verificar se o usuário é parte do interesse
        $usuarioId = $_SESSION['usuario']['id'];
        if ($interesse['id_contratante'] != $usuarioId && $interesse['id_freelancer'] != $usuarioId) {
            header('Location: /Aptus/interesses/meus');
            exit;
        }
        
        $tituloPagina = 'Detalhes do Interesse - Aptus';
        $cssPagina = 'interesses.css';
        
        require '../app/Views/interesses/detalhes.php';
    }
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
                    a.titulo as anuncio_titulo, a.preco as anuncio_preco,
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
     * Cancela um interesse
     */
    public function cancelar()
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
            header('Location: /Aptus/interesses/meus');
            exit;
        }

        $pdo = Database::getConnection();
        
        // Verificar se o interesse pertence ao usuário
        $sql = "SELECT id_contratante, id_freelancer, id_anuncio FROM interesse WHERE id_interesse = ? AND situacao = 'ativo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$interesseId]);
        $interesse = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$interesse) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Interesse não encontrado.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }
        
        if ($interesse['id_contratante'] != $usuarioId && $interesse['id_freelancer'] != $usuarioId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Você não tem permissão para cancelar este interesse.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }

        try {
            $sql = "UPDATE interesse SET situacao = 'cancelado' WHERE id_interesse = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$interesseId]);
            
            // Notificar a outra parte
            $notificadoId = ($interesse['id_contratante'] == $usuarioId) ? $interesse['id_freelancer'] : $interesse['id_contratante'];
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $notificadoId,
                $interesseId,
                'interesse_cancelado',
                'Interesse cancelado',
                "O usuário cancelou o interesse no serviço."
            ]);

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Interesse cancelado com sucesso.'
            ];

        } catch (Exception $e) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao cancelar interesse.'
            ];
        }

        header('Location: /Aptus/interesses/meus');
        exit;
    }

    /**
     * Conclui um interesse
     */
    public function concluir()
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
            header('Location: /Aptus/interesses/meus');
            exit;
        }

        $pdo = Database::getConnection();
        
        // Verificar se o interesse pertence ao usuário (apenas freelancer pode concluir)
        $sql = "SELECT id_freelancer, id_contratante, id_anuncio FROM interesse WHERE id_interesse = ? AND situacao = 'ativo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$interesseId]);
        $interesse = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$interesse) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Interesse não encontrado.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }
        
        // Apenas o freelancer pode concluir
        if ($interesse['id_freelancer'] != $usuarioId) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Apenas o freelancer pode concluir o interesse.'
            ];
            header('Location: /Aptus/interesses/meus');
            exit;
        }

        try {
            $sql = "UPDATE interesse SET situacao = 'concluido', data_conclusao = NOW() WHERE id_interesse = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$interesseId]);
            
            // Notificar o contratante
            $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $interesse['id_contratante'],
                $interesseId,
                'interesse_concluido',
                'Serviço concluído!',
                "O serviço foi concluído com sucesso."
            ]);

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Serviço concluído com sucesso!'
            ];

        } catch (Exception $e) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao concluir serviço.'
            ];
        }

        header('Location: /Aptus/interesses/recebidos');
        exit;
    }

    /**
     * Envia e-mail de notificação para o freelancer
     */
    private function enviarEmailInteresse($anuncio, $contratanteNome, $freelancerNome)
    {
        $mailer = new Mailer();
        
        $assunto = "Novo interesse no seu serviço - Aptus";
        
        $mensagem = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a2f3e; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #006577; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8fafc; padding: 30px; border-radius: 0 0 8px 8px; border: 1px solid #e2e8f0; }
                .btn { display: inline-block; padding: 10px 20px; background: #006577; color: #fff; text-decoration: none; border-radius: 6px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin: 0; color: #fff;'>📩 Novo Interesse</h2>
                </div>
                <div class='content'>
                    <p>Olá <strong>" . htmlspecialchars($freelancerNome) . "</strong>,</p>
                    <p>O usuário <strong>" . htmlspecialchars($contratanteNome) . "</strong> demonstrou interesse no seu serviço:</p>
                    <div style='background: #fff; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                        <p><strong>Serviço:</strong> " . htmlspecialchars($anuncio['titulo']) . "</p>
                        <p><strong>Preço:</strong> R$ " . number_format($anuncio['preco'], 2, ',', '.') . "</p>
                    </div>
                    <p style='text-align: center;'>
                        <a href='" . $_ENV['APP_URL'] . "/interesses/recebidos' class='btn'>Ver Interesses</a>
                    </p>
                    <p>Entre em contato com o cliente para negociar os detalhes.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Aptus - Conectando Talentos</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Buscar email do freelancer
        $freelancer = $this->usuario->findById($anuncio['id_usuario']);
        if ($freelancer) {
            $mailer->enviar($freelancer['email'], $freelancer['nome'], $assunto, $mensagem);
        }
    }
}