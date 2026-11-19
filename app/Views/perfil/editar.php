<?php
// app/Views/perfil/editar.php
// Editar perfil dividido em abas: Informações pessoais, Segurança e Outras configurações.
// A aba aberta vem de ?aba= (padrão: pessoais); a troca de aba é feita por JS sem recarregar.
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../Helpers/UploadHelper.php';
require_once __DIR__ . '/../../Helpers/DocumentoHelper.php';
require_once __DIR__ . '/../../Helpers/GoogleAuth.php';

$tituloPagina = $tituloPagina ?? 'Editar Perfil - Aptus';
$cssPagina    = $cssPagina    ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuario = $usuarioData ?? $_SESSION['usuario'];

$abas = [
    'pessoais'  => ['icone' => 'fa-user',          'rotulo' => 'Informações pessoais'],
    'seguranca' => ['icone' => 'fa-shield-alt',    'rotulo' => 'Segurança'],
    'outras'    => ['icone' => 'fa-sliders-h',     'rotulo' => 'Outras configurações'],
];
$abaAtiva = $_GET['aba'] ?? 'pessoais';
if (!isset($abas[$abaAtiva])) $abaAtiva = 'pessoais';

$__cpfOk  = DocumentoHelper::cpfValido((string) ($usuario['cpf_cnpj'] ?? ''));
$__nasc   = (string) ($usuario['data_nascimento'] ?? '');
$__nascOk = $__nasc !== '' && $__nasc !== '0000-00-00';

$emailVerificado = !empty($usuario['email_verificado']);
$emailPendente   = (string) ($usuario['email_pendente'] ?? '');
$codigoAtivo     = !empty($usuario['codigo_email_hash'])
                && !empty($usuario['codigo_email_expira'])
                && strtotime($usuario['codigo_email_expira']) > time();
$googleLigado    = !empty($usuario['google_id']);
?>

<div class="perfil-container perfil-editar">
    <div class="perfil-header">
        <h1><i class="fas fa-edit"></i> Editar Perfil</h1>
        <a href="<?= aptus_base() ?>/perfil" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>" role="status">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="editar-layout">
        <nav class="editar-abas" role="tablist" aria-label="Seções do perfil">
            <?php foreach ($abas as $chave => $aba): ?>
                <a href="?aba=<?= $chave ?>" role="tab" id="aba-<?= $chave ?>"
                   class="editar-aba <?= $chave === $abaAtiva ? 'ativa' : '' ?>"
                   aria-controls="painel-<?= $chave ?>" aria-selected="<?= $chave === $abaAtiva ? 'true' : 'false' ?>"
                   data-aba="<?= $chave ?>" data-sem-carregamento>
                    <i class="fas <?= $aba['icone'] ?>" aria-hidden="true"></i>
                    <span><?= $aba['rotulo'] ?></span>
                    <?php if ($chave === 'seguranca' && (!$emailVerificado || $codigoAtivo)): ?>
                        <span class="aba-alerta" title="Ação pendente" aria-label="Ação pendente"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="editar-paineis">

            <!-- ============================================================
                 INFORMAÇÕES PESSOAIS
                 ============================================================ -->
            <section class="perfil-card editar-painel" role="tabpanel" id="painel-pessoais"
                     aria-labelledby="aba-pessoais" <?= $abaAtiva !== 'pessoais' ? 'hidden' : '' ?>>
                <form method="POST" action="<?= aptus_base() ?>/perfil/atualizar" class="perfil-form" id="formPerfil" enctype="multipart/form-data">
                    <h2 class="painel-titulo">Informações pessoais</h2>
                    <p class="painel-sub">Seus dados de cadastro e como as pessoas veem você no Aptus.</p>

                    <div class="form-group foto-perfil-group">
                        <label>Foto de Perfil</label>
                        <div class="foto-preview-container">
                            <div class="foto-preview">
                                <?php if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] != 'default.png'): ?>
                                    <img src="<?= htmlspecialchars(UploadHelper::getUrl($usuario['foto_perfil']), ENT_QUOTES, 'UTF-8') ?>"
                                         alt="Foto de perfil" id="fotoPreview">
                                <?php else: ?>
                                    <img src="<?= aptus_base() ?>/public/images/default-avatar.svg"
                                         alt="Foto de perfil" id="fotoPreview">
                                <?php endif; ?>
                            </div>
                            <div class="foto-upload">
                                <label for="foto_perfil" class="btn-upload">
                                    <i class="fas fa-camera"></i> Escolher foto
                                </label>
                                <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" hidden>
                                <p class="foto-helper">Formatos: JPG, PNG, WEBP. Máximo: 2MB</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" class="form-control"
                               value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Nome de usuário</label>
                        <div class="input-prefixo">
                            <span aria-hidden="true">@</span>
                            <input type="text" id="username" name="username" class="form-control" required
                                   minlength="3" maxlength="30" pattern="[a-z0-9._]{3,30}"
                                   value="<?= htmlspecialchars($usuario['username'] ?? '') ?>"
                                   data-username-check data-atual="<?= htmlspecialchars($usuario['username'] ?? '') ?>"
                                   aria-describedby="usernameStatus">
                        </div>
                        <small id="usernameStatus" class="campo-status" role="status" aria-live="polite"></small>
                    </div>

                    <fieldset class="endereco-fieldset">
                        <legend>Identificação</legend>
                        <?php if (!$__cpfOk || !$__nascOk): ?>
                            <p class="campo-ajuda">Para anunciar ou contratar serviços é obrigatório informar CPF e data de nascimento e ter 18 anos ou mais. Depois de salvos, esses dados não podem ser alterados por aqui.</p>
                        <?php endif; ?>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="cpf">CPF</label>
                                <?php if ($__cpfOk): ?>
                                    <input type="text" id="cpf" class="form-control" disabled
                                           value="***.<?= substr($usuario['cpf_cnpj'], 3, 3) ?>.<?= substr($usuario['cpf_cnpj'], 6, 3) ?>-**">
                                    <small class="campo-status ok">Verificado</small>
                                <?php else: ?>
                                    <input type="text" id="cpf" name="cpf" class="form-control" inputmode="numeric"
                                           placeholder="000.000.000-00" maxlength="14" data-cpf-check>
                                    <small class="campo-status" data-cpf-status role="status" aria-live="polite"></small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="data_nascimento">Data de nascimento</label>
                                <?php if ($__nascOk): ?>
                                    <input type="text" id="data_nascimento" class="form-control" disabled
                                           value="<?= date('d/m/Y', strtotime($__nasc)) ?>">
                                <?php else: ?>
                                    <input type="text" id="data_nascimento" name="data_nascimento" class="form-control"
                                           inputmode="numeric" placeholder="dd/mm/aaaa" maxlength="10" data-idade-check>
                                    <small class="campo-status" data-idade-status role="status" aria-live="polite"></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" class="form-control"
                                   value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>"
                                   placeholder="(11) 99999-9999">
                        </div>
                        <div class="form-group">
                            <label for="whatsapp">WhatsApp</label>
                            <input type="text" id="whatsapp" name="whatsapp" class="form-control"
                                   value="<?= htmlspecialchars($usuario['whatsapp'] ?? '') ?>"
                                   placeholder="(11) 99999-9999">
                        </div>
                    </div>

                    <?php $endereco = $usuario; $enderecoObrigatorio = false; require __DIR__ . '/../partials/endereco.php'; ?>

                    <div class="form-group">
                        <label for="bio">Biografia</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4"
                                  placeholder="Conte um pouco sobre você..."><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-salvar">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <a href="<?= aptus_base() ?>/perfil" class="btn-cancelar">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <?= CsrfMiddleware::field() ?>
                </form>
            </section>

            <!-- ============================================================
                 SEGURANÇA
                 ============================================================ -->
            <section class="editar-painel painel-seguranca" role="tabpanel" id="painel-seguranca"
                     aria-labelledby="aba-seguranca" <?= $abaAtiva !== 'seguranca' ? 'hidden' : '' ?>>

                <!-- E-mail -->
                <div class="perfil-card seg-card">
                    <div class="seg-card-topo">
                        <span class="seg-icone" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                        <div>
                            <h2 class="painel-titulo">E-mail</h2>
                            <p class="painel-sub">Usado para entrar, recuperar a senha e receber avisos.</p>
                        </div>
                    </div>

                    <div class="seg-linha">
                        <span class="seg-valor"><?= htmlspecialchars($usuario['email']) ?></span>
                        <?php if ($emailVerificado): ?>
                            <span class="seg-selo ok"><i class="fas fa-check-circle" aria-hidden="true"></i> Verificado</span>
                        <?php else: ?>
                            <span class="seg-selo pendente"><i class="fas fa-exclamation-circle" aria-hidden="true"></i> Não verificado</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($codigoAtivo): ?>
                        <div class="seg-codigo">
                            <p>
                                <?php if ($emailPendente !== ''): ?>
                                    Enviamos um código para <strong><?= htmlspecialchars($emailPendente) ?></strong>. Digite-o abaixo para concluir a troca. Até lá, sua conta continua usando o e-mail atual.
                                <?php else: ?>
                                    Enviamos um código para <strong><?= htmlspecialchars($usuario['email']) ?></strong>. Digite-o abaixo para verificar seu e-mail.
                                <?php endif; ?>
                            </p>
                            <form method="POST" action="<?= aptus_base() ?>/perfil/email/confirmar" class="perfil-form seg-form-codigo">
                                <label for="codigo" class="sr-only">Código de 6 dígitos</label>
                                <input type="text" id="codigo" name="codigo" class="form-control campo-codigo" required
                                       inputmode="numeric" autocomplete="one-time-code" pattern="\d{6}" maxlength="6"
                                       placeholder="000000" aria-describedby="ajudaCodigo">
                                <button type="submit" class="btn-salvar"><i class="fas fa-check"></i> Confirmar código</button>
                                <?= CsrfMiddleware::field() ?>
                            </form>
                            <small class="campo-ajuda" id="ajudaCodigo">O código tem 6 números e vale por <?= (int) Usuario::CODIGO_MINUTOS ?> minutos. Não chegou? Veja a caixa de spam ou peça outro.</small>
                            <div class="seg-acoes-secundarias">
                                <form method="POST" action="<?= aptus_base() ?>/perfil/email/codigo">
                                    <input type="hidden" name="novo_email" value="<?= htmlspecialchars($emailPendente) ?>">
                                    <?php if ($emailPendente !== ''): ?>
                                        <p class="campo-ajuda">Para reenviar, confirme sua senha:</p>
                                        <input type="password" name="senha_atual" class="form-control seg-senha-mini" placeholder="Senha atual" autocomplete="current-password" required>
                                    <?php endif; ?>
                                    <button type="submit" class="btn-link"><i class="fas fa-redo"></i> Reenviar código</button>
                                    <?= CsrfMiddleware::field() ?>
                                </form>
                                <form method="POST" action="<?= aptus_base() ?>/perfil/email/cancelar">
                                    <button type="submit" class="btn-link btn-link-perigo"><i class="fas fa-times"></i> Cancelar</button>
                                    <?= CsrfMiddleware::field() ?>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if (!$emailVerificado): ?>
                            <form method="POST" action="<?= aptus_base() ?>/perfil/email/codigo" class="seg-form-inline">
                                <p class="campo-ajuda">Confirme que este e-mail é seu: enviaremos um código de 6 dígitos.</p>
                                <button type="submit" class="btn-salvar"><i class="fas fa-paper-plane"></i> Enviar código de verificação</button>
                                <?= CsrfMiddleware::field() ?>
                            </form>
                        <?php endif; ?>

                        <details class="seg-detalhes" <?= !$emailVerificado ? '' : '' ?>>
                            <summary><i class="fas fa-exchange-alt" aria-hidden="true"></i> Trocar e-mail</summary>
                            <form method="POST" action="<?= aptus_base() ?>/perfil/email/codigo" class="perfil-form">
                                <div class="form-group">
                                    <label for="novo_email">Novo e-mail</label>
                                    <input type="email" id="novo_email" name="novo_email" class="form-control" required maxlength="100"
                                           autocomplete="email" placeholder="seu.novo@email.com">
                                </div>
                                <div class="form-group">
                                    <label for="senha_email">Senha atual</label>
                                    <input type="password" id="senha_email" name="senha_atual" class="form-control" required
                                           autocomplete="current-password">
                                    <small class="campo-ajuda">Por segurança, confirme sua senha. Vamos enviar um código para o novo e-mail; a troca só acontece depois que você digitar esse código.</small>
                                </div>
                                <button type="submit" class="btn-salvar"><i class="fas fa-paper-plane"></i> Enviar código</button>
                                <?= CsrfMiddleware::field() ?>
                            </form>
                        </details>
                    <?php endif; ?>
                </div>

                <!-- Senha -->
                <div class="perfil-card seg-card">
                    <div class="seg-card-topo">
                        <span class="seg-icone" aria-hidden="true"><i class="fas fa-key"></i></span>
                        <div>
                            <h2 class="painel-titulo">Senha</h2>
                            <p class="painel-sub">Ao trocar a senha, o "Lembrar-me" dos outros aparelhos é desligado.</p>
                        </div>
                    </div>
                    <details class="seg-detalhes">
                        <summary><i class="fas fa-lock" aria-hidden="true"></i> Alterar senha</summary>
                        <form method="POST" action="<?= aptus_base() ?>/perfil/senha" class="perfil-form">
                            <div class="form-group">
                                <label for="senha_atual">Senha atual</label>
                                <input type="password" id="senha_atual" name="senha_atual" class="form-control" required autocomplete="current-password">
                            </div>
                            <div class="form-row-2">
                                <div class="form-group">
                                    <label for="nova_senha">Nova senha</label>
                                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" required minlength="8" autocomplete="new-password">
                                </div>
                                <div class="form-group">
                                    <label for="nova_senha_confirm">Confirmar nova senha</label>
                                    <input type="password" id="nova_senha_confirm" name="nova_senha_confirm" class="form-control" required minlength="8" autocomplete="new-password">
                                </div>
                            </div>
                            <small class="campo-ajuda">Mínimo de 8 caracteres, com letras maiúsculas, minúsculas e números.</small>
                            <div class="form-actions">
                                <button type="submit" class="btn-salvar"><i class="fas fa-save"></i> Salvar nova senha</button>
                            </div>
                            <?= CsrfMiddleware::field() ?>
                        </form>
                    </details>
                </div>

                <!-- Google -->
                <?php if (GoogleAuth::configurado() || $googleLigado): ?>
                <div class="perfil-card seg-card">
                    <div class="seg-card-topo">
                        <span class="seg-icone" aria-hidden="true"><i class="fab fa-google"></i></span>
                        <div>
                            <h2 class="painel-titulo">Login com Google</h2>
                            <p class="painel-sub">
                                <?= $googleLigado
                                    ? 'Você pode entrar com seu e-mail e senha ou só clicando em "Entrar com Google".'
                                    : 'Conecte sua conta Google para entrar com um clique, sem digitar senha. Seu e-mail e senha continuam funcionando.' ?>
                            </p>
                        </div>
                    </div>
                    <div class="seg-linha">
                        <?php if ($googleLigado): ?>
                            <?php if (!empty($usuario['google_email'])): ?>
                                <span class="seg-valor"><?= GoogleAuth::iconeSvg(16) ?> <?= htmlspecialchars($usuario['google_email']) ?></span>
                            <?php endif; ?>
                            <span class="seg-selo ok"><i class="fas fa-link" aria-hidden="true"></i> Conectado</span>
                            <form method="POST" action="<?= aptus_base() ?>/perfil/google/desvincular"
                                  onsubmit="return confirm('Desconectar a conta Google? Depois disso, para entrar, use seu e-mail e senha.');">
                                <button type="submit" class="btn-link btn-link-perigo"><i class="fas fa-unlink"></i> Desconectar</button>
                                <?= CsrfMiddleware::field() ?>
                            </form>
                        <?php else: ?>
                            <span class="seg-selo neutro">Não conectado</span>
                            <a href="<?= aptus_base() ?>/auth/google?conectar=1" class="btn-google-mini">
                                <?= GoogleAuth::iconeSvg() ?> Conectar conta Google
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!$googleLigado): ?>
                        <small class="campo-ajuda">Pode ser uma conta Google com outro e-mail. Ela só não pode estar ligada a outro cadastro do Aptus.</small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- ============================================================
                 OUTRAS CONFIGURAÇÕES
                 ============================================================ -->
            <section class="editar-painel" role="tabpanel" id="painel-outras"
                     aria-labelledby="aba-outras" <?= $abaAtiva !== 'outras' ? 'hidden' : '' ?>>

                <div class="perfil-card seg-card perfil-form">
                    <div class="seg-card-topo">
                        <span class="seg-icone" aria-hidden="true"><i class="fas fa-map-marker-alt"></i></span>
                        <div>
                            <h2 class="painel-titulo">Privacidade</h2>
                            <p class="painel-sub">Escolha o que aparece no seu perfil público.</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="privacidade_localizacao">Localização no perfil público</label>
                        <?php $__priv = $usuario['privacidade_localizacao'] ?? 'oculto'; ?>
                        <select id="privacidade_localizacao" name="privacidade_localizacao" class="form-control" form="formPerfil">
                            <option value="oculto" <?= $__priv === 'oculto' ? 'selected' : '' ?>>Não mostrar minha localização</option>
                            <option value="cidade" <?= $__priv === 'cidade' ? 'selected' : '' ?>>Só a cidade</option>
                            <option value="cidade_bairro" <?= $__priv === 'cidade_bairro' ? 'selected' : '' ?>>Cidade e bairro</option>
                        </select>
                        <small class="campo-ajuda">Rua, número e CEP nunca aparecem para outras pessoas.</small>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-salvar" form="formPerfil"><i class="fas fa-save"></i> Salvar</button>
                    </div>
                </div>

                <div class="perfil-card seg-card">
                    <div class="seg-card-topo">
                        <span class="seg-icone" aria-hidden="true"><i class="fas fa-cog"></i></span>
                        <div>
                            <h2 class="painel-titulo">Mais opções</h2>
                            <p class="painel-sub">Atalhos para outras preferências da sua conta.</p>
                        </div>
                    </div>
                    <ul class="opcoes-lista">
                        <li>
                            <a href="<?= aptus_base() ?>/perfil/bloqueados">
                                <i class="fas fa-user-slash" aria-hidden="true"></i>
                                <span><strong>Usuários bloqueados</strong><small>Veja e desfaça bloqueios.</small></span>
                                <i class="fas fa-chevron-right seta" aria-hidden="true"></i>
                            </a>
                        </li>
                        <li>
                            <button type="button" data-abrir-acessibilidade>
                                <i class="fas fa-universal-access" aria-hidden="true"></i>
                                <span><strong>Aparência e acessibilidade</strong><small>Tema claro ou escuro, tamanho da fonte e modo para daltonismo.</small></span>
                                <i class="fas fa-chevron-right seta" aria-hidden="true"></i>
                            </button>
                        </li>
                        <li>
                            <a href="<?= aptus_base() ?>/perfil/portfolio">
                                <i class="fas fa-images" aria-hidden="true"></i>
                                <span><strong>Meu portfólio</strong><small>Trabalhos que aparecem no seu perfil público.</small></span>
                                <i class="fas fa-chevron-right seta" aria-hidden="true"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Preview da foto
    var inputFoto = document.getElementById('foto_perfil');
    var preview   = document.getElementById('fotoPreview');
    if (inputFoto && preview) {
        inputFoto.addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) { preview.src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }

    // Abas sem recarregar a página (a URL guarda a aba para voltar nela)
    var abas = document.querySelectorAll('.editar-aba');
    function ativar(chave, focar) {
        abas.forEach(function (a) {
            var on = a.dataset.aba === chave;
            a.classList.toggle('ativa', on);
            a.setAttribute('aria-selected', on ? 'true' : 'false');
            a.tabIndex = on ? 0 : -1;
            document.getElementById('painel-' + a.dataset.aba).hidden = !on;
            if (on && focar) a.focus();
        });
        try { history.replaceState(null, '', '?aba=' + chave); } catch (e) {}
    }
    abas.forEach(function (a, i) {
        a.tabIndex = a.classList.contains('ativa') ? 0 : -1;
        a.addEventListener('click', function (e) { e.preventDefault(); ativar(a.dataset.aba, false); });
        a.addEventListener('keydown', function (e) {
            var dir = (e.key === 'ArrowRight' || e.key === 'ArrowDown') ? 1 : (e.key === 'ArrowLeft' || e.key === 'ArrowUp') ? -1 : 0;
            if (!dir) return;
            e.preventDefault();
            ativar(abas[(i + dir + abas.length) % abas.length].dataset.aba, true);
        });
    });

    // Só números no campo do código
    var codigo = document.getElementById('codigo');
    if (codigo) codigo.addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 6); });

    // Atalho para o painel de acessibilidade da navbar
    var btnAcess = document.querySelector('[data-abrir-acessibilidade]');
    if (btnAcess) btnAcess.addEventListener('click', function () {
        var b = document.getElementById('btnAcessibilidade');
        if (b) b.click();
    });
});
</script>

<?php $jsPagina = ['cep.js', 'cadastro.js']; require_once __DIR__ . '/../layouts/footer.php'; ?>
