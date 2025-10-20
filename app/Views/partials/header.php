<?php
// app/Views/partials/header.php
if (!defined('BASE_URL')) exit; 
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
    
    <style>
        /* Estilos adicionais para Cards e Tabelas no Dashboard */
        .card-kpi {
            background-color: #0d1f33; 
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            border-left: 5px solid #3498db;
            transition: transform 0.3s ease;
            height: 100%;
        }
        .card-kpi:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .card-kpi .kpi-value {
            font-size: 38px;
            font-weight: 700;
            color: #ffffff;
            margin: 5px 0;
        }
        .card-kpi .kpi-label {
            font-size: 14px;
            color: #bdc3c7;
            text-transform: uppercase;
        }
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card-chart {
            background-color: #0d1f33;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            height: 400px;
        }
        .grid-2-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 992px) {
            .grid-2-col {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <?php require_once('sidebar.php'); ?>
    <?php require_once('navbar.php'); ?>

    <div class="sidebar-overlay"></div>

    <main class="main-content">