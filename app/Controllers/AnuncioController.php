<?php
// app/Controllers/AnuncioController.php

require_once __DIR__ . '/../Models/Anuncio.php';
require_once __DIR__ . '/../Models/Categoria.php';

class AnuncioController
{
    private $anuncio;
    private $categoria;

    public function __construct()
    {
        $this->anuncio = new Anuncio();
        $this->categoria = new Categoria();
    }

    public function index()
    {
        $tituloPagina = 'Serviços - Aptus';
        $cssPagina = 'anuncios.css';
        
        $anuncios = $this->anuncio->getAll();
        $totalAnuncios = count($anuncios);
        
        require '../app/Views/anuncios/index.php';
    }

    // app/Controllers/AnuncioController.php - No metodo show()

    public function show($slug)
    {
        $anuncio = $this->anuncio->findBySlug($slug);
        
        if (!$anuncio) {
            header('Location: /Aptus/anuncios');
            exit;
        }
        
        $this->anuncio->incrementarVisualizacao($anuncio['id_anuncio']);
        $fotos = $this->anuncio->getFotos($anuncio['id_anuncio']);
        
        $usuarioInteressado = false;
        $favoritado = false;
        $totalFavoritos = 0;
        
        if (isset($_SESSION['usuario'])) {
            $usuarioId = $_SESSION['usuario']['id'];
            
            // Verificar se o usuario ja tem interesse
            $usuarioInteressado = $this->anuncio->hasInteresse($anuncio['id_anuncio'], $usuarioId);
            
            // Verificar se o anuncio esta nos favoritos
            require_once __DIR__ . '/../Models/Favorito.php';
            $favorito = new Favorito();
            $favoritado = $favorito->existe($usuarioId, $anuncio['id_anuncio']);
            $totalFavoritos = $favorito->contarPorAnuncio($anuncio['id_anuncio']);
        }
        
        $tituloPagina = $anuncio['titulo'] . ' - Aptus';
        $cssPagina = 'anuncios.css';
        
        require '../app/Views/anuncios/show.php';
    }

    /**
     * Página para criar novo anúncio
     */
    public function criar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $categorias = $this->categoria->getAll();
        
        $tituloPagina = 'Criar Anúncio - Aptus';
        $cssPagina = 'anuncios.css';
        
        require '../app/Views/anuncios/criar.php';
    }

    /**
     * Salva novo anúncio
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

        $usuarioId = $_SESSION['usuario']['id'];
        
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoriaId = (int)($_POST['categoria_id'] ?? 0);
        $preco = (float)($_POST['preco'] ?? 0);

        if (empty($titulo) || empty($descricao) || $categoriaId <= 0 || $preco <= 0) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Preencha todos os campos corretamente.'
            ];
            header('Location: /Aptus/anuncios/criar');
            exit;
        }

        // Gerar slug
        $slug = $this->gerarSlug($titulo);

        $dados = [
            'id_usuario' => $usuarioId,
            'id_categoria' => $categoriaId,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'slug' => $slug,
            'preco' => $preco
        ];

        $id = $this->anuncio->create($dados);

        if ($id) {
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Anúncio criado! Aguarde a aprovação do moderador.'
            ];
            header('Location: /Aptus/anuncios/meus');
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao criar anúncio. Tente novamente.'
            ];
            header('Location: /Aptus/anuncios/criar');
        }
        exit;
    }

    /**
     * Página para editar anúncio
     */
    public function editar($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        if (!$id) {
            header('Location: /Aptus/anuncios/meus');
            exit;
        }

        $anuncio = $this->anuncio->findById($id);
        
        if (!$anuncio || $anuncio['id_usuario'] != $_SESSION['usuario']['id']) {
            header('Location: /Aptus/anuncios/meus');
            exit;
        }

        $categorias = $this->categoria->getAll();
        
        $tituloPagina = 'Editar Anúncio - Aptus';
        $cssPagina = 'anuncios.css';
        
        require '../app/Views/anuncios/editar.php';
    }

    /**
     * Atualiza anúncio existente
     */
    public function atualizar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];
        
        // Verificar se é dono do anúncio
        if (!$this->anuncio->isDono($id, $usuarioId)) {
            header('Location: /Aptus/anuncios/meus');
            exit;
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoriaId = (int)($_POST['categoria_id'] ?? 0);
        $preco = (float)($_POST['preco'] ?? 0);
        $situacao = $_POST['situacao'] ?? 'ativo';

        if (empty($titulo) || empty($descricao) || $categoriaId <= 0 || $preco <= 0) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Preencha todos os campos corretamente.'
            ];
            header('Location: /Aptus/anuncios/editar/' . $id);
            exit;
        }

        // Gerar slug se o título mudou
        $anuncio = $this->anuncio->findById($id);
        $slug = ($anuncio['titulo'] != $titulo) ? $this->gerarSlug($titulo) : $anuncio['slug'];

        $dados = [
            'id_categoria' => $categoriaId,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'slug' => $slug,
            'preco' => $preco,
            'situacao' => $situacao
        ];

        if ($this->anuncio->update($id, $dados)) {
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Anúncio atualizado com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao atualizar anúncio.'
            ];
        }

        header('Location: /Aptus/anuncios/meus');
        exit;
    }

    /**
     * Pausa um anúncio
     */
    public function pausar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if ($this->anuncio->isDono($id, $usuarioId)) {
            $this->anuncio->pausar($id);
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Anúncio pausado com sucesso.'
            ];
        }

        header('Location: /Aptus/anuncios/meus');
        exit;
    }

    /**
     * Ativa um anúncio pausado
     */
    public function ativar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $usuarioId = $_SESSION['usuario']['id'];

        if ($this->anuncio->isDono($id, $usuarioId)) {
            $this->anuncio->ativar($id);
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Anúncio ativado com sucesso.'
            ];
        }

        header('Location: /Aptus/anuncios/meus');
        exit;
    }

    /**
     * Exclui um anúncio
     */
    public function excluir($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        if (!$id) {
            header('Location: /Aptus/anuncios/meus');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];

        if ($this->anuncio->isDono($id, $usuarioId)) {
            $this->anuncio->delete($id);
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Anúncio excluído com sucesso.'
            ];
        }

        header('Location: /Aptus/anuncios/meus');
        exit;
    }

    /**
     * Lista meus anúncios (freelancer)
     */
    public function meus()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $anuncios = $this->anuncio->getByUsuario($usuarioId);
        
        $tituloPagina = 'Meus Anúncios - Aptus';
        $cssPagina = 'anuncios.css';
        
        require '../app/Views/anuncios/meus.php';
    }

    /**
     * Gera slug a partir do título
     */
    private function gerarSlug($titulo)
    {
        $slug = strtolower(trim($titulo));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug . '-' . time();
    }
}