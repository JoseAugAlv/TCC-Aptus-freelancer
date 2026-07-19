<?php
// app/Views/cliente/dashboard.php

$tituloPagina = $tituloPagina ?? 'Dashboard Cliente - Aptus';
$cssPagina = $cssPagina ?? 'cliente.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuario = $_SESSION['usuario'] ?? null;
$usuarioData = $usuarioData ?? [];
?>

<div class="cliente-dashboard">
    <div class="dashboard-header">
        <div>
            <h1>Dashboard Cliente</h1>
            <p>Bem-vindo, <?= htmlspecialchars($usuario['nome'] ?? '') ?>!</p>
        </div>
        <div class="header-actions">
            <a href="/Aptus/anuncios" class="btn-primary">+ Explorar Serviços</a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-paper-plane"></i></div>
            <div class="kpi-value"><?= $totalInteresses ?? 0 ?></div>
            <div class="kpi-label">Total de Interesses</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-clock" style="color: #3b82f6;"></i></div>
            <div class="kpi-value"><?= $interessesAtivos ?? 0 ?></div>
            <div class="kpi-label">Em Andamento</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-check-circle" style="color: #10b981;"></i></div>
            <div class="kpi-value"><?= $interessesConcluidos ?? 0 ?></div>
            <div class="kpi-label">Concluídos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-times-circle" style="color: #ef4444;"></i></div>
            <div class="kpi-value"><?= $interessesCancelados ?? 0 ?></div>
            <div class="kpi-label">Cancelados</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-heart" style="color: #ef4444;"></i></div>
            <div class="kpi-value"><?= $totalFavoritos ?? 0 ?></div>
            <div class="kpi-label">Favoritos</div>
        </div>
    </div>

    <!-- Últimos Interesses -->
    <div class="card">
        <h3><i class="fas fa-paper-plane"></i> Últimos Interesses</h3>
        <?php if (empty($ultimosInteresses)): ?>
            <p class="empty-message">Você ainda não enviou nenhum interesse.</p>
            <a href="/Aptus/anuncios" class="btn-primary">Explorar serviços</a>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Serviço</th>
                        <th>Freelancer</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosInteresses as $interesse): ?>
                        <tr>
                            <td><?= htmlspecialchars($interesse['anuncio_titulo']) ?></td>
                            <td><?= htmlspecialchars($interesse['freelancer_nome']) ?></td>
                            <td>R$ <?= number_format($interesse['anuncio_preco'], 2, ',', '.') ?></td>
                            <td>
                                <?php 
                                    $cor = match($interesse['situacao']) {
                                        'ativo' => '#10b981',
                                        'concluido' => '#3b82f6',
                                        'cancelado' => '#ef4444',
                                        default => '#94a3b8'
                                    };
                                ?>
                                <span style="color: <?= $cor ?>; font-weight: bold;">
                                    <?= ucfirst($interesse['situacao']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($interesse['data_interesse'])) ?></td>
                            <td>
                                <a href="/Aptus/interesses/detalhes/<?= $interesse['id_interesse'] ?>">Ver</a>
                                <?php if ($interesse['situacao'] == 'ativo'): ?>
                                    <form method="POST" action="/Aptus/interesses/cancelar" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $interesse['id_interesse'] ?>">
                                        <button type="submit" onclick="return confirm('Cancelar este interesse?')" style="background: none; border: none; color: #ef4444; cursor: pointer; text-decoration: underline;">Cancelar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="card-footer">
                <a href="/Aptus/interesses/meus">Ver todos os interesses →</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Favoritos -->
    <div class="card">
        <h3><i class="fas fa-heart"></i> Favoritos</h3>
        <?php if (empty($favoritos)): ?>
            <p class="empty-message">Você ainda não tem favoritos.</p>
            <a href="/Aptus/anuncios" class="btn-primary">Explorar serviços</a>
        <?php else: ?>
            <div class="favoritos-grid">
                <?php foreach ($favoritos as $favorito): ?>
                    <div class="favorito-card">
                        <div class="favorito-imagem">
                            <?php if (!empty($favorito['foto_capa'])): ?>
                                <img src="/Aptus/public/uploads/anuncios/<?= htmlspecialchars($favorito['foto_capa']) ?>" alt="<?= htmlspecialchars($favorito['titulo']) ?>">
                            <?php else: ?>
                                <i class="fas fa-briefcase"></i>
                            <?php endif; ?>
                        </div>
                        <div class="favorito-info">
                            <h4><?= htmlspecialchars($favorito['titulo']) ?></h4>
                            <p class="favorito-freelancer">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($favorito['freelancer_nome']) ?>
                            </p>
                            <p class="favorito-categoria">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($favorito['categoria_nome']) ?>
                            </p>
                            <p class="favorito-preco">R$ <?= number_format($favorito['preco'], 2, ',', '.') ?></p>
                            <a href="/Aptus/anuncios/<?= htmlspecialchars($favorito['slug']) ?>" class="btn-ver">Ver Serviço</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer">
                <a href="/Aptus/favoritos">Ver todos os favoritos →</a>
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
            <a href="/Aptus/perfil/editar">Editar perfil →</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>