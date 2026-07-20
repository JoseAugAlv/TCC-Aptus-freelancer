<?php
// app/Views/relatorios/index.php

$tituloPagina = $tituloPagina ?? 'Relatorios - Aptus';
$cssPagina = $cssPagina ?? 'relatorios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div class="relatorios-container">
    <div class="relatorios-header">
        <h1><i class="fas fa-chart-bar"></i> Relatorios</h1>
        <p>Estatisticas e metricas do sistema</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-search"></i></div>
            <div class="kpi-value"><?= $totalBuscas ?? 0 ?></div>
            <div class="kpi-label">Buscas (30 dias)</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-search" style="color: #3b82f6;"></i></div>
            <div class="kpi-value"><?= $totalBuscasSemana ?? 0 ?></div>
            <div class="kpi-label">Buscas (7 dias)</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-tags" style="color: #f59e0b;"></i></div>
            <div class="kpi-value"><?= $buscasSemCategoria ?? 0 ?></div>
            <div class="kpi-label">Buscas sem categoria</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-briefcase" style="color: #10b981;"></i></div>
            <div class="kpi-value"><?= $totalAnuncios ?? 0 ?></div>
            <div class="kpi-label">Anuncios Ativos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-users" style="color: #8b5cf6;"></i></div>
            <div class="kpi-value"><?= $totalUsuarios ?? 0 ?></div>
            <div class="kpi-label">Usuarios Ativos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-handshake" style="color: #ec4899;"></i></div>
            <div class="kpi-value"><?= $totalInteressesAtivos ?? 0 ?></div>
            <div class="kpi-label">Interesses Ativos</div>
        </div>
    </div>

    <!-- Graficos -->
    <div class="relatorios-grid">
        <div class="card">
            <h3><i class="fas fa-tags"></i> Categorias Mais Buscadas</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Categoria</th>
                            <th>Buscas</th>
                            <th>Percentual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categoriasMaisBuscadas)): ?>
                            <tr><td colspan="4">Nenhuma busca registrada</td></tr>
                        <?php else: ?>
                            <?php 
                            $totalCategorias = array_sum(array_column($categoriasMaisBuscadas, 'total_buscas'));
                            $posicao = 1;
                            foreach ($categoriasMaisBuscadas as $cat): 
                                $percentual = $totalCategorias > 0 ? round(($cat['total_buscas'] / $totalCategorias) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><?= $posicao ?></td>
                                    <td>
                                        <i class="<?= htmlspecialchars($cat['icone'] ?? 'fas fa-tag') ?>"></i>
                                        <?= htmlspecialchars($cat['nome'] ?? 'N/A') ?>
                                    </td>
                                    <td><?= $cat['total_buscas'] ?></td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= $percentual ?>%; background: <?= $posicao <= 3 ? '#10b981' : ($posicao <= 5 ? '#f59e0b' : '#3b82f6') ?>;">
                                                <?= $percentual ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                $posicao++;
                            endforeach; 
                            ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <a href="/Aptus/relatorios/categorias-pdf" class="btn-pdf" target="_blank">
                    <i class="fas fa-file-pdf"></i> Baixar PDF
                </a>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-search"></i> Termos Mais Buscados</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Termo</th>
                            <th>Buscas</th>
                            <th>Percentual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($termosMaisBuscados)): ?>
                            <tr><td colspan="4">Nenhum termo registrado</td></tr>
                        <?php else: ?>
                            <?php 
                            $totalTermos = array_sum(array_column($termosMaisBuscados, 'total'));
                            $posicao = 1;
                            foreach ($termosMaisBuscados as $termo): 
                                $percentual = $totalTermos > 0 ? round(($termo['total'] / $totalTermos) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><?= $posicao ?></td>
                                    <td>"<?= htmlspecialchars($termo['termo_buscado']) ?>"</td>
                                    <td><?= $termo['total'] ?></td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= $percentual ?>%; background: <?= $posicao <= 3 ? '#10b981' : ($posicao <= 5 ? '#f59e0b' : '#3b82f6') ?>;">
                                                <?= $percentual ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                $posicao++;
                            endforeach; 
                            ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <a href="/Aptus/relatorios/termos-pdf" class="btn-pdf" target="_blank">
                    <i class="fas fa-file-pdf"></i> Baixar PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Usuarios que mais buscam -->
    <div class="card">
        <h3><i class="fas fa-user"></i> Usuarios que Mais Buscaram</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Total de Buscas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuariosMaisBuscam)): ?>
                        <tr><td colspan="4">Nenhum usuario registrado</td></tr>
                    <?php else: ?>
                        <?php $posicao = 1; foreach ($usuariosMaisBuscam as $user): ?>
                            <tr>
                                <td><?= $posicao ?></td>
                                <td><?= htmlspecialchars($user['nome']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= $user['total_buscas'] ?></td>
                            </tr>
                        <?php $posicao++; endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="relatorios-links">
        <a href="/Aptus/admin/dashboard"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
    </div>
</div>



<?php require_once __DIR__ . '/../layouts/footer.php'; ?>