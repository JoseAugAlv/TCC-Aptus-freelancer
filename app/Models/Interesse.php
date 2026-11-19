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
                       c.email AS contratante_email,
                       (SELECT COUNT(*) FROM interesse o
                         WHERE o.id_anuncio = i.id_anuncio AND o.situacao = 'pendente'
                           AND o.id_interesse <> i.id_interesse) AS total_concorrentes
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
                WHERE i.id_contratante = ? AND (i.situacao = 'ativo' OR (i.situacao = 'concluido' AND (
                    i.data_conclusao >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    OR NOT EXISTS (SELECT 1 FROM avaliacao av WHERE av.id_interesse = i.id_interesse AND av.id_avaliador = i.id_contratante))))
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
                WHERE i.id_freelancer = ? AND (i.situacao = 'ativo' OR (i.situacao = 'concluido' AND (
                    i.data_conclusao >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    OR NOT EXISTS (SELECT 1 FROM avaliacao av WHERE av.id_interesse = i.id_interesse AND av.id_avaliador = i.id_freelancer))))
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

    /** Serviços realmente concluídos pelo usuário como prestador. */
    public function contarConcluidosFreelancer(int $usuarioId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM interesse WHERE id_freelancer = ? AND situacao = 'concluido'");
        $stmt->execute([$usuarioId]);
        return (int) $stmt->fetchColumn();
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

    // app/Models/Interesse.php

    /**
     * "Concluir serviço": cada parte confirma que o serviço foi feito.
     * Quando as duas confirmam, verificarEConcluir() muda para 'concluido'
     * e a avaliação fica liberada para as duas partes.
     */
    public function confirmarExecucaoCliente($interesseId, $usuarioId)
    {
        $sql  = "UPDATE interesse SET concluido_contratante = 1
                WHERE id_interesse = ? AND id_contratante = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId]);
        return $stmt->rowCount() > 0;
    }

    public function confirmarExecucaoFreelancer($interesseId, $usuarioId)
    {
        $sql  = "UPDATE interesse SET concluido_freelancer = 1
                WHERE id_interesse = ? AND id_freelancer = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId]);
        return $stmt->rowCount() > 0;
    }

    public function verificarEConcluir($interesseId)
    {
        $sql  = "SELECT concluido_contratante, concluido_freelancer, situacao FROM interesse WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r && $r['situacao'] === 'ativo' && $r['concluido_contratante'] && $r['concluido_freelancer']) {
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

    /**
     * Quantos clientes estão sendo atendidos agora (propostas aceitas e ainda
     * não concluídas) neste anúncio.
     */
    public function contarAtivosNoAnuncio(int $anuncioId, int $excetoInteresseId = 0): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM interesse
             WHERE id_anuncio = ? AND situacao = 'ativo' AND id_interesse <> ?"
        );
        $stmt->execute([$anuncioId, $excetoInteresseId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * O anúncio já atende o máximo de clientes simultâneos definido pelo prestador
     * (anuncio_servico.max_simultaneos)? Enquanto estiver cheio, novas propostas
     * ficam bloqueadas até algum atendimento ser concluído ou cancelado.
     */
    public function limiteAtingido(int $anuncioId, int $limite, int $excetoInteresseId = 0): bool
    {
        return $this->contarAtivosNoAnuncio($anuncioId, $excetoInteresseId) >= max(1, $limite);
    }

    /** Compatibilidade: existe ao menos um atendimento em andamento? */
    public function temAtivoNoAnuncio(int $anuncioId, int $excetoInteresseId = 0): bool
    {
        return $this->contarAtivosNoAnuncio($anuncioId, $excetoInteresseId) > 0;
    }

    /**
     * Quando o prestador preenche a última vaga do anúncio, as demais propostas
     * pendentes são recusadas. Retorna as propostas recusadas (para notificar).
     */
    public function recusarConcorrentes(int $anuncioId, int $interesseEscolhidoId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id_interesse, id_contratante FROM interesse
             WHERE id_anuncio = ? AND situacao = 'pendente' AND id_interesse <> ?"
        );
        $stmt->execute([$anuncioId, $interesseEscolhidoId]);
        $concorrentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($concorrentes) {
            $upd = $this->conn->prepare(
                "UPDATE interesse SET situacao = 'recusado', data_recusa = NOW()
                 WHERE id_anuncio = ? AND situacao = 'pendente' AND id_interesse <> ?"
            );
            $upd->execute([$anuncioId, $interesseEscolhidoId]);
        }
        return $concorrentes;
    }
}
