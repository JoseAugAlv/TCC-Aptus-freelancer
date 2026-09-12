<?php
// app/Helpers/UploadHelper.php

require_once __DIR__ . '/SecurityHelper.php';
require_once __DIR__ . '/../Config/config.php';

class UploadHelper
{
    public static $tiposPermitidos = [
        'perfil'    => ['jpg', 'jpeg', 'png', 'webp'],
        'anuncio'   => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'portfolio' => ['jpg', 'jpeg', 'png', 'webp'],
    ];

    public static $tamanhosMaximos = [
        'perfil'    => 2097152, // 2MB
        'anuncio'   => 5242880, // 5MB
        'portfolio' => 5242880, // 5MB
    ];

    private static function publicDir(): string
    {
        $dir = realpath(__DIR__ . '/../../public');
        if ($dir === false) {
            throw new RuntimeException('Diretório public não encontrado.');
        }
        return $dir;
    }

    private static function baseUrl(): string
    {
        $appUrl = Config::get('APP_URL', '/Aptus');
        return rtrim($appUrl, '/') . '/public';
    }

    public static function upload($arquivo, $categoria, $subpasta = '')
    {
        if (!isset(self::$tiposPermitidos[$categoria])) {
            return ['success' => false, 'message' => 'Categoria de upload invalida.'];
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $mensagens = [
                UPLOAD_ERR_INI_SIZE   => 'Arquivo excede o tamanho maximo permitido pelo servidor.',
                UPLOAD_ERR_FORM_SIZE  => 'Arquivo excede o tamanho maximo permitido.',
                UPLOAD_ERR_PARTIAL    => 'Upload incompleto.',
                UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporaria nao encontrada.',
                UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever o arquivo no disco.',
                UPLOAD_ERR_EXTENSION  => 'Upload bloqueado por extensao PHP.',
            ];
            return ['success' => false, 'message' => $mensagens[$arquivo['error']] ?? 'Erro no upload.'];
        }

        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, self::$tiposPermitidos[$categoria], true)) {
            return [
                'success' => false,
                'message' => 'Formato de arquivo nao permitido. Use: '
                           . implode(', ', self::$tiposPermitidos[$categoria]),
            ];
        }

        if ($arquivo['size'] > self::$tamanhosMaximos[$categoria]) {
            $maxMB = self::$tamanhosMaximos[$categoria] / 1048576;
            return ['success' => false, 'message' => "Arquivo muito grande. Maximo: {$maxMB}MB"];
        }

        $mimesPermitidos = self::getPermittedMimes($categoria);
        if (!SecurityHelper::validarMimeArquivo($arquivo['tmp_name'], $mimesPermitidos)) {
            return ['success' => false, 'message' => 'Tipo de arquivo invalido.'];
        }

        $pastaBase = self::publicDir() . '/uploads/' . $categoria;
        if (!empty($subpasta)) {
            $pastaBase .= '/' . basename($subpasta);
        }

        if (!file_exists($pastaBase)) {
            if (!@mkdir($pastaBase, 0755, true) && !is_dir($pastaBase)) {
                return ['success' => false, 'message' => 'Nao foi possivel criar a pasta de upload.'];
            }
        }

        $nomeArquivo     = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
        $caminhoCompleto = $pastaBase . '/' . $nomeArquivo;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            return ['success' => false, 'message' => 'Erro ao salvar o arquivo.'];
        }

        if (function_exists('imagecreatefrompng')) {
            self::redimensionar($caminhoCompleto, $categoria);
        } else {
            error_log('Aviso: Extensao GD nao disponivel. Imagem nao redimensionada.');
        }

        SecurityHelper::logAuditoria(
            'upload_arquivo',
            $_SESSION['usuario']['id'] ?? 'anonymous',
            'Arquivo: ' . $nomeArquivo . ' | Categoria: ' . $categoria,
            'info'
        );

        $caminhoRelativo = 'uploads/' . $categoria;
        if (!empty($subpasta)) {
            $caminhoRelativo .= '/' . basename($subpasta);
        }
        $caminhoRelativo .= '/' . $nomeArquivo;

        return [
            'success' => true,
            'arquivo' => $caminhoRelativo,
            'nome'    => $nomeArquivo,
        ];
    }

    public static function redimensionar($caminho, $categoria, $larguraMax = 800, $alturaMax = 800)
    {
        if (!function_exists('imagecreatefrompng')) {
            return false;
        }

        $info = @getimagesize($caminho);
        if (!$info) {
            return false;
        }

        [$largura, $altura, $tipo] = $info;

        if ($largura <= $larguraMax && $altura <= $alturaMax) {
            return true;
        }

        $proporcao = $largura / max($altura, 1);
        if ($largura > $altura) {
            $novaLargura = $larguraMax;
            $novaAltura  = (int) round($larguraMax / $proporcao);
        } else {
            $novaAltura  = $alturaMax;
            $novaLargura = (int) round($alturaMax * $proporcao);
        }

        switch ($tipo) {
            case IMAGETYPE_JPEG: $origem = imagecreatefromjpeg($caminho); break;
            case IMAGETYPE_PNG:  $origem = imagecreatefrompng($caminho);  break;
            case IMAGETYPE_GIF:  $origem = imagecreatefromgif($caminho);  break;
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

        imagecopyresampled(
            $destino, $origem,
            0, 0, 0, 0,
            $novaLargura, $novaAltura,
            $largura, $altura
        );

        switch ($tipo) {
            case IMAGETYPE_JPEG: imagejpeg($destino, $caminho, 85); break;
            case IMAGETYPE_PNG:  imagepng($destino, $caminho, 8);   break;
            case IMAGETYPE_GIF:  imagegif($destino, $caminho);      break;
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

        $base = self::publicDir() . '/uploads';
        $alvo = realpath(self::publicDir() . '/' . ltrim($caminho, '/'));

        // Só remove se o alvo estiver DENTRO da pasta de uploads
        if ($alvo === false || strpos($alvo, $base) !== 0) {
            return false;
        }

        return @unlink($alvo);
    }

    private static function getPermittedMimes($categoria)
    {
        $mimeMap = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
        ];

        $mimes = [];
        foreach (self::$tiposPermitidos[$categoria] as $ext) {
            if (isset($mimeMap[$ext])) {
                $mimes[] = $mimeMap[$ext];
            }
        }
        return array_values(array_unique($mimes));
    }

    public static function getUrl($caminho)
    {
        if (empty($caminho)) {
            return self::baseUrl() . '/images/default.png';
        }
        return self::baseUrl() . '/' . ltrim($caminho, '/');
    }
}