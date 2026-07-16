<?php
// app/Core/Mail.php

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mail
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromName;

    public function __construct()
    {
        $this->host = Config::get('MAIL_HOST') ?? 'smtp.gmail.com';
        $this->port = Config::get('MAIL_PORT') ?? 587;
        $this->username = Config::get('MAIL_USER') ?? '';
        $this->password = Config::get('MAIL_PASS') ?? '';
        $this->fromName = Config::get('MAIL_FROM_NAME') ?? 'Aptus';
    }

    /**
     * Envia um e-mail usando SMTP
     */
    public function send($para, $assunto, $mensagem, $paraNome = '')
    {
        if (empty($this->username) || empty($this->password)) {
            error_log("Mail: Credenciais SMTP não configuradas");
            return false;
        }

        $mail = new PHPMailer(true);
        
        try {
            // Configuração do servidor SMTP
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->port;

            $mail->setFrom($this->username, $this->fromName);
            $mail->addAddress($para, $paraNome);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $mensagem;
            $mail->AltBody = strip_tags($mensagem);

            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Envia e-mail de redefinição de senha
     */
    public function sendResetPassword($email, $nome, $token)
    {
        $appUrl = Config::get('APP_URL') ?? 'http://localhost/Aptus';
        $link = $appUrl . '/auth/redefinir?token=' . $token;
        
        $assunto = "Redefinição de Senha - Aptus";
        
        $mensagem = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; padding: 40px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
                .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #006577; }
                .header h1 { color: #006577; font-weight: 800; font-size: 28px; margin: 0; }
                .header h1 span { color: #C9A227; }
                .content { color: #1a2f3e; line-height: 1.8; }
                .btn { display: inline-block; padding: 14px 35px; background: #006577; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 700; margin: 20px 0; }
                .btn:hover { background: #004d5c; }
                .token-box { background: #f1f5f9; padding: 12px; border-radius: 8px; font-family: monospace; word-break: break-all; font-size: 14px; color: #1a2f3e; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #95a5a6; font-size: 14px; }
                .footer a { color: #006577; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>APTUS</h1>
                </div>
                <div class='content'>
                    <h2>Olá, " . htmlspecialchars($nome) . "!</h2>
                    <p>Recebemos uma solicitação para redefinir sua senha no <strong>Aptus</strong>.</p>
                    <p>Clique no botão abaixo para redefinir sua senha:</p>
                    <p style='text-align: center;'>
                        <a href='" . $link . "' class='btn'>Redefinir Senha</a>
                    </p>
                    <p>Ou copie e cole o link no navegador:</p>
                    <p class='token-box'>" . $link . "</p>
                    <p><strong>Este link é válido por 1 hora.</strong></p>
                    <p>Se você não solicitou a redefinição de senha, ignore este e-mail.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Aptus - Conectando Talentos</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->send($email, $assunto, $mensagem, $nome);
    }

    /**
     * Envia e-mail de verificação de conta
     */
    public function sendVerificationEmail($email, $nome, $token)
    {
        $appUrl = Config::get('APP_URL') ?? 'http://localhost/Aptus';
        $link = $appUrl . '/auth/verificar?token=' . $token;
        
        $assunto = "Confirme seu e-mail - Aptus";
        
        $mensagem = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; padding: 40px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
                .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #006577; }
                .header h1 { color: #006577; font-weight: 800; font-size: 28px; margin: 0; }
                .content { color: #1a2f3e; line-height: 1.8; }
                .btn { display: inline-block; padding: 14px 35px; background: #006577; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 700; margin: 20px 0; }
                .btn:hover { background: #004d5c; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #95a5a6; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>APTUS</h1>
                </div>
                <div class='content'>
                    <h2>Olá, " . htmlspecialchars($nome) . "!</h2>
                    <p>Bem-vindo ao <strong>Aptus</strong>! Para começar a usar sua conta, você precisa confirmar seu e-mail.</p>
                    <p>Clique no botão abaixo para verificar seu endereço de e-mail:</p>
                    <p style='text-align: center;'>
                        <a href='" . $link . "' class='btn'>Confirmar E-mail</a>
                    </p>
                    <p>Se você não criou uma conta no Aptus, ignore este e-mail.</p>
                    <p>Este link é válido por 24 horas.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Aptus - Conectando Talentos</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->send($email, $assunto, $mensagem, $nome);
    }

    /**
     * Método enviar() - alias para send() para compatibilidade
     * Usado pelo AuthController
     */
    public function enviar($email, $nome, $assunto, $mensagem)
    {
        return $this->send($email, $assunto, $mensagem, $nome);
    }
}