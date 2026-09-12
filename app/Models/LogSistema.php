<?php
require_once __DIR__ . '/../Config/database.php';
class LogSistema {
    private $conn;
    public function __construct() { $this->conn = Database::getConnection(); }
    public function getWithPagination($limit, $offset) {
        $sql = "SELECT l.*, u.nome AS usuario_nome FROM log_sistema l LEFT JOIN usuario u ON l.id_usuario = u.id_usuario ORDER BY l.data_criacao DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getTotal() {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM log_sistema");
        return (int) $stmt->fetchColumn();
    }
    public function getTabelas() {
        $stmt = $this->conn->query("SELECT DISTINCT tabela_afetada FROM log_sistema WHERE tabela_afetada IS NOT NULL");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    public function getAcoes() {
        $stmt = $this->conn->query("SELECT DISTINCT acao FROM log_sistema WHERE acao IS NOT NULL");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
