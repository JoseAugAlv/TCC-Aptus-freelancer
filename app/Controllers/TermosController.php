<?php
// app/Controllers/TermosController.php

class TermosController
{
    public function index()
    {
        $tituloPagina = 'Termos de Uso e Politica de Privacidade - Aptus';
        $cssPagina = 'termos.css';
        
        require '../app/Views/termos/index.php';
    }
}