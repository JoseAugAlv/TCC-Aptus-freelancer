<?php
// app/Views/avaliacoes/responder.php

$tituloPagina = $tituloPagina ?? 'Responder Avaliação - Aptus';
$cssPagina = $cssPagina ?? 'avaliacoes.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$avaliacao = $avaliacao ?? [];
?>

<h1>Responder Avaliação</h1>
<p>Responda ao comentário do cliente</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div>
    <h3>Avaliação de <?= htmlspecialchars($avaliacao['avaliador_nome'] ?? '') ?></h3>
    <p><strong>Nota:</strong> <?= $avaliacao['nota'] ?? 0 ?> estrelas</p>
    <p><strong>Comentário:</strong> <?= nl2br(htmlspecialchars($avaliacao['comentario'] ?? '')) ?></p>
</div>

<hr>

<form method="POST" action="/Aptus/avaliacoes/salvar-resposta">
    <input type="hidden" name="avaliacao_id" value="<?= $avaliacao['id_avaliacao'] ?? 0 ?>">

    <div>
        <label for="resposta">Sua Resposta</label>
        <textarea id="resposta" name="resposta" rows="4" placeholder="Escreva sua resposta ao cliente..." required></textarea>
    </div>

    <div>
        <button type="submit">Enviar Resposta</button>
        <a href="/Aptus/interesses/detalhes/<?= $avaliacao['id_interesse'] ?? 0 ?>">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>