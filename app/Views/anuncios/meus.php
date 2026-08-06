<?php
// app/Views/anuncios/meus.php

$tituloPagina = $tituloPagina ?? 'Meus Anúncios - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncios = $anuncios ?? [];
?>

<div class="anuncio-container">
    <div class="anuncio-header">
        <h1>Meus Anúncios</h1>
        <p>Gerencie seus serviços</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="btn-criar-container">
        <a href="/Aptus/anuncios/criar" class="btn-criar">
            <i class="fas fa-plus"></i> Criar Novo Anúncio
        </a>
    </div>

    <?php if (empty($anuncios)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>Você ainda não possui anúncios.</p>
            <p style="font-size: 0.9rem; color: #94a8b4;">Comece agora mesmo criando seu primeiro serviço</p>
            <a href="/Aptus/anuncios/criar" class="btn-criar">
                <i></i> Criar primeiro anúncio
            </a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Interesses</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anuncios as $anuncio): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($anuncio['titulo']) ?></strong></td>
                            <td><?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Sem categoria') ?></td>
                            <td class="preco">R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?></td>
                            <td>
                                <span class="status-badge <?= $anuncio['situacao'] ?>">
                                    <?= ucfirst($anuncio['situacao']) ?>
                                </span>
                                <?php if (($anuncio['id_situacao_moderacao'] ?? 0) == 1): ?>
                                    <span class="status-badge pendente" style="margin-left: 0.3rem;">
                                        Pendente
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="interesses">
                                    <i class="fas fa-heart"></i> 
                                    <?= $anuncio['total_interesses'] ?? 0 ?>
                                </span>
                            </td>
                            <td class="data"><?= date('d/m/Y', strtotime($anuncio['data_criacao'])) ?></td>
                            <td>
                                <div class="acoes">
                                    <a href="/Aptus/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>" class="btn-acao ver" title="Ver">
                                        <i class="fas fa-eye"></i> <span>Ver</span>
                                    </a>
                                    <a href="/Aptus/anuncios/editar/<?= $anuncio['id_anuncio'] ?>" class="btn-acao editar" title="Editar">
                                        <i class="fas fa-edit"></i> <span>Editar</span>
                                    </a>
                                    <?php if ($anuncio['situacao'] == 'ativo'): ?>
                                        <form method="POST" action="/Aptus/anuncios/pausar">
                                            <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                                            <button type="submit" class="btn-acao pausar" title="Pausar">
                                                <i class="fas fa-pause"></i> <span>Pausar</span>
                                            </button>
                                        </form>
                                    <?php elseif ($anuncio['situacao'] == 'pausado'): ?>
                                        <form method="POST" action="/Aptus/anuncios/ativar">
                                            <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                                            <button type="submit" class="btn-acao ativar" title="Ativar">
                                                <i class="fas fa-play"></i> <span>Ativar</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="/Aptus/anuncios/excluir/<?= $anuncio['id_anuncio'] ?>" 
                                       class="btn-acao excluir" 
                                       title="Excluir"
                                       onclick="return confirm('Tem certeza que deseja excluir este anúncio?')">
                                        <i class="fas fa-trash"></i> <span>Excluir</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="anuncio-voltar">
        <a href="/Aptus/"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>