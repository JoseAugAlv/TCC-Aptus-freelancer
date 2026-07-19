<?php
// app/Models/Notificacao.php

require_once __DIR__ . '/../Config/database.php';

class Notificacao
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getNaoLidas($usuarioId)
    {
        $sql = "SELECT * FROM notificacao 
                WHERE id_usuario = ? AND lida = FALSE 
                ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTodas($usuarioId, $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM notificacao 
                WHERE id_usuario = ? 
                ORDER BY data_criacao DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$usuarioId, $limit, $offset]);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$usuarioId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarNaoLidas($usuarioId)
    {
        $sql = "SELECT COUNT(*) as total FROM notificacao 
                WHERE id_usuario = ? AND lida = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function marcarLida($id, $usuarioId)
    {
        $sql = "UPDATE notificacao 
                SET lida = TRUE, data_leitura = NOW() 
                WHERE id_notificacao = ? AND id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id, $usuarioId]);
    }

    public function marcarTodasLidas($usuarioId)
    {
        $sql = "UPDATE notificacao 
                SET lida = TRUE, data_leitura = NOW() 
                WHERE id_usuario = ? AND lida = FALSE";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuarioId]);
    }
}