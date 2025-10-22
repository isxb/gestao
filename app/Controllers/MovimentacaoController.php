<?php
// app/Controllers/MovimentacaoController.php

class MovimentacaoController {
    
    private $movimentacaoModel;
    private $colaboradorModel;
    private $ccustoModel;

    public function __construct() {
        // --- 1. PROTEÇÃO DE ROTA (Apenas Admin, RH e Gestor) ---
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $allowedAccess = ['Admin', 'RH', 'Gestor'];
        if (!in_array($_SESSION['access_level'], $allowedAccess)) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Acesso negado. Você não tem permissão para gerenciar movimentações.'];
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->movimentacaoModel = new Movimentacao();
        $this->colaboradorModel = new Colaborador();
        $this->ccustoModel = new CCusto();
    }

    /**
     * Exibe a lista principal de movimentações/auditoria.
     * Rota: BASE_URL/movimentacao
     */
    public function index() {
        // Usa a função de histórico completo com filtros vazios por padrão
        $filters = $_GET ?? [];
        $historico = $this->movimentacaoModel->getHistoricoCompleto($filters);
        
        // Dados auxiliares para filtros, se necessário
        $ccustos = $this->ccustoModel->getAll();

        require_once(VIEW_PATH . 'movimentacao/index.php');
    }

    /**
     * Exibe o formulário para solicitação de nova Transferência.
     * Rota: BASE_URL/movimentacao/novo
     */
    public function novo() {
        $colaborador = null; // Colaborador a ser transferido (inicialmente nulo)
        $centrosCusto = $this->ccustoModel->getAllActive();

        require_once(VIEW_PATH . 'movimentacao/form.php');
    }
    
    /**
     * Busca os dados do colaborador para o formulário.
     * Rota: POST /movimentacao/buscar_colaborador
     */
    public function buscar_colaborador() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['matricula'])) {
            header('Location: ' . BASE_URL . 'movimentacao/novo');
            exit;
        }
        
        $matricula = intval($_POST['matricula']);
        $colaborador = $this->colaboradorModel->getByMatricula($matricula);
        
        if (!$colaborador) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Colaborador com matrícula ' . $matricula . ' não encontrado ou inativo.'];
            header('Location: ' . BASE_URL . 'movimentacao/novo');
            exit;
        }
        
        // Se Gestor, verifica se tem permissão sobre o C.C. atual do colaborador
        if ($_SESSION['access_level'] == 'Gestor') {
            if (!in_array($colaborador['id_cc_atual'], $_SESSION['cc_liberados'])) {
                 $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Você não tem permissão para movimentar colaboradores fora dos seus Centros de Custo liberados.'];
                 header('Location: ' . BASE_URL . 'movimentacao');
                 exit;
            }
        }
        
        $centrosCusto = $this->ccustoModel->getAllActive();
        // Passa o objeto do colaborador para a view do formulário
        require_once(VIEW_PATH . 'movimentacao/form.php');
    }

    /**
     * Processa a solicitação de transferência.
     * Rota: POST /movimentacao/solicitar
     */
    public function solicitar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'movimentacao');
            exit;
        }

        $data = $_POST;
        $matricula = $data['matricula_colaborador'] ?? null;
        $ccDestino = $data['id_cc_destino'] ?? null;
        $motivo = $data['motivo'] ?? 'Solicitação de Transferência via Sistema Gestivo.';
        $userId = $_SESSION['user_id'];
        
        // 1. Busca colaborador atual para obter o C.C. de origem
        $colaborador = $this->colaboradorModel->getByMatricula($matricula);
        if (!$colaborador) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Colaborador não encontrado.'];
            header('Location: ' . BASE_URL . 'movimentacao/novo');
            exit;
        }
        
        // 2. Garante que o C.C. de destino é diferente do atual
        $ccOrigem = $colaborador['id_cc_atual'];
        if ($ccOrigem == $ccDestino) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'O C.C. de destino deve ser diferente do C.C. atual.'];
            header('Location: ' . BASE_URL . 'movimentacao/novo');
            exit;
        }

        // 3. Registra a movimentação como PENDENTE
        $result = $this->registrarMovimentacao($matricula, $ccOrigem, $ccDestino, 'Transferência', $motivo, $userId, 'Pendente');

        if ($result) {
            // Atualiza o status do colaborador para 'Transferido' para bloquear novas edições até aprovação
            $this->colaboradorModel->updateStatus($matricula, 'Transferido');
            
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Solicitação de Transferência registrada com sucesso. Aguardando aprovação do RH.'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao registrar a solicitação de transferência.'];
        }

        header('Location: ' . BASE_URL . 'movimentacao');
        exit;
    }
    
    /**
     * Função auxiliar que loga a movimentação no Model.
     */
    private function registrarMovimentacao($matricula, $ccOrigem, $ccDestino, $tipo, $motivo, $userId, $status) {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO movimentacoes (matricula_colaborador, id_cc_origem, id_cc_destino, tipo_movimentacao, motivo, id_usuario_registro, status_aprovacao) 
                VALUES (:matricula, :origem, :destino, :tipo, :motivo, :user_id, :status)";
        
        $stmt = $db->prepare($sql);
        try {
            return $stmt->execute([
                ':matricula' => $matricula,
                ':origem' => $ccOrigem,
                ':destino' => $ccDestino,
                ':tipo' => $tipo,
                ':motivo' => $motivo,
                ':user_id' => $userId,
                ':status' => $status
            ]);
        } catch (Exception $e) {
            // Tratar erro
            return false;
        }
    }
}