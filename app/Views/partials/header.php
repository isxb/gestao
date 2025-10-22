<?php
// app/Views/partials/header.php
if (!defined('BASE_URL')) exit; 

// FIX 1: Proteção contra inclusão recursiva (Adicionar no início do arquivo)
if (defined('HEADER_LOADED')) {
    // Isso deve parar a execução do script e evitar o loop de conteúdo
    die('Erro Fatal: Tentativa de inclusão recursiva do Header.');
}
define('HEADER_LOADED', true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reframax | Sistema Gestivo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="dashboard-page">
    <?php 
    // FIX 2: Usa o caminho absoluto (VIEW_PATH) para forçar o include correto
    // O arquivo sidebar.php e navbar.php estão na pasta 'partials'
    require_once(VIEW_PATH . 'partials/sidebar.php'); 
    require_once(VIEW_PATH . 'partials/navbar.php'); 
    ?>

    <div class="sidebar-overlay"></div>

    <main class="main-content">