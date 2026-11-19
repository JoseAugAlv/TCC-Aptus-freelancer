<?php
// app/Controllers/AuthController.php
require_once __DIR__ . '/../Config/SessionConfig.php';
SessionConfig::configure();

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Core/Mailer.php';
require_once __DIR__ . '/../Helpers/GoogleAuth.php';

class AuthController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    public function index()
    {
        if (isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/')); exit; }

        $tituloPagina = 'Login - Aptus';
        $cssPagina = 'login.css';
        $googleAtivo = GoogleAuth::configurado();
        require '../app/Views/auth/index.php';
    }

    public function login()
    {
        if (isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/')); exit; }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos.'];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }

        require_once __DIR__ . '/../Helpers/LoginAttempt.php';
        if (!LoginAttempt::check($email)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Muitas tentativas. Tente novamente mais tarde.'];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }

        $usuario = $this->usuario->findByEmail($email);
        $lembrar = isset($_POST['lembrar']) && $_POST['lembrar'] == '1';

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            LoginAttempt::increment($email);
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail ou senha incorretos.'];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }

        if (!$usuario['email_verificado']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Por favor, verifique seu e-mail antes de fazer login.'];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }

        if (!$usuario['ativo']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Usuário inativo. Entre em contato com o administrador.'];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }

        if (!empty($usuario['banido'])) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Sua conta foi banida. Motivo: ' . ($usuario['motivo_banimento'] ?? 'Não informado')];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }

        LoginAttempt::reset($email);
        $this->iniciarSessao($usuario, $lembrar, 'Login realizado - Email: ' . $email);

        header(('Location: ' . aptus_base() . '/'));
        exit;
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['usuario'])) {
            // Revoga o token de "lembrar de mim" no banco
            try {
                $pdo  = Database::getConnection();
                $stmt = $pdo->prepare(
                    "UPDATE usuario
                    SET remember_token = NULL, remember_token_expira = NULL
                    WHERE id_usuario = ?"
                );
                $stmt->execute([$_SESSION['usuario']['id']]);
            } catch (Throwable $e) {
                error_log('Erro ao revogar token no logout: ' . $e->getMessage());
            }

            require_once __DIR__ . '/../Helpers/SecurityHelper.php';
            SecurityHelper::logAuditoria(
                'logout_usuario',
                $_SESSION['usuario']['id'],
                'Logout realizado - Email: ' . $_SESSION['usuario']['email'],
                'info'
            );
        }

        $_SESSION = [];

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

        $appUrl = parse_url(\Config::get('APP_URL', '/Aptus'), PHP_URL_PATH) ?: '/Aptus';
        $domain = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');

        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => rtrim($appUrl, '/') . '/',
            'domain'   => $domain,
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        header(('Location: ' . aptus_base() . '/login'));
        exit;
    }
    /**
     * Abre a sessão do usuário (login por senha ou pelo Google):
     * novo ID de sessão, token "lembrar de mim" opcional e auditoria.
     */
    private function iniciarSessao(array $usuario, bool $lembrar, string $detalheAuditoria): void
    {
        session_regenerate_id(true);

        // ---------- TOKEN "LEMBRAR DE MIM" (hash + expiração) ----------
        if ($lembrar) {
            $token      = bin2hex(random_bytes(32));
            $tokenHash  = hash('sha256', $token);
            $expira     = date('Y-m-d H:i:s', time() + 30 * 24 * 3600);

            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare(
                "UPDATE usuario
                SET remember_token = ?, remember_token_expira = ?
                WHERE id_usuario = ?"
            );
            $stmt->execute([$tokenHash, $expira, $usuario['id_usuario']]);

            $appUrl = parse_url(\Config::get('APP_URL', '/Aptus'), PHP_URL_PATH) ?: '/Aptus';
            $domain = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');

            setcookie('remember_token', $token, [
                'expires'  => time() + 30 * 24 * 3600,
                'path'     => rtrim($appUrl, '/') . '/',
                'domain'   => $domain,
                'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

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
            $detalheAuditoria,
            'info'
        );

    }

    // ==================================================================
    // Login / cadastro com Google
    // ==================================================================

    /** GET /auth/google — manda a pessoa para a tela de escolha de conta do Google. */
    public function google()
    {
        if (!GoogleAuth::configurado()) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Login com Google indisponível no momento.'];
            header('Location: ' . aptus_base() . '/login'); exit;
        }

        // "Conectar conta Google" a partir do perfil (usuário já logado)
        if (isset($_GET['conectar']) && isset($_SESSION['usuario'])) {
            $_SESSION['google_conectar'] = (int) $_SESSION['usuario']['id'];
        } else {
            unset($_SESSION['google_conectar']);
            if (isset($_SESSION['usuario'])) { header('Location: ' . aptus_base() . '/'); exit; }
        }

        header('Location: ' . GoogleAuth::urlAutorizacao());
        exit;
    }

    /** GET /auth/google/callback — volta do Google. */
    public function googleCallback()
    {
        $voltarLogin = function (string $msg, string $tipo = 'erro') {
            $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $msg];
            header('Location: ' . aptus_base() . '/login'); exit;
        };

        if (!GoogleAuth::configurado()) $voltarLogin('Login com Google indisponível no momento.');
        if (!empty($_GET['error']))      $voltarLogin('Login com Google cancelado.', 'aviso');
        if (!GoogleAuth::stateValido($_GET['state'] ?? null) || empty($_GET['code'])) {
            $voltarLogin('Não foi possível validar o login com Google. Tente novamente.');
        }

        $info = GoogleAuth::obterUsuario((string) $_GET['code']);
        if (!$info) $voltarLogin('Não foi possível obter seus dados do Google. Tente novamente.');
        if (empty($info['email_verified'])) {
            $voltarLogin('O e-mail da sua conta Google não está verificado pelo Google.');
        }

        $googleId = (string) $info['sub'];
        $email    = strtolower(trim((string) $info['email']));

        // --- Conectar a conta Google ao perfil de quem já está logado ---
        $conectarId = (int) ($_SESSION['google_conectar'] ?? 0);
        unset($_SESSION['google_conectar']);
        if ($conectarId > 0 && isset($_SESSION['usuario']) && (int) $_SESSION['usuario']['id'] === $conectarId) {
            $voltarPerfil = function (string $tipo, string $msg) {
                $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $msg];
                header('Location: ' . aptus_base() . '/perfil/editar?aba=seguranca'); exit;
            };
            $eu = $this->usuario->findById($conectarId);
            if (!$eu) $voltarPerfil('erro', 'Conta não encontrada.');

            // A mesma conta Google não pode entrar em dois cadastros
            $outro = $this->usuario->findByGoogleId($googleId);
            if ($outro && (int) $outro['id_usuario'] !== $conectarId) {
                $voltarPerfil('erro', 'Esta conta Google já está conectada a outro cadastro do Aptus.');
            }
            // Nem pode ser o e-mail de OUTRA conta do Aptus (o login pelo Google ficaria ambíguo)
            $donoEmail = $this->usuario->findByEmail($email);
            if ($donoEmail && (int) $donoEmail['id_usuario'] !== $conectarId) {
                $voltarPerfil('erro', 'O e-mail desta conta Google (' . $email . ') já é usado por outro cadastro do Aptus. Escolha outra conta Google.');
            }

            // A pessoa já está logada, então pode conectar uma conta Google com
            // qualquer e-mail. Só confirmamos o e-mail do cadastro se for o mesmo.
            $mesmoEmail = strtolower((string) $eu['email']) === $email;
            $this->usuario->vincularGoogle($conectarId, $googleId, $email, $mesmoEmail);

            require_once __DIR__ . '/../Helpers/SecurityHelper.php';
            SecurityHelper::logAuditoria('google_conectado', $conectarId, 'Conta Google conectada: ' . $email, 'info');

            $voltarPerfil('sucesso', 'Conta Google ' . $email . ' conectada! Nos próximos acessos, é só clicar em "Entrar com Google".');
        }

        // --- Login de quem já tem conta ---
        $usuario = $this->usuario->findByGoogleId($googleId) ?: $this->usuario->findByEmail($email);

        if ($usuario) {
            if (!empty($usuario['google_id']) && $usuario['google_id'] !== $googleId) {
                $voltarLogin('Este e-mail já está ligado a outra conta Google.');
            }
            if (empty($usuario['google_id'])) {
                // Achou pelo e-mail: o Google confirmou que o e-mail é da pessoa,
                // então liga a conta e dispensa a verificação por link
                $this->usuario->vincularGoogle((int) $usuario['id_usuario'], $googleId, $email, true);
            } elseif (strtolower((string) ($usuario['google_email'] ?? '')) !== $email) {
                $this->usuario->atualizarGoogleEmail((int) $usuario['id_usuario'], $email);
            }
            if (!$usuario['ativo'])          $voltarLogin('Usuário inativo. Entre em contato com o administrador.');
            if (!empty($usuario['banido']))  $voltarLogin('Sua conta foi banida. Motivo: ' . ($usuario['motivo_banimento'] ?? 'Não informado'));

            $this->iniciarSessao($usuario, false, 'Login com Google - Email: ' . $email);
            header('Location: ' . aptus_base() . '/'); exit;
        }

        // --- Conta nova: guarda os dados do Google e pede o restante do cadastro ---
        $_SESSION['google_cadastro'] = [
            'sub'    => $googleId,
            'email'  => $email,
            'nome'   => trim((string) ($info['name'] ?? '')),
            'expira' => time() + 1800,
        ];
        header('Location: ' . aptus_base() . '/auth/google/completar');
        exit;
    }

    /** Dados do Google guardados na sessão (ou null se expiraram). */
    private function googleCadastroPendente(): ?array
    {
        $g = $_SESSION['google_cadastro'] ?? null;
        if (!$g || ($g['expira'] ?? 0) < time()) {
            unset($_SESSION['google_cadastro']);
            return null;
        }
        return $g;
    }

    /** GET /auth/google/completar — formulário com o que falta (CPF, endereço, senha...). */
    public function googleCompletar()
    {
        if (isset($_SESSION['usuario'])) { header('Location: ' . aptus_base() . '/'); exit; }

        $google = $this->googleCadastroPendente();
        if (!$google) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Sua sessão do Google expirou. Clique em "Continuar com Google" de novo.'];
            header('Location: ' . aptus_base() . '/login'); exit;
        }

        $usernameSugerido = $this->usuario->sugerirUsername($google['nome'] !== '' ? $google['nome'] : explode('@', $google['email'])[0]);
        $tituloPagina = 'Completar cadastro - Aptus';
        $cssPagina    = 'login.css';
        require '../app/Views/auth/google_completar.php';
    }

    /** POST /auth/google/finalizar — cria a conta já verificada e entra. */
    public function googleFinalizar()
    {
        if (isset($_SESSION['usuario'])) { header('Location: ' . aptus_base() . '/'); exit; }

        $google = $this->googleCadastroPendente();
        if (!$google) {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Sua sessão do Google expirou. Clique em "Continuar com Google" de novo.'];
            header('Location: ' . aptus_base() . '/login'); exit;
        }

        require_once __DIR__ . '/../Helpers/DocumentoHelper.php';
        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        try { $this->usuario->limparCadastrosExpirados(); } catch (Throwable $e) { error_log($e->getMessage()); }

        $nome         = trim($_POST['nome'] ?? '');
        $senha        = $_POST['senha'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';
        $username     = DocumentoHelper::normalizarUsername((string) ($_POST['username'] ?? ''));
        $cpf          = DocumentoHelper::somenteDigitos((string) ($_POST['cpf'] ?? ''));
        $nascimento   = trim((string) ($_POST['data_nascimento'] ?? ''));
        $endereco     = [
            'cep'         => DocumentoHelper::somenteDigitos((string) ($_POST['cep'] ?? '')),
            'logradouro'  => mb_substr(trim($_POST['logradouro'] ?? ''), 0, 150),
            'numero'      => mb_substr(trim($_POST['numero'] ?? ''), 0, 10),
            'complemento' => mb_substr(trim($_POST['complemento'] ?? ''), 0, 60),
            'bairro'      => mb_substr(trim($_POST['bairro'] ?? ''), 0, 100),
            'cidade'      => mb_substr(trim($_POST['cidade'] ?? ''), 0, 100),
            'estado'      => strtoupper(mb_substr(trim($_POST['estado'] ?? ''), 0, 2)),
        ];

        $_SESSION['cadastro_old'] = array_merge($endereco, [
            'nome' => $nome, 'username' => $username, 'cpf' => $cpf, 'data_nascimento' => $nascimento,
        ]);
        $falhar = function (string $msg) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => $msg];
            header('Location: ' . aptus_base() . '/auth/google/completar'); exit;
        };

        if ($nome === '' || $username === '' || $cpf === '' || $nascimento === '' || $senha === '') {
            $falhar('Preencha todos os campos obrigatórios.');
        }
        if (!DocumentoHelper::usernameValido($username)) {
            $falhar('Nome de usuário inválido: use 3 a 30 letras minúsculas, números, ponto ou sublinhado.');
        }
        if ($this->usuario->usernameEmUso($username)) $falhar('Este nome de usuário já está em uso. Escolha outro.');
        if (!DocumentoHelper::cpfValido($cpf))         $falhar('CPF inválido. Confira os números digitados.');
        if ($this->usuario->cpfEmUso($cpf))            $falhar('Este CPF já está cadastrado em outra conta. Se é você, entre com seu e-mail e senha.');
        if (!DocumentoHelper::dataNascimentoValida($nascimento)) $falhar('Data de nascimento inválida.');
        if (DocumentoHelper::idade($nascimento) < 18)  $falhar('É preciso ter 18 anos ou mais para se cadastrar na Aptus.');
        if (strlen($endereco['cep']) !== 8 || $endereco['logradouro'] === '' || $endereco['numero'] === ''
            || $endereco['bairro'] === '' || $endereco['cidade'] === '' || strlen($endereco['estado']) !== 2) {
            $falhar('Preencha o endereço completo: CEP, rua, número, bairro, cidade e estado.');
        }
        $endereco['cep'] = substr($endereco['cep'], 0, 5) . '-' . substr($endereco['cep'], 5);

        if (strlen($senha) < 8) $falhar('A senha deve ter no mínimo 8 caracteres.');
        $forca = SecurityHelper::validarForcaSenha($senha);
        if (!$forca['valida'])  $falhar('Senha fraca: ' . implode(', ', $forca['erros']));
        if ($senha !== $senhaConfirm) $falhar('As senhas não coincidem.');

        // E-mail pode ter sido cadastrado por outra via enquanto a pessoa preenchia
        if ($this->usuario->findByEmail($google['email']) || $this->usuario->findByGoogleId($google['sub'])) {
            unset($_SESSION['google_cadastro'], $_SESSION['cadastro_old']);
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Já existe uma conta com este e-mail. Clique em "Continuar com Google" para entrar.'];
            header('Location: ' . aptus_base() . '/login'); exit;
        }

        try {
            $ok = $this->usuario->create([
                'id_perfil'        => 3,
                'nome'             => $nome,
                'username'         => $username,
                'email'            => $google['email'],
                'senha'            => $senha,
                'cpf_cnpj'         => $cpf,
                'data_nascimento'  => $nascimento,
                'google_id'        => $google['sub'],
                'google_email'     => $google['email'],
                'email_verificado' => true,
            ] + $endereco);
        } catch (PDOException $e) {
            error_log('Cadastro Google falhou: ' . $e->getMessage());
            $ok = false;
        }

        if (!$ok) $falhar('Não foi possível criar sua conta agora. Tente novamente.');

        unset($_SESSION['google_cadastro'], $_SESSION['cadastro_old']);
        $novo = $this->usuario->findByGoogleId($google['sub']);
        $this->iniciarSessao($novo, false, 'Cadastro com Google - Email: ' . $google['email']);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Conta criada com sucesso! Bem-vindo(a) ao Aptus.'];
        header('Location: ' . aptus_base() . '/');
        exit;
    }

    public function cadastrar()
    {
        if (isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/')); exit; }

        $tituloPagina = 'Criar Conta - Aptus';
        $cssPagina = 'login.css';
        $googleAtivo = GoogleAuth::configurado();
        require '../app/Views/auth/cadastrar.php';
    }

    /**
     * FIX CRÍTICO: sem enumeração de e-mails. Sempre responde de forma neutra.
     * Se o e-mail já existir, envia um e-mail de "recupere sua senha".
     */
    public function salvar()
    {
        if (isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/')); exit; }

        require_once __DIR__ . '/../Helpers/DocumentoHelper.php';

        // Cadastros antigos que nunca confirmaram o e-mail liberam CPF/e-mail/@usuário
        try { $this->usuario->limparCadastrosExpirados(); } catch (Throwable $e) { error_log($e->getMessage()); }

        $nome         = trim($_POST['nome'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $senha        = $_POST['senha'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';
        $username     = DocumentoHelper::normalizarUsername((string) ($_POST['username'] ?? ''));
        $cpf          = DocumentoHelper::somenteDigitos((string) ($_POST['cpf'] ?? ''));
        $nascimento   = trim((string) ($_POST['data_nascimento'] ?? ''));
        $endereco     = [
            'cep'         => DocumentoHelper::somenteDigitos((string) ($_POST['cep'] ?? '')),
            'logradouro'  => mb_substr(trim($_POST['logradouro'] ?? ''), 0, 150),
            'numero'      => mb_substr(trim($_POST['numero'] ?? ''), 0, 10),
            'complemento' => mb_substr(trim($_POST['complemento'] ?? ''), 0, 60),
            'bairro'      => mb_substr(trim($_POST['bairro'] ?? ''), 0, 100),
            'cidade'      => mb_substr(trim($_POST['cidade'] ?? ''), 0, 100),
            'estado'      => strtoupper(mb_substr(trim($_POST['estado'] ?? ''), 0, 2)),
        ];

        // Em caso de erro, devolve o que foi digitado (menos as senhas)
        $_SESSION['cadastro_old'] = array_merge($endereco, [
            'nome' => $nome, 'email' => $email, 'username' => $username,
            'cpf' => $cpf, 'data_nascimento' => $nascimento,
        ]);
        $falhar = function (string $msg) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => $msg];
            header(('Location: ' . aptus_base() . '/login/cadastrar')); exit;
        };

        if (empty($nome) || empty($email) || empty($senha) || $username === '' || $cpf === '' || $nascimento === '') {
            $falhar('Preencha todos os campos obrigatórios.');
        }

        if (!DocumentoHelper::usernameValido($username)) {
            $falhar('Nome de usuário inválido: use 3 a 30 letras minúsculas, números, ponto ou sublinhado.');
        }
        if ($this->usuario->usernameEmUso($username)) {
            $falhar('Este nome de usuário já está em uso. Escolha outro.');
        }

        if (!DocumentoHelper::cpfValido($cpf)) {
            $falhar('CPF inválido. Confira os números digitados.');
        }
        if (!DocumentoHelper::dataNascimentoValida($nascimento)) {
            $falhar('Data de nascimento inválida.');
        }
        if (DocumentoHelper::idade($nascimento) < 18) {
            $falhar('É preciso ter 18 anos ou mais para se cadastrar na Aptus.');
        }

        if (strlen($endereco['cep']) !== 8 || $endereco['logradouro'] === '' || $endereco['numero'] === ''
            || $endereco['bairro'] === '' || $endereco['cidade'] === '' || strlen($endereco['estado']) !== 2) {
            $falhar('Preencha o endereço completo: CEP, rua, número, bairro, cidade e estado.');
        }
        $endereco['cep'] = substr($endereco['cep'], 0, 5) . '-' . substr($endereco['cep'], 5);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail inválido.'];
            header(('Location: ' . aptus_base() . '/login/cadastrar')); exit;
        }

        if (strlen($senha) < 8) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no mínimo 8 caracteres.'];
            header(('Location: ' . aptus_base() . '/login/cadastrar')); exit;
        }

        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        $forca = SecurityHelper::validarForcaSenha($senha);
        if (!$forca['valida']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Senha fraca: ' . implode(', ', $forca['erros'])];
            header(('Location: ' . aptus_base() . '/login/cadastrar')); exit;
        }

        if ($senha !== $senhaConfirm) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'As senhas não coincidem.'];
            header(('Location: ' . aptus_base() . '/login/cadastrar')); exit;
        }

        $usuarioExistente = $this->usuario->findByEmail($email);
        $cpfDuplicado     = !$usuarioExistente && $this->usuario->cpfEmUso($cpf);

        if ($cpfDuplicado) {
            // CPF já usado em outra conta: não cria e responde de forma neutra
            error_log('Cadastro bloqueado: CPF já cadastrado (e-mail informado: ' . $email . ')');
        } elseif ($usuarioExistente) {
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
            // [FIX] Sempre gera token + expiração de 24h de uma vez só.
            //       O bloco duplicado anterior (if/else interno) era inútil.
            $token     = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $dados = [
                'id_perfil'                 => 3,
                'nome'                      => $nome,
                'username'                  => $username,
                'email'                     => $email,
                'senha'                     => $senha,
                'cpf_cnpj'                  => $cpf,
                'data_nascimento'           => $nascimento,
                'token_verificacao'         => $token,
                'token_verificacao_expira'  => $expiracao,
            ];

            $resultado = $this->usuario->create($dados + $endereco);

            if ($resultado) {
                try {
                    $mailer = new Mailer();
                    $mailer->sendVerificationEmail($email, $nome, $token);
                } catch (Throwable $e) {
                    error_log('Falha ao enviar e-mail de verificação: ' . $e->getMessage());
                }
            } else {
                error_log('Falha ao criar usuário: ' . $email);
            }
        }

        unset($_SESSION['cadastro_old']);

        // Resposta SEMPRE idêntica, exista ou não a conta
        $_SESSION['flash'] = [
            'tipo'     => 'sucesso',
            'mensagem' => 'Se os dados estiverem corretos, você receberá um e-mail com instruções em instantes.',
        ];
        header(('Location: ' . aptus_base() . '/login'));
        exit;
    }

    public function verificar()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Token inválido.'];
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $tokenData = $this->usuario->findTokenVerificacao($token);

        if ($tokenData
            && !$tokenData['email_verificado']
            && !empty($tokenData['token_verificacao_expira'])
            && strtotime($tokenData['token_verificacao_expira']) < time()) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Link expirado. Solicite um novo.'];
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $resultado = $this->usuario->verificarEmail($token);

        if ($resultado) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'E-mail verificado com sucesso!'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Token inválido ou expirado.'];
        }

        header(('Location: ' . aptus_base() . '/login'));
        exit;
    }

    public function reenviarVerificacao()
    {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) { header(('Location: ' . aptus_base() . '/login')); exit; }

        require_once __DIR__ . '/../Helpers/LoginAttempt.php';

        // Rate limit por IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!LoginAttempt::check('reenvio_' . $ip, $ip)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Muitas tentativas. Aguarde alguns minutos.'];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }
        LoginAttempt::increment('reenvio_' . $ip, $ip);

        $usuario = $this->usuario->findByEmail($email);

        if ($usuario && !$usuario['email_verificado']) {
            $token = bin2hex(random_bytes(32));
            $this->usuario->reenviarTokenVerificacao($usuario['id_usuario'], $token);

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
        header(('Location: ' . aptus_base() . '/login'));
        exit;
    }

    public function enviarToken()
    {
        if (isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/')); exit; }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail inválido.'];
            header(('Location: ' . aptus_base() . '/auth/esqueci-senha')); exit;
        }

        require_once __DIR__ . '/../Helpers/LoginAttempt.php';

        // Rate limit por IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!LoginAttempt::check('reset_' . $ip, $ip)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Muitas tentativas. Aguarde alguns minutos.'];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }
        LoginAttempt::increment('reset_' . $ip, $ip);

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
            }
        }

        $_SESSION['flash'] = [
            'tipo'     => 'sucesso',
            'mensagem' => 'Se este e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.',
        ];
        header(('Location: ' . aptus_base() . '/login'));
        exit;
    }

    public function esqueciSenha()
    {
        if (isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/')); exit; }

        $tituloPagina = 'Esqueci a Senha - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/esqueci_senha.php';
    }

    public function redefinir()
    {
        if (isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/')); exit; }

        $token = $_GET['token'] ?? '';
        if (empty($token)) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $tokenData = $this->usuario->findTokenReset($token);
        if (!$tokenData) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Token inválido ou expirado.'];
            header(('Location: ' . aptus_base() . '/login')); exit;
        }

        $tituloPagina = 'Redefinir Senha - Aptus';
        $cssPagina = 'login.css';
        require '../app/Views/auth/redefinir_senha.php';
    }

    public function redefinirSenha()
    {
        if (isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/')); exit; }

        $token        = $_POST['token'] ?? '';
        $senha        = $_POST['senha'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';

        if (empty($token) || empty($senha)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos.'];
            header(('Location: ' . aptus_base() . '/auth/redefinir?token=') . urlencode($token)); exit;
        }

        if (strlen($senha) < 8) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no mínimo 8 caracteres.'];
            header(('Location: ' . aptus_base() . '/auth/redefinir?token=') . urlencode($token)); exit;
        }
        if (strlen($senha) < 8) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no mínimo 8 caracteres.'];
            header(('Location: ' . aptus_base() . '/auth/redefinir?token=') . urlencode($token)); exit;
        }

        // NOVO: validação de força
        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        $forca = SecurityHelper::validarForcaSenha($senha);
        if (!$forca['valida']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Senha fraca: ' . implode(', ', $forca['erros'])];
            header(('Location: ' . aptus_base() . '/auth/redefinir?token=') . urlencode($token)); exit;
        }

        if ($senha !== $senhaConfirm) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'As senhas não coincidem.'];
            header(('Location: ' . aptus_base() . '/auth/redefinir?token=') . urlencode($token)); exit;
        }

        $resultado = $this->usuario->redefinirSenha($token, $senha);

        if ($resultado) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Senha redefinida com sucesso! Faça login.'];
            header(('Location: ' . aptus_base() . '/login?sucesso=1'));
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Token inválido ou expirado.'];
            header(('Location: ' . aptus_base() . '/login'));
        }
        exit;
    }

    /**
     * GET /usuarios/username-disponivel?u=nome — usado no cadastro e no perfil.
     */
    public function usernameDisponivel()
    {
        require_once __DIR__ . '/../Helpers/DocumentoHelper.php';
        $u      = DocumentoHelper::normalizarUsername((string) ($_GET['u'] ?? ''));
        $valido = DocumentoHelper::usernameValido($u);
        $meuId  = (int) ($_SESSION['usuario']['id'] ?? 0);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode([
            'valido'     => $valido,
            'disponivel' => $valido && !$this->usuario->usernameEmUso($u, $meuId),
        ]);
        exit;
    }
}
