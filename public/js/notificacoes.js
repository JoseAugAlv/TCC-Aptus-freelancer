// public/js/notificacoes.js
// [FIX-CRIT-2.3] Passa o CSRF token em todos os POST. Antes só o /contador
//                tentava (e errado, via meta inexistente).

document.addEventListener('DOMContentLoaded', function() {

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function atualizarBadgeNotificacoes() {
        fetch('/Aptus/notificacoes/contador')
            .then(function(response) {
                if (!response.ok) throw new Error('Erro na resposta do servidor');
                return response.json();
            })
            .then(function(data) {
                var total = data.total || 0;

                var badgeNav = document.getElementById('badgeNotificacao');
                if (badgeNav) {
                    if (total > 0) { badgeNav.textContent = total; badgeNav.style.display = 'inline-flex'; }
                    else { badgeNav.style.display = 'none'; }
                }

                var badgeMobile = document.getElementById('badgeMobile');
                if (badgeMobile) {
                    if (total > 0) { badgeMobile.textContent = total; badgeMobile.style.display = 'inline-flex'; }
                    else { badgeMobile.style.display = 'none'; }
                }

                var badgeMenu = document.getElementById('badgeMenu');
                if (badgeMenu) {
                    if (total > 0) { badgeMenu.textContent = total; badgeMenu.style.display = 'inline-flex'; }
                    else { badgeMenu.style.display = 'none'; }
                }
            })
            .catch(function(error) {
                console.log('Erro ao buscar notificacoes:', error);
            });
    }

    function marcarNotificacaoLida(id, element) {
        var formData = new FormData();
        formData.append('id', id);

        fetch('/Aptus/notificacoes/marcar-lida', {
            method: 'POST',
            headers: { 'X-CSRF-Token': getCsrfToken() },
            body: formData
        })
        .then(function() {
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

    function marcarTodasLidas() {
        fetch('/Aptus/notificacoes/marcar-todas-lidas', {
            method: 'POST',
            headers: { 'X-CSRF-Token': getCsrfToken() }
        })
        .then(function() {
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

    setInterval(atualizarBadgeNotificacoes, 30000);

    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) atualizarBadgeNotificacoes();
    });

    atualizarBadgeNotificacoes();

    document.querySelectorAll('.btn-marcar-lida').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            if (id) marcarNotificacaoLida(id, this);
        });
    });

    var btnMarcarTodas = document.querySelector('.btn-marcar-todas');
    if (btnMarcarTodas) {
        btnMarcarTodas.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Marcar todas as notificacoes como lidas?')) {
                marcarTodasLidas();
            }
        });
    }

    window.atualizarBadgeNotificacoes = atualizarBadgeNotificacoes;
});