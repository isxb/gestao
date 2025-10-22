<?php
// app/Views/rh/aprovacao.php
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

<h1 style="color: #3498db;">RH: Aprovação de Transferências Pendentes</h1>

<div class="card-chart" style="overflow-x: auto; padding: 10px;">
    
    <?php if (empty($pendencias)): ?>
        <p style="padding: 15px; text-align: center; color: #7f8c8d;"><i class="fas fa-check-circle" style="color: #2ecc71;"></i> Nenhuma movimentação pendente de aprovação no momento.</p>
    <?php else: ?>
    
    <table class="table" style="width: 100%; border-collapse: collapse; color: #e0e0e0;">
        <thead>
            <tr style="background-color: #173859;">
                <th style="padding: 10px; text-align: left;">ID</th>
                <th style="padding: 10px; text-align: left;">Colaborador</th>
                <th style="padding: 10px; text-align: left;">Origem</th>
                <th style="padding: 10px; text-align: left;">Destino</th>
                <th style="padding: 10px; text-align: left;">Data Sol.</th>
                <th style="padding: 10px; text-align: left;">Motivo / Solicitante</th>
                <th style="padding: 10px; text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pendencias as $pendencia): ?>
                <tr style="border-bottom: 1px solid #2a528a;">
                    <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($pendencia['id_mov']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($pendencia['colaborador_nome']) ?> (Mat: <?= htmlspecialchars($pendencia['matricula']) ?>)</td>
                    <td style="padding: 10px; color: #7f8c8d;"><?= htmlspecialchars($pendencia['cc_origem_sigla'] ?? 'N/A') ?></td>
                    <td style="padding: 10px; color: #3498db; font-weight: 600;"><?= htmlspecialchars($pendencia['cc_destino_sigla']) ?></td>
                    <td style="padding: 10px; font-size: 0.9em;"><?= date('d/m/Y H:i', strtotime($pendencia['data_movimentacao'])) ?></td>
                    <td style="padding: 10px; font-size: 0.9em;">
                        **Motivo:** <?= htmlspecialchars(substr($pendencia['motivo'], 0, 50)) ?>...<br>
                        **Por:** <?= htmlspecialchars($pendencia['gestor_solicitante']) ?>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <form method="POST" action="<?= BASE_URL ?>rh/processar" style="display: inline-flex; gap: 5px;">
                            <input type="hidden" name="id_mov" value="<?= $pendencia['id_mov'] ?>">
                            
                            <button type="submit" name="action" value="aprovar" title="Aprovar Transferência" class="btn-primary" style="padding: 5px 10px; background-color: #2ecc71;">
                                <i class="fas fa-check"></i> Aprovar
                            </button>
                            
                            <button type="submit" name="action" value="rejeitar" title="Rejeitar e Manter Colaborador" class="btn-secondary" style="padding: 5px 10px; background-color: #e74c3c; color: white; border: none;" onclick="return confirm('Deseja REJEITAR esta movimentação?')">
                                <i class="fas fa-times"></i> Rejeitar
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php endif; ?>

</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>