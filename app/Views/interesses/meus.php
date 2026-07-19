<?php
// app/Views/interesses/meus.php

$tituloPagina = $tituloPagina ?? 'Meus Interesses - Aptus';
$cssPagina = $cssPagina ?? 'interesses.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesses = $interesses ?? [];
?>

<h1>Meus Interesses</h1>
<p>Interesses que você enviou</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (empty($interesses)): ?>
    <p>Você ainda não enviou nenhum interesse.</p>
    <p><a href="/Aptus/anuncios">Explorar serviços</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Serviço</th>
                <th>Freelancer</th>
                <th>Preço</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($interesses as $interesse): ?>
                <tr>
                    <td><?= htmlspecialchars($interesse['anuncio_titulo']) ?></td>
                    <td><?= htmlspecialchars($interesse['freelancer_nome']) ?></td>
                    <td>R$ <?= number_format($interesse['anuncio_preco'], 2, ',', '.') ?></td>
                    <td>
                        <?php 
                            $status = $interesse['situacao'];
                            $cor = match($status) {
                                'ativo' => 'green',
                                'concluido' => 'blue',
                                'cancelado' => 'red',
                                default => 'gray'
                            };
                        ?>
                        <span style="color: <?= $cor ?>; font-weight: bold;">
                            <?= ucfirst($status) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($interesse['data_interesse'])) ?></td>
                    <td>
                        <a href="/Aptus/interesses/detalhes/<?= $interesse['id_interesse'] ?>">Ver</a>
                        <?php if ($interesse['situacao'] == 'ativo'): ?>
                            <form method="POST" action="/Aptus/interesses/cancelar" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $interesse['id_interesse'] ?>">
                                <button type="submit" onclick="return confirm('Cancelar este interesse?')">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p><a href="/Aptus/">Voltar</a></p>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>