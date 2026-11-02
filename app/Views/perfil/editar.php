<?php
// app/Views/perfil/editar.php
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

$tituloPagina = $tituloPagina ?? 'Editar Perfil - Aptus';
$cssPagina    = $cssPagina    ?? 'perfil.css';
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
        <div class="flash-message flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="perfil-card">
        <form method="POST" action="/Aptus/perfil/atualizar" class="perfil-form" enctype="multipart/form-data">

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
                        <input type="file" id="foto_perfil" name="foto_perfil"
                               accept="image/*" style="display: none;">
                        <p class="foto-helper">Formatos: JPG, PNG, WEBP. Máximo: 2MB</p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control"
                       value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" class="form-control"
                       value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>"
                       placeholder="(11) 99999-9999">
            </div>

            <div class="form-group">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="whatsapp" class="form-control"
                       value="<?= htmlspecialchars($usuario['whatsapp'] ?? '') ?>"
                       placeholder="(11) 99999-9999">
            </div>

            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" class="form-control"
                       value="<?= htmlspecialchars($usuario['cep'] ?? '') ?>"
                       placeholder="00000-000" maxlength="9" data-mask="cep">
                <small id="cep-status" style="color:#64748b; font-size:0.8rem; display:block; margin-top:4px;"></small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" class="form-control"
                           value="<?= htmlspecialchars($usuario['cidade'] ?? '') ?>"
                           placeholder="Sua cidade">
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <input type="text" id="estado" name="estado" class="form-control"
                           value="<?= htmlspecialchars($usuario['estado'] ?? '') ?>"
                           placeholder="SP" maxlength="2">
                </div>
            </div>

            <div class="form-group">
                <label for="bio">Biografia</label>
                <textarea id="bio" name="bio" class="form-control" rows="4"
                          placeholder="Conte um pouco sobre você..."><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-salvar">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
                <a href="/Aptus/perfil" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
            <?= CsrfMiddleware::field() ?>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Preview foto
    var inputFoto = document.getElementById('foto_perfil');
    var preview   = document.getElementById('fotoPreview');
    if (inputFoto && preview) {
        inputFoto.addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) { preview.src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }

    // Busca ViaCEP
    var cepInput = document.getElementById('cep');
    var status   = document.getElementById('cep-status');
    if (!cepInput) return;

    cepInput.addEventListener('blur', function () {
        var cep = this.value.replace(/\D/g, '');
        if (cep.length !== 8) return;

        status.textContent = 'Buscando endereço…';
        status.style.color = '#64748b';

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.erro) {
                    status.textContent = 'CEP não encontrado.';
                    status.style.color = '#dc2626';
                    return;
                }
                status.textContent = data.logradouro + ', ' + data.bairro + ' — ' + data.localidade + '/' + data.uf;
                status.style.color = '#10b981';

                var cid = document.getElementById('cidade');
                var est = document.getElementById('estado');
                if (cid) cid.value = data.localidade;
                if (est) est.value = data.uf;
            })
            .catch(function () {
                status.textContent = 'Erro ao buscar CEP.';
                status.style.color = '#dc2626';
            });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>