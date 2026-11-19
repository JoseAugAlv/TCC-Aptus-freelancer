/**
 * home.js - Página inicial do Aptus
 */

document.addEventListener('DOMContentLoaded', function() {

    // ==========================================
    // FILTROS
    // ==========================================
    const btnFiltros = document.getElementById('btnFiltros');
    const filtrosSection = document.getElementById('filtrosSection');
    const formFiltros = document.getElementById('formFiltros');
    const searchInput = document.getElementById('searchInput');

    if (btnFiltros && filtrosSection) {
        const abrir = function(aberto) {
            filtrosSection.style.display = aberto ? 'block' : 'none';
            btnFiltros.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        };

        btnFiltros.setAttribute('aria-controls', 'filtrosSection');
        btnFiltros.setAttribute('aria-expanded', 'false');

        btnFiltros.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            abrir(filtrosSection.style.display !== 'block');
        });

        // Fechar ao clicar fora
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.search-section')) {
                abrir(false);
            }
        });

        // Fechar com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && filtrosSection.style.display === 'block') {
                abrir(false);
                btnFiltros.focus();
            }
        });
    }

    // Leva o texto digitado na busca junto com os filtros
    if (formFiltros && searchInput) {
        formFiltros.addEventListener('submit', function() {
            const q = formFiltros.querySelector('input[name="q"]');
            if (q) q.value = searchInput.value.trim();
        });
    }
});
