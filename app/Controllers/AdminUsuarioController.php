<?php
require_once __DIR__ . '/../Config/database.php';
class AdminUsuarioController {
    private $conn;
    public function __construct() { $this->conn = Database::getConnection(); }
    public function atualizar() { header('Location: /Aptus/admin/usuarios'); exit; }
    public function excluir() { header('Location: /Aptus/admin/usuarios'); exit; }
    public function banir() { header('Location: /Aptus/admin/usuarios'); exit; }
    public function desbanir() { header('Location: /Aptus/admin/usuarios'); exit; }
}
