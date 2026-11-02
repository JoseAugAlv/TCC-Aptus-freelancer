<?php
// app/Controllers/AdminUsuarioController.php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Moderador.php';

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

    /**
     * Listagem de usuários (faltava rota GET)
     */
    public function index()
    {
        $this->verificaPermissao();

        $model    = new Moderador();
        $usuarios = $model->getUsuarios();

        $tituloPagina = 'Usuários - Admin';
        $cssPagina    = 'usuarios.css';

        require '../app/Views/admin/usuarios/index.php';
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
                Database::clearCache();
                $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário atualizado.'];
            } catch (PDOException $e) {
                error_log('Erro ao atualizar usuário (admin): ' . $e->getMessage());
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível atualizar (e-mail duplicado?).'];
            }
        }

        // CORRIGIDO: volta pro /admin/usuarios (rota que agora existe)
        header('Location: /Aptus/admin/usuarios');
        exit;
    }

    public function excluir()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
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
            Database::clearCache();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário removido.'];
        } catch (PDOException $e) {
            error_log('Erro ao excluir usuário: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível excluir.'];
        }

        header('Location: /Aptus/admin/usuarios');
        exit;
    }

    public function banir()
    {
        $this->verificaPermissao();

        $id          = (int) ($_POST['id'] ?? 0);
        $motivo      = trim($_POST['motivo'] ?? '');
        $moderadorId = (int) $_SESSION['usuario']['id'];

        if ($id <= 0) { header('Location: /Aptus/moderator/usuarios'); exit; }

        if ($motivo === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Informe o motivo do banimento.'];
            header('Location: /Aptus/moderator/usuarios');
            exit;
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
            Database::clearCache();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário banido.'];
        } catch (PDOException $e) {
            error_log('Erro ao banir: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível banir.'];
        }

        header('Location: /Aptus/moderator/usuarios');
        exit;
    }

    public function desbanir()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $this->conn->prepare(
                    "UPDATE usuario
                     SET banido = 0, motivo_banimento = NULL, data_banimento = NULL,
                         id_moderador_banimento = NULL, ativo = 1
                     WHERE id_usuario = ?"
                );
                $stmt->execute([$id]);
                Database::clearCache();
                $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário desbanido.'];
            } catch (PDOException $e) {
                error_log('Erro ao desbanir: ' . $e->getMessage());
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível desbanir.'];
            }
        }

        header('Location: /Aptus/moderator/usuarios');
        exit;
    }
}