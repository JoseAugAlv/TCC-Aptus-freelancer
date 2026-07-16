<?php

class PaginationHelper
{
    /**
     * Calcula os dados da paginação
     */
    public static function paginate($total, $paginaAtual = 1, $limite = 20)
    {
        $totalPaginas = ceil($total / $limite);
        $paginaAtual = max(1, min($paginaAtual, $totalPaginas));
        $offset = ($paginaAtual - 1) * $limite;
        
        return [
            'total' => $total,
            'totalPaginas' => $totalPaginas,
            'paginaAtual' => $paginaAtual,
            'limite' => $limite,
            'offset' => $offset
        ];
    }

    /**
     * Gera os links de navegação
     */
    public static function render($baseUrl, $paginaAtual, $totalPaginas, $params = [])
    {
        if ($totalPaginas <= 1) {
            return '';
        }

        $html = '<div class="paginacao">';
        
        // Botão Anterior
        if ($paginaAtual > 1) {
            $url = self::buildUrl($baseUrl, $paginaAtual - 1, $params);
            $html .= '<a href="' . $url . '" class="pag-btn">Anterior</a>';
        }
        
        // Números das páginas
        $inicio = max(1, $paginaAtual - 2);
        $fim = min($totalPaginas, $paginaAtual + 2);
        
        if ($inicio > 1) {
            $html .= '<span>...</span>';
        }
        
        for ($i = $inicio; $i <= $fim; $i++) {
            $url = self::buildUrl($baseUrl, $i, $params);
            $class = ($i == $paginaAtual) ? 'pag-btn active' : 'pag-btn';
            $html .= '<a href="' . $url . '" class="' . $class . '">' . $i . '</a>';
        }
        
        if ($fim < $totalPaginas) {
            $html .= '<span>...</span>';
        }
        
        // Botão Próximo
        if ($paginaAtual < $totalPaginas) {
            $url = self::buildUrl($baseUrl, $paginaAtual + 1, $params);
            $html .= '<a href="' . $url . '" class="pag-btn">Próximo</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    private static function buildUrl($baseUrl, $pagina, $params = [])
    {
        $params['pagina'] = $pagina;
        return $baseUrl . '?' . http_build_query($params);
    }
}