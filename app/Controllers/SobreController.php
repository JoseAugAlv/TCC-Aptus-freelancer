<?php
// app/Controllers/SobreController.php

class SobreController
{
    public function index()
    {
        $tituloPagina = 'Sobre Nós - Aptus';
        $cssPagina = 'sobre.css';
        
        require '../app/Views/sobre/index.php';
    }
}