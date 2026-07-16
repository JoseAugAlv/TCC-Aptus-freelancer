// ============================================================
// APTUS - MAIN.JS
// Menu Hamburguer, Dropdowns e Utilitários
// ============================================================

document.addEventListener('DOMContentLoaded', function() {

    // ==========================================
    // MENU HAMBURGER
    // ==========================================
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.querySelector('.navbar-links');
    const body = document.body;
    
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function(e) {
            e.stopPropagation();
            navLinks.classList.toggle('ativo');
            body.style.overflow = navLinks.classList.contains('ativo') ? 'hidden' : '';
        });
    }
    
    // Fechar menu ao clicar em um link
    if (navLinks) {
        navLinks.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navLinks.classList.remove('ativo');
                body.style.overflow = '';
            });
        });
    }
    
    // Fechar menu ao clicar fora
    document.addEventListener('click', function(e) {
        if (navLinks && navLinks.classList.contains('ativo')) {
            if (!navLinks.contains(e.target) && !hamburger.contains(e.target)) {
                navLinks.classList.remove('ativo');
                body.style.overflow = '';
            }
        }
    });

    // ==========================================
    // MENU PERFIL
    // ==========================================
    const btnPerfil = document.getElementById('btnPerfilNavbar');
    const menuPerfil = document.getElementById('menuPerfilNavbar');
    
    if (btnPerfil && menuPerfil) {
        btnPerfil.addEventListener('click', function(e) {
            e.stopPropagation();
            menuPerfil.classList.toggle('ativo');
        });
        
        document.addEventListener('click', function(e) {
            if (!btnPerfil.contains(e.target) && !menuPerfil.contains(e.target)) {
                menuPerfil.classList.remove('ativo');
            }
        });
    }

    // ==========================================
    // DROPDOWN ADMIN
    // ==========================================
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(function(dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('open');
            });
        }
    });
    
    document.addEventListener('click', function(e) {
        dropdowns.forEach(function(dropdown) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    });

    // ==========================================
    // FLASH MESSAGES AUTO-FECHAR
    // ==========================================
    document.querySelectorAll('.flash-sucesso, .flash-erro, .flash-aviso').forEach(function(msg) {
        setTimeout(function() {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(function() {
                msg.style.display = 'none';
            }, 500);
        }, 4000);
    });

    // ==========================================
    // FUNÇÃO LOGOUT
    // ==========================================
    window.logout = function() {
        if (confirm('Deseja realmente sair da sua conta?')) {
            window.location.href = '/Aptus/logout';
        }
    };
});