<?php
// app/Models/Denuncia.php

require_once __DIR__ . '/../Config/database.php';

class Denuncia
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Cria uma nova denuncia
     */
    public function create($data)
    {
        $sql = "INSERT INTO denuncia (id_denunciante, id_denunciado, id_anuncio, motivo, descricao) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['id_denunciante'],
            $data['id_denunciado'],
            $data['id_anuncio'] ?? null,
            $data['motivo'],
            $data['descricao'] ?? null
        ]);
    }

    /**
     * Busca denuncia por ID
     */
    public function findById($id)
    {
        $sql = "SELECT d.*, 
                       denunciante.nome as denunciante_nome,
                       denunciante.email as denunciante_email,
                       denunciado.nome as denunciado_nome,
                       denunciado.email as denunciado_email,
                       a.titulo as anuncio_titulo,
                       s.situacao as situacao_nome
                FROM denuncia d
                JOIN usuario denunciante ON d.id_denunciante = denunciante.id_usuario
                JOIN usuario denunciado ON d.id_denunciado = denunciado.id_usuario
                LEFT JOIN anuncio_servico a ON d.id_anuncio = a.id_anuncio
                JOIN situacao s ON d.id_situacao = s.id_situacao
                WHERE d.id_denuncia = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca denuncias por denunciante
     */
    public function getByDenunciante($usuarioId)
    {
        $sql = "SELECT d.*, 
                       denunciado.nome as denunciado_nome,
                       a.titulo as anuncio_titulo,
                       s.situacao as situacao_nome
                FROM denuncia d
                JOIN usuario denunciado ON d.id_denunciado = denunciado.id_usuario
                LEFT JOIN anuncio_servico a ON d.id_anuncio = a.id_anuncio
                JOIN situacao s ON d.id_situacao = s.id_situacao
                WHERE d.id_denunciante = ?
                ORDER BY d.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca denuncias por denunciado
     */
    public function getByDenunciado($usuarioId)
    {
        $sql = "SELECT d.*, 
                       denunciante.nome as denunciante_nome,
                       a.titulo as anuncio_titulo,
                       s.situacao as situacao_nome
                FROM denuncia d
                JOIN usuario denunciante ON d.id_denunciante = denunciante.id_usuario
                LEFT JOIN anuncio_servico a ON d.id_anuncio = a.id_anuncio
                JOIN situacao s ON d.id_situacao = s.id_situacao
                WHERE d.id_denunciado = ?
                ORDER BY d.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca denuncias pendentes
     */
    public function getPendentes()
    {
        $sql = "SELECT d.*, 
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

    /**
     * Busca todas as denuncias
     */
    public function getAll()
    {
        $sql = "SELECT d.*, 
                       denunciante.nome as denunciante_nome,
                       denunciado.nome as denunciado_nome,
                       a.titulo as anuncio_titulo,
                       s.situacao as situacao_nome
                FROM denuncia d
                JOIN usuario denunciante ON d.id_denunciante = denunciante.id_usuario
                JOIN usuario denunciado ON d.id_denunciado = denunciado.id_usuario
                LEFT JOIN anuncio_servico a ON d.id_anuncio = a.id_anuncio
                JOIN situacao s ON d.id_situacao = s.id_situacao
                ORDER BY d.data_criacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza situacao da denuncia
     */
    public function atualizarSituacao($id, $situacaoId, $moderadorId)
    {
        $sql = "UPDATE denuncia 
                SET id_situacao = ?, id_moderador_analise = ?, data_analise = NOW() 
                WHERE id_denuncia = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$situacaoId, $moderadorId, $id]);
    }

    /**
     * Aprova uma denuncia (situacao = 2 - Aprovado)
     */
    public function aprovar($id, $moderadorId)
    {
        return $this->atualizarSituacao($id, 2, $moderadorId);
    }

    /**
     * Rejeita uma denuncia (situacao = 3 - Rejeitado)
     */
    public function rejeitar($id, $moderadorId)
    {
        return $this->atualizarSituacao($id, 3, $moderadorId);
    }

    /**
     * Verifica se o usuario ja denunciou algo
     */
    public function jaDenunciou($denuncianteId, $denunciadoId, $anuncioId = null)
    {
        $sql = "SELECT id_denuncia FROM denuncia 
                WHERE id_denunciante = ? AND id_denunciado = ?";
        $params = [$denuncianteId, $denunciadoId];
        
        if ($anuncioId) {
            $sql .= " AND id_anuncio = ?";
            $params[] = $anuncioId;
        }
        
        $sql .= " AND id_situacao = 1 LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Conta denuncias pendentes
     */
    public function countPendentes()
    {
        $sql = "SELECT COUNT(*) as total FROM denuncia WHERE id_situacao = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Busca motivos para denuncia
     */
    public function getMotivos()
    {
        return [
            'conteudo_inadequado' => 'Conteúdo inadequado ou ofensivo',
            'spam' => 'Spam ou publicidade enganosa',
            'fraude' => 'Fraude ou golpe',
            'assédio' => 'Assédio ou discriminação',
            'informacao_falsa' => 'Informação falsa ou enganosa',
            'servico_nao_realizado' => 'Serviço não realizado conforme combinado',
            'outro' => 'Outro motivo'
        ];
    }
}