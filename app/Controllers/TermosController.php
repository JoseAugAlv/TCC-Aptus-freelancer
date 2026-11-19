<?php
// app/Controllers/TermosController.php

class TermosController
{
    public function index()
    {
        $tituloPagina = 'Termos de Uso e Política de Privacidade - Aptus';
        $cssPagina = 'termos.css';
        
        require '../app/Views/termos/index.php';
    }

    public function politicaAnuncios()
    {
        $tituloPagina = 'Política de Anúncios - Aptus';
        $cssPagina    = 'termos.css';
        require '../app/Views/termos/politica_anuncios.php';
    }
}
