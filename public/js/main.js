// ============================================================
// APTUS - MAIN.JS
// Utilitários gerais carregados no rodapé.
// A navbar (perfil, dropdowns, hamburger) fica só em nav.js;
// registrar os mesmos eventos aqui fazia os menus abrirem e
// fecharem no mesmo clique.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // FLASH MESSAGES AUTO-FECHAR
    // ==========================================
    document.querySelectorAll('.flash-sucesso, .flash-erro, .flash-aviso').forEach(function (msg) {
        // erros ficam mais tempo na tela para dar tempo de ler
        var tempo = msg.classList.contains('flash-erro') ? 8000 : 5000;
        setTimeout(function () {
            msg.style.overflow = 'hidden';
            msg.style.maxHeight = msg.offsetHeight + 'px';
            msg.style.transition = 'opacity 0.4s ease, max-height 0.4s ease 0.3s, margin 0.4s ease 0.3s, padding 0.4s ease 0.3s';
            requestAnimationFrame(function () {
                msg.style.opacity = '0';
                msg.style.maxHeight = '0';
                msg.style.marginTop = '0';
                msg.style.marginBottom = '0';
                msg.style.paddingTop = '0';
                msg.style.paddingBottom = '0';
            });
            setTimeout(function () { msg.style.display = 'none'; }, 800);
        }, tempo);
    });
});
