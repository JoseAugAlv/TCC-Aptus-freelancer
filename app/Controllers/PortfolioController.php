<?php
// app/Controllers/PortfolioController.php

require_once __DIR__ . '/../Models/Portfolio.php';

class PortfolioController
{
    private $portfolio;

    public function __construct()
    {
        $this->portfolio = new Portfolio();
    }

    /**
     * Lista todos os itens do portfólio do usuário logado
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: /Aptus/login');
            exit;
        }
        
        $usuarioId = $_SESSION['usuario']['id'];
        $itens = $this->portfolio->getByUsuario($usuarioId);
        
        $tituloPagina = 'Meu Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        
        require '../app/Views/perfil/portfolio.php';
    }

    /**
     * Exibe o formulário para criar um novo item
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
        
        $tituloPagina = 'Adicionar ao Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        
        require '../app/Views/perfil/portfolio_criar.php';
    }

    /**
     * Salva um novo item no portfólio
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
        
        // Validação
        if (empty($titulo)) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'O título é obrigatório.'
            ];
            header('Location: /Aptus/perfil/portfolio/criar');
            exit;
        }

        // Upload da imagem
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Aptus/public/uploads/portfolio/';
            
            // Criar diretório se não existir
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
            $caminhoCompleto = $uploadDir . $nomeArquivo;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
                $imagem = $nomeArquivo;
            }
        }

        $dados = [
            'id_usuario' => $usuarioId,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'imagem' => $imagem
        ];

        if ($this->portfolio->create($dados)) {
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Item adicionado ao portfólio com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao adicionar item. Tente novamente.'
            ];
        }

        header('Location: /Aptus/perfil/portfolio');
        exit;
    }

    /**
     * Exibe o formulário para editar um item
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
            header('Location: /Aptus/perfil/portfolio');
            exit;
        }
        
        $usuarioId = $_SESSION['usuario']['id'];
        $item = $this->portfolio->findById($id);
        
        // Verificar se o item pertence ao usuário
        if (!$item || $item['id_usuario'] != $usuarioId) {
            header('Location: /Aptus/perfil/portfolio');
            exit;
        }
        
        $tituloPagina = 'Editar Portfólio - Aptus';
        $cssPagina = 'perfil.css';
        
        require '../app/Views/perfil/portfolio_editar.php';
    }

    /**
     * Atualiza um item do portfólio
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
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        
        // Verificar se o item pertence ao usuário
        $item = $this->portfolio->findById($id);
        if (!$item || $item['id_usuario'] != $usuarioId) {
            header('Location: /Aptus/perfil/portfolio');
            exit;
        }

        // Validação
        if (empty($titulo)) {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'O título é obrigatório.'
            ];
            header('Location: /Aptus/perfil/portfolio/editar/' . $id);
            exit;
        }

        $dados = [
            'titulo' => $titulo,
            'descricao' => $descricao
        ];

        // Upload da nova imagem (se houver)
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Aptus/public/uploads/portfolio/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
            $caminhoCompleto = $uploadDir . $nomeArquivo;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
                // Remover imagem antiga
                if (!empty($item['imagem']) && file_exists($uploadDir . $item['imagem'])) {
                    unlink($uploadDir . $item['imagem']);
                }
                $dados['imagem'] = $nomeArquivo;
            }
        }

        if ($this->portfolio->update($id, $dados)) {
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Item atualizado com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao atualizar item. Tente novamente.'
            ];
        }

        header('Location: /Aptus/perfil/portfolio');
        exit;
    }

    /**
     * Remove um item do portfólio
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
            header('Location: /Aptus/perfil/portfolio');
            exit;
        }
        
        $usuarioId = $_SESSION['usuario']['id'];
        
        // Verificar se o item pertence ao usuário
        $item = $this->portfolio->findById($id);
        if (!$item || $item['id_usuario'] != $usuarioId) {
            header('Location: /Aptus/perfil/portfolio');
            exit;
        }

        // Remover arquivo de imagem
        if (!empty($item['imagem'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Aptus/public/uploads/portfolio/';
            if (file_exists($uploadDir . $item['imagem'])) {
                unlink($uploadDir . $item['imagem']);
            }
        }

        if ($this->portfolio->delete($id)) {
            $_SESSION['flash'] = [
                'tipo' => 'sucesso',
                'mensagem' => 'Item removido com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Erro ao remover item. Tente novamente.'
            ];
        }

        header('Location: /Aptus/perfil/portfolio');
        exit;
    }
}