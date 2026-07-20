<?php
// app/Helpers/UploadHelper.php

require_once __DIR__ . '/SecurityHelper.php';

class UploadHelper
{
    public static $tiposPermitidos = [
        'perfil' => ['jpg', 'jpeg', 'png', 'webp'],
        'anuncio' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'portfolio' => ['jpg', 'jpeg', 'png', 'webp']
    ];

    public static $tamanhosMaximos = [
        'perfil' => 2097152,    // 2MB
        'anuncio' => 5242880,   // 5MB
        'portfolio' => 5242880  // 5MB
    ];

    /**
     * Faz upload de uma imagem
     */
    public static function upload($arquivo, $categoria, $subpasta = '')
    {
        if (!isset(self::$tiposPermitidos[$categoria])) {
            return ['success' => false, 'message' => 'Categoria de upload invalida.'];
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $mensagens = [
                UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho maximo permitido pelo servidor.',
                UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho maximo permitido.',
                UPLOAD_ERR_PARTIAL => 'Upload incompleto.',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporaria nao encontrada.',
                UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever o arquivo no disco.',
                UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensao PHP.'
            ];
            return ['success' => false, 'message' => $mensagens[$arquivo['error']] ?? 'Erro no upload.'];
        }

        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, self::$tiposPermitidos[$categoria])) {
            return ['success' => false, 'message' => 'Formato de arquivo nao permitido. Use: ' . implode(', ', self::$tiposPermitidos[$categoria])];
        }

        if ($arquivo['size'] > self::$tamanhosMaximos[$categoria]) {
            $maxMB = self::$tamanhosMaximos[$categoria] / 1048576;
            return ['success' => false, 'message' => "Arquivo muito grande. Maximo: {$maxMB}MB"];
        }

        // Validar MIME real
        $mimesPermitidos = self::getPermittedMimes($categoria);
        if (!SecurityHelper::validarMimeArquivo($arquivo['tmp_name'], $mimesPermitidos)) {
            return ['success' => false, 'message' => 'Tipo de arquivo invalido.'];
        }

        $pastaBase = $_SERVER['DOCUMENT_ROOT'] . '/Aptus/public/uploads/' . $categoria;
        if (!empty($subpasta)) {
            $pastaBase .= '/' . $subpasta;
        }
        
        if (!file_exists($pastaBase)) {
            mkdir($pastaBase, 0755, true);
        }

        $nomeArquivo = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
        $caminhoCompleto = $pastaBase . '/' . $nomeArquivo;

        if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            // Redimensionar se GD estiver disponivel
            if (function_exists('imagecreatefrompng')) {
                self::redimensionar($caminhoCompleto, $categoria);
            } else {
                // Log de aviso se GD nao estiver disponivel
                error_log('Aviso: Extensao GD nao disponivel. A imagem nao foi redimensionada.');
            }
            
            SecurityHelper::logAuditoria(
                'upload_arquivo',
                $_SESSION['usuario']['id'] ?? 'anonymous',
                'Arquivo: ' . $nomeArquivo . ' | Categoria: ' . $categoria,
                'info'
            );

            $caminhoRelativo = 'uploads/' . $categoria;
            if (!empty($subpasta)) {
                $caminhoRelativo .= '/' . $subpasta;
            }
            $caminhoRelativo .= '/' . $nomeArquivo;

            return ['success' => true, 'arquivo' => $caminhoRelativo, 'nome' => $nomeArquivo];
        }

        return ['success' => false, 'message' => 'Erro ao salvar o arquivo.'];
    }

    /**
     * Redimensiona imagem (se GD estiver disponivel)
     */
    public static function redimensionar($caminho, $categoria, $larguraMax = 800, $alturaMax = 800)
    {
        // Verificar se GD esta disponivel
        if (!function_exists('imagecreatefrompng')) {
            return false;
        }

        $info = @getimagesize($caminho);
        if (!$info) {
            return false;
        }

        $largura = $info[0];
        $altura = $info[1];
        $tipo = $info[2];

        if ($largura <= $larguraMax && $altura <= $alturaMax) {
            return true;
        }

        $proporcao = $largura / $altura;
        if ($largura > $altura) {
            $novaLargura = $larguraMax;
            $novaAltura = $larguraMax / $proporcao;
        } else {
            $novaAltura = $alturaMax;
            $novaLargura = $alturaMax * $proporcao;
        }

        switch ($tipo) {
            case IMAGETYPE_JPEG:
                $origem = imagecreatefromjpeg($caminho);
                break;
            case IMAGETYPE_PNG:
                $origem = imagecreatefrompng($caminho);
                break;
            case IMAGETYPE_GIF:
                $origem = imagecreatefromgif($caminho);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $origem = imagecreatefromwebp($caminho);
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }

        if (!$origem) {
            return false;
        }

        $destino = imagecreatetruecolor($novaLargura, $novaAltura);

        if ($tipo === IMAGETYPE_PNG) {
            imagealphablending($destino, false);
            imagesavealpha($destino, true);
            $transparente = imagecolorallocatealpha($destino, 0, 0, 0, 127);
            imagefilledrectangle($destino, 0, 0, $novaLargura, $novaAltura, $transparente);
        }

        imagecopyresampled($destino, $origem, 0, 0, 0, 0, $novaLargura, $novaAltura, $largura, $altura);

        switch ($tipo) {
            case IMAGETYPE_JPEG:
                imagejpeg($destino, $caminho, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($destino, $caminho, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($destino, $caminho);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagewebp')) {
                    imagewebp($destino, $caminho, 85);
                }
                break;
        }

        imagedestroy($origem);
        imagedestroy($destino);

        return true;
    }

    public static function remover($caminho)
    {
        if (empty($caminho)) {
            return false;
        }

        $caminhoCompleto = $_SERVER['DOCUMENT_ROOT'] . '/Aptus/public/' . $caminho;
        if (file_exists($caminhoCompleto)) {
            return unlink($caminhoCompleto);
        }
        return false;
    }

    private static function getPermittedMimes($categoria)
    {
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif'
        ];

        $mimes = [];
        foreach (self::$tiposPermitidos[$categoria] as $ext) {
            if (isset($mimeMap[$ext])) {
                $mimes[] = $mimeMap[$ext];
            }
        }

        return array_unique($mimes);
    }

    public static function getUrl($caminho)
    {
        if (empty($caminho)) {
            return '/Aptus/public/images/default.png';
        }
        return '/Aptus/public/' . $caminho;
    }
}