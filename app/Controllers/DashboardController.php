<?php
// app/Controllers/DashboardController.php

class DashboardController {
    
    // Modelos que serão necessários para o dashboard
    private $colaboradorModel;
    private $ccustoModel;

    public function __construct() {
        // Inicializa os Modelos (Serão criados a seguir)
        $this->colaboradorModel = new Colaborador();
        $this->ccustoModel = new CCusto();
    }

    /**
     * Exibe o painel principal do sistema.
     * Rota: BASE_URL/dashboard
     */
    public function index() {
        // --- 1. PROTEÇÃO DE ROTA (Obrigatoriedade em todos os Controllers internos) ---
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // --- 2. BUSCA DE DADOS PARA O DASHBOARD ---
        
        // Dados brutos (simulação por enquanto, o Model fará as consultas reais)
        $totalColaboradores = $this->colaboradorModel->getTotalAtivos();
        $distribuicaoCC = $this->colaboradorModel->getDistribuicaoPorCC();
        $movimentacoesMes = $this->colaboradorModel->getMovimentacoesMensais(date('Y-m'));
        
        $totalAtivos = $totalColaboradores['ativos'] ?? 0;
        $totalInativos = $totalColaboradores['inativos'] ?? 0;
        $contratacoesMes = $movimentacoesMes['contratacoes'] ?? 0;
        $desligamentosMes = $movimentacoesMes['desligamentos'] ?? 0;
        $transferenciasMes = $movimentacoesMes['transferencias'] ?? 0;

        // Dados para Gráficos (Exemplo de estrutura JSON para o Frontend)
        $chartDataCC = json_encode($distribuicaoCC);
        
        // --- 3. CARREGA A VIEW ---
        
        // A view principal do Dashboard
        require_once(VIEW_PATH . 'dashboard/index.php');
    }
    
    // ... Outras funções do dashboard, se necessário ...
}