<?php
// app/Views/colaboradores/index.php
if (!defined('BASE_URL')) exit; 

require_once(VIEW_PATH . 'partials/header.php');

// Exibe mensagem de feedback da sessão (após salvar, editar, excluir)
if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    $feedbackClass = $feedback['type'] === 'success' ? 'success' : 'error';
    echo '<div class="message-feedback ' . $feedbackClass . '">' . htmlspecialchars($feedback['message']) . '</div>';
    unset($_SESSION['feedback']); // Limpa a mensagem após exibir
}

// Helper para manter filtros nos links de paginação/formulário
function getFilterQuery($exclude = []) {
    $query = $_GET;
    foreach ($exclude as $key) {
        unset($query[$key]);
    }
    return http_build_query($query);
}

// Nível de acesso para botões
$canWrite = ($_SESSION['access_level'] == 'Admin' || $_SESSION['access_level'] == 'RH');
?>

<h1 style="color: #FF6600;">Gestão de Colaboradores (Efetivo Total: <?= $totalRecords ?>)</h1>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    
    <?php if ($canWrite): ?>
    <a href="<?= BASE_URL ?>colaboradores/novo" class="btn-primary-action" style="width: auto; padding: 10px 20px; background-color: #2ecc71;">
        <i class="fas fa-user-plus"></i> Novo Colaborador
    </a>
    <?php endif; ?>

    <?php if ($_SESSION['access_level'] == 'Admin' || $_SESSION['access_level'] == 'RH'): ?>
    <div style="display: flex; gap: 10px;">
        <button class="btn-secondary" style="width: auto; border-color: #3498db; color: #3498db;" onclick="alert('Funcionalidade de Exportação de Excel em desenvolvimento...')">
             <i class="fas fa-file-excel"></i> Exportar
        </button>
        <button class="btn-secondary" style="width: auto; border-color: #FF6600; color: #FF6600;" onclick="alert('Funcionalidade de Importação de Excel em desenvolvimento...')">
             <i class="fas fa-file-upload"></i> Importar
        </button>
    </div>
    <?php endif; ?>
</div>

<div class="card-chart" style="margin-bottom: 20px;">
    <h3 style="color: #bdc3c7; margin-top: 0;">Filtros de Pesquisa (Até 10 Filtros)</h3>
    <form method="GET" action="<?= BASE_URL ?>colaboradores" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        
        <div class="form-group" style="margin-bottom: 0;">
            <label>Busca Livre (Nome/Matrícula)</label>
            <input type="text" name="search" placeholder="Nome ou Matrícula" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label>Status</label>
            <select name="status">
                <option value="Todos" <?= ($_GET['status'] ?? '') == 'Todos' ? 'selected' : '' ?>>Todos</option>
                <option value="Ativo" <?= ($_GET['status'] ?? '') == 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="Transferido" <?= ($_GET['status'] ?? '') == 'Transferido' ? 'selected' : '' ?>>Transferido</option>
                <option value="Desligado" <?= ($_GET['status'] ?? '') == 'Desligado' ? 'selected' : '' ?>>Desligado</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label>Centro de Custo (C.C.)</label>
            <select name="cc">
                <option value="">-- Selecione --</option>
                <?php foreach ($centrosCusto as $cc): ?>
                    <option value="<?= $cc['id_cc'] ?>" <?= ($_GET['cc'] ?? '') == $cc['id_cc'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cc['sigla_cc']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label>Função</label>
            <select name="funcao">
                <option value="">-- Selecione --</option>
                <?php foreach ($funcoesDisponiveis as $funcao): ?>
                    <option value="<?= $funcao ?>" <?= ($_GET['funcao'] ?? '') == $funcao ? 'selected' : '' ?>>
                        <?= htmlspecialchars($funcao) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
             <button type="submit" class="btn-primary-action" style="width: 100%; margin-top: 25px;">
                <i class="fas fa-filter"></i> Aplicar Filtros
            </button>
        </div>
    </form>
</div>

<div class="card-chart" style="overflow-x: auto; padding: 10px;">
    <table class="table" style="width: 100%; border-collapse: collapse; color: #e0e0e0;">
        <thead>
            <tr style="background-color: #173859;">
                <th style="padding: 10px; text-align: left;">Matrícula</th>
                <th style="padding: 10px; text-align: left;">Nome</th>
                <th style="padding: 10px; text-align: left;">Função</th>
                <th style="padding: 10px; text-align: left;">C.C. Atual</th>
                <th style="padding: 10px; text-align: left;">Admissão</th>
                <th style="padding: 10px; text-align: left;">Status</th>
                <th style="padding: 10px; text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($colaboradores)): ?>
                <tr>
                    <td colspan="7" style="padding: 15px; text-align: center; color: #7f8c8d;">Nenhum colaborador encontrado com os filtros aplicados.</td>
                </tr>
            <?php endif; ?>
            
            <?php foreach ($colaboradores as $colab): ?>
                <tr style="border-bottom: 1px solid #2a528a; background-color: <?= $colab['status'] == 'Desligado' ? 'rgba(231, 76, 60, 0.1)' : 'transparent' ?>;">
                    <td style="padding: 10px;"><?= htmlspecialchars($colab['matricula']) ?></td>
                    <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($colab['nome']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($colab['funcao']) ?></td>
                    <td style="padding: 10px; color: #FF6600;"><?= htmlspecialchars($colab['sigla_cc']) ?></td>
                    <td style="padding: 10px;"><?= date('d/m/Y', strtotime($colab['data_admissao'])) ?></td>
                    <td style="padding: 10px;">
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                            background-color: <?= $colab['status'] == 'Ativo' ? '#2ecc71' : ($colab['status'] == 'Transferido' ? '#3498db' : '#e74c3c') ?>;
                            color: white;">
                            <?= htmlspecialchars($colab['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <a href="<?= BASE_URL ?>colaboradores/editar/<?= $colab['matricula'] ?>" title="Editar/Movimentar" style="color: #3498db; margin-right: 10px;"><i class="fas fa-edit"></i></a>
                        
                        <?php if ($canWrite && $colab['status'] != 'Desligado'): ?>
                        <a href="<?= BASE_URL ?>colaboradores/excluir/<?= $colab['matricula'] ?>" title="Marcar como Desligado" style="color: #e74c3c;" onclick="return confirm('ATENÇÃO: Deseja realmente marcar este colaborador como DESLIGADO? Essa ação é registrada como movimentação.')">
                            <i class="fas fa-user-times"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div style="margin-top: 20px; text-align: center;">
        <?php 
        $queryString = getFilterQuery(['page']);
        for ($i = 1; $i <= $totalPages; $i++): 
        ?>
            <a href="<?= BASE_URL ?>colaboradores?<?= $queryString ?>&page=<?= $i ?>" 
               style="padding: 8px 12px; margin: 0 5px; border-radius: 4px; text-decoration: none; 
                      background-color: <?= $currentPage == $i ? '#FF6600' : '#173859' ?>; 
                      color: white;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once(VIEW_PATH . 'partials/footer.php'); ?>