// public/js/notificacoes.js

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Atualiza o badge de notificacoes via AJAX
     */
    function atualizarBadgeNotificacoes() {
        fetch('/Aptus/notificacoes/contador')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor');
                }
                return response.json();
            })
            .then(function(data) {
                var total = data.total || 0;
                
                // Atualizar badge na navbar (icone do sino)
                var badgeNav = document.getElementById('badgeNotificacao');
                if (badgeNav) {
                    if (total > 0) {
                        badgeNav.textContent = total;
                        badgeNav.style.display = 'inline-flex';
                    } else {
                        badgeNav.style.display = 'none';
                    }
                }
                
                // Badge mobile (no botao de perfil)
                var badgeMobile = document.getElementById('badgeMobile');
                if (badgeMobile) {
                    if (total > 0) {
                        badgeMobile.textContent = total;
                        badgeMobile.style.display = 'inline-flex';
                    } else {
                        badgeMobile.style.display = 'none';
                    }
                }
                
                // Badge no menu dropdown
                var badgeMenu = document.getElementById('badgeMenu');
                if (badgeMenu) {
                    if (total > 0) {
                        badgeMenu.textContent = total;
                        badgeMenu.style.display = 'inline-flex';
                    } else {
                        badgeMenu.style.display = 'none';
                    }
                }
            })
            .catch(function(error) {
                console.log('Erro ao buscar notificacoes:', error);
            });
    }

    /**
     * Marca notificacao como lida via AJAX
     */
    function marcarNotificacaoLida(id, element) {
        var formData = new FormData();
        formData.append('id', id);
        
        fetch('/Aptus/notificacoes/marcar-lida', {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.text();
        })
        .then(function() {
            // Remover visualmente a notificacao
            if (element) {
                var item = element.closest('.notificacao-item');
                if (item) {
                    item.style.opacity = '0.5';
                    var badge = item.querySelector('.notificacao-badge');
                    if (badge) badge.remove();
                    var btn = item.querySelector('.btn-marcar-lida');
                    if (btn) btn.remove();
                }
            }
            atualizarBadgeNotificacoes();
        })
        .catch(function(error) {
            console.log('Erro ao marcar como lida:', error);
        });
    }

    /**
     * Marca todas as notificacoes como lidas via AJAX
     */
    function marcarTodasLidas() {
        fetch('/Aptus/notificacoes/marcar-todas-lidas', {
            method: 'POST'
        })
        .then(function(response) {
            return response.text();
        })
        .then(function() {
            // Remover visualmente todas as notificacoes nao lidas
            document.querySelectorAll('.notificacao-item.nao-lida').forEach(function(item) {
                item.style.opacity = '0.5';
                var badge = item.querySelector('.notificacao-badge');
                if (badge) badge.remove();
                var btn = item.querySelector('.btn-marcar-lida');
                if (btn) btn.remove();
            });
            atualizarBadgeNotificacoes();
        })
        .catch(function(error) {
            console.log('Erro ao marcar todas como lidas:', error);
        });
    }

    // ============================================================
    // EVENTOS
    // ============================================================

    // Atualizar a cada 30 segundos
    setInterval(atualizarBadgeNotificacoes, 30000);

    // Atualizar quando a pagina ganhar foco
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            atualizarBadgeNotificacoes();
        }
    });

    // Atualizar ao carregar a pagina
    atualizarBadgeNotificacoes();

    // Marcar notificacao como lida (botoes individuais)
    document.querySelectorAll('.btn-marcar-lida').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            if (id) {
                marcarNotificacaoLida(id, this);
            }
        });
    });

    // Marcar todas como lidas
    var btnMarcarTodas = document.querySelector('.btn-marcar-todas');
    if (btnMarcarTodas) {
        btnMarcarTodas.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Marcar todas as notificacoes como lidas?')) {
                marcarTodasLidas();
            }
        });
    }

    // Tornar funcoes globais para uso inline
    window.atualizarBadgeNotificacoes = atualizarBadgeNotificacoes;
    window.marcarNotificacaoLida = marcarNotificacaoLida;
    window.marcarTodasLidas = marcarTodasLidas;
});