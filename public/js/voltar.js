/* public/js/voltar.js
 * Seta "Voltar" global: leva para a página anterior que o usuário visitou
 * nesta aba, exatamente como ela estava (com filtros/categoria/página da URL).
 *
 * Mantém uma pilha de URLs no sessionStorage (por aba):
 *  - recarregar a mesma página não empilha de novo;
 *  - chegar na penúltima URL (seta ou botão do navegador) desempilha;
 *  - qualquer outra navegação empilha.
 * Sem pilha (aba nova, link externo), cai no history.back() ou na home.
 */
(function () {
    'use strict';

    var CHAVE = 'aptus_historico';
    var LIMITE = 50;
    var HOME = window.APTUS_BASE + '/';
    var atual = location.pathname + location.search;

    function ler() {
        try {
            var v = JSON.parse(sessionStorage.getItem(CHAVE) || '[]');
            return Array.isArray(v) ? v : [];
        } catch (e) { return []; }
    }

    function gravar(pilha) {
        try { sessionStorage.setItem(CHAVE, JSON.stringify(pilha.slice(-LIMITE))); } catch (e) { /* sem storage */ }
    }

    // telas de autenticação não entram no histórico (não voltar para o login)
    var TRANSITORIA = /^\/Aptus\/(login|logout|cadastrar|cadastro|verificar|esqueci-senha|redefinir-senha)(\/|$)/i;

    var pilha = ler();
    var n = pilha.length;
    if (TRANSITORIA.test(location.pathname)) {
        // não empilha
    } else if (n && pilha[n - 1] === atual) {
        // recarregou ou voltou de um POST que redirecionou para a mesma página
    } else if (n > 1 && pilha[n - 2] === atual) {
        pilha.pop();
    } else {
        pilha.push(atual);
    }
    gravar(pilha);

    function destino() {
        var p = ler();
        if (!p.length) return null;
        if (p[p.length - 1] !== atual) return p[p.length - 1];
        return p.length > 1 ? p[p.length - 2] : null;
    }

    function referrerInterno() {
        try {
            return document.referrer && new URL(document.referrer).origin === location.origin;
        } catch (e) { return false; }
    }

    // Insere a seta no topo do container principal da página, para ela
    // acompanhar o layout (largura, margens e alinhamento) de cada tela.
    var WRAPPERS = /^(DIV|MAIN|SECTION|ARTICLE|HEADER)$/;
    var IGNORAR = /^(SCRIPT|STYLE|NOSCRIPT|TEMPLATE|LINK|META|NAV|FOOTER|ASIDE|DIALOG)$/;

    function visivel(el) {
        var cs = getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden') return false;
        if (cs.position === 'fixed' || cs.position === 'absolute' || cs.position === 'sticky') return false;
        return el.offsetHeight > 0;
    }

    function ehLinha(el) {
        var cs = getComputedStyle(el);
        if (cs.display.indexOf('grid') !== -1) return true;
        return cs.display.indexOf('flex') !== -1 && cs.flexDirection.indexOf('row') === 0;
    }

    function primeiroFilho(el) {
        for (var c = el.firstElementChild; c; c = c.nextElementSibling) {
            if (IGNORAR.test(c.tagName) || !visivel(c)) continue;
            if (/alert|flash|toast|cookie|modal|vlibras/i.test(c.className + ' ' + c.id)) continue;
            return c;
        }
        return null;
    }

    function inserir() {
        if (document.querySelector('.voltar-wrap')) return;
        var wrap = document.createElement('div');
        wrap.className = 'voltar-wrap';
        wrap.innerHTML = '<a href="' + HOME + '" class="btn-voltar-global" data-voltar ' +
            'aria-label="Voltar para a página anterior"><i class="fas fa-arrow-left" aria-hidden="true"></i>' +
            '<span>Voltar</span></a>';

        var slot = document.querySelector('[data-voltar-slot]');
        if (slot) { slot.appendChild(wrap); return; }

        var alvo = primeiroFilho(document.body);
        if (!alvo) return;
        // desce pelos wrappers (main > .container > ...) até o container com conteúdo
        for (var i = 0; i < 4; i++) {
            if (ehLinha(alvo)) break;
            var filho = primeiroFilho(alvo);
            if (!filho || !WRAPPERS.test(filho.tagName)) break;
            // só desce se o filho não for um bloco "cartão" estreito lado a lado
            if (filho.offsetWidth < alvo.clientWidth * 0.6) break;
            alvo = filho;
        }

        if (ehLinha(alvo) || !WRAPPERS.test(alvo.tagName)) {
            // linha flex/grid ou elemento solto (h1, p, form): entra antes dele
            alvo.parentNode.insertBefore(wrap, alvo);
        } else {
            alvo.insertBefore(wrap, primeiroFilho(alvo));
        }
        ajustar(wrap, alvo);
    }

    function ajustar(wrap, alvo) {
        // Em blocos de largura total (hero, body), alinha com o container padrão
        if (window.innerWidth > 1240 && wrap.offsetWidth > window.innerWidth * 0.92) {
            wrap.classList.add('voltar-wrap--largo');
        }
        // Se caiu atrás da navbar fixa: desce a seta para logo abaixo dela, sempre
        // no fluxo (nunca por cima de texto), e devolve esse espaço tirando-o do
        // padding-top do bloco seguinte, para a página manter o mesmo respiro.
        var nav = document.querySelector('nav.navbar');
        var navFundo = nav ? nav.getBoundingClientRect().bottom : 0;
        var topo = wrap.getBoundingClientRect().top;
        if (topo < navFundo + 8) {
            var desloc = Math.ceil(navFundo + 12 - topo);
            wrap.style.marginTop = desloc + 'px';
            if (wrap.nextElementSibling === alvo) {
                var ocupado = desloc + wrap.offsetHeight
                    + (parseFloat(getComputedStyle(wrap).marginBottom) || 0);
                var pt = parseFloat(getComputedStyle(alvo).paddingTop) || 0;
                alvo.style.paddingTop = Math.max(16, pt - ocupado) + 'px';
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inserir);
    } else {
        inserir();
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('[data-voltar]') : null;
        if (!btn) return;
        e.preventDefault();

        var alvo = destino();
        if (alvo) {
            location.href = alvo;
        } else if (referrerInterno() && history.length > 1) {
            history.back();
        } else {
            location.href = HOME;
        }
    });
})();
