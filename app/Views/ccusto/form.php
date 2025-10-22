<?php
// app/Views/ccusto/form.php
if (!defined('BASE_URL')) exit; 

require_once(VIEW_PATH . 'partials/header.php');

$isEditing = !empty($ccusto['id_cc']);
$pageTitle = $isEditing ? 'Editar Centro de Custo: ' . htmlspecialchars($ccusto['sigla_cc']) : 'Novo Cadastro de Centro de Custo';
$buttonText = $isEditing ? 'Salvar Alterações' : 'Cadastrar C.C.';
$currentStatus = $ccusto['status'] ?? 'Ativo';
?>

<h1 style="color: #3498db;"><?= $pageTitle ?></h1>

<div class="card-chart" style="max-width: 500px; margin: 0 auto;">
    <form action="<?= BASE_URL ?>ccusto/salvar" method="POST">
        
        <?php if ($isEditing): ?>
            <input type="hidden" name="id_cc" value="<?= htmlspecialchars($ccusto['id_cc']) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Sigla (Ex: ADM, FORNO) <span style="color: #e74c3c;">*</span></label>
            <input type="text" name="sigla_cc" value="<?= htmlspecialchars($ccusto['sigla_cc'] ?? '') ?>" required maxlength="10">
        </div>
        
        <div class="form-group">
            <label>Nome Completo (Ex: Administração Central) <span style="color: #e74c3c;">*</span></label>
            <input type="text" name="nome_cc" value="<?= htmlspecialchars($ccusto['nome_cc'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="Ativo" <?= $currentStatus == 'Ativo' ? 'selected' : '' ?>>Ativo (Disponível)</option>
                <option value="Inativo" <?= $currentStatus == 'Inativo' ? 'selected' : '' ?>>Inativo (Não usar mais)</option>
            </select>
        </div>

        <div class="form-buttons" style="margin-top: 20px;">
            <button type="submit" class="btn-primary-action" style="background-color: #3498db;">
                <i class="fas fa-save"></i> <?= $buttonText ?>
            </button>
            <a href="<?= BASE_URL ?>ccusto" class="btn-secondary" style="text-decoration: none; text-align: center; border: 1px solid #7f8c8d; color: #7f8c8d; padding: 12px 15px;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>

    </form>
</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>