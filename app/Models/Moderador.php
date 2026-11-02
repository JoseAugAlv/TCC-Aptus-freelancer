<?php
// app/Models/Moderador.php

require_once __DIR__ . '/../Config/database.php';

class Moderador
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getAnunciosPendentes()
    {
        $sql  = "SELECT a.*, u.nome as freelancer_nome, u.email as freelancer_email,
                        c.nome as categoria_nome
                 FROM anuncio_servico a
                 JOIN usuario u ON a.id_usuario = u.id_usuario
                 JOIN categoria c ON a.id_categoria = c.id_categoria
                 WHERE a.id_situacao_moderacao = 1
                 ORDER BY a.data_criacao ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * NOVO: listar todos os anúncios (qualquer status) para gestão
     */
    public function getTodosAnuncios($status = null)
    {
        $sql = "SELECT a.*, u.nome AS freelancer_nome, u.email AS freelancer_email,
                       c.nome AS categoria_nome,
                       s.situacao AS situacao_moderacao
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                JOIN situacao s ON a.id_situacao_moderacao = s.id_situacao
                WHERE a.situacao != 'excluido'";
        $params = [];

        if ($status !== null && $status !== '') {
            $sql .= " AND a.id_situacao_moderacao = ?";
            $params[] = (int) $status;
        }

        $sql .= " ORDER BY a.data_criacao DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * NOVO: suspender (pausar) um anúncio
     */
    public function suspenderAnuncio($id)
    {
        $sql  = "UPDATE anuncio_servico SET situacao = 'pausado' WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        $ok   = $stmt->execute([$id]);
        Database::clearCache();
        return $ok;
    }

    public function getDenunciasPendentes()
    {
        $sql  = "SELECT d.*,
                        denunciante.nome as denunciante_nome,
                        denunciado.nome as denunciado_nome,
                        a.titulo as anuncio_titulo,
                        s.situacao as situacao_nome
                 FROM denuncia d
                 JOIN usuario denunciante ON d.id_denunciante = denunciante.id_usuario
                 JOIN usuario denunciado ON d.id_denunciado = denunciado.id_usuario
                 LEFT JOIN anuncio_servico a ON d.id_anuncio = a.id_anuncio
                 JOIN situacao s ON d.id_situacao = s.id_situacao
                 WHERE d.id_situacao = 1
                 ORDER BY d.data_criacao ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDisputasPendentes()
    {
        $sql  = "SELECT dp.*, u.nome as aberto_por_nome, s.situacao as situacao_nome,
                        a.titulo as anuncio_titulo
                 FROM disputa dp
                 JOIN usuario u ON dp.id_aberto_por = u.id_usuario
                 JOIN situacao s ON dp.id_situacao = s.id_situacao
                 JOIN interesse i ON dp.id_interesse = i.id_interesse
                 JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                 WHERE dp.id_situacao = 1
                 ORDER BY dp.data_abertura ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsuarios()
    {
        $sql  = "SELECT u.*, p.perfil as nome_perfil
                 FROM usuario u
                 LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
                 WHERE u.ativo = TRUE
                 ORDER BY u.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorias()
    {
        $sql  = "SELECT c.*,
                        (SELECT COUNT(*) FROM anuncio_servico a
                         WHERE a.id_categoria = c.id_categoria AND a.situacao = 'ativo') as total_anuncios
                 FROM categoria c
                 WHERE c.ativo = TRUE
                 ORDER BY c.nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}