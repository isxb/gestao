<?php
// app/Views/movimentacao/index.php
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

<h1 style="color: #3498db;">Histórico de Movimentações (Transferências e Status)</h1>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <a href="<?= BASE_URL ?>movimentacao/novo" class="btn-primary-action" style="width: auto; padding: 10px 20px; background-color: #3498db;">
        <i class="fas fa-plus"></i> Nova Transferência
    </a>
    
    </div>

<div class="card-chart" style="overflow-x: auto; padding: 10px;">
    
    <table class="table" style="width: 100%; border-collapse: collapse; color: #e0e0e0;">
        <thead>
            <tr style="background-color: #173859;">
                <th style="padding: 10px; text-align: left;">Matrícula</th>
                <th style="padding: 10px; text-align: left;">Colaborador</th>
                <th style="padding: 10px; text-align: left;">De / Para C.C.</th>
                <th style="padding: 10px; text-align: left;">Tipo</th>
                <th style="padding: 10px; text-align: left;">Data/Hora</th>
                <th style="padding: 10px; text-align: left;">Motivo</th>
                <th style="padding: 10px; text-align: left;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($historico)): ?>
                <tr>
                    <td colspan="7" style="padding: 15px; text-align: center; color: #7f8c8d;">Nenhuma movimentação registrada.</td>
                </tr>
            <?php endif; ?>
            
            <?php foreach ($historico as $item): ?>
                <tr style="border-bottom: 1px solid #2a528a; font-size: 0.9em;">
                    <td style="padding: 10px;"><?= htmlspecialchars($item['matricula']) ?></td>
                    <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($item['colaborador_nome']) ?></td>
                    <td style="padding: 10px; color: #3498db;">
                        <?= htmlspecialchars($item['cc_origem_sigla'] ?? 'N/A') ?> 
                        <i class="fas fa-arrow-right" style="font-size: 0.8em; margin: 0 5px; color: #7f8c8d;"></i> 
                        <?= htmlspecialchars($item['cc_destino_sigla'] ?? 'N/A') ?>
                    </td>
                    <td style="padding: 10px;"><?= htmlspecialchars($item['tipo_movimentacao']) ?></td>
                    <td style="padding: 10px;"><?= date('d/m/Y H:i', strtotime($item['data_movimentacao'])) ?></td>
                    <td style="padding: 10px; font-style: italic;"><?= htmlspecialchars(substr($item['motivo'], 0, 80)) ?>...</td>
                    <td style="padding: 10px;">
                         <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                            background-color: <?= $item['status_aprovacao'] == 'Aprovada' ? '#2ecc71' : ($item['status_aprovacao'] == 'Pendente' ? '#f1c40f' : '#e74c3c') ?>;
                            color: white;">
                            <?= htmlspecialchars($item['status_aprovacao']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>