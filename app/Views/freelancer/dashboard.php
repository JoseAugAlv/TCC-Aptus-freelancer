<?php
// app/Views/freelancer/dashboard.php

$tituloPagina = $tituloPagina ?? 'Dashboard Freelancer - Aptus';
$cssPagina = $cssPagina ?? 'freelancer.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuario = $_SESSION['usuario'] ?? null;
$usuarioData = $usuarioData ?? [];
?>

<div class="freelancer-dashboard">
    <div class="dashboard-header">
        <h1>Dashboard Freelancer</h1>
        <p>Bem-vindo, <?= htmlspecialchars($usuario['nome'] ?? '') ?>!</p>
        <div class="header-actions">
            <a href="<?= aptus_base() ?>/anuncios/criar" class="btn-primary">+ Novo Serviço</a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-briefcase"></i></div>
            <div class="kpi-value"><?= $totalServicos ?? 0 ?></div>
            <div class="kpi-label">Total de Serviços</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-check-circle" style="color: var(--cor-sucesso);"></i></div>
            <div class="kpi-value"><?= $servicosAtivos ?? 0 ?></div>
            <div class="kpi-label">Ativos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-pause-circle" style="color: var(--cor-aviso);"></i></div>
            <div class="kpi-value"><?= $servicosPausados ?? 0 ?></div>
            <div class="kpi-label">Pausados</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-clock" style="color: var(--cor-info);"></i></div>
            <div class="kpi-value"><?= $servicosPendentes ?? 0 ?></div>
            <div class="kpi-label">Pendentes</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-inbox" style="color: #8b5cf6;"></i></div>
            <div class="kpi-value"><?= $interessesRecebidos ?? 0 ?></div>
            <div class="kpi-label">Interesses Recebidos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-check-double" style="color: var(--cor-sucesso);"></i></div>
            <div class="kpi-value"><?= $interessesConcluidos ?? 0 ?></div>
            <div class="kpi-label">Concluídos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-clock" style="color: var(--cor-aviso);"></i></div>
            <div class="kpi-value"><?= $propostasPendentes ?? 0 ?></div>
            <div class="kpi-label">Propostas Pendentes</div>
        </div>
    </div>

    <!-- Avaliação -->
    <div class="avaliacao-card">
        <div class="avaliacao-info">
            <span class="avaliacao-label">Avaliação Média</span>
            <div class="avaliacao-estrelas">
                <?php $estrelasNota = $avaliacao['nota'] ?? 0; $estrelasTotal = $avaliacao['total'] ?? 0; require __DIR__ . '/../partials/estrelas.php'; ?>
            </div>
        </div>
    </div>

    <!-- Últimos Serviços -->
    <div class="card">
        <h3><i class="fas fa-tools"></i> Últimos Serviços</h3>
        <?php if (empty($ultimosServicos)): ?>
            <p class="empty-message">Nenhum serviço criado ainda.</p>
            <a href="<?= aptus_base() ?>/anuncios/criar" class="btn-primary">Criar primeiro serviço</a>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Serviço</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Interesses</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosServicos as $servico): ?>
                        <tr>
                            <td><?= htmlspecialchars($servico['titulo']) ?></td>
                            <td><?= htmlspecialchars($servico['categoria_nome']) ?></td>
                            <td>R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td>
                            <td><?= $servico['total_interesses'] ?? 0 ?></td>
                            <td>
                                <?php 
                                    $cor = match($servico['situacao']) {
                                        'ativo' => 'green',
                                        'pausado' => 'orange',
                                        default => 'gray'
                                    };
                                ?>
                                <span style="color: <?= $cor ?>; font-weight: bold;">
                                    <?= ucfirst($servico['situacao']) ?>
                                </span>
                                <?php if ($servico['id_situacao_moderacao'] == 1): ?>
                                    <span style="color: orange; font-size: 0.7rem;">(Pendente)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= aptus_base() ?>/anuncios/<?= htmlspecialchars($servico['slug']) ?>">Ver</a>
                                <a href="<?= aptus_base() ?>/anuncios/editar/<?= url_id($servico['id_anuncio']) ?>">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="card-footer">
                <a href="<?= aptus_base() ?>/anuncios/meus">Ver todos os serviços →</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Últimos Interesses Recebidos -->
    <div class="card">
        <h3><i class="fas fa-inbox"></i> Últimos Interesses Recebidos</h3>
        <?php if (empty($ultimosInteresses)): ?>
            <p class="empty-message">Nenhum interesse recebido ainda.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosInteresses as $interesse): ?>
                        <tr>
                            <td><?= htmlspecialchars($interesse['contratante_nome']) ?></td>
                            <td><?= htmlspecialchars($interesse['anuncio_titulo']) ?></td>
                            <td>
                                <?php 
                                    $cor = match($interesse['situacao']) {
                                        'ativo' => 'green',
                                        'concluido' => 'blue',
                                        'cancelado' => 'red',
                                        default => 'gray'
                                    };
                                ?>
                                <span style="color: <?= $cor ?>; font-weight: bold;">
                                    <?= ucfirst($interesse['situacao']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($interesse['data_interesse'])) ?></td>
                            <td>
                                <a href="<?= aptus_base() ?>/interesses/detalhes/<?= url_id($interesse['id_interesse']) ?>">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="card-footer">
                <a href="<?= aptus_base() ?>/interesses/recebidos">Ver todos os interesses →</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Informações do Perfil -->
    <div class="card">
        <h3><i class="fas fa-user"></i> Informações do Perfil</h3>
        <div class="perfil-info-grid">
            <div>
                <span class="label">Nome</span>
                <span class="value"><?= htmlspecialchars($usuarioData['nome'] ?? '') ?></span>
            </div>
            <div>
                <span class="label">E-mail</span>
                <span class="value"><?= htmlspecialchars($usuarioData['email'] ?? '') ?></span>
            </div>
            <div>
                <span class="label">Telefone</span>
                <span class="value"><?= htmlspecialchars($usuarioData['telefone'] ?? 'Não informado') ?></span>
            </div>
            <div>
                <span class="label">Membro desde</span>
                <span class="value"><?= date('d/m/Y', strtotime($usuarioData['data_criacao'] ?? 'now')) ?></span>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?= aptus_base() ?>/perfil/editar">Editar perfil →</a>
        </div>
    </div>

    <div style="text-align: center; margin-top: 2rem;">
        <a href="<?= aptus_base() ?>/" class="btn-primary">← Voltar ao Início</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>