<?php
// app/Views/denuncias/criar.php

$tituloPagina = $tituloPagina ?? 'Denunciar - Aptus';
$cssPagina = $cssPagina ?? 'denuncias.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$tipo = $_GET['tipo'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$motivos = $motivos ?? [];
?>

<div class="denuncia-container">
    <div class="denuncia-header">
        <h1><i class="fas fa-flag"></i> Denunciar</h1>
        <p>Denuncie conteudo inadequado para a moderacao</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="denuncia-card">
        <form method="POST" action="/Aptus/denuncias/salvar">
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="denuncia-info">
                <p><strong>Tipo:</strong> <?= $tipo === 'anuncio' ? 'Anuncio' : 'Perfil' ?></p>
                <p><strong>ID:</strong> #<?= $id ?></p>
            </div>

            <div class="form-group">
                <label for="motivo">Motivo *</label>
                <select id="motivo" name="motivo" required>
                    <option value="">Selecione um motivo</option>
                    <?php foreach ($motivos as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>">
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="descricao">Descricao (opcional)</label>
                <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva detalhadamente o problema..."></textarea>
            </div>

            <div class="denuncia-acoes">
                <button type="submit" class="btn-enviar">
                    <i class="fas fa-paper-plane"></i> Enviar Denuncia
                </button>
                <a href="javascript:history.back()" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

    <div class="denuncia-aviso">
        <i class="fas fa-info-circle"></i>
        <p>Denuncias falsas podem resultar em penalidades. Denuncie apenas conteudo que viole as regras da plataforma.</p>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>