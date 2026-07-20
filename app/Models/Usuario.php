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
     * Busca usuario por email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca usuario por ID
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
     * Busca usuario por token de verificacao
     */
    public function findByToken($token)
    {
        $sql = "SELECT * FROM usuario WHERE token_verificacao = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna o total de usuarios ativos
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
     * Cria um novo usuario
     */
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
            $data['token_verificacao']
        ]);
    }

    /**
     * Verifica o email do usuario
     */
    public function verificarEmail($token)
    {
        $sql = "UPDATE usuario SET email_verificado = 1, data_verificacao = NOW(), token_verificacao = NULL 
                WHERE token_verificacao = ? AND email_verificado = 0";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$token]);
    }

    /**
     * Atualiza dados do usuario
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

    /**
     * Salva token de reset de senha
     */
    public function salvarTokenReset($email, $token)
    {
        // Buscar usuario
        $usuario = $this->findByEmail($email);
        if (!$usuario) {
            return false;
        }

        // Invalidar tokens anteriores
        $sql = "UPDATE reset_senha SET usado = 1 WHERE id_usuario = ? AND usado = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuario['id_usuario']]);

        // Inserir novo token
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $sql = "INSERT INTO reset_senha (id_usuario, token, expiracao) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuario['id_usuario'], $token, $expiracao]);
    }

    /**
     * Busca token de reset de senha
     */
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

    /**
     * Redefine a senha do usuario
     */
    public function redefinirSenha($token, $novaSenha)
    {
        $tokenData = $this->findTokenReset($token);
        if (!$tokenData) {
            return false;
        }

        // Atualizar senha
        $sql = "UPDATE usuario SET senha = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([password_hash($novaSenha, PASSWORD_DEFAULT), $tokenData['id_usuario']]);

        // Marcar token como usado
        $sql = "UPDATE reset_senha SET usado = 1 WHERE token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);

        return true;
    }
}