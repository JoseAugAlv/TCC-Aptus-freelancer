<?php
// app/Models/Anuncio.php

require_once __DIR__ . '/../Config/database.php';


class Anuncio
{
    private $conn;

    public function __construct()
    {
        $this->conn = AptusDatabase::getConnection();
    }

    /**
     * Busca todos os anúncios ativos e aprovados
     */
    public function getAll()
    {
        $sql = "SELECT a.*, u.nome as freelancer_nome, u.foto_perfil, u.nota_media, u.total_avaliacoes,
                c.nome as categoria_nome, c.icone as categoria_icone,
                (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') as total_interesses
                FROM anuncio_servico a 
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.situacao = 'ativo' AND a.id_situacao_moderacao = 2
                ORDER BY a.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca anúncios em destaque (mais visualizados)
     */
    public function getDestaques($limit = 6)
    {
        $sql = "SELECT a.*, u.nome as freelancer_nome, u.foto_perfil, u.nota_media, u.total_avaliacoes,
                c.nome as categoria_nome, c.icone as categoria_icone
                FROM anuncio_servico a 
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.situacao = 'ativo' AND a.id_situacao_moderacao = 2
                ORDER BY a.visualizacoes DESC, a.data_criacao DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca anúncio por slug (para página de detalhes)
     */
    public function findBySlug($slug)
    {
        $sql = "SELECT a.*, u.nome as freelancer_nome, u.foto_perfil, u.nota_media, u.total_avaliacoes,
                u.bio as freelancer_bio, u.cidade, u.estado, u.whatsapp, u.telefone,
                c.nome as categoria_nome, c.icone as categoria_icone,
                (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') as total_interesses,
                (SELECT COUNT(*) FROM favorito f WHERE f.id_anuncio = a.id_anuncio) as total_favoritos
                FROM anuncio_servico a 
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.slug = ? AND a.situacao = 'ativo' AND a.id_situacao_moderacao = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca anúncio por ID
     */
    public function findById($id)
    {
        $sql = "SELECT a.*, u.nome as freelancer_nome, u.foto_perfil, u.nota_media,
                c.nome as categoria_nome, c.icone as categoria_icone
                FROM anuncio_servico a 
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca anúncios de um usuário específico
     */
    public function getByUsuario($usuarioId)
    {
        $sql = "SELECT a.*, c.nome as categoria_nome, c.icone as categoria_icone,
                (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') as total_interesses
                FROM anuncio_servico a 
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_usuario = ? 
                ORDER BY a.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca anúncios pendentes de moderação (RF17)
     */
    public function getPendentesModeracao()
    {
        $sql = "SELECT a.*, u.nome as freelancer_nome, u.email as freelancer_email,
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
     * Busca avançada com filtros (RF09/RF10)
     */
    public function buscar($termo, $categoriaId = 0, $avaliacaoMin = 0, $ordenar = 'recentes')
    {
        $sql = "SELECT a.*, u.nome as freelancer_nome, u.foto_perfil, u.nota_media, u.total_avaliacoes,
                c.nome as categoria_nome, c.icone as categoria_icone
                FROM anuncio_servico a 
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.situacao = 'ativo' AND a.id_situacao_moderacao = 2";
        $params = [];

        if (!empty($termo)) {
            $sql .= " AND (a.titulo LIKE ? OR a.descricao LIKE ?)";
            $params[] = "%$termo%";
            $params[] = "%$termo%";
        }

        if ($categoriaId > 0) {
            $sql .= " AND a.id_categoria = ?";
            $params[] = $categoriaId;
        }

        if ($avaliacaoMin > 0) {
            $sql .= " AND u.nota_media >= ?";
            $params[] = $avaliacaoMin;
        }

        switch ($ordenar) {
            case 'avaliacao':
                $sql .= " ORDER BY u.nota_media DESC, a.data_criacao DESC";
                break;
            case 'preco':
                $sql .= " ORDER BY a.preco ASC";
                break;
            case 'visualizacoes':
                $sql .= " ORDER BY a.visualizacoes DESC";
                break;
            case 'recentes':
            default:
                $sql .= " ORDER BY a.data_criacao DESC";
                break;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um novo anúncio (RF05)
     */
    public function create($data)
    {
        $sql = "INSERT INTO anuncio_servico (id_usuario, id_categoria, titulo, descricao, slug, preco, foto_capa, situacao, id_situacao_moderacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'ativo', 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['id_usuario'],
            $data['id_categoria'],
            $data['titulo'],
            $data['descricao'],
            $data['slug'],
            $data['preco'],
            $data['foto_capa'] ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    /**
     * Atualiza um anúncio existente
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['id_categoria', 'titulo', 'descricao', 'slug', 'preco', 'foto_capa', 'situacao'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE anuncio_servico SET " . implode(", ", $fields) . " WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Aprova um anúncio na moderação (RF17)
     */
    public function aprovar($id)
    {
        $sql = "UPDATE anuncio_servico SET id_situacao_moderacao = 2 WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Rejeita um anúncio na moderação (RF17)
     */
    public function rejeitar($id, $motivo)
    {
        $sql = "UPDATE anuncio_servico SET id_situacao_moderacao = 3, motivo_remocao = ? WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$motivo, $id]);
    }

    /**
     * Pausa um anúncio (RF06 - controlado pelo freelancer)
     */
    public function pausar($id)
    {
        $sql = "UPDATE anuncio_servico SET situacao = 'pausado' WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Ativa um anúncio pausado (RF06 - controlado pelo freelancer)
     */
    public function ativar($id)
    {
        $sql = "UPDATE anuncio_servico SET situacao = 'ativo' WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Exclui (marca como excluído) um anúncio (RF06)
     */
    public function delete($id)
    {
        $sql = "UPDATE anuncio_servico SET situacao = 'excluido' WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Incrementa o contador de visualizações do anúncio
     */
    public function incrementarVisualizacao($id)
    {
        $sql = "UPDATE anuncio_servico SET visualizacoes = visualizacoes + 1 WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Verifica se o usuário é o dono do anúncio
     */
    public function isDono($anuncioId, $usuarioId)
    {
        $sql = "SELECT id_anuncio FROM anuncio_servico WHERE id_anuncio = ? AND id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId, $usuarioId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Total de anúncios ativos
     */
    public function getTotal()
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
    public function getPendentesCount()
    {
        $sql = "SELECT COUNT(*) as total FROM anuncio_servico WHERE id_situacao_moderacao = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Total de anúncios de um usuário
     */
    public function getTotalByUsuario($usuarioId)
    {
        $sql = "SELECT COUNT(*) as total FROM anuncio_servico WHERE id_usuario = ? AND situacao != 'excluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Busca anúncios por categoria
     */
    public function getByCategoria($categoriaId, $limit = null)
    {
        $sql = "SELECT a.*, u.nome as freelancer_nome, u.foto_perfil, u.nota_media,
                c.nome as categoria_nome
                FROM anuncio_servico a 
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_categoria = ? AND a.situacao = 'ativo' AND a.id_situacao_moderacao = 2
                ORDER BY a.data_criacao DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$categoriaId, $limit]);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$categoriaId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fotos adicionais do anúncio
     */
    public function getFotos($anuncioId)
    {
        $sql = "SELECT * FROM anuncio_foto WHERE id_anuncio = ? ORDER BY ordem ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona foto ao anúncio
     */
    public function addFoto($anuncioId, $arquivo, $ordem = 0)
    {
        $sql = "INSERT INTO anuncio_foto (id_anuncio, arquivo, ordem) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$anuncioId, $arquivo, $ordem]);
    }

    /**
     * Remove foto do anúncio
     */
    public function removeFoto($id)
    {
        $sql = "DELETE FROM anuncio_foto WHERE id_anuncio_foto = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}