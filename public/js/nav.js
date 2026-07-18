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
    
    // Fechar sidebar ao clicar em um link
    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                sidebar.classList.remove('active');
                body.style.overflow = '';
            });
        });
    }
    
    // Fechar sidebar ao clicar fora
    document.addEventListener('click', function(e) {
        if (sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && !openBtn.contains(e.target)) {
                sidebar.classList.remove('active');
                body.style.overflow = '';
            }
        }
    });
    
    // ==========================================
    // DROPDOWN
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
    // MENU MOBILE
    // ==========================================
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
});