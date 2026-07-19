<?php
// app/Models/Avaliacao.php

require_once __DIR__ . '/../Config/database.php';

class Avaliacao
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Cria uma nova avaliação
     */
    public function create($data)
    {
        $sql = "INSERT INTO avaliacao (id_interesse, id_avaliador, id_avaliado, nota, comentario) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['id_interesse'],
            $data['id_avaliador'],
            $data['id_avaliado'],
            $data['nota'],
            $data['comentario']
        ]);
    }

    /**
     * Busca avaliação por interesse
     */
    public function findByInteresse($interesseId)
    {
        $sql = "SELECT a.*, 
                       av.nome as avaliador_nome, av.foto_perfil as avaliador_foto,
                       avd.nome as avaliado_nome, avd.foto_perfil as avaliado_foto,
                       i.situacao as interesse_situacao
                FROM avaliacao a
                JOIN usuario av ON a.id_avaliador = av.id_usuario
                JOIN usuario avd ON a.id_avaliado = avd.id_usuario
                JOIN interesse i ON a.id_interesse = i.id_interesse
                WHERE a.id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca avaliações de um usuário (como avaliado)
     */
    public function getByAvaliado($usuarioId, $limit = null)
    {
        $sql = "SELECT a.*, 
                       av.nome as avaliador_nome, av.foto_perfil as avaliador_foto,
                       i.situacao as interesse_situacao,
                       an.titulo as anuncio_titulo
                FROM avaliacao a
                JOIN usuario av ON a.id_avaliador = av.id_usuario
                JOIN interesse i ON a.id_interesse = i.id_interesse
                JOIN anuncio_servico an ON i.id_anuncio = an.id_anuncio
                WHERE a.id_avaliado = ?
                ORDER BY a.data_avaliacao DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$usuarioId, $limit]);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$usuarioId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca avaliações feitas por um usuário (como avaliador)
     */
    public function getByAvaliador($usuarioId)
    {
        $sql = "SELECT a.*, 
                       avd.nome as avaliado_nome, avd.foto_perfil as avaliado_foto,
                       i.situacao as interesse_situacao,
                       an.titulo as anuncio_titulo
                FROM avaliacao a
                JOIN usuario avd ON a.id_avaliado = avd.id_usuario
                JOIN interesse i ON a.id_interesse = i.id_interesse
                JOIN anuncio_servico an ON i.id_anuncio = an.id_anuncio
                WHERE a.id_avaliador = ?
                ORDER BY a.data_avaliacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se já existe avaliação para um interesse
     */
    public function exists($interesseId)
    {
        $sql = "SELECT id_avaliacao FROM avaliacao WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Responde uma avaliação (freelancer)
     */
    public function responder($id, $resposta)
    {
        $sql = "UPDATE avaliacao SET resposta_avaliado = ?, data_resposta = NOW() WHERE id_avaliacao = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$resposta, $id]);
    }

    /**
     * Recalcula nota média do usuário
     */
    public function recalcularNotaMedia($usuarioId)
    {
        $sql = "SELECT AVG(nota) as media, COUNT(*) as total 
                FROM avaliacao 
                WHERE id_avaliado = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $media = round($result['media'] ?? 0, 2);
        $total = $result['total'] ?? 0;
        
        $sql = "UPDATE usuario SET nota_media = ?, total_avaliacoes = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$media, $total, $usuarioId]);
    }
}