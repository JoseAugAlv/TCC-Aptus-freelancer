<?php
// app/Views/home/buscar.php

$tituloPagina = $tituloPagina ?? 'Busca Avancada - Aptus';
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
        <h1>Busca Avancada</h1>
        <p>Encontre os melhores servicos com filtros personalizados</p>
    </div>

    <hr>

    <div class="busca-filtros">
        <form method="GET" action="/Aptus/buscar" class="filtros-form">
            <div class="filtros-row">
                <div class="filtro-group">
                    <label for="q">O que voce procura?</label>
                    <input type="text" id="q" name="q" value="<?= htmlspecialchars($termo) ?>" placeholder="Digite palavras-chave...">
                </div>

                <div class="filtro-group">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria">
                        <option value="0">Todas as categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>" <?= ($filtroCategoria == $cat['id_categoria']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filtro-group">
                    <label for="avaliacao">Avaliacao minima</label>
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
                        <option value="preco_asc" <?= ($ordenar == 'preco_asc') ? 'selected' : '' ?>>Menor preco</option>
                        <option value="preco_desc" <?= ($ordenar == 'preco_desc') ? 'selected' : '' ?>>Maior preco</option>
                        <option value="visualizacoes" <?= ($ordenar == 'visualizacoes') ? 'selected' : '' ?>>Mais vistos</option>
                    </select>
                </div>
            </div>

            <div class="filtros-row">
                <div class="filtro-group">
                    <label for="preco_min">Preco minimo (R$)</label>
                    <input type="number" id="preco_min" name="preco_min" value="<?= $filtroPrecoMin ?>" placeholder="0" min="0" step="10">
                </div>

                <div class="filtro-group">
                    <label for="preco_max">Preco maximo (R$)</label>
                    <input type="number" id="preco_max" name="preco_max" value="<?= $filtroPrecoMax ?>" placeholder="1000" min="0" step="10">
                </div>

                <div class="filtro-group filtro-actions">
                    <button type="submit" class="btn-buscar">Buscar</button>
                    <a href="/Aptus/buscar" class="btn-limpar">Limpar filtros</a>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($termo) || $filtroCategoria > 0 || $filtroPrecoMin > 0 || $filtroPrecoMax > 0): ?>
        <div class="resultados-header">
            <span class="total-resultados"><?= count($resultados) ?> resultado(s) encontrado(s)</span>
            <a href="/Aptus/buscar" class="btn-limpar-pequeno">Limpar busca</a>
        </div>
    <?php endif; ?>

    <div class="resultados-grid">
        <?php if (empty($resultados) && (!empty($termo) || $filtroCategoria > 0 || $filtroPrecoMin > 0 || $filtroPrecoMax > 0)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Nenhum resultado encontrado</h3>
                <p>Tente buscar com outros termos ou remova os filtros.</p>
                <a href="/Aptus/buscar" class="btn-limpar">Limpar busca</a>
            </div>
        <?php elseif (empty($resultados)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Busque por servicos</h3>
                <p>Utilize os filtros acima para encontrar profissionais qualificados.</p>
            </div>
        <?php else: ?>
            <?php foreach ($resultados as $anuncio): ?>
                <div class="anuncio-card">
                    <div class="anuncio-imagem">
                        <?php if (!empty($anuncio['foto_capa'])): ?>
                            <img src="/Aptus/public/uploads/anuncios/<?= htmlspecialchars($anuncio['foto_capa']) ?>" 
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
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= round($anuncio['nota_media'] ?? 0)): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span class="nota"><?= number_format($anuncio['nota_media'] ?? 0, 1) ?></span>
                            <span class="total">(<?= $anuncio['total_avaliacoes'] ?? 0 ?>)</span>
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
                        
                        <a href="/Aptus/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>" class="btn-ver">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>