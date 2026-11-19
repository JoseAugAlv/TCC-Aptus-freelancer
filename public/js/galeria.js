// public/js/galeria.js
// Visualizador de fotos do anúncio em tela cheia.
// - Clique na foto principal ou em qualquer miniatura abre a foto grande.
// - Setas na tela, teclado (← → Esc) e deslizar no celular trocam a foto.
(function () {
    'use strict';

    var fotos = [];
    var atual = 0;
    var caixa, img, contador, ultimoFoco;

    function coletarFotos() {
        var lista = Array.prototype.map.call(
            document.querySelectorAll('.galeria-anuncio [data-foto]'),
            function (b) { return b.getAttribute('data-foto'); }
        );
        var principal = document.getElementById('fotoPrincipal');
        if (!lista.length && principal) lista = [principal.getAttribute('src')];
        return lista;
    }

    function montar() {
        caixa = document.createElement('div');
        caixao.className = 'galeria-visualizador';
        caixa.setAttribute('role', 'dialog');
        caixa.setAttribute('aria-modal', 'true');
        caixa.setAttribute('aria-label', 'Fotos do serviço');
        caixa.hidden = true;
        caixa.innerHTML =
            '<button type="button" class="gv-fechar" aria-label="Fechar"><i class="fas fa-times" aria-hidden="true"></i></button>' +
            '<button type="button" class="gv-nav gv-anterior" aria-label="Foto anterior"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>' +
            '<figure class="gv-figura"><img alt="Foto do serviço ampliada"></figure>' +
            '<button type="button" class="gv-nav gv-proxima" aria-label="Próxima foto"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>' +
            '<p class="gv-contador" aria-live="polite"></p>';
        document.body.appendChild(caixa);

        img = caixa.querySelector('img');
        contador = caixa.querySelector('.gv-contador');

        caixa.querySelector('.gv-fechar').addEventListener('click', fechar);
        caixa.querySelector('.gv-anterior').addEventListener('click', function () { mostrar(atual - 1); });
        caixa.querySelector('.gv-proxima').addEventListener('click', function () { mostrar(atual + 1); });
        // Clique no fundo escuro (fora da foto e dos botões) fecha
        caixa.addEventListener('click', function (e) {
            if (e.target === caixa || e.target.classList.contains('gv-figura')) fechar();
        });

        // Deslizar no celular
        var inicioX = null;
        caixa.addEventListener('touchstart', function (e) { inicioX = e.touches[0].clientX; }, { passive: true });
        caixa.addEventListener('touchend', function (e) {
            if (inicioX === null) return;
            var dx = e.changedTouches[0].clientX - inicioX;
            if (Math.abs(dx) > 50) mostrar(atual + (dx < 0 ? 1 : -1));
            inicioX = null;
        });
    }

    function mostrar(i) {
        if (!fotos.length) return;
        atual = (i + fotos.length) % fotos.length;
        img.src = fotos[atual];
        contador.textContent = fotos.length > 1 ? (atual + 1) + ' de ' + fotos.length : '';
        caixa.classList.toggle('gv-unica', fotos.length < 2);
    }

    function abrir(i) {
        fotos = coletarFotos();
        if (!fotos.length) return;
        if (!caixa) montar();
        ultimoFoco = document.activeElement;
        caixa.hidden = false;
        document.documentElement.classList.add('gv-aberto');
        mostrar(i);
        caixa.querySelector('.gv-fechar').focus();
    }

    function fechar() {
        if (!caixa || caixa.hidden) return;
        caixa.hidden = true;
        document.documentElement.classList.remove('gv-aberto');
        if (ultimoFoco && ultimoFoco.focus) ultimoFoco.focus();
    }

    document.addEventListener('click', function (e) {
        var mini = e.target.closest('.galeria-anuncio [data-foto]');
        if (mini) {
            var todas = Array.prototype.slice.call(document.querySelectorAll('.galeria-anuncio [data-foto]'));
            document.querySelectorAll('.galeria-anuncio .ativa').forEach(function (x) { x.classList.remove('ativa'); });
            mini.classList.add('ativa');
            var principal = document.getElementById('fotoPrincipal');
            if (principal) principal.src = mini.getAttribute('data-foto');
            abrir(todas.indexOf(mini));
            return;
        }
        if (e.target.closest('[data-abrir-galeria]')) {
            var principalSrc = (document.getElementById('fotoPrincipal') || {}).src || '';
            var lista = coletarFotos();
            var idx = lista.findIndex(function (u) { return principalSrc.indexOf(u) !== -1 || u.indexOf(principalSrc) !== -1; });
            abrir(idx >= 0 ? idx : 0);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (!caixa || caixa.hidden) return;
        if (e.key === 'Escape') fechar();
        else if (e.key === 'ArrowLeft') mostrar(atual - 1);
        else if (e.key === 'ArrowRight') mostrar(atual + 1);
        else if (e.key === 'Tab') {
            // Mantém o foco dentro do visualizador
            var focaveis = caixa.querySelectorAll('button:not([hidden])');
            var primeiro = focaveis[0], ultimo = focaveis[focaveis.length - 1];
            if (e.shiftKey && document.activeElement === primeiro) { e.preventDefault(); ultimo.focus(); }
            else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primeiro.focus(); }
        }
    });
})();
