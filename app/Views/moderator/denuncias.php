<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/moderator/denuncias.php

$tituloPagina = $tituloPagina ?? 'Denúncias - Aptus';
$cssPagina = $cssPagina ?? 'denuncias.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$denuncias = $denuncias ?? [];
?>

<div class="denuncias-container">
    <div class="denuncias-header">
        <h1>Denúncias</h1>
        <p>Histórico completo de denúncias da plataforma</p>
        <?php if (!empty($totalPendentes)): ?>
            <span class="badge-count"><?= (int) $totalPendentes ?> pendente(s)</span>
        <?php endif; ?>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

        <!-- FILTROS -->
        <div class="filtros-denuncias">
            <form method="GET" action="<?= aptus_base() ?>/moderator/denuncias" class="filtros-form">
                <div class="filtro-group">
                    <label for="status"><i class="fas fa-filter"></i> Status</label>
                    <select id="status" name="status">
                        <option value="">Todas</option>
                        <option value="pendente" <?= isset($_GET['status']) && $_GET['status'] === 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                        <option value="aprovado" <?= isset($_GET['status']) && $_GET['status'] === 'aprovado' ? 'selected' : '' ?>>Aprovadas</option>
                        <option value="rejeitado" <?= isset($_GET['status']) && $_GET['status'] === 'rejeitado' ? 'selected' : '' ?>>Rejeitadas</option>
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
                    <a href="<?= aptus_base() ?>/moderator/denuncias" class="btn-limpar-filtros">
                        <i class="fas fa-undo"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

    <?php if (empty($denuncias)): ?>
        <!-- EMPTY STATE -->
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Nenhuma denúncia encontrada</h3>
            <p>Nenhuma denúncia corresponde aos filtros escolhidos.</p>
            <a href="<?= aptus_base() ?>/moderator" class="btn-voltar-empty">
                 Voltar ao Dashboard
            </a>
        </div>
    <?php else: ?>
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
                        <th><i class="fas fa-flag"></i> Status</th>
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
                            <td data-label="Status">
                                <?php $__st = (int) $denuncia['id_situacao']; ?>
                                <span class="status-badge <?= $__st === 1 ? 'pendente' : ($__st === 2 ? 'aprovado' : 'rejeitado') ?>">
                                    <?= htmlspecialchars($denuncia['situacao_nome']) ?>
                                </span>
                                <?php if ($__st !== 1 && !empty($denuncia['moderador_nome'])): ?>
                                    <small class="denuncia-analise">por <?= htmlspecialchars($denuncia['moderador_nome']) ?></small>
                                <?php endif; ?>
                                <?php if ((int) ($denuncia['total_anexos'] ?? 0) > 0): ?>
                                    <small class="denuncia-analise"><i class="fas fa-paperclip"></i> <?= (int) $denuncia['total_anexos'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Ações">
                                <div class="acoes-cell">
                                    <a href="<?= aptus_base() ?>/moderator/denuncias/visualizar/<?= url_id($denuncia['id_denuncia']) ?>" class="btn-analisar">
                                        <i class="fas fa-eye"></i> <?= (int) $denuncia['id_situacao'] === 1 ? 'Analisar' : 'Ver' ?>
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
            <a href="<?= aptus_base() ?>/moderator" class="btn-voltar-footer">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>