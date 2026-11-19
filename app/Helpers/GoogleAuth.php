<?php
// app/Helpers/GoogleAuth.php
//
// Login com Google (OAuth 2.0 + OpenID Connect), sem bibliotecas extras.
// Configuração no .env:
//   GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
//   GOOGLE_CLIENT_SECRET=xxxxxxxx
// URI de redirecionamento a cadastrar no Google Cloud:
//   {APP_URL}/auth/google/callback
// Sem as duas chaves, o botão "Entrar com Google" simplesmente não aparece.

require_once __DIR__ . '/../Config/config.php';

class GoogleAuth
{
    private const URL_AUTORIZAR = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const URL_TOKEN     = 'https://oauth2.googleapis.com/token';
    private const URL_USUARIO   = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function configurado(): bool
    {
        return trim((string) Config::get('GOOGLE_CLIENT_ID')) !== ''
            && trim((string) Config::get('GOOGLE_CLIENT_SECRET')) !== '';
    }

    /** URL absoluta de retorno (precisa ser idêntica à cadastrada no Google Cloud). */
    public static function redirectUri(): string
    {
        $appUrl = rtrim(trim((string) (Config::get('APP_URL') ?: '')), '/');
        if (!preg_match('#^https?://#i', $appUrl)) {
            $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $appUrl = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/' . ltrim($appUrl, '/');
            $appUrl = rtrim($appUrl, '/');
        }
        return $appUrl . '/auth/google/callback';
    }

    /** Monta a URL do Google e guarda o "state" (proteção CSRF) na sessão. */
    public static function urlAutorizacao(): string
    {
        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;

        return self::URL_AUTORIZAR . '?' . http_build_query([
            'client_id'     => Config::get('GOOGLE_CLIENT_ID'),
            'redirect_uri'  => self::redirectUri(),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'prompt'        => 'select_account',
            'access_type'   => 'online',
        ]);
    }

    public static function stateValido(?string $state): bool
    {
        $esperado = $_SESSION['google_oauth_state'] ?? '';
        unset($_SESSION['google_oauth_state']);
        return $esperado !== '' && is_string($state) && hash_equals($esperado, $state);
    }

    /**
     * Troca o "code" pelos dados do usuário.
     * Retorna ['sub', 'email', 'email_verified', 'name', 'picture'] ou null.
     */
    public static function obterUsuario(string $code): ?array
    {
        $token = self::requisicao(self::URL_TOKEN, [
            'code'          => $code,
            'client_id'     => Config::get('GOOGLE_CLIENT_ID'),
            'client_secret' => Config::get('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'  => self::redirectUri(),
            'grant_type'    => 'authorization_code',
        ]);
        if (empty($token['access_token'])) {
            error_log('Google OAuth: falha ao obter token: ' . json_encode($token));
            return null;
        }

        $info = self::requisicao(self::URL_USUARIO, null, $token['access_token']);
        if (empty($info['sub']) || empty($info['email'])) {
            error_log('Google OAuth: resposta de usuário inválida.');
            return null;
        }
        return $info;
    }

    /** POST (com $campos) ou GET (com $bearer) e decodifica o JSON da resposta. */
    private static function requisicao(string $url, ?array $campos, ?string $bearer = null): ?array
    {
        $headers = ['Accept: application/json'];
        if ($bearer) $headers[] = 'Authorization: Bearer ' . $bearer;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => $headers,
            ]);
            if ($campos !== null) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($campos));
            }
            $resposta = curl_exec($ch);
            if ($resposta === false) error_log('Google OAuth (cURL): ' . curl_error($ch));
            curl_close($ch);
        } else {
            $opcoes = ['http' => [
                'method'        => $campos !== null ? 'POST' : 'GET',
                'header'        => implode("\r\n", array_merge($headers, $campos !== null ? ['Content-Type: application/x-www-form-urlencoded'] : [])),
                'content'       => $campos !== null ? http_build_query($campos) : '',
                'timeout'       => 15,
                'ignore_errors' => true,
            ]];
            $resposta = @file_get_contents($url, false, stream_context_create($opcoes));
        }

        if (!is_string($resposta) || $resposta === '') return null;
        $json = json_decode($resposta, true);
        return is_array($json) ? $json : null;
    }

    /** Logo "G" oficial do Google, inline (sem imagem externa). */
    public static function iconeSvg(int $tam = 18): string
    {
        return '<svg width="' . $tam . '" height="' . $tam . '" viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
             . '<path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.2-.1-2.3-.4-3.5z"/>'
             . '<path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>'
             . '<path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/>'
             . '<path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.2-.1-2.3-.4-3.5z"/>'
             . '</svg>';
    }
}
