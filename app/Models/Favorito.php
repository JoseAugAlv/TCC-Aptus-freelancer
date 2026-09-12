<?php
// app/Models/Favorito.php

require_once __DIR__ . '/../Config/database.php';

class Favorito
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function adicionar($usuarioId, $anuncioId)
    {
        if ($this->existe($usuarioId, $anuncioId)) {
            return false;
        }

        $sql  = "INSERT INTO favorito (id_usuario, id_anuncio) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuarioId, $anuncioId]);
    }

    public function remover($usuarioId, $anuncioId)
    {
        $sql  = "DELETE FROM favorito WHERE id_usuario = ? AND id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuarioId, $anuncioId]);
    }

    public function existe($usuarioId, $anuncioId)
    {
        $sql  = "SELECT id_favorito FROM favorito WHERE id_usuario = ? AND id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId, $anuncioId]);
        return $stmt->fetch() !== false;
    }

    public function getByUsuario($usuarioId)
    {
        $sql = "SELECT f.*,
                       a.titulo, a.preco, a.slug, a.foto_capa, a.descricao, a.visualizacoes,
                       u.nome AS freelancer_nome, u.foto_perfil AS freelancer_foto, u.nota_media,
                       c.nome AS categoria_nome, c.icone AS categoria_icone,
                       (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') AS total_interesses
                FROM favorito f
                JOIN anuncio_servico a ON f.id_anuncio = a.id_anuncio
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE f.id_usuario = ?
                ORDER BY f.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarPorAnuncio($anuncioId)
    {
        $sql  = "SELECT COUNT(*) AS total FROM favorito WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function removerPorAnuncio($anuncioId)
    {
        $sql  = "DELETE FROM favorito WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$anuncioId]);
    }
}