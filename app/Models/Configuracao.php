<?php
// app/Models/Configuracao.php

require_once __DIR__ . '/../Config/database.php';

class Configuracao
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Retorna todas as configuracoes
     */
    public function getAll()
    {
        $sql = "SELECT * FROM configuracao";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $configs = [];
        foreach ($result as $row) {
            $configs[$row['chave']] = $row['valor'];
        }
        
        return $configs;
    }

    /**
     * Busca uma configuracao pela chave
     */
    public function get($chave)
    {
        $sql = "SELECT valor FROM configuracao WHERE chave = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$chave]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['valor'] ?? null;
    }

    /**
     * Atualiza uma configuracao
     */
    public function set($chave, $valor)
    {
        $sql = "INSERT INTO configuracao (chave, valor) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE valor = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$chave, $valor, $valor]);
    }

    /**
     * Atualiza multiplas configuracoes
     */
    public function setMultiples($configs)
    {
        $this->conn->beginTransaction();
        
        try {
            $sql = "INSERT INTO configuracao (chave, valor) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE valor = ?";
            $stmt = $this->conn->prepare($sql);
            
            foreach ($configs as $chave => $valor) {
                $stmt->execute([$chave, $valor, $valor]);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Retorna configuracoes padrao
     */
    public function getDefaults()
    {
        return [
            'site_nome' => 'Aptus',
            'site_descricao' => 'Conectando Talentos',
            'site_email' => 'contato@aptus.com',
            'site_telefone' => '(11) 99999-9999',
            'site_endereco' => 'Sao Paulo, SP',
            
            'upload_max_size' => '5',
            'upload_allow_types' => 'jpg,jpeg,png,webp,gif',
            
            'moderacao_automatica' => '0',
            
            'sessao_tempo' => '3600',
            'tentativas_login' => '5',
            
            'email_host' => 'smtp.gmail.com',
            'email_port' => '587',
            'email_user' => '',
            'email_pass' => '',
            'email_from_name' => 'Aptus',
            
            'manutencao' => '0',
            'manutencao_mensagem' => 'Sistema em manutencao. Volte em breve.'
        ];
    }

    /**
     * Inicializa configuracoes padrao (se nao existirem)
     */
    public function initDefaults()
    {
        $defaults = $this->getDefaults();
        $existentes = $this->getAll();
        
        $toInsert = [];
        foreach ($defaults as $chave => $valor) {
            if (!isset($existentes[$chave])) {
                $toInsert[$chave] = $valor;
            }
        }
        
        if (!empty($toInsert)) {
            return $this->setMultiples($toInsert);
        }
        
        return true;
    }
}