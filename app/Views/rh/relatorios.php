<?php
// app/Views/rh/relatorios.php
if (!defined('BASE_URL')) exit; 

require_once(VIEW_PATH . 'partials/header.php');
?>

<h1 style="color: #3498db;">Auditoria e Relatórios de Movimentação</h1>

<div class="card-chart" style="margin-bottom: 20px;">
    <h3 style="color: #bdc3c7; margin-top: 0;">Filtros de Auditoria</h3>
    <form method="GET" action="<?= BASE_URL ?>rh/relatorios" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        
        <div class="form-group" style="margin-bottom: 0;">
            <label>Data Mínima (Movimentação)</label>
            <input type="date" name="data_min" value="<?= htmlspecialchars($_GET['data_min'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label>Data Máxima (Movimentação)</label>
            <input type="date" name="data_max" value="<?= htmlspecialchars($_GET['data_max'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label>Tipo de Movimentação</label>
            <select name="tipo">
                <option value="">Todos</option>
                <option value="Admissão" <?= ($_GET['tipo'] ?? '') == 'Admissão' ? 'selected' : '' ?>>Admissão</option>
                <option value="Transferência" <?= ($_GET['tipo'] ?? '') == 'Transferência' ? 'selected' : '' ?>>Transferência</option>
                <option value="Desligamento" <?= ($_GET['tipo'] ?? '') == 'Desligamento' ? 'selected' : '' ?>>Desligamento</option>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
             <button type="submit" class="btn-primary-action" style="width: 100%; margin-top: 25px; background-color: #3498db;">
                <i class="fas fa-search"></i> Gerar Relatório
            </button>
        </div>
    </form>
</div>

<div class="card-chart" style="overflow-x: auto; padding: 10px;">
    
    <?php if (!empty($historico)): ?>
        <div style="text-align: right; margin-bottom: 15px;">
            <a href="<?= BASE_URL ?>rh/exportar?<?= http_build_query($_GET) ?>" class="btn-primary-action" style="width: auto; padding: 8px 15px; background-color: #2ecc71;">
                <i class="fas fa-file-excel"></i> Exportar para Excel
            </a>
            <button onclick="alert('Funcionalidade de Exportação de PDF em desenvolvimento...')" class="btn-secondary" style="width: auto; padding: 8px 15px; border-color: #e74c3c; color: #e74c3c;">
                <i class="fas fa-file-pdf"></i> Exportar para PDF
            </button>
        </div>
    <?php endif; ?>
    
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
                    <td colspan="7" style="padding: 15px; text-align: center; color: #7f8c8d;">
                        <?= isset($_GET['data_min']) ? 'Nenhum registro encontrado com os filtros aplicados.' : 'Use os filtros acima para gerar relatórios de auditoria.' ?>
                    </td>
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