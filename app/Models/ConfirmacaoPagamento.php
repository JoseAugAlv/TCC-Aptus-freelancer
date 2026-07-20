<?php
// app/Models/ConfirmacaoPagamento.php

require_once __DIR__ . '/../Config/database.php';

class ConfirmacaoPagamento
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Busca confirmacao por interesse
     */
    public function getByInteresse($interesseId)
    {
        $sql = "SELECT * FROM confirmacao_pagamento WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$interesseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria confirmacao de pagamento
     */
    public function create($data)
    {
        $sql = "INSERT INTO confirmacao_pagamento (id_interesse) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$data['id_interesse']]);
    }

    /**
     * Atualiza confirmacao do contratante
     */
    public function confirmarContratante($interesseId, $valor, $formaPagamento, $dataPagamento, $observacao = null)
    {
        $sql = "UPDATE confirmacao_pagamento 
                SET confirmado_contratante = TRUE, 
                    valor_informado_contratante = ?, 
                    forma_pagamento_contratante = ?, 
                    data_pagamento_contratante = ?, 
                    data_confirmacao_contratante = NOW(),
                    observacao_contratante = ?
                WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$valor, $formaPagamento, $dataPagamento, $observacao, $interesseId]);
    }

    /**
     * Atualiza confirmacao do freelancer
     */
    public function confirmarFreelancer($interesseId, $valor, $dataRecebimento, $observacao = null)
    {
        $sql = "UPDATE confirmacao_pagamento 
                SET confirmado_freelancer = TRUE, 
                    valor_informado_freelancer = ?, 
                    data_recebimento_freelancer = ?, 
                    data_confirmacao_freelancer = NOW(),
                    observacao_freelancer = ?
                WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$valor, $dataRecebimento, $observacao, $interesseId]);
    }

    /**
     * Verifica e atualiza situacao final da confirmacao
     */
    public function verificarEAtualizarSituacao($interesseId)
    {
        $confirmacao = $this->getByInteresse($interesseId);
        
        if (!$confirmacao) {
            return false;
        }

        $situacao = 'pendente';
        
        if ($confirmacao['confirmado_contratante'] && $confirmacao['confirmado_freelancer']) {
            $valorContratante = $confirmacao['valor_informado_contratante'];
            $valorFreelancer = $confirmacao['valor_informado_freelancer'];
            
            if ($valorContratante == $valorFreelancer) {
                $situacao = 'confirmado';
            } else {
                $situacao = 'divergente';
            }
        }

        $sql = "UPDATE confirmacao_pagamento SET situacao_final = ? WHERE id_interesse = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$situacao, $interesseId]);
    }
}