<?php
require_once __DIR__ . '/../Helpers/SecurityHelper.php';
require_once __DIR__ . '/../Core/Auth.php';
class AdminCategoriaController {
    public function index() { require_once __DIR__ . '/../Views/admin/categorias.php'; }
    public function criar() { require_once __DIR__ . '/../Views/admin/categorias/criar.php'; }
    public function salvar() { header('Location: /Aptus/admin/categorias'); exit; }
    public function editar($id = null) { require_once __DIR__ . '/../Views/admin/categorias/editar.php'; }
    public function atualizar() { header('Location: /Aptus/admin/categorias'); exit; }
    public function excluir($id = null) { header('Location: /Aptus/admin/categorias'); exit; }
}
