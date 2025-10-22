<?php
// app/Views/usuarios/form.php
if (!defined('BASE_URL')) exit; 

require_once(VIEW_PATH . 'partials/header.php');

$isEditing = !empty($usuario['id_usuario']);
$pageTitle = $isEditing ? 'Editar Usuário: ' . htmlspecialchars($usuario['nome']) : 'Novo Cadastro de Usuário';
$buttonText = $isEditing ? 'Atualizar Dados' : 'Cadastrar Usuário';
$currentLevel = $usuario['nivel_acesso'] ?? 'Colaborador';
?>

<h1 style="color: #3498db;"><?= $pageTitle ?></h1>

<div class="card-chart" style="max-width: 900px; margin: 0 auto;">
    <form action="<?= BASE_URL ?>usuario/salvar" method="POST">
        
        <?php if ($isEditing): ?>
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <fieldset style="border: 1px solid #3498db; padding: 20px; border-radius: 8px;">
                <legend style="color: #3498db; font-weight: 600; padding: 0 10px;">Informações de Acesso</legend>
                
                <div class="form-group">
                    <label>Nome <span style="color: #e74c3c;">*</span></label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>E-mail (Login) <span style="color: #e74c3c;">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Função</label>
                    <input type="text" name="funcao" value="<?= htmlspecialchars($usuario['funcao'] ?? '') ?>" placeholder="Ex: Analista de RH">
                </div>

                <div class="form-group">
                    <label>Nível de Acesso <span style="color: #e74c3c;">*</span></label>
                    <select name="nivel_acesso" required>
                        <?php 
                        // ACESS_LEVELS é uma constante definida em Config.php
                        $levels = ['Admin', 'RH', 'Gestor', 'Colaborador']; 
                        foreach ($levels as $level): 
                        ?>
                            <option value="<?= $level ?>" <?= $currentLevel == $level ? 'selected' : '' ?>>
                                <?= $level ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>C.C. Principal (Para referência)</label>
                    <select name="id_cc_principal">
                        <option value="">-- Selecione --</option>
                        <?php 
                        $currentCC = $usuario['id_cc_principal'] ?? '';
                        foreach ($ccustos as $cc): ?>
                            <option value="<?= $cc['id_cc'] ?>" <?= $currentCC == $cc['id_cc'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cc['sigla_cc']) . ' - ' . htmlspecialchars($cc['nome_cc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </fieldset>

            <fieldset style="border: 1px solid #3498db; padding: 20px; border-radius: 8px;">
                <legend style="color: #3498db; font-weight: 600; padding: 0 10px;">Gerenciamento e Segurança</legend>
                
                <div class="form-group">
                    <label>Senha <?= $isEditing ? '(Deixe em branco para não alterar)' : '<span style="color: #e74c3c;">*</span>' ?></label>
                    <input type="password" name="password" <?= $isEditing ? '' : 'required' ?>>
                </div>
                <div class="form-group">
                    <label>Confirmação de Senha</label>
                    <input type="password" name="password_confirm">
                </div>

                <div class="form-group">
                    <label>Centros de Custo Liberados (Acesso de Gestão)</label>
                    <div style="height: 150px; overflow-y: scroll; padding: 10px; border: 1px solid #2a528a; border-radius: 6px; background-color: rgba(255, 255, 255, 0.05);">
                        <?php foreach ($ccustos as $cc): ?>
                            <div style="margin-bottom: 5px;">
                                <?php 
                                $isChecked = in_array($cc['id_cc'], $cc_liberados);
                                ?>
                                <input type="checkbox" id="cc_<?= $cc['id_cc'] ?>" name="cc_liberados[]" value="<?= $cc['id_cc'] ?>" <?= $isChecked ? 'checked' : '' ?>>
                                <label for="cc_<?= $cc['id_cc'] ?>" style="display: inline; font-size: 14px;"><?= htmlspecialchars($cc['sigla_cc'] . ' - ' . $cc['nome_cc']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="font-size: 12px; color: #7f8c8d; margin-top: 5px;">*Usuários 'Gestor' só verão colaboradores nos C.C.s marcados.</p>
                </div>
            </fieldset>
        </div>

        <div class="form-buttons" style="margin-top: 20px;">
            <button type="submit" class="btn-primary-action" style="background-color: #3498db;">
                <i class="fas fa-save"></i> <?= $buttonText ?>
            </button>
            <a href="<?= BASE_URL ?>usuario" class="btn-secondary" style="text-decoration: none; text-align: center; border: 1px solid #7f8c8d; color: #7f8c8d; padding: 12px 15px;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>

    </form>
</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>