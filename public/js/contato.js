// public/js/contato.js

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================================
    // CONTADOR DE CARACTERES
    // ============================================================
    const textarea = document.getElementById('mensagem');
    const charCount = document.getElementById('charCount');
    const maxLength = 1000;

    function atualizarContador() {
        if (textarea) {
            const len = textarea.value.length;
            charCount.textContent = len + ' / ' + maxLength + ' caracteres';
            if (len >= maxLength * 0.9) {
                charCount.classList.add('limite');
            } else {
                charCount.classList.remove('limite');
            }
        }
    }

    if (textarea) {
        textarea.addEventListener('input', atualizarContador);
        atualizarContador();
    }

    // ============================================================
    // FAQ - EXPANDIR/RECOLHER
    // ============================================================
    function toggleFaq(el) {
        const aberto = el.classList.contains('aberto');
        const allFaqs = document.querySelectorAll('.faq-item');
        
        allFaqs.forEach(i => {
            i.classList.remove('aberto');
            i.setAttribute('aria-expanded', 'false');
        });
        
        if (!aberto) {
            el.classList.add('aberto');
            el.setAttribute('aria-expanded', 'true');
        }
    }

    // Torna toggleFaq global para ser usado no onclick
    window.toggleFaq = toggleFaq;

    // Suporte para teclado nos itens FAQ
    document.querySelectorAll('.faq-item').forEach(item => {
        item.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleFaq(this);
            }
        });
    });

    // ============================================================
    // FEEDBACK DO FORMULÁRIO
    // ============================================================
    const formContato = document.getElementById('formContato');
    if (formContato) {
        formContato.addEventListener('submit', function() {
            const btn = document.getElementById('btnEnviar');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                btn.disabled = true;
            }
        });
    }
});