/* public/js/ver-mais.js
 * Botão "Ver mais": carrega o próximo lote de itens sem recarregar a página.
 *   <button data-ver-mais data-url="/Aptus/anuncios?parcial=1" data-alvo=".grid" data-pagina="1">
 * O servidor devolve só o HTML dos itens e o cabeçalho X-Tem-Mais: 1|0.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('[data-ver-mais]') : null;
        if (!btn || btn.disabled) return;

        var alvo = document.querySelector(btn.getAttribute('data-alvo'));
        if (!alvo) return;

        var proxima = (parseInt(btn.getAttribute('data-pagina'), 10) || 1) + 1;
        var url = btn.getAttribute('data-url');
        url += (url.indexOf('?') === -1 ? '?' : '&') + 'pagina=' + proxima;

        var textoOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Carregando...';

        fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text().then(function (html) {
                    return { html: html, temMais: r.headers.get('X-Tem-Mais') === '1' };
                });
            })
            .then(function (res) {
                var tmp = document.createElement('div');
                tmp.innerHTML = res.html;
                var primeiro = null;
                while (tmp.firstChild) {
                    var no = tmp.firstChild;
                    if (!primeiro && no.nodeType === 1) primeiro = no;
                    alvo.appendChild(no);
                }
                btn.setAttribute('data-pagina', String(proxima));
                if (!res.temMais) {
                    btn.parentNode.removeChild(btn);
                    return;
                }
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
                btn.innerHTML = textoOriginal;
                // leva o foco ao primeiro item novo (acessibilidade)
                if (primeiro) {
                    var link = primeiro.querySelector('a[href]:not([tabindex="-1"])');
                    if (link) link.focus({ preventScroll: true });
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
                btn.innerHTML = 'Erro ao carregar. Tentar de novo';
            });
    });
})();
