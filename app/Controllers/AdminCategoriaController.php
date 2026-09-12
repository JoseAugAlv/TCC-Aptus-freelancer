<?php
// app/Controllers/AdminCategoriaController.php
require_once __DIR__ . '/../Helpers/SecurityHelper.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Categoria.php';

class AdminCategoriaController
{
    private function verificarPermissao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int) $_SESSION['usuario']['role'], [1, 4], true)) {
            header('Location: /Aptus/login');
            exit;
        }
    }

    public function index()
    {
        $this->verificarPermissao();

        $model        = new Categoria();
        $categorias   = $model->getAll();
        $tituloPagina = 'Categorias - Admin';
        $cssPagina    = 'admin.css';

        require __DIR__ . '/../Views/admin/categorias.php';
    }

    public function criar()
    {
        $this->verificarPermissao();

        $tituloPagina = 'Nova Categoria - Admin';
        $cssPagina    = 'admin.css';

        require __DIR__ . '/../Views/admin/categorias/criar.php';
    }

    public function salvar()
    {
        $this->verificarPermissao();

        $nome      = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $icone     = trim($_POST['icone'] ?? 'fas fa-tag');

        if ($nome !== '') {
            $model = new Categoria();
            $model->create(['nome' => $nome, 'descricao' => $descricao, 'icone' => $icone]);
        }

        header('Location: /Aptus/admin/categorias');
        exit;
    }

    public function editar($id = null)
    {
        $this->verificarPermissao();

        $model = new Categoria();
        $cat   = $model->findById((int) $id);

        if (!$cat) {
            header('Location: /Aptus/admin/categorias');
            exit;
        }

        $tituloPagina = 'Editar Categoria - Admin';
        $cssPagina    = 'admin.css';

        require __DIR__ . '/../Views/admin/categorias/editar.php';
    }

    public function atualizar()
    {
        $this->verificarPermissao();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $model = new Categoria();
            $model->update($id, [
                'nome'      => trim($_POST['nome'] ?? ''),
                'descricao' => trim($_POST['descricao'] ?? ''),
                'icone'     => trim($_POST['icone'] ?? 'fas fa-tag'),
            ]);
        }

        header('Location: /Aptus/admin/categorias');
        exit;
    }

    /**
     * Exclusão via POST (era GET, vulnerável a CSRF).
     * O id agora vem do corpo do POST.
     */
    public function excluir()
    {
        $this->verificarPermissao();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $model = new Categoria();
            $model->delete($id);
            $_SESSION['flash'] = [
                'tipo'     => 'sucesso',
                'mensagem' => 'Categoria removida com sucesso.',
            ];
        }

        header('Location: /Aptus/admin/categorias');
        exit;
    }
}