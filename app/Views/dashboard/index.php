<?php
// app/Views/dashboard/index.php
if (!defined('BASE_URL')) exit; 

// Inclui o cabeçalho e inicia o layout
require_once(VIEW_PATH . 'partials/header.php');
?>

    <h1 style="color: #3498db;">Dashboard de Efetivo e Movimentação</h1>

    <div class="kpi-row">
        
        <div class="card-kpi" style="border-left-color: #2ecc71;">
            <i class="fas fa-users" style="font-size: 24px; color: #2ecc71;"></i>
            <div class="kpi-value"><?= number_format($totalAtivos, 0, ',', '.') ?></div>
            <div class="kpi-label">Colaboradores Ativos</div>
        </div>

        <div class="card-kpi" style="border-left-color: #3498db;">
            <i class="fas fa-plus-circle" style="font-size: 24px; color: #3498db;"></i>
            <div class="kpi-value"><?= $contratacoesMes ?></div>
            <div class="kpi-label">Contratações (Mês)</div>
        </div>

        <div class="card-kpi" style="border-left-color: #3498db;">
            <i class="fas fa-exchange-alt" style="font-size: 24px; color: #3498db;"></i>
            <div class="kpi-value"><?= $transferenciasMes ?></div>
            <div class="kpi-label">Transferências (Mês)</div>
        </div>

        <div class="card-kpi" style="border-left-color: #e74c3c;">
            <i class="fas fa-minus-circle" style="font-size: 24px; color: #e74c3c;"></i>
            <div class="kpi-value"><?= $desligamentosMes ?></div>
            <div class="kpi-label">Desligamentos (Mês)</div>
        </div>
    </div>

    <div class="grid-2-col">
        
        <div class="card-chart chart-container">
            <h3 style="color: #ffffff; margin-top: 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-bottom: 20px;">
                Distribuição de Efetivo por C.C.
            </h3>
            <canvas id="chartDistribuicaoCC"></canvas>
        </div>
        
        <div class="card-chart chart-container">
            <h3 style="color: #ffffff; margin-top: 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-bottom: 20px;">
                Efetivo vs. Vagas Abertas (Exemplo)
            </h3>
            <canvas id="chartFuncaoVagas"></canvas>
            <p style="font-size: 13px; color: #7f8c8d; margin-top: 10px;">
                <i class="fas fa-info-circle"></i> Baseado no último levantamento de vagas.
            </p>
        </div>
    </div>
    
    <script>
        // Dados de PHP (distribuicaoCC) são passados via JSON
        const dataDistribuicaoCC = <?= $chartDataCC ?>;

        // 1. Gráfico de Distribuição por C.C. (Gráfico de Rosca)
        const labelsCC = dataDistribuicaoCC.map(item => item.label);
        const valuesCC = dataDistribuicaoCC.map(item => item.value);

        new Chart(document.getElementById('chartDistribuicaoCC'), {
            type: 'doughnut',
            data: {
                labels: labelsCC,
                datasets: [{
                    label: 'Colaboradores',
                    data: valuesCC,
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.8)', // Azul
                        'rgba(46, 204, 113, 0.8)', // Verde
                        'rgba(241, 196, 15, 0.8)', // Amarelo
                        'rgba(52, 152, 219, 0.8)', // AZUL NO LUGAR DO LARANJA (Repete o azul se for a 4a cor)
                        'rgba(155, 89, 182, 0.8)' // Roxo
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#bdc3c7',
                        }
                    },
                    title: {
                        display: false,
                    }
                }
            }
        });

        // 2. Gráfico de Vagas (Exemplo de Gráfico de Barras)
        new Chart(document.getElementById('chartFuncaoVagas'), {
            type: 'bar',
            data: {
                labels: ['Mecânico I', 'Operador Forno', 'Eletricista', 'Aux. Geral'],
                datasets: [{
                    label: 'Efetivo Atual',
                    data: [25, 60, 10, 35],
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                }, {
                    label: 'Vagas Ocupadas (Meta)',
                    data: [30, 65, 12, 40],
                    backgroundColor: 'rgba(52, 152, 219, 0.7)', // AZUL NO LUGAR DO LARANJA
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: '#bdc3c7' }
                    }
                },
                scales: {
                    x: { ticks: { color: '#bdc3c7' } },
                    y: { ticks: { color: '#bdc3c7' } }
                }
            }
        });
    </script>

<?php
// Inclui o rodapé e encerra o layout
require_once(VIEW_PATH . 'partials/footer.php');
?>