<?php

require_once __DIR__ . '/../Models/Portfolio.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';

class PortfolioController
{
    private $portfolio;

    public function __construct()
    {
        $this->portfolio = new Portfolio();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioId = $_SESSION['usuario']['id'];
        $itens = $this->portfolio->getByUsuario($usuarioId);

        $tituloPagina = 'Meu Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/portfolio.php';
    }

    public function criar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $tituloPagina = 'Adicionar ao Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/portfolio_criar.php';
    }

    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if ($titulo === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'O título é obrigatório.'];
            header('Location: /Aptus/perfil/portfolio/criar');
            exit;
        }

        // [FIX-CRIT-2.2] Upload passa pelo helper
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $resultado = UploadHelper::upload($_FILES['imagem'], 'portfolio');
            if ($resultado['success']) {
                $imagem = $resultado['nome'];   // só o nome do arquivo
            } else {
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro no upload: ' . $resultado['message']];
                header('Location: /Aptus/perfil/portfolio/criar');
                exit;
            }
        }

        $dados = [
            'id_usuario' => $usuarioId,
            'titulo'     => $titulo,
            'descricao'  => $descricao,
            'imagem'     => $imagem,
        ];

        if ($this->portfolio->create($dados)) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Item adicionado ao portfólio com sucesso!'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao adicionar item. Tente novamente.'];
        }

        header('Location: /Aptus/perfil/portfolio');
        exit;
    }

    public function editar($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        if (!$id) { header('Location: /Aptus/perfil/portfolio'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $item = $this->portfolio->findById($id);

        if (!$item || (int) $item['id_usuario'] !== $usuarioId) {
            header('Location: /Aptus/perfil/portfolio');
            exit;
        }

        $tituloPagina = 'Editar Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        require '../app/Views/perfil/portfolio_editar.php';
    }

    public function atualizar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }

        $id = (int) ($_POST['id'] ?? 0);
        $usuarioId = (int) $_SESSION['usuario']['id'];
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        $item = $this->portfolio->findById($id);
        if (!$item || (int) $item['id_usuario'] !== $usuarioId) {
            header('Location: /Aptus/perfil/portfolio');
            exit;
        }

        if ($titulo === '') {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'O título é obrigatório.'];
            header('Location: /Aptus/perfil/portfolio/editar/' . $id);
            exit;
        }

        $dados = ['titulo' => $titulo, 'descricao' => $descricao];

        // [FIX-CRIT-2.2] Upload passa pelo helper
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $resultado = UploadHelper::upload($_FILES['imagem'], 'portfolio');
            if ($resultado['success']) {
                // Apaga a antiga (nome apenas, dentro de uploads/portfolio/)
                if (!empty($item['imagem'])) {
                    $antigo = $_SERVER['DOCUMENT_ROOT'] . '/Aptus/public/uploads/portfolio/' . basename($item['imagem']);
                    if (file_exists($antigo)) @unlink($antigo);
                }
                $dados['imagem'] = $resultado['nome'];
            } else {
                $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro no upload: ' . $resultado['message']];
                header('Location: /Aptus/perfil/portfolio/editar/' . $id);
                exit;
            }
        }

        if ($this->portfolio->update($id, $dados)) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Item atualizado com sucesso!'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao atualizar item. Tente novamente.'];
        }

        header('Location: /Aptus/perfil/portfolio');
        exit;
    }

    public function excluir($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) { header('Location: /Aptus/login'); exit; }
        if (!$id) { header('Location: /Aptus/perfil/portfolio'); exit; }

        $usuarioId = (int) $_SESSION['usuario']['id'];
        $item = $this->portfolio->findById($id);
        if (!$item || (int) $item['id_usuario'] !== $usuarioId) {
            header('Location: /Aptus/perfil/portfolio');
            exit;
        }

        if (!empty($item['imagem'])) {
            $caminho = $_SERVER['DOCUMENT_ROOT'] . '/Aptus/public/uploads/portfolio/' . basename($item['imagem']);
            if (file_exists($caminho)) @unlink($caminho);
        }

        if ($this->portfolio->delete($id)) {
            $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Item removido com sucesso!'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Erro ao remover item. Tente novamente.'];
        }

        header('Location: /Aptus/perfil/portfolio');
        exit;
    }
}