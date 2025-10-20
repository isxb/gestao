<?php
// app/Views/ccusto/index.php
if (!defined('BASE_URL')) exit; 

require_once(VIEW_PATH . 'partials/header.php');

// Exibe mensagem de feedback
if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    $feedbackClass = $feedback['type'] === 'success' ? 'success' : 'error';
    echo '<div class="message-feedback ' . $feedbackClass . '">' . htmlspecialchars($feedback['message']) . '</div>';
    unset($_SESSION['feedback']);
}
?>

<h1 style="color: #FF6600;">Gerenciamento de Centros de Custo (C.C.)</h1>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <a href="<?= BASE_URL ?>ccusto/form" class="btn-primary-action" style="width: auto; padding: 10px 20px; background-color: #2ecc71;">
        <i class="fas fa-plus"></i> Novo Centro de Custo
    </a>
</div>

<div class="card-chart" style="overflow-x: auto; padding: 10px;">
    <table class="table" style="width: 100%; border-collapse: collapse; color: #e0e0e0;">
        <thead>
            <tr style="background-color: #173859;">
                <th style="padding: 10px; text-align: left;">ID</th>
                <th style="padding: 10px; text-align: left;">Sigla</th>
                <th style="padding: 10px; text-align: left;">Nome do Centro de Custo</th>
                <th style="padding: 10px; text-align: left;">Status</th>
                <th style="padding: 10px; text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ccustos)): ?>
                <tr>
                    <td colspan="5" style="padding: 15px; text-align: center; color: #7f8c8d;">Nenhum Centro de Custo cadastrado.</td>
                </tr>
            <?php endif; ?>
            
            <?php foreach ($ccustos as $cc): ?>
                <tr style="border-bottom: 1px solid #2a528a; background-color: <?= $cc['status'] == 'Inativo' ? 'rgba(231, 76, 60, 0.1)' : 'transparent' ?>;">
                    <td style="padding: 10px;"><?= htmlspecialchars($cc['id_cc']) ?></td>
                    <td style="padding: 10px; font-weight: 600; color: #FF6600;"><?= htmlspecialchars($cc['sigla_cc']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($cc['nome_cc']) ?></td>
                    <td style="padding: 10px;">
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                            background-color: <?= $cc['status'] == 'Ativo' ? '#2ecc71' : '#e74c3c' ?>;
                            color: white;">
                            <?= htmlspecialchars($cc['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <a href="<?= BASE_URL ?>ccusto/form/<?= $cc['id_cc'] ?>" title="Editar C.C." style="color: #3498db; margin-right: 10px;"><i class="fas fa-edit"></i></a>
                        
                        <?php 
                        $newStatus = $cc['status'] == 'Ativo' ? 'Inativo' : 'Ativo';
                        $confirmMsg = $cc['status'] == 'Ativo' ? 'Deseja realmente INATIVAR este C.C.? Isso afetará novos cadastros e filtros.' : 'Deseja realmente REATIVAR este C.C.?';
                        $iconClass = $cc['status'] == 'Ativo' ? 'fas fa-toggle-off' : 'fas fa-toggle-on';
                        $iconColor = $cc['status'] == 'Ativo' ? '#e74c3c' : '#2ecc71';
                        ?>
                        <a href="<?= BASE_URL ?>ccusto/toggleStatus/<?= $cc['id_cc'] ?>" title="Alterar Status para <?= $newStatus ?>" style="color: <?= $iconColor ?>;" onclick="return confirm('<?= $confirmMsg ?>')">
                            <i class="<?= $iconClass ?>"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>