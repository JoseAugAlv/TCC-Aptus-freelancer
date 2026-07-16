<?php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Notificacao.php';

    class NavHelper
    {
        public static function getPerfil($idUsuario)
        {
            try {
                $pdo = Database::getConnection();
                
                $sql = "SELECT id_perfil 
                        FROM usuario_projeto 
                        WHERE id_usuario = ? 
                        ORDER BY id_perfil ASC 
                        LIMIT 1";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$idUsuario]);
                
                $idPerfil = $stmt->fetchColumn();
                
                // Se não encontrar, retorna 3 (Participante)
                return $idPerfil ? (int) $idPerfil : 3;
                
            } catch (Exception $e) {
                return 3;
            }
        }

    public static function getContadorNotificacoes($idUsuario)
    {
        if (!$idUsuario) {
            return 0;
        }
        
        try {
            $notificacao = new Notificacao();
            return $notificacao->contarNaoLidas($idUsuario);
        } catch (Exception $e) {
            return 0;
        }
    }
}