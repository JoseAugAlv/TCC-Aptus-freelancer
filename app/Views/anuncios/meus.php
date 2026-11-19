<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/anuncios/meus.php

$tituloPagina = $tituloPagina ?? 'Meus Anúncios - Aptus';
$cssPagina    = $cssPagina    ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncios = $anuncios ?? [];
?>

<div class="anuncio-container">
    <div class="anuncio-header">
        <h1>Meus Anúncios</h1>
        <p>Gerencie seus serviços</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="btn-criar-container">
        <a href="<?= aptus_base() ?>/anuncios/criar" class="btn-criar">
            <i class="fas fa-plus"></i> Criar Novo Anúncio
        </a>
    </div>

    <?php if (empty($anuncios)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>Você ainda não possui anúncios.</p>
            <p style="font-size: 0.9rem; color: var(--tema-texto-suave, #94a8b4);">Comece agora mesmo criando seu primeiro serviço</p>
            <a href="<?= aptus_base() ?>/anuncios/criar" class="btn-criar">
                Criar primeiro anúncio
            </a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
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
                            <td><strong><?= htmlspecialchars($anuncio['titulo']) ?></strong></td>
                            <td><?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Sem categoria') ?></td>
                            <td class="preco">R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?></td>
                            <td>
                                <span class="status-badge <?= $anuncio['situacao'] ?>">
                                    <?= ucfirst($anuncio['situacao']) ?>
                                </span>
                                <?php if (($anuncio['id_situacao_moderacao'] ?? 0) == 1): ?>
                                    <span class="status-badge pendente" style="margin-left: 0.3rem;">Em análise</span>
                                <?php elseif (($anuncio['id_situacao_moderacao'] ?? 0) == 3): ?>
                                    <?php $__revogado = str_starts_with((string) ($anuncio['motivo_remocao'] ?? ''), 'Revogado:'); ?>
                                    <span class="status-badge rejeitado" style="margin-left: 0.3rem;"
                                          title="<?= htmlspecialchars($anuncio['motivo_remocao'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= $__revogado ? 'Revogado' : 'Rejeitado' ?>
                                    </span>
                                    <small class="motivo-moderacao">
                                        <?= htmlspecialchars($anuncio['motivo_remocao'] ?? '') ?> — edite para reenviar.
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="interesses">
                                    <i class="fas fa-heart"></i>
                                    <?= $anuncio['total_interesses'] ?? 0 ?>
                                </span>
                            </td>
                            <td class="data"><?= date('d/m/Y', strtotime($anuncio['data_criacao'])) ?></td>
                            <td>
                                <div class="acoes">
                                    <a href="<?= aptus_base() ?>/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>" class="btn-acao ver" title="Ver">
                                        <i class="fas fa-eye"></i> <span>Ver</span>
                                    </a>
                                    <a href="<?= aptus_base() ?>/anuncios/editar/<?= url_id($anuncio['id_anuncio']) ?>" class="btn-acao editar" title="Editar">
                                        <i class="fas fa-edit"></i> <span>Editar</span>
                                    </a>

                                    <?php if ($anuncio['situacao'] == 'ativo'): ?>
                                        <form method="POST" action="<?= aptus_base() ?>/anuncios/pausar" class="form-acao">
                                            <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn-acao pausar" title="Pausar">
                                                <i class="fas fa-pause"></i> <span>Pausar</span>
                                            </button>
                                        </form>
                                    <?php elseif ($anuncio['situacao'] == 'pausado'): ?>
                                        <form method="POST" action="<?= aptus_base() ?>/anuncios/ativar" class="form-acao">
                                            <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn-acao ativar" title="Ativar">
                                                <i class="fas fa-play"></i> <span>Ativar</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" action="<?= aptus_base() ?>/anuncios/excluir" class="form-acao"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este anúncio?');">
                                        <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                                        <?= CsrfMiddleware::field() ?>
                                        <button type="submit" class="btn-acao excluir" title="Excluir">
                                            <i class="fas fa-trash"></i> <span>Excluir</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="anuncio-voltar">
        <a href="<?= aptus_base() ?>/"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<style>
.form-acao {
    display: inline;
    margin: 0;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>