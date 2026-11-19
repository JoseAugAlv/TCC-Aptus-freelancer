<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/moderator/usuarios.php

$tituloPagina = $tituloPagina ?? 'Usuários - Aptus';
$cssPagina    = $cssPagina    ?? 'usuarios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuarios   = $usuarios ?? [];
$roleLogado = (int) ($_SESSION['usuario']['role'] ?? 0);
$idLogado   = (int) ($_SESSION['usuario']['id'] ?? 0);
require_once __DIR__ . '/../../Controllers/AdminUsuarioController.php';
?>

<div class="usuarios-wrapper">
    <div class="usuarios-header">
        <h1>Usuários</h1>
        <p>Gerenciar usuários da plataforma</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($usuarios)): ?>
        <div class="empty-state">
            <p>Nenhum usuário encontrado.</p>
            <a href="<?= aptus_base() ?>/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php
                            $banido = !empty($usuario['banido']);
                            $ativo  = array_key_exists('ativo', $usuario) ? (bool) $usuario['ativo'] : true;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($usuario['nome_perfil'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($banido): ?>
                                    <span class="badge badge-excluido">Banido</span>
                                <?php elseif (!$ativo): ?>
                                    <span class="badge badge-pausado">Inativo</span>
                                <?php else: ?>
                                    <span class="badge badge-ativo">Ativo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($usuario['data_criacao'])) ?></td>
                            <td>
                                <div class="acoes-linha">
                                    <a href="<?= aptus_base() ?>/perfil/publico/<?= url_id((int) $usuario['id_usuario']) ?>" class="btn-ver">
                                        Ver
                                    </a>

                                    <?php
                                        $podeGerenciar = in_array($roleLogado, [1, 4], true)
                                            && AdminUsuarioController::podeGerenciar(
                                                $roleLogado, $idLogado,
                                                (int) $usuario['id_usuario'], (int) $usuario['id_perfil']
                                            );
                                    ?>
                                    <?php if ($podeGerenciar && !$banido): ?>
                                        <form method="POST" action="<?= aptus_base() ?>/admin/usuarios/banir"
                                              class="form-banir"
                                              onsubmit="return confirm('Confirmar banimento deste usuário?');">
                                            <input type="hidden" name="id" value="<?= (int) $usuario['id_usuario'] ?>">
                                            <input type="text" name="motivo" placeholder="Motivo do banimento"
                                                   required maxlength="255" class="input-motivo">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn-banir">Banir</button>
                                        </form>
                                    <?php elseif ($podeGerenciar && $banido): ?>
                                        <form method="POST" action="<?= aptus_base() ?>/admin/usuarios/desbanir"
                                              class="form-banir"
                                              onsubmit="return confirm('Remover o banimento deste usuário?');">
                                            <input type="hidden" name="id" value="<?= (int) $usuario['id_usuario'] ?>">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn-ver">Desbanir</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="voltar-wrapper">
            <a href="<?= aptus_base() ?>/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php endif; ?>
</div>

<style>
.acoes-linha {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.acoes-linha .form-banir {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0;
    flex-wrap: wrap;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>