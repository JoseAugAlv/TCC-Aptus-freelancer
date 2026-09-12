<?php
// app/Models/Mensagem.php

require_once __DIR__ . '/../Config/database.php';

class Mensagem
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function enviar($interesseId, $remetenteId, $destinatarioId, $mensagem, $arquivo = null)
    {
        $sql = "INSERT INTO mensagem (id_interesse, id_remetente, id_destinatario, mensagem, arquivo_anexo)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$interesseId, $remetenteId, $destinatarioId, $mensagem, $arquivo]);
    }

    public function getByInteresse($interesseId, $limit = 50, $offset = 0)
    {
        $sql = "SELECT m.*,
                       u.nome AS remetente_nome, u.foto_perfil AS remetente_foto
                FROM mensagem m
                JOIN usuario u ON m.id_remetente = u.id_usuario
                WHERE m.id_interesse = ?
                ORDER BY m.data_envio DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, (int) $limit, (int) $offset]);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getConversasRecentes($usuarioId)
    {
        // FIX: parênteses em (contratante OR freelancer) para o AND do cancelado não vazar
        $sql = "SELECT
                    i.id_interesse,
                    a.titulo AS anuncio_titulo,
                    a.slug   AS anuncio_slug,
                    u.id_usuario AS outro_usuario_id,
                    u.nome AS outro_usuario_nome,
                    u.foto_perfil AS outro_usuario_foto,
                    (SELECT COUNT(*) FROM mensagem m2
                     WHERE m2.id_interesse = i.id_interesse
                       AND m2.id_destinatario = ?
                       AND m2.lida = FALSE) AS nao_lidas,
                    (SELECT m3.mensagem FROM mensagem m3
                     WHERE m3.id_interesse = i.id_interesse
                     ORDER BY m3.data_envio DESC LIMIT 1) AS ultima_mensagem,
                    (SELECT m3.data_envio FROM mensagem m3
                     WHERE m3.id_interesse = i.id_interesse
                     ORDER BY m3.data_envio DESC LIMIT 1) AS ultima_data
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario u ON (
                    CASE
                        WHEN i.id_contratante = ? THEN i.id_freelancer
                        ELSE i.id_contratante
                    END = u.id_usuario
                )
                WHERE (i.id_contratante = ? OR i.id_freelancer = ?)
                  AND i.situacao != 'cancelado'
                  AND EXISTS (
                      SELECT 1 FROM mensagem m WHERE m.id_interesse = i.id_interesse
                  )
                ORDER BY ultima_data DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId, $usuarioId, $usuarioId, $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarLidas($interesseId, $usuarioId)
    {
        $sql = "UPDATE mensagem
                SET lida = TRUE
                WHERE id_interesse = ? AND id_destinatario = ? AND lida = FALSE";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$interesseId, $usuarioId]);
    }

    public function contarNaoLidas($usuarioId)
    {
        $sql = "SELECT COUNT(*) AS total FROM mensagem
                WHERE id_destinatario = ? AND lida = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function usuarioPodeVer($interesseId, $usuarioId)
    {
        $sql = "SELECT id_interesse FROM interesse
                WHERE id_interesse = ? AND (id_contratante = ? OR id_freelancer = ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId, $usuarioId]);
        return $stmt->fetch() !== false;
    }

    public function getDadosInteresse($interesseId, $usuarioId)
    {
        $sql = "SELECT i.*,
                       a.titulo AS anuncio_titulo, a.slug AS anuncio_slug,
                       c.nome AS contratante_nome, c.foto_perfil AS contratante_foto,
                       f.nome AS freelancer_nome, f.foto_perfil AS freelancer_foto
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE i.id_interesse = ?
                  AND (i.id_contratante = ? OR i.id_freelancer = ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId, $usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOutroUsuario($interesseId, $usuarioId)
    {
        $sql = "SELECT u.id_usuario, u.nome, u.foto_perfil
                FROM interesse i
                JOIN usuario u ON (
                    CASE
                        WHEN i.id_contratante = ? THEN i.id_freelancer
                        ELSE i.id_contratante
                    END = u.id_usuario
                )
                WHERE i.id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId, $interesseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}