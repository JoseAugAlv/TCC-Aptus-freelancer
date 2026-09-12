<?php
// app/Controllers/PerfilController.php

require_once __DIR__ . '/../Models/Usuario.php';

class PerfilController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioData = $this->usuario->findById($_SESSION['usuario']['id']);
        if (!$usuarioData) { session_destroy(); header('Location: /Aptus/login'); exit; }

        $tituloPagina = 'Meu Perfil - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/index.php';
    }

    public function editar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioData = $this->usuario->findById($_SESSION['usuario']['id']);
        if (!$usuarioData) { session_destroy(); header('Location: /Aptus/login'); exit; }

        $tituloPagina = 'Editar Perfil - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/editar.php';
    }

    public function portfolio()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $tituloPagina = 'Meu Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/portfolio.php';
    }

    public function publico($id = null)
    {
        if (!$id) { header('Location: /Aptus/'); exit; }

        $perfilData = $this->usuario->findById($id);
        if (!$perfilData) { header('Location: /Aptus/'); exit; }

        $tituloPagina = 'Perfil - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/publico.php';
    }

public function atualizar()
    {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

    $id = (int) $_SESSION['usuario']['id'];

    $dados = [
        'nome'     => trim($_POST['nome'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'bio'      => trim($_POST['bio'] ?? ''),
        'cidade'   => trim($_POST['cidade'] ?? ''),
        'estado'   => trim($_POST['estado'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
    ];

    if ($dados['nome'] === '' || $dados['email'] === '') {
        $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Nome e e-mail são obrigatórios.'];
        header('Location: /Aptus/perfil/editar'); exit;
    }

    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail inválido.'];
        header('Location: /Aptus/perfil/editar'); exit;
    }

    $usuarioAtual = $this->usuario->findById($id);
    if (!$usuarioAtual) { session_destroy(); header('Location: /Aptus/login'); exit; }

    $emailMudou = strtolower($dados['email']) !== strtolower($usuarioAtual['email']);

    // [FIX-ALTA-3.3] Verifica unicidade + envia e-mail de reverificação
    if ($emailMudou) {
        $existente = $this->usuario->findByEmail($dados['email']);
        if ($existente && (int) $existente['id_usuario'] !== $id) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Este e-mail já está em uso por outra conta.'];
            header('Location: /Aptus/perfil/editar'); exit;
        }
    }

    // Upload da foto
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/../Helpers/UploadHelper.php';
        $resultado = UploadHelper::upload($_FILES['foto_perfil'], 'perfil');
        if ($resultado['success']) {
            if (!empty($usuarioAtual['foto_perfil']) && $usuarioAtual['foto_perfil'] !== 'default.png') {
                UploadHelper::remover($usuarioAtual['foto_perfil']);
            }
            $dados['foto_perfil'] = $resultado['arquivo'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro no upload: ' . $resultado['message']];
            header('Location: /Aptus/perfil/editar'); exit;
        }
    }

    try {
        // [FIX-ALTA-3.5] Atualiza os dados "comuns" via whitelist pública
        $ok = $this->usuario->update($id, $dados);

        // [FIX-ALTA-3.5] Se o e-mail mudou, dispara método dedicado
        // que zera email_verificado + gera token + envia verificação.
        if ($ok && $emailMudou) {
            $novoToken = bin2hex(random_bytes(32));
            $this->usuario->trocarEmail($id, $dados['email'], $novoToken);

            require_once __DIR__ . '/../Core/Mailer.php';
            $mailer = new Mailer();
            $mailer->sendVerificationEmail($dados['email'], $dados['nome'], $novoToken);

            $_SESSION['flash'] = [
                'tipo'     => 'aviso',
                'mensagem' => 'Perfil atualizado! Enviamos um e-mail de verificação para ' . $dados['email'] . '. Confirme antes do próximo login.'
            ];
        } elseif ($ok) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Perfil atualizado com sucesso!'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao atualizar perfil. Tente novamente.'];
        }

        if ($ok) {
            $_SESSION['usuario']['nome'] = $dados['nome'];
            if (isset($dados['foto_perfil'])) {
                $_SESSION['usuario']['foto_perfil'] = $dados['foto_perfil'];
            }
        }
    } catch (PDOException $e) {
        error_log('Erro ao atualizar perfil: ' . $e->getMessage());
        $msg = (strpos($e->getMessage(), 'Duplicate') !== false)
            ? 'Este e-mail já está em uso por outra conta.'
            : 'Não foi possível atualizar o perfil. Tente novamente.';
        $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => $msg];
    }

    header('Location: /Aptus/perfil'); exit;
    }
}