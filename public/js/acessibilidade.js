// public/js/acessibilidade.js
// Painel de acessibilidade da navbar: tema claro/escuro, modos para
// daltonismo e tamanho da fonte. As preferências ficam no localStorage
// do navegador. O header.php aplica o estado salvo antes da página
// renderizar (sem "piscar"); este arquivo cuida do painel e das mudanças.

(function () {
    'use strict';

    var CHAVE = 'aptus_acessibilidade';
    var ESCALAS = [0.875, 1, 1.125, 1.25, 1.5];
    var TEMAS = ['claro', 'escuro'];
    var DALTONISMOS = ['nenhum', 'protanopia', 'deuteranopia', 'tritanopia'];
    var root = document.documentElement;

    function temaDoSistema() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'escuro'
            : 'claro';
    }

    function lerPreferencias() {
        var salvo = {};
        try {
            salvo = JSON.parse(localStorage.getItem(CHAVE) || '{}') || {};
        } catch (e) {
            salvo = {};
        }
        return {
            tema: TEMAS.indexOf(salvo.tema) !== -1 ? salvo.tema : null, // null = segue o sistema
            daltonismo: DALTONISMOS.indexOf(salvo.daltonismo) !== -1 ? salvo.daltonismo : 'nenhum',
            escala: ESCALAS.indexOf(salvo.escala) !== -1 ? salvo.escala : 1
        };
    }

    function salvarPreferencias(prefs) {
        try {
            localStorage.setItem(CHAVE, JSON.stringify(prefs));
        } catch (e) {
            // Navegação privada / armazenamento bloqueado: aplica só nesta página.
        }
    }

    function aplicar(prefs) {
        root.setAttribute('data-tema', prefs.tema || temaDoSistema());
        root.setAttribute('data-daltonismo', prefs.daltonismo);
        root.style.setProperty('--escala-fonte', String(prefs.escala));
    }

    var prefs = lerPreferencias();
    aplicar(prefs);

    // Se o usuário não escolheu um tema, acompanha a mudança do sistema.
    if (window.matchMedia) {
        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        var aoMudarSistema = function () {
            if (!prefs.tema) {
                aplicar(prefs);
                atualizarPainel();
            }
        };
        if (mq.addEventListener) {
            mq.addEventListener('change', aoMudarSistema);
        } else if (mq.addListener) {
            mq.addListener(aoMudarSistema);
        }
    }

    var painel, botao, valorFonte, aviso;

    function anunciar(texto) {
        if (aviso) {
            aviso.textContent = '';
            // Pequeno atraso para leitores de tela repetirem o mesmo texto.
            setTimeout(function () { aviso.textContent = texto; }, 50);
        }
    }

    function atualizarPainel() {
        if (!painel) return;

        var temaAtual = root.getAttribute('data-tema');
        painel.querySelectorAll('[data-acess-tema]').forEach(function (btn) {
            btn.setAttribute('aria-pressed', btn.getAttribute('data-acess-tema') === temaAtual ? 'true' : 'false');
        });

        painel.querySelectorAll('[data-acess-daltonismo]').forEach(function (btn) {
            btn.setAttribute('aria-checked', btn.getAttribute('data-acess-daltonismo') === prefs.daltonismo ? 'true' : 'false');
            btn.tabIndex = btn.getAttribute('data-acess-daltonismo') === prefs.daltonismo ? 0 : -1;
        });

        var indice = ESCALAS.indexOf(prefs.escala);
        if (valorFonte) valorFonte.textContent = (prefs.escala * 100).toLocaleString('pt-BR') + '%';
        var menor = painel.querySelector('[data-acess-fonte="diminuir"]');
        var maior = painel.querySelector('[data-acess-fonte="aumentar"]');
        if (menor) menor.disabled = indice <= 0;
        if (maior) maior.disabled = indice >= ESCALAS.length - 1;
    }

    function mudar(novo, mensagem) {
        prefs = novo;
        aplicar(prefs);
        salvarPreferencias(prefs);
        atualizarPainel();
        if (mensagem) anunciar(mensagem);
    }

    function abrirPainel(focarPrimeiro) {
        painel.hidden = false;
        botao.setAttribute('aria-expanded', 'true');
        // Fecha o menu de perfil para não sobrepor os dois.
        var menuPerfil = document.getElementById('menuPerfilNavbar');
        if (menuPerfil) menuPerfil.classList.remove('ativo');
        // Só move o foco quando aberto pelo teclado (Enter/Espaço).
        if (focarPrimeiro) {
            var primeiro = painel.querySelector('button:not([disabled])');
            if (primeiro) primeiro.focus();
        }
    }

    function fecharPainel(devolverFoco) {
        painel.hidden = true;
        botao.setAttribute('aria-expanded', 'false');
        if (devolverFoco) botao.focus();
    }

    document.addEventListener('DOMContentLoaded', function () {
        painel = document.getElementById('painelAcessibilidade');
        botao = document.getElementById('btnAcessibilidade');
        if (!painel || !botao) return;

        valorFonte = document.getElementById('acessFonteValor');
        aviso = document.getElementById('acessAviso');
        atualizarPainel();

        botao.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (painel.hidden) {
                abrirPainel(e.detail === 0);
            } else {
                fecharPainel(false);
            }
        });

        painel.addEventListener('click', function (e) {
            e.stopPropagation();
            var alvo = e.target.closest('button');
            if (!alvo) return;

            if (alvo.hasAttribute('data-acess-tema')) {
                var tema = alvo.getAttribute('data-acess-tema');
                mudar({ tema: tema, daltonismo: prefs.daltonismo, escala: prefs.escala },
                    'Tema ' + tema + ' ativado');
            } else if (alvo.hasAttribute('data-acess-daltonismo')) {
                var modo = alvo.getAttribute('data-acess-daltonismo');
                mudar({ tema: prefs.tema, daltonismo: modo, escala: prefs.escala },
                    modo === 'nenhum' ? 'Cores padrão ativadas' : 'Modo ' + modo + ' ativado');
            } else if (alvo.hasAttribute('data-acess-fonte')) {
                var i = ESCALAS.indexOf(prefs.escala);
                i += alvo.getAttribute('data-acess-fonte') === 'aumentar' ? 1 : -1;
                i = Math.max(0, Math.min(ESCALAS.length - 1, i));
                mudar({ tema: prefs.tema, daltonismo: prefs.daltonismo, escala: ESCALAS[i] },
                    'Tamanho do texto ' + (ESCALAS[i] * 100).toLocaleString('pt-BR') + '%');
            } else if (alvo.hasAttribute('data-acess-restaurar')) {
                try { localStorage.removeItem(CHAVE); } catch (err) { /* ignora */ }
                prefs = { tema: null, daltonismo: 'nenhum', escala: 1 };
                aplicar(prefs);
                atualizarPainel();
                anunciar('Preferências de acessibilidade restauradas');
            }
        });

        // Setas navegam entre os modos de daltonismo (padrão radiogroup).
        var grupo = painel.querySelector('[role="radiogroup"]');
        if (grupo) {
            grupo.addEventListener('keydown', function (e) {
                if (['ArrowDown', 'ArrowUp', 'ArrowRight', 'ArrowLeft'].indexOf(e.key) === -1) return;
                e.preventDefault();
                var opcoes = Array.prototype.slice.call(grupo.querySelectorAll('[role="radio"]'));
                var atual = opcoes.indexOf(document.activeElement);
                var passo = (e.key === 'ArrowDown' || e.key === 'ArrowRight') ? 1 : -1;
                var proximo = opcoes[(atual + passo + opcoes.length) % opcoes.length];
                proximo.focus();
                proximo.click();
            });
        }

        document.addEventListener('click', function (e) {
            if (!painel.hidden && !painel.contains(e.target) && !botao.contains(e.target)) {
                fecharPainel(false);
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !painel.hidden) {
                fecharPainel(true);
            }
        });

        // Abrir o menu de perfil fecha o painel.
        var btnPerfil = document.getElementById('btnPerfilNavbar');
        if (btnPerfil) {
            btnPerfil.addEventListener('click', function () {
                if (!painel.hidden) fecharPainel(false);
            });
        }
    });
})();
