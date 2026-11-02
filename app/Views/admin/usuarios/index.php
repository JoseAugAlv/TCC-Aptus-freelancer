<?php
require_once __DIR__ . '/../../../Middleware/CsrfMiddleware.php';

$tituloPagina = $tituloPagina ?? 'Usuários - Admin';
$cssPagina    = $cssPagina    ?? 'usuarios.css';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/nav.php';

$usuarios = $usuarios ?? [];
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
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nome']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['nome_perfil'] ?? 'Usuário') ?></td>
                            <td>
                                <?php if (!empty($u['banido'])): ?>
                                    <span class="badge badge-excluido">Banido</span>
                                <?php elseif (empty($u['ativo'])): ?>
                                    <span class="badge badge-pausado">Inativo</span>
                                <?php else: ?>
                                    <span class="badge badge-ativo">Ativo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/Aptus/perfil/publico/<?= (int) $u['id_usuario'] ?>">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>