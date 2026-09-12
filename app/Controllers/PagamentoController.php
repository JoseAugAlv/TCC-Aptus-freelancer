<?php
require_once __DIR__ . '/../Helpers/SecurityHelper.php';
require_once __DIR__ . '/../Core/Auth.php';
class PagamentoController {
    public function index() { require_once __DIR__ . '/../Views/pagamentos/index.php'; }
    public function confirmar() { require_once __DIR__ . '/../Views/pagamentos/confirmar.php'; }
    public function confirmarContratante() { header('Location: /Aptus/pagamentos'); exit; }
    public function confirmarFreelancer() { header('Location: /Aptus/pagamentos'); exit; }
}
