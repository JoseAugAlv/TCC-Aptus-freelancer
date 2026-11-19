<?php
$tituloPagina = 'Consentimento de Cookies — Aptus';
$cssPagina = 'cookies.css';
require '../app/Views/layouts/header.php';
require '../app/Views/layouts/nav.php';
?>
<section class="cookies-section" style="padding:calc(var(--altura-navbar) + 2rem) 1rem 3rem; max-width:780px; margin:0 auto;">
    <h1>Consentimento de Cookies</h1>
    <p style="color:var(--tema-texto-sec, #64748b)">Aptus — Proteção de Dados e Privacidade</p>

    <div style="background:var(--tema-fundo-alt, #f8fafc);border:1px solid var(--tema-borda, #e2e8f0);border-radius:12px;padding:1.25rem;margin:1rem 0;">
        <h2>O que são cookies?</h2>
        <p>Cookies são pequenos arquivos de texto armazenados no seu dispositivo pelo navegador quando você acessa um site. Eles ajudam a melhorar a experiência de navegação, lembrar preferências e garantir a segurança da sessão.</p>
    </div>

    <div style="background:var(--tema-fundo-alt, #f8fafc);border:1px solid var(--tema-borda, #e2e8f0);border-radius:12px;padding:1.25rem;margin:1rem 0;">
        <h2>Tipos que utilizamos</h2>
        <ul>
            <li><strong>Essenciais:</strong> necessários para o funcionamento do site (login, sessão, CSRF).</li>
            <li><strong>Preferências:</strong> lembram configurações de idioma, tema e exibição.</li>
            <li><strong>Estatísticos:</strong> ajudam a entender como os visitantes interagem com o site (dados anônimos).</li>
            <li><strong>Marketing:</strong> utilizados apenas com seu consentimento explícito.</li>
        </ul>
    </div>

    <div style="background:var(--tema-fundo-alt, #f8fafc);border:1px solid var(--tema-borda, #e2e8f0);border-radius:12px;padding:1.25rem;margin:1rem 0;">
        <h2>LGPD e seus direitos</h2>
        <p>Nosso tratamento de dados segue a Lei Geral de Proteção de Dados (Lei nº 13.709/2018). Você pode solicitar acesso, correção, exclusão e portabilidade dos seus dados a qualquer momento pelo e-mail <strong>privacidade@aptus.com</strong> ou pela página de contato.</p>
        <ul>
            <li>Consentimento pode ser revogado a qualquer momento.</li>
            <li>Dados são armazenados apenas pelo tempo necessário.</li>
            <li>Não compartilhamos dados pessoais com terceiros sem base legal.</li>
        </ul>
    </div>

    <div style="background:var(--tema-fundo-alt, #f8fafc);border:1px solid var(--tema-borda, #e2e8f0);border-radius:12px;padding:1.25rem;margin:1rem 0;">
        <h2>Política de Privacidade</h2>
        <p>Consulte nossa <a href="<?= aptus_base() ?>/termos#privacidade">Política de Privacidade</a> completa, incluindo direitos do titular, bases legais de tratamento, retenção e medidas de segurança.</p>
    </div>

    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.5rem;">
        <a href="<?= aptus_base() ?>/" class="btn btn-primary">Aceito e continuarei navegando</a>
        <a href="<?= aptus_base() ?>/termos" class="btn btn-secondary">Ler Termos e Privacidade</a>
    </div>

    <p style="margin-top:2rem;color:var(--tema-texto-sec, #64748b);font-size:.85rem;">Atualizado em <?= date('d/m/Y') ?>. Ao clicar em "Aceito", você confirma que concorda com o uso de cookies conforme descrito acima.</p>
</section>
<?php require '../app/Views/layouts/footer.php'; ?>
