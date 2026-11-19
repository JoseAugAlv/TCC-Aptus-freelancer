/* public/js/cep.js
 * Busca de endereço pelo CEP e de CEP pelo endereço (ViaCEP), nos campos de
 * app/Views/partials/endereco.php.
 *  - CEP com 8 dígitos  -> preenche rua, bairro, cidade e UF;
 *  - UF + cidade + rua  -> "Descobrir CEP" lista os CEPs encontrados.
 */
(function () {
    'use strict';

    var API = 'https://viacep.com.br/ws/';

    function digitos(v) { return String(v || '').replace(/\D/g, ''); }

    function iniciar(box) {
        var cep = box.querySelector('[data-cep-input]');
        var uf = box.querySelector('[data-cep-uf]');
        var cidade = box.querySelector('[data-cep-cidade]');
        var rua = box.querySelector('[data-cep-logradouro]');
        var bairro = box.querySelector('[data-cep-bairro]');
        var numero = box.querySelector('[data-cep-numero]');
        var status = box.querySelector('[data-cep-status]');
        var opcoes = box.querySelector('[data-cep-opcoes]');
        var botao = box.querySelector('[data-cep-descobrir]');
        var ultimoCep = digitos(cep.value);

        function aviso(texto, tipo) {
            status.textContent = texto || '';
            status.className = 'cep-status' + (tipo ? ' ' + tipo : '');
        }

        function preencher(d) {
            if (d.cep) cep.value = d.cep;
            if (d.logradouro) rua.value = d.logradouro;
            if (d.bairro) bairro.value = d.bairro;
            if (d.localidade) cidade.value = d.localidade;
            if (d.uf) uf.value = d.uf;
            ultimoCep = digitos(cep.value);
        }

        function buscarPorCep() {
            var d = digitos(cep.value);
            if (d.length !== 8 || d === ultimoCep && rua.value) return;
            ultimoCep = d;
            aviso('Buscando endereço...');
            fetch(API + d + '/json/')
                .then(function (r) { return r.json(); })
                .then(function (dados) {
                    if (dados.erro) { aviso('CEP não encontrado. Preencha o endereço manualmente.', 'erro'); return; }
                    preencher(dados);
                    aviso('Endereço preenchido. Confira e informe o número.', 'ok');
                    if (numero && !numero.value) numero.focus();
                })
                .catch(function () { aviso('Não foi possível consultar o CEP agora. Preencha manualmente.', 'erro'); });
        }

        function descobrirCep() {
            opcoes.hidden = true;
            opcoes.innerHTML = '';
            var u = uf.value, c = cidade.value.trim(), l = rua.value.trim();
            if (!u || c.length < 3 || l.length < 3) {
                aviso('Para descobrir o CEP, informe o estado, a cidade e pelo menos 3 letras da rua.', 'erro');
                return;
            }
            aviso('Procurando CEP...');
            fetch(API + encodeURIComponent(u) + '/' + encodeURIComponent(c) + '/' + encodeURIComponent(l) + '/json/')
                .then(function (r) { return r.json(); })
                .then(function (lista) {
                    if (!Array.isArray(lista) || !lista.length) {
                        aviso('Nenhum CEP encontrado para esse endereço. Confira a grafia da rua e da cidade.', 'erro');
                        return;
                    }
                    if (lista.length === 1) {
                        preencher(lista[0]);
                        aviso('CEP encontrado: ' + lista[0].cep + '.', 'ok');
                        return;
                    }
                    aviso(lista.length + ' CEPs encontrados. Escolha o seu:', 'ok');
                    lista.slice(0, 20).forEach(function (item) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'cep-opcao';
                        b.textContent = item.cep + ' — ' + item.logradouro
                            + (item.complemento ? ' (' + item.complemento + ')' : '')
                            + ', ' + item.bairro;
                        b.addEventListener('click', function () {
                            preencher(item);
                            opcoes.hidden = true;
                            aviso('CEP selecionado: ' + item.cep + '.', 'ok');
                            if (numero && !numero.value) numero.focus();
                        });
                        opcoes.appendChild(b);
                    });
                    opcoes.hidden = false;
                })
                .catch(function () { aviso('Não foi possível consultar agora. Tente de novo em instantes.', 'erro'); });
        }

        cep.addEventListener('input', function () { if (digitos(cep.value).length === 8) buscarPorCep(); });
        cep.addEventListener('blur', buscarPorCep);
        if (botao) botao.addEventListener('click', descobrirCep);
    }

    document.querySelectorAll('[data-endereco]').forEach(iniciar);
})();
