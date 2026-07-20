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
     * Registra uma busca no log
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
     * Busca as categorias mais pesquisadas
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
     * Total de buscas no periodo
     */
    public function getTotalBuscas($dias = 30)
    {
        $sql = "SELECT COUNT(*) as total FROM busca_log WHERE data_busca >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$dias]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Total de buscas por dia (ultimos 7 dias)
     */
    public function getBuscasPorDia($dias = 7)
    {
        $sql = "SELECT DATE(data_busca) as data, COUNT(*) as total
                FROM busca_log
                WHERE data_busca >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(data_busca)
                ORDER BY data ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Usuarios que mais buscaram
     */
    public function getUsuariosMaisBuscam($limite = 5)
    {
        $sql = "SELECT u.id_usuario, u.nome, u.email, COUNT(b.id_busca) as total_buscas
                FROM busca_log b
                JOIN usuario u ON b.id_usuario = u.id_usuario
                WHERE b.id_usuario IS NOT NULL
                GROUP BY b.id_usuario
                ORDER BY total_buscas DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscas sem categoria (apenas termo)
     */
    public function getBuscasSemCategoria()
    {
        $sql = "SELECT COUNT(*) as total FROM busca_log WHERE id_categoria IS NULL AND termo_buscado IS NOT NULL AND termo_buscado != ''";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}