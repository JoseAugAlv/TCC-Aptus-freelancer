<?php
// app/Helpers/UploadHelper.php

require_once __DIR__ . '/SecurityHelper.php';

class UploadHelper
{
    public static function upload($arquivo, $pasta, $tiposPermitidos = ['pdf', 'jpg', 'jpeg', 'png', 'webp'], $tamanhoMaximo = 2097152)
    {
        // 1. Verificar erro
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erro ao fazer upload do arquivo.'];
        }

        // 2. Validar extensão
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $tiposPermitidos)) {
            return ['success' => false, 'message' => 'Formato de arquivo não permitido.'];
        }

        // 3. Validar tamanho
        if ($arquivo['size'] > $tamanhoMaximo) {
            return ['success' => false, 'message' => 'Arquivo muito grande.'];
        }

        // 4. 🔒 Validar MIME real
        $mimesPermitidos = self::getPermittedMimes($tiposPermitidos);
        if (!SecurityHelper::validarMimeArquivo($arquivo['tmp_name'], $mimesPermitidos)) {
            return ['success' => false, 'message' => 'Tipo de arquivo inválido.'];
        }

        // 5. Criar pasta
        $caminhoCompleto = $_SERVER['DOCUMENT_ROOT'] . '/RecycleWays/public/' . $pasta;
        if (!file_exists($caminhoCompleto)) {
            mkdir($caminhoCompleto, 0755, true);
        }

        // 6. Gerar nome único
        $nomeArquivo = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
        $caminhoArquivo = $caminhoCompleto . '/' . $nomeArquivo;

        // 7. Mover arquivo
        if (move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
            SecurityHelper::logAuditoria(
                'upload_arquivo',
                $_SESSION['usuario']['id'] ?? 'anonymous',
                'Arquivo: ' . $nomeArquivo,
                'info'
            );

            return ['success' => true, 'arquivo' => $pasta . '/' . $nomeArquivo];
        }

        return ['success' => false, 'message' => 'Erro ao salvar o arquivo.'];
    }

    private static function getPermittedMimes($extensoes)
    {
        $mimeMap = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'zip' => 'application/zip'
        ];

        $mimes = [];
        foreach ($extensoes as $ext) {
            if (isset($mimeMap[$ext])) {
                $mimes[] = $mimeMap[$ext];
            }
        }

        return array_unique($mimes);
    }
}