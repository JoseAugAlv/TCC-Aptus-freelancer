<?php
// app/Models/Usuario.php

require_once __DIR__ . '/../Config/database.php';

class Usuario
{
    private $conn;

    public function __construct()
    {
        $this->conn = AptusDatabase::getConnection();
    }

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
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
}