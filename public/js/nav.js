// public/js/nav.js
// Único responsável pelo comportamento da navbar (menu de perfil,
// dropdowns e menu hamburger). Não registre esses mesmos eventos em
// outro arquivo: dois "toggle" no mesmo clique abrem e fecham o menu
// ao mesmo tempo, e ele parece não funcionar.

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var body = document.body;
    var btnPerfil = document.getElementById('btnPerfilNavbar');
    var menuPerfil = document.getElementById('menuPerfilNavbar');
    var hamburger = document.getElementById('hamburger');
    var navLinks = document.querySelector('.navbar-links');
    var dropdowns = document.querySelectorAll('.navbar .dropdown');

    function fecharPerfil() {
        if (menuPerfil) menuPerfil.classList.remove('ativo');
        if (btnPerfil) btnPerfil.setAttribute('aria-expanded', 'false');
    }

    function fecharDropdowns(exceto) {
        dropdowns.forEach(function (d) {
            if (d !== exceto) {
                d.classList.remove('open');
                var t = d.querySelector('.dropdown-toggle');
                if (t) t.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function fecharHamburger() {
        if (navLinks && navLinks.classList.contains('ativo')) {
            navLinks.classList.remove('ativo');
            body.style.overflow = '';
        }
        if (hamburger) hamburger.setAttribute('aria-expanded', 'false');
    }

    // ==========================================
    // MENU DE PERFIL
    // ==========================================
    if (btnPerfil && menuPerfil) {
        btnPerfil.type = 'button';
        btnPerfil.setAttribute('aria-haspopup', 'true');
        btnPerfil.setAttribute('aria-expanded', 'false');
        btnPerfil.setAttribute('aria-controls', 'menuPerfilNavbar');

        btnPerfil.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var abrir = !menuPerfil.classList.contains('ativo');
            fecharDropdowns();
            menuPerfil.classList.toggle('ativo', abrir);
            btnPerfil.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        });

        menuPerfil.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // ==========================================
    // DROPDOWNS (Anunciar / Admin)
    // ==========================================
    dropdowns.forEach(function (dropdown) {
        var toggle = dropdown.querySelector('.dropdown-toggle');
        if (!toggle) return;
        toggle.setAttribute('aria-haspopup', 'true');
        toggle.setAttribute('aria-expanded', 'false');

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var abrir = !dropdown.classList.contains('open');
            fecharDropdowns(dropdown);
            fecharPerfil();
            dropdown.classList.toggle('open', abrir);
            toggle.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        });
    });

    // ==========================================
    // MENU HAMBURGER (telas menores)
    // ==========================================
    if (hamburger && navLinks) {
        hamburger.setAttribute('role', 'button');
        hamburger.setAttribute('tabindex', '0');
        hamburger.setAttribute('aria-label', 'Abrir menu');
        hamburger.setAttribute('aria-expanded', 'false');

        var alternarHamburger = function (e) {
            e.preventDefault();
            e.stopPropagation();
            var abrir = !navLinks.classList.contains('ativo');
            navLinks.classList.toggle('ativo', abrir);
            body.style.overflow = abrir ? 'hidden' : '';
            hamburger.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        };

        hamburger.addEventListener('click', alternarHamburger);
        hamburger.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') alternarHamburger(e);
        });

        // Clicar em um link de navegação fecha o menu (exceto os que abrem dropdown).
        navLinks.querySelectorAll('a:not(.dropdown-toggle)').forEach(function (link) {
            link.addEventListener('click', fecharHamburger);
        });
    }

    // ==========================================
    // FECHAR AO CLICAR FORA / ESC
    // ==========================================
    document.addEventListener('click', function (e) {
        if (menuPerfil && btnPerfil && !menuPerfil.contains(e.target) && !btnPerfil.contains(e.target)) {
            fecharPerfil();
        }
        dropdowns.forEach(function (d) {
            if (!d.contains(e.target)) d.classList.remove('open');
        });
        if (navLinks && hamburger && !navLinks.contains(e.target) && !hamburger.contains(e.target)) {
            fecharHamburger();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            fecharPerfil();
            fecharDropdowns();
            fecharHamburger();
        }
    });
});
