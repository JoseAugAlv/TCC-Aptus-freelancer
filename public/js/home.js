/**
 * home.js - Página inicial do Aptus
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // FILTROS
    // ==========================================
    const btnFiltros = document.getElementById('btnFiltros');
    const filtrosSection = document.getElementById('filtrosSection');
    
    if (btnFiltros && filtrosSection) {
        btnFiltros.addEventListener('click', function(e) {
            e.stopPropagation();
            filtrosSection.style.display = 
                filtrosSection.style.display === 'none' ? 'block' : 'none';
        });

        // Fechar filtros ao clicar fora
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.search-section')) {
                filtrosSection.style.display = 'none';
            }
        });
    }

    // ==========================================
    // BUSCA
    // ==========================================
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                // O formulário cuida do envio via GET
                console.log('Buscando: ' + this.value);
            }
        });
    }
});