<?php
// app/Controllers/RHController.php

class RHController {
    
    private $movimentacaoModel;
    private $colaboradorModel;

    public function __construct() {
        // --- PROTEÇÃO DE ROTA (Apenas Admin e RH) ---
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $allowedAccess = ['Admin', 'RH'];
        if (!in_array($_SESSION['access_level'], $allowedAccess)) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Acesso negado. Funcionalidades exclusivas do RH.'];
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->movimentacaoModel = new Movimentacao();
        // O ColaboradorModel é necessário para importação/exportação no futuro
        $this->colaboradorModel = new Colaborador();
    }

    /**
     * Exibe a tela de aprovação de transferências pendentes.
     * Rota: BASE_URL/rh/aprovacao
     */
    public function aprovacao() {
        // Busca todas as movimentações com status 'Pendente'
        $pendencias = $this->movimentacaoModel->getPendenciasAprovacao();

        require_once(VIEW_PATH . 'rh/aprovacao.php');
    }
    
    /**
     * Processa a ação (Aprovar/Rejeitar) em uma movimentação.
     * Rota: POST /rh/processar
     */
    public function processar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'rh/aprovacao');
            exit;
        }

        $movId = intval($_POST['id_mov'] ?? 0);
        $action = $_POST['action'] ?? ''; // 'aprovar' ou 'rejeitar'
        $rhUserId = $_SESSION['user_id'];

        if ($movId > 0 && ($action === 'aprovar' || $action === 'rejeitar')) {
            $status = ($action === 'aprovar') ? 'Aprovada' : 'Rejeitada';
            
            if ($this->movimentacaoModel->updateStatus($movId, $status, $rhUserId)) {
                $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Movimentação ID ' . $movId . ' foi ' . strtolower($status) . ' com sucesso.'];
            } else {
                $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao processar a movimentação.'];
            }
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Parâmetros inválidos.'];
        }

        header('Location: ' . BASE_URL . 'rh/aprovacao');
        exit;
    }

    /**
     * Exibe a tela de relatórios completos (Auditoria de Movimentação).
     * Rota: BASE_URL/rh/relatorios
     */
    public function relatorios() {
        $filters = $_GET ?? [];
        $historico = [];
        
        // Se houver filtros, busca o histórico completo
        if (!empty($filters)) {
             $historico = $this->movimentacaoModel->getHistoricoCompleto($filters);
        }

        require_once(VIEW_PATH . 'rh/relatorios.php');
    }
    
    /**
     * Gera o relatório de auditoria de movimentação em Excel.
     * Rota: BASE_URL/rh/exportar
     */
    public function exportar() {
        // NOTA: Esta função exigiria uma biblioteca como PhpSpreadsheet (que precisa ser instalada via Composer).
        // Por ser um projeto puro, vamos simular a exportação para CSV/Excel.
        
        $filters = $_GET ?? [];
        $historico = $this->movimentacaoModel->getHistoricoCompleto($filters);

        // 1. Configura os headers para download do arquivo CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="auditoria_movimentacao_' . date('Ymd_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // 2. Cabeçalho do CSV
        fputcsv($output, ['ID Mov.', 'Matricula', 'Nome Colaborador', 'Função', 'CC Origem', 'CC Destino', 'Tipo', 'Data', 'Status', 'Motivo', 'Registrado Por'], ';');
        
        // 3. Dados
        foreach ($historico as $item) {
            fputcsv($output, [
                $item['id_mov'],
                $item['matricula'],
                $item['colaborador_nome'],
                $item['funcao'],
                $item['cc_origem_sigla'] ?? 'N/A',
                $item['cc_destino_sigla'] ?? 'N/A',
                $item['tipo_movimentacao'],
                date('d/m/Y H:i', strtotime($item['data_movimentacao'])),
                $item['status_aprovacao'],
                $item['motivo'],
                $item['usuario_registro']
            ], ';');
        }
        
        fclose($output);
        exit;
    }
}