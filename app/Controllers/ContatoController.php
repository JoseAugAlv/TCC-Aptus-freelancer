<?php
// app/Controllers/ContatoController.php

require_once __DIR__ . '/../Core/Mailer.php';

class ContatoController
{
    public function index()
    {
        $sucesso = false;
        $erro = '';
        
        // Processar envio do formulário
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $assunto = trim($_POST['assunto'] ?? '');
            $mensagem = trim($_POST['mensagem'] ?? '');
            
            // Validações
            if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
                $erro = 'Todos os campos são obrigatórios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = 'E-mail inválido.';
            } else {
                // Enviar e-mail
                $mail = new Mailer();
                
                // ============================================================
                // ALTERE ESTE E-MAIL PARA UM ENDEREÇO VÁLIDO
                // ============================================================
                $emailAdmin = 'seu_email@dominio.com';  // <-- ALTERE AQUI
                // ============================================================
                
                // E-mail para o administrador
                $assuntoEmail = '[Aptus Contato] ' . $assunto;
                $corpoEmail = "
                    <html>
                    <head>
                        <style>
                            body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a2f3e; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #006577; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 8px 8px; border: 1px solid #e2e8f0; }
                            .field { margin-bottom: 15px; }
                            .field label { font-weight: 600; display: block; margin-bottom: 5px; color: #006577; }
                            .field p { margin: 0; padding: 8px 12px; background: #fff; border-radius: 6px; border: 1px solid #e2e8f0; }
                            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2 style='margin: 0; color: #fff;'>📩 Novo Contato - Aptus</h2>
                            </div>
                            <div class='content'>
                                <div class='field'>
                                    <label>Nome</label>
                                    <p>" . htmlspecialchars($nome) . "</p>
                                </div>
                                <div class='field'>
                                    <label>E-mail</label>
                                    <p>" . htmlspecialchars($email) . "</p>
                                </div>
                                <div class='field'>
                                    <label>Assunto</label>
                                    <p>" . htmlspecialchars($assunto) . "</p>
                                </div>
                                <div class='field'>
                                    <label>Mensagem</label>
                                    <p style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($mensagem)) . "</p>
                                </div>
                            </div>
                            <div class='footer'>
                                <p>Enviado através do site Aptus</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                // E-mail de confirmação para o usuário
                $corpoConfirmacao = "
                    <html>
                    <head>
                        <style>
                            body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a2f3e; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #006577; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 8px 8px; border: 1px solid #e2e8f0; }
                            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
                            .btn { display: inline-block; padding: 10px 20px; background: #006577; color: #fff; text-decoration: none; border-radius: 6px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2 style='margin: 0; color: #fff;'>✅ Recebemos sua mensagem!</h2>
                            </div>
                            <div class='content'>
                                <p>Olá <strong>" . htmlspecialchars($nome) . "</strong>,</p>
                                <p>Agradecemos pelo seu contato! Recebemos sua mensagem e nossa equipe responderá em até <strong>5 dias úteis</strong>.</p>
                                <p style='margin-top: 20px;'>
                                    <strong>Resumo da sua mensagem:</strong>
                                </p>
                                <div style='background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 10px 0;'>
                                    <p><strong>Assunto:</strong> " . htmlspecialchars($assunto) . "</p>
                                    <p><strong>Mensagem:</strong></p>
                                    <p style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($mensagem)) . "</p>
                                </div>
                                <p style='margin-top: 20px;'>Enquanto isso, você pode explorar nossos serviços:</p>
                                <p style='text-align: center;'>
                                    <a href='/Aptus/anuncios' class='btn'>Ver Serviços</a>
                                </p>
                            </div>
                            <div class='footer'>
                                <p>&copy; " . date('Y') . " Aptus - Conectando Talentos</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                // Enviar para o administrador
                $enviadoAdmin = $mail->enviar($emailAdmin, 'Administrador', $assuntoEmail, $corpoEmail);
                
                // Enviar confirmação para o usuário
                $assuntoConfirmacao = 'Recebemos sua mensagem - Aptus';
                $enviadoUsuario = $mail->enviar($email, $nome, $assuntoConfirmacao, $corpoConfirmacao);
                
                if ($enviadoAdmin && $enviadoUsuario) {
                    $sucesso = true;
                } else {
                    $erro = 'Erro ao enviar a mensagem. Por favor, tente novamente mais tarde.';
                }
            }
        }

        $tituloPagina = 'Contato - Aptus';
        $cssPagina = 'contato.css';
        
        require '../app/Views/contato/index.php';
    }
}