<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
require_once __DIR__ . "/../../Config/config.php";
// app/Views/auth/index.php

$tituloPagina = $tituloPagina ?? 'Login - Aptus';
$cssPagina    = $cssPagina    ?? 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$status   = $_GET['status']   ?? '';
$mensagem = $_GET['mensagem'] ?? '';
$email    = $_GET['email']    ?? '';

// ======================= PAINEL DEV =======================
$appEnv   = Config::get('APP_ENV', 'development');
$modoDev  = ($appEnv !== 'production');
$senhaDev = 'Aptus@2026';

$gruposDev = [
    'Administrativo' => [
        ['rotulo' => 'Admin',     'email' => 'admin@aptus.com',     'icone' => 'fa-user-shield', 'cor' => '#dc2626'],
        ['rotulo' => 'Moderador', 'email' => 'moderador@aptus.com', 'icone' => 'fa-user-cog',    'cor' => '#d97706'],
        ['rotulo' => 'Usuário',   'email' => 'usuario@aptus.com',   'icone' => 'fa-user',        'cor' => '#059669'],
        ['rotulo' => 'Master',    'email' => 'master@aptus.com',    'icone' => 'fa-crown',       'cor' => '#7c3aed'],
    ],
    'Freelancers' => [
        ['rotulo' => 'Ana',      'email' => 'ana@aptus.com',      'icone' => 'fa-palette',    'cor' => '#db2777'],
        ['rotulo' => 'Roberto',  'email' => 'roberto@aptus.com',  'icone' => 'fa-code',       'cor' => '#0284c7'],
        ['rotulo' => 'Carla',    'email' => 'carla@aptus.com',    'icone' => 'fa-camera',     'cor' => '#e11d48'],
        ['rotulo' => 'Fernando', 'email' => 'fernando@aptus.com', 'icone' => 'fa-language',   'cor' => '#4f46e5'],
        ['rotulo' => 'Mariana',  'email' => 'mariana@aptus.com',  'icone' => 'fa-chart-line', 'cor' => '#0d9488'],
    ],
    'Prestadores' => [
        ['rotulo' => 'Lucas',    'email' => 'lucas@aptus.com',        'icone' => 'fa-bolt',     'cor' => '#d97706'],
        ['rotulo' => 'Patrícia', 'email' => 'patricia@aptus.com',     'icone' => 'fa-wrench',   'cor' => '#0891b2'],
        ['rotulo' => 'João',     'email' => 'joao@aptus.com',         'icone' => 'fa-hard-hat', 'cor' => '#ea580c'],
        ['rotulo' => 'Maria',    'email' => 'maria.santos@aptus.com', 'icone' => 'fa-broom',    'cor' => '#9333ea'],
        ['rotulo' => 'Carlos',   'email' => 'carlos@aptus.com',       'icone' => 'fa-seedling', 'cor' => '#16a34a'],
    ],
];
// ==========================================================
?>

<section class="auth-section animate-in">
    <div class="auth-layout <?= $modoDev ? 'com-dev' : 'sem-dev' ?>">

        <?php if ($modoDev): ?>
        <aside class="dev-panel">
            <div class="dev-header">
                <div class="dev-header-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="dev-header-text">
                    <span class="dev-titulo">Ambiente de Teste</span>
                    <span class="dev-subtitulo">Acesso rápido para validação</span>
                </div>
            </div>

            <div class="dev-senha">
                <i class="fas fa-key"></i>
                <span>Senha universal</span>
                <code><?= htmlspecialchars($senhaDev) ?></code>
            </div>

            <button type="button" class="dev-atalho" data-auto="1">
                <i class="fas fa-bolt"></i>
                <span>Entrar como <strong>Administrador</strong></span>
                <i class="fas fa-arrow-right dev-atalho-arrow"></i>
            </button>

            <?php foreach ($gruposDev as $titulo => $usuarios): ?>
                <div class="dev-grupo">
                    <div class="dev-grupo-titulo"><?= htmlspecialchars($titulo) ?></div>
                    <div class="dev-lista">
                        <?php foreach ($usuarios as $u): ?>
                            <button type="button"
                                    class="dev-user"
                                    style="--user-cor: <?= htmlspecialchars($u['cor']) ?>"
                                    title="Logar como <?= htmlspecialchars($u['email']) ?>"
                                    data-email="<?= htmlspecialchars($u['email']) ?>"
                                    data-senha="<?= htmlspecialchars($senhaDev) ?>">
                                <span class="dev-user-avatar">
                                    <i class="fas <?= htmlspecialchars($u['icone']) ?>"></i>
                                </span>
                                <span class="dev-user-info">
                                    <strong><?= htmlspecialchars($u['rotulo']) ?></strong>
                                    <small><?= htmlspecialchars($u['email']) ?></small>
                                </span>
                                <span class="dev-user-seta">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="dev-footer">
                <i class="fas fa-shield-alt"></i>
                <span>Visível apenas em <strong>APP_ENV != production</strong></span>
            </div>
        </aside>
        <?php endif; ?>

        <div class="auth-card">
            <div class="auth-header">
                <h2>Bem-vindo de volta</h2>
                <p>Faça login para acessar sua conta</p>
            </div>

            <?php
            $flashParaExibir = null;

            if (isset($_SESSION['flash'])) {
                $flashParaExibir = $_SESSION['flash'];
                unset($_SESSION['flash']);
            } elseif ($status === 'erro' && $mensagem !== '') {
                $flashParaExibir = ['tipo' => 'erro', 'mensagem' => $mensagem];
            } elseif ($status === 'sucesso' && $mensagem !== '') {
                $flashParaExibir = ['tipo' => 'sucesso', 'mensagem' => $mensagem];
            }

            if ($flashParaExibir):
            ?>
                <div id="flashData"
                     data-tipo="<?= htmlspecialchars($flashParaExibir['tipo'], ENT_QUOTES, 'UTF-8') ?>"
                     data-mensagem="<?= htmlspecialchars($flashParaExibir['mensagem'], ENT_QUOTES, 'UTF-8') ?>"
                     data-email="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                     style="display:none;"></div>
            <?php endif; ?>

            <form method="POST" action="/Aptus/login" class="auth-form" id="loginForm" onsubmit="return aceitarLgpdEContinuar(event)">
                <div class="form-group">
                    <label for="email">E-mail <span class="obrigatorio">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="seu@email.com" required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="senha">Senha <span class="obrigatorio">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="senha" name="senha" class="form-control"
                               placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="toggle-senha" id="toggleSenha" aria-label="Mostrar senha">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group remember-group">
                    <label class="remember-check">
                        <input type="checkbox" name="lembrar" value="1">
                        <span>Lembrar-me por 30 dias</span>
                    </label>
                    <a href="/Aptus/auth/esqueci-senha" class="link-esqueci">Esqueci a senha</a>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="btnLogin">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
                <?= CsrfMiddleware::field() ?>
            </form>

            <div class="auth-footer">
                <p>Não tem uma conta? <a href="/Aptus/login/cadastrar">Criar Conta Grátis</a></p>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function aceitarLgpdEContinuar(e) {
    var modal = document.getElementById('lgpd-modal');
    if (modal && modal.style.display !== 'none') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Aceite necessário',
                text: 'Por favor, concorde com os Termos, Privacidade e Cookies (LGPD) para continuar.',
                confirmButtonColor: '#006577',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Por favor, concorde com os Termos, Privacidade e Cookies (LGPD) para continuar.');
        }
        modal.style.display = 'flex';
        e.preventDefault();
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // FLASH via SweetAlert2
    // ============================================================
    var flash = document.getElementById('flashData');

    if (flash && typeof Swal !== 'undefined') {
        var tipo     = flash.dataset.tipo || 'info';
        var mensagem = flash.dataset.mensagem || '';

        var config = {
            confirmButtonColor: '#006577',
            confirmButtonText: 'OK',
            allowOutsideClick: true,
            allowEscapeKey: true
        };

        switch (tipo) {
            case 'sucesso':
                config.icon  = 'success';
                config.title = 'Tudo certo!';
                config.text  = mensagem;
                break;
            case 'erro':
                config.icon  = 'error';
                config.title = 'Não foi possível entrar';
                config.text  = mensagem;
                break;
            case 'aviso':
                config.icon  = 'warning';
                config.title = 'Atenção';
                config.text  = mensagem;
                break;
            default:
                config.icon  = 'info';
                config.title = 'Aviso';
                config.text  = mensagem;
        }

        Swal.fire(config);

        if (tipo === 'erro') {
            var senhaInput = document.getElementById('senha');
            if (senhaInput) {
                senhaInput.value = '';
                senhaInput.focus();
            }
        }
    }

    // ============================================================
    // Painel dev
    // ============================================================
    var emailInput = document.getElementById('email');
    var senhaInput = document.getElementById('senha');
    var btnLogin   = document.getElementById('btnLogin');

    var toggle = document.getElementById('toggleSenha');
    if (toggle && senhaInput) {
        toggle.addEventListener('click', function () {
            var isPass = senhaInput.type === 'password';
            senhaInput.type = isPass ? 'text' : 'password';
            toggle.querySelector('i').className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    function limparLGPD() {
        var modal = document.getElementById('lgpd-modal');
        if (modal) modal.style.display = 'none';
        try {
            document.cookie = 'aptus_lgpd_aceito=1;path=/;max-age=31536000;SameSite=Lax';
            localStorage.setItem('aptus_lgpd_aceito', '1');
        } catch (e) {}
    }

    document.querySelectorAll('.dev-user, .dev-atalho').forEach(function (el) {
        el.addEventListener('click', function () {
            var isAtalho = this.dataset.auto === '1';
            var email    = isAtalho ? 'admin@aptus.com' : this.dataset.email;
            var senha    = isAtalho ? '<?= addslashes($senhaDev) ?>' : this.dataset.senha;

            if (emailInput) emailInput.value = email || '';
            if (senhaInput) senhaInput.value = senha || '';

            limparLGPD();
            if (btnLogin) btnLogin.focus();

            if (isAtalho && btnLogin) btnLogin.click();
        });
    });
});
</script>

<style>
/* ==========================================================
   LAYOUT
   ========================================================== */
.auth-layout {
    display: grid;
    gap: 24px;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
    align-items: start;
}
.auth-layout.com-dev  { grid-template-columns: 380px 1fr; }
.auth-layout.sem-dev  { grid-template-columns: 1fr; max-width: 440px; }
.auth-layout.sem-dev .auth-card { margin: 0 auto; width: 100%; max-width: 440px; }

/* ==========================================================
   PAINEL DEV — TEMA CLARO
   ========================================================== */
.dev-panel {
    background: #ffffff;
    border-radius: 18px;
    padding: 22px 20px;
    color: #1a2f3e;
    box-shadow: 0 8px 32px rgba(0, 101, 119, 0.10);
    border: 1px solid #d8edf1;
    position: sticky;
    top: 90px;
    max-height: calc(100vh - 110px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
    position: relative;
}
.dev-panel::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 18px 18px 0 0;
    background: linear-gradient(90deg, #006577 0%, #C9A227 100%);
}
.dev-panel::-webkit-scrollbar { width: 8px; }
.dev-panel::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
.dev-panel::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

.dev-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e8eff3;
    padding-top: 6px;
}
.dev-header-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #006577, #004d5c);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.15rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 101, 119, 0.25);
}
.dev-header-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.dev-titulo    {
    font-weight: 800;
    font-size: 0.95rem;
    color: #006577;
    letter-spacing: .2px;
}
.dev-subtitulo { font-size: 0.72rem; color: #64748b; }

.dev-senha {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #fef7e6;
    border: 1px solid #f5d97a;
    border-radius: 10px;
    margin-bottom: 16px;
    font-size: 0.78rem;
    color: #8a6d1a;
    font-weight: 600;
}
.dev-senha i { color: #C9A227; font-size: 0.9rem; }
.dev-senha span { flex: 1; }
.dev-senha code {
    background: #C9A227;
    color: #ffffff;
    padding: 3px 10px;
    border-radius: 6px;
    font-weight: 800;
    font-family: 'Courier New', monospace;
    font-size: 0.78rem;
    letter-spacing: .5px;
}

.dev-atalho {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 13px 16px;
    margin-bottom: 18px;
    background: linear-gradient(135deg, #006577, #004d5c);
    color: #ffffff;
    border: none;
    border-radius: 11px;
    font-family: inherit;
    font-size: 0.86rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    box-shadow: 0 6px 18px rgba(0, 101, 119, 0.25);
    text-align: left;
}
.dev-atalho > i:first-child { color: #C9A227; font-size: 1rem; }
.dev-atalho span { flex: 1; color: #ffffff; }
.dev-atalho strong { color: #C9A227; font-weight: 800; }
.dev-atalho-arrow { opacity: 0.7; transition: transform .2s; color: #ffffff; font-size: 0.8rem; }
.dev-atalho:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(0, 101, 119, 0.35); }
.dev-atalho:hover .dev-atalho-arrow { transform: translateX(4px); opacity: 1; }
.dev-atalho:active { transform: translateY(0) scale(.99); }

.dev-grupo { margin-bottom: 18px; }
.dev-grupo:last-of-type { margin-bottom: 12px; }
.dev-grupo-titulo {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #006577;
    margin-bottom: 8px;
    padding-left: 2px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dev-grupo-titulo::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(0, 101, 119, 0.15), transparent);
}

.dev-lista { display: flex; flex-direction: column; gap: 6px; }

/* Item do usuário — TEMA CLARO com alto contraste */
.dev-user {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid var(--user-cor, #64748b);
    border-radius: 9px;
    cursor: pointer;
    transition: all .18s ease;
    font-family: inherit;
    text-align: left;
    color: #1a2f3e;
}
.dev-user:hover {
    background: var(--user-cor, #64748b);
    border-color: var(--user-cor, #64748b);
    transform: translateX(3px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.dev-user:hover .dev-user-info strong,
.dev-user:hover .dev-user-info small { color: #ffffff; }
.dev-user:hover .dev-user-avatar {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}
.dev-user:hover .dev-user-seta { color: #ffffff; }

.dev-user-avatar {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: var(--user-cor, #64748b);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 0.82rem;
    flex-shrink: 0;
    transition: all .18s ease;
}

.dev-user-info { flex: 1; display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.dev-user-info strong {
    font-size: 0.84rem;
    font-weight: 700;
    color: #1a2f3e;
    letter-spacing: .2px;
    line-height: 1.2;
    transition: color .18s ease;
}
.dev-user-info small {
    font-size: 0.68rem;
    color: #64748b;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: color .18s ease;
}
.dev-user-seta {
    font-size: 0.7rem;
    color: #94a3b8;
    transition: all .18s ease;
    flex-shrink: 0;
}

.dev-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    margin-top: 14px;
    background: #f8fdfe;
    border: 1px solid #e8eff3;
    border-radius: 8px;
    font-size: 0.7rem;
    color: #64748b;
    line-height: 1.4;
}
.dev-footer i { color: #059669; font-size: 0.8rem; flex-shrink: 0; }
.dev-footer strong { color: #006577; }

/* ==========================================================
   CARD DE LOGIN
   ========================================================== */
.input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.input-wrap > i {
    position: absolute;
    left: 14px;
    color: #006577;
    font-size: 0.95rem;
    pointer-events: none;
    opacity: 0.7;
}
.input-wrap .form-control {
    padding-left: 40px !important;
    padding-right: 42px !important;
}
.toggle-senha {
    position: absolute;
    right: 10px;
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 6px;
    transition: all .2s ease;
    font-size: 0.9rem;
}
.toggle-senha:hover { color: #006577; background: rgba(0, 101, 119, 0.06); }
.remember-group {
    display: flex !important;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: -4px;
}
.link-esqueci {
    font-size: 0.82rem;
    color: #006577;
    text-decoration: none;
    font-weight: 500;
    transition: color .2s ease;
}
.link-esqueci:hover { color: #C9A227; text-decoration: underline; }
.btn-full {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-full i { font-size: 0.9rem; }

/* ==========================================================
   RESPONSIVO
   ========================================================== */
@media (max-width: 900px) {
    .auth-layout.com-dev { grid-template-columns: 1fr; max-width: 520px; }
    .dev-panel { position: static; max-height: none; order: 2; }
    .auth-card { order: 1; }
}

@media (max-width: 520px) {
    .dev-panel { padding: 16px 14px; border-radius: 14px; }
    .dev-panel::before { border-radius: 14px 14px 0 0; }
    .dev-header-icon { width: 36px; height: 36px; font-size: 1rem; }
    .dev-titulo { font-size: 0.88rem; }
    .dev-user-info strong { font-size: 0.8rem; }
    .dev-user-info small { font-size: 0.64rem; }
    .dev-atalho { font-size: 0.82rem; padding: 11px 14px; }
    .remember-group { flex-direction: column; align-items: flex-start; }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>