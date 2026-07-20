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

    /**
     * Busca um interesse por ID
     */
    public function findById($id)
    {
        $sql = "SELECT i.*, 
                       a.titulo as anuncio_titulo, a.preco as anuncio_preco, a.slug as anuncio_slug,
                       c.nome as contratante_nome, c.email as contratante_email, c.telefone as contratante_telefone,
                       f.nome as freelancer_nome, f.email as freelancer_email, f.telefone as freelancer_telefone
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE i.id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um novo interesse (pendente)
     */
    public function create($data)
    {
        $sql = "INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial, situacao) 
                VALUES (?, ?, ?, ?, 'pendente')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['id_anuncio'],
            $data['id_contratante'],
            $data['id_freelancer'],
            $data['mensagem_inicial'] ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    /**
     * Aceita um interesse (freelancer)
     */
    public function aceitar($id)
    {
        $sql = "UPDATE interesse SET situacao = 'ativo', data_aceite = NOW() WHERE id_interesse = ? AND situacao = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Recusa um interesse (freelancer)
     */
    public function recusar($id)
    {
        $sql = "UPDATE interesse SET situacao = 'recusado', data_recusa = NOW() WHERE id_interesse = ? AND situacao = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Confirma que o serviço foi realizado (contratante e freelancer)
     */
    public function confirmarExecucao($id, $usuarioId)
    {
        $sql = "UPDATE interesse SET 
                    confirmado_contratante = CASE WHEN id_contratante = ? THEN TRUE ELSE confirmado_contratante END,
                    confirmado_freelancer = CASE WHEN id_freelancer = ? THEN TRUE ELSE confirmado_freelancer END
                WHERE id_interesse = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuarioId, $usuarioId, $id]);
    }

    /**
     * Verifica se ambos confirmaram a execução
     */
    public function verificasConfirmacaoExecucao($id)
    {
        $sql = "SELECT confirmado_contratante, confirmado_freelancer, situacao 
                FROM interesse WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['confirmado_contratante'] && $result['confirmado_freelancer']) {
            $this->concluir($id);
            return true;
        }
        return false;
    }

    /**
     * Conclui um interesse
     */
    public function concluir($id)
    {
        $sql = "UPDATE interesse SET situacao = 'concluido', data_conclusao = NOW() WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Busca interesses pendentes (para freelancer)
     */
    public function getPendentesByFreelancer($freelancerId)
    {
        $sql = "SELECT i.*, 
                       a.titulo as anuncio_titulo, a.slug as anuncio_slug, a.preco as anuncio_preco,
                       c.nome as contratante_nome, c.foto_perfil as contratante_foto,
                       c.email as contratante_email
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                WHERE i.id_freelancer = ? AND i.situacao = 'pendente'
                ORDER BY i.data_interesse ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca interesses ativos (contratante)
     */
    public function getAtivosByContratante($contratanteId)
    {
        $sql = "SELECT i.*, 
                       a.titulo as anuncio_titulo, a.slug as anuncio_slug, a.preco as anuncio_preco,
                       f.nome as freelancer_nome, f.foto_perfil as freelancer_foto
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario f ON i.id_freelancer = f.id_usuario
                WHERE i.id_contratante = ? AND i.situacao = 'ativo'
                ORDER BY i.data_interesse DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$contratanteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca interesses ativos (freelancer)
     */
    public function getAtivosByFreelancer($freelancerId)
    {
        $sql = "SELECT i.*, 
                       a.titulo as anuncio_titulo, a.slug as anuncio_slug, a.preco as anuncio_preco,
                       c.nome as contratante_nome, c.foto_perfil as contratante_foto
                FROM interesse i
                JOIN anuncio_servico a ON i.id_anuncio = a.id_anuncio
                JOIN usuario c ON i.id_contratante = c.id_usuario
                WHERE i.id_freelancer = ? AND i.situacao = 'ativo'
                ORDER BY i.data_interesse DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se o usuario ja tem interesse pendente ou ativo no anuncio
     */
    public function existsAtivo($anuncioId, $contratanteId)
    {
        $sql = "SELECT id_interesse FROM interesse 
                WHERE id_anuncio = ? AND id_contratante = ? AND situacao IN ('pendente', 'ativo', 'concluido')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anuncioId, $contratanteId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica se o interesse pertence ao usuario
     */
    public function pertence($interesseId, $usuarioId)
    {
        $sql = "SELECT id_interesse FROM interesse 
                WHERE id_interesse = ? AND (id_contratante = ? OR id_freelancer = ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId, $usuarioId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Cancela um interesse
     */
    public function cancelar($id)
    {
        $sql = "UPDATE interesse SET situacao = 'cancelado' WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ============================================================
    // METODOS PARA DASHBOARD
    // ============================================================

    public function countPendentesByFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_freelancer = ? AND situacao = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countAtivosByFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_freelancer = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countConcluidosByFreelancer($freelancerId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_freelancer = ? AND situacao = 'concluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$freelancerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countAtivosByContratante($contratanteId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_contratante = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$contratanteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countConcluidosByContratante($contratanteId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_contratante = ? AND situacao = 'concluido'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$contratanteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countPendentesByContratante($contratanteId)
    {
        $sql = "SELECT COUNT(*) as total FROM interesse WHERE id_contratante = ? AND situacao = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$contratanteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Verifica se o cliente ja avaliou o interesse
     */
    public function clienteJaAvaliou($interesseId)
    {
        $sql = "SELECT id_avaliacao FROM avaliacao WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica se o usuario ja avaliou o interesse
     * (usuario pode ser cliente ou freelancer)
     */
    public function usuarioJaAvaliou($interesseId, $usuarioId)
    {
        $sql = "SELECT id_avaliacao FROM avaliacao 
                WHERE id_interesse = ? AND id_avaliador = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId, $usuarioId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica se ambos ja avaliaram
     */
    public function ambosJaAvaliaram($interesseId)
    {
        $sql = "SELECT COUNT(DISTINCT id_avaliador) as total 
                FROM avaliacao WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Precisa ter 2 avaliadores diferentes (cliente e freelancer)
        return ($result['total'] ?? 0) >= 2;
    }

    /**
     * Confirma execucao do servico (cliente)
     * So permite se ja tiver avaliado
     */
    public function confirmarExecucaoCliente($interesseId, $usuarioId)
    {
        // Verificar se cliente ja avaliou
        if (!$this->usuarioJaAvaliou($interesseId, $usuarioId)) {
            return false;
        }

        $sql = "UPDATE interesse SET 
                    confirmado_contratante = TRUE
                WHERE id_interesse = ? AND id_contratante = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$interesseId, $usuarioId]);
    }

    /**
     * Confirma execucao do servico (freelancer)
     * So permite se ja tiver avaliado
     */
    public function confirmarExecucaoFreelancer($interesseId, $usuarioId)
    {
        // Verificar se freelancer ja avaliou
        if (!$this->usuarioJaAvaliou($interesseId, $usuarioId)) {
            return false;
        }

        $sql = "UPDATE interesse SET 
                    confirmado_freelancer = TRUE
                WHERE id_interesse = ? AND id_freelancer = ? AND situacao = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$interesseId, $usuarioId]);
    }

    /**
     * Verifica se ambos confirmaram e conclui
     */
    public function verificarEConcluir($interesseId)
    {
        $sql = "SELECT confirmado_contratante, confirmado_freelancer, situacao 
                FROM interesse WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['confirmado_contratante'] && $result['confirmado_freelancer']) {
            $sql = "UPDATE interesse SET situacao = 'concluido', data_conclusao = NOW() WHERE id_interesse = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$interesseId]);
            return true;
        }
        return false;
    }
}
