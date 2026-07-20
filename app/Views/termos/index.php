<?php
// app/Views/termos/index.php

$tituloPagina = $tituloPagina ?? 'Termos de Uso e Politica de Privacidade - Aptus';
$cssPagina = $cssPagina ?? 'termos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div class="termos-container">
    <div class="termos-header">
        <h1><i class="fas fa-file-contract"></i> Termos de Uso e Politica de Privacidade</h1>
        <p>Leia atentamente os termos e condicoes para utilizar a plataforma Aptus</p>
        <p class="termos-data">Ultima atualizacao: <?= date('d/m/Y') ?></p>
    </div>

    <hr>

    <div class="termos-content">
        
        <!-- Indice -->
        <div class="termos-indice">
            <h3>Indice</h3>
            <ul>
                <li><a href="#introducao">1. Introducao</a></li>
                <li><a href="#aceitacao">2. Aceitacao dos Termos</a></li>
                <li><a href="#cadastro">3. Cadastro e Conta</a></li>
                <li><a href="#servicos">4. Servicos Oferecidos</a></li>
                <li><a href="#responsabilidades">5. Responsabilidades do Usuario</a></li>
                <li><a href="#pagamentos">6. Pagamentos e Transacoes</a></li>
                <li><a href="#cancelamento">7. Cancelamento e Reembolso</a></li>
                <li><a href="#privacidade">8. Politica de Privacidade</a></li>
                <li><a href="#dados">9. Coleta e Uso de Dados</a></li>
                <li><a href="#seguranca">10. Seguranca da Informacao</a></li>
                <li><a href="#moderacao">11. Moderacao e Conteudo</a></li>
                <li><a href="#propriedade">12. Propriedade Intelectual</a></li>
                <li><a href="#alteracoes">13. Alteracoes nos Termos</a></li>
                <li><a href="#contato">14. Contato</a></li>
            </ul>
        </div>

        <!-- 1. Introducao -->
        <div class="termos-section" id="introducao">
            <h2>1. Introducao</h2>
            <p>
                A plataforma Aptus e um marketplace que conecta profissionais (freelancers) a clientes que buscam 
                servicos de qualidade. Estes Termos de Uso regem o relacionamento entre a Aptus, seus usuarios e 
                terceiros que utilizam a plataforma.
            </p>
            <p>
                Ao acessar ou utilizar a plataforma Aptus, voce concorda com estes termos e condicoes. Se nao 
                concordar, por favor, nao utilize a plataforma.
            </p>
        </div>

        <!-- 2. Aceitacao -->
        <div class="termos-section" id="aceitacao">
            <h2>2. Aceitacao dos Termos</h2>
            <p>
                Ao criar uma conta ou utilizar os servicos da Aptus, voce declara que leu, entendeu e concordou 
                com todos os termos e condicoes descritos neste documento.
            </p>
            <p>
                Os termos se aplicam a todos os usuarios, incluindo visitantes, clientes e freelancers.
            </p>
        </div>

        <!-- 3. Cadastro -->
        <div class="termos-section" id="cadastro">
            <h2>3. Cadastro e Conta</h2>
            <p>
                Para utilizar a plataforma, voce deve criar uma conta fornecendo informacoes veridicas e atualizadas.
            </p>
            <ul>
                <li>Voce e responsavel pela seguranca da sua conta e senha.</li>
                <li>Nao compartilhe suas credenciais com terceiros.</li>
                <li>Voce deve ter pelo menos 18 anos para criar uma conta.</li>
                <li>Informacoes falsas podem resultar no bloqueio da conta.</li>
            </ul>
        </div>

        <!-- 4. Servicos -->
        <div class="termos-section" id="servicos">
            <h2>4. Servicos Oferecidos</h2>
            <p>
                A Aptus oferece uma plataforma para:
            </p>
            <ul>
                <li><strong>Freelancers:</strong> Anunciar servicos, receber propostas e gerenciar contratos.</li>
                <li><strong>Clientes:</strong> Buscar servicos, enviar propostas e contratar profissionais.</li>
                <li><strong>Comunicacao:</strong> Chat integrado entre clientes e freelancers.</li>
                <li><strong>Pagamento:</strong> Confirmacao de pagamento em via de mao dupla (a plataforma nao retem valores).</li>
            </ul>
        </div>

        <!-- 5. Responsabilidades -->
        <div class="termos-section" id="responsabilidades">
            <h2>5. Responsabilidades do Usuario</h2>
            <p>Ao utilizar a plataforma, voce se compromete a:</p>
            <ul>
                <li>Fornecer informacoes verdadeiras e precisas.</li>
                <li>Nao praticar atividades ilegais ou fraudulentas.</li>
                <li>Respeitar os direitos de outros usuarios.</li>
                <li>Nao usar a plataforma para spam ou assedio.</li>
                <li>Nao publicar conteudo ofensivo ou inadequado.</li>
                <li>Cumprir com os prazos e acordos estabelecidos.</li>
            </ul>
        </div>

        <!-- 6. Pagamentos -->
        <div class="termos-section" id="pagamentos">
            <h2>6. Pagamentos e Transacoes</h2>
            <p>
                A Aptus atua como intermediaria facilitando a conexao entre clientes e freelancers, mas 
                <strong>NAO RETEM</strong> valores de pagamento.
            </p>
            <ul>
                <li>O pagamento e combinado diretamente entre as partes.</li>
                <li>A plataforma oferece um sistema de confirmacao de pagamento em via de mao dupla.</li>
                <li>Em caso de divergencia, a plataforma pode mediar a disputa.</li>
                <li>Nao nos responsabilizamos por pagamentos nao realizados fora da plataforma.</li>
            </ul>
        </div>

        <!-- 7. Cancelamento -->
        <div class="termos-section" id="cancelamento">
            <h2>7. Cancelamento e Reembolso</h2>
            <ul>
                <li>Ambas as partes podem cancelar um interesse a qualquer momento.</li>
                <li>Cancelamentos devem ser comunicados atraves da plataforma.</li>
                <li>Reembolsos sao de responsabilidade das partes envolvidas.</li>
                <li>A plataforma nao se responsabiliza por valores pagos fora do sistema.</li>
            </ul>
        </div>

        <!-- 8. Privacidade -->
        <div class="termos-section" id="privacidade">
            <h2>8. Politica de Privacidade</h2>
            <p>
                A Aptus respeita sua privacidade e esta comprometida em proteger seus dados pessoais.
            </p>
            <ul>
                <li>Coletamos apenas os dados necessarios para o funcionamento da plataforma.</li>
                <li>Seus dados nao serao compartilhados com terceiros sem seu consentimento.</li>
                <li>Voce pode solicitar a exclusao de seus dados a qualquer momento.</li>
            </ul>
        </div>

        <!-- 9. Dados -->
        <div class="termos-section" id="dados">
            <h2>9. Coleta e Uso de Dados</h2>
            <p><strong>Dados coletados:</strong></p>
            <ul>
                <li>Nome, e-mail, telefone e dados de perfil.</li>
                <li>Historico de servicos e interesses.</li>
                <li>Mensagens e comunicacoes.</li>
                <li>Dados de navegacao e interacao na plataforma.</li>
            </ul>
            <p><strong>Uso dos dados:</strong></p>
            <ul>
                <li>Fornecer e melhorar nossos servicos.</li>
                <li>Comunicar-se com voce sobre sua conta.</li>
                <li>Personalizar sua experiencia na plataforma.</li>
                <li>Gerar estatisticas e relatorios anonimos.</li>
            </ul>
        </div>

        <!-- 10. Seguranca -->
        <div class="termos-section" id="seguranca">
            <h2>10. Seguranca da Informacao</h2>
            <p>
                A Aptus adota medidas de seguranca para proteger seus dados, incluindo:
            </p>
            <ul>
                <li>Criptografia de dados (SSL/TLS).</li>
                <li>Armazenamento seguro de senhas (hash e salt).</li>
                <li>Monitoramento de atividades suspeitas.</li>
                <li>Backups regulares do banco de dados.</li>
            </ul>
            <p>
                Apesar disso, nenhum sistema e 100% seguro. Recomendamos que voce tambem adote boas praticas 
                de seguranca, como senhas fortes e verificacao em duas etapas.
            </p>
        </div>

        <!-- 11. Moderacao -->
        <div class="termos-section" id="moderacao">
            <h2>11. Moderacao e Conteudo</h2>
            <p>
                A Aptus reserva-se o direito de moderar todo o conteudo publicado na plataforma.
            </p>
            <ul>
                <li>Anuncios passam por moderacao antes de serem publicados.</li>
                <li>Conteudo inadequado sera removido sem aviso previo.</li>
                <li>Usuarios que violarem as regras podem ser banidos.</li>
                <li>Denuncias serao analisadas pela equipe de moderacao.</li>
            </ul>
        </div>

        <!-- 12. Propriedade Intelectual -->
        <div class="termos-section" id="propriedade">
            <h2>12. Propriedade Intelectual</h2>
            <p>
                Todos os direitos de propriedade intelectual da plataforma Aptus pertencem a seus criadores.
            </p>
            <ul>
                <li>O codigo, design e conteudo da plataforma sao protegidos por direitos autorais.</li>
                <li>Usuarios mantem os direitos sobre seus proprios conteudos.</li>
                <li>Ao publicar na plataforma, voce concede a Aptus uma licenca para exibir seu conteudo.</li>
            </ul>
        </div>

        <!-- 13. Alteracoes -->
        <div class="termos-section" id="alteracoes">
            <h2>13. Alteracoes nos Termos</h2>
            <p>
                A Aptus pode atualizar estes termos periodicamente. As alteracoes serao comunicadas aos usuarios 
                atraves da plataforma ou por e-mail.
            </p>
            <ul>
                <li>Termos atualizados entram em vigor imediatamente.</li>
                <li>O uso continuado da plataforma implica na aceitacao dos novos termos.</li>
                <li>Recomendamos revisar esta pagina regularmente.</li>
            </ul>
        </div>

        <!-- 14. Contato -->
        <div class="termos-section" id="contato">
            <h2>14. Contato</h2>
            <p>
                Em caso de duvidas sobre estes termos, entre em contato conosco:
            </p>
            <div class="termos-contato">
                <p><i class="fas fa-envelope"></i> <a href="mailto:contato@aptus.com">contato@aptus.com</a></p>
                <p><i class="fas fa-phone"></i> <a href="tel:+5511999999999">(11) 99999-9999</a></p>
                <p><i class="fas fa-map-marker-alt"></i> Sao Paulo, SP</p>
            </div>
        </div>

        <!-- Botao Voltar -->
        <div class="termos-voltar">
            <a href="/Aptus/" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar para o Inicio
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>