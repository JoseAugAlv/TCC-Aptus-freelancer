<?php
require_once __DIR__ . '/../Helpers/SecurityHelper.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Categoria.php';

class AdminCategoriaController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['role'], [1,4])) {
            header('Location: /Aptus/login'); exit;
        }
        $model = new Categoria();
        $categorias = $model->getAll();
        $tituloPagina = 'Categorias - Admin';
        $cssPagina = 'admin.css';
        require __DIR__ . '/../Views/admin/categorias.php';
    }

    public function criar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['role'], [1,4])) {
            header('Location: /Aptus/login'); exit;
        }
        $tituloPagina = 'Nova Categoria - Admin';
        $cssPagina = 'admin.css';
        require __DIR__ . '/../Views/admin/categorias/criar.php';
    }

    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['role'], [1,4])) {
            header('Location: /Aptus/login'); exit;
        }
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $icone = trim($_POST['icone'] ?? 'fas fa-tag');
        if ($nome !== '') {
            $model = new Categoria();
            $model->create(['nome' => $nome, 'descricao' => $descricao, 'icone' => $icone]);
        }
        header('Location: /Aptus/admin/categorias'); exit;
    }

    public function editar($id = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['role'], [1,4])) {
            header('Location: /Aptus/login'); exit;
        }
        $model = new Categoria();
        $cat = $model->findById((int)$id);
        if (!$cat) { header('Location: /Aptus/admin/categorias'); exit; }
        $tituloPagina = 'Editar Categoria - Admin';
        $cssPagina = 'admin.css';
        require __DIR__ . '/../Views/admin/categorias/editar.php';
    }

    public function atualizar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['role'], [1,4])) {
            header('Location: /Aptus/login'); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $model = new Categoria();
            $model->update($id, [
                'nome' => trim($_POST['nome'] ?? ''),
                'descricao' => trim($_POST['descricao'] ?? ''),
                'icone' => trim($_POST['icone'] ?? 'fas fa-tag'),
            ]);
        }
        header('Location: /Aptus/admin/categorias'); exit;
    }

    public function excluir($id = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['role'], [1,4])) {
            header('Location: /Aptus/login'); exit;
        }
        if ($id > 0) {
            $model = new Categoria();
            $model->delete((int)$id);
        }
        header('Location: /Aptus/admin/categorias'); exit;
    }
}