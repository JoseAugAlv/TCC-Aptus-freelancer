<?php
// app/Controllers/ConfiguracaoController.php

require_once __DIR__ . '/../Models/Configuracao.php';

class ConfiguracaoController
{
    private $config;

    public function __construct()
    {
        $this->config = new Configuracao();
    }

    /**
     * Pagina de configuracoes
     * Rota: GET /admin/configuracoes
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 4])) {
            header('Location: /Aptus/');
            exit;
        }

        // Inicializar configuracoes padrao
        $this->config->initDefaults();
        
        $configs = $this->config->getAll();
        
        $tituloPagina = 'Configuracoes - Aptus';
        $cssPagina = 'admin.css';
        
        require '../app/Views/admin/configuracoes.php';
    }

    /**
     * Salva configuracoes
     * Rota: POST /admin/configuracoes/salvar
     */
    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 4])) {
            header('Location: /Aptus/');
            exit;
        }

        // Coletar dados do formulario
        $configs = [
            'site_nome' => trim($_POST['site_nome'] ?? 'Aptus'),
            'site_descricao' => trim($_POST['site_descricao'] ?? ''),
            'site_email' => trim($_POST['site_email'] ?? ''),
            'site_telefone' => trim($_POST['site_telefone'] ?? ''),
            'site_endereco' => trim($_POST['site_endereco'] ?? ''),
            
            'upload_max_size' => trim($_POST['upload_max_size'] ?? '5'),
            'upload_allow_types' => trim($_POST['upload_allow_types'] ?? 'jpg,jpeg,png,webp,gif'),
            
            'moderacao_automatica' => isset($_POST['moderacao_automatica']) ? '1' : '0',
            
            'sessao_tempo' => trim($_POST['sessao_tempo'] ?? '3600'),
            'tentativas_login' => trim($_POST['tentativas_login'] ?? '5'),
            
            'email_host' => trim($_POST['email_host'] ?? ''),
            'email_port' => trim($_POST['email_port'] ?? '587'),
            'email_user' => trim($_POST['email_user'] ?? ''),
            'email_pass' => trim($_POST['email_pass'] ?? ''),
            'email_from_name' => trim($_POST['email_from_name'] ?? 'Aptus'),
            
            'pagamento_pix' => isset($_POST['pagamento_pix']) ? '1' : '0',
            'pagamento_transferencia' => isset($_POST['pagamento_transferencia']) ? '1' : '0',
            'pagamento_dinheiro' => isset($_POST['pagamento_dinheiro']) ? '1' : '0',
            'pagamento_cartao' => isset($_POST['pagamento_cartao']) ? '1' : '0',
            
            'manutencao' => isset($_POST['manutencao']) ? '1' : '0',
            'manutencao_mensagem' => trim($_POST['manutencao_mensagem'] ?? '')
        ];

        // Validar email
        if (!empty($configs['site_email']) && !filter_var($configs['site_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Email invalido.'];
            header('Location: /Aptus/admin/configuracoes');
            exit;
        }

        // Salvar
        $resultado = $this->config->setMultiples($configs);

        if ($resultado) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Configuracoes salvas com sucesso!'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao salvar configuracoes.'];
        }

        header('Location: /Aptus/admin/configuracoes');
        exit;
    }
}