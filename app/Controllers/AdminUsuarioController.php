<?php
require_once __DIR__ . '/../Config/database.php';
class AdminUsuarioController {
    private $conn;
    public function __construct() { $this->conn = Database::getConnection(); }

    public function atualizar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['role'], [1,4])) { header('Location: /Aptus/login'); exit; }
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($id > 0 && $nome && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $this->conn->prepare("UPDATE usuario SET nome = ?, email = ? WHERE id_usuario = ?");
            $stmt->execute([$nome, $email, $id]);
        }
        header('Location: /Aptus/admin/usuarios'); exit;
    }

    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['role'], [1,4])) { header('Location: /Aptus/login'); exit; }
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $stmt = $this->conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
            $stmt->execute([$id]);
        }
        header('Location: /Aptus/admin/usuarios'); exit;
    }

    public function banir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['role'], [1,4])) { header('Location: /Aptus/login'); exit; }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $this->conn->prepare("UPDATE usuario SET ativo = 0 WHERE id_usuario = ?");
            $stmt->execute([$id]);
        }
        header('Location: /Aptus/admin/usuarios'); exit;
    }

    public function desbanir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['role'], [1,4])) { header('Location: /Aptus/login'); exit; }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $this->conn->prepare("UPDATE usuario SET ativo = 1 WHERE id_usuario = ?");
            $stmt->execute([$id]);
        }
        header('Location: /Aptus/admin/usuarios'); exit;
    }
}
