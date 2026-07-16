<?php
$tituloPagina = 'Minhas Notificações - RecycleWays';
$cssPagina = 'notificacoes.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
require_once __DIR__ . '/../../Helpers/ViewHelper.php';
?>

<?php
if (!isset($_SESSION['usuario'])) {
    header('Location: /RecycleWays/login');
    exit;
}

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $tipo = $flash['tipo'];
    $mensagem = htmlspecialchars($flash['mensagem']);
    echo '<div class="flash-' . $tipo . '">' . $mensagem . '</div>';
    unset($_SESSION['flash']);
}

// Contar notificações não lidas
$naoLidas = 0;
$notificacoesNovas = [];
$notificacoesAntigas = [];

if (!empty($notificacoes)) {
    foreach ($notificacoes as $n) {
        if (!$n['lida']) {
            $naoLidas++;
            $notificacoesNovas[] = $n;
        } else {
            $notificacoesAntigas[] = $n;
        }
    }
}

// Verificar se deve mostrar todas ou apenas novas
$mostrarTodas = isset($_GET['todas']) && $_GET['todas'] == '1';
$notificacoesExibir = $mostrarTodas ? $notificacoes : $notificacoesNovas;
?>

<section class="notificacoes-section animate-in">
    <div class="container">
        <div class="notificacoes-content">

            <!-- Hero -->
            <div class="notificacoes-hero">
                <div>
                    <h1><i class="fas fa-bell" style="color: var(--color-impact-green);"></i> Minhas Notificações</h1>
                    <p style="color: var(--color-text); margin-top: 0.3rem;">
                        <i class="fas fa-inbox"></i> Fique por dentro de tudo que acontece
                    </p>
                </div>
                <span class="badge-count">
                    <i class="fas fa-bell"></i> 
                    <?php if ($mostrarTodas): ?>
                        <?= count($notificacoes ?? []) ?> notificação(ões)
                    <?php else: ?>
                        <?= count($notificacoesNovas) ?> nova(s)
                    <?php endif; ?>
                    <?php if ($naoLidas > 0 && !$mostrarTodas): ?>
                        <span class="nao-lidas"><?= $naoLidas ?> não lidas</span>
                    <?php endif; ?>
                </span>
            </div>

            <!-- Filtros e Botões -->
            <?php if (!empty($notificacoes)): ?>
                <div class="header-notificacoes">
                    <div class="filtros-notificacoes">
                        <a href="/RecycleWays/notificacoes" class="btn-filtro <?= !$mostrarTodas ? 'ativo' : '' ?>">
                            <i class="fas fa-circle"></i> Novas
                            <?php if ($naoLidas > 0): ?>
                                <span class="badge-filtro"><?= $naoLidas ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/RecycleWays/notificacoes?todas=1" class="btn-filtro <?= $mostrarTodas ? 'ativo' : '' ?>">
                            <i class="fas fa-check-circle"></i> Todas
                            <span class="badge-filtro"><?= count($notificacoes ?? []) ?></span>
                        </a>
                    </div>
                    
                    <?php if ($naoLidas > 0): ?>
                        <form action="/RecycleWays/notificacoes/marcar-todas-lidas" method="POST" class="form-marcar-todas">
                            <?= ViewHelper::csrfField() ?>
                            <button type="submit" class="btn-marcar-todas">
                                <i class="fas fa-check-double"></i> Marcar todas como lidas
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Lista de Notificações -->
            <?php if (empty($notificacoesExibir)): ?>
                <div class="empty-state">
                    <?php if ($mostrarTodas): ?>
                        <i class="fas fa-bell-slash"></i>
                        <h3>Nenhuma notificação</h3>
                        <p>Você não tem notificações no momento. Fique tranquilo!</p>
                        <a href="/RecycleWays/" class="btn btn-sm">
                            <i class="fas fa-home"></i> Início
                        </a>
                    <?php else: ?>
                        <i class="fas fa-check-circle" style="color: var(--color-impact-green);"></i>
                        <h3>Nehuma notificação nova!</h3>
                        <p>Você leu todas as notificações.</p>
                        <div style="display: flex; gap: 0.8rem; justify-content: center; flex-wrap: wrap; margin-top: 0.5rem;">
                            <a href="/RecycleWays/notificacoes?todas=1" class="btn btn-sm btn-outline">
                                <i class="fas fa-history"></i> Ver histórico
                            </a>
                            <a href="/RecycleWays/" class="btn btn-sm">
                                <i class="fas fa-home"></i> Início
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="lista-notificacoes">
                    <?php foreach ($notificacoesExibir as $notificacao): ?>
                        <div class="notificacao-item <?= $notificacao['lida'] ? 'lida' : 'nao-lida' ?>">
                            <div class="notificacao-conteudo">
                                <div class="notificacao-titulo">
                                    <?= htmlspecialchars($notificacao['titulo']) ?>
                                    <?php if (!$notificacao['lida']): ?>
                                        <span class="badge-nao-lida">
                                            <i class="fas fa-circle"></i> Nova
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="notificacao-mensagem">
                                    <?= htmlspecialchars($notificacao['mensagem']) ?>
                                </p>
                                <small class="notificacao-data">
                                    <i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($notificacao['data_criacao'])) ?>
                                </small>
                            </div>
                            <?php if (!$notificacao['lida']): ?>
                                <form action="/RecycleWays/notificacoes/marcar-lida" method="POST" class="form-marcar-lida">
                                    <?= ViewHelper::csrfField() ?>
                                    <input type="hidden" name="id_notificacao" value="<?= $notificacao['id_notificacao'] ?>">
                                    <button type="submit" class="btn-marcar-lida">
                                        <i class="fas fa-check"></i> Marcar como lida
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($mostrarTodas && count($notificacoesAntigas) > 0): ?>
                    <div class="info-historico">
                        <i class="fas fa-info-circle"></i> 
                        Mostrando todas as notificações. 
                        <a href="/RecycleWays/notificacoes">Ver apenas as novas</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Voltar -->
            <div class="notificacoes-voltar">
                <a href="/RecycleWays/" class="btn btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>