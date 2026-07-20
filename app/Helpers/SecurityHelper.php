<?php
// app/Helpers/SecurityHelper.php

class SecurityHelper
{
    /**
     * Valida força de senha no backend
     */
    public static function validarForcaSenha($senha)
    {
        $erros = [];

        if (strlen($senha) < 8) {
            $erros[] = 'Mínimo 8 caracteres';
        }
        if (!preg_match('/[A-Z]/', $senha)) {
            $erros[] = 'Pelo menos 1 letra maiúscula';
        }
        if (!preg_match('/[a-z]/', $senha)) {
            $erros[] = 'Pelo menos 1 letra minúscula';
        }
        if (!preg_match('/[0-9]/', $senha)) {
            $erros[] = 'Pelo menos 1 número';
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $senha)) {
            $erros[] = 'Pelo menos 1 caractere especial';
        }

        return [
            'valida' => count($erros) === 0,
            'erros' => $erros
        ];
    }

    /**
     * Gera token CSRF
     */
    public static function gerarCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica token CSRF
     */
    public static function verificarCsrfToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        
        return true;
    }

    /**
     * Sanitiza caminho de arquivo para evitar path traversal
     */
    public static function sanitizarCaminhoArquivo($arquivo, $diretorioBase, $extensoesPermitidas = [])
    {
        // 1. Remover tudo acima do nome do arquivo
        $nomeArquivo = basename($arquivo);

        // 2. Validar com regex
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $nomeArquivo)) {
            return null;
        }

        // 3. Montar caminho completo
        $caminhoCompleto = realpath($diretorioBase . DIRECTORY_SEPARATOR . $nomeArquivo);

        // 4. Verificar se o caminho resolvido está realmente dentro do diretório base
        if ($caminhoCompleto === false || strpos($caminhoCompleto, realpath($diretorioBase)) !== 0) {
            return null;
        }

        // 5. Validar extensões
        if (!empty($extensoesPermitidas)) {
            $extensao = strtolower(pathinfo($caminhoCompleto, PATHINFO_EXTENSION));
            if (!in_array($extensao, $extensoesPermitidas)) {
                return null;
            }
        }

        return $caminhoCompleto;
    }

    /**
     * Log de ações sensíveis
     */
    public static function logAuditoria($acao, $usuario_id, $detalhes = '', $tipo = 'info')
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0750, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $usuario = is_numeric($usuario_id) ? $usuario_id : $usuario_id;
        $mensagem = "[{$timestamp}] [{$tipo}] Usuario:{$usuario} | IP:{$ip} | Acao:{$acao} | Detalhes:{$detalhes}\n";

        file_put_contents($logDir . '/auditoria.log', $mensagem, FILE_APPEND);
    }

    /**
     * Escapa dados para saída HTML (Prevenção de XSS)
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Gera um hash seguro para token
     */
    public static function gerarToken($tamanho = 32)
    {
        return bin2hex(random_bytes($tamanho));
    }
    /**
     * Valida tipo real de arquivo com finfo
     */
    public static function validarMimeArquivo($caminhoTemp, $mimesPermitidos = ['image/jpeg', 'image/png', 'application/pdf'])
    {
        if (!function_exists('finfo_file')) {
            // Fallback: usar getimagesize para imagens
            if (in_array('image/jpeg', $mimesPermitidos) || in_array('image/png', $mimesPermitidos)) {
                $info = @getimagesize($caminhoTemp);
                return $info !== false;
            }
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $caminhoTemp);
        finfo_close($finfo);

        return in_array($mime, $mimesPermitidos);
    }

}