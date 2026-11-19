<?php
// app/Controllers/AnuncioController.php

require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Categoria.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';
require_once __DIR__ . '/../Helpers/MoneyHelper.php';
require_once __DIR__ . '/../Helpers/RestricaoHelper.php';


class AnuncioController
{
    /** Limite de clientes atendidos ao mesmo tempo em um anúncio (escolhido pelo prestador). */
    public const MAX_SIMULTANEOS_LIMITE = 20;

    public const POR_PAGINA = 12;
    public const MAX_FOTOS  = 8;

    private $anuncio;
    private $categoria;

    public function __construct()
    {
        $this->anuncio   = new Anuncio();
        $this->categoria = new Categoria();
    }

    /**
     * Lista anúncios (filtros ?categoria= e ?freelancer=) em lotes de 12.
     * O botão "Ver mais" pede ?pagina=N&parcial=1 e recebe só os cards.
     */
    public function index()
    {
        $tituloPagina = 'Serviços - Aptus';
        $cssPagina    = 'anuncios.css';

        $categoriaId  = UrlId::query('categoria');
        $freelancerId = UrlId::query('freelancer');
        $pagina       = max(1, (int) ($_GET['pagina'] ?? 1));

        $filtros       = ['categoria' => $categoriaId, 'freelancer' => $freelancerId];
        $totalAnuncios = $this->anuncio->contarPublicos($filtros);
        $anuncios      = $this->anuncio->listarPublicos($filtros, self::POR_PAGINA, ($pagina - 1) * self::POR_PAGINA);
        $temMais       = $pagina * self::POR_PAGINA < $totalAnuncios;

        if (!empty($_GET['parcial'])) {
            header('Content-Type: text/html; charset=UTF-8');
            header('X-Tem-Mais: ' . ($temMais ? '1' : '0'));
            require '../app/Views/anuncios/_cards.php';
            exit;
        }

        $freelancerInfo = null;
        if ($freelancerId > 0) {
            $stmt = Database::getConnection()->prepare('SELECT nome, username FROM usuario WHERE id_usuario = ?');
            $stmt->execute([$freelancerId]);
            $freelancerInfo = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $categorias = $this->categoria->getAll();
        $catAtual   = $categoriaId;
        $proximaUrl = (aptus_base() . '/anuncios?') . http_build_query(array_filter([
            'categoria'  => $categoriaId ? url_id($categoriaId) : null,
            'freelancer' => $freelancerId ? url_id($freelancerId) : null,
            'parcial'    => 1,
        ]));

        require '../app/Views/anuncios/index.php';
    }

    public function show($slug)
    {
        $anuncio = $this->anuncio->findBySlug($slug);
        $previewNaoPublicado = false;

        // Anúncio ainda não publicado: só o dono e a equipe (Admin/Moderador/Master) podem pré-visualizar
        if (!$anuncio && isset($_SESSION['usuario'])) {
            $stmt = Database::getConnection()->prepare(
                "SELECT id_anuncio, id_usuario FROM anuncio_servico WHERE slug = ? AND situacao != 'excluido' LIMIT 1"
            );
            $stmt->execute([$slug]);
            $rascunho = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($rascunho
                && ((int) $rascunho['id_usuario'] === (int) $_SESSION['usuario']['id']
                    || in_array((int) $_SESSION['usuario']['role'], [1, 2, 4], true))) {
                $anuncio = $this->anuncio->findById((int) $rascunho['id_anuncio']);
                $previewNaoPublicado = (bool) $anuncio;
            }
        }

        if (!$anuncio) { header(('Location: ' . aptus_base() . '/anuncios')); exit; }

        if (!$previewNaoPublicado) {
            $this->anuncio->incrementarVisualizacao($anuncio['id_anuncio']);
        }
        $fotos = $this->anuncio->getFotos($anuncio['id_anuncio']);

        require_once __DIR__ . '/../Models/Avaliacao.php';
        $avaliacaoModel    = new Avaliacao();
        $avaliacoesAnuncio = $avaliacaoModel->getDoAnuncio((int) $anuncio['id_anuncio']);
        $distribuicao      = $avaliacaoModel->distribuicaoAnuncio((int) $anuncio['id_anuncio']);

        $usuarioInteressado = false;
        $favoritado         = false;
        $totalFavoritos     = 0;
        $idInteresseAtual   = 0;
        $impedimento        = null;

        if (isset($_SESSION['usuario'])) {
            $usuarioId = $_SESSION['usuario']['id'];
            if ((int) $usuarioId !== (int) $anuncio['id_usuario']) {
                $impedimento = RestricaoHelper::existeBloqueio((int) $usuarioId, (int) $anuncio['id_usuario'])
                    ? 'Existe um bloqueio entre você e este prestador, por isso não é possível contratar.'
                    : RestricaoHelper::motivoImpedimento((int) $usuarioId, 'contratar');
            }
            $usuarioInteressado = $this->anuncio->hasInteresse($anuncio['id_anuncio'], $usuarioId);

            require_once __DIR__ . '/../Models/Favorito.php';
            $favorito       = new Favorito();
            $favoritado     = $favorito->existe($usuarioId, $anuncio['id_anuncio']);
            $totalFavoritos = $favorito->contarPorAnuncio($anuncio['id_anuncio']);

            // [FIX] Pega id do interesse existente para o link do chat
            if ($usuarioInteressado) {
                $pdo  = Database::getConnection();
                $stmt = $pdo->prepare(
                    "SELECT id_interesse FROM interesse
                     WHERE id_anuncio = ? AND id_contratante = ?
                       AND situacao IN ('pendente','ativo','concluido')
                     ORDER BY data_interesse DESC LIMIT 1"
                );
                $stmt->execute([$anuncio['id_anuncio'], $usuarioId]);
                $idInteresseAtual = (int) $stmt->fetchColumn();
            }
        }

        // Vagas simultâneas: quando todas estão ocupadas o botão de interesse muda
        require_once __DIR__ . '/../Models/Interesse.php';
        $limiteSimultaneos = max(1, (int) ($anuncio['max_simultaneos'] ?? 1));
        $ativosAgora       = (new Interesse())->contarAtivosNoAnuncio((int) $anuncio['id_anuncio']);
        $agendaCheia       = $ativosAgora >= $limiteSimultaneos;

        $tituloPagina = $anuncio['titulo'] . ' - Aptus';
        $cssPagina    = 'anuncios.css';
        require '../app/Views/anuncios/show.php';
    }

    public function criar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }
        RestricaoHelper::exigir((int) $_SESSION['usuario']['id'], 'anunciar', (aptus_base() . '/anuncios/meus'));

        $categorias   = $this->categoria->getAll();
        $tituloPagina = 'Criar Anúncio - Aptus';
        $cssPagina    = 'anuncios.css';
        require '../app/Views/anuncios/criar.php';
    }

    public function editar($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }
        if (!$id) { header(('Location: ' . aptus_base() . '/anuncios/meus')); exit; }

        $anuncio = $this->anuncio->findById($id);
        if (!$anuncio || $anuncio['id_usuario'] != $_SESSION['usuario']['id']) {
            header(('Location: ' . aptus_base() . '/anuncios/meus'));
            exit;
        }

        $fotos        = $this->anuncio->getFotos($id);
        $categorias   = $this->categoria->getAll();
        $tituloPagina = 'Editar Anúncio - Aptus';
        $cssPagina    = 'anuncios.css';
        require '../app/Views/anuncios/editar.php';
    }

    public function atualizar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $id        = (int) ($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if (!$this->anuncio->isDono($id, $usuarioId)) {
            header(('Location: ' . aptus_base() . '/anuncios/meus')); exit;
        }

        $titulo      = trim($_POST['titulo'] ?? '');
        $descricao   = trim($_POST['descricao'] ?? '');
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $preco = MoneyHelper::parse($_POST['preco'] ?? 0);
        $situacao    = $_POST['situacao'] ?? 'ativo';
        $simultaneos = self::lerSimultaneos();

        if (empty($titulo) || empty($descricao) || $categoriaId <= 0 || $preco <= 0) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos corretamente.'];
            header(('Location: ' . aptus_base() . '/anuncios/editar/') . url_id($id));
            exit;
        }

        $anuncio = $this->anuncio->findById($id);
        $slug    = ($anuncio['titulo'] != $titulo) ? $this->gerarSlug($titulo) : $anuncio['slug'];

        $dados = [
            'id_categoria' => $categoriaId,
            'titulo'       => $titulo,
            'descricao'    => $descricao,
            'slug'         => $slug,
            'preco'        => $preco,
            'max_simultaneos' => $simultaneos,
            'situacao'     => $situacao,
        ];

        // Fotos: remover as marcadas, enviar as novas e definir a capa
        $voltar = (aptus_base() . '/anuncios/editar/') . url_id($id);
        foreach ((array) ($_POST['remover_fotos'] ?? []) as $token) {
            $foto = $this->anuncio->findFoto((int) UrlId::decode((string) $token));
            if ($foto && (int) $foto['id_anuncio'] === $id) {
                $this->anuncio->removeFoto((int) $foto['id_anuncio_foto']);
                UploadHelper::remover($foto['arquivo']);
            }
        }

        $novas = $this->receberFotos($id, $voltar);

        $capa = $this->escolherCapa($id, (string) ($_POST['capa'] ?? ''), $novas);
        $dados['foto_capa'] = $capa ?? '';

        if ($this->anuncio->update($id, $dados)) {
            $msg = 'Anúncio atualizado com sucesso!';
            if ((int) $anuncio['id_situacao_moderacao'] === 3 && $this->anuncio->reenviarParaAnalise($id)) {
                $msg = 'Anúncio atualizado e reenviado para análise da moderação.';
            }
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => $msg];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao atualizar anúncio.'];
        }

        header(('Location: ' . aptus_base() . '/anuncios/meus'));
        exit;
    }

    public function pausar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $id        = (int) ($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if ($this->anuncio->isDono($id, $usuarioId)) {
            $this->anuncio->pausar($id);
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio pausado.'];
        }
        header(('Location: ' . aptus_base() . '/anuncios/meus'));
        exit;
    }

    public function ativar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $id        = (int) ($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if ($this->anuncio->isDono($id, $usuarioId)) {
            $this->anuncio->ativar($id);
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio ativado.'];
        }
        header(('Location: ' . aptus_base() . '/anuncios/meus'));
        exit;
    }

    public function excluir()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $id        = (int) ($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if ($id <= 0) { header(('Location: ' . aptus_base() . '/anuncios/meus')); exit; }

        if ($this->anuncio->isDono($id, $usuarioId)) {
            $this->anuncio->delete($id);
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio excluído.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Sem permissão.'];
        }
        header(('Location: ' . aptus_base() . '/anuncios/meus'));
        exit;
    }

    public function meus()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        $usuarioId = $_SESSION['usuario']['id'];
        $anuncios  = $this->anuncio->getByUsuario($usuarioId);

        $tituloPagina = 'Meus Anúncios - Aptus';
        $cssPagina    = 'anuncios.css';
        require '../app/Views/anuncios/meus.php';
    }

    private function gerarSlug($titulo)
    {
        $slug = trim($titulo);
        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
            if ($conv !== false) $slug = $conv;
        }
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        if ($slug === '') $slug = 'anuncio';
        return $slug . '-' . time();
    }

    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header(('Location: ' . aptus_base() . '/login')); exit; }

        require_once __DIR__ . '/../Helpers/MoneyHelper.php';

        $usuarioId   = $_SESSION['usuario']['id'];
        RestricaoHelper::exigir((int) $usuarioId, 'anunciar', (aptus_base() . '/anuncios/meus'));
        $titulo      = trim($_POST['titulo'] ?? '');
        $descricao   = trim($_POST['descricao'] ?? '');
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $preco       = MoneyHelper::parse($_POST['preco'] ?? 0);
        $simultaneos = self::lerSimultaneos();

        if (empty($titulo) || empty($descricao) || $categoriaId <= 0 || $preco <= 0) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Preencha todos os campos corretamente.'];
            header(('Location: ' . aptus_base() . '/anuncios/criar'));
            exit;
        }

        // Confere a quantidade de fotos antes de criar o anúncio
        if ($erroFotos = $this->validarLoteFotos(0)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => $erroFotos];
            header(('Location: ' . aptus_base() . '/anuncios/criar'));
            exit;
        }

        $slug  = $this->gerarSlug($titulo);
        $dados = [
            'id_usuario'   => $usuarioId,
            'id_categoria' => $categoriaId,
            'titulo'       => $titulo,
            'descricao'    => $descricao,
            'slug'         => $slug,
            'preco'        => $preco,
            'max_simultaneos' => $simultaneos,
            'foto_capa'    => null,
        ];

        $id = $this->anuncio->create($dados);

        if ($id) {
            $novas = $this->receberFotos((int) $id, (aptus_base() . '/anuncios/editar/') . url_id((int) $id));
            $capa  = $this->escolherCapa((int) $id, (string) ($_POST['capa'] ?? 'nova_0'), $novas);
            if ($capa) {
                $this->anuncio->definirCapa((int) $id, $capa);
            }

            // Aplica moderação automática se configurada
            try {
                require_once __DIR__ . '/../Models/Configuracao.php';
                $cfg = new Configuracao();
                if ($cfg->get('moderacao_automatica') === '1') {
                    $this->anuncio->aprovar((int) $id);
                    $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio criado e aprovado automaticamente!'];
                } else {
                    $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio criado! Aguarde a aprovação do moderador.'];
                }
            } catch (Throwable $e) {
                error_log('Erro na moderação automática: ' . $e->getMessage());
                $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Anúncio criado! Aguarde aprovação.'];
            }
            header(('Location: ' . aptus_base() . '/anuncios/meus'));
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao criar anúncio. Tente novamente.'];
            header(('Location: ' . aptus_base() . '/anuncios/criar'));
        }
        exit;
    }

    /** Quantidade de clientes atendidos ao mesmo tempo, vinda do formulário (1 a MAX_SIMULTANEOS_LIMITE). */
    private static function lerSimultaneos(): int
    {
        $n = (int) ($_POST['max_simultaneos'] ?? 1);
        return max(1, min(self::MAX_SIMULTANEOS_LIMITE, $n));
    }

    /** Arquivos enviados em fotos[] (ignora campos vazios). */
    private function arquivosEnviados(): array
    {
        $f = $_FILES['fotos'] ?? null;
        if (!$f || !is_array($f['name'])) return [];
        $lista = [];
        foreach ($f['name'] as $i => $nome) {
            if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || $nome === '') continue;
            $lista[$i] = [
                'name' => $nome, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i],
                'error' => $f['error'][$i], 'size' => $f['size'][$i],
            ];
        }
        return $lista;
    }

    private function validarLoteFotos(int $anuncioId): ?string
    {
        $qtd   = count($this->arquivosEnviados());
        $atual = $anuncioId > 0 ? $this->anuncio->contarFotos($anuncioId) : 0;
        if ($qtd + $atual > self::MAX_FOTOS) {
            return 'Cada anúncio pode ter no máximo ' . self::MAX_FOTOS . ' fotos.';
        }
        return null;
    }

    /**
     * Salva as fotos enviadas em anuncio_foto. Devolve [índice do input => arquivo].
     * Em caso de erro, redireciona para $voltar com a mensagem.
     */
    private function receberFotos(int $anuncioId, string $voltar): array
    {
        if ($erro = $this->validarLoteFotos($anuncioId)) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => $erro];
            header('Location: ' . $voltar);
            exit;
        }

        $ordem  = $this->anuncio->contarFotos($anuncioId);
        $salvas = [];
        foreach ($this->arquivosEnviados() as $i => $arquivo) {
            $r = UploadHelper::upload($arquivo, 'anuncio');
            if (!$r['success']) {
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Foto "' . $arquivo['name'] . '": ' . $r['message']];
                header('Location: ' . $voltar);
                exit;
            }
            $this->anuncio->addFoto($anuncioId, $r['arquivo'], $ordem++);
            $salvas[$i] = $r['arquivo'];
        }
        return $salvas;
    }

    /**
     * A capa é obrigatória sempre que houver fotos. $escolha: token de uma foto
     * já salva, ou "nova_N" (N = posição no campo de novas fotos). Escolha
     * inválida: mantém a capa atual ou usa a primeira foto.
     */
    private function escolherCapa(int $anuncioId, string $escolha, array $novas): ?string
    {
        $fotos = $this->anuncio->getFotos($anuncioId);
        if (!$fotos) return null;
        $arquivos = array_column($fotos, 'arquivo');

        if (preg_match('/^nova_(\d+)$/', $escolha, $m) && isset($novas[(int) $m[1]])) {
            return $novas[(int) $m[1]];
        }
        if ($escolha !== '' && ($id = UrlId::decode($escolha))) {
            foreach ($fotos as $f) {
                if ((int) $f['id_anuncio_foto'] === $id) return $f['arquivo'];
            }
        }

        $atual = $this->anuncio->findById($anuncioId)['foto_capa'] ?? '';
        if ($atual !== '' && in_array($atual, $arquivos, true)) return $atual;
        return $arquivos[0];
    }
}
