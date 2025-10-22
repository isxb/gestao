<?php
// app/Views/movimentacao/form.php
if (!defined('BASE_URL')) exit; 

require_once(VIEW_PATH . 'partials/header.php');

$pageTitle = 'Solicitar Nova Transferência de Colaborador';
$isTransfer = !empty($colaborador['matricula']);
?>

<h1 style="color: #3498db;"><?= $pageTitle ?></h1>

<div class="card-chart" style="max-width: 700px; margin: 0 auto;">
    
    <?php if (!$isTransfer): ?>
        <h3 style="color: #bdc3c7; margin-top: 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            Passo 1: Encontre o Colaborador
        </h3>
        <form action="<?= BASE_URL ?>movimentacao/buscar_colaborador" method="POST">
            <div style="display: flex; gap: 20px; align-items: flex-end;">
                <div class="form-group" style="flex-grow: 1;">
                    <label>Matrícula do Colaborador <span style="color: #e74c3c;">*</span></label>
                    <input type="number" name="matricula" required placeholder="Ex: 4001">
                </div>
                <button type="submit" class="btn-primary-action" style="width: 150px; background-color: #3498db;">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>
    <?php else: ?>
    
    <h3 style="color: #bdc3c7; margin-top: 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
        Passo 2: Definir Transferência
    </h3>
    
    <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #3498db; border-radius: 8px;">
        <p style="margin: 0;">**Colaborador:** <span style="font-weight: 600; color: #ffffff;"><?= htmlspecialchars($colaborador['nome']) ?></span> (Mat: <?= htmlspecialchars($colaborador['matricula']) ?>)</p>
        <p style="margin: 5px 0 0 0;">**C.C. Atual:** <span style="color: #3498db; font-weight: 600;"><?= htmlspecialchars($colaborador['sigla_cc'] ?? 'C.C. não encontrado') ?></span></p>
    </div>

    <form action="<?= BASE_URL ?>movimentacao/solicitar" method="POST">
        
        <input type="hidden" name="matricula_colaborador" value="<?= htmlspecialchars($colaborador['matricula']) ?>">

        <div class="form-group">
            <label>Novo Centro de Custo (Destino) <span style="color: #e74c3c;">*</span></label>
            <select name="id_cc_destino" required>
                <option value="">-- Selecione o Centro de Custo --</option>
                <?php 
                // Assumindo que o colaborador model foi enriquecido com a sigla/nome do CC atual
                $currentCC = $colaborador['id_cc_atual'] ?? '';
                foreach ($centrosCusto as $cc): ?>
                    <option value="<?= $cc['id_cc'] ?>" <?= $currentCC == $cc['id_cc'] ? 'disabled style="background-color: #2a528a; color: #7f8c8d;"' : '' ?>>
                        <?= htmlspecialchars($cc['sigla_cc']) . ' - ' . htmlspecialchars($cc['nome_cc']) ?>
                        <?= $currentCC == $cc['id_cc'] ? ' (Atual)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
            
        <div class="form-group">
            <label>Motivo da Transferência/Solicitação <span style="color: #e74c3c;">*</span></label>
            <textarea name="motivo" rows="4" required placeholder="Descreva o motivo da transferência: projeto, necessidade da área, etc."></textarea>
        </div>
        
        <div class="message-feedback info" style="font-size: 13px;">
            <i class="fas fa-info-circle"></i> Esta solicitação será registrada como **Pendente** e o colaborador será marcado como **Transferido** no sistema, aguardando aprovação do RH.
        </div>
        
        <div class="form-buttons" style="margin-top: 20px;">
            <button type="submit" class="btn-primary-action" style="background-color: #3498db;">
                <i class="fas fa-arrow-right"></i> Solicitar Transferência
            </button>
            <a href="<?= BASE_URL ?>movimentacao" class="btn-secondary" style="text-decoration: none; text-align: center; border: 1px solid #7f8c8d; color: #7f8c8d; padding: 12px 15px;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>