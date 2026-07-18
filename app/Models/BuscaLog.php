<?php
// app/Models/BuscaLog.php

require_once __DIR__ . '/../Config/database.php';

class BuscaLog
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Registra uma busca no log (RF19)
     */
    public function registrar($usuarioId, $termo, $categoriaId = null)
    {
        if (empty($termo) && empty($categoriaId)) {
            return false;
        }

        $sql = "INSERT INTO busca_log (id_usuario, termo_buscado, id_categoria) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuarioId, $termo, $categoriaId]);
    }

    /**
     * Busca as categorias mais pesquisadas (RF19)
     */
    public function getCategoriasMaisBuscadas($limite = 10)
    {
        $sql = "SELECT c.id_categoria, c.nome, c.icone, COUNT(b.id_busca) as total_buscas
                FROM busca_log b
                LEFT JOIN categoria c ON b.id_categoria = c.id_categoria
                WHERE b.id_categoria IS NOT NULL
                GROUP BY b.id_categoria
                ORDER BY total_buscas DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca os termos mais pesquisados
     */
    public function getTermosMaisBuscados($limite = 10)
    {
        $sql = "SELECT termo_buscado, COUNT(*) as total
                FROM busca_log
                WHERE termo_buscado IS NOT NULL AND termo_buscado != ''
                GROUP BY termo_buscado
                ORDER BY total DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Total de buscas no período
     */
    public function getTotalBuscas($dias = 30)
    {
        $sql = "SELECT COUNT(*) as total FROM busca_log WHERE data_busca >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$dias]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}