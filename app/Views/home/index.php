<?php
// app/Views/home/index.php

$tituloPagina = $tituloPagina ?? 'Aptus - Conectando Talentos';
$cssPagina = $cssPagina ?? 'home.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuario = $_SESSION['usuario'] ?? null;
?>

<main id="inicio">
    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Conectando Talentos</h1>
            <p>Encontre os melhores profissionais para seus projetos ou publique o que você precisa</p>

            <div class="search-section">
                <form action="/Aptus/buscar" method="GET" class="search-container">
                    <input type="text" name="q" class="search-input" id="searchInput"
                        placeholder="O que você está procurando?" aria-label="Campo de busca de serviços"
                        value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    <button type="button" class="btn-filtros" id="btnFiltros">
                        <i class="fas fa-sliders-h"></i> Filtros
                    </button>
                    <button type="submit" class="btn-search-submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <div class="filtros-section" id="filtrosSection" style="display: none;">
                    <form id="formFiltros" action="/Aptus/buscar" method="GET">
                        <h3><i class="fas fa-star"></i> Nível de Avaliação</h3>
                        <div class="filtro-grupo">
                            <label><input type="radio" name="avaliacao" value="5"> 5 estrelas</label>
                            <label><input type="radio" name="avaliacao" value="4"> 4 estrelas ou mais</label>
                            <label><input type="radio" name="avaliacao" value="3"> 3 estrelas ou mais</label>
                        </div>

                        <h3><i class="fas fa-tools"></i> Tipo de Serviço</h3>
                        <div class="filtro-grupo">
                            <label><input type="checkbox" name="servico[]" value="eletricista"> Eletricista</label>
                            <label><input type="checkbox" name="servico[]" value="encanador"> Encanador</label>
                            <label><input type="checkbox" name="servico[]" value="diarista"> Diarista</label>
                            <label><input type="checkbox" name="servico[]" value="pedreiro"> Pedreiro</label>
                            <label><input type="checkbox" name="servico[]" value="pintor"> Pintor</label>
                            <label><input type="checkbox" name="servico[]" value="cuidador"> Cuidador de idosos</label>
                            <label><input type="checkbox" name="servico[]" value="jardineiro"> Jardineiro</label>
                            <label><input type="checkbox" name="servico[]" value="outros"> Outros</label>
                        </div>
                        
                        <div class="filtro-acoes">
                            <button type="submit" class="btn-aplicar-filtros">Aplicar Filtros</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- CATEGORIAS POPULARES -->
    <section class="categorias-section" id="servicos">
        <h2>Categorias Populares</h2>
        <div class="categorias-grid">
            <?php if (!empty($categoriasPopulares)): ?>
                <?php foreach ($categoriasPopulares as $cat): ?>
                    <a href="/Aptus/buscar?categoria=<?php echo $cat['id_categoria']; ?>" class="categoria-card">
                        <div class="icone">
                            <i class="<?php echo htmlspecialchars($cat['icone'] ?? 'fas fa-tag'); ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($cat['nome']); ?></h3>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; color: var(--cor-texto-secundario);">
                    Nenhuma categoria cadastrada.
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- SEÇÃO DE SERVIÇOS EM DESTAQUE -->
    <section class="profissionais-section" id="servicos-recentes">
        <div class="container">
            <h2><i class="fas fa-star"></i> Serviços em Destaque</h2>
            <div class="profissionais-grid">
                <?php if (!empty($anunciosDestaque)): ?>
                    <?php foreach ($anunciosDestaque as $anuncio): ?>
                        <div class="profissional-card">
                            <div class="categoria-tag">
                                <i class="<?php echo htmlspecialchars($anuncio['categoria_icone'] ?? 'fas fa-tag'); ?>"></i> 
                                <?php echo htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral'); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($anuncio['titulo']); ?></h3>
                            <p class="descricao">
                                <?php echo htmlspecialchars(mb_strimwidth($anuncio['descricao'], 0, 120, "...")); ?>
                            </p>
                            <div class="info-footer">
                                <span class="autor">
                                    <i class="fas fa-user-circle"></i> 
                                    <?php echo htmlspecialchars($anuncio['freelancer_nome']); ?>
                                </span>
                                <span class="preco">
                                    <i class="fas fa-tag"></i> 
                                    R$ <?php echo number_format($anuncio['preco'], 2, ',', '.'); ?>
                                </span>
                            </div>
                            
                            <!-- Avaliação com estrelas -->
                            <div style="margin: 0 1.5rem 0.5rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= round($anuncio['nota_media'] ?? 0)): ?>
                                        <i class="fas fa-star" style="color: #f59e0b; font-size: 0.9rem;"></i>
                                    <?php else: ?>
                                        <i class="far fa-star" style="color: #d1d5db; font-size: 0.9rem;"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span style="color: var(--cor-texto-secundario); font-size: 0.85rem;">
                                    (<?php echo number_format($anuncio['nota_media'] ?? 0, 1); ?>)
                                </span>
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-top: 5px;">
                                <a href="/Aptus/anuncios/<?php echo htmlspecialchars($anuncio['slug']); ?>" class="btn-perfil" style="flex: 1; margin-top: 0;">
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
                <a href="/Aptus/buscar" class="btn-perfil" style="display: inline-block; width: auto; padding: 0.8rem 2.5rem;">
                    <i class="fas fa-arrow-right"></i> Ver Todos os Serviços
                </a>
            </div>
        </div>
    </section>

    <!-- SEÇÃO DE PEDIDOS/PROJETOS DOS CLIENTES (desativada) -->
    <section class="profissionais-section" id="pedidos-recentes" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(226, 251, 255, 0.3) 100%); display: none;">
        <div class="container">
            <h2><i class="fas fa-briefcase"></i> Pedidos dos Usuários</h2>
            <p style="text-align: center; color: var(--cor-texto-secundario); margin-bottom: 2rem;">
                Clientes procurando por profissionais qualificados. Envie sua proposta!
            </p>
            <div class="profissionais-grid">
                <div class="msg-vazia">
                    <i class="fas fa-briefcase" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    <p>Funcionalidade em desenvolvimento. Em breve você poderá visualizar pedidos aqui!</p>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- FLASH MESSAGES -->
<?php if ($flash): ?>
    <div class="flash-<?php echo $flash['tipo']; ?>" style="position: fixed; top: 80px; right: 20px; z-index: 9999; padding: 1rem 2rem; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); max-width: 400px; animation: slideInRight 0.5s ease;">
        <?php echo htmlspecialchars($flash['mensagem']); ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>