<?php
// app/Models/Interesse.php

require_once __DIR__ . '/../Config/database.php';

class Interesse
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Busca um interesse por ID
     */
    public function findById($id)
    {
        $sql = "SELECT i.*, 
                       a.titulo as anuncio_titulo, a.preco as anuncio_preco,
                       c.nome as contratante_nome, c.email as contratante_email, c.telefone as contratante_telefone,
                       f.nome as freelancer_nome, f.email as freelancer_email, f.telefone as freelancer_telefone
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE i.id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca interesses por contratante
     */
    public function findByContratante($usuarioId)
    {
        $sql = "SELECT i.*, 
                       a.titulo as anuncio_titulo, a.preco as anuncio_preco,
                       f.nome as freelancer_nome, f.foto_perfil as freelancer_foto
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE i.id_contratante = ?
                ORDER BY i.data_interesse DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca interesses por freelancer
     */
    public function findByFreelancer($usuarioId)
    {
        $sql = "SELECT i.*, 
                       a.titulo as anuncio_titulo, a.preco as anuncio_preco,
                       c.nome as contratante_nome, c.foto_perfil as contratante_foto
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                WHERE i.id_freelancer = ?
                ORDER BY i.data_interesse DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se já existe interesse ativo para um anúncio e contratante
     */
    public function existsAtivo($anuncioId, $contratanteId)
    {
        $sql = "SELECT id_interesse FROM interesse 
                WHERE id_anuncio = ? AND id_contratante = ? AND situacao IN ('ativo', 'concluido')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId, $contratanteId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Cria um novo interesse
     */
    public function create($data)
    {
        $sql = "INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['id_anuncio'],
            $data['id_contratante'],
            $data['id_freelancer'],
            $data['mensagem_inicial'] ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    /**
     * Atualiza situação do interesse
     */
    public function updateSituacao($id, $situacao)
    {
        $sql = "UPDATE interesse SET situacao = ? WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$situacao, $id]);
    }

    /**
     * Conclui um interesse
     */
    public function concluir($id)
    {
        $sql = "UPDATE interesse SET situacao = 'concluido', data_conclusao = NOW() WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Cancela um interesse
     */
    public function cancelar($id)
    {
        $sql = "UPDATE interesse SET situacao = 'cancelado' WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Busca interesse com dados completos para notificações
     */
    public function getComDados($id)
    {
        $sql = "SELECT i.*, 
                       a.titulo as anuncio_titulo, a.id_usuario as id_freelancer,
                       c.nome as contratante_nome,
                       f.nome as freelancer_nome
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE i.id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Total de interesses ativos de um freelancer
     */
    public function countAtivosByFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_freelancer = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Total de interesses concluídos de um freelancer
     */
    public function countConcluidosByFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_freelancer = ? AND situacao = 'concluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}