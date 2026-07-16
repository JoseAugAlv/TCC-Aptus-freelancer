<?php
require_once __DIR__ . '/../Models/Notificacao.php';

class BaseController
{
    protected function loadView($view, $dados = [])
    {
        // Prepara dados do nav
        if (isset($_SESSION['usuario'])) {
            $notificacao = new Notificacao();
            $dados['totalNotificacoes'] = $notificacao->contarNaoLidas($_SESSION['usuario']['id']);
        } else {
            $dados['totalNotificacoes'] = 0;
        }
        
        // Extrai as variáveis para a view
        extract($dados);
        
        require '../app/Views/' . $view . '.php';
    }
}