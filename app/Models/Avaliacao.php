<?php
// app/Models/Avaliacao.php

require_once __DIR__ . '/../Config/database.php';

class Avaliacao
{
    /**
     * Avaliação que conta para a reputação de PRESTADOR (e para a nota do anúncio):
     * feita pelo cliente sobre o freelancer, num serviço concluído.
     * Exige os aliases a (avaliacao) e i (interesse).
     */
    public const COND_PRESTADOR = "i.situacao = 'concluido' AND a.id_avaliado = i.id_freelancer AND a.id_avaliador = i.id_contratante";

    /** Avaliação recebida como CLIENTE (feita pelo freelancer), em serviço concluído. */
    public const COND_CLIENTE = "i.situacao = 'concluido' AND a.id_avaliado = i.id_contratante AND a.id_avaliador = i.id_freelancer";

    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Cria uma nova avaliação
     */
    public function create($data)
    {
        $sql = "INSERT INTO avaliacao (id_interesse, id_avaliador, id_avaliado, nota, comentario) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['id_interesse'],
            $data['id_avaliador'],
            $data['id_avaliado'],
            $data['nota'],
            $data['comentario']
        ]);
    }

    /**
     * Busca avaliação por interesse
     */
    public function findById($id) {
        $sql = "SELECT a.*, av.nome AS avaliador_nome, avd.nome AS avaliado_nome
                FROM avaliacao a
                JOIN usuario av ON a.id_avaliador = av.id_usuario
                JOIN usuario avd ON a.id_avaliado = avd.id_usuario
                WHERE a.id_avaliacao = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByInteresse($interesseId)
    {
        $sql = "SELECT a.*, 
                       av.nome as avaliador_nome, av.foto_perfil as avaliador_foto,
                       avd.nome as avaliado_nome, avd.foto_perfil as avaliado_foto,
                       i.situacao as interesse_situacao
                FROM avaliacao a
                JOIN usuario av ON a.id_avaliador = av.id_usuario
                JOIN usuario avd ON a.id_avaliado = avd.id_usuario
                JOIN interesse i ON a.id_interesse = i.id_interesse
                WHERE a.id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca avaliações de um usuário (como avaliado)
     */
    public function getByAvaliado($usuarioId, $limit = null)
    {
        $sql = "SELECT a.*, 
                       av.nome as avaliador_nome, av.foto_perfil as avaliador_foto,
                       i.situacao as interesse_situacao,
                       an.titulo as anuncio_titulo
                FROM avaliacao a
                JOIN usuario av ON a.id_avaliador = av.id_usuario
                JOIN interesse i ON a.id_interesse = i.id_interesse
                JOIN anuncio_servico an ON i.id_anuncio = an.id_anuncio
                WHERE a.id_avaliado = ?
                ORDER BY a.data_avaliacao DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$usuarioId, $limit]);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$usuarioId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca avaliações feitas por um usuário (como avaliador)
     */
    public function getByAvaliador($usuarioId)
    {
        $sql = "SELECT a.*, 
                       avd.nome as avaliado_nome, avd.foto_perfil as avaliado_foto,
                       i.situacao as interesse_situacao,
                       an.titulo as anuncio_titulo
                FROM avaliacao a
                JOIN usuario avd ON a.id_avaliado = avd.id_usuario
                JOIN interesse i ON a.id_interesse = i.id_interesse
                JOIN anuncio_servico an ON i.id_anuncio = an.id_anuncio
                WHERE a.id_avaliador = ?
                ORDER BY a.data_avaliacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se já existe avaliação para um interesse
     */
    public function exists($interesseId)
    {
        $sql = "SELECT id_avaliacao FROM avaliacao WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Responde uma avaliação (freelancer)
     */
    public function responder($id, $resposta)
    {
        $sql = "UPDATE avaliacao SET resposta_avaliado = ?, data_resposta = NOW() WHERE id_avaliacao = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$resposta, $id]);
    }

    /**
     * Recalcula a reputação de prestador (usuario.nota_media / total_avaliacoes)
     * a partir das avaliações reais. É chamada sempre que uma avaliação é salva.
     */
    public function recalcularNotaMedia($usuarioId)
    {
        $r = $this->resumoPrestador((int) $usuarioId);
        $stmt = $this->conn->prepare("UPDATE usuario SET nota_media = ?, total_avaliacoes = ? WHERE id_usuario = ?");
        return $stmt->execute([$r['media'], $r['total'], $usuarioId]);
    }

    /** Recalcula todos os usuários (corrige médias que não batem com as avaliações). */
    public function recalcularTodos(): int
    {
        $ids = $this->conn->query("SELECT id_usuario FROM usuario")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($ids as $id) {
            $this->recalcularNotaMedia((int) $id);
        }
        return count($ids);
    }

    /** ['media' => float, 'total' => int] das avaliações recebidas como prestador. */
    public function resumoPrestador(int $usuarioId): array
    {
        return $this->resumo("a.id_avaliado = ? AND " . self::COND_PRESTADOR, [$usuarioId]);
    }

    /** ['media' => float, 'total' => int] das avaliações recebidas como cliente. */
    public function resumoCliente(int $usuarioId): array
    {
        return $this->resumo("a.id_avaliado = ? AND " . self::COND_CLIENTE, [$usuarioId]);
    }

    /** ['media' => float, 'total' => int] das avaliações de um anúncio. */
    public function resumoAnuncio(int $anuncioId): array
    {
        return $this->resumo("i.id_anuncio = ? AND " . self::COND_PRESTADOR, [$anuncioId]);
    }

    /** Distribuição de notas (5 a 1) das avaliações de um anúncio. */
    public function distribuicaoAnuncio(int $anuncioId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.nota, COUNT(*) AS qtd FROM avaliacao a JOIN interesse i ON i.id_interesse = a.id_interesse
             WHERE i.id_anuncio = ? AND " . self::COND_PRESTADOR . " GROUP BY a.nota"
        );
        $stmt->execute([$anuncioId]);
        $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $dist[(int) $r['nota']] = (int) $r['qtd'];
        }
        return $dist;
    }

    /** Avaliações (com comentário e resposta) de um anúncio, mais recentes primeiro. */
    public function getDoAnuncio(int $anuncioId, int $limite = 20): array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.id_avaliacao, a.nota, a.comentario, a.resposta_avaliado, a.data_resposta, a.data_avaliacao,
                    av.nome AS avaliador_nome, av.username AS avaliador_username, av.foto_perfil AS avaliador_foto
             FROM avaliacao a
             JOIN interesse i ON i.id_interesse = a.id_interesse
             JOIN usuario av ON av.id_usuario = a.id_avaliador
             WHERE i.id_anuncio = ? AND " . self::COND_PRESTADOR . "
             ORDER BY a.data_avaliacao DESC, a.id_avaliacao DESC
             LIMIT " . max(1, $limite)
        );
        $stmt->execute([$anuncioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function resumo(string $where, array $params): array
    {
        $stmt = $this->conn->prepare(
            "SELECT AVG(a.nota) AS media, COUNT(*) AS total
             FROM avaliacao a JOIN interesse i ON i.id_interesse = a.id_interesse
             WHERE {$where}"
        );
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $total = (int) ($r['total'] ?? 0);
        return ['media' => $total ? round((float) $r['media'], 2) : 0.0, 'total' => $total];
    }
}
