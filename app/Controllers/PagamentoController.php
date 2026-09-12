<?php
require_once __DIR__ . '/../Config/database.php';
class PagamentoController {
    private $conn;
    public function __construct() { $this->conn = Database::getConnection(); }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        $tituloPagina = 'Pagamentos - Aptus'; $cssPagina = 'pagamentos.css';
        require '../app/Views/pagamentos/index.php';
    }

    public function confirmar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        $tituloPagina = 'Confirmar Pagamento - Aptus'; $cssPagina = 'pagamentos.css';
        require '../app/Views/pagamentos/confirmar.php';
    }

    public function confirmarContratante() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        $id = (int)($_POST['interesse_id'] ?? 0);
        if ($id > 0) {
            $stmt = $this->conn->prepare("UPDATE confirmacao_pagamento SET confirmado_contratante = 1 WHERE id_interesse = ?");
            $stmt->execute([$id]);
        }
        header('Location: /Aptus/pagamentos/confirmar'); exit;
    }

    public function confirmarFreelancer() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        $id = (int)($_POST['interesse_id'] ?? 0);
        if ($id > 0) {
            $stmt = $this->conn->prepare("UPDATE confirmacao_pagamento SET confirmado_freelancer = 1 WHERE id_interesse = ?");
            $stmt->execute([$id]);
        }
        header('Location: /Aptus/pagamentos/confirmar'); exit;
    }
}
