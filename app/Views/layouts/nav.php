<?php
// app/Views/layouts/nav.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['usuario'] ?? null;

if ($usuario) {
    $nomeUsuario = $usuario['nome'] ?? 'Usuário';
    $role = $usuario['role'] ?? 3;
    $totalNotificacoes = 0;
    
    if (file_exists(__DIR__ . '/../Models/Notificacao.php')) {
        require_once __DIR__ . '/../Models/Notificacao.php';
        $notificacao = new Notificacao();
        $totalNotificacoes = $notificacao->contarNaoLidas($usuario['id']);
    }
} else {
    $nomeUsuario = null;
    $role = null;
    $totalNotificacoes = 0;
}
?>

<nav class="navbar">
    <a href="/Aptus/" class="navbar-logo">
        <img id="img-logo" src="/Aptus/public/images/logo.png" 
             alt="Logo" 
             onerror="this.src='/Aptus/public/images/logo-default.png'; this.onerror=null;">
    </a>

    <ul class="navbar-links">
        <li><a href="/Aptus/"><i class="fas fa-home"></i> Início</a></li>
        <li><a href="/Aptus/anuncios"><i class="fas fa-tools"></i> Serviços</a></li>
        <li><a href="/Aptus/sobre"><i class="fas fa-info-circle"></i> Sobre</a></li>
        <li><a href="/Aptus/contato"><i class="fas fa-envelope"></i> Contato</a></li>
        
        <?php if ($usuario && $role == 3): ?>
            <li><a href="/Aptus/anuncios/criar"><i class="fas fa-plus-circle"></i> Anunciar</a></li>
        <?php endif; ?>
        
        <?php if ($usuario && ($role == 1 || $role == 2 || $role == 4)): ?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-cog"></i> Admin <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <?php if ($role == 1 || $role == 4): ?>
                        <li><a href="/Aptus/admin/dashboard"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 1 || $role == 2): ?>
                        <li><a href="/Aptus/moderator"><i class="fas fa-shield-alt"></i> Moderação</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 1 || $role == 4): ?>
                        <li><a href="/Aptus/admin/configuracoes"><i class="fas fa-cogs"></i> Configurações</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 4): ?>
                        <li><a href="/Aptus/master" style="color: #f59e0b;"><i class="fas fa-crown"></i> Área Master</a></li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>
        
        <?php if ($usuario): ?>
            <li>
                <a href="/Aptus/notificacoes" class="nav-notificacao" id="navNotificacao">
                    <i class="fas fa-bell"></i>
                    <?php if ($totalNotificacoes > 0): ?>
                        <span class="badge-notificacao" id="badgeNotificacao"><?= $totalNotificacoes ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="navbar-perfil">
        <?php if ($usuario): ?>
            <button class="btn-perfil-navbar" id="btnPerfilNavbar">
                <i class="fas fa-user"></i>
                <span><?php echo htmlspecialchars(explode(' ', $nomeUsuario)[0]); ?></span>
                <?php if ($totalNotificacoes > 0): ?>
                    <span class="badge-notificacao badge-mobile" id="badgeMobile"><?= $totalNotificacoes ?></span>
                <?php endif; ?>
            </button>
        <?php else: ?>
            <a href="/Aptus/login" class="btn-login-navbar">
                <i class="fas fa-sign-in-alt"></i> Logar
            </a>
        <?php endif; ?>

        <div class="menu-perfil-navbar" id="menuPerfilNavbar">
            <div class="menu-title">
                <i class="fas fa-user-circle"></i>
                <span>Minha Conta</span>
            </div>

            <button onclick="irParaPainel()">
                <i class="fas fa-id-card"></i> Meu Painel
            </button>

            <?php if ($usuario): ?>
                <a href="/Aptus/perfil" class="menu-item">
                    <i class="fas fa-user"></i> Meu Perfil
                </a>
                <a href="/Aptus/notificacoes" class="menu-item" id="menuNotificacao">
                    <i class="fas fa-bell"></i> Notificações
                    <?php if ($totalNotificacoes > 0): ?>
                        <span class="badge-notificacao badge-menu" id="badgeMenu"><?= $totalNotificacoes ?></span>
                    <?php endif; ?>
                </a>
                <a href="/Aptus/interesses/meus" class="menu-item">
                    <i class="fas fa-paper-plane"></i> Interesses Enviados
                </a>
                <a href="/Aptus/interesses/recebidos" class="menu-item">
                    <i class="fas fa-inbox"></i> Interesses Recebidos
                </a>
            <?php endif; ?>

            <button class="btn-logout-dropdown" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Sair da Conta
            </button>
        </div>
    </div>

    <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<script id="usuarioData" type="application/json">
<?php echo json_encode($_SESSION['usuario'] ?? null); ?>
</script>

<script src="/Aptus/public/js/nav.js"></script>
<script src="/Aptus/public/js/notificacoes.js"></script>