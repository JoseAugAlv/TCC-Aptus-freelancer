<?php
// app/Views/admin/dashboard.php

$tituloPagina = $tituloPagina ?? 'Dashboard - Admin';
$cssPagina = $cssPagina ?? 'admin.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Dashboard Administrativo</h1>
        <div class="admin-header-info">
            <span><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y H:i') ?></span>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
            <div class="kpi-label">Usuários</div>
            <div class="kpi-value"><?= $totalUsuarios ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-briefcase"></i></div>
            <div class="kpi-label">Anúncios Ativos</div>
            <div class="kpi-value"><?= $totalAnuncios ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
            <div class="kpi-label">Freelancers</div>
            <div class="kpi-value"><?= $totalFreelancers ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-handshake"></i></div>
            <div class="kpi-label">Interesses</div>
            <div class="kpi-value"><?= $totalInteresses ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-tags"></i></div>
            <div class="kpi-label">Categorias</div>
            <div class="kpi-value"><?= $totalCategorias ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-clock"></i></div>
            <div class="kpi-label">Anúncios Pendentes</div>
            <div class="kpi-value"><?= $anunciosPendentes ?></div>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="admin-content">
        <div class="admin-left">
            <!-- Últimos Usuários -->
            <div class="card">
                <h3><i class="fas fa-user-plus"></i> Últimos Usuários</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th>Cadastro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimosUsuarios)): ?>
                                <tr><td colspan="4" style="text-align: center; color: #888;">Nenhum usuário cadastrado</td></tr>
                            <?php else: ?>
                                <?php foreach ($ultimosUsuarios as $user): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($user['foto_perfil']) && $user['foto_perfil'] != 'default.png'): ?>
                                                <img src="/Aptus/public/uploads/<?= htmlspecialchars($user['foto_perfil']) ?>" 
                                                     alt="Foto" class="avatar-mini">
                                            <?php else: ?>
                                                <i class="fas fa-user-circle" style="font-size: 1.5rem; color: #94a3b8;"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($user['nome']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($user['nome_perfil'] ?? 'usuario') ?>">
                                                <?= htmlspecialchars($user['nome_perfil'] ?? 'Usuário') ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($user['data_criacao'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Últimos Anúncios -->
            <div class="card">
                <h3><i class="fas fa-tools"></i> Últimos Anúncios</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Anúncio</th>
                                <th>Categoria</th>
                                <th>Freelancer</th>
                                <th>Preço</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimosAnuncios)): ?>
                                <tr><td colspan="5" style="text-align: center; color: #888;">Nenhum anúncio cadastrado</td></tr>
                            <?php else: ?>
                                <?php foreach ($ultimosAnuncios as $anuncio): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($anuncio['titulo']) ?></td>
                                        <td><?= htmlspecialchars($anuncio['categoria_nome']) ?></td>
                                        <td><?= htmlspecialchars($anuncio['freelancer_nome']) ?></td>
                                        <td>R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?></td>
                                        <td>
                                            <span class="badge badge-<?= $anuncio['situacao'] ?>">
                                                <?= ucfirst($anuncio['situacao']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="admin-right">
            <!-- Gráfico -->
            <div class="card">
                <h3><i class="fas fa-chart-pie"></i> Distribuição de Usuários</h3>
                <canvas id="userChart" height="250"></canvas>
            </div>

            <!-- Resumo -->
            <div class="card">
                <h3><i class="fas fa-info-circle"></i> Resumo</h3>
                <ul class="resumo-list">
                    <li>
                        <i class="fas fa-user-tie" style="color: #3b82f6;"></i>
                        <span>Administradores</span>
                        <strong><?= $totaisPorTipo['Admin'] ?? 0 ?></strong>
                    </li>
                    <li>
                        <i class="fas fa-user-cog" style="color: #f59e0b;"></i>
                        <span>Moderadores</span>
                        <strong><?= $totaisPorTipo['Moderador'] ?? 0 ?></strong>
                    </li>
                    <li>
                        <i class="fas fa-user" style="color: #10b981;"></i>
                        <span>Usuários</span>
                        <strong><?= $totaisPorTipo['Usuario'] ?? 0 ?></strong>
                    </li>
                    <li>
                        <i class="fas fa-user-shield" style="color: #8b5cf6;"></i>
                        <span>Master</span>
                        <strong><?= $totaisPorTipo['Master'] ?? 0 ?></strong>
                    </li>
                    <li>
                        <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                        <span>Denúncias Pendentes</span>
                        <strong><?= $denunciasPendentes ?></strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Distribuição de Usuários
    const ctx = document.getElementById('userChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                data: <?= json_encode($data) ?>,
                backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#8b5cf6'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            cutout: '65%'
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>