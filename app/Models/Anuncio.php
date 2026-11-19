<?php
// app/Models/Anuncio.php

require_once __DIR__ . '/../Config/database.php';

class Anuncio
{
    /**
     * Condição única de "anúncio visível ao público": ativo, aprovado e com o
     * dono fora de suspensão/banimento. Quando a suspensão termina, os anúncios
     * voltam a aparecer sozinhos (não há o que "desarquivar").
     * Exige os aliases a (anuncio_servico) e u (usuario).
     */
    public const VISIVEL = "a.situacao = 'ativo' AND a.id_situacao_moderacao = 2
                  AND u.banido = 0 AND (u.suspenso_ate IS NULL OR u.suspenso_ate <= NOW())";

    /** Média e total de avaliações do próprio anúncio (serviços concluídos, cliente -> prestador). */
    public const NOTA_EXPR = "(SELECT AVG(av.nota) FROM avaliacao av JOIN interesse ia ON ia.id_interesse = av.id_interesse
                      WHERE ia.id_anuncio = a.id_anuncio AND ia.situacao = 'concluido'
                        AND av.id_avaliado = ia.id_freelancer AND av.id_avaliador = ia.id_contratante)";
    public const TOTAL_EXPR = "(SELECT COUNT(*) FROM avaliacao av JOIN interesse ia ON ia.id_interesse = av.id_interesse
                      WHERE ia.id_anuncio = a.id_anuncio AND ia.situacao = 'concluido'
                        AND av.id_avaliado = ia.id_freelancer AND av.id_avaliador = ia.id_contratante)";
    public const COLS_AVALIACAO = self::NOTA_EXPR . " AS nota_anuncio, " . self::TOTAL_EXPR . " AS total_avaliacoes_anuncio";

    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ", u.nome AS freelancer_nome, u.foto_perfil, u.nota_media,
                       c.nome AS categoria_nome, c.icone AS categoria_icone
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE " . self::VISIVEL . "
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

        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ", u.nome AS freelancer_nome, u.foto_perfil, u.nota_media,
                       c.nome AS categoria_nome, c.icone AS categoria_icone
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE " . self::VISIVEL . "
                ORDER BY a.visualizacoes DESC, a.data_criacao DESC
                LIMIT {$limit}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findBySlug($slug)
    {
        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ", u.nome AS freelancer_nome, u.foto_perfil, u.nota_media, u.total_avaliacoes,
                       u.bio AS freelancer_bio, u.cidade, u.estado, u.whatsapp, u.telefone,
                       c.nome AS categoria_nome, c.icone AS categoria_icone,
                       (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') AS total_interesses,
                       (SELECT COUNT(*) FROM favorito f WHERE f.id_anuncio = a.id_anuncio) AS total_favoritos
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.slug = ? AND " . self::VISIVEL . "";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ", u.nome AS freelancer_nome, u.foto_perfil, u.nota_media,
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
        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ", c.nome AS categoria_nome, c.icone AS categoria_icone,
                       u.nome AS freelancer_nome,
                       (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') AS total_interesses
                FROM anuncio_servico a
                JOIN categoria c ON a.id_categoria = c.id_categoria
                JOIN usuario u ON a.id_usuario = u.id_usuario
                WHERE a.id_usuario = ?
                ORDER BY a.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendentesModeracao()
    {
        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ", u.nome AS freelancer_nome, u.email AS freelancer_email,
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

    /**
     * Monta o FROM/WHERE da busca avançada (compartilhado entre listagem e contagem).
     */
    private function montarFiltrosBusca(array $filtros, array &$params): string
    {
        $sql = " FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE " . self::VISIVEL . "";

        if (!empty($filtros['termo'])) {
            // Escapa coringas do LIKE para que "%" e "_" sejam buscados literalmente
            $termo    = '%' . addcslashes($filtros['termo'], '\%_') . '%';
            // "@joao_12" ou "joao_12": o nome de usuário exato traz todos os anúncios da pessoa
            $username = strtolower(ltrim(trim($filtros['termo']), '@'));
            $sql     .= " AND (a.titulo LIKE ? OR a.descricao LIKE ? OR u.nome LIKE ? OR u.username = ?)";
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $username;
        }

        if (!empty($filtros['categoria']) && $filtros['categoria'] > 0) {
            $sql .= " AND a.id_categoria = ?";
            $params[] = $filtros['categoria'];
        }

        if (!empty($filtros['avaliacao']) && $filtros['avaliacao'] > 0) {
            $sql .= " AND " . self::NOTA_EXPR . " >= ?";
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

        return $sql;
    }

    /**
     * Total de resultados da busca avançada (para paginação).
     */
    public function contarAvancado(array $filtros): int
    {
        $params = [];
        $sql    = "SELECT COUNT(*)" . $this->montarFiltrosBusca($filtros, $params);
        $stmt   = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca avançada. Aceita 'limite' e 'offset' opcionais para paginação.
     */
    public function buscarAvancado($filtros)
    {
        $params = [];
        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ",
                       u.nome AS freelancer_nome, u.foto_perfil, u.nota_media, u.total_avaliacoes,
                       c.nome AS categoria_nome, c.icone AS categoria_icone,
                       (SELECT COUNT(*) FROM interesse i WHERE i.id_anuncio = a.id_anuncio AND i.situacao = 'ativo') AS total_interesses"
             . $this->montarFiltrosBusca($filtros, $params);

        $ordenacao = $filtros['ordenar'] ?? 'recentes';
        switch ($ordenacao) {
            case 'avaliacao':
                $sql .= " ORDER BY nota_anuncio IS NULL, nota_anuncio DESC, total_avaliacoes_anuncio DESC, a.data_criacao DESC";
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

        if (!empty($filtros['limite'])) {
            $sql .= sprintf(" LIMIT %d OFFSET %d", max(1, (int) $filtros['limite']), max(0, (int) ($filtros['offset'] ?? 0)));
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

    /**
     * Listagem pública paginada ("Ver mais"). Filtros: categoria, freelancer.
     */
    public function listarPublicos(array $filtros, int $limite, int $offset = 0): array
    {
        [$where, $params] = $this->filtrosPublicos($filtros);
        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ", u.nome AS freelancer_nome, u.username AS freelancer_username,
                       u.foto_perfil, u.nota_media,
                       c.nome AS categoria_nome, c.icone AS categoria_icone
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE {$where}
                ORDER BY a.data_criacao DESC, a.id_anuncio DESC
                LIMIT " . max(1, $limite) . " OFFSET " . max(0, $offset);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarPublicos(array $filtros): int
    {
        [$where, $params] = $this->filtrosPublicos($filtros);
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM anuncio_servico a JOIN usuario u ON a.id_usuario = u.id_usuario WHERE {$where}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function filtrosPublicos(array $filtros): array
    {
        $where  = self::VISIVEL;
        $params = [];
        if (!empty($filtros['categoria'])) {
            $where   .= " AND a.id_categoria = ?";
            $params[] = (int) $filtros['categoria'];
        }
        if (!empty($filtros['freelancer'])) {
            $where   .= " AND a.id_usuario = ?";
            $params[] = (int) $filtros['freelancer'];
        }
        return [$where, $params];
    }

    public function create($data)
    {
        $sql = "INSERT INTO anuncio_servico
                (id_usuario, id_categoria, titulo, descricao, slug, preco, max_simultaneos, foto_capa, situacao, id_situacao_moderacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ativo', 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['id_usuario'],
            $data['id_categoria'],
            $data['titulo'],
            $data['descricao'],
            $data['slug'],
            $data['preco'],
            (int) ($data['max_simultaneos'] ?? 1),
            $data['foto_capa'] ?? null,
        ]);
        return $this->conn->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields       = [];
        $params       = [];
        $allowed      = ['id_categoria', 'titulo', 'descricao', 'slug', 'preco', 'max_simultaneos', 'foto_capa', 'situacao'];

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

    /**
     * Revoga um anúncio já aprovado: sai do ar e só volta após nova aprovação
     * (o dono edita e o anúncio retorna para a fila como pendente).
     */
    public function revogar($id, $motivo)
    {
        $sql  = "UPDATE anuncio_servico SET id_situacao_moderacao = 3, motivo_remocao = ?
                 WHERE id_anuncio = ? AND id_situacao_moderacao = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['Revogado: ' . $motivo, $id]);
        return $stmt->rowCount() > 0;
    }

    /** Rejeitado/revogado e editado pelo dono: volta para a fila de moderação. */
    public function reenviarParaAnalise($id)
    {
        $sql  = "UPDATE anuncio_servico SET id_situacao_moderacao = 1
                 WHERE id_anuncio = ? AND id_situacao_moderacao = 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
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
        $sql  = "SELECT COUNT(*) AS total FROM anuncio_servico a JOIN usuario u ON a.id_usuario = u.id_usuario
                 WHERE " . self::VISIVEL;
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
        $sql = "SELECT a.*, " . self::COLS_AVALIACAO . ", u.nome AS freelancer_nome, u.foto_perfil, u.nota_media,
                       c.nome AS categoria_nome
                FROM anuncio_servico a
                JOIN usuario u ON a.id_usuario = u.id_usuario
                JOIN categoria c ON a.id_categoria = c.id_categoria
                WHERE a.id_categoria = ? AND " . self::VISIVEL . "
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

    public function findFoto(int $fotoId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM anuncio_foto WHERE id_anuncio_foto = ?");
        $stmt->execute([$fotoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function contarFotos(int $anuncioId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM anuncio_foto WHERE id_anuncio = ?");
        $stmt->execute([$anuncioId]);
        return (int) $stmt->fetchColumn();
    }

    /** A capa é sempre uma das fotos do anúncio (anuncio_servico.foto_capa). */
    public function definirCapa(int $anuncioId, string $arquivo): bool
    {
        $stmt = $this->conn->prepare("UPDATE anuncio_servico SET foto_capa = ? WHERE id_anuncio = ?");
        return $stmt->execute([$arquivo, $anuncioId]);
    }
}