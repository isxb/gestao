<?php 
// app/Views/auth/login.php
// Garante que o BASE_URL está disponível
if (!defined('BASE_URL')) exit; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reframax - Portal de Acesso</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Adicione o CSS de cores Reframax aqui se não estiver no style.css */
        /* Cores que remetem à empresa Reframax (tons industriais: cinza, azul escuro e laranja) */
        /* O seu CSS já está usando um azul escuro de fundo (#0b1a37) e #3498db/FF6600 para destaque. */
    </style>
</head>
<body class="login-page">

    <div class="login-wrapper">
        
        <div class="shield-column">
            <div style="text-align: center;">
                <h2 style="color: white; font-size: 32px; font-weight: 700; margin-bottom: 5px;">REFRAMAX</h2>
                <p style="color: #FF6600; font-size: 14px; letter-spacing: 5px;">SISTEMA GESTIVO</p>
                </div>
            </div>

        <div class="form-column">
            <h1 style="color: #3498db;">Portal de Acesso</h1>
            <p class="welcome-text">
                Faça login utilizando as informações da sua conta corporativa.
            </p>
            
            <?php 
            // Exibe a mensagem de erro se houver
            if (!empty($error_message)): 
            ?>
                <div class="login-form-error-general">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php 
            endif; 
            ?>

            <form action="<?= BASE_URL ?>login" method="POST">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="seu.usuario@reframax.com.br" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="********" required>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Lembrar Senha</label>
                    </div>
                    <a href="#">Esqueceu minha senha?</a> 
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Iniciar
                    </button>
                    <button type="button" class="btn-secondary" onclick="alert('Entre em contato com o RH para cadastro de acesso.')">
                        Registrar-se
                    </button>
                </div>

            </form>
        </div>
    </div>

</body>
</html>