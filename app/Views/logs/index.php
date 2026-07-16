<?php
$tituloPagina = 'Logs do Sistema - RecycleWays';
$cssPagina = 'logs.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<section class="logs-section animate-in">
    <div class="container">
        <div class="logs-content">

            <!-- Hero -->
            <div class="logs-hero">
                <div>
                    <h1><i class="fas fa-history" style="color: #6c757d;"></i> Logs do Sistema</h1>
                    <p style="color: var(--color-text); margin-top: 0.3rem;">
                        <i class="fas fa-list"></i> Visualize todas as atividades do sistema
                    </p>
                </div>
                <span class="badge-count">
                    <i class="fas fa-file-alt"></i> <?= $total ?? 0 ?> registro(s)
                </span>
            </div>

            <!-- Filtros -->
            <div class="logs-filtros">
                <form method="GET" action="/RecycleWays/logs">
                    <div class="filtros-grid">
                        <div class="filtro-group">
                            <label for="buscar"><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" id="buscar" name="buscar" class="form-control" value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" placeholder="Digite para buscar...">
                        </div>
                        
                        <div class="filtro-group">
                            <label for="tabela"><i class="fas fa-table"></i> Tabela</label>
                            <select id="tabela" name="tabela" class="form-control">
                                <option value="">Todas</option>
                                <?php foreach ($tabelas as $t): ?>
                                    <option value="<?= htmlspecialchars($t) ?>" <?= ($_GET['tabela'] ?? '') == $t ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filtro-group">
                            <label for="acao"><i class="fas fa-tag"></i> Ação</label>
                            <select id="acao" name="acao" class="form-control">
                                <option value="">Todas</option>
                                <?php foreach ($acoes as $a): ?>
                                    <option value="<?= htmlspecialchars($a) ?>" <?= ($_GET['acao'] ?? '') == $a ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filtro-group filtro-actions">
                            <label>&nbsp;</label>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="submit" class="btn" style="background: #2563eb; color: white;">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <a href="/RecycleWays/logs" class="btn" style="background: #6c757d; color: white;">
                                    <i class="fas fa-undo"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Lista de Logs -->
            <?php if (empty($logs)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Nenhum log encontrado</h3>
                    <p>Tente ajustar os filtros para encontrar o que procura.</p>
                </div>
            <?php else: ?>
                <div class="logs-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ação</th>
                                <th>Tabela</th>
                                <th>Registro</th>
                                <th>Detalhes</th>
                                <th>Usuário</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>#<?= $log['id_log'] ?></td>
                                    <td><span class="badge-acao"><?= htmlspecialchars($log['acao']) ?></span></td>
                                    <td><?= htmlspecialchars($log['tabela_afetada'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($log['registro_id'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($log['detalhes'] ?? '-') ?></td>
                                    <td><strong><?= htmlspecialchars($log['usuario_nome'] ?? 'Sistema') ?></strong></td>
                                    <td><?= date('d/m/Y H:i:s', strtotime($log['data_criacao'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <?php if (isset($paginationHtml)): ?>
                    <div class="pagination-container">
                        <?= $paginationHtml ?>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center; font-size: 0.9rem; color: var(--color-text);">
                    <i class="fas fa-info-circle"></i> 
                    Total de registros: <strong><?= $total ?? 0 ?></strong>
                    | Mostrando <strong><?= count($logs) ?></strong> registro(s)
                </div>
            <?php endif; ?>

            <!-- Voltar -->
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #f0f0f0; text-align: center;">
                <a href="/RecycleWays/" class="btn">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

        </div>
    </div>
</section>

<style>
.logs-filtros {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    margin-bottom: 2rem;
}
.filtros-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}
.filtro-group label {
    display: block;
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--color-text);
    margin-bottom: 0.3rem;
}
.filtro-group .form-control {
    width: 100%;
    padding: 0.6rem 0.8rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 0.9rem;
    background: var(--color-white);
}
.logs-table-wrapper {
    overflow-x: auto;
    border: 2px solid var(--color-forest-deep);
    border-radius: 8px;
    margin-bottom: 1.5rem;
}
.logs-table-wrapper table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}
.logs-table-wrapper thead {
    background: var(--color-forest-deep);
}
.logs-table-wrapper thead th {
    color: var(--color-white);
    padding: 0.8rem 1rem;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-align: left;
}
.logs-table-wrapper tbody td {
    padding: 0.7rem 1rem;
    border-bottom: 1px solid #e9ecef;
    color: var(--color-text);
    font-size: 0.9rem;
}
.badge-acao {
    display: inline-block;
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    background: #dbeafe;
    color: #1e40af;
}
.pagination-container {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin: 1.5rem 0;
    flex-wrap: wrap;
}
.pagination-container a,
.pagination-container span {
    padding: 0.4rem 0.8rem;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    text-decoration: none;
    color: var(--color-text);
    font-weight: 600;
    font-size: 0.85rem;
}
.pagination-container a:hover {
    background: var(--color-impact-green);
    color: white;
    border-color: var(--color-impact-green);
}
.pagination-container .active {
    background: var(--color-forest-deep);
    color: white;
    border-color: var(--color-forest-deep);
}
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}
.empty-state i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}
.empty-state h3 {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.3rem;
    color: var(--color-forest-deep);
    margin-bottom: 0.5rem;
}
.empty-state p {
    color: var(--color-text);
    margin-bottom: 1.5rem;
}
@media (max-width: 992px) {
    .filtros-grid {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 768px) {
    .filtros-grid {
        grid-template-columns: 1fr;
    }
    .filtro-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .filtro-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>