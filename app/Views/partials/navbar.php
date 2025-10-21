<?php
// app/Views/partials/navbar.php
if (!defined('BASE_URL')) exit; 
?>

<header class="navbar-dashboard">
    <div class="nav-left">
        <i id="menu-toggle" class="fas fa-bars menu-toggle"></i>
        <a href="<?= BASE_URL ?>dashboard" class="logo-main">
            <img src="<?= BASE_URL ?>images/logo_reframax.png" alt="Reframax Logo" class="logo-image-navbar">
        </a>
    </div>

    <div class="nav-links-main">
        <span class="user-info-nav">
            <i class="fas fa-user-circle"></i> 
            <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= htmlspecialchars($_SESSION['access_level']) ?>)
        </span>
        <div style="position: relative;">
            <input type="text" placeholder="Buscar..." style="padding: 6px 10px; border-radius: 4px; border: 1px solid #2a528a; background-color: rgba(255,255,255,0.1); color: white; width: 150px;">
            <i class="fas fa-search" style="position: absolute; right: 10px; top: 8px; color: #bdc3c7; font-size: 12px;"></i>
        </div>
        <a href="<?= BASE_URL ?>auth/logout" title="Sair do Sistema">
            <i class="fas fa-power-off"></i>
        </a>
    </div>
</header>