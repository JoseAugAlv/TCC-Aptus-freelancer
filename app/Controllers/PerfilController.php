<?php
// app/Controllers/PerfilController.php

require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Helpers/DocumentoHelper.php';
require_once __DIR__ . '/../Helpers/RestricaoHelper.php';
require_once __DIR__ . '/../Controllers/AnuncioController.php';

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
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $usuarioData = $this->usuario->findById($_SESSION['usuario']['id']);
        if (!$usuarioData) { session_destroy(); header(('Location: ' . aptus_base() . '/login')); exit; }

        require_once __DIR__ . '/../Models/Interesse.php';
        $servicosConcluidos = (new Interesse())->contarConcluidosFreelancer((int) $usuarioData['id_usuario']);

        $tituloPagina = 'Meu Perfil - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/index.php';
    }

    public function editar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $usuarioData = $this->usuario->findById($_SESSION['usuario']['id']);
        if (!$usuarioData) { session_destroy(); header(('Location: ' . aptus_base() . '/login')); exit; }

        $tituloPagina = 'Editar Perfil - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/editar.php';
    }

    public function portfolio()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $tituloPagina = 'Meu Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/portfolio.php';
    }

    public function publico($id = null)
    {
        if (!$id) { header(('Location: ' . aptus_base() . '/')); exit; }

        $perfilData = $this->usuario->findById($id);
        if (!$perfilData) { header(('Location: ' . aptus_base() . '/')); exit; }

        // Todos os anúncios publicados da pessoa (primeiro lote; o resto via "Ver mais")
        $anuncioModel  = new Anuncio();
        $filtros       = ['freelancer' => (int) $id];
        $totalAnuncios = $anuncioModel->contarPublicos($filtros);
        $anuncios      = $anuncioModel->listarPublicos($filtros, AnuncioController::POR_PAGINA, 0);
        $temMais       = $totalAnuncios > count($anuncios);

        $localizacao   = self::localizacaoPublica($perfilData);

        require_once __DIR__ . '/../Models/Interesse.php';
        require_once __DIR__ . '/../Models/Avaliacao.php';
        $servicosConcluidos = (new Interesse())->contarConcluidosFreelancer((int) $id);
        $resumoCliente      = (new Avaliacao())->resumoCliente((int) $id);
        $euBloqueei    = false;
        $bloqueioAtivo = false;
        if (isset($_SESSION['usuario'])) {
            $meuId         = (int) $_SESSION['usuario']['id'];
            $euBloqueei    = RestricaoHelper::bloqueouUsuario($meuId, (int) $id);
            $bloqueioAtivo = RestricaoHelper::existeBloqueio($meuId, (int) $id);
        }

        $tituloPagina = $perfilData['nome'] . ' (@' . ($perfilData['username'] ?? '') . ') - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/publico.php';
    }

    public function atualizar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $id = (int) $_SESSION['usuario']['id'];

        $dados = [
            'nome'     => trim($_POST['nome'] ?? ''),
            'bio'      => trim($_POST['bio'] ?? ''),
            'cidade'   => trim($_POST['cidade'] ?? ''),
            'estado'   => trim($_POST['estado'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'cep'      => trim($_POST['cep'] ?? ''),
            'username'    => DocumentoHelper::normalizarUsername((string) ($_POST['username'] ?? '')),
            'logradouro'  => mb_substr(trim($_POST['logradouro'] ?? ''), 0, 150),
            'numero'      => mb_substr(trim($_POST['numero'] ?? ''), 0, 10),
            'complemento' => mb_substr(trim($_POST['complemento'] ?? ''), 0, 60),
            'bairro'      => mb_substr(trim($_POST['bairro'] ?? ''), 0, 100),
            'privacidade_localizacao' => in_array($_POST['privacidade_localizacao'] ?? '', ['oculto', 'cidade', 'cidade_bairro'], true)
                ? $_POST['privacidade_localizacao'] : 'oculto',
        ];
        $dados['estado'] = strtoupper(mb_substr($dados['estado'], 0, 2));

        if ($dados['nome'] === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'O nome é obrigatório.'];
            header(('Location: ' . aptus_base() . '/perfil/editar')); exit;
        }

        $usuarioAtual = $this->usuario->findById($id);
        if (!$usuarioAtual) { session_destroy(); header(('Location: ' . aptus_base() . '/login')); exit; }

        // Nome de usuário único (estilo @instagram)
        if (!DocumentoHelper::usernameValido($dados['username'])) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Nome de usuário inválido: use 3 a 30 letras minúsculas, números, ponto ou sublinhado.'];
            header(('Location: ' . aptus_base() . '/perfil/editar')); exit;
        }
        if ($this->usuario->usernameEmUso($dados['username'], $id)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Este nome de usuário já está em uso. Escolha outro.'];
            header(('Location: ' . aptus_base() . '/perfil/editar')); exit;
        }

        // CPF e nascimento: definidos uma vez (identidade verificada não muda pelo perfil)
        if (!DocumentoHelper::cpfValido((string) $usuarioAtual['cpf_cnpj']) && isset($_POST['cpf'])) {
            $cpf = DocumentoHelper::somenteDigitos((string) $_POST['cpf']);
            if ($cpf !== '') {
                if (!DocumentoHelper::cpfValido($cpf)) {
                    $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'CPF inválido. Confira os números digitados.'];
                    header(('Location: ' . aptus_base() . '/perfil/editar')); exit;
                }
                if ($this->usuario->cpfEmUso($cpf, $id)) {
                    $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Este CPF já está cadastrado em outra conta.'];
                    header(('Location: ' . aptus_base() . '/perfil/editar')); exit;
                }
                $dados['cpf_cnpj'] = $cpf;
            }
        }
        $nascAtual = (string) ($usuarioAtual['data_nascimento'] ?? '');
        if ((!$nascAtual || $nascAtual === '0000-00-00') && !empty($_POST['data_nascimento'])) {
            $nasc = (string) $_POST['data_nascimento'];
            if (!DocumentoHelper::dataNascimentoValida($nasc)) {
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Data de nascimento inválida.'];
                header(('Location: ' . aptus_base() . '/perfil/editar')); exit;
            }
            $dados['data_nascimento'] = $nasc;
        }

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
                header(('Location: ' . aptus_base() . '/perfil/editar')); exit;
            }
        }

        try {
            $ok = $this->usuario->update($id, $dados);

            if ($ok) {
                $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Perfil atualizado com sucesso!'];
            } else {
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao atualizar perfil.'];
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
                ? 'Algum dado informado (nome de usuário ou CPF) já está em uso por outra conta.'
                : 'Não foi possível atualizar. Tente novamente.';
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => $msg];
        }

        header(('Location: ' . aptus_base() . '/perfil')); exit;
    }

    // ==================================================================
    // Aba "Segurança" do editar perfil
    // ==================================================================

    /** Volta para a aba Segurança com uma mensagem. */
    private function voltarSeguranca(string $tipo, string $mensagem): void
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
        header('Location: ' . aptus_base() . '/perfil/editar?aba=seguranca');
        exit;
    }

    private function usuarioLogado(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $u = $this->usuario->findById((int) $_SESSION['usuario']['id']);
        if (!$u) { session_destroy(); header(('Location: ' . aptus_base() . '/login')); exit; }
        return $u;
    }

    /**
     * POST /perfil/email/codigo
     * Envia um código de 6 dígitos. Com "novo_email" preenchido, é uma troca de
     * e-mail (o e-mail só muda depois de confirmar o código); vazio, confirma o
     * e-mail atual.
     */
    public function enviarCodigoEmail()
    {
        $u  = $this->usuarioLogado();
        $id = (int) $u['id_usuario'];

        // Um código por minuto, para não usar o site como disparador de e-mails
        $ultimo = (int) ($_SESSION['codigo_email_enviado_em'] ?? 0);
        if ($ultimo > time() - 60) {
            $this->voltarSeguranca('aviso', 'Aguarde ' . (60 - (time() - $ultimo)) . ' segundos para pedir outro código.');
        }

        $novoEmail = strtolower(trim((string) ($_POST['novo_email'] ?? '')));
        $troca     = $novoEmail !== '' && $novoEmail !== strtolower((string) $u['email']);

        if ($troca) {
            if (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL) || strlen($novoEmail) > 100) {
                $this->voltarSeguranca('erro', 'Digite um e-mail válido.');
            }
            $existente = $this->usuario->findByEmail($novoEmail);
            if ($existente && (int) $existente['id_usuario'] !== $id) {
                $this->voltarSeguranca('erro', 'Este e-mail já está em uso por outra conta.');
            }
            // Conta com senha: confirma a senha atual antes de trocar o e-mail
            if (!password_verify((string) ($_POST['senha_atual'] ?? ''), (string) $u['senha'])) {
                $this->voltarSeguranca('erro', 'Senha atual incorreta.');
            }
        } elseif (!empty($u['email_verificado'])) {
            $this->voltarSeguranca('aviso', 'Seu e-mail já está verificado. Para trocar, informe um novo endereço.');
        }

        $destino = $troca ? $novoEmail : (string) $u['email'];
        $codigo  = $this->usuario->gerarCodigoEmail($id, $troca ? $novoEmail : null);

        require_once __DIR__ . '/../Core/Mailer.php';
        $enviado = (new Mailer())->sendCodigoEmail($destino, $u['nome'], $codigo, Usuario::CODIGO_MINUTOS, $troca);

        if (!$enviado) {
            $this->usuario->cancelarCodigoEmail($id);
            $this->voltarSeguranca('erro', 'Não foi possível enviar o e-mail agora. Tente novamente em alguns minutos.');
        }

        $_SESSION['codigo_email_enviado_em'] = time();
        $this->voltarSeguranca('sucesso', 'Enviamos um código de 6 dígitos para ' . $destino . '. Ele vale por ' . Usuario::CODIGO_MINUTOS . ' minutos.');
    }

    /** POST /perfil/email/confirmar */
    public function confirmarCodigoEmail()
    {
        $u  = $this->usuarioLogado();
        $id = (int) $u['id_usuario'];

        $r = $this->usuario->confirmarCodigoEmail($id, (string) ($_POST['codigo'] ?? ''));
        if (!$r['ok']) {
            $this->voltarSeguranca('erro', $r['erro']);
        }

        $_SESSION['usuario']['email'] = $r['email'];
        unset($_SESSION['codigo_email_enviado_em']);

        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        SecurityHelper::logAuditoria('email_confirmado', $id, 'E-mail confirmado por código: ' . $r['email'], 'info');

        $this->voltarSeguranca('sucesso', 'E-mail confirmado! Sua conta agora usa ' . $r['email'] . '.');
    }

    /** POST /perfil/email/cancelar */
    public function cancelarTrocaEmail()
    {
        $u = $this->usuarioLogado();
        $this->usuario->cancelarCodigoEmail((int) $u['id_usuario']);
        $this->voltarSeguranca('sucesso', 'Troca de e-mail cancelada.');
    }

    /** POST /perfil/senha */
    public function alterarSenha()
    {
        $u  = $this->usuarioLogado();
        $id = (int) $u['id_usuario'];

        $atual    = (string) ($_POST['senha_atual'] ?? '');
        $nova     = (string) ($_POST['nova_senha'] ?? '');
        $confirma = (string) ($_POST['nova_senha_confirm'] ?? '');

        if (!password_verify($atual, (string) $u['senha'])) {
            $this->voltarSeguranca('erro', 'Senha atual incorreta.');
        }
        if ($nova !== $confirma) {
            $this->voltarSeguranca('erro', 'A nova senha e a confirmação não são iguais.');
        }
        if (strlen($nova) < 8) {
            $this->voltarSeguranca('erro', 'A nova senha deve ter no mínimo 8 caracteres.');
        }
        require_once __DIR__ . '/../Helpers/SecurityHelper.php';
        $forca = SecurityHelper::validarForcaSenha($nova);
        if (!$forca['valida']) {
            $this->voltarSeguranca('erro', 'Senha fraca: ' . implode(', ', $forca['erros']));
        }
        if (password_verify($nova, (string) $u['senha'])) {
            $this->voltarSeguranca('aviso', 'A nova senha precisa ser diferente da atual.');
        }

        $this->usuario->alterarSenha($id, $nova);
        SecurityHelper::logAuditoria('senha_alterada', $id, 'Senha alterada pelo perfil', 'info');

        $this->voltarSeguranca('sucesso', 'Senha alterada com sucesso.');
    }

    /** POST /perfil/google/desvincular */
    public function desvincularGoogle()
    {
        $u = $this->usuarioLogado();
        $this->usuario->desvincularGoogle((int) $u['id_usuario']);
        $this->voltarSeguranca('sucesso', 'Conta Google desconectada. Para entrar, use seu e-mail e senha.');
    }


    /**
     * Localização exibida no perfil público conforme a escolha do usuário:
     * oculto (padrão), só a cidade, ou cidade e bairro. Rua e número nunca aparecem.
     */
    public static function localizacaoPublica(array $u): ?string
    {
        $pref = $u['privacidade_localizacao'] ?? 'oculto';
        if ($pref === 'oculto' || empty($u['cidade'])) return null;

        $cidade = $u['cidade'] . (!empty($u['estado']) ? '/' . $u['estado'] : '');
        if ($pref === 'cidade_bairro' && !empty($u['bairro'])) {
            return $u['bairro'] . ', ' . $cidade;
        }
        return $cidade;
    }

    /** POST /usuarios/bloquear */
    public function bloquear()
    {
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $meuId  = (int) $_SESSION['usuario']['id'];
        $alvoId = (int) UrlId::decode((string) ($_POST['usuario'] ?? ''));
        $voltar = $alvoId ? (aptus_base() . '/perfil/publico/') . url_id($alvoId) : (aptus_base() . '/');

        if ($alvoId <= 0 || $alvoId === $meuId || !$this->usuario->findById($alvoId)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Usuário inválido.'];
            header('Location: ' . $voltar); exit;
        }

        $stmt = Database::getConnection()->prepare(
            "INSERT IGNORE INTO usuario_bloqueio (id_bloqueador, id_bloqueado) VALUES (?, ?)"
        );
        $stmt->execute([$meuId, $alvoId]);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário bloqueado. Vocês não poderão contratar serviços um do outro nem trocar mensagens.'];
        header('Location: ' . $voltar); exit;
    }

    /** POST /usuarios/desbloquear */
    public function desbloquear()
    {
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $meuId  = (int) $_SESSION['usuario']['id'];
        $alvoId = (int) UrlId::decode((string) ($_POST['usuario'] ?? ''));

        $stmt = Database::getConnection()->prepare(
            "DELETE FROM usuario_bloqueio WHERE id_bloqueador = ? AND id_bloqueado = ?"
        );
        $stmt->execute([$meuId, $alvoId]);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Usuário desbloqueado.'];
        $voltar = ($_POST['voltar'] ?? '') === 'lista' ? (aptus_base() . '/perfil/bloqueados') : (aptus_base() . '/perfil/publico/') . url_id($alvoId);
        header('Location: ' . $voltar); exit;
    }

    /** GET /perfil/bloqueados */
    public function bloqueados()
    {
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $stmt = Database::getConnection()->prepare(
            "SELECT u.id_usuario, u.nome, u.username, u.foto_perfil, b.data_criacao
             FROM usuario_bloqueio b JOIN usuario u ON u.id_usuario = b.id_bloqueado
             WHERE b.id_bloqueador = ? ORDER BY b.data_criacao DESC"
        );
        $stmt->execute([(int) $_SESSION['usuario']['id']]);
        $bloqueados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tituloPagina = 'Usuários bloqueados - Aptus';
        $cssPagina    = 'perfil.css';
        require '../app/Views/perfil/bloqueados.php';
    }
}
