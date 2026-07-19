// public/js/favoritos.js

document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // BOTAO FAVORITAR (na pagina de detalhes do anuncio)
    // ============================================================
    var btnFavorito = document.querySelector('.btn-favorito');
    
    if (btnFavorito) {
        btnFavorito.addEventListener('click', function() {
            var anuncioId = this.dataset.anuncioId;
            var isFavoritado = this.classList.contains('ativo');
            var icone = this.querySelector('i');
            var texto = this.querySelector('.favorito-texto');
            var contador = this.querySelector('.favorito-contador');
            
            var url = isFavoritado ? '/Aptus/favoritos/remover' : '/Aptus/favoritos/adicionar';
            var formData = new FormData();
            formData.append('anuncio_id', anuncioId);
            
            // Desabilitar botao durante a requisicao
            this.disabled = true;
            this.style.opacity = '0.7';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    // Atualizar estado do botao
                    if (data.favoritado) {
                        btnFavorito.classList.add('ativo');
                        icone.className = 'fas fa-heart';
                        texto.textContent = 'Favoritado';
                    } else {
                        btnFavorito.classList.remove('ativo');
                        icone.className = 'far fa-heart';
                        texto.textContent = 'Favoritar';
                    }
                    
                    // Atualizar contador
                    if (contador) {
                        contador.textContent = '(' + data.total + ')';
                    }
                    
                    // Exibir mensagem de sucesso (opcional)
                    showToast(data.message, 'success');
                    
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(function() {
                showToast('Erro ao processar. Tente novamente.', 'error');
            })
            .finally(function() {
                btnFavorito.disabled = false;
                btnFavorito.style.opacity = '1';
            });
        });
    }
    
    // ============================================================
    // TOAST NOTIFICATION
    // ============================================================
    function showToast(message, type) {
        var toast = document.createElement('div');
        toast.className = 'toast-notification toast-' + type;
        toast.innerHTML = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            z-index: 9999;
            animation: slideInRight 0.5s ease;
            max-width: 400px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        `;
        
        if (type === 'success') {
            toast.style.background = '#10b981';
        } else if (type === 'error') {
            toast.style.background = '#ef4444';
        } else {
            toast.style.background = '#3b82f6';
        }
        
        document.body.appendChild(toast);
        
        setTimeout(function() {
            toast.style.transition = 'all 0.5s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100px)';
            setTimeout(function() {
                toast.remove();
            }, 500);
        }, 3000);
    }
});

// Adicionar ao CSS global
var style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(style);