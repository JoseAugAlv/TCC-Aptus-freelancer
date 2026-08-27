<?php
// app/Views/moderator/usuarios.php

$tituloPagina = $tituloPagina ?? 'Usuários - Aptus';
$cssPagina = $cssPagina ?? 'usuarios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuarios = $usuarios ?? [];
?>

<div class="usuarios-wrapper">
    <div class="usuarios-header">
        <h1>Usuários</h1>
        <p>Gerenciar usuários da plataforma</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($usuarios)): ?>
        <div class="empty-state">
            <p>Nenhum usuário encontrado.</p>
            <a href="/Aptus/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nome']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td><?= htmlspecialchars($usuario['nome_perfil'] ?? 'Usuário') ?></td>
                            <td><?= date('d/m/Y', strtotime($usuario['data_criacao'])) ?></td>
                            <td>
                                <a href="/Aptus/perfil/publico/<?= $usuario['id_usuario'] ?>" class="btn-ver">Ver</a>
                                <?php if (in_array($_SESSION['usuario']['role'], [1, 4])): ?>
                                    <form method="POST" action="/Aptus/admin/usuarios/banir">
                                        <input type="hidden" name="id" value="<?= $usuario['id_usuario'] ?>">
                                        <button type="submit" class="btn-banir" onclick="return confirm('Tem certeza que deseja banir este usuário?')">Banir</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="voltar-wrapper">
            <a href="/Aptus/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>