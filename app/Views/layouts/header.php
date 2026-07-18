<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? 'Aptus - Conectando Talentos' ?></title>

    <link rel="shortcut icon" href="/Aptus/public/images/logo.ico" type="image/x-icon">

    <!-- CSS Base -->
    <link rel="stylesheet" href="/Aptus/public/css/style.css">
    <link rel="stylesheet" href="/Aptus/public/css/header.css">
    <link rel="stylesheet" href="/Aptus/public/css/nav.css">
    <link rel="stylesheet" href="/Aptus/public/css/footer.css">
    <link rel="stylesheet" href="/Aptus/public/css/responsive.css">
    
    <!-- CSS Específico da Página -->
    <?php if (isset($cssPagina)): ?>
        <link rel="stylesheet" href="/Aptus/public/css/<?= $cssPagina ?>">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>