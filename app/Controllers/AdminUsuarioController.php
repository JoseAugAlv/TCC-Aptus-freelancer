<?php
// app/Controllers/AdminUsuarioController.php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Moderador.php';
require_once __DIR__ . '/../Helpers/Role.php';
require_once __DIR__ . '/../Helpers/RestricaoHelper.php';

class AdminUsuarioController
{
    public const POR_PAGINA = 20;

    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    private function verificaPermissao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario']) || !in_array((int) $_SESSION['usuario']['role'], [1, 4], true)) {
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }
    }

    /**
     * Regra de hierarquia: ninguém age sobre a própria conta, e só se pode
     * gerenciar perfis abaixo do seu (Master > Admin > Moderador/Usuário).
     */
    public static function podeGerenciar(int $roleLogado, int $idLogado, int $idAlvo, int $roleAlvo): bool
    {
        if ($idAlvo === $idLogado) return false;
        if ($roleLogado === Role::SUPER_ADMIN) return $roleAlvo !== Role::SUPER_ADMIN;
        if ($roleLogado === Role::ADMIN) return in_array($roleAlvo, [Role::AUXILIAR, Role::USUARIO], true);
        return false;
    }

    /**
     * Carrega o usuário alvo e aplica a hierarquia; redireciona com erro se não puder.
     */
    private function alvoGerenciavel(int $id, string $voltar): array
    {
        $stmt = $this->conn->prepare("SELECT id_usuario, id_perfil FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$id]);
        $alvo = $stmt->fetch(PDO::FETCH_ASSOC);

        $roleLogado = (int) $_SESSION['usuario']['role'];
        $idLogado   = (int) $_SESSION['usuario']['id'];

        if (!$alvo || !self::podeGerenciar($roleLogado, $idLogado, (int) $alvo['id_usuario'], (int) $alvo['id_perfil'])) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Você não tem permissão para gerenciar este usuário.'];
            header('Location: ' . $voltar);
            exit;
        }
        return $alvo;
    }

    public function index()
    {
        $this->verificaPermissao();

        $busca  = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        [$usuarios, $totalUsuarios] = $this->listarUsuarios($busca, self::POR_PAGINA, ($pagina - 1) * self::POR_PAGINA);
        $temMais    = $pagina * self::POR_PAGINA < $totalUsuarios;
        $proximaUrl = (aptus_base() . '/admin/usuarios?') . http_build_query(array_filter(['q' => $busca, 'parcial' => 1]));

        if (!empty($_GET['parcial'])) {
            header('Content-Type: text/html; charset=UTF-8');
            header('X-Tem-Mais: ' . ($temMais ? '1' : '0'));
            require '../app/Views/admin/usuarios/_linhas.php';
            exit;
        }

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
            $this->alvoGerenciavel($id, (aptus_base() . '/admin/usuarios'));
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

        header(('Location: ' . aptus_base() . '/admin/usuarios'));
        exit;
    }

    /**
     * Altera o perfil (papel) de um usuário. Exclusivo do Master.
     */
    public function alterarPerfil()
    {
        $this->verificaPermissao();

        if ((int) $_SESSION['usuario']['role'] !== Role::SUPER_ADMIN) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Apenas o Master pode alterar perfis.'];
            header(('Location: ' . aptus_base() . '/admin/usuarios'));
            exit;
        }

        $id         = (int) ($_POST['id'] ?? 0);
        $novoPerfil = (int) ($_POST['id_perfil'] ?? 0);

        if ($id <= 0 || !in_array($novoPerfil, [Role::ADMIN, Role::AUXILIAR, Role::USUARIO], true)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Perfil inválido.'];
            header(('Location: ' . aptus_base() . '/admin/usuarios'));
            exit;
        }

        $this->alvoGerenciavel($id, (aptus_base() . '/admin/usuarios'));

        try {
            $stmt = $this->conn->prepare("UPDATE usuario SET id_perfil = ? WHERE id_usuario = ?");
            $stmt->execute([$novoPerfil, $id]);
            Database::clearCache();

            require_once __DIR__ . '/../Helpers/SecurityHelper.php';
            SecurityHelper::logAuditoria(
                'alterar_perfil_usuario',
                (int) $_SESSION['usuario']['id'],
                "Perfil do usuário #{$id} alterado para {$novoPerfil}",
                'info'
            );
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Perfil alterado com sucesso.'];
        } catch (PDOException $e) {
            error_log('Erro ao alterar perfil: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível alterar o perfil.'];
        }

        header(('Location: ' . aptus_base() . '/admin/usuarios'));
        exit;
    }

    public function excluir()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { header(('Location: ' . aptus_base() . '/admin/usuarios')); exit; }

        $this->alvoGerenciavel($id, (aptus_base() . '/admin/usuarios'));

        try {
            $stmt = $this->conn->prepare(
                "UPDATE usuario
                 SET ativo = 0,
                     email = CONCAT('deletado_', id_usuario, '@aptus.local'),
                     nome  = 'Usuário removido',
                     remember_token = NULL,
                     remember_token_expira = NULL
                 WHERE id_usuario = ?"
            );
            $stmt->execute([$id]);
            Database::clearCache();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário removido.'];
        } catch (PDOException $e) {
            error_log('Erro ao excluir usuário: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível excluir.'];
        }

        header(('Location: ' . aptus_base() . '/admin/usuarios'));
        exit;
    }

    public function banir()
    {
        $this->verificaPermissao();

        $id          = (int) ($_POST['id'] ?? 0);
        $motivo      = trim($_POST['motivo'] ?? '');
        $moderadorId = (int) $_SESSION['usuario']['id'];

        if ($id <= 0) { header(('Location: ' . aptus_base() . '/moderator/usuarios')); exit; }

        if ($motivo === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Informe o motivo do banimento.'];
            header(('Location: ' . aptus_base() . '/moderator/usuarios'));
            exit;
        }

        $this->alvoGerenciavel($id, (aptus_base() . '/moderator/usuarios'));

        try {
            $stmt = $this->conn->prepare(
                "UPDATE usuario
                 SET banido = 1,
                     motivo_banimento = ?,
                     data_banimento = NOW(),
                     id_moderador_banimento = ?,
                     remember_token = NULL,
                     remember_token_expira = NULL
                 WHERE id_usuario = ?"
            );
            $stmt->execute([mb_substr($motivo, 0, 255), $moderadorId, $id]);
            Database::clearCache();
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário banido.'];
        } catch (PDOException $e) {
            error_log('Erro ao banir: ' . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível banir.'];
        }

        header(('Location: ' . aptus_base() . '/moderator/usuarios'));
        exit;
    }

    public function desbanir()
    {
        $this->verificaPermissao();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->alvoGerenciavel($id, (aptus_base() . '/moderator/usuarios'));
            try {
                $stmt = $this->conn->prepare(
                    "UPDATE usuario
                     SET banido = 0, motivo_banimento = NULL, data_banimento = NULL,
                         id_moderador_banimento = NULL
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

        header(('Location: ' . aptus_base() . '/moderator/usuarios'));
        exit;
    }

    /** Lista paginada de usuários ativos, com busca por nome, @username ou e-mail. */
    private function listarUsuarios(string $busca, int $limite, int $offset): array
    {
        $where  = 'u.ativo = 1';
        $params = [];
        if ($busca !== '') {
            $like     = '%' . addcslashes($busca, '\%_') . '%';
            $where   .= ' AND (u.nome LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
            $params   = [$like, $like, '%' . addcslashes(ltrim($busca, '@'), '\%_') . '%'];
        }
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM usuario u WHERE {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $stmt = $this->conn->prepare(
            "SELECT u.*, p.perfil AS nome_perfil FROM usuario u
             LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
             WHERE {$where} ORDER BY u.data_criacao DESC, u.id_usuario DESC
             LIMIT " . max(1, $limite) . " OFFSET " . max(0, $offset)
        );
        $stmt->execute($params);
        return [$stmt->fetchAll(PDO::FETCH_ASSOC), $total];
    }

    /**
     * Suspensão por prazo: o usuário continua entrando, mas não anuncia nem
     * contrata, e seus anúncios saem do ar até o fim do prazo (voltam sozinhos).
     */
    public function suspender()
    {
        $this->verificaPermissao();

        $id     = (int) ($_POST['id'] ?? 0);
        $prazo  = (string) ($_POST['prazo'] ?? '');
        $motivo = trim((string) ($_POST['motivo'] ?? ''));
        $voltar = (aptus_base() . '/admin/usuarios');

        if ($id <= 0 || !isset(RestricaoHelper::PRAZOS_SUSPENSAO[$prazo])) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Escolha um prazo de suspensão válido.'];
            header('Location: ' . $voltar); exit;
        }
        if ($motivo === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Informe o motivo da suspensão.'];
            header('Location: ' . $voltar); exit;
        }

        $this->alvoGerenciavel($id, $voltar);

        [$intervalo, $rotulo] = RestricaoHelper::PRAZOS_SUSPENSAO[$prazo];
        $ate = date('Y-m-d H:i:s', strtotime($intervalo));

        $stmt = $this->conn->prepare(
            "UPDATE usuario SET suspenso_ate = ?, motivo_suspensao = ?, id_admin_suspensao = ? WHERE id_usuario = ?"
        );
        $stmt->execute([$ate, mb_substr($motivo, 0, 255), (int) $_SESSION['usuario']['id'], $id]);
        Database::clearCache();

        $this->notificar($id, 'Sua conta foi suspensa',
            'Sua conta está suspensa por ' . $rotulo . ' (até ' . date('d/m/Y H:i', strtotime($ate)) . '). Motivo: ' . $motivo
            . '. Durante a suspensão você pode acessar a plataforma, mas não pode anunciar nem contratar serviços. '
            . 'Seus anúncios ficam arquivados e voltam ao ar automaticamente ao fim do prazo.');

        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        SecurityHelper::logAuditoria('usuario_suspenso', $_SESSION['usuario']['id'], "Usuário {$id} por {$rotulo}: {$motivo}", 'warning');

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => "Usuário suspenso por {$rotulo}."];
        header('Location: ' . $voltar);
        exit;
    }

    public function removerSuspensao()
    {
        $this->verificaPermissao();

        $id     = (int) ($_POST['id'] ?? 0);
        $voltar = (aptus_base() . '/admin/usuarios');
        if ($id <= 0) { header('Location: ' . $voltar); exit; }

        $this->alvoGerenciavel($id, $voltar);

        $stmt = $this->conn->prepare(
            "UPDATE usuario SET suspenso_ate = NULL, motivo_suspensao = NULL, id_admin_suspensao = NULL WHERE id_usuario = ?"
        );
        $stmt->execute([$id]);
        Database::clearCache();

        $this->notificar($id, 'Suspensão encerrada', 'A suspensão da sua conta foi encerrada. Seus anúncios aprovados voltaram ao ar.');

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Suspensão removida.'];
        header('Location: ' . $voltar);
        exit;
    }

    private function notificar(int $usuarioId, string $titulo, string $mensagem): void
    {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
                 VALUES (?, 'conta', ?, ?, 'usuario', ?)"
            );
            $stmt->execute([$usuarioId, $titulo, $mensagem, $usuarioId]);
        } catch (Throwable $e) {
            error_log('Falha ao notificar usuário: ' . $e->getMessage());
        }
    }
}
