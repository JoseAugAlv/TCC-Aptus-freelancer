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

    public function findById($id)
    {
        $sql = "SELECT i.*,
                       a.titulo AS anuncio_titulo, a.preco AS anuncio_preco, a.slug AS anuncio_slug,
                       c.nome AS contratante_nome, c.email AS contratante_email, c.telefone AS contratante_telefone,
                       f.nome AS freelancer_nome, f.email AS freelancer_email, f.telefone AS freelancer_telefone
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE i.id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial, situacao)
                VALUES (?, ?, ?, ?, 'pendente')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['id_anuncio'],
            $data['id_contratante'],
            $data['id_freelancer'],
            $data['mensagem_inicial'] ?? null,
        ]);
        return $this->conn->lastInsertId();
    }

    public function aceitar($id)
    {
        $sql  = "UPDATE interesse SET situacao = 'ativo', data_aceite = NOW() WHERE id_interesse = ? AND situacao = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function recusar($id)
    {
        $sql  = "UPDATE interesse SET situacao = 'recusado', data_recusa = NOW() WHERE id_interesse = ? AND situacao = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function cancelar($id)
    {
        $sql  = "UPDATE interesse SET situacao = 'cancelado' WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function concluir($id)
    {
        $sql  = "UPDATE interesse SET situacao = 'concluido', data_conclusao = NOW() WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getPendentesByFreelancer($freelancerId)
    {
        $sql = "SELECT i.*,
                       a.titulo AS anuncio_titulo, a.slug AS anuncio_slug, a.preco AS anuncio_preco,
                       c.nome AS contratante_nome, c.foto_perfil AS contratante_foto,
                       c.email AS contratante_email
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                WHERE i.id_freelancer = ? AND i.situacao = 'pendente'
                ORDER BY i.data_interesse ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAtivosByContratante($contratanteId)
    {
        $sql = "SELECT i.*,
                       a.titulo AS anuncio_titulo, a.slug AS anuncio_slug, a.preco AS anuncio_preco,
                       f.nome AS freelancer_nome, f.foto_perfil AS freelancer_foto,
                       cp.situacao_final, cp.confirmado_contratante, cp.confirmado_freelancer
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                LEFT JOIN confirmacao_pagamento cp ON i.id_interesse = cp.id_interesse
                WHERE i.id_contratante = ? AND i.situacao = 'ativo'
                ORDER BY i.data_interesse DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$contratanteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAtivosByFreelancer($freelancerId)
    {
        $sql = "SELECT i.*,
                       a.titulo AS anuncio_titulo, a.slug AS anuncio_slug, a.preco AS anuncio_preco,
                       c.nome AS contratante_nome, c.foto_perfil AS contratante_foto,
                       cp.situacao_final, cp.confirmado_contratante, cp.confirmado_freelancer
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                LEFT JOIN confirmacao_pagamento cp ON i.id_interesse = cp.id_interesse
                WHERE i.id_freelancer = ? AND i.situacao = 'ativo'
                ORDER BY i.data_interesse DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsAtivo($anuncioId, $contratanteId)
    {
        $sql  = "SELECT id_interesse FROM interesse
                 WHERE id_anuncio = ? AND id_contratante = ? AND situacao IN ('pendente', 'ativo', 'concluido')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId, $contratanteId]);
        return $stmt->fetch() !== false;
    }

    public function pertence($interesseId, $usuarioId)
    {
        $sql  = "SELECT id_interesse FROM interesse
                 WHERE id_interesse = ? AND (id_contratante = ? OR id_freelancer = ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId, $usuarioId]);
        return $stmt->fetch() !== false;
    }

    public function usuarioJaAvaliou($interesseId, $usuarioId)
    {
        $sql  = "SELECT id_avaliacao FROM avaliacao WHERE id_interesse = ? AND id_avaliador = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId]);
        return $stmt->fetch() !== false;
    }

    public function clienteJaAvaliou($interesseId)
    {
        $sql  = "SELECT id_avaliacao FROM avaliacao WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetch() !== false;
    }

    public function ambosJaAvaliaram($interesseId)
    {
        $sql  = "SELECT COUNT(DISTINCT id_avaliador) AS total FROM avaliacao WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int) ($r['total'] ?? 0)) >= 2;
    }

    public function confirmarExecucaoCliente($interesseId, $usuarioId)
    {
        if (!$this->usuarioJaAvaliou($interesseId, $usuarioId)) {
            return false;
        }

        $sql  = "UPDATE interesse SET confirmado_contratante = TRUE
                 WHERE id_interesse = ? AND id_contratante = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$interesseId, $usuarioId]);
    }

    public function confirmarExecucaoFreelancer($interesseId, $usuarioId)
    {
        if (!$this->usuarioJaAvaliou($interesseId, $usuarioId)) {
            return false;
        }

        $sql  = "UPDATE interesse SET confirmado_freelancer = TRUE
                 WHERE id_interesse = ? AND id_freelancer = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$interesseId, $usuarioId]);
    }

    public function verificarEConcluir($interesseId)
    {
        $sql  = "SELECT confirmado_contratante, confirmado_freelancer, situacao FROM interesse WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r && $r['confirmado_contratante'] && $r['confirmado_freelancer']) {
            $sql  = "UPDATE interesse SET situacao = 'concluido', data_conclusao = NOW() WHERE id_interesse = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$interesseId]);
            return true;
        }
        return false;
    }

    // ==================== Contadores ====================

    public function countPendentesByFreelancer($freelancerId)
    {
        $sql  = "SELECT COUNT(*) AS total FROM interesse WHERE id_freelancer = ? AND situacao = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function countAtivosByFreelancer($freelancerId)
    {
        $sql  = "SELECT COUNT(*) AS total FROM interesse WHERE id_freelancer = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function countConcluidosByFreelancer($freelancerId)
    {
        $sql  = "SELECT COUNT(*) AS total FROM interesse WHERE id_freelancer = ? AND situacao = 'concluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function countAtivosByContratante($contratanteId)
    {
        $sql  = "SELECT COUNT(*) AS total FROM interesse WHERE id_contratante = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$contratanteId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function countConcluidosByContratante($contratanteId)
    {
        $sql  = "SELECT COUNT(*) AS total FROM interesse WHERE id_contratante = ? AND situacao = 'concluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$contratanteId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function countPendentesByContratante($contratanteId)
    {
        $sql  = "SELECT COUNT(*) AS total FROM interesse WHERE id_contratante = ? AND situacao = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$contratanteId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }
}