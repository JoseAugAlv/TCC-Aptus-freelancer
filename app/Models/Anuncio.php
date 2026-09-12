<?php
// app/Models/Anuncio.php

require_once __DIR__ . '/../Config/database.php';

class Anuncio
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT a.*, u.nome AS freelancer_nome, u.foto_perfil, u.nota_media,
                       c.nome AS categoria_nome, c.icone AS categoria_icone
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
     * LIMIT como int interpolado (compatível com todo MySQL/MariaDB).
     */
    public function getDestaques($limit = 6)
    {
        $limit = max(1, (int) $limit);

        $sql = "SELECT a.*, u.nome AS freelancer_nome, u.foto_perfil, u.nota_media,
                       c.nome AS categoria_nome, c.icone AS categoria_icone
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.situacao = 'ativo' AND a.id_situacao_moderacao = 2
                ORDER BY a.visualizacoes DESC, a.data_criacao DESC
                LIMIT {$limit}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findBySlug($slug)
    {
        $sql = "SELECT a.*, u.nome AS freelancer_nome, u.foto_perfil, u.nota_media, u.total_avaliacoes,
                       u.bio AS freelancer_bio, u.cidade, u.estado, u.whatsapp, u.telefone,
                       c.nome AS categoria_nome, c.icone AS categoria_icone,
                       (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') AS total_interesses,
                       (SELECT COUNT(*) FROM favorito f WHERE f.id_anuncio = a.id_anuncio) AS total_favoritos
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.slug = ? AND a.situacao = 'ativo' AND a.id_situacao_moderacao = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT a.*, u.nome AS freelancer_nome, u.foto_perfil, u.nota_media,
                       c.nome AS categoria_nome, c.icone AS categoria_icone
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUsuario($usuarioId)
    {
        $sql = "SELECT a.*, c.nome AS categoria_nome, c.icone AS categoria_icone,
                       (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') AS total_interesses
                FROM anuncio_servico a
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_usuario = ?
                ORDER BY a.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendentesModeracao()
    {
        $sql = "SELECT a.*, u.nome AS freelancer_nome, u.email AS freelancer_email,
                       c.nome AS categoria_nome
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_situacao_moderacao = 1
                ORDER BY a.data_criacao ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarAvancado($filtros)
    {
        $sql = "SELECT a.*,
                       u.nome AS freelancer_nome, u.foto_perfil, u.nota_media, u.total_avaliacoes,
                       c.nome AS categoria_nome, c.icone AS categoria_icone,
                       (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') AS total_interesses
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.situacao = 'ativo' AND a.id_situacao_moderacao = 2";

        $params = [];

        if (!empty($filtros['termo'])) {
            $sql .= " AND (a.titulo LIKE ? OR a.descricao LIKE ?)";
            $termo    = '%' . $filtros['termo'] . '%';
            $params[] = $termo;
            $params[] = $termo;
        }

        if (!empty($filtros['categoria']) && $filtros['categoria'] > 0) {
            $sql .= " AND a.id_categoria = ?";
            $params[] = $filtros['categoria'];
        }

        if (!empty($filtros['avaliacao']) && $filtros['avaliacao'] > 0) {
            $sql .= " AND u.nota_media >= ?";
            $params[] = $filtros['avaliacao'];
        }

        if (!empty($filtros['preco_min']) && $filtros['preco_min'] > 0) {
            $sql .= " AND a.preco >= ?";
            $params[] = $filtros['preco_min'];
        }

        if (!empty($filtros['preco_max']) && $filtros['preco_max'] > 0) {
            $sql .= " AND a.preco <= ?";
            $params[] = $filtros['preco_max'];
        }

        $ordenacao = $filtros['ordenar'] ?? 'recentes';
        switch ($ordenacao) {
            case 'avaliacao':
                $sql .= " ORDER BY u.nota_media DESC, a.data_criacao DESC";
                break;
            case 'preco_asc':
                $sql .= " ORDER BY a.preco ASC";
                break;
            case 'preco_desc':
                $sql .= " ORDER BY a.preco DESC";
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

    public function buscar($termo, $categoriaId = 0, $avaliacaoMin = 0, $ordenar = 'recentes')
    {
        return $this->buscarAvancado([
            'termo'     => $termo,
            'categoria' => $categoriaId,
            'avaliacao' => $avaliacaoMin,
            'ordenar'   => $ordenar,
        ]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO anuncio_servico
                (id_usuario, id_categoria, titulo, descricao, slug, preco, foto_capa, situacao, id_situacao_moderacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'ativo', 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['id_usuario'],
            $data['id_categoria'],
            $data['titulo'],
            $data['descricao'],
            $data['slug'],
            $data['preco'],
            $data['foto_capa'] ?? null,
        ]);
        return $this->conn->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields       = [];
        $params       = [];
        $allowed      = ['id_categoria', 'titulo', 'descricao', 'slug', 'preco', 'foto_capa', 'situacao'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql  = "UPDATE anuncio_servico SET " . implode(', ', $fields) . " WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function aprovar($id)
    {
        $sql  = "UPDATE anuncio_servico SET id_situacao_moderacao = 2 WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function rejeitar($id, $motivo)
    {
        $sql  = "UPDATE anuncio_servico SET id_situacao_moderacao = 3, motivo_remocao = ? WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$motivo, $id]);
    }

    public function pausar($id)
    {
        $sql  = "UPDATE anuncio_servico SET situacao = 'pausado' WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function ativar($id)
    {
        $sql  = "UPDATE anuncio_servico SET situacao = 'ativo' WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function delete($id)
    {
        $sql  = "UPDATE anuncio_servico SET situacao = 'excluido' WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function incrementarVisualizacao($id)
    {
        $sql  = "UPDATE anuncio_servico SET visualizacoes = visualizacoes + 1 WHERE id_anuncio = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function isDono($anuncioId, $usuarioId)
    {
        $sql  = "SELECT id_anuncio FROM anuncio_servico WHERE id_anuncio = ? AND id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId, $usuarioId]);
        return $stmt->fetch() !== false;
    }

    public function hasInteresse($anuncioId, $usuarioId)
    {
        $sql  = "SELECT id_interesse FROM interesse
                 WHERE id_anuncio = ? AND id_contratante = ? AND situacao IN ('ativo', 'concluido')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId, $usuarioId]);
        return $stmt->fetch() !== false;
    }

    public function getTotal()
    {
        $sql  = "SELECT COUNT(*) AS total FROM anuncio_servico WHERE situacao = 'ativo' AND id_situacao_moderacao = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function getPendentesCount()
    {
        $sql  = "SELECT COUNT(*) AS total FROM anuncio_servico WHERE id_situacao_moderacao = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function getTotalByUsuario($usuarioId)
    {
        $sql  = "SELECT COUNT(*) AS total FROM anuncio_servico WHERE id_usuario = ? AND situacao != 'excluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($r['total'] ?? 0);
    }

    public function getByCategoria($categoriaId, $limit = null)
    {
        $sql = "SELECT a.*, u.nome AS freelancer_nome, u.foto_perfil, u.nota_media,
                       c.nome AS categoria_nome
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_categoria = ? AND a.situacao = 'ativo' AND a.id_situacao_moderacao = 2
                ORDER BY a.data_criacao DESC";

        if ($limit) {
            $limit = (int) $limit;
            $sql  .= " LIMIT {$limit}";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$categoriaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFotos($anuncioId)
    {
        $sql  = "SELECT * FROM anuncio_foto WHERE id_anuncio = ? ORDER BY ordem ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addFoto($anuncioId, $arquivo, $ordem = 0)
    {
        $sql  = "INSERT INTO anuncio_foto (id_anuncio, arquivo, ordem) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$anuncioId, $arquivo, $ordem]);
    }

    public function removeFoto($id)
    {
        $sql  = "DELETE FROM anuncio_foto WHERE id_anuncio_foto = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}