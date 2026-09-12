// public/js/masks.js
// Máscaras e normalização de campos do Aptus.
// Aplicado automaticamente por name/id. Sem dependências externas.

(function () {
    'use strict';

    // ============================================================
    // Helpers
    // ============================================================
    function onlyDigits(str) {
        return String(str || '').replace(/\D/g, '');
    }

    function toIsoDate(str) {
        // Aceita 01012000, 01/01/2000, 2000-01-01
        var d = onlyDigits(str);
        if (d.length !== 8) return str;
        return d.slice(4, 8) + '-' + d.slice(2, 4) + '-' + d.slice(0, 2);
    }

    function toBrDate(str) {
        // Aceita 2000-01-01
        var m = String(str || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return str;
        return m[3] + '/' + m[2] + '/' + m[1];
    }

    // ============================================================
    // MONEY — formata no blur, normaliza no submit
    // Aceita: "1500", "1500.50", "1.500,50", "R$ 1500,00", "R$1500"
    // ============================================================
    function parseMoney(v) {
        var s = String(v || '').trim()
            .replace(/R\$\s?/gi, '')
            .replace(/\s/g, '');
        if (s === '') return 0;

        // Se tem vírgula, é formato BR: 1.500,50
        if (s.indexOf(',') !== -1) {
            s = s.replace(/\./g, '').replace(',', '.');
        }
        var n = parseFloat(s);
        return isNaN(n) ? 0 : n;
    }

    function formatMoney(n) {
        n = parseFloat(n) || 0;
        var parts = n.toFixed(2).split('.');
        var inteiro = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'R$ ' + inteiro + ',' + parts[1];
    }

    function setupMoney(input) {
        // Formata o valor inicial (se já vier preenchido do banco)
        if (input.value && input.value.trim() !== '') {
            var inicial = parseMoney(input.value);
            input.value = inicial > 0 ? formatMoney(inicial) : '';
        }

        // Aceita apenas dígitos e separadores enquanto digita
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^\d.,]/g, '');
        });

        // Ao perder o foco, formata bonitinho
        input.addEventListener('blur', function () {
            var n = parseMoney(this.value);
            this.value = n > 0 ? formatMoney(n) : '';
        });

        // No submit, manda o valor puro tipo "1500.00"
        var form = input.closest('form');
        if (form && !form.dataset.maskBound) {
            form.dataset.maskBound = '1';
            form.addEventListener('submit', function () {
                form.querySelectorAll('[data-mask="money"]').forEach(function (el) {
                    var n = parseMoney(el.value);
                    el.value = n > 0 ? n.toFixed(2) : '';
                });
            }, true);
        }
    }

    // ============================================================
    // PHONE — (11) 98765-4321 / (11) 3456-7890
    // ============================================================
    function maskPhone(v) {
        var d = onlyDigits(v).slice(0, 11);
        if (!d) return '';
        if (d.length <= 2) return '(' + d;
        if (d.length <= 6) return '(' + d.slice(0, 2) + ') ' + d.slice(2);
        if (d.length <= 10) return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
        return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
    }

    function setupPhone(input) {
        if (input.value) input.value = maskPhone(input.value);
        input.addEventListener('input', function () {
            this.value = maskPhone(this.value);
        });
    }

    // ============================================================
    // CPF / CNPJ
    // ============================================================
    function maskCpf(d) {
        d = d.slice(0, 11);
        var out = d.slice(0, 3);
        if (d.length > 3) out += '.' + d.slice(3, 6);
        if (d.length > 6) out += '.' + d.slice(6, 9);
        if (d.length > 9) out += '-' + d.slice(9, 11);
        return out;
    }

    function maskCnpj(d) {
        d = d.slice(0, 14);
        var out = d.slice(0, 2);
        if (d.length > 2) out += '.' + d.slice(2, 5);
        if (d.length > 5) out += '.' + d.slice(5, 8);
        if (d.length > 8) out += '/' + d.slice(8, 12);
        if (d.length > 12) out += '-' + d.slice(12, 14);
        return out;
    }

    function maskCpfCnpj(v) {
        var d = onlyDigits(v).slice(0, 14);
        return d.length <= 11 ? maskCpf(d) : maskCnpj(d);
    }

    function setupCpfCnpj(input) {
        if (input.value) input.value = maskCpfCnpj(input.value);
        input.addEventListener('input', function () {
            this.value = maskCpfCnpj(this.value);
        });
    }

    // ============================================================
    // CEP — 00000-000
    // ============================================================
    function maskCep(v) {
        var d = onlyDigits(v).slice(0, 8);
        if (d.length <= 5) return d;
        return d.slice(0, 5) + '-' + d.slice(5);
    }

    function setupCep(input) {
        if (input.value) input.value = maskCep(input.value);
        input.addEventListener('input', function () {
            this.value = maskCep(this.value);
        });
    }

    // ============================================================
    // DATA — dd/mm/aaaa <-> aaaa-mm-dd (hidden)
    // ============================================================
    function maskDate(v) {
        var d = onlyDigits(v).slice(0, 8);
        var out = d.slice(0, 2);
        if (d.length > 2) out += '/' + d.slice(2, 4);
        if (d.length > 4) out += '/' + d.slice(4, 8);
        return out;
    }

    function setupDate(input) {
        if (input.value) {
            // Se já está em formato ISO (2000-01-01), mostra BR
            if (/^\d{4}-\d{2}-\d{2}/.test(input.value)) {
                input.value = toBrDate(input.value);
            } else {
                input.value = maskDate(input.value);
            }
        }
        input.addEventListener('input', function () {
            this.value = maskDate(this.value);
        });

        // No submit, converte para ISO
        var form = input.closest('form');
        if (form && !form.dataset.dateBound) {
            form.dataset.dateBound = '1';
            form.addEventListener('submit', function () {
                form.querySelectorAll('[data-mask="date"]').forEach(function (el) {
                    el.value = toIsoDate(el.value);
                });
            }, true);
        }
    }

    // ============================================================
    // NUMBER puro (só dígitos, com min/max)
    // ============================================================
    function setupNumber(input) {
        input.addEventListener('input', function () {
            this.value = onlyDigits(this.value);
        });
    }

    // ============================================================
    // AUTO-APLICAÇÃO por name e/ou data-mask
    // ============================================================
    var regras = {
        money: {
            names: ['preco', 'valor', 'preco_min', 'preco_max', 'valor_informado_contratante', 'valor_informado_freelancer'],
            setup: setupMoney,
        },
        phone: {
            names: ['telefone', 'whatsapp', 'site_telefone'],
            setup: setupPhone,
        },
        cpf_cnpj: {
            names: ['cpf_cnpj', 'cpf', 'cnpj'],
            setup: setupCpfCnpj,
        },
        cep: {
            names: ['cep'],
            setup: setupCep,
        },
        date: {
            names: ['data_nascimento'],
            setup: setupDate,
        },
    };

    function aplicar(el, tipo) {
        if (!el || el.dataset.maskApplied === '1') return;
        var regra = regras[tipo];
        if (!regra) return;
        if (el.type === 'number' && tipo !== 'number') {
            // Converte pra texto antes de aplicar (money, phone, etc.)
            el.type = 'text';
        }
        el.dataset.maskApplied = '1';
        el.dataset.mask = tipo;
        regra.setup(el);
    }

    function init() {
        // 1) Explicitamente marcados com data-mask
        document.querySelectorAll('[data-mask]').forEach(function (el) {
            aplicar(el, el.dataset.mask);
        });

        // 2) Auto por name
        Object.keys(regras).forEach(function (tipo) {
            regras[tipo].names.forEach(function (nome) {
                document.querySelectorAll('input[name="' + nome + '"]').forEach(function (el) {
                    if (!el.dataset.mask) aplicar(el, tipo);
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expõe utilitários para uso avulso
    window.AptusMasks = {
        init: init,
        parseMoney: parseMoney,
        formatMoney: formatMoney,
        maskPhone: maskPhone,
        maskCpfCnpj: maskCpfCnpj,
        maskCep: maskCep,
        maskDate: maskDate,
    };
})();