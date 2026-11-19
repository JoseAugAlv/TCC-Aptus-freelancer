<?php
// app/Helpers/UrlId.php
//
// Esconde IDs numéricos das URLs. Em vez de /chat/12 a URL vira /chat/Xk3p...
// O token é o ID cifrado (AES-128) com a chave do app (APP_KEY no .env):
//   - não dá para adivinhar o próximo registro trocando 12 por 13;
//   - é determinístico (o mesmo ID sempre gera o mesmo token), então links
//     continuam estáveis enquanto a APP_KEY não mudar.
// A autorização continua sendo feita nos controllers; isto não substitui as
// checagens de dono/participante, apenas tira o ID da URL.

require_once __DIR__ . '/../Config/config.php';

class UrlId
{
    private const MAGICO = 'APT1';
    private const CIFRA  = 'aes-128-ecb';

    private static ?string $chave = null;

    private static function chave(): string
    {
        if (self::$chave === null) {
            $base = Config::get('APP_KEY')
                ?: 'aptus|' . Config::get('DB_NAME') . '|' . Config::get('DB_USER') . '|' . Config::get('DB_PASS');
            self::$chave = substr(hash('sha256', (string) $base, true), 0, 16);
        }
        return self::$chave;
    }

    /** ID numérico → token para URL (22 caracteres, [A-Za-z0-9_-]). */
    public static function encode($id): string
    {
        $id = (int) $id;
        if ($id <= 0) return '';

        // 16 bytes = 1 bloco AES: marcador + ID (64 bits) + preenchimento fixo
        $bloco  = self::MAGICO . pack('J', $id) . "\0\0\0\0";
        $cifrado = openssl_encrypt($bloco, self::CIFRA, self::chave(), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);

        return rtrim(strtr(base64_encode($cifrado), '+/', '-_'), '=');
    }

    /** Token → ID numérico, ou null se for inválido/adulterado (inclusive ID numérico puro). */
    public static function decode($token): ?int
    {
        if (!is_string($token) || !preg_match('/^[A-Za-z0-9_-]{22}$/', $token)) {
            return null;
        }

        $cifrado = base64_decode(strtr($token, '-_', '+/') . '==', true);
        if ($cifrado === false || strlen($cifrado) !== 16) return null;

        $bloco = openssl_decrypt($cifrado, self::CIFRA, self::chave(), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        if ($bloco === false || substr($bloco, 0, 4) !== self::MAGICO || substr($bloco, 12) !== "\0\0\0\0") {
            return null;
        }

        $id = unpack('J', substr($bloco, 4, 8))[1];
        return $id > 0 ? (int) $id : null;
    }

    /** Lê um parâmetro da query string (?nome=token) já decodificado. 0 se ausente/inválido. */
    public static function query(string $nome): int
    {
        return self::decode($_GET[$nome] ?? null) ?? 0;
    }
}

if (!function_exists('url_id')) {
    /** Atalho para as views: <a href="/Aptus/chat/<?= url_id($id) ?>"> */
    function url_id($id): string
    {
        return UrlId::encode($id);
    }
}
