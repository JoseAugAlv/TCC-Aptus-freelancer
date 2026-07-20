<?php
// app/Views/admin/configuracoes.php

$tituloPagina = $tituloPagina ?? 'Configuracoes - Aptus';
$cssPagina = $cssPagina ?? 'admin.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$configs = $configs ?? [];
?>

<div class="config-container">
    <div class="config-header">
        <h1><i class="fas fa-cogs"></i> Configuracoes</h1>
        <p>Gerencie as configuracoes do sistema</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form method="POST" action="/Aptus/admin/configuracoes/salvar" class="config-form">
        
        <!-- Configuracoes Gerais -->
        <div class="config-card">
            <h3><i class="fas fa-globe"></i> Configuracoes Gerais</h3>
            
            <div class="form-group">
                <label for="site_nome">Nome do Site</label>
                <input type="text" id="site_nome" name="site_nome" 
                       value="<?= htmlspecialchars($configs['site_nome'] ?? 'Aptus') ?>" 
                       class="form-control">
            </div>
            
            <div class="form-group">
                <label for="site_descricao">Descricao do Site</label>
                <input type="text" id="site_descricao" name="site_descricao" 
                       value="<?= htmlspecialchars($configs['site_descricao'] ?? '') ?>" 
                       class="form-control">
            </div>
            
            <div class="form-group">
                <label for="site_email">Email de Contato</label>
                <input type="email" id="site_email" name="site_email" 
                       value="<?= htmlspecialchars($configs['site_email'] ?? '') ?>" 
                       class="form-control">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="site_telefone">Telefone</label>
                    <input type="text" id="site_telefone" name="site_telefone" 
                           value="<?= htmlspecialchars($configs['site_telefone'] ?? '') ?>" 
                           class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="site_endereco">Endereco</label>
                    <input type="text" id="site_endereco" name="site_endereco" 
                           value="<?= htmlspecialchars($configs['site_endereco'] ?? '') ?>" 
                           class="form-control">
                </div>
            </div>
        </div>

        <!-- Configuracoes de Upload -->
        <div class="config-card">
            <h3><i class="fas fa-upload"></i> Configuracoes de Upload</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="upload_max_size">Tamanho Maximo (MB)</label>
                    <input type="number" id="upload_max_size" name="upload_max_size" 
                           value="<?= htmlspecialchars($configs['upload_max_size'] ?? '5') ?>" 
                           class="form-control" min="1" max="50">
                </div>
                
                <div class="form-group">
                    <label for="upload_allow_types">Tipos Permitidos</label>
                    <input type="text" id="upload_allow_types" name="upload_allow_types" 
                           value="<?= htmlspecialchars($configs['upload_allow_types'] ?? 'jpg,jpeg,png,webp,gif') ?>" 
                           class="form-control" placeholder="jpg,jpeg,png,webp,gif">
                </div>
            </div>
        </div>

        <!-- Configuracoes de Moderacao -->
        <div class="config-card">
            <h3><i class="fas fa-shield-alt"></i> Configuracoes de Moderacao</h3>
            
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="moderacao_automatica" value="1"
                           <?= ($configs['moderacao_automatica'] ?? '0') == '1' ? 'checked' : '' ?>>
                    Aprovacao automatica de anuncios
                </label>
                <small class="help-text">Se ativado, anuncios sao aprovados automaticamente sem moderacao manual.</small>
            </div>
        </div>

        <!-- Configuracoes de Seguranca -->
        <div class="config-card">
            <h3><i class="fas fa-lock"></i> Configuracoes de Seguranca</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sessao_tempo">Tempo de Sessao (segundos)</label>
                    <input type="number" id="sessao_tempo" name="sessao_tempo" 
                           value="<?= htmlspecialchars($configs['sessao_tempo'] ?? '3600') ?>" 
                           class="form-control" min="300">
                </div>
                
                <div class="form-group">
                    <label for="tentativas_login">Tentativas de Login</label>
                    <input type="number" id="tentativas_login" name="tentativas_login" 
                           value="<?= htmlspecialchars($configs['tentativas_login'] ?? '5') ?>" 
                           class="form-control" min="1">
                </div>
            </div>
        </div>

        <!-- Configuracoes de Email -->
        <div class="config-card">
            <h3><i class="fas fa-envelope"></i> Configuracoes de Email (SMTP)</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email_host">Servidor SMTP</label>
                    <input type="text" id="email_host" name="email_host" 
                           value="<?= htmlspecialchars($configs['email_host'] ?? 'smtp.gmail.com') ?>" 
                           class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="email_port">Porta</label>
                    <input type="number" id="email_port" name="email_port" 
                           value="<?= htmlspecialchars($configs['email_port'] ?? '587') ?>" 
                           class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email_from_name">Nome do Remetente</label>
                <input type="text" id="email_from_name" name="email_from_name" 
                       value="<?= htmlspecialchars($configs['email_from_name'] ?? 'Aptus') ?>" 
                       class="form-control">
            </div>
            
            <div class="form-group">
                <label for="email_user">Usuario SMTP</label>
                <input type="email" id="email_user" name="email_user" 
                       value="<?= htmlspecialchars($configs['email_user'] ?? '') ?>" 
                       class="form-control" placeholder="seuemail@gmail.com">
            </div>
            
            <div class="form-group">
                <label for="email_pass">Senha SMTP</label>
                <input type="password" id="email_pass" name="email_pass" 
                       value="<?= htmlspecialchars($configs['email_pass'] ?? '') ?>" 
                       class="form-control" placeholder="******">
                <small class="help-text">Deixe em branco para manter a senha atual.</small>
            </div>
        </div>

        <!-- Configuracoes de Manutencao -->
        <div class="config-card">
            <h3><i class="fas fa-tools"></i> Manutencao</h3>
            
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="manutencao" value="1"
                           <?= ($configs['manutencao'] ?? '0') == '1' ? 'checked' : '' ?>>
                    Ativar Modo Manutencao
                </label>
                <small class="help-text">Quando ativado, apenas administradores podem acessar o site.</small>
            </div>
            
            <div class="form-group">
                <label for="manutencao_mensagem">Mensagem de Manutencao</label>
                <textarea id="manutencao_mensagem" name="manutencao_mensagem" 
                          class="form-control" rows="3"><?= htmlspecialchars($configs['manutencao_mensagem'] ?? 'Sistema em manutencao. Volte em breve.') ?></textarea>
            </div>
        </div>

        <!-- Botoes -->
        <div class="config-actions">
            <button type="submit" class="btn-salvar">
                <i class="fas fa-save"></i> Salvar Configuracoes
            </button>
            <a href="/Aptus/admin/dashboard" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </form>
</div>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>