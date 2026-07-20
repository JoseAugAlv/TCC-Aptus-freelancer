<?php
// app/Views/disputas/criar.php

$tituloPagina = $tituloPagina ?? 'Abrir Disputa - Aptus';
$cssPagina = $cssPagina ?? 'disputas.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesse = $interesse ?? [];
$motivos = $motivos ?? [];
?>

<div class="disputa-container">
    <div class="disputa-header">
        <h1>Abrir Disputa</h1>
        <p>Resolva conflitos relacionados ao servico</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="disputa-card">
        <div class="disputa-info">
            <p><strong>Servico:</strong> <?= htmlspecialchars($interesse['anuncio_titulo'] ?? 'N/A') ?></p>
            <p><strong>Freelancer:</strong> <?= htmlspecialchars($interesse['freelancer_nome'] ?? 'N/A') ?></p>
            <p><strong>Cliente:</strong> <?= htmlspecialchars($interesse['contratante_nome'] ?? 'N/A') ?></p>
            <p><strong>Valor:</strong> R$ <?= number_format($interesse['anuncio_preco'] ?? 0, 2, ',', '.') ?></p>
        </div>

        <div class="disputa-aviso">
            <p><i class="fas fa-exclamation-triangle"></i> A disputa sera analisada por um moderador. Ambos os lados serao notificados.</p>
        </div>

        <form method="POST" action="/Aptus/disputas/salvar">
            <input type="hidden" name="interesse_id" value="<?= $interesse['id_interesse'] ?? 0 ?>">

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
                <label for="descricao">Descricao *</label>
                <textarea id="descricao" name="descricao" rows="5" 
                          placeholder="Descreva detalhadamente o problema..." required></textarea>
            </div>

            <div class="disputa-acoes">
                <button type="submit" class="btn-enviar">
                    <i class="fas fa-gavel"></i> Abrir Disputa
                </button>
                <a href="/Aptus/interesses/ativos" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>