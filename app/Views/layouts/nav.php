<?php
// app/Views/layouts/nav.php

$usuario = $_SESSION['usuario'] ?? null;

if ($usuario) {
    $nomeUsuario = $usuario['nome'] ?? 'Usuario';
    $role = $usuario['role'] ?? 3;
    $totalNotificacoes = 0;
    
    if (file_exists(__DIR__ . '/../Models/Notificacao.php')) {
        require_once __DIR__ . '/../Models/Notificacao.php';
        $notificacao = new Notificacao();
        $totalNotificacoes = $notificacao->contarNaoLidas($usuario['id']);
    }
    require_once __DIR__ . '/../../Helpers/RestricaoHelper.php';
    $__suspensoAte = \RestricaoHelper::suspensoAte((int) $usuario['id']);
} else {
    $__suspensoAte = null;
    $nomeUsuario = null;
    $role = null;
    $totalNotificacoes = 0;
}
?>

<?php
$__caminhoAtual = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$__ehHome = in_array(strtolower($__caminhoAtual), ['', '/aptus', '/aptus/public', '/aptus/index.php'], true);
?>
<nav class="navbar">
    <a href="<?= aptus_base() ?>/" class="navbar-logo">
        <img id="img-logo" src="<?= aptus_base() ?>/public/images/logo.png" 
             alt="Logo" 
             onerror="this.src='<?= aptus_base() ?>/public/images/logo-default.png'; this.onerror=null;">
    </a>

    <ul class="navbar-links">
        <li><a href="<?= aptus_base() ?>/"><i class="fas fa-home"></i> Início</a></li>
        <li><a href="<?= aptus_base() ?>/anuncios"><i class="fas fa-tools"></i> Serviços</a></li>
        <li><a href="<?= aptus_base() ?>/sobre"><i class="fas fa-info-circle"></i> Sobre</a></li>
        <li><a href="<?= aptus_base() ?>/contato"><i class="fas fa-envelope"></i> Contato</a></li>
        
        <?php if ($usuario && $role == 3): ?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-plus-circle"></i> Anúnciar <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <?php if ($__suspensoAte): ?>
                        <li><span class="link-bloqueado" title="Conta suspensa"><i class="fas fa-lock"></i> Criar Anúncio</span></li>
                    <?php else: ?>
                        <li><a href="<?= aptus_base() ?>/anuncios/criar"><i class="fas fa-plus"></i> Criar Anúncio</a></li>
                    <?php endif; ?>
                    <li><a href="<?= aptus_base() ?>/anuncios/meus"><i class="fas fa-list"></i> Meus Anúncios</a></li>
                </ul>
            </li>
        <?php endif; ?>
        
        <?php if ($usuario && ($role == 1 || $role == 2 || $role == 4)): ?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-cog"></i> Admin <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <?php if ($role == 1 || $role == 4): ?>
                        <li><a href="<?= aptus_base() ?>/admin/dashboard"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 1 || $role == 2 || $role == 4): ?>
                        <li><a href="<?= aptus_base() ?>/moderator"><i class="fas fa-shield-alt"></i> Moderação</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 1 || $role == 4): ?>
                        <li><a href="<?= aptus_base() ?>/admin/usuarios"><i class="fas fa-users"></i> Usuários</a></li>
                        <li><a href="<?= aptus_base() ?>/admin/configuracoes"><i class="fas fa-cogs"></i> Configurações</a></li>
                    <?php endif; ?>
                    
                    <!-- RELATORIOS - ADICIONADO AQUI -->
                    <?php if ($role == 1 || $role == 2 || $role == 4): ?>
                        <li><a href="<?= aptus_base() ?>/relatorios"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 4): ?>
                        <li><a href="<?= aptus_base() ?>/master" style="color: #f59e0b;"><i class="fas fa-crown"></i> Área Master</a></li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>
        
        <!-- NOTIFICACAO COM BADGE -->
        <?php if ($usuario): ?>
            <li>
                <a href="<?= aptus_base() ?>/notificacoes" class="nav-notificacao" id="navNotificacao">
                    <i class="fas fa-bell"></i>
                    <span class="badge-notificacao" id="badgeNotificacao" style="display: <?= $totalNotificacoes > 0 ? 'inline-flex' : 'none' ?>;">
                        <?= $totalNotificacoes ?>
                    </span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="navbar-perfil">
        <!-- ACESSIBILIDADE: tema, daltonismo e tamanho da fonte -->
        <div class="acessibilidade-nav">
            <button type="button" class="btn-acessibilidade" id="btnAcessibilidade"
                    aria-haspopup="true" aria-expanded="false" aria-controls="painelAcessibilidade"
                    title="Acessibilidade">
                <i class="fas fa-universal-access" aria-hidden="true"></i>
                <span class="sr-only">Opções de acessibilidade</span>
            </button>

            <div class="painel-acessibilidade" id="painelAcessibilidade" role="dialog"
                 aria-labelledby="acessTitulo" hidden>
                <div class="menu-title" id="acessTitulo">
                    <i class="fas fa-universal-access" aria-hidden="true"></i>
                    <span>Acessibilidade</span>
                </div>

                <div class="acess-grupo">
                    <div class="acess-grupo-titulo" id="acessTemaTitulo">
                        <i class="fas fa-circle-half-stroke" aria-hidden="true"></i> Tema
                    </div>
                    <div class="acess-segmentado" role="group" aria-labelledby="acessTemaTitulo">
                        <button type="button" class="acess-opcao" data-acess-tema="claro" aria-pressed="false">
                            <i class="fas fa-sun" aria-hidden="true"></i> Claro
                        </button>
                        <button type="button" class="acess-opcao" data-acess-tema="escuro" aria-pressed="false">
                            <i class="fas fa-moon" aria-hidden="true"></i> Escuro
                        </button>
                    </div>
                </div>

                <div class="acess-grupo">
                    <div class="acess-grupo-titulo" id="acessFonteTitulo">
                        <i class="fas fa-text-height" aria-hidden="true"></i> Tamanho do texto
                    </div>
                    <div class="acess-segmentado" role="group" aria-labelledby="acessFonteTitulo">
                        <button type="button" class="acess-opcao" data-acess-fonte="diminuir" aria-label="Diminuir texto">
                            A<sup>−</sup>
                        </button>
                        <span class="acess-fonte-valor" id="acessFonteValor">100%</span>
                        <button type="button" class="acess-opcao" data-acess-fonte="aumentar" aria-label="Aumentar texto">
                            A<sup>+</sup>
                        </button>
                    </div>
                </div>

                <div class="acess-grupo">
                    <div class="acess-grupo-titulo" id="acessDaltonismoTitulo">
                        <i class="fas fa-eye" aria-hidden="true"></i> Daltonismo
                    </div>
                    <div class="acess-lista" role="radiogroup" aria-labelledby="acessDaltonismoTitulo">
                        <button type="button" class="acess-opcao" role="radio" data-acess-daltonismo="nenhum" aria-checked="true">
                            <span class="acess-opcao-texto">Cores padrão</span>
                            <span class="acess-amostras" aria-hidden="true">
                                <span style="background:#10b981"></span><span style="background:#ef4444"></span><span style="background:#f59e0b"></span>
                            </span>
                        </button>
                        <button type="button" class="acess-opcao" role="radio" data-acess-daltonismo="protanopia" aria-checked="false">
                            <span class="acess-opcao-texto">Protanopia <small>dificuldade com vermelho</small></span>
                            <span class="acess-amostras" aria-hidden="true">
                                <span style="background:#0072b2"></span><span style="background:#d55e00"></span><span style="background:#b7791f"></span>
                            </span>
                        </button>
                        <button type="button" class="acess-opcao" role="radio" data-acess-daltonismo="deuteranopia" aria-checked="false">
                            <span class="acess-opcao-texto">Deuteranopia <small>dificuldade com verde</small></span>
                            <span class="acess-amostras" aria-hidden="true">
                                <span style="background:#0072b2"></span><span style="background:#c2410c"></span><span style="background:#b7791f"></span>
                            </span>
                        </button>
                        <button type="button" class="acess-opcao" role="radio" data-acess-daltonismo="tritanopia" aria-checked="false">
                            <span class="acess-opcao-texto">Tritanopia <small>dificuldade com azul/amarelo</small></span>
                            <span class="acess-amostras" aria-hidden="true">
                                <span style="background:#0f766e"></span><span style="background:#c81e3a"></span><span style="background:#a21caf"></span>
                            </span>
                        </button>
                    </div>
                </div>

                <button type="button" class="btn-acess-restaurar" data-acess-restaurar>
                    <i class="fas fa-rotate-left" aria-hidden="true"></i> Restaurar padrão
                </button>

                <div class="sr-only" id="acessAviso" role="status" aria-live="polite"></div>
            </div>
        </div>

        <?php if ($usuario): ?>
            <button type="button" class="btn-perfil-navbar" id="btnPerfilNavbar">
                <i class="fas fa-user"></i>
                <span><?php echo htmlspecialchars(explode(' ', $nomeUsuario)[0]); ?></span>
                <?php if ($totalNotificacoes > 0): ?>
                    <span class="badge-notificacao badge-mobile" id="badgeMobile"><?= $totalNotificacoes ?></span>
                <?php endif; ?>
            </button>
        <?php else: ?>
            <a href="<?= aptus_base() ?>/login" class="btn-login-navbar">
                <i class="fas fa-sign-in-alt"></i> Logar
            </a>
        <?php endif; ?>

        <div class="menu-perfil-navbar" id="menuPerfilNavbar">
            <div class="menu-title">
                <i class="fas fa-user-circle"></i>
                <span>Minha Conta</span>
            </div>

            <?php if ($usuario): ?>
            <div class="menu-perfil-itens">
                <span class="menu-grupo">Conta</span>
                <a href="<?= aptus_base() ?>/perfil" class="menu-item">
                    <i class="fas fa-user"></i> Meu Perfil
                </a>
                <a href="<?= aptus_base() ?>/chat" class="menu-item">
                    <i class="fas fa-comments"></i> Chat
                    <?php if ($totalNotificacoes > 0): ?>
                        <span class="badge-notificacao badge-menu" id="badgeMenu"><?= $totalNotificacoes ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= aptus_base() ?>/favoritos" class="menu-item">
                    <i class="fas fa-heart"></i> Meus Favoritos
                </a>

                <span class="menu-grupo">Painéis</span>
                <a href="<?= aptus_base() ?>/freelancer" class="menu-item">
                    <i class="fas fa-briefcase"></i> Dashboard Freelancer
                </a>
                <?php if ($role == 3): ?>
                    <a href="<?= aptus_base() ?>/cliente" class="menu-item">
                        <i class="fas fa-chart-line"></i> Dashboard Cliente
                    </a>
                <?php endif; ?>

                <span class="menu-grupo">Serviços</span>
                <a href="<?= aptus_base() ?>/interesses/pendentes" class="menu-item">
                    <i class="fas fa-clock"></i> Propostas Pendentes
                </a>
                <a href="<?= aptus_base() ?>/interesses/ativos" class="menu-item">
                    <i class="fas fa-check-circle"></i> Serviços Ativos
                </a>
                <a href="<?= aptus_base() ?>/interesses/meus" class="menu-item">
                    <i class="fas fa-paper-plane"></i> Meus Interesses
                </a>

                <span class="menu-grupo">Segurança</span>
                <a href="<?= aptus_base() ?>/denuncias" class="menu-item">
                    <i class="fas fa-flag"></i> Minhas Denúncias
                </a>
                <a href="<?= aptus_base() ?>/perfil/bloqueados" class="menu-item">
                    <i class="fas fa-user-slash"></i> Usuários Bloqueados
                </a>
            </div>
            <?php endif; ?>

            <!-- Fora da área com rolagem: "Sair" fica sempre visível no fim do menu -->
            <form method="POST" action="<?= aptus_base() ?>/logout" class="menu-perfil-sair">
                <?= \CsrfMiddleware::field() ?>
                <button type="submit" class="btn-logout-dropdown">
                    <i class="fas fa-sign-out-alt"></i> Sair da Conta
                </button>
            </form>
        </div>
    </div>

    <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<?php if (!empty($__suspensoAte)): ?>
<div class="aviso-suspensao" role="status">
    <i class="fas fa-user-clock" aria-hidden="true"></i>
    <span>Sua conta está suspensa até <strong><?= date('d/m/Y H:i', strtotime($__suspensoAte)) ?></strong>.
        Você pode navegar, mas não pode anunciar nem contratar serviços.</span>
    <a href="<?= aptus_base() ?>/notificacoes">Ver motivo</a>
</div>
<?php endif; ?>



<script id="usuarioData" type="application/json">
<?php echo json_encode($_SESSION['usuario'] ?? null); ?>
</script>

<script src="<?= aptus_asset('js/nav.js') ?>"></script>
<?php if (!$__ehHome): ?>
<script src="<?= aptus_asset('js/voltar.js') ?>"></script>
<?php endif; ?>
<script src="<?= aptus_asset('js/acessibilidade.js') ?>"></script>
<script src="<?= aptus_asset('js/notificacoes.js') ?>"></script>