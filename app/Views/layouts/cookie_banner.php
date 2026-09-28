<?php
/**
 * app/Views/layouts/cookie_banner.php
 * Banner de consentimento de cookies (LGPD)
 */
$APP_PATH = '/Aptus/';
?>

<div id="cookieBanner" class="cookie-banner" role="dialog" aria-modal="true"
     aria-label="Consentimento de cookies" style="display:none;">

    <div class="cookie-banner-inner">

        <div class="cookie-banner-icon">
            <i class="fas fa-cookie-bite"></i>
        </div>

        <div class="cookie-banner-text">
            <strong>Este site utiliza cookies</strong>
            <p>
                Utilizamos cookies para melhorar sua experiência, manter sua sessão segura
                e analisar o uso do site. Você pode aceitar ou recusar os cookies não essenciais.
                Saiba mais na nossa
                <a href="/Aptus/cookies">Política de Cookies</a>.
            </p>
        </div>

        <div class="cookie-banner-actions">
            <button type="button" class="cookie-btn cookie-btn-refuse" id="cookieRefuse">
                Recusar
            </button>
            <button type="button" class="cookie-btn cookie-btn-accept" id="cookieAccept">
                Aceitar
            </button>
        </div>

    </div>
</div>

<style>
/* ============================================================
   BANNER DE COOKIES — CSS completo (base + mobile)
   ============================================================ */

/* ---------- BASE ---------- */
.cookie-banner,
.cookie-banner * {
    box-sizing: border-box;
}

.cookie-banner {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    z-index: 2147483647;
    background: var(--cor-primaria, #006577);
    color: #ffffff;
    border-top: 3px solid var(--cor-dourado, #C9A227);
    box-shadow: 0 -6px 24px rgba(0, 0, 0, 0.25);
    padding: 0 0 60px 0
    transform: translateY(100%);
    transition: transform 0.35s ease;
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
}

.cookie-banner.show {
    transform: translateY(0);
}

.cookie-banner-inner {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
     padding: 16px 22px 6px 22px
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
}

/* Ícone */
.cookie-banner-icon {
    flex-shrink: 0;
    font-size: 1.7rem;
    line-height: 1;
    color: var(--cor-dourado, #C9A227);
}

/* Texto */
.cookie-banner-text {
    flex: 1 1 220px;
    min-width: 0;
    font-size: 0.92rem;
    line-height: 1.55;
    color: #ffffff;
}

.cookie-banner-text strong {
    display: block;
    font-size: 1rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 4px;
    letter-spacing: .2px;
}

.cookie-banner-text p {
    margin: 0;
    color: rgba(255, 255, 255, 0.88);
}

.cookie-banner-text a {
    color: var(--cor-dourado, #C9A227);
    text-decoration: underline;
    font-weight: 600;
    transition: color .25s;
}

.cookie-banner-text a:hover {
    color: #facc15;
}

/* Botões (desktop base) */
.cookie-banner-actions {
    display: flex;
    flex-direction: row;
    gap: 10px;
    flex-shrink: 0;
}

.cookie-btn {
    padding: 11px 24px;
    border-radius: 8px;
    border: none;
    font-family: inherit;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    line-height: 1.2;
    transition: all .25s ease;
}

/* Botão ACEITAR — fundo dourado, texto verde-escuro */
.cookie-btn-accept {
    background: var(--cor-dourado, #C9A227);
    color: var(--cor-primaria, #006577);
}

.cookie-btn-accept:hover {
    background: #facc15;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(201, 162, 39, 0.45);
}

/* Botão RECUSAR — contorno branco translúcido */
.cookie-btn-refuse {
    background: transparent;
    color: #ffffff;
    border: 2px solid rgba(255, 255, 255, 0.55);
}

.cookie-btn-refuse:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: #ffffff;
    color: #ffffff;
}

/* ---------- MOBILE ---------- */
@media (max-width: 900px) {

    .cookie-banner-inner {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        padding: 14px 16px 6px 16px;
    }

    .cookie-banner-icon {
        align-self: flex-start;
        font-size: 1.5rem;
    }

    .cookie-banner-text {
        width: 100%;
        font-size: 0.85rem;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .cookie-banner-text strong {
        font-size: 0.95rem;
    }

    .cookie-banner-actions {
        width: 100%;
        gap: 10px;
        margin-top: 6px;
    }

    .cookie-btn {
        flex: 1 1 0;
        min-width: 0;
        min-height: 48px;
        padding: 13px 14px;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
}

/* ---------- MOBILE PEQUENO ---------- */
@media (max-width: 380px) {
    .cookie-banner-inner {
        padding: 12px 12px 16px 12px;
        gap: 10px;
    }
    .cookie-banner-text {
        font-size: 0.8rem;
    }
    .cookie-btn {
        font-size: 0.82rem;
        padding: 12px 8px;
        min-height: 46px;
    }
}

/* ---------- LANDSCAPE ---------- */
@media (max-height: 500px) and (orientation: landscape) {
    .cookie-banner-inner {
        flex-direction: row;
        align-items: center;
        padding: 10px 16px;
        gap: 12px;
    }
    .cookie-banner-text p {
        max-height: 2.8em;
        overflow: hidden;
    }
    .cookie-banner-actions {
        width: auto;
        margin-top: 0;
    }
    .cookie-btn {
        flex: 0 0 auto;
        min-height: 42px;
        padding: 10px 16px;
    }
}
</style>

<script>
(function () {
    'use strict';

    var PATH = <?= json_encode($APP_PATH) ?>;
    var KEY  = 'aptus_cookie_consent';
    var DAYS = 365;

    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() +
                          ';path=' + PATH + ';SameSite=Lax';
    }

    function getCookie(name) {
        var m = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return m ? m[2] : null;
    }

    function calcularSafeArea() {
        var ua = navigator.userAgent;
        if (window.innerWidth > 900) return 0;

        // iPhone tem barra fina (home indicator)
        if (/iPhone|iPad|iPod/i.test(ua)) return window.innerWidth <= 380 ? 20 : 24;

        // Android Chrome tem barra de navegação grossa
        if (/Android/i.test(ua)) return window.innerWidth <= 380 ? 60 : 68;

        return 56;
    }

    function posicionarBanner() {
        var b = document.getElementById('cookieBanner');
        if (!b) return;

        // Banner colado no fundo (verde até o fim da tela)
        b.style.setProperty('bottom', '0px', 'important');

        // Folga interna para o conteúdo não ficar atrás da barra do navegador
        b.style.setProperty('padding-bottom', calcularSafeArea() + 'px', 'important');
    }

    function mostrarBanner() {
        var b = document.getElementById('cookieBanner');
        if (!b) return;
        posicionarBanner();
        b.style.display = 'block';
        void b.offsetHeight;
        b.classList.add('show');
    }

    function esconderBanner() {
        var b = document.getElementById('cookieBanner');
        if (!b) return;
        b.classList.remove('show');
        setTimeout(function () { b.style.display = 'none'; }, 450);
    }

    function decidir(valor) {
        setCookie(KEY, valor, DAYS);
        try { localStorage.setItem(KEY, valor); } catch (e) {}
        esconderBanner();
        if (typeof gtag === 'function') {
            gtag('consent', 'update', {
                analytics_storage: valor === 'aceito' ? 'granted' : 'denied'
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var banner = document.getElementById('cookieBanner');
        if (!banner) return;

        if (!getCookie(KEY)) {
            setTimeout(mostrarBanner, 400);
        }

        var btnAceitar = document.getElementById('cookieAccept');
        if (btnAceitar) btnAceitar.addEventListener('click', function () { decidir('aceito'); });

        var btnRecusar = document.getElementById('cookieRefuse');
        if (btnRecusar) btnRecusar.addEventListener('click', function () { decidir('recusado'); });

        window.addEventListener('resize', posicionarBanner);
        window.addEventListener('orientationchange', function () {
            setTimeout(posicionarBanner, 300);
        });
    });

    window.aptusResetCookieBanner = function () {
        setCookie(KEY, '', -1);
        try { localStorage.removeItem(KEY); } catch (e) {}
        location.reload();
    };
})();
</script>