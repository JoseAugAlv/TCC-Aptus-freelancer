<?php
// app/Controllers/ModeradorController.php

require_once __DIR__ . '/../Models/Moderador.php';
require_once __DIR__ . '/../Models/Anuncio.php';

class ModeradorController
{
    private $moderador;
    private $anuncio;

    public function __construct()
    {
        $this->moderador = new Moderador();
        $this->anuncio   = new Anuncio();
    }

    private function verificaPermissao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) {
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }
        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4])) {
            header(('Location: ' . aptus_base() . '/'));
            exit;
        }
    }

    public function index()
    {
        $this->verificaPermissao();

        $anunciosPendentes  = count($this->moderador->getAnunciosPendentes());
        $denunciasPendentes = count($this->moderador->getDenunciasPendentes());
        $disputasPendentes  = count($this->moderador->getDisputasPendentes());
        $totalUsuarios      = count($this->moderador->getUsuarios());
        $totalCategorias    = count($this->moderador->getCategorias());

        require '../app/Views/moderator/index.php';
    }

    /**
     * Lista TODOS os anúncios com filtro por status
     */
    public function anuncios()
    {
        $this->verificaPermissao();

        $status   = $_GET['status'] ?? null;
        $anuncios = $this->moderador->getTodosAnuncios($status);

        require '../app/Views/moderator/anuncios.php';
    }

    public function aprovarAnuncio()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { header(('Location: ' . aptus_base() . '/moderator/anuncios')); exit; }
        $this->bloquearAnuncioProprio($id);

        $this->anuncio->aprovar($id);
        $this->notificarFreelancer($id, 'aprovado');

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio aprovado com sucesso!'];
        header(('Location: ' . aptus_base() . '/moderator/anuncios'));
        exit;
    }

    public function rejeitarAnuncio()
    {
        $this->verificaPermissao();

        $id     = (int) ($_POST['id'] ?? 0);
        $motivo = trim((string) ($_POST['motivo'] ?? '')) ?: 'Conteúdo inadequado';
        $motivo = mb_substr($motivo, 0, 200);
        if ($id <= 0) { header(('Location: ' . aptus_base() . '/moderator/anuncios')); exit; }
        $this->bloquearAnuncioProprio($id);

        $this->anuncio->rejeitar($id, $motivo);
        $this->notificarFreelancer($id, 'rejeitado', $motivo);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio rejeitado com sucesso!'];
        header(('Location: ' . aptus_base() . '/moderator/anuncios'));
        exit;
    }

    /**
     * Revoga um anúncio já aprovado: ele sai do ar, o dono é notificado e só
     * volta após editar e passar por nova aprovação.
     */
    public function revogarAnuncio()
    {
        $this->verificaPermissao();

        $id     = (int) ($_POST['id'] ?? 0);
        $motivo = trim((string) ($_POST['motivo'] ?? ''));
        if ($id <= 0) { header(('Location: ' . aptus_base() . '/moderator/anuncios')); exit; }
        $this->bloquearAnuncioProprio($id);

        if ($motivo === '' || mb_strlen($motivo) > 200) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Informe o motivo da revogação (até 200 caracteres).'];
            header(('Location: ' . aptus_base() . '/moderator/anuncios?status=2'));
            exit;
        }

        if ($this->anuncio->revogar($id, $motivo)) {
            $this->notificarFreelancer($id, 'revogado', $motivo);
            require_once __DIR__ . '/../Helpers/SecurityHelper.php';
            SecurityHelper::logAuditoria('anuncio_revogado', $_SESSION['usuario']['id'], "Anúncio {$id}: {$motivo}", 'warning');
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio revogado. O anunciante foi notificado.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'aviso', 'mensagem' => 'Só é possível revogar anúncios aprovados.'];
        }
        header(('Location: ' . aptus_base() . '/moderator/anuncios?status=2'));
        exit;
    }

    /**
     * NOVO: suspender anúncio (pausar sem rejeitar)
     */
    public function suspenderAnuncio()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { header(('Location: ' . aptus_base() . '/moderator/anuncios')); exit; }
        $this->bloquearAnuncioProprio($id);

        $this->moderador->suspenderAnuncio($id);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio suspenso.'];
        header(('Location: ' . aptus_base() . '/moderator/anuncios'));
        exit;
    }

    private function notificarFreelancer($anuncioId, $status, $motivo = null)
    {
        try {
            $pdo  = Database::getConnection();
            $sql  = "SELECT a.id_usuario, a.titulo FROM anuncio_servico a WHERE a.id_anuncio = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$anuncioId]);
            $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$anuncio) return;

            $motivoTxt = $motivo ?: 'Não informado';
            [$titulo, $mensagem] = match ($status) {
                'aprovado' => ['Seu anúncio foi aprovado!',
                               "Seu anúncio '{$anuncio['titulo']}' foi aprovado e está disponível para visualização."],
                'revogado' => ['Seu anúncio foi revogado',
                               "A aprovação do anúncio '{$anuncio['titulo']}' foi revogada e ele saiu do ar. Motivo: {$motivoTxt}. "
                               . 'Edite o anúncio para corrigir e ele voltará para nova análise.'],
                default    => ['Seu anúncio foi rejeitado',
                               "Seu anúncio '{$anuncio['titulo']}' foi rejeitado. Motivo: {$motivoTxt}. "
                               . 'Edite o anúncio para corrigir e ele voltará para nova análise.'],
            };

            $sql  = "INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $anuncio['id_usuario'], 'moderacao_anuncio', $titulo, $mensagem,
                'anuncio_servico', $anuncioId
            ]);
        } catch (Exception $e) {
            error_log('Erro ao notificar freelancer: ' . $e->getMessage());
        }
    }

    public function denuncias()
    {
        $this->verificaPermissao();
        require_once __DIR__ . '/../Models/Denuncia.php';
        $denunciaModel  = new Denuncia();
        $denuncias      = $denunciaModel->listarHistorico($_GET);
        $totalPendentes = $denunciaModel->countPendentes();
        require '../app/Views/moderator/denuncias.php';
    }

    public function usuarios()
    {
        $this->verificaPermissao();
        $usuarios = $this->moderador->getUsuarios();
        require '../app/Views/moderator/usuarios.php';
    }

    public function categorias()
    {
        $this->verificaPermissao();
        $categorias = $this->moderador->getCategorias();
        require '../app/Views/moderator/categorias.php';
    }
    public function salvarCategoria()
    {
        $this->verificaPermissao();

        $nome      = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $icone     = trim($_POST['icone'] ?? 'fas fa-tag');

        if ($nome === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Informe o nome da categoria.'];
            header(('Location: ' . aptus_base() . '/moderator/categorias'));
            exit;
        }

        // Verifica duplicidade
        require_once __DIR__ . '/../Models/Categoria.php';
        $model = new Categoria();

        if ($model->findByNome($nome)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Já existe uma categoria com este nome.'];
            header(('Location: ' . aptus_base() . '/moderator/categorias'));
            exit;
        }

        if ($model->create(['nome' => $nome, 'descricao' => $descricao, 'icone' => $icone])) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Categoria criada com sucesso.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível criar a categoria.'];
        }

        header(('Location: ' . aptus_base() . '/moderator/categorias'));
        exit;
    }

    public function editarCategoria()
    {
        $this->verificaPermissao();

        $id        = (int) ($_POST['id'] ?? 0);
        $nome      = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $icone     = trim($_POST['icone'] ?? 'fas fa-tag');

        if ($id <= 0 || $nome === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Dados inválidos.'];
            header(('Location: ' . aptus_base() . '/moderator/categorias'));
            exit;
        }

        require_once __DIR__ . '/../Models/Categoria.php';
        $model = new Categoria();

        if ($model->update($id, ['nome' => $nome, 'descricao' => $descricao, 'icone' => $icone])) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Categoria atualizada.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível atualizar.'];
        }

        header(('Location: ' . aptus_base() . '/moderator/categorias'));
        exit;
    }

    public function excluirCategoria()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            header(('Location: ' . aptus_base() . '/moderator/categorias'));
            exit;
        }

        require_once __DIR__ . '/../Models/Categoria.php';
        $model = new Categoria();

        if ($model->delete($id)) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Categoria removida.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível remover.'];
        }

        header(('Location: ' . aptus_base() . '/moderator/categorias'));
        exit;
    }

    /**
     * Moderador não pode moderar o próprio anúncio (conflito de interesse).
     */
    private function bloquearAnuncioProprio(int $anuncioId): void
    {
        $anuncio = $this->anuncio->findById($anuncioId);
        if ($anuncio && (int) $anuncio['id_usuario'] === (int) $_SESSION['usuario']['id']) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não pode moderar o seu próprio anúncio.'];
            header(('Location: ' . aptus_base() . '/moderator/anuncios'));
            exit;
        }
    }
}
