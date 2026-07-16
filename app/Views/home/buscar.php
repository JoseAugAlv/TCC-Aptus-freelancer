<?php
// app/Views/home/buscar.php

$tituloPagina = $tituloPagina ?? 'Busca - Aptus';
$cssPagina = $cssPagina ?? 'home.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$termo = htmlspecialchars($termoBuscado ?? '');
?>

<section class="busca-section">
    <div class="busca-header">
        <h1>Busca de Serviços</h1>
        
        <form action="/Aptus/buscar" method="GET" class="filtro-form">
            <div class="filtro-row">
                <input type="text" name="q" placeholder="O que você procura?" value="<?= $termo ?>">
                
                <select name="categoria">
                    <option value="0">Todas as categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>" <?= (($_GET['categoria'] ?? 0) == $cat['id_categoria']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="avaliacao">
                    <option value="0">Todas as avaliações</option>
                    <option value="4.5" <?= (($_GET['avaliacao'] ?? 0) >= 4.5) ? 'selected' : '' ?>>4.5+ estrelas</option>
                    <option value="4" <?= (($_GET['avaliacao'] ?? 0) >= 4) ? 'selected' : '' ?>>4.0+ estrelas</option>
                    <option value="3" <?= (($_GET['avaliacao'] ?? 0) >= 3) ? 'selected' : '' ?>>3.0+ estrelas</option>
                </select>
                
                <select name="ordenar">
                    <option value="recentes" <?= (($_GET['ordenar'] ?? '') == 'recentes') ? 'selected' : '' ?>>Mais recentes</option>
                    <option value="avaliacao" <?= (($_GET['ordenar'] ?? '') == 'avaliacao') ? 'selected' : '' ?>>Melhor avaliação</option>
                    <option value="preco" <?= (($_GET['ordenar'] ?? '') == 'preco') ? 'selected' : '' ?>>Menor preço</option>
                    <option value="visualizacoes" <?= (($_GET['ordenar'] ?? '') == 'visualizacoes') ? 'selected' : '' ?>>Mais vistos</option>
                </select>
                
                <button type="submit">Buscar</button>
            </div>
        </form>
    </div>
    
    <?php if (!empty($termo) || !empty($_GET['categoria'])): ?>
        <div class="resultados-header">
            <span class="total-resultados"><?= count($resultados) ?> resultado(s)</span>
        </div>
    <?php endif; ?>
    
    <div class="resultados-grid">
        <?php if (empty($resultados) && (!empty($termo) || !empty($_GET['categoria']))): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Nenhum resultado encontrado</h3>
                <p>Tente buscar com outros termos ou remova os filtros.</p>
                <a href="/Aptus/buscar" class="btn-limpar">Limpar busca</a>
            </div>
        <?php elseif (empty($resultados)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Busque por serviços</h3>
                <p>Utilize o campo acima para encontrar profissionais qualificados.</p>
            </div>
        <?php else: ?>
            <?php foreach ($resultados as $anuncio): ?>
                <div class="anuncio-card">
                    <div class="categoria-tag">
                        <?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral') ?>
                    </div>
                    
                    <h3><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                    
                    <p class="descricao">
                        <?= htmlspecialchars(mb_strimwidth($anuncio['descricao'], 0, 150, '...')) ?>
                    </p>
                    
                    <div class="info-footer">
                        <span class="freelancer">
                            <i class="fas fa-user"></i>
                            <?= htmlspecialchars($anuncio['freelancer_nome']) ?>
                        </span>
                        <span class="preco">
                            <i class="fas fa-tag"></i>
                            R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?>
                        </span>
                    </div>
                    
                    <div class="avaliacao">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= round($anuncio['nota_media'] ?? 0)): ?>
                                <i class="fas fa-star" style="color: #f59e0b;"></i>
                            <?php else: ?>
                                <i class="far fa-star" style="color: #d1d5db;"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span class="nota">(<?= number_format($anuncio['nota_media'] ?? 0, 1) ?>)</span>
                    </div>
                    
                    <div class="visualizacoes">
                        <i class="fas fa-eye"></i> <?= $anuncio['visualizacoes'] ?? 0 ?> visualizações
                    </div>
                    
                    <a href="/Aptus/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>" class="btn-ver">
                        Ver Detalhes
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>