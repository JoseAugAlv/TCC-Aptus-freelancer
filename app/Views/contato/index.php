<?php
// app/Views/contato/index.php

$tituloPagina = $tituloPagina ?? 'Contato - Aptus';
$cssPagina = $cssPagina ?? 'contato.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$sucesso = $sucesso ?? false;
$erro = $erro ?? '';
?>

<section class="contato-hero">
    <h1><i class="fas fa-envelope" style="font-size:.85em;"></i> Fale Conosco</h1>
    <p>Tem dúvidas, sugestões ou precisa de suporte? Nossa equipe está pronta para ajudar!</p>
</section>

<div class="contato-wrapper">

    <div class="info-card">
        <h2><i class="fas fa-address-card"></i> Informações</h2>

        <div class="info-item">
            <div class="info-icon"><i class="fas fa-envelope"></i></div>
            <div class="info-text">
                <strong>E-mail</strong>
                <a href="mailto:contato@aptus.com.br">contato@aptus.com.br</a>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
            <div class="info-text">
                <strong>Telefone / WhatsApp</strong>
                <a href="https://wa.me/5511973651202" target="_blank" rel="noopener noreferrer">(11) 97365-1202</a>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon"><i class="fas fa-clock"></i></div>
            <div class="info-text">
                <strong>Horário de Atendimento</strong>
                <span>Seg – Sex: 8h às 18h</span>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="info-text">
                <strong>Localização</strong>
                <span>Brasil – atendimento online</span>
            </div>
        </div>

        <div class="divider"></div>

        <p class="redes-title">Nossas Redes Sociais</p>
        <div class="redes-sociais">
            <a href="#" class="rede-btn instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="rede-btn linkedin" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://wa.me/5511973651202" class="rede-btn whatsapp" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i></a>
            <a href="#" class="rede-btn facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        </div>
    </div>

    <!-- DIV DIREITA - FORMULÁRIO -->
    <div class="form-card">
        <h2>Envie sua mensagem</h2>
        <p class="subtitulo">Preencha o formulário abaixo e responderemos em até 5 dias úteis.</p>

        <?php if ($sucesso): ?>
            <div class="alerta sucesso" role="alert">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>✓ Mensagem enviada com sucesso!</strong><br>
                    Obrigado pelo contato! Entraremos em contato em breve.<br>
                    <small>Você receberá uma confirmação por e-mail.</small>
                </div>
            </div>
        <?php elseif ($erro): ?>
            <div class="alerta erro" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="/Aptus/contato" novalidate id="formContato">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome completo <span class="obrigatorio">*</span></label>
                    <input type="text" id="nome" name="nome" placeholder="Seu nome"
                           maxlength="100" required
                           autocomplete="name"
                           value="<?= isset($_POST['nome']) && !$sucesso ? htmlspecialchars($_POST['nome']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="email">E-mail <span class="obrigatorio">*</span></label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com"
                           maxlength="150" required
                           autocomplete="email"
                           value="<?= isset($_POST['email']) && !$sucesso ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="assunto">Assunto <span class="obrigatorio">*</span></label>
                <select id="assunto" name="assunto" required>
                    <option value="" disabled <?= !isset($_POST['assunto']) || $sucesso ? 'selected' : '' ?>>Selecione um assunto</option>
                    <option value="Dúvida geral" <?= isset($_POST['assunto']) && $_POST['assunto'] === 'Dúvida geral' && !$sucesso ? 'selected' : '' ?>>Dúvida geral</option>
                    <option value="Suporte técnico" <?= isset($_POST['assunto']) && $_POST['assunto'] === 'Suporte técnico' && !$sucesso ? 'selected' : '' ?>>Suporte técnico</option>
                    <option value="Parceria" <?= isset($_POST['assunto']) && $_POST['assunto'] === 'Parceria' && !$sucesso ? 'selected' : '' ?>>Parceria</option>
                    <option value="Reclamação" <?= isset($_POST['assunto']) && $_POST['assunto'] === 'Reclamação' && !$sucesso ? 'selected' : '' ?>>Reclamação</option>
                    <option value="Sugestão" <?= isset($_POST['assunto']) && $_POST['assunto'] === 'Sugestão' && !$sucesso ? 'selected' : '' ?>>Sugestão</option>
                    <option value="Outro" <?= isset($_POST['assunto']) && $_POST['assunto'] === 'Outro' && !$sucesso ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="mensagem">Mensagem <span class="obrigatorio">*</span></label>
                <textarea id="mensagem" name="mensagem" placeholder="Escreva sua mensagem aqui..."
                          maxlength="1000" required><?= isset($_POST['mensagem']) && !$sucesso ? htmlspecialchars($_POST['mensagem']) : '' ?></textarea>
                <p class="char-count" id="charCount" aria-live="polite">0 / 1000 caracteres</p>
            </div>

            <button type="submit" class="btn-enviar" id="btnEnviar">
                <i class="fas fa-paper-plane"></i>
                Enviar Mensagem
            </button>

            <p class="privacidade">
                <i class="fas fa-lock"></i>
                Seus dados são protegidos e não serão compartilhados com terceiros.
            </p>
        </form>
    </div>
</div>

<!-- FAQ SECTION -->
<div class="faq-section">
    <h2><i class="fas fa-question-circle"></i> Perguntas Frequentes</h2>
    <div class="faq-grid">
        <div class="faq-item" onclick="toggleFaq(this)" role="button" tabindex="0" aria-expanded="false">
            <div class="faq-pergunta">
                Como contratar um freelancer na Aptus?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-resposta">
                Basta criar uma conta gratuita, pesquisar o serviço desejado e enviar uma proposta ao profissional. Após acordo, o pagamento é processado de forma segura pela plataforma.
            </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)" role="button" tabindex="0" aria-expanded="false">
            <div class="faq-pergunta">
                Quanto tempo leva para receber uma resposta?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-resposta">
                Nossa equipe de suporte responde em até 5 dias úteis. Para urgências, utilize o canal de WhatsApp disponível nas informações ao lado.
            </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)" role="button" tabindex="0" aria-expanded="false">
            <div class="faq-pergunta">
                Como me tornar um freelancer na plataforma?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-resposta">
                Crie uma conta, acesse seu perfil e selecione a opção "Tornar-me freelancer". Preencha seu portfólio e comece a receber propostas de clientes.
            </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)" role="button" tabindex="0" aria-expanded="false">
            <div class="faq-pergunta">
                O cadastro na Aptus é gratuito?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-resposta">
                Sim! O cadastro é 100% gratuito tanto para clientes quanto para freelancers. Cobramos apenas uma pequena taxa sobre transações concluídas com sucesso.
            </div>
        </div>
    </div>
</div>

<script src="/Aptus/public/js/contato.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>