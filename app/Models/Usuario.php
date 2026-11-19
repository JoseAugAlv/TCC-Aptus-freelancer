<?php
// app/Models/Usuario.php

require_once __DIR__ . '/../Config/database.php';

class Usuario
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function findByEmail($email)
    {
        $sql  = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function usernameEmUso(string $username, int $excetoId = 0): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM usuario WHERE username = ? AND id_usuario <> ? LIMIT 1");
        $stmt->execute([$username, $excetoId]);
        return (bool) $stmt->fetchColumn();
    }

    public function cpfEmUso(string $cpf, int $excetoId = 0): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM usuario WHERE cpf_cnpj = ? AND id_usuario <> ? LIMIT 1");
        $stmt->execute([$cpf, $excetoId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Sugere um @username livre a partir do nome (ex.: "Ana Paula" -> ana.paula, ana.paula2...). */
    public function sugerirUsername(string $nome): string
    {
        $base = $nome;
        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
            if ($conv !== false) $base = $conv;
        }
        $base = trim(preg_replace('/[^a-z0-9]+/', '.', strtolower($base)), '.');
        $base = substr($base !== '' ? $base : 'usuario', 0, 24);
        if (strlen($base) < 3) $base = str_pad($base, 3, '0');
        $tentativa = $base;
        for ($i = 2; $this->usernameEmUso($tentativa); $i++) {
            $tentativa = $base . $i;
        }
        return $tentativa;
    }

    public function findById($id)
    {
        $sql = "SELECT u.*, p.perfil AS nome_perfil
                FROM usuario u
                LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
                WHERE u.id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByToken($token)
    {
        $sql  = "SELECT * FROM usuario WHERE token_verificacao = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalAtivos()
    {
        $sql  = "SELECT COUNT(*) AS total FROM usuario WHERE ativo = TRUE AND banido = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function create($data)
    {
        $sql = "INSERT INTO usuario
                (id_perfil, nome, username, email, senha, cpf_cnpj, data_nascimento,
                 cep, logradouro, numero, complemento, bairro, cidade, estado,
                 token_verificacao, token_verificacao_expira, google_id, google_email, email_verificado, data_verificacao, data_criacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $verificado = !empty($data['email_verificado']);
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['id_perfil'] ?? 3,
            $data['nome'],
            $data['username'] ?? $this->sugerirUsername($data['nome']),
            $data['email'],
            password_hash($data['senha'], PASSWORD_DEFAULT),
            $data['cpf_cnpj'] ?? null,
            $data['data_nascimento'] ?? null,
            $data['cep'] ?? null,
            $data['logradouro'] ?? null,
            $data['numero'] ?? null,
            $data['complemento'] ?? null,
            $data['bairro'] ?? null,
            $data['cidade'] ?? null,
            $data['estado'] ?? null,
            $data['token_verificacao'] ?? null,
            $data['token_verificacao_expira'] ?? null,
            $data['google_id'] ?? null,
            !empty($data['google_id']) ? ($data['google_email'] ?? $data['email']) : null,
            $verificado ? 1 : 0,
            $verificado ? date('Y-m-d H:i:s') : null,
        ]);
    }

    // ------------------------------------------------------------------
    // Login com Google
    // ------------------------------------------------------------------

    public function findByGoogleId(string $googleId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE google_id = ? LIMIT 1");
        $stmt->execute([$googleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Liga uma conta Google a uma conta do Aptus. Depois disso a pessoa pode
     * entrar só com o Google, mesmo que a conta Google use outro e-mail.
     *
     * $confirmaEmail = true quando o e-mail do Google é o MESMO do cadastro:
     * o Google já confirmou que o endereço é da pessoa, então a conta fica
     * verificada. Com e-mails diferentes, a verificação da conta não muda.
     */
    public function vincularGoogle(int $id, string $googleId, ?string $googleEmail = null, bool $confirmaEmail = false): bool
    {
        $extra = $confirmaEmail
            ? ", email_verificado = 1,
                 data_verificacao = COALESCE(data_verificacao, NOW()),
                 token_verificacao = NULL,
                 token_verificacao_expira = NULL"
            : '';
        $stmt = $this->conn->prepare(
            "UPDATE usuario SET google_id = ?, google_email = ? {$extra} WHERE id_usuario = ?"
        );
        return $stmt->execute([$googleId, $googleEmail, $id]);
    }

    /** Mantém atualizado o e-mail exibido da conta Google conectada. */
    public function atualizarGoogleEmail(int $id, string $googleEmail): void
    {
        $this->conn->prepare("UPDATE usuario SET google_email = ? WHERE id_usuario = ?")->execute([$googleEmail, $id]);
    }

    public function desvincularGoogle(int $id): bool
    {
        $stmt = $this->conn->prepare("UPDATE usuario SET google_id = NULL, google_email = NULL WHERE id_usuario = ?");
        return $stmt->execute([$id]);
    }

    // ------------------------------------------------------------------
    // Cadastros não confirmados
    // ------------------------------------------------------------------

    /**
     * Apaga cadastros que não confirmaram o e-mail no prazo (24h desde o
     * último link enviado), liberando CPF, e-mail e nome de usuário.
     * Só apaga contas sem nenhuma atividade no sistema.
     * Retorna quantas contas foram removidas.
     */
    public function limparCadastrosExpirados(): int
    {
        $ids = $this->conn->query(
            "SELECT u.id_usuario FROM usuario u
             WHERE u.email_verificado = 0
               AND u.token_verificacao_expira IS NOT NULL
               AND u.token_verificacao_expira < NOW()
               AND NOT EXISTS (SELECT 1 FROM anuncio_servico x WHERE x.id_usuario = u.id_usuario)
               AND NOT EXISTS (SELECT 1 FROM interesse x WHERE x.id_contratante = u.id_usuario OR x.id_freelancer = u.id_usuario)
             LIMIT 200"
        )->fetchAll(PDO::FETCH_COLUMN);

        $removidos = 0;
        foreach ($ids as $id) {
            try {
                $this->conn->beginTransaction();
                foreach (['reset_senha', 'notificacao', 'log_sistema', 'busca_log', 'usuario_habilidade'] as $tabela) {
                    $this->conn->prepare("DELETE FROM {$tabela} WHERE id_usuario = ?")->execute([$id]);
                }
                $this->conn->prepare("DELETE FROM usuario WHERE id_usuario = ? AND email_verificado = 0")->execute([$id]);
                $this->conn->commit();
                $removidos++;
            } catch (Throwable $e) {
                if ($this->conn->inTransaction()) $this->conn->rollBack();
                error_log("Limpeza de cadastro pendente {$id} falhou: " . $e->getMessage());
            }
        }
        return $removidos;
    }

    // ------------------------------------------------------------------
    // E-mail: verificação e troca por código de 6 dígitos
    // ------------------------------------------------------------------

    public const CODIGO_MINUTOS    = 15;
    public const CODIGO_TENTATIVAS = 5;

    /**
     * Gera um código de 6 dígitos. Se $novoEmail vier preenchido, ele fica como
     * "pendente" e o e-mail da conta só muda depois que o código for confirmado.
     * Com $novoEmail nulo, o código confirma o e-mail atual.
     * Retorna o código (para enviar por e-mail).
     */
    public function gerarCodigoEmail(int $id, ?string $novoEmail): string
    {
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $this->conn->prepare(
            "UPDATE usuario
             SET email_pendente = ?,
                 codigo_email_hash = ?,
                 codigo_email_expira = DATE_ADD(NOW(), INTERVAL " . self::CODIGO_MINUTOS . " MINUTE),
                 codigo_email_tentativas = 0
             WHERE id_usuario = ?"
        );
        $stmt->execute([$novoEmail, hash('sha256', $codigo), $id]);
        return $codigo;
    }

    /**
     * Confere o código. Retorna ['ok' => true, 'email' => e-mail confirmado]
     * ou ['ok' => false, 'erro' => mensagem].
     */
    public function confirmarCodigoEmail(int $id, string $codigo): array
    {
        $stmt = $this->conn->prepare(
            "SELECT email, email_pendente, codigo_email_hash, codigo_email_tentativas,
                    (codigo_email_expira IS NOT NULL AND codigo_email_expira > NOW()) AS valido
             FROM usuario WHERE id_usuario = ?"
        );
        $stmt->execute([$id]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$u || empty($u['codigo_email_hash'])) {
            return ['ok' => false, 'erro' => 'Nenhum código pendente. Peça um novo código.'];
        }
        if (!(int) $u['valido']) {
            $this->cancelarCodigoEmail($id);
            return ['ok' => false, 'erro' => 'O código expirou. Peça um novo código.'];
        }
        if ((int) $u['codigo_email_tentativas'] >= self::CODIGO_TENTATIVAS) {
            $this->cancelarCodigoEmail($id);
            return ['ok' => false, 'erro' => 'Muitas tentativas erradas. Peça um novo código.'];
        }

        $codigo = preg_replace('/\D/', '', $codigo);
        if (!hash_equals($u['codigo_email_hash'], hash('sha256', $codigo))) {
            $this->conn->prepare("UPDATE usuario SET codigo_email_tentativas = codigo_email_tentativas + 1 WHERE id_usuario = ?")
                       ->execute([$id]);
            $restantes = self::CODIGO_TENTATIVAS - (int) $u['codigo_email_tentativas'] - 1;
            return ['ok' => false, 'erro' => 'Código incorreto.' . ($restantes > 0 ? " Você ainda tem {$restantes} tentativa(s)." : ' Peça um novo código.')];
        }

        $novo = $u['email_pendente'] ?: $u['email'];
        if ($u['email_pendente']) {
            $dono = $this->findByEmail($u['email_pendente']);
            if ($dono && (int) $dono['id_usuario'] !== $id) {
                $this->cancelarCodigoEmail($id);
                return ['ok' => false, 'erro' => 'Este e-mail passou a ser usado por outra conta.'];
            }
        }

        $upd = $this->conn->prepare(
            "UPDATE usuario
             SET email = ?,
                 email_verificado = 1,
                 data_verificacao = NOW(),
                 token_verificacao = NULL,
                 token_verificacao_expira = NULL,
                 email_pendente = NULL,
                 codigo_email_hash = NULL,
                 codigo_email_expira = NULL,
                 codigo_email_tentativas = 0
             WHERE id_usuario = ?"
        );
        $upd->execute([$novo, $id]);
        return ['ok' => true, 'email' => $novo];
    }

    public function cancelarCodigoEmail(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE usuario
             SET email_pendente = NULL, codigo_email_hash = NULL,
                 codigo_email_expira = NULL, codigo_email_tentativas = 0
             WHERE id_usuario = ?"
        );
        return $stmt->execute([$id]);
    }

    /** Troca a senha e derruba os "lembrar de mim" de outros aparelhos. */
    public function alterarSenha(int $id, string $novaSenha): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE usuario SET senha = ?, remember_token = NULL, remember_token_expira = NULL WHERE id_usuario = ?"
        );
        return $stmt->execute([password_hash($novaSenha, PASSWORD_DEFAULT), $id]);
    }

    /**
     * FIX CRÍTICO: retorna rowCount > 0 para o controller saber se de fato verificou.
     */
    public function verificarEmail($token)
    {
        $sql = "UPDATE usuario
                SET email_verificado = 1,
                    data_verificacao = NOW(),
                    token_verificacao = NULL,
                    token_verificacao_expira = NULL
                WHERE token_verificacao = ?
                AND email_verificado = 0
                AND token_verificacao_expira IS NOT NULL
                AND token_verificacao_expira > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }
    public function reenviarTokenVerificacao($usuarioId, $novoToken)
    {
        $expiracao = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $sql = "UPDATE usuario
                SET token_verificacao = ?,
                    token_verificacao_expira = ?
                WHERE id_usuario = ? AND email_verificado = 0";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$novoToken, $expiracao, $usuarioId]);
    }

    public function findTokenVerificacao($token)
    {
        $sql = "SELECT id_usuario, nome, email, email_verificado, token_verificacao_expira
                FROM usuario
                WHERE token_verificacao = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [];

        $allowedFields = [
            'nome', 'email', 'telefone', 'whatsapp', 'cpf_cnpj',
            'data_nascimento', 'foto_perfil', 'bio', 'cidade', 'estado',
            'cep', 'username', 'logradouro', 'numero', 'complemento', 'bairro',
            'privacidade_localizacao',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (isset($data['senha']) && $data['senha'] !== '') {
            $fields[] = "senha = ?";
            $params[] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql  = "UPDATE usuario SET " . implode(", ", $fields) . " WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function salvarTokenReset($email, $token)
    {
        $usuario = $this->findByEmail($email);
        if (!$usuario) {
            return false;
        }

        $sql  = "UPDATE reset_senha SET usado = 1 WHERE id_usuario = ? AND usado = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuario['id_usuario']]);

        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $sql  = "INSERT INTO reset_senha (id_usuario, token, expiracao) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$usuario['id_usuario'], $token, $expiracao]);
    }

    public function findTokenReset($token)
    {
        $sql = "SELECT r.*, u.id_usuario, u.email, u.nome
                FROM reset_senha r
                JOIN usuario u ON r.id_usuario = u.id_usuario
                WHERE r.token = ? AND r.usado = 0 AND r.expiracao > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function redefinirSenha($token, $novaSenha)
    {
        $tokenData = $this->findTokenReset($token);
        if (!$tokenData) {
            return false;
        }

        $sql  = "UPDATE usuario SET senha = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            password_hash($novaSenha, PASSWORD_DEFAULT),
            $tokenData['id_usuario'],
        ]);

        $sql  = "UPDATE reset_senha SET usado = 1 WHERE token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);

        return true;
    }
}