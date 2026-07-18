<?php
// app/Models/Portfolio.php

require_once __DIR__ . '/../Config/database.php';

class Portfolio
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Busca todos os itens de um usuário
     */
    public function getByUsuario($usuarioId)
    {
        $sql = "SELECT * FROM portfolio WHERE id_usuario = ? ORDER BY ordem ASC, data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um item por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM portfolio WHERE id_portfolio = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um novo item no portfólio
     */
    public function create($data)
    {
        $sql = "INSERT INTO portfolio (id_usuario, titulo, descricao, imagem) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['id_usuario'],
            $data['titulo'],
            $data['descricao'] ?? null,
            $data['imagem'] ?? null
        ]);
    }

    /**
     * Atualiza um item do portfólio
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['titulo', 'descricao', 'imagem', 'ordem'];
        
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
        $sql = "UPDATE portfolio SET " . implode(", ", $fields) . " WHERE id_portfolio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Remove um item do portfólio
     */
    public function delete($id)
    {
        $sql = "DELETE FROM portfolio WHERE id_portfolio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Busca itens do portfólio de um usuário (público)
     */
    public function getPublicoByUsuario($usuarioId, $limit = 6)
    {
        $sql = "SELECT * FROM portfolio WHERE id_usuario = ? ORDER BY ordem ASC, data_criacao DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}