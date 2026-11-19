<?php
require_once __DIR__ . '/../../../Middleware/CsrfMiddleware.php';

$tituloPagina = $tituloPagina ?? 'Usuários - Admin';
$cssPagina    = $cssPagina    ?? 'usuarios.css';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/nav.php';

$usuarios   = $usuarios ?? [];
?>

<div class="usuarios-wrapper">
    <div class="usuarios-header">
        <h1>Usuários</h1>
        <p>Gerencie contas de usuários</p>
    </div>
    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form method="GET" action="<?= aptus_base() ?>/admin/usuarios" class="usuarios-busca" role="search">
        <label for="buscaUsuario" class="sr-only">Buscar usuário</label>
        <input type="search" id="buscaUsuario" name="q" value="<?= htmlspecialchars($busca ?? '') ?>"
               placeholder="Buscar por nome, @usuário ou e-mail" maxlength="100">
        <button type="submit" class="btn-ver"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
        <span class="usuarios-total"><?= (int) ($totalUsuarios ?? count($usuarios)) ?> usuário(s)</span>
    </form>

    <?php if (empty($usuarios)): ?>
        <div class="empty-state"><p>Nenhum usuário encontrado.</p></div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="linhasUsuarios">
                    <?php require __DIR__ . '/_linhas.php'; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($temMais)): ?>
            <div class="ver-mais-wrap">
                <button type="button" class="btn-ver-mais" data-ver-mais data-alvo="#linhasUsuarios"
                        data-url="<?= htmlspecialchars($proximaUrl, ENT_QUOTES, 'UTF-8') ?>" data-pagina="1">
                    <i class="fas fa-plus" aria-hidden="true"></i> Ver mais
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php $jsPagina = 'ver-mais.js'; require_once __DIR__ . '/../../layouts/footer.php'; ?>