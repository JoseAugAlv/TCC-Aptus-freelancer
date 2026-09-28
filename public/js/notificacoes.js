// public/js/notificacoes.js
// - Só faz requisições AJAX se o usuário estiver logado
// - Lê o CSRF token do <meta name="csrf-token">

document.addEventListener('DOMContentLoaded', function() {

    // ------------------------------------------------------------------
    // Detecta se o usuário está logado lendo o <script id="usuarioData">
    // (inserido por app/Views/layouts/nav.php)
    // ------------------------------------------------------------------
    var usuarioLogado = false;
    try {
        var el = document.getElementById('usuarioData');
        if (el) {
            var u = JSON.parse(el.textContent.trim() || 'null');
            usuarioLogado = u && u.id;
        }
    } catch (e) {
        usuarioLogado = false;
    }

    // Se não está logado, não faz NENHUMA chamada AJAX
    if (!usuarioLogado) {
        return;
    }

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function atualizarBadgeNotificacoes() {
        fetch('/Aptus/notificacoes/contador', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) {
            // Se voltar 401/403/redirect, não tenta parsear JSON
            if (!response.ok) return null;
            var ct = response.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) return null;
            return response.json();
        })
        .then(function(data) {
            if (!data) return;
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
            headers: {
                'X-CSRF-Token': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
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
            headers: {
                'X-CSRF-Token': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            }
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