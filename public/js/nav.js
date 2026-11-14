// public/js/nav.js

document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // SIDEBAR
    // ==========================================
    const openBtn = document.getElementById('openBtn');
    const closeBtn = document.getElementById('closeBtn');
    const sidebar = document.getElementById('sidebar');
    const body = document.body;

    if (openBtn && sidebar) {
        openBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.add('active');
            body.style.overflow = 'hidden';
        });
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('active');
            body.style.overflow = '';
        });
    }

    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                sidebar.classList.remove('active');
                body.style.overflow = '';
            });
        });
    }

    document.addEventListener('click', function(e) {
        if (sidebar && sidebar.classList.contains('active')) {
            const clicouForaDaSidebar = !sidebar.contains(e.target);
            const clicouForaDoBotaoAbrir = !openBtn || !openBtn.contains(e.target);
            if (clicouForaDaSidebar && clicouForaDoBotaoAbrir) {
                sidebar.classList.remove('active');
                body.style.overflow = '';
            }
        }
    });

    // ==========================================
    // MENU DE PERFIL
    // ==========================================
    const btnPerfil = document.getElementById('btnPerfilNavbar');
    const menuPerfil = document.getElementById('menuPerfilNavbar');

    if (btnPerfil && menuPerfil) {
        // Evita que o botão seja tratado como submit caso esteja dentro de um form.
        btnPerfil.type = 'button';

        btnPerfil.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            menuPerfil.classList.toggle('ativo');
        });

        menuPerfil.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        document.addEventListener('click', function(e) {
            if (!menuPerfil.contains(e.target) && !btnPerfil.contains(e.target)) {
                menuPerfil.classList.remove('ativo');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                menuPerfil.classList.remove('ativo');
            }
        });
    }

    // ==========================================
    // DROPDOWN
    // ==========================================
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(function(dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
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
    // MENU MOBILE
    // ==========================================
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function(e) {
            e.preventDefault();
            navMenu.classList.toggle('active');
        });
    }
});
