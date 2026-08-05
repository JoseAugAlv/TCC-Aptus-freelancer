<?php
// app/Views/moderator/denuncias.php

$tituloPagina = $tituloPagina ?? 'Denúncias - Aptus';
$cssPagina = $cssPagina ?? 'denuncias.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$denuncias = $denuncias ?? [];
?>

<div class="denuncias-container">
    <div class="denuncias-header">
        <h1><i class="fas fa-flag"></i> Denúncias</h1>
        <p>Gerencie as denúncias da plataforma</p>
        <?php if (!empty($denuncias)): ?>
            <span class="badge-count"><?= count($denuncias) ?> pendente(s)</span>
        <?php endif; ?>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($denuncias)): ?>
        <!-- EMPTY STATE -->
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Nenhuma denúncia pendente</h3>
            <p>Não há denúncias aguardando análise no momento.</p>
            <a href="/Aptus/moderator" class="btn-voltar-empty">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
    <?php else: ?>
        <!-- FILTROS -->
        <div class="filtros-denuncias">
            <form method="GET" action="/Aptus/moderator/denuncias" class="filtros-form">
                <div class="filtro-group">
                    <label for="status"><i class="fas fa-filter"></i> Status</label>
                    <select id="status" name="status">
                        <option value="">Todos</option>
                        <option value="pendente" <?= isset($_GET['status']) && $_GET['status'] === 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                        <option value="aprovado" <?= isset($_GET['status']) && $_GET['status'] === 'aprovado' ? 'selected' : '' ?>>Aprovados</option>
                        <option value="rejeitado" <?= isset($_GET['status']) && $_GET['status'] === 'rejeitado' ? 'selected' : '' ?>>Rejeitados</option>
                    </select>
                </div>
                <div class="filtro-group">
                    <label for="data_inicio"><i class="fas fa-calendar"></i> Data inicial</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?= isset($_GET['data_inicio']) ? htmlspecialchars($_GET['data_inicio']) : '' ?>">
                </div>
                <div class="filtro-group">
                    <label for="data_fim"><i class="fas fa-calendar"></i> Data final</label>
                    <input type="date" id="data_fim" name="data_fim" value="<?= isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim']) : '' ?>">
                </div>
                <div class="filtro-actions">
                    <button type="submit" class="btn-filtrar">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="/Aptus/moderator/denuncias" class="btn-limpar-filtros">
                        <i class="fas fa-undo"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- TABELA -->
        <div class="tabela-wrapper">
            <table class="tabela-denuncias">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Denunciante</th>
                        <th><i class="fas fa-user-slash"></i> Denunciado</th>
                        <th><i class="fas fa-exclamation-triangle"></i> Motivo</th>
                        <th><i class="fas fa-ad"></i> Anúncio</th>
                        <th><i class="fas fa-calendar-alt"></i> Data</th>
                        <th><i class="fas fa-cog"></i> Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($denuncias as $denuncia): ?>
                        <tr>
                            <td data-label="Denunciante">
                                <strong><?= htmlspecialchars($denuncia['denunciante_nome']) ?></strong>
                            </td>
                            <td data-label="Denunciado">
                                <?= htmlspecialchars($denuncia['denunciado_nome']) ?>
                            </td>
                            <td data-label="Motivo">
                                <span class="motivo-truncado" title="<?= htmlspecialchars($denuncia['motivo']) ?>">
                                    <?= htmlspecialchars($denuncia['motivo']) ?>
                                </span>
                            </td>
                            <td data-label="Anúncio">
                                <?= htmlspecialchars($denuncia['anuncio_titulo'] ?? 'N/A') ?>
                            </td>
                            <td data-label="Data">
                                <?= date('d/m/Y H:i', strtotime($denuncia['data_criacao'])) ?>
                            </td>
                            <td data-label="Ações">
                                <div class="acoes-cell">
                                    <a href="/Aptus/moderator/denuncias/visualizar/<?= $denuncia['id_denuncia'] ?>" class="btn-analisar">
                                        <i class="fas fa-eye"></i> Analisar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- RODAPÉ -->
        <div class="denuncias-footer">
            <a href="/Aptus/moderator" class="btn-voltar-footer">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>