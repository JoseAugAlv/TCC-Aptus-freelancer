<?php
// app/Models/Dashboard.php

require_once __DIR__ . '/../Config/database.php';

class Dashboard
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Total de usuários por tipo
     */
    public function getTotalUsuariosPorTipo()
    {
        $sql = "SELECT 
                    p.perfil AS tipo_usuario,
                    COUNT(u.id_usuario) AS total
                FROM usuario u
                RIGHT JOIN perfil p ON u.id_perfil = p.id_perfil
                GROUP BY p.id_perfil, p.perfil";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totais = [];
        foreach ($result as $row) {
            $totais[$row['tipo_usuario']] = $row['total'];
        }
        
        $defaults = ['Admin' => 0, 'Moderador' => 0, 'Usuario' => 0, 'Master' => 0];
        return array_merge($defaults, $totais);
    }

    /**
     * Total de anúncios ativos
     */
    public function getTotalAnuncios()
    {
        $sql = "SELECT COUNT(*) as total FROM anuncio_servico WHERE situacao = 'ativo' AND id_situacao_moderacao = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Total de anúncios pendentes de moderação
     */
    public function getAnunciosPendentes()
    {
        $sql = "SELECT COUNT(*) as total FROM anuncio_servico WHERE id_situacao_moderacao = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Total de interesses ativos
     */
    public function getTotalInteresses()
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Total de usuários ativos
     */
    public function getTotalUsuarios()
    {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE ativo = TRUE AND banido = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Total de categorias
     */
    public function getTotalCategorias()
    {
        $sql = "SELECT COUNT(*) as total FROM categoria WHERE ativo = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Denúncias pendentes
     */
    public function getDenunciasPendentes()
    {
        $sql = "SELECT COUNT(*) as total FROM denuncia WHERE id_situacao = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Total de freelancers (usuários que têm anúncios)
     */
    public function getTotalFreelancers()
    {
        $sql = "SELECT COUNT(DISTINCT id_usuario) as total 
                FROM anuncio_servico 
                WHERE situacao = 'ativo' AND id_situacao_moderacao = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Últimos usuários cadastrados
     */
    public function getUltimosUsuarios($limite = 5)
    {
        $sql = "SELECT u.id_usuario, u.nome, u.email, u.foto_perfil, u.data_criacao,
                       p.perfil AS nome_perfil
                FROM usuario u
                LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
                WHERE u.ativo = TRUE
                ORDER BY u.data_criacao DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Últimos anúncios cadastrados
     */
    public function getUltimosAnuncios($limite = 5)
    {
        $sql = "SELECT a.id_anuncio, a.titulo, a.preco, a.situacao, a.data_criacao,
                       c.nome AS categoria_nome,
                       u.nome AS freelancer_nome
                FROM anuncio_servico a
                JOIN categoria c ON a.id_categoria = c.id_categoria
                JOIN usuario u ON a.id_usuario = u.id_usuario
                WHERE a.situacao != 'excluido'
                ORDER BY a.data_criacao DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Últimos interesses (contratações)
     */
    public function getUltimosInteresses($limite = 5)
    {
        $sql = "SELECT i.id_interesse, i.situacao, i.data_interesse,
                       a.titulo AS anuncio_titulo,
                       c.nome AS contratante_nome,
                       f.nome AS freelancer_nome
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                ORDER BY i.data_interesse DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    // MÉTODOS PARA FREELANCER
    // ============================================================

    /**
     * Total de serviços do freelancer
     */
    public function getTotalServicosFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM anuncio_servico WHERE id_usuario = ? AND situacao != 'excluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Serviços ativos do freelancer
     */
    public function getServicosAtivosFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM anuncio_servico WHERE id_usuario = ? AND situacao = 'ativo' AND id_situacao_moderacao = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Serviços pausados do freelancer
     */
    public function getServicosPausadosFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM anuncio_servico WHERE id_usuario = ? AND situacao = 'pausado'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Serviços pendentes de moderação do freelancer
     */
    public function getServicosPendentesFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM anuncio_servico WHERE id_usuario = ? AND id_situacao_moderacao = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Interesses recebidos (propostas) do freelancer
     */
    public function getInteressesRecebidosFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_freelancer = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Interesses concluídos do freelancer
     */
    public function getInteressesConcluidosFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_freelancer = ? AND situacao = 'concluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Últimos serviços criados pelo freelancer
     */
    public function getUltimosServicosFreelancer($freelancerId, $limit = 5)
    {
        $sql = "SELECT a.*, c.nome as categoria_nome,
                       (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') as total_interesses
                FROM anuncio_servico a
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_usuario = ? AND a.situacao != 'excluido'
                ORDER BY a.data_criacao DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Últimos interesses recebidos pelo freelancer
     */
    public function getUltimosInteressesFreelancer($freelancerId, $limit = 5)
    {
        $sql = "SELECT i.*, a.titulo as anuncio_titulo,
                       u.nome as contratante_nome, u.foto_perfil as contratante_foto
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario u ON i.id_contratante = u.id_usuario
                WHERE i.id_freelancer = ?
                ORDER BY i.data_interesse DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Avaliação média do freelancer
     */
    public function getNotaMediaFreelancer($freelancerId)
    {
        $sql = "SELECT nota_media, total_avaliacoes FROM usuario WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'nota' => $result['nota_media'] ?? 0,
            'total' => $result['total_avaliacoes'] ?? 0
        ];
    }

    /**
     * Total de interesses enviados pelo cliente
     */
    public function getTotalInteressesCliente($clienteId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_contratante = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clienteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Interesses ativos do cliente
     */
    public function getInteressesAtivosCliente($clienteId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_contratante = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clienteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Interesses concluídos do cliente
     */
    public function getInteressesConcluidosCliente($clienteId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_contratante = ? AND situacao = 'concluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clienteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Interesses cancelados do cliente
     */
    public function getInteressesCanceladosCliente($clienteId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_contratante = ? AND situacao = 'cancelado'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clienteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Últimos interesses enviados pelo cliente
     */
    public function getUltimosInteressesCliente($clienteId, $limit = 5)
    {
        $sql = "SELECT i.*, a.titulo as anuncio_titulo, a.preco as anuncio_preco,
                       f.nome as freelancer_nome, f.foto_perfil as freelancer_foto
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE i.id_contratante = ?
                ORDER BY i.data_interesse DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clienteId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Favoritos do cliente
     */
    public function getFavoritosCliente($clienteId, $limit = 5)
    {
        $sql = "SELECT f.*, a.titulo, a.preco, a.slug, a.foto_capa,
                       u.nome as freelancer_nome, c.nome as categoria_nome
                FROM favorito f
                JOIN anuncio_servico a ON f.id_anuncio = a.id_anuncio
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE f.id_usuario = ?
                ORDER BY f.data_criacao DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clienteId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Total de favoritos do cliente
     */
    public function getTotalFavoritosCliente($clienteId)
    {
        $sql = "SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clienteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    
    

}