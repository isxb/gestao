<?php
// app/Views/usuarios/index.php
if (!defined('BASE_URL')) exit; 

require_once(VIEW_PATH . 'partials/header.php');

// Exibe mensagem de feedback
if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    $feedbackClass = $feedback['type'] === 'success' ? 'success' : 'error';
    echo '<div class="message-feedback ' . $feedbackClass . '">' . htmlspecialchars($feedback['message']) . '</div>';
    unset($_SESSION['feedback']);
}

$currentUserId = $_SESSION['user_id'];
?>

<h1 style="color: #3498db;">Gerenciamento de Usuários do Sistema</h1>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <a href="<?= BASE_URL ?>usuario/novo" class="btn-primary-action" style="width: auto; padding: 10px 20px; background-color: #2ecc71;">
        <i class="fas fa-user-plus"></i> Novo Usuário
    </a>

    <div style="display: flex; gap: 10px;">
        <button class="btn-secondary" style="width: auto; border-color: #3498db; color: #3498db;" onclick="alert('Funcionalidade de Exportação de Excel em desenvolvimento...')">
             <i class="fas fa-file-excel"></i> Exportar
        </button>
        <button class="btn-secondary" style="width: auto; border-color: #3498db; color: #3498db;" onclick="alert('Funcionalidade de Importação de Excel em desenvolvimento...')">
             <i class="fas fa-file-upload"></i> Importar
        </button>
    </div>
</div>

<div class="card-chart" style="overflow-x: auto; padding: 10px;">
    <table class="table" style="width: 100%; border-collapse: collapse; color: #e0e0e0;">
        <thead>
            <tr style="background-color: #173859;">
                <th style="padding: 10px; text-align: left;">ID</th>
                <th style="padding: 10px; text-align: left;">Nome</th>
                <th style="padding: 10px; text-align: left;">E-mail (Login)</th>
                <th style="padding: 10px; text-align: left;">Nível de Acesso</th>
                <th style="padding: 10px; text-align: left;">C.C. Principal</th>
                <th style="padding: 10px; text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="6" style="padding: 15px; text-align: center; color: #7f8c8d;">Nenhum usuário cadastrado.</td>
                </tr>
            <?php endif; ?>
            
            <?php foreach ($usuarios as $user): ?>
                <tr style="border-bottom: 1px solid #2a528a;">
                    <td style="padding: 10px;"><?= htmlspecialchars($user['id_usuario']) ?></td>
                    <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($user['nome']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($user['email']) ?></td>
                    <td style="padding: 10px;">
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                            background-color: <?= $user['nivel_acesso'] == 'Admin' ? '#e74c3c' : '#3498db' ?>;
                            color: white;">
                            <?= htmlspecialchars($user['nivel_acesso']) ?>
                        </span>
                    </td>
                    <td style="padding: 10px; color: #3498db;"><?= htmlspecialchars($user['cc_principal_sigla'] ?? 'N/A') ?></td>
                    <td style="padding: 10px; text-align: center;">
                        <a href="<?= BASE_URL ?>usuario/editar/<?= $user['id_usuario'] ?>" title="Editar Permissões" style="color: #3498db; margin-right: 10px;"><i class="fas fa-edit"></i></a>
                        
                        <?php if ($user['id_usuario'] != $currentUserId): // Não permite excluir a própria conta ?>
                        <a href="<?= BASE_URL ?>usuario/excluir/<?= $user['id_usuario'] ?>" title="Excluir Usuário" style="color: #e74c3c;" onclick="return confirm('ATENÇÃO: Deseja realmente excluir este usuário? Isso é irreversível.')">
                            <i class="fas fa-user-times"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>