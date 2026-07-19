// public/js/notificacoes.js

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Atualiza o badge de notificações
     */
    function atualizarBadgeNotificacoes() {
        fetch('/Aptus/notificacoes/contador')
            .then(response => response.json())
            .then(data => {
                const total = data.total || 0;
                
                // Atualizar badge na navbar (links)
                const badgeNav = document.querySelector('.nav-notificacao .badge-notificacao');
                const badgeMobile = document.getElementById('badgeMobile');
                const badgeMenu = document.getElementById('badgeMenu');
                const badgeNotificacao = document.getElementById('badgeNotificacao');
                
                // Badge na navbar
                if (badgeNav) {
                    if (total > 0) {
                        badgeNav.textContent = total;
                        badgeNav.style.display = 'inline';
                    } else {
                        badgeNav.style.display = 'none';
                    }
                } else if (badgeNotificacao) {
                    if (total > 0) {
                        badgeNotificacao.textContent = total;
                        badgeNotificacao.style.display = 'inline';
                    } else {
                        badgeNotificacao.style.display = 'none';
                    }
                }
                
                // Badge mobile (no botão de perfil)
                if (badgeMobile) {
                    if (total > 0) {
                        badgeMobile.textContent = total;
                        badgeMobile.style.display = 'inline';
                    } else {
                        badgeMobile.style.display = 'none';
                    }
                }
                
                // Badge no menu dropdown
                if (badgeMenu) {
                    if (total > 0) {
                        badgeMenu.textContent = total;
                        badgeMenu.style.display = 'inline';
                    } else {
                        badgeMenu.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.log('Erro ao buscar notificações:', error);
            });
    }

    /**
     * Marca notificação como lida via AJAX
     */
    function marcarNotificacaoLida(id, element) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('/Aptus/notificacoes/marcar-lida', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            // Remover visualmente a notificação
            if (element) {
                const item = element.closest('.notificacao-item');
                if (item) {
                    item.style.opacity = '0.5';
                    const badge = item.querySelector('.notificacao-badge');
                    if (badge) badge.remove();
                    const btn = item.querySelector('.btn-marcar-lida');
                    if (btn) btn.remove();
                }
            }
            atualizarBadgeNotificacoes();
        })
        .catch(error => {
            console.log('Erro ao marcar como lida:', error);
        });
    }

    /**
     * Marca todas as notificações como lidas via AJAX
     */
    function marcarTodasLidas() {
        fetch('/Aptus/notificacoes/marcar-todas-lidas', {
            method: 'POST'
        })
        .then(response => response.text())
        .then(() => {
            // Remover visualmente todas as notificações não lidas
            document.querySelectorAll('.notificacao-item.nao-lida').forEach(item => {
                item.style.opacity = '0.5';
                const badge = item.querySelector('.notificacao-badge');
                if (badge) badge.remove();
                const btn = item.querySelector('.btn-marcar-lida');
                if (btn) btn.remove();
            });
            atualizarBadgeNotificacoes();
        })
        .catch(error => {
            console.log('Erro ao marcar todas como lidas:', error);
        });
    }

    // ============================================================
    // EVENTOS
    // ============================================================

    // Atualizar a cada 30 segundos
    setInterval(atualizarBadgeNotificacoes, 30000);

    // Atualizar quando a página ganhar foco
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            atualizarBadgeNotificacoes();
        }
    });

    // Marcar notificação como lida (botões individuais)
    document.querySelectorAll('.btn-marcar-lida').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            if (id) {
                marcarNotificacaoLida(id, this);
            }
        });
    });

    // Marcar todas como lidas
    const btnMarcarTodas = document.querySelector('.btn-marcar-todas');
    if (btnMarcarTodas) {
        btnMarcarTodas.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Marcar todas as notificações como lidas?')) {
                marcarTodasLidas();
            }
        });
    }

    // Tornar funções globais para uso inline
    window.atualizarBadgeNotificacoes = atualizarBadgeNotificacoes;
    window.marcarNotificacaoLida = marcarNotificacaoLida;
    window.marcarTodasLidas = marcarTodasLidas;
});