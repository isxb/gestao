<?php
// app/Views/partials/sidebar.php
if (!defined('BASE_URL')) exit; 

// Obtém o nível de acesso do usuário logado
$userAccessLevel = $_SESSION['access_level'] ?? 'Colaborador';
?>

<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>dashboard" class="logo-sidebar">REFRAMAX</a>
        <p class="user-greeting-sidebar">Bem-vindo(a), <?= explode(' ', $_SESSION['user_name'])[0] ?>!</p>
    </div>

    <ul>
        <li>
            <a href="<?= BASE_URL ?>dashboard" class="<?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active-link' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>colaboradores" class="<?= strpos($_SERVER['REQUEST_URI'], 'colaboradores') !== false ? 'active-link' : '' ?>">
                <i class="fas fa-users"></i> Colaboradores
            </a>
        </li>
        
        <?php if ($userAccessLevel == 'Admin' || $userAccessLevel == 'RH' || $userAccessLevel == 'Gestor'): ?>
        <li>
            <a href="<?= BASE_URL ?>movimentacoes" class="<?= strpos($_SERVER['REQUEST_URI'], 'movimentacoes') !== false ? 'active-link' : '' ?>">
                <i class="fas fa-exchange-alt"></i> Transferências
            </a>
        </li>
        <?php endif; ?>

        <?php if ($userAccessLevel == 'Admin' || $userAccessLevel == 'RH'): ?>
        <li>
            <a href="<?= BASE_URL ?>usuarios" class="<?= strpos($_SERVER['REQUEST_URI'], 'usuarios') !== false ? 'active-link' : '' ?>">
                <i class="fas fa-user-shield"></i> Usuários
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>rh/aprovacao" class="<?= strpos($_SERVER['REQUEST_URI'], 'aprovacao') !== false ? 'active-link' : '' ?>">
                <i class="fas fa-check-double"></i> RH (Aprovações)
            </a>
        </li>
        <?php endif; ?>
        
        <?php if ($userAccessLevel == 'Admin' || $userAccessLevel == 'RH' || $userAccessLevel == 'Gestor'): ?>
        <li>
            <a href="<?= BASE_URL ?>relatorios" class="<?= strpos($_SERVER['REQUEST_URI'], 'relatorios') !== false ? 'active-link' : '' ?>">
                <i class="fas fa-chart-pie"></i> Relatórios
            </a>
        </li>
        <?php endif; ?>
        
        <li style="margin-top: 30px;">
            <a href="<?= BASE_URL ?>auth/logout" style="color: #e74c3c;">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </li>
    </ul>
</nav>