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

    /**
     * Busca todas as categorias ativas
     */
    public function getAll()
    {
        $sql = "SELECT * FROM categoria WHERE ativo = TRUE ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca categorias com contagem de anúncios (populares)
     */
    public function getPopulares($limit = 8)
    {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM anuncio_servico a 
                 WHERE a.id_categoria = c.id_categoria 
                 AND a.situacao = 'ativo' 
                 AND a.id_situacao_moderacao = 2) as total_anuncios
                FROM categoria c 
                WHERE c.ativo = TRUE 
                ORDER BY total_anuncios DESC 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca categoria por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM categoria WHERE id_categoria = ? AND ativo = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca categoria por nome
     */
    public function findByNome($nome)
    {
        $sql = "SELECT * FROM categoria WHERE nome = ? AND ativo = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nome]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria uma nova categoria (RF18 - Moderador/Admin)
     */
    public function create($data)
    {
        $sql = "INSERT INTO categoria (nome, descricao, icone) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['icone'] ?? 'fas fa-tag'
        ]);
    }

    /**
     * Atualiza uma categoria (RF18 - Moderador/Admin)
     */
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
        $sql = "UPDATE categoria SET " . implode(", ", $fields) . " WHERE id_categoria = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Exclui (desativa) uma categoria (RF18 - Moderador/Admin)
     */
    public function delete($id)
    {
        $sql = "UPDATE categoria SET ativo = FALSE WHERE id_categoria = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Total de categorias ativas
     */
    public function getTotal()
    {
        $sql = "SELECT COUNT(*) as total FROM categoria WHERE ativo = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}