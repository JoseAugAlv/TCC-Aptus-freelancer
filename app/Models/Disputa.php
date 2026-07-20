<?php
// app/Models/Disputa.php

require_once __DIR__ . '/../Config/database.php';

class Disputa
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Cria uma nova disputa
     */
    public function create($data)
    {
        $sql = "INSERT INTO disputa (id_interesse, id_aberto_por, motivo, descricao) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['id_interesse'],
            $data['id_aberto_por'],
            $data['motivo'],
            $data['descricao']
        ]);
    }

    /**
     * Busca disputa por ID
     */
    public function findById($id)
    {
        $sql = "SELECT d.*, 
                       u.nome as aberto_por_nome,
                       u.email as aberto_por_email,
                       s.situacao as situacao_nome,
                       i.id_contratante, i.id_freelancer,
                       a.titulo as anuncio_titulo,
                       c.nome as contratante_nome,
                       f.nome as freelancer_nome,
                       cp.situacao_final as pagamento_situacao,
                       cp.valor_informado_contratante,
                       cp.valor_informado_freelancer
                FROM disputa d
                JOIN usuario u ON d.id_aberto_por = u.id_usuario
                JOIN situacao s ON d.id_situacao = s.id_situacao
                JOIN interesse i ON d.id_interesse = i.id_interesse
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                LEFT JOIN confirmacao_pagamento cp ON i.id_interesse = cp.id_interesse
                WHERE d.id_disputa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca disputas por interesse
     */
    public function getByInteresse($interesseId)
    {
        $sql = "SELECT d.*, 
                       u.nome as aberto_por_nome,
                       s.situacao as situacao_nome
                FROM disputa d
                JOIN usuario u ON d.id_aberto_por = u.id_usuario
                JOIN situacao s ON d.id_situacao = s.id_situacao
                WHERE d.id_interesse = ?
                ORDER BY d.data_abertura DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca disputas pendentes (para moderador)
     */
    public function getPendentes()
    {
        $sql = "SELECT d.*, 
                       u.nome as aberto_por_nome,
                       s.situacao as situacao_nome,
                       a.titulo as anuncio_titulo,
                       c.nome as contratante_nome,
                       f.nome as freelancer_nome
                FROM disputa d
                JOIN usuario u ON d.id_aberto_por = u.id_usuario
                JOIN situacao s ON d.id_situacao = s.id_situacao
                JOIN interesse i ON d.id_interesse = i.id_interesse
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE d.id_situacao = 1
                ORDER BY d.data_abertura ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca disputas de um usuario
     */
    public function getByUsuario($usuarioId)
    {
        $sql = "SELECT d.*, 
                       u.nome as aberto_por_nome,
                       s.situacao as situacao_nome,
                       a.titulo as anuncio_titulo
                FROM disputa d
                JOIN usuario u ON d.id_aberto_por = u.id_usuario
                JOIN situacao s ON d.id_situacao = s.id_situacao
                JOIN interesse i ON d.id_interesse = i.id_interesse
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                WHERE d.id_aberto_por = ? OR i.id_contratante = ? OR i.id_freelancer = ?
                ORDER BY d.data_abertura DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId, $usuarioId, $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza situacao da disputa (moderador)
     */
    public function atualizarSituacao($id, $situacaoId, $responsavelId, $resposta = null)
    {
        $sql = "UPDATE disputa 
                SET id_situacao = ?, id_responsavel = ?, resposta = ?, data_resolucao = NOW() 
                WHERE id_disputa = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$situacaoId, $responsavelId, $resposta, $id]);
    }

    /**
     * Aprova disputa (situacao = 2 - Aprovado)
     */
    public function aprovar($id, $moderadorId, $resposta = null)
    {
        return $this->atualizarSituacao($id, 2, $moderadorId, $resposta);
    }

    /**
     * Rejeita disputa (situacao = 3 - Rejeitado)
     */
    public function rejeitar($id, $moderadorId, $resposta = null)
    {
        return $this->atualizarSituacao($id, 3, $moderadorId, $resposta);
    }

    /**
     * Verifica se ja existe disputa ativa para um interesse
     */
    public function existsAtiva($interesseId)
    {
        $sql = "SELECT id_disputa FROM disputa 
                WHERE id_interesse = ? AND id_situacao = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica se o usuario pode abrir disputa
     */
    public function podeAbrir($interesseId, $usuarioId)
    {
        $sql = "SELECT i.id_interesse, cp.situacao_final
                FROM interesse i
                LEFT JOIN confirmacao_pagamento cp ON i.id_interesse = cp.id_interesse
                WHERE i.id_interesse = ? 
                AND (i.id_contratante = ? OR i.id_freelancer = ?)
                AND i.situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId, $usuarioId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        // So pode abrir se pagamento divergente
        if (isset($result['situacao_final']) && $result['situacao_final'] != 'divergente') {
            return false;
        }
        
        return true;
    }

    /**
     * Conta disputas pendentes
     */
    public function countPendentes()
    {
        $sql = "SELECT COUNT(*) as total FROM disputa WHERE id_situacao = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Busca motivos para disputa
     */
    public function getMotivos()
    {
        return [
            'pagamento_divergente' => 'Valor do pagamento divergente',
            'servico_nao_realizado' => 'Servico nao realizado conforme combinado',
            'prazo_nao_cumprido' => 'Prazo nao cumprido',
            'qualidade_insatisfatoria' => 'Qualidade insatisfatoria',
            'comunicacao_insuficiente' => 'Comunicacao insuficiente',
            'outro' => 'Outro motivo'
        ];
    }
}