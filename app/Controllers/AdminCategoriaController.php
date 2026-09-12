<?php
require_once __DIR__ . '/../Helpers/SecurityHelper.php';
require_once __DIR__ . '/../Core/Auth.php';
class AdminCategoriaController {
    public function index() { require_once __DIR__ . '/../Views/admin/categorias.php';
    header("Location: /Aptus/admin/categorias"); exit;  }
    public function criar() { require_once __DIR__ . '/../Views/admin/categorias/criar.php'; }
    public function salvar() { require_once __DIR__ . '/../Models/Categoria.php'; $m = new Categoria(); if (!empty($_POST['nome'])) $m->salvar($_POST['nome'], $_POST['descricao'] ?? ''); header('Location: /Aptus/admin/categorias'); exit; }
    public function editar($id = null) { require_once __DIR__ . '/../Models/Categoria.php'; $m = new Categoria(); $cat = $m->findById((int)$id); require __DIR__ . '/../Views/admin/categorias/editar.php'; }
    public function atualizar() { require_once __DIR__ . '/../Models/Categoria.php'; $m = new Categoria(); if (!empty($_POST['id'])) $m->atualizar((int)$_POST['id'], $_POST['nome'] ?? '', $_POST['descricao'] ?? ''); header('Location: /Aptus/admin/categorias'); exit; }
    public function excluir($id = null) { require_once __DIR__ . '/../Models/Categoria.php'; $m = new Categoria(); $m->excluir((int)$id); header('Location: /Aptus/admin/categorias'); exit; }
}
