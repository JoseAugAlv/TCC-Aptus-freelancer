<?php
// app/Views/layouts/nav.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['usuario'] ?? null;

if ($usuario) {
    $perfil = $usuario['role'] ?? 3;
    $nomeUsuario = $usuario['nome'] ?? 'Usuário';
    $totalNotificacoes = 0;
    
    if (file_exists(__DIR__ . '/../../Helpers/NavHelper.php')) {
        require_once __DIR__ . '/../../Helpers/NavHelper.php';
        if (class_exists('NavHelper')) {
            $totalNotificacoes = NavHelper::getContadorNotificacoes($usuario['id']);
        }
    }
} else {
    $perfil = null;
    $nomeUsuario = null;
    $totalNotificacoes = 0;
}
?>

<nav class="navbar">
    <a href="/Aptus/" class="navbar-logo">
        <img id="img-logo" src="/Aptus/public/images/logo.png" alt="Logo Aptus" 
             onerror="this.src='/Aptus/public/images/logo-default.png'; this.onerror=null;">
    </a>

    <ul class="navbar-links">
        <li><a href="/Aptus/"><i class="fas fa-home"></i> Início</a></li>
        <li><a href="/Aptus/anuncios"><i class="fas fa-tools"></i> Serviços</a></li>
        <li><a href="/Aptus/sobre"><i class="fas fa-info-circle"></i> Sobre</a></li>
        <li><a href="/Aptus/contato"><i class="fas fa-envelope"></i> Contato</a></li>
        
        <?php if ($usuario): ?>
            <?php if ($perfil == 3): ?>
                <li><a href="/Aptus/anuncios/criar"><i class="fas fa-plus-circle"></i> Anunciar</a></li>
            <?php endif; ?>
            
            <?php if ($perfil == 1 || $perfil == 2 || $perfil == 4): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-cog"></i> Admin <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ($perfil == 1 || $perfil == 2): ?>
                            <li><a href="/Aptus/moderator/anuncios"><i class="fas fa-clipboard-list"></i> Moderar Anúncios</a></li>
                            <li><a href="/Aptus/moderator/denuncias"><i class="fas fa-exclamation-triangle"></i> Denúncias</a></li>
                            <li><a href="/Aptus/moderator/disputas"><i class="fas fa-gavel"></i> Disputas</a></li>
                            <li><a href="/Aptus/moderator/usuarios"><i class="fas fa-users"></i> Usuários</a></li>
                            <li><a href="/Aptus/moderator/categorias"><i class="fas fa-tags"></i> Categorias</a></li>
                        <?php endif; ?>
                        
                        <?php if ($perfil == 1 || $perfil == 4): ?>
                            <li><a href="/Aptus/admin/dashboard"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                            <li><a href="/Aptus/admin/usuarios"><i class="fas fa-user-cog"></i> Gerenciar Usuários</a></li>
                            <li><a href="/Aptus/admin/anuncios"><i class="fas fa-briefcase"></i> Gerenciar Anúncios</a></li>
                            <li><a href="/Aptus/admin/denuncias"><i class="fas fa-flag"></i> Gerenciar Denúncias</a></li>
                            <li><a href="/Aptus/admin/disputas"><i class="fas fa-scale-balanced"></i> Gerenciar Disputas</a></li>
                            <li><a href="/Aptus/admin/categorias"><i class="fas fa-list"></i> Gerenciar Categorias</a></li>
                            <li><a href="/Aptus/logs"><i class="fas fa-history"></i> Logs do Sistema</a></li>
                            <li><a href="/Aptus/relatorios"><i class="fas fa-file-alt"></i> Relatórios</a></li>
                        <?php endif; ?>
                        
                        <?php if ($perfil == 4): ?>
                            <li><a href="/Aptus/master" style="color: #f59e0b;"><i class="fas fa-crown"></i> Área Master</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>

    <div class="navbar-perfil">
        <?php if ($usuario): ?>
            <button class="btn-perfil-navbar" id="btnPerfilNavbar">
                <i class="fas fa-user"></i>
                <span style="margin-left: 8px; font-size: 0.9rem; font-weight: 600; color: #1a1a1a;">
                    <?php echo htmlspecialchars(explode(' ', $nomeUsuario)[0]); ?>
                </span>
                <?php if ($totalNotificacoes > 0): ?>
                    <span class="badge-notificacao"><?= $totalNotificacoes ?></span>
                <?php endif; ?>
            </button>
        <?php else: ?>
            <button class="btn-login-navbar" onclick="window.location.href='/Aptus/login'">
                <i class="fas fa-sign-in-alt"></i> Logar
            </button>
        <?php endif; ?>

        <div class="menu-perfil-navbar" id="menuPerfilNavbar">
            <div class="menu-title">
                <i class="fas fa-user-circle"></i>
                <span>Minha Conta</span>
            </div>

            <?php if ($usuario): ?>
                <button onclick="window.location.href='/Aptus/perfil'">
                    <i class="fas fa-id-card"></i> Meu Perfil
                </button>
                <button onclick="window.location.href='/Aptus/interesses/meus'">
                    <i class="fas fa-handshake"></i> Meus Interesses
                </button>
                <button onclick="window.location.href='/Aptus/favoritos'">
                    <i class="fas fa-heart"></i> Favoritos
                </button>
                <?php if ($perfil == 3): ?>
                    <button onclick="window.location.href='/Aptus/anuncios/criar'">
                        <i class="fas fa-plus-circle"></i> Anunciar Serviço
                    </button>
                <?php endif; ?>
                <button onclick="window.location.href='/Aptus/notificacoes'">
                    <i class="fas fa-bell"></i> Notificações
                    <?php if ($totalNotificacoes > 0): ?>
                        <span class="badge-notificacao"><?= $totalNotificacoes ?></span>
                    <?php endif; ?>
                </button>
                <?php if ($perfil == 1 || $perfil == 2 || $perfil == 4): ?>
                    <button onclick="window.location.href='/Aptus/moderator/anuncios'" style="border-top: 1px solid rgba(0,101,119,0.08);">
                        <i class="fas fa-shield-alt"></i> Moderação
                    </button>
                <?php endif; ?>
                <?php if ($perfil == 1 || $perfil == 4): ?>
                    <button onclick="window.location.href='/Aptus/admin/dashboard'">
                        <i class="fas fa-cog"></i> Administração
                    </button>
                <?php endif; ?>
                <button class="btn-logout-dropdown" onclick="logout()" style="border-top: 1px solid rgba(0,101,119,0.08); color: #dc2626;">
                    <i class="fas fa-sign-out-alt"></i> Sair da Conta
                </button>
            <?php else: ?>
                <button onclick="window.location.href='/Aptus/login'">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
                <button onclick="window.location.href='/Aptus/login/cadastrar'">
                    <i class="fas fa-user-plus"></i> Cadastrar
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<script src="/Aptus/public/js/main.js"></script>