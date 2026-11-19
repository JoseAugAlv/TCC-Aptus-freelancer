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
                require_once __DIR__ . '/../Models/Configuracao.php';
                $cfg = new Configuracao();
                $emailAdmin = $cfg->get('site_email') ?: 'contato@aptus.com';
                // ============================================================
                
                // E-mail para o administrador e confirmação para o usuário (layout em Mailer)
                $enviadoAdmin   = $mail->sendContatoAdmin($emailAdmin, $nome, $email, $assunto, $mensagem);
                $enviadoUsuario = $mail->sendContatoConfirmacao($email, $nome, $assunto, $mensagem);

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