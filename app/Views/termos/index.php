<?php
// app/Views/termos/index.php

$tituloPagina = $tituloPagina ?? 'Termos de Uso e Política de Privacidade - Aptus';
$cssPagina = $cssPagina ?? 'termos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div class="termos-container">
    <div class="termos-header">
        <h1></i> Termos de Uso e Política de Privacidade</h1>
        <p>Leia atentamente os termos e condições para utilizar a plataforma Aptus</p>
        <p class="termos-data">Última atualização: <?= date('d/m/Y') ?></p>
    </div>

    <hr>

    <div class="termos-content">
        
        <!-- Indice -->
        <div class="termos-indice">
            <h3>Indice</h3>
            <ul>
                <li><a href="#introducao">1. Introdução</a></li>
                <li><a href="#aceitacao">2. Aceitação dos Termos</a></li>
                <li><a href="#cadastro">3. Cadastro e Conta</a></li>
                <li><a href="#servicos">4. Serviços Oferecidos</a></li>
                <li><a href="#responsabilidades">5. Responsabilidades do Usuário</a></li>
                <li><a href="#pagamentos">6. Pagamentos e Transações</a></li>
                <li><a href="#cancelamento">7. Cancelamento e Reembolso</a></li>
                <li><a href="#privacidade">8. Política de Privacidade</a></li>
                <li><a href="#dados">9. Coleta e Uso de Dados</a></li>
                <li><a href="#seguranca">10. Segurança da Informação</a></li>
                <li><a href="#moderacao">11. Moderação e Conteúdo</a></li>
                <li><a href="#propriedade">12. Propriedade Intelectual</a></li>
                <li><a href="#alteracoes">13. Alterações nos Termos</a></li>
                <li><a href="#contato">14. Contato</a></li>
            </ul>
        </div>

        <!-- 1. Introducao -->
        <div class="termos-section" id="introducao">
            <h2>1. Introdução</h2>
            <p>
                A plataforma Aptus e um marketplace que conecta profissionais (freelancers) a clientes que buscam 
                serviços de qualidade. Estes Termos de Uso regem o relacionamento entre a Aptus, seus usuários e 
                terceiros que utilizam a plataforma.
            </p>
            <p>
                Ao acessar ou utilizar a plataforma Aptus, você concorda com estes termos e condições. Se não 
                concordar, por favor, não utilize a plataforma.
            </p>
        </div>

        <!-- 2. Aceitacao -->
        <div class="termos-section" id="aceitacao">
            <h2>2. Aceitação dos Termos</h2>
            <p>
                Ao criar uma conta ou utilizar os serviços da Aptus, você declara que leu, entendeu e concordou 
                com todos os termos e condições descritos neste documento.
            </p>
            <p>
                Os termos se aplicam a todos os usuários, incluindo visitantes, clientes e freelancers.
            </p>
        </div>

        <!-- 3. Cadastro -->
        <div class="termos-section" id="cadastro">
            <h2>3. Cadastro e Conta</h2>
            <p>
                Para utilizar a plataforma, você deve criar uma conta fornecendo informações verídicas e atualizadas.
            </p>
            <ul>
                <li>Você é responsável pela segurança da sua conta e senha.</li>
                <li>Não compartilhe suas credenciais com terceiros.</li>
                <li>Você deve ter pelo menos 18 anos para criar uma conta.</li>
                <li>Informações falsas podem resultar no bloqueio da conta.</li>
            </ul>
        </div>

        <!-- 4. Servicos -->
        <div class="termos-section" id="servicos">
            <h2>4. Serviços Oferecidos</h2>
            <p>
                A Aptus oferece uma plataforma para:
            </p>
            <ul>
                <li><strong>Freelancers:</strong> Anunciar serviços, receber propostas e gerenciar contratos.</li>
                <li><strong>Clientes:</strong> Buscar serviços, enviar propostas e contratar profissionais.</li>
                <li><strong>Comunicação:</strong> Chat integrado entre clientes e freelancers.</li>
                <li><strong>Pagamento:</strong> Confirmação de pagamento em via de mão dupla (a plataforma não retém valores).</li>
            </ul>
        </div>

        <!-- 5. Responsabilidades -->
        <div class="termos-section" id="responsabilidades">
            <h2>5. Responsabilidades do Usuário</h2>
            <p>Ao utilizar a plataforma, você se compromete a:</p>
            <ul>
                <li>Fornecer informações verdadeiras e precisas.</li>
                <li>Não praticar atividades ilegais ou fraudulentas.</li>
                <li>Respeitar os direitos de outros usuários.</li>
                <li>Não usar a plataforma para spam ou assédio.</li>
                <li>Não publicar conteúdo ofensivo ou inadequado.</li>
                <li>Cumprir com os prazos e acordos estabelecidos.</li>
            </ul>
        </div>

        <!-- 6. Pagamentos -->
        <div class="termos-section" id="pagamentos">
            <h2>6. Pagamentos e Transações</h2>
            <p>
                A Aptus atua como intermediária facilitando a conexão entre clientes e freelancers, mas 
                <strong>NÃO RETÉM</strong> valores de pagamento.
            </p>
            <ul>
                <li>O pagamento e combinado diretamente entre as partes.</li>
                <li>A plataforma oferece um sistema de confirmação de pagamento em via de mão dupla.</li>
                <li>Em caso de divergência, a plataforma pode mediar a disputa.</li>
                <li>Não nos responsabilizamos por pagamentos não realizados fora da plataforma.</li>
            </ul>
        </div>

        <!-- 7. Cancelamento -->
        <div class="termos-section" id="cancelamento">
            <h2>7. Cancelamento e Reembolso</h2>
            <ul>
                <li>Ambas as partes podem cancelar um interesse a qualquer momento.</li>
                <li>Cancelamentos devem ser comunicados através da plataforma.</li>
                <li>Reembolsos são de responsabilidade das partes envolvidas.</li>
                <li>A plataforma não se responsabiliza por valores pagos fora do sistema.</li>
            </ul>
        </div>

        <!-- 8. Privacidade -->
        <div class="termos-section" id="privacidade">
            <h2>8. Política de Privacidade</h2>
            <p>
                A Aptus respeita sua privacidade e esta comprometida em proteger seus dados pessoais.
            </p>
            <ul>
                <li>Coletamos apenas os dados necessarios para o funcionamento da plataforma.</li>
                <li>Seus dados não serão compartilhados com terceiros sem seu consentimento.</li>
                <li>Você pode solicitar a exclusão de seus dados a qualquer momento.</li>
            </ul>
        </div>

        <!-- 9. Dados -->
        <div class="termos-section" id="dados">
            <h2>9. Coleta e Uso de Dados</h2>
            <p><strong>Dados coletados:</strong></p>
            <ul>
                <li>Nome, e-mail, telefone e dados de perfil.</li>
                <li>Histórico de serviços e interesses.</li>
                <li>Mensagens e comunicacoes.</li>
                <li>Dados de navegação e interacao na plataforma.</li>
            </ul>
            <p><strong>Uso dos dados:</strong></p>
            <ul>
                <li>Fornecer e melhorar nossos serviços.</li>
                <li>Comunicar-se com você sobre sua conta.</li>
                <li>Personalizar sua experiência na plataforma.</li>
                <li>Gerar estatísticas e relatórios anônimos.</li>
            </ul>
        </div>

        <!-- 10. Seguranca -->
        <div class="termos-section" id="seguranca">
            <h2>10. Segurança da Informação</h2>
            <p>
                A Aptus adota medidas de segurança para proteger seus dados, incluindo:
            </p>
            <ul>
                <li>Criptografia de dados (SSL/TLS).</li>
                <li>Armazenamento seguro de senhas (hash e salt).</li>
                <li>Monitoramento de atividades suspeitas.</li>
                <li>Backups regulares do banco de dados.</li>
            </ul>
            <p>
                Apesar disso, nenhum sistema e 100% seguro. Recomendamos que você também adote boas práticas 
                de segurança, como senhas fortes e verificação em duas etapas.
            </p>
        </div>

        <!-- 11. Moderacao -->
        <div class="termos-section" id="moderacao">
            <h2>11. Moderação e Conteúdo</h2>
            <p>
                A Aptus reserva-se o direito de moderar todo o conteúdo publicado na plataforma.
            </p>
            <ul>
                <li>Anúncios passam por moderação antes de serem publicados.</li>
                <li>Conteúdo inadequado será removido sem aviso prévio.</li>
                <li>Usuários que violarem as regras podem ser banidos.</li>
                <li>Denúncias serão analisadas pela equipe de moderação.</li>
            </ul>
        </div>

        <!-- 12. Propriedade Intelectual -->
        <div class="termos-section" id="propriedade">
            <h2>12. Propriedade Intelectual</h2>
            <p>
                Todos os direitos de propriedade intelectual da plataforma Aptus pertencem a seus criadores.
            </p>
            <ul>
                <li>O código, design e conteúdo da plataforma são protegidos por direitos autorais.</li>
                <li>Usuários mantem os direitos sobre seus proprios conteúdos.</li>
                <li>Ao publicar na plataforma, você concede a Aptus uma licença para exibir seu conteúdo.</li>
            </ul>
        </div>

        <!-- 13. Alteracoes -->
        <div class="termos-section" id="alteracoes">
            <h2>13. Alterações nos Termos</h2>
            <p>
                A Aptus pode atualizar estes termos periodicamente. As alterações serão comunicadas aos usuários 
                através da plataforma ou por e-mail.
            </p>
            <ul>
                <li>Termos atualizados entram em vigor imediatamente.</li>
                <li>O uso continuado da plataforma implica na aceitação dos novos termos.</li>
                <li>Recomendamos revisar esta página regularmente.</li>
            </ul>
        </div>

        <!-- 14. Contato -->
        <div class="termos-section" id="contato">
            <h2>14. Contato</h2>
            <p>
                Em caso de dúvidas sobre estes termos, entre em contato conosco:
            </p>
            <div class="termos-contato">
                <p><i class="fas fa-envelope"></i> <a href="mailto:contato@aptus.com">contato@aptus.com</a></p>
                <p><i class="fas fa-phone"></i> <a href="tel:+5511999999999">(11) 99999-9999</a></p>
                <p><i class="fas fa-map-marker-alt"></i> São Paulo, SP</p>
            </div>
        </div>

        <!-- Botao Voltar -->
        <div class="termos-voltar">
            <a href="<?= aptus_base() ?>/" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar para o Início
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>