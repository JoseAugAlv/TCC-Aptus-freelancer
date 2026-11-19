<?php
// app/Controllers/DenunciaController.php

require_once __DIR__ . '/../Models/Denuncia.php';
require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';

class DenunciaController
{
    public const MAX_ANEXOS = 3;

    private $denuncia;
    private $anuncio;
    private $usuario;

    public function __construct()
    {
        $this->denuncia = new Denuncia();
        $this->anuncio = new Anuncio();
        $this->usuario = new Usuario();
    }

    /**
     * Pagina para criar uma denuncia
     * Rota: GET /denuncias/criar
     */
    public function criar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $tipo = $_GET['tipo'] ?? '';
        $id = UrlId::query('id');

        // Nome do alvo para o usuário conferir o que está denunciando
        $alvoNome = '';
        if ($tipo === 'anuncio' && $id > 0) {
            $alvo = $this->anuncio->findById($id);
            $alvoNome = $alvo['titulo'] ?? '';
        } elseif ($tipo === 'perfil' && $id > 0) {
            $alvo = $this->usuario->findById($id);
            $alvoNome = $alvo['nome'] ?? '';
        }

        if (!in_array($tipo, ['anuncio', 'perfil'], true) || $alvoNome === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Conteúdo para denúncia não encontrado.'];
            header('Location: ' . aptus_base() . '/');
            exit;
        }

        $motivos = $this->denuncia->getMotivos();

        require '../app/Views/denuncias/criar.php';
    }

    /**
     * Salva uma nova denuncia
     * Rota: POST /denuncias/salvar
     */
    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $tipo = $_POST['tipo'] ?? '';
        // O formulário envia o ID cifrado (url_id); IDs numéricos crus não são aceitos.
        $id = (int) (UrlId::decode((string) ($_POST['id'] ?? '')) ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        // Validacoes
        if (empty($tipo) || $id <= 0 || empty($motivo)) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Preencha todos os campos obrigatórios.'
            ];
            header('Location: ' . aptus_base() . ($id > 0
                ? '/denuncias/criar?tipo=' . urlencode($tipo) . '&id=' . url_id($id)
                : '/'));
            exit;
        }

        // Buscar dados do alvo
        $denunciadoId = 0;
        $anuncioId = null;

        if ($tipo === 'anuncio') {
            $anuncio = $this->anuncio->findById($id);
            if (!$anuncio) {
                $_SESSION['flash'] = [
                    'tipo' => 'erro',
                    'mensagem' => 'Anúncio não encontrado.'
                ];
                header(('Location: ' . aptus_base() . '/anuncios'));
                exit;
            }
            $denunciadoId = $anuncio['id_usuario'];
            $anuncioId = $id;

            // Nao pode denunciar o proprio anuncio
            if ($denunciadoId == $usuarioId) {
                $_SESSION['flash'] = [
                    'tipo' => 'erro',
                    'mensagem' => 'Você não pode denunciar seu próprio anúncio.'
                ];
                header(('Location: ' . aptus_base() . '/anuncios/') . $anuncio['slug']);
                exit;
            }
        } elseif ($tipo === 'perfil') {
            $usuario = $this->usuario->findById($id);
            if (!$usuario) {
                $_SESSION['flash'] = [
                    'tipo' => 'erro',
                    'mensagem' => 'Usuário não encontrado.'
                ];
                header(('Location: ' . aptus_base() . '/'));
                exit;
            }
            $denunciadoId = $id;

            // Nao pode denunciar o proprio perfil
            if ($denunciadoId == $usuarioId) {
                $_SESSION['flash'] = [
                    'tipo' => 'erro',
                    'mensagem' => 'Você não pode denunciar seu próprio perfil.'
                ];
                header(('Location: ' . aptus_base() . '/perfil'));
                exit;
            }
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Tipo de denúncia inválido.'
            ];
            header(('Location: ' . aptus_base() . '/'));
            exit;
        }

        // Verificar se ja denunciou
        if ($this->denuncia->jaDenunciou($usuarioId, $denunciadoId, $anuncioId)) {
            $_SESSION['flash'] = [
                'tipo' => 'aviso',
                'mensagem' => 'Você já denunciou este conteúdo. Aguarde a análise do moderador.'
            ];
            header(('Location: ' . aptus_base() . '/denuncias'));
            exit;
        }

        $anexos = $this->anexosEnviados();
        if (count($anexos) > self::MAX_ANEXOS) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Envie no máximo ' . self::MAX_ANEXOS . ' anexos.'];
            header(('Location: ' . aptus_base() . '/denuncias/criar?tipo=') . urlencode($tipo) . '&id=' . url_id($id));
            exit;
        }

        // Criar denuncia
        $dados = [
            'id_denunciante' => $usuarioId,
            'id_denunciado' => $denunciadoId,
            'id_anuncio' => $anuncioId,
            'motivo' => $motivo,
            'descricao' => $descricao
        ];

        $resultado = $this->denuncia->create($dados);

        if ($resultado) {
            $falhas = [];
            foreach ($anexos as $arquivo) {
                $r = UploadHelper::upload($arquivo, 'denuncia');
                if ($r['success']) {
                    $this->denuncia->addAnexo((int) $resultado, $r['arquivo']);
                } else {
                    $falhas[] = $arquivo['name'] . ' (' . $r['message'] . ')';
                }
            }
            $_SESSION['flash'] = [
                'tipo' => $falhas ? 'aviso' : 'sucesso',
                'mensagem' => 'Denúncia enviada! Um moderador irá analisar. Acompanhe em Minhas denúncias.'
                    . ($falhas ? ' Anexos não enviados: ' . implode('; ', $falhas) : ''),
            ];
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao enviar denúncia. Tente novamente.'
            ];
        }

        // Redirecionar de volta
        if ($tipo === 'anuncio') {
            header(('Location: ' . aptus_base() . '/anuncios/') . $anuncio['slug']);
        } else {
            header(('Location: ' . aptus_base() . '/perfil/publico/') . url_id($denunciadoId));
        }
        exit;
    }

    /**
     * Lista denuncias (moderador) — histórico completo com filtros
     * Rota: GET /moderator/denuncias
     */
    public function listar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4])) {
            header(('Location: ' . aptus_base() . '/'));
            exit;
        }

        $denuncias = $this->denuncia->listarHistorico($_GET);
        $totalPendentes = $this->denuncia->countPendentes();

        require '../app/Views/moderator/denuncias.php';
    }

    /**
     * Visualiza uma denuncia (moderador)
     * Rota: GET /moderator/denuncias/visualizar/{id}
     */
    public function visualizar($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4])) {
            header(('Location: ' . aptus_base() . '/'));
            exit;
        }

        // Tentar pegar ID de diferentes formas
        if (!$id) {
            $id = UrlId::query('id');
        }

        if ($id <= 0) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'ID da denúncia inválido.'
            ];
            header(('Location: ' . aptus_base() . '/moderator/denuncias'));
            exit;
        }

        $denuncia = $this->denuncia->findById($id);

        if (!$denuncia) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Denúncia não encontrada.'
            ];
            header(('Location: ' . aptus_base() . '/moderator/denuncias'));
            exit;
        }

        $motivos = $this->denuncia->getMotivos();
        $anexos  = $this->denuncia->getAnexos((int) $id);

        // CORREÇÃO: caminho correto para a pasta denuncias
        require '../app/Views/denuncias/visualizar.php';
    }

    /**
     * Aprova uma denuncia (moderador)
     * Rota: POST /moderator/denuncias/aprovar
     */
    public function aprovar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4])) {
            header(('Location: ' . aptus_base() . '/'));
            exit;
        }

        // O formulário envia o ID cifrado (url_id); IDs numéricos crus não são aceitos.
        $id = (int) (UrlId::decode((string) ($_POST['id'] ?? '')) ?? 0);
        $moderadorId = $_SESSION['usuario']['id'];

        if ($id <= 0) {
            header(('Location: ' . aptus_base() . '/moderator/denuncias'));
            exit;
        }

        $ok = $this->denuncia->aprovar($id, $moderadorId);

        $_SESSION['flash'] = $ok
            ? ['tipo' => 'sucesso', 'mensagem' => 'Denúncia aprovada.']
            : ['tipo' => 'aviso', 'mensagem' => 'Esta denúncia já foi analisada.'];

        header(('Location: ' . aptus_base() . '/moderator/denuncias'));
        exit;
    }

    /**
     * Rejeita uma denuncia (moderador)
     * Rota: POST /moderator/denuncias/rejeitar
     */
    public function rejeitar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header(('Location: ' . aptus_base() . '/login'));
            exit;
        }

        $role = (int) $_SESSION['usuario']['role'];
        if (!in_array($role, [1, 2, 4])) {
            header(('Location: ' . aptus_base() . '/'));
            exit;
        }

        // O formulário envia o ID cifrado (url_id); IDs numéricos crus não são aceitos.
        $id = (int) (UrlId::decode((string) ($_POST['id'] ?? '')) ?? 0);
        $moderadorId = $_SESSION['usuario']['id'];

        if ($id <= 0) {
            header(('Location: ' . aptus_base() . '/moderator/denuncias'));
            exit;
        }

        $ok = $this->denuncia->rejeitar($id, $moderadorId);

        $_SESSION['flash'] = $ok
            ? ['tipo' => 'sucesso', 'mensagem' => 'Denúncia rejeitada.']
            : ['tipo' => 'aviso', 'mensagem' => 'Esta denúncia já foi analisada.'];

        header(('Location: ' . aptus_base() . '/moderator/denuncias'));
        exit;
    }

    /**
     * Histórico de denúncias feitas pelo usuário logado.
     * Rota: GET /denuncias
     */
    public function minhas()
    {
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $denuncias    = $this->denuncia->getByDenunciante((int) $_SESSION['usuario']['id']);
        $motivos      = $this->denuncia->getMotivos();
        $tituloPagina = 'Minhas denúncias - Aptus';
        $cssPagina    = 'denuncias.css';
        require '../app/Views/denuncias/minhas.php';
    }

    /**
     * Entrega um anexo só para a equipe de moderação ou para quem denunciou.
     * Rota: GET /denuncias/anexo/{id}
     */
    public function anexo($id = null)
    {
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $anexo = $this->denuncia->findAnexo((int) $id);
        $uid   = (int) $_SESSION['usuario']['id'];
        $staff = in_array((int) $_SESSION['usuario']['role'], [1, 2, 4], true);

        if (!$anexo || (!$staff && (int) $anexo['id_denunciante'] !== $uid)) {
            http_response_code(404);
            exit('Anexo não encontrado.');
        }

        $base    = realpath(__DIR__ . '/../../public/uploads/denuncia');
        $caminho = realpath(__DIR__ . '/../../public/' . ltrim($anexo['arquivo'], '/'));
        if ($base === false || $caminho === false || strpos($caminho, $base) !== 0 || !is_file($caminho)) {
            http_response_code(404);
            exit('Arquivo indisponível.');
        }

        $mime = function_exists('mime_content_type') ? (mime_content_type($caminho) ?: 'application/octet-stream') : 'application/octet-stream';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)) {
            $mime = 'application/octet-stream';
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($caminho));
        header('Content-Disposition: inline; filename="anexo-' . (int) $anexo['id_anexo'] . '.' . pathinfo($caminho, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=0, no-store');
        readfile($caminho);
        exit;
    }

    /** Arquivos enviados em anexos[] (ignora campos vazios). */
    private function anexosEnviados(): array
    {
        $f = $_FILES['anexos'] ?? null;
        if (!$f || !is_array($f['name'])) return [];
        $lista = [];
        foreach ($f['name'] as $i => $nome) {
            if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || $nome === '') continue;
            $lista[] = [
                'name' => $nome, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i],
                'error' => $f['error'][$i], 'size' => $f['size'][$i],
            ];
        }
        return $lista;
    }
}
