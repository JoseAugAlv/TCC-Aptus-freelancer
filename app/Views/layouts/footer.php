<?php
// app/Views/layouts/footer.php
require_once __DIR__ . '/../../Models/Configuracao.php';
$__cfg = new Configuracao();
$__siteNome     = $__cfg->get('site_nome')     ?: 'Aptus';
$__siteTelefone = $__cfg->get('site_telefone') ?: '(11) 99999-9999';
$__siteEmail    = $__cfg->get('site_email')    ?: 'contato@aptus.com';
$__siteEndereco = $__cfg->get('site_endereco') ?: 'São Paulo, SP';
?>
<footer class="footer-moderno">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3 class="footer-titulo">Sobre</h3>
                <ul class="footer-links">
                    <li><a href="<?= aptus_base() ?>/sobre">Sobre Nós</a></li>
                    <li><a href="<?= aptus_base() ?>/sobre#como-funciona">Como Funciona</a></li>
                    <li><a href="<?= aptus_base() ?>/anuncios">Serviços</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3 class="footer-titulo">Recursos</h3>
                <ul class="footer-links">
                    <li><a href="<?= aptus_base() ?>/buscar">Buscar Serviços</a></li>
                    <li><a href="<?= aptus_base() ?>/anuncios/criar">Anunciar Serviço</a></li>
                    <li><a href="<?= aptus_base() ?>/favoritos">Meus Favoritos</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3 class="footer-titulo">Suporte</h3>
                <ul class="footer-links">
                    <li><a href="<?= aptus_base() ?>/contato">Central de Ajuda</a></li>
                    <li><a href="<?= aptus_base() ?>/termos">Política de Privacidade</a></li>
                    <li><a href="<?= aptus_base() ?>/termos">Termos de Uso</a></li>
                    <li><a href="<?= aptus_base() ?>/politica-anuncios">Política de Anúncios</a></li>
                    <li><a href="<?= aptus_base() ?>/cookies">Política de Cookies</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3 class="footer-titulo">Contato</h3>
                <div class="footer-contato">
                    <i class="fas fa-phone-alt"></i> <?= htmlspecialchars($__siteTelefone) ?>
                </div>
                <div class="footer-contato">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($__siteEmail) ?>
                </div>
                <div class="footer-contato">
                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($__siteEndereco) ?>
                </div>
                <div class="footer-social">
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($__siteNome) ?>. Todos os direitos reservados.</p>
            <div class="footer-selo">
                <i class="fas fa-shield-alt"></i> Tecnologia com segurança
            </div>
        </div>
    </div>
</footer>

<script src="<?= aptus_asset('js/main.js') ?>"></script>
<script src="<?= aptus_asset('js/botao-carregando.js') ?>"></script>
<script src="<?= aptus_asset('js/masks.js') ?>"></script>
<?php foreach ((array) ($jsPagina ?? []) as $__js): ?>
<script src="<?= aptus_asset('js/' . basename($__js)) ?>"></script>
<?php endforeach; ?>


  <!-- VLibras Widget -->
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        new window.VLibras.Widget('https://vlibras.gov.br/app');

        // No celular o botão do VLibras ficava no meio da lateral direita, por
        // cima do conteúdo. Ele é desenhado num shadow DOM (o CSS da página não
        // alcança), então injetamos o estilo lá dentro: desce para o canto
        // inferior direito. A altura vem de --vw-bottom (o chat sobe o botão).
        (function () {
            var tentativas = 0;
            var timer = setInterval(function () {
                var hosts = document.querySelectorAll('#vlibras-access-wrapper');
                var prontos = Array.prototype.filter.call(hosts, function (h) { return h.shadowRoot; });
                if (prontos.length || ++tentativas > 40) {
                    clearInterval(timer);
                    prontos.forEach(function (host) {
                        if (host.shadowRoot.getElementById('aptus-vw-mobile')) return;
                        var css = document.createElement('style');
                        css.id = 'aptus-vw-mobile';
                        css.textContent = '@media (max-width: 768px) {'
                            + '#vlibras-access { top: auto !important; bottom: var(--vw-bottom, 16px) !important; right: 8px !important; }'
                            + '}';
                        host.shadowRoot.appendChild(css);
                    });
                }
            }, 250);
        })();
    </script>
     
</body>
</html>