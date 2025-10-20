<?php
// app/Views/colaboradores/form.php
if (!defined('BASE_URL')) exit; 

require_once(VIEW_PATH . 'partials/header.php');

$isEditing = !empty($colaborador['matricula']);
$pageTitle = $isEditing ? 'Editar Colaborador: ' . htmlspecialchars($colaborador['nome']) : 'Novo Cadastro de Colaborador';
$buttonText = $isEditing ? 'Salvar Alterações' : 'Cadastrar Colaborador';
?>

<h1 style="color: #FF6600;"><?= $pageTitle ?></h1>

<div class="card-chart" style="max-width: 800px; margin: 0 auto;">
    <form action="<?= BASE_URL ?>colaboradores/salvar" method="POST">
        
        <?php if ($isEditing): ?>
            <input type="hidden" name="matricula" value="<?= htmlspecialchars($colaborador['matricula']) ?>">
        <?php endif; ?>

        <fieldset style="border: 1px solid #3498db; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <legend style="color: #3498db; font-weight: 600; padding: 0 10px;">Dados Principais</legend>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <div class="form-group">
                    <label>Matrícula <span style="color: #e74c3c;">*</span></label>
                    <input type="number" name="matricula" value="<?= htmlspecialchars($colaborador['matricula'] ?? '') ?>" required <?= $isEditing ? 'readonly' : '' ?> placeholder="Ex: 4001">
                </div>
                
                <div class="form-group">
                    <label>Nome Completo <span style="color: #e74c3c;">*</span></label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($colaborador['nome'] ?? '') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Função</label>
                    <input type="text" name="funcao" value="<?= htmlspecialchars($colaborador['funcao'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Data de Admissão <span style="color: #e74c3c;">*</span></label>
                    <input type="date" name="data_admissao" value="<?= htmlspecialchars($colaborador['data_admissao'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Tipo de Contrato</label>
                    <select name="tipo_contrato">
                        <?php 
                        $selectedContrato = $colaborador['tipo_contrato'] ?? 'CLT';
                        $contratos = ['CLT', 'Terceirizado', 'Temporário', 'Estágio'];
                        foreach ($contratos as $c): ?>
                            <option value="<?= $c ?>" <?= $selectedContrato == $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset style="border: 1px solid #FF6600; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <legend style="color: #FF6600; font-weight: 600; padding: 0 10px;">Centro de Custo e Status</legend>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>C.C. Atual <span style="color: #e74c3c;">*</span></label>
                    <select name="id_cc_atual" required>
                        <option value="">-- Selecione o Centro de Custo --</option>
                        <?php 
                        $currentCC = $colaborador['id_cc_atual'] ?? '';
                        foreach ($centrosCusto as $cc): ?>
                            <option value="<?= $cc['id_cc'] ?>" <?= $currentCC == $cc['id_cc'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cc['sigla_cc']) . ' - ' . htmlspecialchars($cc['nome_cc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php 
                        $currentStatus = $colaborador['status'] ?? 'Ativo';
                        $statuses = ['Ativo', 'Transferido', 'Desligado', 'Férias'];
                        foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>" <?= $currentStatus == $s ? 'selected' : '' ?>>
                                <?= $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Motivo da Movimentação/Status (Se alterado)</label>
                <textarea name="motivo_movimentacao" rows="3" placeholder="Detalhes sobre a transferência, desligamento, ou mudança de status..."></textarea>
            </div>
        </fieldset>
        
        <div class="form-buttons">
            <button type="submit" class="btn-primary-action" style="background-color: #FF6600;">
                <i class="fas fa-save"></i> <?= $buttonText ?>
            </button>
            <a href="<?= BASE_URL ?>colaboradores" class="btn-secondary" style="text-decoration: none; text-align: center; border: 1px solid #7f8c8d; color: #7f8c8d; padding: 12px 15px;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>

    </form>
</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>