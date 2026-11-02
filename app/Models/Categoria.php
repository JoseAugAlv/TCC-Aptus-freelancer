<?php
// app/Models/Categoria.php

require_once __DIR__ . '/../Config/database.php';

class Categoria
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT * FROM categoria WHERE ativo = TRUE ORDER BY nome";
        return Database::fetchAll($sql, [], 300);
    }

    public function getPopulares($limit = 8)
    {
        $limit = max(1, (int) $limit);
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM anuncio_servico a
                WHERE a.id_categoria = c.id_categoria
                AND a.situacao = 'ativo'
                AND a.id_situacao_moderacao = 2) as total_anuncios
                FROM categoria c
                WHERE c.ativo = TRUE
                ORDER BY total_anuncios DESC
                LIMIT ?";
        return Database::fetchAll($sql, [$limit], 120);
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM categoria WHERE id_categoria = ? AND ativo = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByNome($nome)
    {
        $sql = "SELECT * FROM categoria WHERE nome = ? AND ativo = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nome]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO categoria (nome, descricao, icone) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $ok   = $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['icone'] ?? 'fas fa-tag'
        ]);
        Database::clearCache();
        return $ok;
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [];

        $allowedFields = ['nome', 'descricao', 'icone', 'ativo'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql  = "UPDATE categoria SET " . implode(", ", $fields) . " WHERE id_categoria = ?";
        $stmt = $this->conn->prepare($sql);
        $ok   = $stmt->execute($params);
        Database::clearCache();
        return $ok;
    }

    public function delete($id)
    {
        $sql  = "UPDATE categoria SET ativo = FALSE WHERE id_categoria = ?";
        $stmt = $this->conn->prepare($sql);
        $ok   = $stmt->execute([$id]);
        Database::clearCache();
        return $ok;
    }

    public function getTotal()
    {
        $sql = "SELECT COUNT(*) as total FROM categoria WHERE ativo = TRUE";
        return (int) Database::fetchColumn($sql, [], 60);
    }
}