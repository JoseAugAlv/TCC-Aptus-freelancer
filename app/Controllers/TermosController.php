<?php
// app/Controllers/TermosController.php

class TermosController
{
    public function index()
    {
        $tituloPagina = 'Termos de Uso - RecycleWays';
        $cssPagina = 'termos.css';
        
        require '../app/Views/termos/termos.php';
    }
}