<?php
// app/Views/admin/usuarios/_linhas.php — linhas da tabela de usuários (lista e "Ver mais")
require_once __DIR__ . '/../../../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../../Controllers/AdminUsuarioController.php';
require_once __DIR__ . '/../../../Helpers/RestricaoHelper.php';

$roleLogado = (int) ($_SESSION['usuario']['role'] ?? 0);
$idLogado   = (int) ($_SESSION['usuario']['id'] ?? 0);
?>
<?php foreach ($usuarios as $u): ?>
    <?php
        $__gerencia = AdminUsuarioController::podeGerenciar($roleLogado, $idLogado, (int) $u['id_usuario'], (int) $u['id_perfil']);
        $__suspenso = !empty($u['suspenso_ate']) && strtotime($u['suspenso_ate']) > time();
    ?>
    <tr>
        <td>
            <?= htmlspecialchars($u['nome']) ?>
            <small class="username-tag">@<?= htmlspecialchars($u['username'] ?? '') ?></small>
        </td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['nome_perfil'] ?? 'Usuário') ?></td>
        <td>
            <?php if (!empty($u['banido'])): ?>
                <span class="badge badge-excluido">Banido</span>
            <?php elseif ($__suspenso): ?>
                <span class="badge badge-pausado" title="<?= htmlspecialchars($u['motivo_suspensao'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    Suspenso até <?= date('d/m/Y H:i', strtotime($u['suspenso_ate'])) ?>
                </span>
            <?php elseif (empty($u['ativo'])): ?>
                <span class="badge badge-pausado">Inativo</span>
            <?php else: ?>
                <span class="badge badge-ativo">Ativo</span>
            <?php endif; ?>
        </td>
        <td>
            <div class="usuario-acoes">
                <a href="<?= aptus_base() ?>/perfil/publico/<?= url_id((int) $u['id_usuario']) ?>" class="btn-ver">Ver</a>

                <?php if ($roleLogado === 4 && $__gerencia): ?>
                    <form method="POST" action="<?= aptus_base() ?>/admin/usuarios/perfil" class="form-inline"
                          onsubmit="return confirm('Alterar o perfil deste usuário?');">
                        <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                        <select name="id_perfil" class="input-motivo" aria-label="Novo perfil">
                            <option value="3" <?= (int) $u['id_perfil'] === 3 ? 'selected' : '' ?>>Usuário</option>
                            <option value="2" <?= (int) $u['id_perfil'] === 2 ? 'selected' : '' ?>>Moderador</option>
                            <option value="1" <?= (int) $u['id_perfil'] === 1 ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?= CsrfMiddleware::field() ?>
                        <button type="submit" class="btn-ver">Salvar</button>
                    </form>
                <?php endif; ?>

                <?php if ($__gerencia && empty($u['banido'])): ?>
                    <?php if ($__suspenso): ?>
                        <form method="POST" action="<?= aptus_base() ?>/admin/usuarios/remover-suspensao" class="form-inline"
                              onsubmit="return confirm('Encerrar a suspensão agora?');">
                            <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                            <?= CsrfMiddleware::field() ?>
                            <button type="submit" class="btn-ver">Encerrar suspensão</button>
                        </form>
                    <?php else: ?>
                        <details class="acao-motivo">
                            <summary class="btn-suspender">Suspender</summary>
                            <form method="POST" action="<?= aptus_base() ?>/admin/usuarios/suspender">
                                <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                                <?= CsrfMiddleware::field() ?>
                                <label for="prazo<?= (int) $u['id_usuario'] ?>">Prazo</label>
                                <select id="prazo<?= (int) $u['id_usuario'] ?>" name="prazo" class="input-motivo" required>
                                    <?php foreach (RestricaoHelper::PRAZOS_SUSPENSAO as $valor => [, $rotulo]): ?>
                                        <option value="<?= $valor ?>"><?= $rotulo ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="motivoSusp<?= (int) $u['id_usuario'] ?>">Motivo</label>
                                <textarea id="motivoSusp<?= (int) $u['id_usuario'] ?>" name="motivo" rows="2"
                                          maxlength="255" required placeholder="O usuário verá este motivo"></textarea>
                                <button type="submit" class="btn-suspender">Confirmar suspensão</button>
                            </form>
                        </details>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
