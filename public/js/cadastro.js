/* public/js/cadastro.js
 * Validação em tempo real no cadastro/perfil: nome de usuário disponível,
 * CPF válido (dígitos verificadores) e idade mínima de 18 anos.
 * O servidor refaz todas as validações; aqui é só para ajudar quem preenche.
 */
(function () {
    'use strict';

    function digitos(v) { return String(v || '').replace(/\D/g, ''); }

    function marcar(el, statusEl, ok, texto) {
        if (statusEl) {
            statusEl.textContent = texto || '';
            statusEl.className = statusEl.className.replace(/\s*(ok|erro)\b/g, '') + (texto ? (ok ? ' ok' : ' erro') : '');
        }
        el.setCustomValidity(ok || !texto ? '' : texto);
    }

    function cpfValido(cpf) {
        cpf = digitos(cpf);
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        for (var t = 9; t < 11; t++) {
            var soma = 0;
            for (var i = 0; i < t; i++) soma += parseInt(cpf[i], 10) * (t + 1 - i);
            if (parseInt(cpf[t], 10) !== ((10 * soma) % 11) % 10) return false;
        }
        return true;
    }

    function idade(dataBr) {
        var m = String(dataBr || '').match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!m) return null;
        var d = new Date(+m[3], +m[2] - 1, +m[1]);
        if (d.getFullYear() !== +m[3] || d.getMonth() !== +m[2] - 1 || d.getDate() !== +m[1]) return null;
        var hoje = new Date();
        if (d > hoje) return null;
        var anos = hoje.getFullYear() - d.getFullYear();
        if (hoje.getMonth() < d.getMonth() || (hoje.getMonth() === d.getMonth() && hoje.getDate() < d.getDate())) anos--;
        return anos;
    }

    // ---- nome de usuário ----
    var user = document.querySelector('[data-username-check]');
    if (user) {
        var userStatus = document.getElementById('usernameStatus');
        var atual = (user.getAttribute('data-atual') || '').toLowerCase();
        var timer = null;
        user.addEventListener('input', function () {
            user.value = user.value.toLowerCase().replace(/^@/, '').replace(/[^a-z0-9._]/g, '');
            clearTimeout(timer);
            var v = user.value;
            if (v.length < 3) { marcar(user, userStatus, false, v ? 'Use pelo menos 3 caracteres.' : ''); return; }
            if (v === atual) { marcar(user, userStatus, true, 'Este é o seu nome de usuário atual.'); return; }
            timer = setTimeout(function () {
                fetch(window.APTUS_BASE + '/usuarios/username-disponivel?u=' + encodeURIComponent(v), { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (user.value !== v) return;
                        if (!res.valido) marcar(user, userStatus, false, 'Formato inválido: letras minúsculas, números, ponto ou sublinhado.');
                        else if (!res.disponivel) marcar(user, userStatus, false, '@' + v + ' já está em uso.');
                        else marcar(user, userStatus, true, '@' + v + ' está disponível.');
                    })
                    .catch(function () { marcar(user, userStatus, true, ''); });
            }, 350);
        });
    }

    // ---- CPF ----
    var cpf = document.querySelector('[data-cpf-check]');
    if (cpf) {
        var cpfStatus = document.querySelector('[data-cpf-status]');
        var checarCpf = function () {
            var d = digitos(cpf.value);
            if (!d) { marcar(cpf, cpfStatus, true, ''); return; }
            if (d.length < 11) { marcar(cpf, cpfStatus, false, 'CPF incompleto.'); return; }
            marcar(cpf, cpfStatus, cpfValido(d), cpfValido(d) ? 'CPF válido.' : 'CPF inválido. Confira os números.');
        };
        cpf.addEventListener('blur', checarCpf);
        cpf.addEventListener('input', function () { if (digitos(cpf.value).length === 11) checarCpf(); });
    }

    // ---- data de nascimento / maioridade ----
    var nasc = document.querySelector('[data-idade-check]');
    if (nasc) {
        var nascStatus = document.querySelector('[data-idade-status]');
        var checarIdade = function () {
            if (!nasc.value) { marcar(nasc, nascStatus, true, ''); return; }
            var anos = idade(nasc.value);
            if (anos === null) marcar(nasc, nascStatus, false, 'Data inválida. Use dd/mm/aaaa.');
            else if (anos < 18) marcar(nasc, nascStatus, false, 'É preciso ter 18 anos ou mais.');
            else marcar(nasc, nascStatus, true, '');
        };
        nasc.addEventListener('blur', checarIdade);
        nasc.addEventListener('input', function () { if (nasc.value.length === 10) checarIdade(); });
    }
})();
