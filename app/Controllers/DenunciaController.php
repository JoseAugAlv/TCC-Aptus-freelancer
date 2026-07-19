<?php
// app/Controllers/DenunciaController.php

require_once __DIR__ . '/../Models/Denuncia.php';
require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Usuario.php';

class DenunciaController
{
    private $denuncia;
    private $anuncio;
    private $usuario;

    public function __construct()
    {
        $this->denuncia = new Denuncia();
        $this->anuncio = new Anuncio();
        $this->usuario = new Usuario();
    }

    /**
     * Pagina para criar uma denuncia
     * Rota: GET /denuncias/criar
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

        $tipo = $_GET['tipo'] ?? '';
        $id = (int)($_GET['id'] ?? 0);
        
        $motivos = $this->denuncia->getMotivos();
        $tituloPagina = 'Denunciar - Aptus';
        $cssPagina = 'denuncias.css';
        
        require '../app/Views/denuncias/criar.php';
    }

    /**
     * Salva uma nova denuncia
     * Rota: POST /denuncias/salvar
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

        $usuarioId = $_SESSION['usuario']['id'];
        $tipo = $_POST['tipo'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        // Validacoes
        if (empty($tipo) || $id <= 0 || empty($motivo)) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Preencha todos os campos obrigatorios.'
            ];
            header('Location: /Aptus/denuncias/criar');
            exit;
        }

        // Buscar dados do alvo
        $denunciadoId = 0;
        $anuncioId = null;

        if ($tipo === 'anuncio') {
            $anuncio = $this->anuncio->findById($id);
            if (!$anuncio) {
                $_SESSION['flash'] = [
                    'tipo' => 'erro',
                    'mensagem' => 'Anuncio nao encontrado.'
                ];
                header('Location: /Aptus/anuncios');
                exit;
            }
            $denunciadoId = $anuncio['id_usuario'];
            $anuncioId = $id;
            
            // Nao pode denunciar o proprio anuncio
            if ($denunciadoId == $usuarioId) {
                $_SESSION['flash'] = [
                    'tipo' => 'erro',
                    'mensagem' => 'Voce nao pode denunciar seu proprio anuncio.'
                ];
                header('Location: /Aptus/anuncios/' . $anuncio['slug']);
                exit;
            }
        } elseif ($tipo === 'perfil') {
            $usuario = $this->usuario->findById($id);
            if (!$usuario) {
                $_SESSION['flash'] = [
                    'tipo' => 'erro',
                    'mensagem' => 'Usuario nao encontrado.'
                ];
                header('Location: /Aptus/');
                exit;
            }
            $denunciadoId = $id;
            
            // Nao pode denunciar o proprio perfil
            if ($denunciadoId == $usuarioId) {
                $_SESSION['flash'] = [
                    'tipo' => 'erro',
                    'mensagem' => 'Voce nao pode denunciar seu proprio perfil.'
                ];
                header('Location: /Aptus/perfil');
                exit;
            }
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Tipo de denuncia invalido.'
            ];
            header('Location: /Aptus/');
            exit;
        }

        // Verificar se ja denunciou
        if ($this->denuncia->jaDenunciou($usuarioId, $denunciadoId, $anuncioId)) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Voce ja denunciou este conteudo. Aguarde a analise do moderador.'
            ];
            header('Location: /Aptus/');
            exit;
        }

        // Criar denuncia
        $dados = [
            'id_denunciante' => $usuarioId,
            'id_denunciado' => $denunciadoId,
            'id_anuncio' => $anuncioId,
            'motivo' => $motivo,
            'descricao' => $descricao
        ];

        $resultado = $this->denuncia->create($dados);

        if ($resultado) {
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Denuncia enviada com sucesso! Um moderador ira analisar.'
            ];
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao enviar denuncia. Tente novamente.'
            ];
        }

        // Redirecionar de volta
        if ($tipo === 'anuncio') {
            header('Location: /Aptus/anuncios/' . $anuncio['slug']);
        } else {
            header('Location: /Aptus/perfil/publico/' . $denunciadoId);
        }
        exit;
    }

    /**
     * Lista denuncias (moderador)
     * Rota: GET /moderator/denuncias
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

        $denuncias = $this->denuncia->getPendentes();
        $totalPendentes = $this->denuncia->countPendentes();
        
        $tituloPagina = 'Denuncias - Moderacao';
        $cssPagina = 'moderador.css';
        
        require '../app/Views/moderator/denuncias.php';
    }

    /**
     * Visualiza uma denuncia (moderador)
     * Rota: GET /moderator/denuncias/visualizar/{id}
     */
    public function visualizar($id = null)
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

        // Tentar pegar ID de diferentes formas
        if (!$id) {
            $id = (int)($_GET['id'] ?? 0);
        }

        if ($id <= 0) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'ID da denuncia invalido.'
            ];
            header('Location: /Aptus/moderator/denuncias');
            exit;
        }

        $denuncia = $this->denuncia->findById($id);
        
        if (!$denuncia) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Denuncia nao encontrada.'
            ];
            header('Location: /Aptus/moderator/denuncias');
            exit;
        }

        $motivos = $this->denuncia->getMotivos();
        
        $tituloPagina = 'Visualizar Denuncia - Aptus';
        $cssPagina = 'moderador.css';
        
        // CORREÇÃO: caminho correto para a pasta denuncias
        require '../app/Views/denuncias/visualizar.php';
    }

    /**
     * Aprova uma denuncia (moderador)
     * Rota: POST /moderator/denuncias/aprovar
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
        $moderadorId = $_SESSION['usuario']['id'];

        if ($id <= 0) {
            header('Location: /Aptus/moderator/denuncias');
            exit;
        }

        $this->denuncia->aprovar($id, $moderadorId);
        
        $_SESSION['flash'] = [
            'tipo' => 'sucesso',
            'mensagem' => 'Denuncia aprovada.'
        ];
        
        header('Location: /Aptus/moderator/denuncias');
        exit;
    }

    /**
     * Rejeita uma denuncia (moderador)
     * Rota: POST /moderator/denuncias/rejeitar
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
        $moderadorId = $_SESSION['usuario']['id'];

        if ($id <= 0) {
            header('Location: /Aptus/moderator/denuncias');
            exit;
        }

        $this->denuncia->rejeitar($id, $moderadorId);
        
        $_SESSION['flash'] = [
            'tipo' => 'sucesso',
            'mensagem' => 'Denuncia rejeitada.'
        ];
        
        header('Location: /Aptus/moderator/denuncias');
        exit;
    }
}