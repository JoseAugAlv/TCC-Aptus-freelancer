<?php
// app/Controllers/PerfilController.php

require_once __DIR__ . '/../Models/Usuario.php';

class PerfilController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    /**
     * Página de perfil do usuário logado
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        // Buscar dados do usuário no banco
        $usuarioData = $this->usuario->findById($_SESSION['usuario']['id']);
        
        if (!$usuarioData) {
            session_destroy();
            header('Location: /Aptus/login');
            exit;
        }
        
        $tituloPagina = 'Meu Perfil - Aptus';
        $cssPagina = 'perfil.css';
        
        require '../app/Views/perfil/index.php';
    }

    /**
     * Página de edição do perfil
     */
    public function editar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        // Buscar dados do usuário no banco
        $usuarioData = $this->usuario->findById($_SESSION['usuario']['id']);
        
        if (!$usuarioData) {
            session_destroy();
            header('Location: /Aptus/login');
            exit;
        }
        
        $tituloPagina = 'Editar Perfil - Aptus';
        $cssPagina = 'perfil.css';
        
        require '../app/Views/perfil/editar.php';
    }

    /**
     * Processa a atualização do perfil
     */
    public function atualizar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $id = $_SESSION['usuario']['id'];
        
        // Dados do formulário
        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'cidade' => trim($_POST['cidade'] ?? ''),
            'estado' => trim($_POST['estado'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? '')
        ];

        // Validações
        if (empty($dados['nome']) || empty($dados['email'])) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Nome e e-mail são obrigatórios.'
            ];
            header('Location: /Aptus/perfil/editar');
            exit;
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'E-mail inválido.'
            ];
            header('Location: /Aptus/perfil/editar');
            exit;
        }

        // Atualizar no banco
        if ($this->usuario->update($id, $dados)) {
            // Atualizar sessão
            $_SESSION['usuario']['nome'] = $dados['nome'];
            $_SESSION['usuario']['email'] = $dados['email'];
            
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Perfil atualizado com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao atualizar perfil. Tente novamente.'
            ];
        }

        header('Location: /Aptus/perfil');
        exit;
    }

    /**
     * Página de portfólio do usuário
     */
    public function portfolio()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $tituloPagina = 'Meu Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        
        require '../app/Views/perfil/portfolio.php';
    }

    /**
     * Página de perfil público de outro usuário
     */
    public function publico($id = null)
    {
        if (!$id) {
            header('Location: /Aptus/');
            exit;
        }
        
        // Buscar dados do usuário no banco
        $perfilData = $this->usuario->findById($id);
        
        if (!$perfilData) {
            header('Location: /Aptus/');
            exit;
        }
        
        $tituloPagina = 'Perfil - Aptus';
        $cssPagina = 'perfil.css';
        
        require '../app/Views/perfil/publico.php';
    }
}