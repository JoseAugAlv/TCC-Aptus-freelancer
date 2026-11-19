<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/home/buscar.php

$tituloPagina = $tituloPagina ?? 'Busca Avançada - Aptus';
$cssPagina = $cssPagina ?? 'home.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$resultados = $resultados ?? [];
$categorias = $categorias ?? [];
$termo = $termo ?? '';
$filtroCategoria = $filtroCategoria ?? 0;
$filtroAvaliacao = $filtroAvaliacao ?? 0;
$filtroPrecoMin = $filtroPrecoMin ?? 0;
$filtroPrecoMax = $filtroPrecoMax ?? 0;
$ordenar = $ordenar ?? 'recentes';
?>

<div class="busca-container">
    <div class="busca-header">
        <h1>Busca Avançada</h1>
        <p>Encontre os melhores serviços com filtros personalizados</p>
    </div>

    <hr>

    <div class="busca-filtros">
        <form method="GET" action="<?= aptus_base() ?>/buscar" class="filtros-form">
            <div class="filtros-row">
                <div class="filtro-group">
                    <label for="q">O que você procura?</label>
                    <input type="text" id="q" name="q" value="<?= htmlspecialchars($termo) ?>" placeholder="Digite palavras-chave...">
                </div>

                <div class="filtro-group">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= url_id($cat['id_categoria']) ?>" <?= ((int) $filtroCategoria === (int) $cat['id_categoria']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filtro-group">
                    <label for="avaliacao">Avaliação mínima</label>
                    <select id="avaliacao" name="avaliacao">
                        <option value="0">Todas</option>
                        <option value="4.5" <?= ($filtroAvaliacao == 4.5) ? 'selected' : '' ?>>4.5+ estrelas</option>
                        <option value="4.0" <?= ($filtroAvaliacao == 4) ? 'selected' : '' ?>>4.0+ estrelas</option>
                        <option value="3.5" <?= ($filtroAvaliacao == 3.5) ? 'selected' : '' ?>>3.5+ estrelas</option>
                        <option value="3.0" <?= ($filtroAvaliacao == 3) ? 'selected' : '' ?>>3.0+ estrelas</option>
                        <option value="2.0" <?= ($filtroAvaliacao == 2) ? 'selected' : '' ?>>2.0+ estrelas</option>
                    </select>
                </div>

                <div class="filtro-group">
                    <label for="ordenar">Ordenar por</label>
                    <select id="ordenar" name="ordenar">
                        <option value="recentes" <?= ($ordenar == 'recentes') ? 'selected' : '' ?>>Mais recentes</option>
                        <option value="avaliacao" <?= ($ordenar == 'avaliacao') ? 'selected' : '' ?>>Melhor avaliados</option>
                        <option value="preco_asc" <?= ($ordenar == 'preco_asc') ? 'selected' : '' ?>>Menor preço</option>
                        <option value="preco_desc" <?= ($ordenar == 'preco_desc') ? 'selected' : '' ?>>Maior preço</option>
                        <option value="visualizacoes" <?= ($ordenar == 'visualizacoes') ? 'selected' : '' ?>>Mais vistos</option>
                    </select>
                </div>
            </div>

            <div class="filtros-row">
                <div class="filtro-group">
                    <label for="preco_min">Preço mínimo (R$)</label>
                    <input type="number" id="preco_min" name="preco_min" value="<?= $filtroPrecoMin ?>" placeholder="0" min="0" step="10">
                </div>

                <div class="filtro-group">
                    <label for="preco_max">Preço máximo (R$)</label>
                    <input type="number" id="preco_max" name="preco_max" value="<?= $filtroPrecoMax ?>" placeholder="1000" min="0" step="10">
                </div>

                <div class="filtro-group filtro-actions">
                    <button type="submit" class="btn-buscar">Buscar</button>
                    <a href="<?= aptus_base() ?>/buscar" class="btn-limpar">Limpar filtros</a>
                </div>
            </div>
</form>
    </div>

    <?php if (!empty($termo) || $filtroCategoria > 0 || $filtroAvaliacao > 0 || $filtroPrecoMin > 0 || $filtroPrecoMax > 0): ?>
        <div class="resultados-header">
            <span class="total-resultados"><?= (int) ($totalResultados ?? count($resultados)) ?> resultado(s) encontrado(s)</span>
            <a href="<?= aptus_base() ?>/buscar" class="btn-limpar-pequeno">Limpar busca</a>
        </div>
    <?php endif; ?>

    <div class="resultados-grid">
        <?php if (empty($resultados) && (!empty($termo) || $filtroCategoria > 0 || $filtroAvaliacao > 0 || $filtroPrecoMin > 0 || $filtroPrecoMax > 0)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Nenhum resultado encontrado</h3>
                <p>Tente buscar com outros termos ou remova os filtros.</p>
                <a href="<?= aptus_base() ?>/buscar" class="btn-limpar">Limpar busca</a>
            </div>
        <?php elseif (empty($resultados)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Busque por serviços</h3>
                <p>Utilize os filtros acima para encontrar profissionais qualificados.</p>
            </div>
        <?php else: ?>
            <?php foreach ($resultados as $anuncio): ?>
                <div class="anuncio-card">
                    <div class="anuncio-imagem">
                        <?php if (!empty($anuncio['foto_capa'])): ?>
                            <img src="<?= htmlspecialchars(\UploadHelper::capaUrl($anuncio['foto_capa']), ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($anuncio['titulo']) ?>">
                        <?php else: ?>
                            <i class="fas fa-briefcase"></i>
                        <?php endif; ?>
                        <span class="categoria-tag">
                            <?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral') ?>
                        </span>
                    </div>
                    
                    <div class="anuncio-info">
                        <h3><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                        
                        <div class="anuncio-avaliacao">
                            <?php $estrelasNota = $anuncio['nota_anuncio'] ?? 0; $estrelasTotal = $anuncio['total_avaliacoes_anuncio'] ?? 0; require __DIR__ . '/../partials/estrelas.php'; ?>
                        </div>
                        
                        <p class="descricao">
                            <?= htmlspecialchars(mb_strimwidth($anuncio['descricao'], 0, 120, '...')) ?>
                        </p>
                        
                        <div class="anuncio-footer">
                            <span class="freelancer">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($anuncio['freelancer_nome']) ?>
                            </span>
                            <span class="preco">
                                R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?>
                            </span>
                        </div>
                        
                        <div class="anuncio-stats">
                            <span><i class="fas fa-eye"></i> <?= $anuncio['visualizacoes'] ?? 0 ?></span>
                            <span><i class="fas fa-handshake"></i> <?= $anuncio['total_interesses'] ?? 0 ?></span>
                        </div>
                        
                        <a href="<?= aptus_base() ?>/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>" class="btn-ver">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (($totalPaginas ?? 1) > 1): ?>
        <?php
            $__qs = $_GET;
            $__link = function (int $p) use ($__qs) {
                $__qs['pagina'] = $p;
                return (aptus_base() . '/buscar?') . htmlspecialchars(http_build_query($__qs), ENT_QUOTES, 'UTF-8');
            };
        ?>
        <nav class="paginacao" aria-label="Paginação dos resultados">
            <?php if ($paginaAtual > 1): ?>
                <a href="<?= $__link($paginaAtual - 1) ?>" class="btn-limpar-pequeno" rel="prev">&laquo; Anterior</a>
            <?php endif; ?>
            <span class="total-resultados">Página <?= (int) $paginaAtual ?> de <?= (int) $totalPaginas ?></span>
            <?php if ($paginaAtual < $totalPaginas): ?>
                <a href="<?= $__link($paginaAtual + 1) ?>" class="btn-limpar-pequeno" rel="next">Próxima &raquo;</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>