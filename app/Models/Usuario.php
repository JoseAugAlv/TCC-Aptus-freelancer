<?php
// app/Models/Usuario.php

require_once __DIR__ . '/../Config/database.php';

class Usuario
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Busca usuário por email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca usuário por ID
     */
    public function findById($id)
    {
        $sql = "SELECT u.*, p.perfil as nome_perfil 
                FROM usuario u 
                LEFT JOIN perfil p ON u.id_perfil = p.id_perfil 
                WHERE u.id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna o total de usuários ativos
     */
    public function getTotalAtivos()
    {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE ativo = TRUE AND banido = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Atualiza dados do usuário
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['nome', 'email', 'telefone', 'whatsapp', 'cpf_cnpj', 'data_nascimento', 'foto_perfil', 'bio', 'cidade', 'estado'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['senha'])) {
            $fields[] = "senha = ?";
            $params[] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE usuario SET " . implode(", ", $fields) . " WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
}