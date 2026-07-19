<?php
// app/Views/interesses/recebidos.php

$tituloPagina = $tituloPagina ?? 'Interesses Recebidos - Aptus';
$cssPagina = $cssPagina ?? 'interesses.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesses = $interesses ?? [];
?>

<h1>Interesses Recebidos</h1>
<p>Clientes que demonstraram interesse nos seus serviços</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (empty($interesses)): ?>
    <p>Nenhum interesse recebido ainda.</p>
    <p><a href="/Aptus/anuncios">Ver meus anúncios</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Serviço</th>
                <th>Cliente</th>
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
                    <td><?= htmlspecialchars($interesse['contratante_nome']) ?></td>
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
                            <form method="POST" action="/Aptus/interesses/concluir" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $interesse['id_interesse'] ?>">
                                <button type="submit" onclick="return confirm('Concluir este serviço?')">Concluir</button>
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