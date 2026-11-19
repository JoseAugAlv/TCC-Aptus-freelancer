<?php
// app/Core/Mailer.php
//
// Envio de e-mails do Aptus. Todos os e-mails usam o mesmo layout (layout()),
// feito com tabelas e estilos inline para funcionar no Gmail, Outlook e celular.
// A logo vai embutida no próprio e-mail (CID), sem depender de imagem externa.

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    private const COR_PRIMARIA = '#006577';
    private const COR_DOURADO  = '#C9A227';
    private const COR_TEXTO    = '#1f2d3a';
    private const COR_SUAVE    = '#5b6b78';
    private const COR_FUNDO    = '#eef3f6';
    private const COR_BORDA    = '#dfe7ec';
    private const FONTE        = "'Segoe UI', Roboto, Helvetica, Arial, sans-serif";

    private $host;
    private $port;
    private $username;
    private $password;
    private $fromName;

    public function __construct()
    {
        $this->host     = Config::get('MAIL_HOST') ?? 'smtp.gmail.com';
        $this->port     = Config::get('MAIL_PORT') ?? 587;
        $this->username = Config::get('MAIL_USER') ?? '';
        $this->password = Config::get('MAIL_PASS') ?? '';
        $this->fromName = Config::get('MAIL_FROM_NAME') ?? 'Aptus';
    }

    /**
     * URL absoluta do sistema para links de e-mail, sem barra no final.
     * Se o APP_URL vier sem domínio (ex.: "/projetos/Aptus"), usa o host da requisição.
     */
    private function urlBase(): string
    {
        $appUrl = rtrim(trim((string) (Config::get('APP_URL') ?: 'http://localhost/Aptus')), '/');

        if (!preg_match('#^https?://#i', $appUrl)) {
            $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $appUrl = ($https ? 'https' : 'http') . '://' . $host . '/' . ltrim($appUrl, '/');
            $appUrl = rtrim($appUrl, '/');
        }

        return $appUrl;
    }

    private static function e($texto): string
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Envia um e-mail usando SMTP.
     * $texto é a versão em texto puro (clientes sem HTML); se vier vazio,
     * é gerada a partir do HTML.
     */
    public function send($para, $assunto, $mensagem, $paraNome = '', $texto = '')
    {
        if (empty($this->username) || empty($this->password)) {
            error_log("Mail: Credenciais SMTP nao configuradas");
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->port;
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;
            $mail->Encoding   = PHPMailer::ENCODING_BASE64;

            $mail->setFrom($this->username, $this->fromName);
            $mail->addAddress($para, $paraNome);

            $logo = __DIR__ . '/../../public/images/logo-email.png';
            if (strpos($mensagem, 'cid:logo-aptus') !== false && is_file($logo)) {
                $mail->addEmbeddedImage($logo, 'logo-aptus', 'aptus.png', PHPMailer::ENCODING_BASE64, 'image/png');
            }

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $mensagem;
            $mail->AltBody = $texto !== '' ? $texto : $this->htmlParaTexto($mensagem);

            return $mail->send();

        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
            return false;
        }
    }

    private function htmlParaTexto(string $html): string
    {
        $html  = preg_replace('#<(style|head)[^>]*>.*?</\1>#si', '', $html);
        $html  = preg_replace('#<br\s*/?>|</p>|</tr>|</h\d>#i', "\n", $html);
        $texto = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $texto = preg_replace("/[ \t]+/", ' ', $texto);
        return trim(preg_replace("/\n\s*\n+/", "\n\n", $texto));
    }

    /**
     * Layout padrão dos e-mails.
     *
     * $o = [
     *   'preheader' => texto curto que aparece ao lado do assunto na caixa de entrada,
     *   'titulo'    => título principal,
     *   'saudacao'  => "Olá, Fulano," (opcional),
     *   'paragrafos'=> [HTML já escapado, ...],
     *   'botao'     => ['texto' => ..., 'url' => ...] (opcional),
     *   'codigo'    => código de verificação (opcional),
     *   'blocos'    => [['rotulo' => ..., 'valor' => HTML escapado], ...] (opcional),
     *   'nota'      => observação em destaque suave (opcional, HTML escapado),
     *   'rodape'    => texto final pequeno (opcional, HTML escapado),
     * ]
     */
    private function layout(array $o): string
    {
        $f        = self::FONTE;
        $primaria = self::COR_PRIMARIA;
        $dourado  = self::COR_DOURADO;
        $texto    = self::COR_TEXTO;
        $suave    = self::COR_SUAVE;
        $fundo    = self::COR_FUNDO;
        $borda    = self::COR_BORDA;
        $site     = $this->urlBase();
        $ano      = date('Y');

        $preheader = self::e($o['preheader'] ?? '');
        $titulo    = self::e($o['titulo'] ?? '');

        $corpo = '';
        if (!empty($o['saudacao'])) {
            $corpo .= "<p style=\"margin:0 0 16px;font-family:{$f};font-size:16px;line-height:1.6;color:{$texto};\">" . self::e($o['saudacao']) . "</p>";
        }
        foreach ((array) ($o['paragrafos'] ?? []) as $p) {
            $corpo .= "<p style=\"margin:0 0 16px;font-family:{$f};font-size:15px;line-height:1.7;color:{$texto};\">{$p}</p>";
        }

        if (!empty($o['codigo'])) {
            $codigo = self::e(implode(' ', str_split((string) $o['codigo'])));
            $corpo .= "
            <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin:8px 0 24px;\">
              <tr><td align=\"center\" style=\"background:#f4f8fa;border:1px solid {$borda};border-radius:12px;padding:22px 12px;\">
                <div style=\"font-family:{$f};font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:{$suave};margin-bottom:8px;\">Seu código</div>
                <div style=\"font-family:'Courier New',Consolas,monospace;font-size:32px;font-weight:700;letter-spacing:6px;color:{$primaria};\">{$codigo}</div>
              </td></tr>
            </table>";
        }

        foreach ((array) ($o['blocos'] ?? []) as $b) {
            $corpo .= "
            <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin:0 0 12px;\">
              <tr><td style=\"font-family:{$f};font-size:12px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:{$suave};padding:0 0 6px;\">" . self::e($b['rotulo']) . "</td></tr>
              <tr><td style=\"font-family:{$f};font-size:15px;line-height:1.6;color:{$texto};background:#f7fafb;border:1px solid {$borda};border-radius:8px;padding:12px 14px;\">{$b['valor']}</td></tr>
            </table>";
        }

        if (!empty($o['botao']['url'])) {
            $url   = self::e($o['botao']['url']);
            $rot   = self::e($o['botao']['texto'] ?? 'Acessar');
            $corpo .= "
            <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" style=\"margin:12px auto 24px;\">
              <tr><td align=\"center\" bgcolor=\"{$primaria}\" style=\"border-radius:10px;\">
                <a href=\"{$url}\" target=\"_blank\" style=\"display:inline-block;padding:14px 34px;font-family:{$f};font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;\">{$rot}</a>
              </td></tr>
            </table>
            <p style=\"margin:0 0 6px;font-family:{$f};font-size:13px;line-height:1.6;color:{$suave};\">Se o botão não funcionar, copie e cole este endereço no navegador:</p>
            <p style=\"margin:0 0 16px;font-family:{$f};font-size:13px;line-height:1.5;word-break:break-all;\"><a href=\"{$url}\" target=\"_blank\" style=\"color:{$primaria};text-decoration:underline;\">{$url}</a></p>";
        }

        if (!empty($o['nota'])) {
            $corpo .= "
            <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin:8px 0 0;\">
              <tr><td style=\"border-left:3px solid {$dourado};background:#fbf8ee;padding:12px 16px;font-family:{$f};font-size:13px;line-height:1.6;color:{$suave};border-radius:0 8px 8px 0;\">{$o['nota']}</td></tr>
            </table>";
        }

        $rodapeExtra = !empty($o['rodape'])
            ? "<p style=\"margin:0 0 10px;font-family:{$f};font-size:12px;line-height:1.6;color:{$suave};\">{$o['rodape']}</p>"
            : '';

        return "<!DOCTYPE html>
<html lang=\"pt-BR\">
<head>
<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
<meta name=\"color-scheme\" content=\"light\">
<meta name=\"supported-color-schemes\" content=\"light\">
<title>{$titulo}</title>
</head>
<body style=\"margin:0;padding:0;background:{$fundo};-webkit-text-size-adjust:100%;\">
<div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">{$preheader}</div>
<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" bgcolor=\"{$fundo}\" style=\"background:{$fundo};\">
  <tr><td align=\"center\" style=\"padding:32px 12px;\">
    <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\">
      <tr><td align=\"center\" style=\"padding:0 0 20px;\">
        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\"><tr>
          <td style=\"padding-right:10px;vertical-align:middle;\"><img src=\"cid:logo-aptus\" width=\"44\" alt=\"Aptus\" style=\"display:block;border:0;width:44px;height:auto;\"></td>
          <td style=\"vertical-align:middle;font-family:{$f};font-size:24px;font-weight:700;letter-spacing:.5px;color:{$primaria};\">Aptus</td>
        </tr></table>
      </td></tr>
      <tr><td bgcolor=\"#ffffff\" style=\"background:#ffffff;border-radius:16px;border:1px solid {$borda};overflow:hidden;\">
        <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
          <tr><td style=\"height:5px;line-height:5px;font-size:0;background:{$primaria};border-radius:16px 16px 0 0;\">&nbsp;</td></tr>
          <tr><td style=\"padding:36px 40px 12px;\">
            <h1 style=\"margin:0 0 6px;font-family:{$f};font-size:22px;line-height:1.3;font-weight:700;color:{$texto};\">{$titulo}</h1>
            <div style=\"width:48px;height:3px;background:{$dourado};border-radius:2px;margin:0 0 24px;font-size:0;line-height:0;\">&nbsp;</div>
            {$corpo}
          </td></tr>
          <tr><td style=\"padding:8px 40px 32px;\">
            <p style=\"margin:0;font-family:{$f};font-size:14px;line-height:1.6;color:{$texto};\">Atenciosamente,<br><strong>Equipe Aptus</strong></p>
          </td></tr>
        </table>
      </td></tr>
      <tr><td align=\"center\" style=\"padding:24px 24px 0;\">
        {$rodapeExtra}
        <p style=\"margin:0 0 6px;font-family:{$f};font-size:12px;line-height:1.6;color:{$suave};\">Este é um e-mail automático. Por favor, não responda.</p>
        <p style=\"margin:0;font-family:{$f};font-size:12px;line-height:1.6;color:{$suave};\">&copy; {$ano} Aptus &middot; Conectando Talentos &middot; <a href=\"" . self::e($site) . "\" style=\"color:{$primaria};text-decoration:none;\">Acessar o site</a></p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>";
    }

    /** Primeiro nome, para a saudação. */
    private static function primeiroNome($nome): string
    {
        $nome = trim((string) $nome);
        return $nome === '' ? '' : explode(' ', $nome)[0];
    }

    private static function saudacao($nome): string
    {
        $p = self::primeiroNome($nome);
        return $p !== '' ? "Olá, {$p}," : 'Olá,';
    }

    /**
     * Envia e-mail de redefinição de senha
     */
    public function sendResetPassword($email, $nome, $token)
    {
        $link = $this->urlBase() . '/auth/redefinir?token=' . urlencode($token);

        $html = $this->layout([
            'preheader'  => 'Use o link para criar uma nova senha. Ele vale por 1 hora.',
            'titulo'     => 'Redefinição de senha',
            'saudacao'   => self::saudacao($nome),
            'paragrafos' => [
                'Recebemos uma solicitação para redefinir a senha da sua conta no <strong>Aptus</strong>.',
                'Para criar uma nova senha, clique no botão abaixo:',
            ],
            'botao' => ['texto' => 'Redefinir senha', 'url' => $link],
            'nota'  => 'Este link é válido por <strong>1 hora</strong> e só pode ser usado uma vez. '
                     . 'Se você não pediu a redefinição, ignore este e-mail: sua senha continua a mesma.',
        ]);

        $texto = self::saudacao($nome) . "\n\n"
               . "Recebemos uma solicitação para redefinir a senha da sua conta no Aptus.\n"
               . "Para criar uma nova senha, acesse:\n{$link}\n\n"
               . "O link é válido por 1 hora. Se você não pediu a redefinição, ignore este e-mail.\n\nEquipe Aptus";

        return $this->send($email, 'Redefinição de senha - Aptus', $html, $nome, $texto);
    }

    /**
     * Envia e-mail de verificação de conta (cadastro)
     */
    public function sendVerificationEmail($email, $nome, $token)
    {
        $link = $this->urlBase() . '/auth/verificar?token=' . urlencode($token);

        $html = $this->layout([
            'preheader'  => 'Confirme seu e-mail para ativar sua conta no Aptus.',
            'titulo'     => 'Confirme seu e-mail',
            'saudacao'   => self::saudacao($nome),
            'paragrafos' => [
                'Que bom ter você no <strong>Aptus</strong>. Falta só um passo para ativar sua conta: confirmar que este endereço de e-mail é seu.',
            ],
            'botao' => ['texto' => 'Confirmar e-mail', 'url' => $link],
            'nota'  => 'O link é válido por <strong>24 horas</strong>. Cadastros não confirmados nesse prazo são '
                     . 'removidos automaticamente. Se você não criou uma conta no Aptus, ignore este e-mail.',
        ]);

        $texto = self::saudacao($nome) . "\n\n"
               . "Para ativar sua conta no Aptus, confirme seu e-mail acessando:\n{$link}\n\n"
               . "O link é válido por 24 horas. Se você não criou uma conta, ignore este e-mail.\n\nEquipe Aptus";

        return $this->send($email, 'Confirme seu e-mail - Aptus', $html, $nome, $texto);
    }

    /**
     * Código de 6 dígitos para confirmar um e-mail (troca de e-mail ou
     * verificação pela área de Segurança do perfil).
     */
    public function sendCodigoEmail($email, $nome, string $codigo, int $minutos = 15, bool $trocaEmail = true)
    {
        $html = $this->layout([
            'preheader'  => "Seu código de verificação do Aptus é {$codigo}.",
            'titulo'     => $trocaEmail ? 'Confirme seu novo e-mail' : 'Código de verificação',
            'saudacao'   => self::saudacao($nome),
            'paragrafos' => [
                $trocaEmail
                    ? 'Você pediu para usar este endereço como o novo e-mail da sua conta no <strong>Aptus</strong>. Digite o código abaixo na área de Segurança do seu perfil para concluir a troca.'
                    : 'Digite o código abaixo na área de Segurança do seu perfil para confirmar seu e-mail.',
            ],
            'codigo' => $codigo,
            'nota'   => "O código vale por <strong>{$minutos} minutos</strong>. Nunca compartilhe este código com ninguém: "
                      . 'a equipe do Aptus nunca vai pedi-lo. Se não foi você, ignore este e-mail.',
        ]);

        $texto = self::saudacao($nome) . "\n\n"
               . "Seu código de verificação do Aptus é: {$codigo}\n"
               . "Ele vale por {$minutos} minutos. Se não foi você, ignore este e-mail.\n\nEquipe Aptus";

        return $this->send($email, "Seu código de verificação: {$codigo} - Aptus", $html, $nome, $texto);
    }

    /**
     * Mensagem do formulário de contato, enviada para o administrador.
     */
    public function sendContatoAdmin($emailAdmin, $nome, $email, $assunto, $mensagem)
    {
        $html = $this->layout([
            'preheader'  => "Nova mensagem de {$nome}: {$assunto}",
            'titulo'     => 'Nova mensagem de contato',
            'paragrafos' => ['Uma nova mensagem foi enviada pelo formulário de contato do site.'],
            'blocos'     => [
                ['rotulo' => 'Nome',     'valor' => self::e($nome)],
                ['rotulo' => 'E-mail',   'valor' => '<a href="mailto:' . self::e($email) . '" style="color:' . self::COR_PRIMARIA . ';">' . self::e($email) . '</a>'],
                ['rotulo' => 'Assunto',  'valor' => self::e($assunto)],
                ['rotulo' => 'Mensagem', 'valor' => nl2br(self::e($mensagem))],
            ],
            'nota' => 'Para responder, escreva diretamente para o e-mail informado acima.',
        ]);

        $texto = "Nova mensagem de contato\n\nNome: {$nome}\nE-mail: {$email}\nAssunto: {$assunto}\n\n{$mensagem}";

        return $this->send($emailAdmin, '[Contato] ' . $assunto, $html, 'Administrador', $texto);
    }

    /**
     * Confirmação de recebimento enviada para quem usou o formulário de contato.
     */
    public function sendContatoConfirmacao($email, $nome, $assunto, $mensagem)
    {
        $html = $this->layout([
            'preheader'  => 'Recebemos sua mensagem e responderemos em até 5 dias úteis.',
            'titulo'     => 'Recebemos sua mensagem',
            'saudacao'   => self::saudacao($nome),
            'paragrafos' => [
                'Obrigado por entrar em contato com o <strong>Aptus</strong>. Sua mensagem chegou até nós e nossa equipe vai responder em até <strong>5 dias úteis</strong>.',
                'Confira abaixo o que você nos enviou:',
            ],
            'blocos' => [
                ['rotulo' => 'Assunto',  'valor' => self::e($assunto)],
                ['rotulo' => 'Mensagem', 'valor' => nl2br(self::e($mensagem))],
            ],
            'botao' => ['texto' => 'Ver serviços', 'url' => $this->urlBase() . '/anuncios'],
        ]);

        $texto = self::saudacao($nome) . "\n\n"
               . "Recebemos sua mensagem e responderemos em até 5 dias úteis.\n\nAssunto: {$assunto}\n\n{$mensagem}\n\nEquipe Aptus";

        return $this->send($email, 'Recebemos sua mensagem - Aptus', $html, $nome, $texto);
    }

    /**
     * Metodo enviar() - alias para send() para compatibilidade
     */
    public function enviar($email, $nome, $assunto, $mensagem)
    {
        return $this->send($email, $assunto, $mensagem, $nome);
    }
}
