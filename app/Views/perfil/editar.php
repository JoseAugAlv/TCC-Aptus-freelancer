<?php
// app/Views/perfil/editar.php

$tituloPagina = $tituloPagina ?? 'Editar Perfil - Aptus';
$cssPagina = $cssPagina ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuario = $usuarioData ?? $_SESSION['usuario'];
?>

<div class="perfil-container">
    <div class="perfil-header">
        <h1><i class="fas fa-edit"></i> Editar Perfil</h1>
        <a href="/Aptus/perfil" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="perfil-card">
        <form method="POST" action="/Aptus/perfil/atualizar" class="perfil-form" enctype="multipart/form-data">
            
            <!-- Foto de Perfil com Preview -->
            <div class="form-group foto-perfil-group">
                <label>Foto de Perfil</label>
                <div class="foto-preview-container">
                    <div class="foto-preview">
                        <?php if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] != 'default.png'): ?>
                            <img src="/Aptus/public/<?= htmlspecialchars($usuario['foto_perfil']) ?>" 
                                 alt="Foto de perfil" id="fotoPreview">
                        <?php else: ?>
                            <img src="/Aptus/public/images/default-avatar.png" 
                                 alt="Foto de perfil" id="fotoPreview">
                        <?php endif; ?>
                    </div>
                    <div class="foto-upload">
                        <label for="foto_perfil" class="btn-upload">
                            <i class="fas fa-camera"></i> Escolher foto
                        </label>
                        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display: none;">
                        <p class="foto-helper">Formatos: JPG, PNG, WEBP. Maximo: 2MB</p>
                    </div>
                </div>
            </div>

            <!-- Demais campos -->
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" class="form-control" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" placeholder="(11) 99999-9999">
            </div>

            <div class="form-group">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="whatsapp" class="form-control" value="<?= htmlspecialchars($usuario['whatsapp'] ?? '') ?>" placeholder="(11) 99999-9999">
            </div>

            <div class="form-group">
                <label for="bio">Biografia</label>
                <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Conte um pouco sobre voce..."><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" class="form-control" value="<?= htmlspecialchars($usuario['cidade'] ?? '') ?>" placeholder="Sua cidade">
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <input type="text" id="estado" name="estado" class="form-control" value="<?= htmlspecialchars($usuario['estado'] ?? '') ?>" placeholder="SP" maxlength="2">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-salvar">
                    <i class="fas fa-save"></i> Salvar Alteracoes
                </button>
                <a href="/Aptus/perfil" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview da foto de perfil
    var inputFoto = document.getElementById('foto_perfil');
    var preview = document.getElementById('fotoPreview');
    
    if (inputFoto && preview) {
        inputFoto.addEventListener('change', function(e) {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>