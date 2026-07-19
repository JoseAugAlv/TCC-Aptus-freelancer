<?php
// app/Views/moderator/usuarios.php

$tituloPagina = $tituloPagina ?? 'Usuários - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuarios = $usuarios ?? [];
?>

<h1>Usuários</h1>
<p>Gerenciar usuários da plataforma</p>

<hr>

<table border="1" cellpadding="8" cellspacing="0">
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
        <?php if (empty($usuarios)): ?>
            <tr>
                <td colspan="5">Nenhum usuário encontrado.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= htmlspecialchars($usuario['nome_perfil'] ?? 'Usuário') ?></td>
                    <td><?= date('d/m/Y', strtotime($usuario['data_criacao'])) ?></td>
                    <td>
                        <a href="/Aptus/perfil/publico/<?= $usuario['id_usuario'] ?>">Ver</a>
                        <?php if (in_array($_SESSION['usuario']['role'], [1, 4])): ?>
                            <form method="POST" action="/Aptus/admin/usuarios/banir">
                                <input type="hidden" name="id" value="<?= $usuario['id_usuario'] ?>">
                                <button type="submit">Banir</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<p><a href="/Aptus/moderator">Voltar</a></p>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>