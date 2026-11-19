// public/js/botao-carregando.js
// Mostra uma bolinha girando dentro do botão enquanto a próxima página carrega
// (envio de formulário, envio de e-mail, links com cara de botão).
//
// - Formulários tratados via AJAX (preventDefault) não são afetados.
// - O botão NÃO recebe "disabled": assim o name/value dele continua indo no POST.
//   O duplo envio é bloqueado por uma marcação no próprio formulário.
// - Para desligar em um botão/link/form específico: data-sem-carregamento.
(function () {
    'use strict';

    var CLASSE = 'btn-carregando';
    var ATIVOS = [];

    function jaTemSpinner(el) {
        return !!el.querySelector('.fa-spin, .fa-spinner');
    }

    function ativar(el) {
        if (!el || el.classList.contains(CLASSE) || jaTemSpinner(el)) return;
        if (el.closest('[data-sem-carregamento]')) return;

        var estilo = getComputedStyle(el);
        el.style.setProperty('--btn-carregando-cor', estilo.color);
        if (estilo.position === 'static') el.dataset.carregandoPos = '1';

        el.classList.add(CLASSE);
        el.setAttribute('aria-busy', 'true');
        ATIVOS.push(el);
    }

    function limparTudo() {
        ATIVOS.forEach(function (el) {
            el.classList.remove(CLASSE);
            el.removeAttribute('aria-busy');
            delete el.dataset.carregandoPos;
        });
        ATIVOS = [];
        document.querySelectorAll('form[data-enviando]').forEach(function (f) {
            delete f.dataset.enviando;
        });
    }

    function abreEmOutraJanela(alvo) {
        return alvo && alvo !== '_self' && alvo !== '_parent' && alvo !== '_top';
    }

    // ---------- Formulários ----------
    // Captura: bloqueia o segundo clique antes de qualquer outro handler.
    document.addEventListener('submit', function (e) {
        if (e.target.dataset && e.target.dataset.enviando) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        var botao = e.submitter
            || form.querySelector('button[type="submit"], button:not([type])');

        // Espera os outros handlers decidirem (validação, AJAX, confirmação).
        setTimeout(function () {
            if (e.defaultPrevented) return;
            if (form.hasAttribute('data-sem-carregamento')) return;

            var alvo = (botao && botao.getAttribute('formtarget')) || form.getAttribute('target');
            if (abreEmOutraJanela(alvo)) return;

            form.dataset.enviando = '1';
            if (botao && botao.tagName === 'BUTTON') ativar(botao);
        }, 0);
    });

    // ---------- Links com cara de botão ----------
    document.addEventListener('click', function (e) {
        if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

        var link = e.target.closest && e.target.closest('a[href]');
        if (!link) return;
        if (!/(^|[\s-])btn([\s-]|$)/.test(link.className)) return;
        if (link.closest('nav, .navbar, .dropdown-menu')) return;
        if (link.hasAttribute('download') || abreEmOutraJanela(link.getAttribute('target'))) return;

        var href = link.getAttribute('href') || '';
        if (href === '' || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) return;

        var url;
        try { url = new URL(link.href, location.href); } catch (err) { return; }
        if (url.origin !== location.origin) return;
        // Mesmo endereço, só mudando a âncora: não recarrega.
        if (url.hash && url.pathname === location.pathname && url.search === location.search) return;

        setTimeout(function () {
            if (!e.defaultPrevented) ativar(link);
        }, 0);
    });

    // Voltar pelo navegador (cache de página) não pode deixar botão girando.
    window.addEventListener('pageshow', limparTudo);
})();
