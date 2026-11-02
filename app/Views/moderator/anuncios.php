<?php
// app/Views/moderator/anuncios.php
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

$tituloPagina = $tituloPagina ?? 'Moderar Anúncios - Aptus';
$cssPagina    = $cssPagina    ?? 'anuncios_mod.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncios = $anuncios ?? [];
$statusAtual = $_GET['status'] ?? '';
?>

<div class="anuncios-wrapper">

    <div class="anuncios-header">
        <h1>Moderar Anúncios</h1>
        <p>Gerencie todos os anúncios da plataforma</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
        <a href="/Aptus/moderator/anuncios"
           class="filtro-btn <?= $statusAtual === '' ? 'ativo' : '' ?>">Todos</a>
        <a href="/Aptus/moderator/anuncios?status=1"
           class="filtro-btn <?= $statusAtual === '1' ? 'ativo' : '' ?>">Pendentes</a>
        <a href="/Aptus/moderator/anuncios?status=2"
           class="filtro-btn <?= $statusAtual === '2' ? 'ativo' : '' ?>">Aprovados</a>
        <a href="/Aptus/moderator/anuncios?status=3"
           class="filtro-btn <?= $statusAtual === '3' ? 'ativo' : '' ?>">Rejeitados</a>
    </div>

    <?php if (empty($anuncios)): ?>
        <div class="empty-state">
            <p>Nenhum anúncio encontrado.</p>
            <a href="/Aptus/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Freelancer</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anuncios as $anuncio): ?>
                        <tr>
                            <td><?= htmlspecialchars($anuncio['titulo']) ?></td>
                            <td><?= htmlspecialchars($anuncio['categoria_nome']) ?></td>
                            <td><?= htmlspecialchars($anuncio['freelancer_nome']) ?></td>
                            <td>R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($anuncio['situacao_moderacao'] ?? '') ?>">
                                    <?= htmlspecialchars($anuncio['situacao_moderacao'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($anuncio['data_criacao'])) ?></td>
                            <td>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    <?php if ((int) $anuncio['id_situacao_moderacao'] === 1): ?>
                                        <form method="POST" action="/Aptus/moderator/anuncios/aprovar">
                                            <input type="hidden" name="id" value="<?= (int) $anuncio['id_anuncio'] ?>">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn-aprovar"
                                                    onclick="return confirm('Aprovar este anúncio?')">Aprovar</button>
                                        </form>

                                        <form method="POST" action="/Aptus/moderator/anuncios/rejeitar">
                                            <input type="hidden" name="id" value="<?= (int) $anuncio['id_anuncio'] ?>">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn-rejeitar"
                                                    onclick="return confirm('Rejeitar este anúncio?')">Rejeitar</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($anuncio['situacao'] === 'ativo'): ?>
                                        <form method="POST" action="/Aptus/moderator/anuncios/suspender">
                                            <input type="hidden" name="id" value="<?= (int) $anuncio['id_anuncio'] ?>">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn-rejeitar"
                                                    style="background:#f59e0b;"
                                                    onclick="return confirm('Suspender este anúncio?')">Suspender</button>
                                        </form>
                                    <?php endif; ?>

                                    <a href="/Aptus/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>"
                                       class="btn-aprovar" style="background:#3b82f6; text-decoration:none;">Ver</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="voltar-wrapper">
            <a href="/Aptus/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>