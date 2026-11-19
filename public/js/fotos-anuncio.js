/* public/js/fotos-anuncio.js
 * Gerenciador de fotos do anúncio (views/anuncios/_fotos.php):
 *  - acumula várias seleções no mesmo <input type="file" multiple>;
 *  - mostra a prévia de cada foto nova, com opção de capa e de remover;
 *  - garante que sempre exista uma capa marcada quando houver fotos.
 * Valor do rádio "capa": token da foto salva, ou "nova_N" (posição no input).
 */
(function () {
    'use strict';

    var box = document.querySelector('[data-fotos-anuncio]');
    if (!box) return;

    var input = box.querySelector('input[type="file"]');
    var grid = box.querySelector('[data-fotos-grid]');
    var aviso = box.querySelector('[data-fotos-aviso]');
    var max = parseInt(box.getAttribute('data-max'), 10) || 8;
    var novas = [];
    var podeAcumular = typeof DataTransfer !== 'undefined';

    function existentesAtivas() {
        return grid.querySelectorAll('.foto-item:not(.nova):not(.removida)').length;
    }

    function garantirCapa() {
        var marcada = grid.querySelector('.foto-item:not(.removida) input[name="capa"]:checked');
        if (marcada) return;
        var primeira = grid.querySelector('.foto-item:not(.removida) input[name="capa"]');
        if (primeira) primeira.checked = true;
    }

    function sincronizarInput() {
        if (!podeAcumular) return;
        var dt = new DataTransfer();
        novas.forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
    }

    function desenharNovas() {
        var capaEscolhida = (grid.querySelector('input[name="capa"]:checked') || {}).value || '';
        Array.prototype.forEach.call(grid.querySelectorAll('.foto-item.nova'), function (el) { el.remove(); });

        novas.forEach(function (arquivo, i) {
            var item = document.createElement('div');
            item.className = 'foto-item nova';
            var img = document.createElement('img');
            img.alt = 'Nova foto';
            img.src = URL.createObjectURL(arquivo);
            img.onload = function () { URL.revokeObjectURL(img.src); };

            var capa = document.createElement('label');
            capa.className = 'foto-capa-opcao';
            capa.innerHTML = '<input type="radio" name="capa" value="nova_' + i + '"><span>Capa</span>';
            if (capaEscolhida === 'nova_' + i) capa.querySelector('input').checked = true;

            var remover = document.createElement('button');
            remover.type = 'button';
            remover.className = 'foto-remover-opcao';
            remover.title = 'Tirar esta foto';
            remover.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i><span class="sr-only">Tirar foto</span>';
            remover.addEventListener('click', function () {
                novas.splice(i, 1);
                sincronizarInput();
                desenharNovas();
            });

            var selo = document.createElement('span');
            selo.className = 'foto-selo-nova';
            selo.textContent = 'Nova';

            item.appendChild(img);
            item.appendChild(selo);
            item.appendChild(capa);
            item.appendChild(remover);
            grid.appendChild(item);
        });
        garantirCapa();
    }

    input.addEventListener('change', function () {
        var escolhidas = Array.prototype.slice.call(input.files || []);
        if (!podeAcumular) novas = [];
        var livres = max - existentesAtivas() - novas.length;
        if (escolhidas.length > livres) {
            aviso.textContent = 'Limite de ' + max + ' fotos: ' + Math.max(0, livres) + ' foto(s) adicionada(s).';
            escolhidas = escolhidas.slice(0, Math.max(0, livres));
        } else {
            aviso.textContent = '';
        }
        novas = novas.concat(escolhidas);
        sincronizarInput();
        desenharNovas();
    });

    // Fotos salvas marcadas para remoção não podem ser a capa
    grid.addEventListener('change', function (e) {
        if (!e.target.matches('[data-remover]')) return;
        var item = e.target.closest('.foto-item');
        item.classList.toggle('removida', e.target.checked);
        var radio = item.querySelector('input[name="capa"]');
        if (e.target.checked && radio) {
            radio.checked = false;
            radio.disabled = true;
        } else if (radio) {
            radio.disabled = false;
        }
        garantirCapa();
    });

    garantirCapa();
})();
