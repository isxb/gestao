<?php 
// app/Views/setup/index.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reframax - Instalação do Sistema</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>
<body class="login-page">
    <div class="form-page-wrapper" style="max-width: 600px;">
        <h1><span style="color: #3498db;">Reframax</span> - Configuração Inicial</h1>
        <p class="form-instructions">
            Bem-vindo! Para prosseguir, insira as credenciais do banco de dados (que já deve estar criado) e crie sua conta de Administrador mestre.
        </p>
        
        <?php if (!empty($error_message)): ?>
            <div class="message-feedback error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>setup/run" method="POST">
            
            <fieldset style="border: 1px solid #3498db; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <legend style="color: #3498db; font-weight: 600; padding: 0 10px;">Acesso ao Banco de Dados</legend>
                
                <div class="form-group">
                    <label for="db_host">Host do Banco</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label for="db_name">Nome do Banco (Ex: junio413_gestao)</label>
                    <input type="text" id="db_name" name="db_name" required>
                </div>
                <div class="form-group">
                    <label for="db_user">Usuário do Banco</label>
                    <input type="text" id="db_user" name="db_user" required>
                </div>
                <div class="form-group">
                    <label for="db_pass">Senha do Banco</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
            </fieldset>

            <fieldset style="border: 1px solid #3498db; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <legend style="color: #3498db; font-weight: 600; padding: 0 10px;">Conta de Administrador Mestre</legend>
                
                <div class="form-group">
                    <label for="admin_nome">Seu Nome</label>
                    <input type="text" id="admin_nome" name="admin_nome" required>
                </div>
                <div class="form-group">
                    <label for="admin_email">Seu E-mail (Será o Login)</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Senha (Mín. 6 caracteres)</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                </div>
            </fieldset>

            <button type="submit" class="btn-primary-action" style="background-color: #3498db;">
                <i class="fas fa-hammer"></i> Instalar e Iniciar
            </button>
        </form>
    </div>
</body>
</html>