<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../Config/SessionConfig.php';
SessionConfig::configure();

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Core/Mailer.php';

class AuthController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    /**
     * Pagina de login
     */
    public function index()
    {
        if (isset($_SESSION['usuario'])) {
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
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos.'];
            header('Location: /Aptus/login');
            exit;
        }

        $usuario = $this->usuario->findByEmail($email);

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail ou senha incorretos.'];
            header('Location: /Aptus/login');
            exit;
        }

        if (!$usuario['email_verificado']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Por favor, verifique seu e-mail antes de fazer login.'];
            header('Location: /Aptus/login');
            exit;
        }

        if (!$usuario['ativo']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Usuario inativo. Entre em contato com o administrador.'];
            header('Location: /Aptus/login');
            exit;
        }

        if ($usuario['banido']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Sua conta foi banida. Motivo: ' . ($usuario['motivo_banimento'] ?? 'Nao informado')];
            header('Location: /Aptus/login');
            exit;
        }

        $idPerfil = $usuario['id_perfil'] ?? 3;

        $_SESSION['usuario'] = [
            'id' => $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'role' => (int) $idPerfil
        ];

        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        SecurityHelper::logAuditoria(
            'login_usuario',
            $usuario['id_usuario'],
            'Login realizado com sucesso - Email: ' . $email,
            'info'
        );

        header('Location: /Aptus/');
        exit;
    }

    /**
     * Logout
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

    /**
     * Pagina de cadastro
     */
    public function cadastrar()
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }

        $tituloPagina = 'Criar Conta - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/cadastrar.php';
    }

    /**
     * Processa o cadastro
     */
    public function salvar()
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';

        // Validacoes
        if (empty($nome) || empty($email) || empty($senha)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos obrigatorios.'];
            header('Location: /Aptus/login/cadastrar');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail invalido.'];
            header('Location: /Aptus/login/cadastrar');
            exit;
        }

        if (strlen($senha) < 6) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no minimo 6 caracteres.'];
            header('Location: /Aptus/login/cadastrar');
            exit;
        }

        if ($senha !== $senhaConfirm) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'As senhas nao coincidem.'];
            header('Location: /Aptus/login/cadastrar');
            exit;
        }

        // Verificar se email ja existe
        $usuarioExistente = $this->usuario->findByEmail($email);
        if ($usuarioExistente) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Este e-mail ja esta cadastrado.'];
            header('Location: /Aptus/login/cadastrar');
            exit;
        }

        // Gerar token de verificacao
        $token = bin2hex(random_bytes(32));

        // Criar usuario
        $dados = [
            'id_perfil' => 3,
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'token_verificacao' => $token
        ];

        $resultado = $this->usuario->create($dados);

        if ($resultado) {
            // Enviar email de verificacao
            $mailer = new Mailer();
            $mailer->sendVerificationEmail($email, $nome, $token);

            $_SESSION['flash'] = [
                'tipo' => 'sucesso', 
                'mensagem' => 'Cadastro realizado! Enviamos um e-mail de verificacao para ' . $email . '.'
            ];
            header('Location: /Aptus/login?success=1&email=' . urlencode($email));
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao cadastrar. Tente novamente.'];
            header('Location: /Aptus/login/cadastrar');
        }
        exit;
    }

    /**
     * Verifica o email do usuario
     */
    public function verificar()
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            header('Location: /Aptus/login?status=erro&mensagem=Token+invalido');
            exit;
        }

        $resultado = $this->usuario->verificarEmail($token);

        if ($resultado) {
            header('Location: /Aptus/login?status=sucesso&mensagem=E-mail+verificado+com+sucesso!');
        } else {
            header('Location: /Aptus/login?status=erro&mensagem=Token+invalido+ou+expirado');
        }
        exit;
    }

    /**
     * Reenviar email de verificacao
     */
    public function reenviarVerificacao()
    {
        $email = $_GET['email'] ?? '';

        if (empty($email)) {
            header('Location: /Aptus/login');
            exit;
        }

        $usuario = $this->usuario->findByEmail($email);

        if (!$usuario) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Usuario nao encontrado.'];
            header('Location: /Aptus/login');
            exit;
        }

        if ($usuario['email_verificado']) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Este e-mail ja foi verificado.'];
            header('Location: /Aptus/login');
            exit;
        }

        // Gerar novo token
        $token = bin2hex(random_bytes(32));
        
        $pdo = Database::getConnection();
        $sql = "UPDATE usuario SET token_verificacao = ? WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token, $usuario['id_usuario']]);

        // Enviar email
        $mailer = new Mailer();
        $mailer->sendVerificationEmail($email, $usuario['nome'], $token);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'E-mail de verificacao reenviado para ' . $email . '.'];
        header('Location: /Aptus/login');
        exit;
    }

    /**
     * Pagina de esqueci a senha
     */
    public function esqueciSenha()
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }

        $tituloPagina = 'Esqueci a Senha - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/esqueci_senha.php';
    }

    /**
     * Envia token de redefinicao de senha
     */
    public function enviarToken()
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail invalido.'];
            header('Location: /Aptus/auth/esqueci-senha');
            exit;
        }

        $usuario = $this->usuario->findByEmail($email);

        if (!$usuario) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail nao encontrado.'];
            header('Location: /Aptus/auth/esqueci-senha');
            exit;
        }

        // Gerar token
        $token = bin2hex(random_bytes(32));

        // Salvar token
        $resultado = $this->usuario->salvarTokenReset($email, $token);

        if ($resultado) {
            // Enviar email
            $mailer = new Mailer();
            $mailer->sendResetPassword($email, $usuario['nome'], $token);

            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Enviamos um e-mail com instrucoes para redefinir sua senha.'
            ];
            header('Location: /Aptus/login');
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao enviar token. Tente novamente.'];
            header('Location: /Aptus/auth/esqueci-senha');
        }
        exit;
    }

    /**
     * Pagina de redefinicao de senha
     */
    public function redefinir()
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }

        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            header('Location: /Aptus/login');
            exit;
        }

        // Verificar se token e valido
        $tokenData = $this->usuario->findTokenReset($token);

        if (!$tokenData) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Token invalido ou expirado.'];
            header('Location: /Aptus/login');
            exit;
        }

        $tituloPagina = 'Redefinir Senha - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/redefinir_senha.php';
    }

    /**
     * Processa a redefinicao de senha
     */
    public function redefinirSenha()
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: /Aptus/');
            exit;
        }

        $token = $_POST['token'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';

        if (empty($token) || empty($senha)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos.'];
            header('Location: /Aptus/auth/redefinir?token=' . $token);
            exit;
        }

        if (strlen($senha) < 6) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no minimo 6 caracteres.'];
            header('Location: /Aptus/auth/redefinir?token=' . $token);
            exit;
        }

        if ($senha !== $senhaConfirm) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'As senhas nao coincidem.'];
            header('Location: /Aptus/auth/redefinir?token=' . $token);
            exit;
        }

        $resultado = $this->usuario->redefinirSenha($token, $senha);

        if ($resultado) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Senha redefinida com sucesso! Faca login.'];
            header('Location: /Aptus/login?sucesso=1');
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Token invalido ou expirado.'];
            header('Location: /Aptus/login');
        }
        exit;
    }
}