<?php
// app/Views/chat/conversa.php

$tituloPagina = $tituloPagina ?? 'Chat - Aptus';
$cssPagina = $cssPagina ?? 'chat.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$conversas = $conversas ?? [];
$mensagens = $mensagens ?? [];
$interesse = $interesse ?? [];
$outroUsuario = $outroUsuario ?? [];
$usuario = $_SESSION['usuario'] ?? null;
?>

<div class="chat-full">
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h3><i class="fas fa-comments"></i> Conversas</h3>
        </div>
        <div class="chat-sidebar-lista">
            <?php if (empty($conversas)): ?>
                <p class="chat-sidebar-vazio">Nenhuma conversa</p>
            <?php else: ?>
                <?php foreach ($conversas as $conv): ?>
                    <a href="/Aptus/chat/<?= $conv['id_interesse'] ?>" 
                       class="chat-sidebar-item <?= ($conv['id_interesse'] == $interesse['id_interesse']) ? 'ativo' : '' ?>
                              <?= ($conv['nao_lidas'] ?? 0) > 0 ? 'nao-lida' : '' ?>">
                        <div class="chat-sidebar-avatar">
                            <?php if (!empty($conv['outro_usuario_foto']) && $conv['outro_usuario_foto'] != 'default.png'): ?>
                                <img src="/Aptus/public/uploads/<?= htmlspecialchars($conv['outro_usuario_foto']) ?>">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                            <?php if (($conv['nao_lidas'] ?? 0) > 0): ?>
                                <span class="badge"><?= $conv['nao_lidas'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="chat-sidebar-info">
                            <div class="chat-sidebar-nome"><?= htmlspecialchars($conv['outro_usuario_nome']) ?></div>
                            <div class="chat-sidebar-msg"><?= htmlspecialchars(mb_strimwidth($conv['ultima_mensagem'] ?? '', 0, 30, '...')) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="chat-main">
        <?php if ($interesse): ?>
            <div class="chat-main-header">
                <div class="chat-main-usuario">
                    <?php if (!empty($outroUsuario['foto_perfil']) && $outroUsuario['foto_perfil'] != 'default.png'): ?>
                        <img src="/Aptus/public/uploads/<?= htmlspecialchars($outroUsuario['foto_perfil']) ?>" 
                             alt="<?= htmlspecialchars($outroUsuario['nome']) ?>">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($outroUsuario['nome'] ?? '') ?></strong>
                        <small><?= htmlspecialchars($interesse['anuncio_titulo'] ?? '') ?></small>
                    </div>
                </div>
                <a href="/Aptus/anuncios/<?= htmlspecialchars($interesse['anuncio_slug'] ?? '') ?>" class="btn-ver-anuncio">
                    <i class="fas fa-eye"></i> Ver Anuncio
                </a>
            </div>

            <div class="chat-mensagens" id="chatMensagens">
                <?php if (empty($mensagens)): ?>
                    <div class="chat-mensagem-vazia" id="mensagemVazia">
                        <i class="fas fa-comment-dots"></i>
                        <p>Nenhuma mensagem ainda. Comece a conversa!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($mensagens as $msg): ?>
                        <div class="chat-mensagem <?= ($msg['id_remetente'] == $usuario['id']) ? 'enviada' : 'recebida' ?>" 
                             data-id="<?= $msg['id_mensagem'] ?>">
                            <div class="chat-mensagem-conteudo">
                                <div class="chat-mensagem-texto"><?= nl2br(htmlspecialchars($msg['mensagem'])) ?></div>
                                <div class="chat-mensagem-hora"><?= date('H:i', strtotime($msg['data_envio'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="chat-input-area">
                <form id="formEnviarMensagem" method="POST" autocomplete="off">
                    <input type="hidden" name="interesse_id" value="<?= $interesse['id_interesse'] ?? 0 ?>">
                    <input type="text" id="mensagemInput" name="mensagem" placeholder="Digite sua mensagem..." autocomplete="off">
                    <button type="submit" id="btnEnviar">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="chat-main-vazio">
                <i class="fas fa-comments" style="font-size: 4rem; color: #d1d5db;"></i>
                <h3>Selecione uma conversa</h3>
                <p>Escolha uma conversa na lista ao lado para começar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var interesseId = <?= $interesse['id_interesse'] ?? 0 ?>;
    var ultimoId = 0;
    var usuarioId = <?= $usuario['id'] ?? 0 ?>;
    var enviando = false;

    var container = document.getElementById('chatMensagens');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }

    var mensagensExistentes = document.querySelectorAll('.chat-mensagem[data-id]');
    if (mensagensExistentes.length > 0) {
        var ultima = mensagensExistentes[mensagensExistentes.length - 1];
        ultimoId = parseInt(ultima.dataset.id) || 0;
    }

    var form = document.getElementById('formEnviarMensagem');
    var input = document.getElementById('mensagemInput');
    var btnEnviar = document.getElementById('btnEnviar');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (enviando) {
                return;
            }

            var mensagem = input.value.trim();
            console.log('Mensagem capturada:', mensagem);
            
            if (mensagem === '') {
                console.log('Mensagem vazia, ignorando');
                return;
            }

            input.value = '';
            
            enviando = true;
            input.disabled = true;
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            var msgElement = criarMensagemTemporaria(mensagem);
            
            var vazia = document.getElementById('mensagemVazia');
            if (vazia) vazia.remove();

            container.appendChild(msgElement);
            container.scrollTop = container.scrollHeight;

            // ENVIAR COMO JSON
            var dados = {
                interesse_id: interesseId,
                mensagem: mensagem
            };
            
            console.log('Enviando JSON:', dados);
            
            fetch('/Aptus/chat/enviar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dados)
            })
            .then(function(response) { 
                return response.json(); 
            })
            .then(function(data) {
                console.log('Resposta:', data);
                if (data.success) {
                    atualizarMensagem(msgElement, data.mensagem, true);
                    if (data.mensagem && data.mensagem.id_mensagem) {
                        ultimoId = data.mensagem.id_mensagem;
                        msgElement.dataset.id = data.mensagem.id_mensagem;
                    }
                } else {
                    msgElement.querySelector('.chat-mensagem-conteudo').style.borderColor = '#ef4444';
                    msgElement.querySelector('.chat-mensagem-hora').textContent = 'Erro: ' + (data.message || 'Falha ao enviar');
                    msgElement.querySelector('.chat-mensagem-hora').style.color = '#ef4444';
                    
                    input.disabled = false;
                    btnEnviar.disabled = false;
                    btnEnviar.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    enviando = false;
                    
                    input.value = mensagem;
                }
            })
            .catch(function(error) {
                console.error('Erro:', error);
                msgElement.querySelector('.chat-mensagem-conteudo').style.borderColor = '#ef4444';
                msgElement.querySelector('.chat-mensagem-hora').textContent = 'Erro de conexao';
                msgElement.querySelector('.chat-mensagem-hora').style.color = '#ef4444';
                
                input.disabled = false;
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = '<i class="fas fa-paper-plane"></i>';
                enviando = false;
                
                input.value = mensagem;
            });
        });
    }

    function criarMensagemTemporaria(mensagem) {
        var div = document.createElement('div');
        div.className = 'chat-mensagem enviada enviando';
        div.innerHTML = `
            <div class="chat-mensagem-conteudo">
                <div class="chat-mensagem-texto">${mensagem.replace(/\n/g, '<br>').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                <div class="chat-mensagem-hora">${new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'})}</div>
            </div>
        `;
        return div;
    }

    function atualizarMensagem(elemento, msg, minha) {
        elemento.className = 'chat-mensagem ' + (minha ? 'enviada' : 'recebida');
        elemento.dataset.id = msg.id_mensagem || 0;
        
        var texto = elemento.querySelector('.chat-mensagem-texto');
        var mensagemTexto = msg.mensagem || '';
        texto.textContent = mensagemTexto;
        texto.innerHTML = mensagemTexto.replace(/\n/g, '<br>').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        
        var hora = elemento.querySelector('.chat-mensagem-hora');
        var data = new Date(msg.data_envio || Date.now());
        hora.textContent = data.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
        hora.style.color = '';
        
        var input = document.getElementById('mensagemInput');
        var btnEnviar = document.getElementById('btnEnviar');
        if (input) {
            input.disabled = false;
            input.focus();
        }
        if (btnEnviar) {
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
        enviando = false;
    }

    function buscarNovasMensagens() {
        if (interesseId === 0) return;

        fetch('/Aptus/chat/mensagens?interesse_id=' + interesseId + '&ultimo_id=' + ultimoId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.mensagens.length > 0) {
                data.mensagens.forEach(function(msg) {
                    var minha = (msg.id_remetente == usuarioId);
                    var existe = document.querySelector('.chat-mensagem[data-id="' + msg.id_mensagem + '"]');
                    if (!existe) {
                        adicionarMensagemRecebida(msg, minha);
                        if (msg.id_mensagem > ultimoId) {
                            ultimoId = msg.id_mensagem;
                        }
                    }
                });
                container.scrollTop = container.scrollHeight;
            }
        })
        .catch(function() {});
    }

    function adicionarMensagemRecebida(msg, minha) {
        var div = document.createElement('div');
        div.className = 'chat-mensagem ' + (minha ? 'enviada' : 'recebida');
        div.dataset.id = msg.id_mensagem;
        var mensagemTexto = msg.mensagem || '';
        div.innerHTML = `
            <div class="chat-mensagem-conteudo">
                <div class="chat-mensagem-texto">${mensagemTexto.replace(/\n/g, '<br>').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                <div class="chat-mensagem-hora">${new Date(msg.data_envio).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'})}</div>
            </div>
        `;
        container.appendChild(div);
        
        var vazia = document.getElementById('mensagemVazia');
        if (vazia) vazia.remove();
    }

    if (interesseId > 0) {
        setInterval(buscarNovasMensagens, 3000);
    }

    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>