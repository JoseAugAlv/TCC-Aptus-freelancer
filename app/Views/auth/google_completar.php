<?php
// app/Views/auth/google_completar.php
// Segunda etapa do cadastro com Google: nome e e-mail vêm do Google (e-mail já
// verificado); a pessoa completa o que o Aptus exige (CPF, nascimento, endereço, senha).
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../Helpers/GoogleAuth.php';

$tituloPagina = $tituloPagina ?? 'Completar cadastro - Aptus';
$cssPagina    = 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$__old = $_SESSION['cadastro_old'] ?? [];
unset($_SESSION['cadastro_old']);
$__o = fn($k, $padrao = '') => htmlspecialchars((string) ($__old[$k] ?? $padrao), ENT_QUOTES, 'UTF-8');
?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Quase lá!</h2>
                <p>Complete seus dados para criar sua conta</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>" role="alert">
                    <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <div class="google-conta">
                <?= GoogleAuth::iconeSvg(24) ?>
                <div>
                    <small>Conta Google confirmada</small>
                    <strong><?= htmlspecialchars($google['email']) ?></strong>
                </div>
            </div>

            <form method="POST" action="<?= aptus_base() ?>/auth/google/finalizar" class="auth-form" onsubmit="return validarSenhaGoogle()">
                <div class="form-group">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" class="form-control" required autocomplete="name"
                           value="<?= $__o('nome', $google['nome']) ?>">
                </div>

                <div class="form-group">
                    <label for="username">Nome de usuário</label>
                    <div class="input-prefixo">
                        <span aria-hidden="true">@</span>
                        <input type="text" id="username" name="username" class="form-control" required
                               minlength="3" maxlength="30" pattern="[a-z0-9._]{3,30}" autocomplete="username"
                               value="<?= $__o('username', $usernameSugerido) ?>" data-username-check
                               aria-describedby="usernameAjuda usernameStatus">
                    </div>
                    <small id="usernameAjuda" class="campo-ajuda">Letras minúsculas, números, ponto ou sublinhado. Você pode mudar depois.</small>
                    <small id="usernameStatus" class="campo-status" role="status" aria-live="polite"></small>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" class="form-control" inputmode="numeric" required
                               placeholder="000.000.000-00" maxlength="14" value="<?= $__o('cpf') ?>" data-cpf-check>
                        <small class="campo-status" data-cpf-status role="status" aria-live="polite"></small>
                    </div>
                    <div class="form-group">
                        <label for="data_nascimento">Data de nascimento</label>
                        <input type="text" id="data_nascimento" name="data_nascimento" class="form-control" required
                               inputmode="numeric" placeholder="dd/mm/aaaa" maxlength="10" value="<?= $__o('data_nascimento') ?>" data-idade-check>
                        <small class="campo-status" data-idade-status role="status" aria-live="polite"></small>
                    </div>
                </div>
                <p class="campo-ajuda">É preciso ter 18 anos ou mais para usar a Aptus. CPF e data de nascimento nunca aparecem no seu perfil.</p>

                <?php $endereco = $__old; $enderecoObrigatorio = true; require __DIR__ . '/../partials/endereco.php'; ?>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="senha">Crie uma senha</label>
                        <input type="password" id="senha" name="senha" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="senha_confirm">Confirmar senha</label>
                        <input type="password" id="senha_confirm" name="senha_confirm" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                </div>
                <small class="campo-ajuda">Com ela você também consegue entrar com e-mail e senha. Mínimo de 8 caracteres, com maiúscula, minúscula, número e caractere especial.</small>

                <div class="form-group termo-check" style="margin-top:1rem;">
                    <label class="checkbox-termos">
                        <input type="checkbox" name="aceite_termos" value="1" required>
                        <span>
                            Li e concordo com os
                            <a href="<?= aptus_base() ?>/termos" target="_blank" rel="noopener">Termos de Uso</a>,
                            a <a href="<?= aptus_base() ?>/termos#privacidade" target="_blank" rel="noopener">Política de Privacidade</a>
                            e a <a href="<?= aptus_base() ?>/cookies" target="_blank" rel="noopener">Política de Cookies</a>.
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-check"></i> Criar minha conta
                </button>
                <?= CsrfMiddleware::field() ?>
            </form>

            <div class="auth-footer">
                <p>Não é você? <a href="<?= aptus_base() ?>/login">Voltar para o login</a></p>
            </div>
        </div>
    </div>
</section>

<style>
.checkbox-termos { display: flex; align-items: flex-start; gap: 10px; cursor: pointer; }
.checkbox-termos input { margin-top: 3px; }
.checkbox-termos span { font-size: .85rem; color: var(--tema-texto-sec, #475569); line-height: 1.5; }
.checkbox-termos a { color: var(--tema-primaria-texto, var(--cor-primaria)); text-decoration: underline; }
</style>

<script>
function validarSenhaGoogle() {
    var s = document.getElementById('senha').value;
    var c = document.getElementById('senha_confirm').value;
    if (s !== c) { alert('As senhas não coincidem.'); return false; }
    return true;
}
</script>

<?php $jsPagina = ['cep.js', 'cadastro.js']; require_once __DIR__ . '/../layouts/footer.php'; ?>
