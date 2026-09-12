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

    public function index()
    {
        if (isset($_SESSION['usuario'])) { header('Location: /Aptus/'); exit; }

        $tituloPagina = 'Login - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/index.php';
    }

    public function login()
    {
        if (isset($_SESSION['usuario'])) { header('Location: /Aptus/'); exit; }

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos.'];
            header('Location: /Aptus/login'); exit;
        }

        require_once __DIR__ . '/../Helpers/LoginAttempt.php';
        if (!LoginAttempt::check($email)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Muitas tentativas. Tente novamente mais tarde.'];
            header('Location: /Aptus/login'); exit;
        }

        $usuario = $this->usuario->findByEmail($email);
        $lembrar = isset($_POST['lembrar']) && $_POST['lembrar'] == '1';

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            LoginAttempt::increment($email);
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail ou senha incorretos.'];
            header('Location: /Aptus/login'); exit;
        }

        if (!$usuario['email_verificado']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Por favor, verifique seu e-mail antes de fazer login.'];
            header('Location: /Aptus/login'); exit;
        }

        if (!$usuario['ativo']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Usuário inativo. Entre em contato com o administrador.'];
            header('Location: /Aptus/login'); exit;
        }

        if (!empty($usuario['banido'])) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Sua conta foi banida. Motivo: ' . ($usuario['motivo_banimento'] ?? 'Não informado')];
            header('Location: /Aptus/login'); exit;
        }

        // Mitiga session fixation
        session_regenerate_id(true);

        if ($lembrar) {
            $token = bin2hex(random_bytes(32));
            $pdo   = Database::getConnection();
            $stmt  = $pdo->prepare("UPDATE usuario SET remember_token = ? WHERE id_usuario = ?");
            $stmt->execute([$token, $usuario['id_usuario']]);

            $appUrl = parse_url(\Config::get('APP_URL', '/Aptus'), PHP_URL_PATH) ?: '/Aptus';

            setcookie('remember_token', $token, [
                'expires'  => time() + 30 * 24 * 3600,
                'path'     => rtrim($appUrl, '/') . '/',
                'domain'   => $_SERVER['HTTP_HOST'] ?? 'localhost',
                'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        LoginAttempt::reset($email);

        $idPerfil = $usuario['id_perfil'] ?? 3;

        $_SESSION['usuario'] = [
            'id'    => $usuario['id_usuario'],
            'nome'  => $usuario['nome'],
            'email' => $usuario['email'],
            'role'  => (int) $idPerfil,
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

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['usuario'])) {
            require_once __DIR__ . '/../Helpers/SecurityHelper.php';
            SecurityHelper::logAuditoria(
                'logout_usuario',
                $_SESSION['usuario']['id'],
                'Logout realizado - Email: ' . $_SESSION['usuario']['email'],
                'info'
            );
        }

        // Limpa dados da sessão
        $_SESSION = [];

        // Expira o cookie de sessão
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();

        // Expira o remember_token
        $appUrl = parse_url(\Config::get('APP_URL', '/Aptus'), PHP_URL_PATH) ?: '/Aptus';

        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => rtrim($appUrl, '/') . '/',
            'domain'   => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        header('Location: /Aptus/login');
        exit;
    }

    public function cadastrar()
    {
        if (isset($_SESSION['usuario'])) { header('Location: /Aptus/'); exit; }

        $tituloPagina = 'Criar Conta - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/cadastrar.php';
    }

    /**
     * FIX CRÍTICO: sem enumeração de e-mails. Sempre responde de forma neutra.
     * Se o e-mail já existir, envia um e-mail de "recupere sua senha".
     */
    public function salvar()
    {
        if (isset($_SESSION['usuario'])) { header('Location: /Aptus/'); exit; }

        $nome         = trim($_POST['nome'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $senha        = $_POST['senha'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';

        if (empty($nome) || empty($email) || empty($senha)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos obrigatórios.'];
            header('Location: /Aptus/login/cadastrar'); exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail inválido.'];
            header('Location: /Aptus/login/cadastrar'); exit;
        }

        if (strlen($senha) < 8) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no mínimo 8 caracteres.'];
            header('Location: /Aptus/login/cadastrar'); exit;
        }

        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        $forca = SecurityHelper::validarForcaSenha($senha);
        if (!$forca['valida']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Senha fraca: ' . implode(', ', $forca['erros'])];
            header('Location: /Aptus/login/cadastrar'); exit;
        }

        if ($senha !== $senhaConfirm) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'As senhas não coincidem.'];
            header('Location: /Aptus/login/cadastrar'); exit;
        }

        $usuarioExistente = $this->usuario->findByEmail($email);

        if ($usuarioExistente) {
            // Não revela existência. Dispara e-mail de "esqueci senha" e responde neutro.
            $token = bin2hex(random_bytes(32));
            $this->usuario->salvarTokenReset($email, $token);
            try {
                $mailer = new Mailer();
                $mailer->sendResetPassword($email, $usuarioExistente['nome'], $token);
            } catch (Throwable $e) {
                error_log('Falha ao enviar e-mail de aviso de cadastro duplicado: ' . $e->getMessage());
            }
        } else {
            $token = bin2hex(random_bytes(32));

            $dados = [
                'id_perfil'         => 3,
                'nome'              => $nome,
                'email'             => $email,
                'senha'             => $senha,
                'token_verificacao' => $token,
            ];

            $resultado = $this->usuario->create($dados);

            if ($resultado) {
                try {
                    $mailer = new Mailer();
                    $mailer->sendVerificationEmail($email, $nome, $token);
                } catch (Throwable $e) {
                    error_log('Falha ao enviar e-mail de verificação: ' . $e->getMessage());
                }
            }
        }

        // Resposta SEMPRE idêntica, exista ou não a conta
        $_SESSION['flash'] = [
            'tipo'     => 'sucesso',
            'mensagem' => 'Se os dados estiverem corretos, você receberá um e-mail com instruções em instantes.',
        ];
        header('Location: /Aptus/login');
        exit;
    }

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

    public function reenviarVerificacao()
    {
        $email = $_GET['email'] ?? '';
        if (empty($email)) { header('Location: /Aptus/login'); exit; }

        $usuario = $this->usuario->findByEmail($email);

        // Resposta neutra — sem revelar existência
        if ($usuario && !$usuario['email_verificado']) {
            $token = bin2hex(random_bytes(32));
            $pdo   = Database::getConnection();
            $stmt  = $pdo->prepare("UPDATE usuario SET token_verificacao = ? WHERE id_usuario = ?");
            $stmt->execute([$token, $usuario['id_usuario']]);

            try {
                $mailer = new Mailer();
                $mailer->sendVerificationEmail($email, $usuario['nome'], $token);
            } catch (Throwable $e) {
                error_log('Falha ao reenviar verificação: ' . $e->getMessage());
            }
        }

        $_SESSION['flash'] = [
            'tipo' => 'sucesso',
            'mensagem' => 'Se este e-mail estiver cadastrado e pendente, você receberá um novo link de verificação.',
        ];
        header('Location: /Aptus/login');
        exit;
    }

    public function esqueciSenha()
    {
        if (isset($_SESSION['usuario'])) { header('Location: /Aptus/'); exit; }

        $tituloPagina = 'Esqueci a Senha - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/esqueci_senha.php';
    }

    public function enviarToken()
    {
        if (isset($_SESSION['usuario'])) { header('Location: /Aptus/'); exit; }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail inválido.'];
            header('Location: /Aptus/auth/esqueci-senha'); exit;
        }

        $usuario = $this->usuario->findByEmail($email);

        if ($usuario) {
            $token     = bin2hex(random_bytes(32));
            $resultado = $this->usuario->salvarTokenReset($email, $token);

            if ($resultado) {
                try {
                    $mailer = new Mailer();
                    $mailer->sendResetPassword($email, $usuario['nome'], $token);
                } catch (Throwable $e) {
                    error_log('Falha ao enviar e-mail de reset: ' . $e->getMessage());
                }
            } else {
                error_log('Falha ao salvar token de reset para: ' . $email);
            }
        }

        $_SESSION['flash'] = [
            'tipo'     => 'sucesso',
            'mensagem' => 'Se este e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.',
        ];
        header('Location: /Aptus/login');
        exit;
    }

    public function redefinir()
    {
        if (isset($_SESSION['usuario'])) { header('Location: /Aptus/'); exit; }

        $token = $_GET['token'] ?? '';
        if (empty($token)) { header('Location: /Aptus/login'); exit; }

        $tokenData = $this->usuario->findTokenReset($token);
        if (!$tokenData) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Token inválido ou expirado.'];
            header('Location: /Aptus/login'); exit;
        }

        $tituloPagina = 'Redefinir Senha - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/redefinir_senha.php';
    }

    public function redefinirSenha()
    {
        if (isset($_SESSION['usuario'])) { header('Location: /Aptus/'); exit; }

        $token        = $_POST['token'] ?? '';
        $senha        = $_POST['senha'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';

        if (empty($token) || empty($senha)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos.'];
            header('Location: /Aptus/auth/redefinir?token=' . urlencode($token)); exit;
        }

        if (strlen($senha) < 8) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no mínimo 8 caracteres.'];
            header('Location: /Aptus/auth/redefinir?token=' . urlencode($token)); exit;
        }

        if ($senha !== $senhaConfirm) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'As senhas não coincidem.'];
            header('Location: /Aptus/auth/redefinir?token=' . urlencode($token)); exit;
        }

        $resultado = $this->usuario->redefinirSenha($token, $senha);

        if ($resultado) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Senha redefinida com sucesso! Faça login.'];
            header('Location: /Aptus/login?sucesso=1');
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Token inválido ou expirado.'];
            header('Location: /Aptus/login');
        }
        exit;
    }
}