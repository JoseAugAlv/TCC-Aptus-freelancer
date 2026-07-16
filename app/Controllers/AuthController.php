<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../Models/Usuario.php';

class AuthController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    /**
     * Mostra a página de login
     */
    public function index()
    {
        session_start();
        
        // Se já estiver logado, vai para home
        if (isset($_SESSION['usuario_id'])) {
            header('Location: /Aptus/');
            exit;
        }
        
        $tituloPagina = 'Login - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/index.php';
    }

    /**
     * Processa o login
     */
    public function login()
    {
        session_start();

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        // Validação básica
        if (empty($email) || empty($senha)) {
            $_SESSION['flash'] = 'Preencha todos os campos.';
            header('Location: /Aptus/login');
            exit;
        }

        // Buscar usuário
        $usuario = $this->usuario->findByEmail($email);

        // Verificar se existe e se a senha está correta
        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            $_SESSION['flash'] = 'E-mail ou senha incorretos.';
            header('Location: /Aptus/login');
            exit;
        }

        // Verificar se o usuário está ativo
        if (!$usuario['ativo']) {
            $_SESSION['flash'] = 'Usuário inativo.';
            header('Location: /Aptus/login');
            exit;
        }

        // Verificar se foi banido
        if ($usuario['banido']) {
            $_SESSION['flash'] = 'Usuário banido.';
            header('Location: /Aptus/login');
            exit;
        }

        // Criar sessão
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_perfil'] = $usuario['id_perfil'];

        // Redirecionar
        header('Location: /Aptus/');
        exit;
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /Aptus/login');
        exit;
    }
}