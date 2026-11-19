<?php
// app/Views/termos/politica_anuncios.php

$tituloPagina = $tituloPagina ?? 'Política de Anúncios - Aptus';
$cssPagina    = $cssPagina ?? 'termos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div class="termos-container">
    <div class="termos-header">
        <h1>Política de Anúncios</h1>
        <p>O que pode e o que não pode ser anunciado na Aptus</p>
        <p class="termos-data">Última atualização: 17/11/2026</p>
    </div>

    <hr>

    <div class="termos-content">

        <div class="termos-indice">
            <h3>Índice</h3>
            <ul>
                <li><a href="#objetivo">1. Objetivo</a></li>
                <li><a href="#quem-anuncia">2. Quem pode anunciar</a></li>
                <li><a href="#permitido">3. O que é permitido</a></li>
                <li><a href="#proibido">4. O que é proibido</a></li>
                <li><a href="#regulamentadas">5. Profissões regulamentadas</a></li>
                <li><a href="#conteudo">6. Regras para textos, preços e fotos</a></li>
                <li><a href="#dados">7. Dados pessoais e LGPD</a></li>
                <li><a href="#moderacao">8. Moderação, revogação e suspensão</a></li>
                <li><a href="#denuncias">9. Como denunciar</a></li>
                <li><a href="#base-legal">10. Base legal</a></li>
            </ul>
        </div>

        <div class="termos-section" id="objetivo">
            <h2>1. Objetivo</h2>
            <p>
                Esta política define quais serviços podem ser oferecidos na Aptus e como os anúncios devem ser
                escritos. Ela complementa os <a href="<?= aptus_base() ?>/termos">Termos de Uso e a Política de Privacidade</a>.
                Todo anúncio passa por moderação antes de ser publicado e pode ser revisto a qualquer momento.
            </p>
        </div>

        <div class="termos-section" id="quem-anuncia">
            <h2>2. Quem pode anunciar</h2>
            <ul>
                <li>Pessoas com 18 anos ou mais, com CPF e data de nascimento válidos no cadastro.</li>
                <li>Contas que não estejam suspensas ou banidas.</li>
                <li>O anunciante deve ser quem presta o serviço ou estar autorizado a oferecê-lo.</li>
            </ul>
        </div>

        <div class="termos-section" id="permitido">
            <h2>3. O que é permitido</h2>
            <p>Serviços lícitos prestados por pessoas, por exemplo:</p>
            <ul>
                <li>reformas, manutenção, elétrica, hidráulica, pintura, jardinagem e limpeza;</li>
                <li>design, desenvolvimento de sites, fotografia, tradução, redação e marketing;</li>
                <li>aulas particulares, consultoria e cuidados, respeitadas as exigências legais de cada atividade.</li>
            </ul>
        </div>

        <div class="termos-section" id="proibido">
            <h2>4. O que é proibido</h2>
            <p>Não é permitido anunciar, oferecer ou insinuar:</p>
            <ul>
                <li>venda de produtos (a Aptus é exclusivamente para serviços);</li>
                <li>drogas ilícitas, medicamentos controlados sem prescrição e substâncias proibidas pela Anvisa;</li>
                <li>armas, munições, explosivos ou serviços para burlar o Estatuto do Desarmamento;</li>
                <li>conteúdo sexual, serviços sexuais ou qualquer conteúdo envolvendo menores de idade;</li>
                <li>serviços que violem direitos autorais ou marcas (pirataria, desbloqueio de conteúdo pago, falsificação);</li>
                <li>fraudes: falsificação de documentos, "laranjas", lavagem de dinheiro, esquemas de pirâmide ou ganho garantido;</li>
                <li>invasão de contas, sistemas ou dispositivos, venda de dados pessoais ou de listas de contatos;</li>
                <li>serviços de cobrança vexatória, perseguição, espionagem ou monitoramento de pessoas sem consentimento;</li>
                <li>discurso de ódio, discriminação ou incitação à violência;</li>
                <li>trabalho em condições análogas à escravidão ou trabalho infantil;</li>
                <li>anúncios enganosos, com preço falso ou com promessas que não serão cumpridas.</li>
            </ul>
        </div>

        <div class="termos-section" id="regulamentadas">
            <h2>5. Profissões regulamentadas</h2>
            <p>
                Atividades que exigem registro em conselho profissional (por exemplo, engenharia, arquitetura,
                medicina, enfermagem, psicologia, advocacia e contabilidade) só podem ser anunciadas por quem
                possui o registro ativo. Informe o número do registro na descrição. Anunciar essas atividades sem
                habilitação pode configurar exercício ilegal da profissão.
            </p>
        </div>

        <div class="termos-section" id="conteudo">
            <h2>6. Regras para textos, preços e fotos</h2>
            <ul>
                <li>Descreva o serviço de forma clara e verdadeira, sem omitir informações que influenciem a contratação.</li>
                <li>O preço anunciado deve ser o valor real ou o valor inicial, deixando claro o que está incluso.</li>
                <li>Use fotos próprias do seu trabalho ou com autorização de uso. Uma delas deve ser a capa.</li>
                <li>Não publique fotos com rostos de terceiros, documentos, placas de veículos ou endereços sem autorização.</li>
                <li>Não coloque telefone, e-mail ou links externos no título, na descrição ou nas fotos: use o chat da plataforma.</li>
            </ul>
        </div>

        <div class="termos-section" id="dados">
            <h2>7. Dados pessoais e LGPD</h2>
            <p>
                Em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018):
            </p>
            <ul>
                <li>seu endereço completo não aparece no anúncio; você escolhe em Editar Perfil se mostra só a cidade ou a cidade e o bairro;</li>
                <li>CPF e data de nascimento são usados apenas para confirmar sua identidade e maioridade e nunca são exibidos;</li>
                <li>os dados de clientes recebidos por meio da plataforma só podem ser usados para prestar o serviço contratado;</li>
                <li>você pode pedir a correção ou a exclusão dos seus dados pelo canal de <a href="<?= aptus_base() ?>/contato">contato</a>.</li>
            </ul>
        </div>

        <div class="termos-section" id="moderacao">
            <h2>8. Moderação, revogação e suspensão</h2>
            <ul>
                <li><strong>Análise:</strong> todo anúncio novo fica em análise até ser aprovado pela moderação.</li>
                <li><strong>Rejeição ou revogação:</strong> um anúncio rejeitado, ou aprovado e depois revogado, sai do ar.
                    Você recebe uma notificação com o motivo. Ao editar o anúncio, ele volta para nova análise.</li>
                <li><strong>Suspensão da conta:</strong> em caso de violação, a administração pode suspender a conta por
                    24 horas, 7, 14, 30 ou 90 dias, ou 1 ano. Durante a suspensão é possível acessar a plataforma, mas
                    não anunciar nem contratar. Os anúncios ficam arquivados e voltam ao ar automaticamente ao fim do prazo.</li>
                <li><strong>Banimento:</strong> violações graves ou repetidas podem levar ao encerramento da conta.</li>
            </ul>
        </div>

        <div class="termos-section" id="denuncias">
            <h2>9. Como denunciar</h2>
            <p>
                Encontrou um anúncio que viola esta política? Use o botão <strong>Denunciar</strong> na página do
                anúncio. Você pode anexar fotos ou comprovantes e acompanhar o andamento em
                <a href="<?= aptus_base() ?>/denuncias">Minhas denúncias</a>.
            </p>
        </div>

        <div class="termos-section" id="base-legal">
            <h2>10. Base legal</h2>
            <ul>
                <li>Código de Defesa do Consumidor (Lei nº 8.078/1990) — publicidade enganosa e abusiva;</li>
                <li>Marco Civil da Internet (Lei nº 12.965/2014);</li>
                <li>Lei Geral de Proteção de Dados Pessoais (Lei nº 13.709/2018);</li>
                <li>Estatuto da Criança e do Adolescente (Lei nº 8.069/1990);</li>
                <li>Estatuto do Desarmamento (Lei nº 10.826/2003) e Lei de Drogas (Lei nº 11.343/2006);</li>
                <li>Lei de Direitos Autorais (Lei nº 9.610/1998) e Lei de Propriedade Industrial (Lei nº 9.279/1996);</li>
                <li>Lei das Contravenções Penais (Decreto-Lei nº 3.688/1941, art. 47 — exercício ilegal de profissão).</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
