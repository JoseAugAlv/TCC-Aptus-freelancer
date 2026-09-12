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

    public function findByEmail($email)
    {
        $sql  = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT u.*, p.perfil AS nome_perfil
                FROM usuario u
                LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
                WHERE u.id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByToken($token)
    {
        $sql  = "SELECT * FROM usuario WHERE token_verificacao = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalAtivos()
    {
        $sql  = "SELECT COUNT(*) AS total FROM usuario WHERE ativo = TRUE AND banido = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function create($data)
    {
        $sql = "INSERT INTO usuario (id_perfil, nome, email, senha, token_verificacao, email_verificado, data_criacao)
                VALUES (?, ?, ?, ?, ?, 0, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['id_perfil'] ?? 3,
            $data['nome'],
            $data['email'],
            password_hash($data['senha'], PASSWORD_DEFAULT),
            $data['token_verificacao'] ?? null,
        ]);
    }

    /**
     * FIX CRÍTICO: retorna rowCount > 0 para o controller saber se de fato verificou.
     */
    public function verificarEmail($token)
    {
        $sql = "UPDATE usuario
                SET email_verificado = 1,
                    data_verificacao = NOW(),
                    token_verificacao = NULL
                WHERE token_verificacao = ? AND email_verificado = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [];

        $allowedFields = [
            'nome', 'email', 'telefone', 'whatsapp', 'cpf_cnpj',
            'data_nascimento', 'foto_perfil', 'bio', 'cidade', 'estado',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (isset($data['senha']) && $data['senha'] !== '') {
            $fields[] = "senha = ?";
            $params[] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql  = "UPDATE usuario SET " . implode(", ", $fields) . " WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function trocarEmail($id, $novoEmail, $novoToken)
    {
        $sql = "UPDATE usuario
                SET email = ?,
                    email_verificado = 0,
                    token_verificacao = ?,
                    data_verificacao = NULL
                WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$novoEmail, $novoToken, $id]);
    }

    public function salvarTokenReset($email, $token)
    {
        $usuario = $this->findByEmail($email);
        if (!$usuario) {
            return false;
        }

        $sql  = "UPDATE reset_senha SET usado = 1 WHERE id_usuario = ? AND usado = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuario['id_usuario']]);

        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $sql  = "INSERT INTO reset_senha (id_usuario, token, expiracao) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuario['id_usuario'], $token, $expiracao]);
    }

    public function findTokenReset($token)
    {
        $sql = "SELECT r.*, u.id_usuario, u.email, u.nome
                FROM reset_senha r
                JOIN usuario u ON r.id_usuario = u.id_usuario
                WHERE r.token = ? AND r.usado = 0 AND r.expiracao > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function redefinirSenha($token, $novaSenha)
    {
        $tokenData = $this->findTokenReset($token);
        if (!$tokenData) {
            return false;
        }

        $sql  = "UPDATE usuario SET senha = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            password_hash($novaSenha, PASSWORD_DEFAULT),
            $tokenData['id_usuario'],
        ]);

        $sql  = "UPDATE reset_senha SET usado = 1 WHERE token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);

        return true;
    }
}