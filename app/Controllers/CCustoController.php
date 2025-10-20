<?php
// app/Controllers/CCustoController.php

class CCustoController {
    
    private $ccustoModel;

    public function __construct() {
        // --- PROTEÇÃO DE ROTA (Apenas Admin e RH) ---
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $allowedAccess = ['Admin', 'RH'];
        if (!in_array($_SESSION['access_level'], $allowedAccess)) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Acesso negado. Gerenciamento de C.C. é exclusivo do RH/Admin.'];
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->ccustoModel = new CCusto();
    }

    /**
     * Exibe a lista principal de Centros de Custo.
     * Rota: BASE_URL/ccusto
     */
    public function index() {
        // Busca todos os C.C. (ativos e inativos)
        $ccustos = $this->ccustoModel->getAll();

        require_once(VIEW_PATH . 'ccusto/index.php');
    }

    /**
     * Exibe o formulário para cadastro/edição de um C.C.
     * Rota: BASE_URL/ccusto/novo ou BASE_URL/ccusto/editar/{id}
     */
    public function form($id = null) {
        $ccusto = [];
        if ($id) {
            $ccusto = $this->ccustoModel->getById($id);
            if (!$ccusto) {
                $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Centro de Custo não encontrado.'];
                header('Location: ' . BASE_URL . 'ccusto');
                exit;
            }
        }
        
        require_once(VIEW_PATH . 'ccusto/form.php');
    }

    /**
     * Processa o salvamento (novo ou edição) de um C.C.
     * Rota: POST /ccusto/salvar
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'ccusto');
            exit;
        }

        $data = $_POST;
        $id_cc = $data['id_cc'] ?? null;
        
        // Validações básicas
        if (empty($data['nome_cc']) || empty($data['sigla_cc'])) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Nome e Sigla do C.C. são obrigatórios.'];
            header('Location: ' . BASE_URL . 'ccusto/form/' . $id_cc);
            exit;
        }
        
        $isUpdate = !empty($id_cc);
        
        // Chamada ao Model para persistência
        $result = $isUpdate 
            ? $this->ccustoModel->update($id_cc, $data) 
            : $this->ccustoModel->create($data);

        if ($result) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Centro de Custo ' . ($isUpdate ? 'atualizado' : 'cadastrado') . ' com sucesso.'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao salvar C.C. Verifique se a Sigla já está em uso.'];
        }

        header('Location: ' . BASE_URL . 'ccusto');
        exit;
    }

    /**
     * Altera o status de um C.C. (Desativa, não exclui, para manter FKs).
     * Rota: GET /ccusto/toggle/{id}
     */
    public function toggleStatus($id = null) {
        if (!$id) {
             header('Location: ' . BASE_URL . 'ccusto');
             exit;
        }
        
        $ccusto = $this->ccustoModel->getById($id);
        if (!$ccusto) {
             $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Centro de Custo não encontrado.'];
             header('Location: ' . BASE_URL . 'ccusto');
             exit;
        }

        $newStatus = ($ccusto['status'] == 'Ativo') ? 'Inativo' : 'Ativo';
        
        if ($this->ccustoModel->updateStatus($id, $newStatus)) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'C.C. ' . $ccusto['sigla_cc'] . ' alterado para ' . $newStatus . '.'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao alterar status do C.C..'];
        }

        header('Location: ' . BASE_URL . 'ccusto');
        exit;
    }
}