<?php
// app/Views/avaliacoes/criar.php

$tituloPagina = $tituloPagina ?? 'Avaliar Serviço - Aptus';
$cssPagina = $cssPagina ?? 'avaliacoes.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesse = $interesse ?? [];
$anuncio = $this->anuncio->findById($interesse['id_anuncio'] ?? 0);
?>

<h1>Avaliar Serviço</h1>
<p>Compartilhe sua experiência com o freelancer</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div>
    <h2><?= htmlspecialchars($anuncio['titulo'] ?? '') ?></h2>
    <p><strong>Freelancer:</strong> <?= htmlspecialchars($interesse['freelancer_nome'] ?? '') ?></p>
    <p><strong>Preço:</strong> R$ <?= number_format($anuncio['preco'] ?? 0, 2, ',', '.') ?></p>
</div>

<hr>

<form method="POST" action="/Aptus/avaliacoes/salvar">
    <input type="hidden" name="interesse_id" value="<?= $interesse['id_interesse'] ?? 0 ?>">

    <div>
        <label>Nota</label>
        <div>
            <label><input type="radio" name="nota" value="1" required> 1 Estrela</label>
            <label><input type="radio" name="nota" value="2"> 2 Estrelas</label>
            <label><input type="radio" name="nota" value="3"> 3 Estrelas</label>
            <label><input type="radio" name="nota" value="4"> 4 Estrelas</label>
            <label><input type="radio" name="nota" value="5"> 5 Estrelas</label>
        </div>
    </div>

    <div>
        <label for="comentario">Comentário</label>
        <textarea id="comentario" name="comentario" rows="4" placeholder="Descreva sua experiência com o serviço..."></textarea>
    </div>

    <div>
        <button type="submit">Enviar Avaliação</button>
        <a href="/Aptus/interesses/detalhes/<?= $interesse['id_interesse'] ?? 0 ?>">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>