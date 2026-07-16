<?php
// app/Models/AptusNotificacao.php

require_once __DIR__ . '/../Config/database.php';

class AptusNotificacao
{
    private $conn;

    public function __construct()
    {
        $this->conn = AptusDatabase::getConnection();
    }

    /**
     * Cria uma nova notificação
     */
    public function criar($usuarioId, $tipo, $titulo, $mensagem, $interesseId = null, $tabelaOrigem = null, $registroId = null)
    {
        $sql = "INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuarioId, $interesseId, $tipo, $titulo, $mensagem, $tabelaOrigem, $registroId]);
    }

    /**
     * Busca notificações não lidas de um usuário
     */
    public function getNaoLidas($usuarioId)
    {
        $sql = "SELECT * FROM notificacao WHERE id_usuario = ? AND lida = FALSE ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca todas as notificações de um usuário
     */
    public function getTodas($usuarioId, $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM notificacao WHERE id_usuario = ? ORDER BY data_criacao DESC";
        
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

    /**
     * Conta notificações não lidas
     */
    public function contarNaoLidas($usuarioId)
    {
        $sql = "SELECT COUNT(*) as total FROM notificacao WHERE id_usuario = ? AND lida = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Marca uma notificação como lida
     */
    public function marcarLida($id, $usuarioId)
    {
        $sql = "UPDATE notificacao SET lida = TRUE, data_leitura = NOW() WHERE id_notificacao = ? AND id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id, $usuarioId]);
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function marcarTodasLidas($usuarioId)
    {
        $sql = "UPDATE notificacao SET lida = TRUE, data_leitura = NOW() WHERE id_usuario = ? AND lida = FALSE";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuarioId]);
    }

    /**
     * Busca notificações por tipo
     */
    public function getByTipo($usuarioId, $tipo)
    {
        $sql = "SELECT * FROM notificacao WHERE id_usuario = ? AND tipo = ? ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId, $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}