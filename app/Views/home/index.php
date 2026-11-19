<?php
// app/Views/home/index.php
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../Models/Categoria.php';
require_once __DIR__ . '/../../Helpers/UploadHelper.php';

$tituloPagina = $tituloPagina ?? 'Aptus - Conectando Talentos';
$cssPagina    = $cssPagina    ?? 'home.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuario = $_SESSION['usuario'] ?? null;
$__categorias = (new Categoria())->getAll();
?>

<main id="inicio">
    <section class="hero-section">
        <div class="hero-content">
            <h1>Conectando Talentos</h1>
            <p>Encontre os melhores profissionais para seus projetos ou publique o que você precisa</p>

            <div class="search-section">
                <form action="<?= aptus_base() ?>/buscar" method="GET" class="search-container">
                    <input type="text" name="q" class="search-input" id="searchInput"
                           placeholder="O que você está procurando?"
                           aria-label="Campo de busca de serviços"
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="button" class="btn-filtros" id="btnFiltros">
                        <i class="fas fa-sliders-h"></i> Filtros
                    </button>
                    <button type="submit" class="btn-search-submit" aria-label="Buscar">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <span class="btn-search-texto">Buscar</span>
                    </button>
                </form>

                <div class="filtros-section" id="filtrosSection" style="display: none;">
                    <form id="formFiltros" action="<?= aptus_base() ?>/buscar" method="GET">
                        <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

                        <h3><i class="fas fa-star"></i> Nível de Avaliação</h3>
                        <div class="filtro-grupo">
                            <label><input type="radio" name="avaliacao" value="5"> 5 estrelas</label>
                            <label><input type="radio" name="avaliacao" value="4"> 4 estrelas ou mais</label>
                            <label><input type="radio" name="avaliacao" value="3"> 3 estrelas ou mais</label>
                        </div>

                        <h3><i class="fas fa-tags"></i> Categoria</h3>
                        <div class="filtro-grupo">
                            <select name="categoria" style="width:100%; padding:8px; border-radius:6px;">
                                <option value="">Todas</option>
                                <?php foreach ($__categorias as $cat): ?>
                                    <option value="<?= url_id($cat['id_categoria']) ?>">
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <h3><i class="fas fa-dollar-sign"></i> Faixa de Preço (R$)</h3>
                        <div class="filtro-grupo">
                            <input type="number" name="preco_min" min="0" step="10" placeholder="Mínimo"
                                   aria-label="Preço mínimo" style="width:100%; padding:8px; border-radius:6px;">
                            <input type="number" name="preco_max" min="0" step="10" placeholder="Máximo"
                                   aria-label="Preço máximo" style="width:100%; padding:8px; border-radius:6px;">
                        </div>

                        <h3><i class="fas fa-sort-amount-down"></i> Ordenar</h3>
                        <div class="filtro-grupo">
                            <select name="ordenar" style="width:100%; padding:8px; border-radius:6px;">
                                <option value="recentes">Mais recentes</option>
                                <option value="avaliacao">Melhor avaliados</option>
                                <option value="preco_asc">Menor preço</option>
                                <option value="preco_desc">Maior preço</option>
                            </select>
                        </div>

                        <div class="filtro-acoes">
                            <button type="submit" class="btn-aplicar-filtros">Aplicar Filtros</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="categorias-section" id="servicos">
        <h2>Categorias Populares</h2>
        <div class="categorias-grid">
            <?php if (!empty($categoriasPopulares)): ?>
                <?php foreach ($categoriasPopulares as $cat): ?>
                    <a href="<?= aptus_base() ?>/buscar?categoria=<?= url_id($cat['id_categoria']) ?>" class="categoria-card">
                        <div class="icone">
                            <i class="<?= htmlspecialchars($cat['icone'] ?? 'fas fa-tag') ?>"></i>
                        </div>
                        <h3><?= htmlspecialchars($cat['nome']) ?></h3>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; color: var(--cor-texto-secundario);">
                    Nenhuma categoria cadastrada.
                </p>
            <?php endif; ?>
        </div>
    </section>

    <section class="profissionais-section" id="servicos-recentes">
        <div class="container">
            <h2><i class="fas fa-star"></i> Serviços em Destaque</h2>
            <div class="profissionais-grid">
                <?php if (!empty($anunciosDestaque)): ?>
                    <?php foreach ($anunciosDestaque as $anuncio): ?>
                        <div class="profissional-card">
                            <a href="<?= aptus_base() ?>/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>" class="card-capa" tabindex="-1" aria-hidden="true">
                                <img src="<?= htmlspecialchars(\UploadHelper::capaUrl($anuncio['foto_capa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                     alt="" loading="lazy">
                            </a>
                            <div class="categoria-tag">
                                <i class="<?= htmlspecialchars($anuncio['categoria_icone'] ?? 'fas fa-tag') ?>"></i>
                                <?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral') ?>
                            </div>
                            <h3><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                            <p class="descricao">
                                <?= htmlspecialchars(mb_strimwidth($anuncio['descricao'], 0, 120, "...")) ?>
                            </p>
                            <div class="info-footer">
                                <span class="autor">
                                    <i class="fas fa-user-circle"></i>
                                    <?= htmlspecialchars($anuncio['freelancer_nome']) ?>
                                </span>
                                <span class="preco">
                                    <i class="fas fa-tag"></i>
                                    R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?>
                                </span>
                            </div>

                            <div class="card-nota">
                                <?php $estrelasNota = $anuncio['nota_anuncio'] ?? 0; $estrelasTotal = $anuncio['total_avaliacoes_anuncio'] ?? 0; require __DIR__ . '/../partials/estrelas.php'; ?>
                            </div>

                            <div style="display: flex; gap: 10px; margin-top: 5px;">
                                <a href="<?= aptus_base() ?>/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>"
                                   class="btn-perfil" style="flex: 1; margin-top: 0;">
                                    <i class="fas fa-eye"></i> Ver Detalhes
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="msg-vazia">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <p>Nenhum serviço disponível no momento. Volte em breve!</p>
                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?= aptus_base() ?>/buscar" class="btn-perfil" style="display: inline-block; width: auto; padding: 0.8rem 2.5rem;">
                    <i class="fas fa-arrow-right"></i> Ver Todos os Serviços
                </a>
            </div>
        </div>
    </section>
</main>

<?php if ($flash): ?>
    <div class="flash-<?= htmlspecialchars($flash['tipo']) ?>"
         style="position: fixed; top: 80px; right: 20px; z-index: 9999; padding: 1rem 2rem;
                border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                max-width: 400px; animation: slideInRight 0.5s ease;">
        <?= htmlspecialchars($flash['mensagem']) ?>
    </div>
<?php endif; ?>

<?php $jsPagina = 'home.js'; require_once __DIR__ . '/../layouts/footer.php'; ?>