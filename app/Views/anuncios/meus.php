<?php
// app/Views/anuncios/meus.php

$tituloPagina = $tituloPagina ?? 'Meus Anúncios - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncios = $anuncios ?? [];
?>

<h1>Meus Anúncios</h1>
<p>Gerencie seus serviços</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div>
    <a href="/Aptus/anuncios/criar">+ Criar Novo Anúncio</a>
</div>

<?php if (empty($anuncios)): ?>
    <p>Você ainda não possui anúncios.</p>
    <p><a href="/Aptus/anuncios/criar">Criar primeiro anúncio</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Título</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Status</th>
                <th>Interesses</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($anuncios as $anuncio): ?>
                <tr>
                    <td><?= htmlspecialchars($anuncio['titulo']) ?></td>
                    <td><?= htmlspecialchars($anuncio['categoria_nome']) ?></td>
                    <td>R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?></td>
                    <td>
                        <?php 
                            $cor = match($anuncio['situacao']) {
                                'ativo' => 'green',
                                'pausado' => 'orange',
                                'excluido' => 'red',
                                default => 'gray'
                            };
                        ?>
                        <span style="color: <?= $cor ?>; font-weight: bold;">
                            <?= ucfirst($anuncio['situacao']) ?>
                        </span>
                        <?php if ($anuncio['id_situacao_moderacao'] == 1): ?>
                            <span style="color: orange; font-size: 0.8rem;">(Pendente)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $anuncio['total_interesses'] ?? 0 ?></td>
                    <td><?= date('d/m/Y', strtotime($anuncio['data_criacao'])) ?></td>
                    <td>
                        <a href="/Aptus/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>">Ver</a>
                        <a href="/Aptus/anuncios/editar/<?= $anuncio['id_anuncio'] ?>">Editar</a>
                        <?php if ($anuncio['situacao'] == 'ativo'): ?>
                            <form method="POST" action="/Aptus/anuncios/pausar" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                                <button type="submit">Pausar</button>
                            </form>
                        <?php elseif ($anuncio['situacao'] == 'pausado'): ?>
                            <form method="POST" action="/Aptus/anuncios/ativar" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                                <button type="submit">Ativar</button>
                            </form>
                        <?php endif; ?>
                        <a href="/Aptus/anuncios/excluir/<?= $anuncio['id_anuncio'] ?>" onclick="return confirm('Tem certeza que deseja excluir este anúncio?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p><a href="/Aptus/">Voltar</a></p>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>