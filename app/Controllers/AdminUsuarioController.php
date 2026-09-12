<?php

require_once __DIR__ . '/../Config/database.php';

class AdminUsuarioController
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    private function verificaPermissao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int) $_SESSION['usuario']['role'], [1, 4], true)) {
            header('Location: /Aptus/login');
            exit;
        }
    }

    public function atualizar()
    {
        $this->verificaPermissao();

        $id    = (int) ($_POST['id'] ?? 0);
        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($id > 0 && $nome !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $stmt = $this->conn->prepare("UPDATE usuario SET nome = ?, email = ? WHERE id_usuario = ?");
                $stmt->execute([$nome, $email, $id]);
                $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário atualizado.'];
            } catch (PDOException $e) {
                error_log('Erro ao atualizar usuário (admin): ' . $e->getMessage());
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível atualizar (e-mail duplicado?).'];
            }
        }
        header('Location: /Aptus/admin/usuarios'); exit;
    }

    public function excluir()
    {
        $this->verificaPermissao();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) { header('Location: /Aptus/admin/usuarios'); exit; }

        try {
            $stmt = $this->conn->prepare(
                "UPDATE usuario
                 SET ativo = 0,
                     email = CONCAT('deletado_', id_usuario, '@aptus.local'),
                     nome  = 'Usuário removido',
                     remember_token = NULL
                 WHERE id_usuario = ?"
            );
            $stmt->execute([$id]);
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário removido com sucesso.'];
        } catch (PDOException $e) {
            error_log('Erro ao excluir usuário: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível excluir este usuário.'];
        }

        header('Location: /Aptus/admin/usuarios'); exit;
    }

    public function banir()
    {
        $this->verificaPermissao();

        $id          = (int) ($_POST['id'] ?? 0);
        $motivo      = trim($_POST['motivo'] ?? '');
        $moderadorId = (int) $_SESSION['usuario']['id'];

        if ($id <= 0) { header('Location: /Aptus/admin/usuarios'); exit; }

        if ($motivo === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Informe o motivo do banimento.'];
            header('Location: /Aptus/moderator/usuarios'); exit;
        }

        try {
            $stmt = $this->conn->prepare(
                "UPDATE usuario
                 SET banido = 1,
                     motivo_banimento = ?,
                     data_banimento = NOW(),
                     id_moderador_banimento = ?,
                     remember_token = NULL
                 WHERE id_usuario = ?"
            );
            $stmt->execute([$motivo, $moderadorId, $id]);
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário banido com sucesso.'];
        } catch (PDOException $e) {
            error_log('Erro ao banir usuário: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível banir este usuário.'];
        }

        header('Location: /Aptus/moderator/usuarios'); exit;
    }

    public function desbanir()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $this->conn->prepare(
                    "UPDATE usuario
                     SET banido = 0,
                         motivo_banimento = NULL,
                         data_banimento = NULL,
                         id_moderador_banimento = NULL,
                         ativo = 1
                     WHERE id_usuario = ?"
                );
                $stmt->execute([$id]);
                $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário desbanido.'];
            } catch (PDOException $e) {
                error_log('Erro ao desbanir usuário: ' . $e->getMessage());
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível desbanir este usuário.'];
            }
        }
        header('Location: /Aptus/moderator/usuarios'); exit;
    }
}