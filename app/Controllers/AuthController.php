<?php
// app/Controllers/AuthController.php

// INCLUI A CONFIGURAÇÃO DE SESSÃO
require_once __DIR__ . '/../Config/SessionConfig.php';

// CONFIGURA A SESSÃO CORRETAMENTE
SessionConfig::configure();

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Usuario.php';

class AuthController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    public function index()
    {
        // Se já estiver logado, redireciona
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }
        
        $tituloPagina = 'Login - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/index.php';
    }

    public function login()
    {
        // Se já estiver logado, redireciona
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        // Validação básica
        if (empty($email) || empty($senha)) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Preencha todos os campos.'
            ];
            header('Location: /Aptus/login');
            exit;
        }

        $pdo = Database::getConnection();

        $sql = "SELECT * FROM usuario WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica usuário e senha
        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'E-mail ou senha incorretos.'
            ];
            header('Location: /Aptus/login');
            exit;
        }

        // Verifica se o email foi verificado
        if (!$usuario['email_verificado']) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Por favor, verifique seu e-mail antes de fazer login.'
            ];
            header('Location: /Aptus/login');
            exit;
        }

        // Verifica se o usuário está ativo
        if (isset($usuario['ativo']) && !$usuario['ativo']) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Usuário inativo. Entre em contato com o administrador.'
            ];
            header('Location: /Aptus/login');
            exit;
        }

        // Verifica se foi banido
        if ($usuario['banido']) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Sua conta foi banida. Motivo: ' . ($usuario['motivo_banimento'] ?? 'Não informado')
            ];
            header('Location: /Aptus/login');
            exit;
        }

        // Busca o perfil do usuário
        $idPerfil = $usuario['id_perfil'] ?? 3;
        $idProjeto = null;

        // Cria sessão
        $_SESSION['usuario'] = [
            'id' => $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'role' => (int) $idPerfil,
            'id_projeto' => $idProjeto
        ];

        // LOG DE AUDITORIA - LOGIN
        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        SecurityHelper::logAuditoria(
            'login_usuario',
            $usuario['id_usuario'],
            'Login realizado com sucesso - Email: ' . $email,
            'info'
        );

        // Redireciona para a página inicial
        header('Location: /Aptus/');
        exit;
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // LOG DE AUDITORIA - LOGOUT
        if (isset($_SESSION['usuario'])) {
            require_once __DIR__ . '/../Helpers/SecurityHelper.php';
            SecurityHelper::logAuditoria(
                'logout_usuario',
                $_SESSION['usuario']['id'],
                'Logout realizado - Email: ' . $_SESSION['usuario']['email'],
                'info'
            );
        }

        session_destroy();
        setcookie("remember_token", "", time() - 3600, "/");

        header('Location: /Aptus/login');
        exit;
    }
}