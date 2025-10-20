<?php
// app/Controllers/UsuarioController.php

class UsuarioController {
    
    private $usuarioModel;
    private $ccustoModel;

    public function __construct() {
        // --- 1. PROTEÇÃO DE ROTA (Apenas Admin e RH podem gerenciar usuários) ---
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $allowedAccess = ['Admin', 'RH'];
        if (!in_array($_SESSION['access_level'], $allowedAccess)) {
            // Se não for Admin ou RH, redireciona para o Dashboard com erro
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Acesso negado. Você não tem permissão para gerenciar usuários.'];
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->usuarioModel = new Usuario();
        $this->ccustoModel = new CCusto();
    }

    /**
     * Exibe a lista principal de usuários com filtros.
     * Rota: BASE_URL/usuarios
     */
    public function index() {
        $filters = $_GET ?? [];
        
        // Busca todos os usuários e seus C.C.s principais/liberados
        $usuarios = $this->usuarioModel->getAllWithDetails($filters);
        $ccustos = $this->ccustoModel->getAll(); // Para filtros e exibição

        require_once(VIEW_PATH . 'usuarios/index.php');
    }

    /**
     * Exibe o formulário para cadastro de um novo usuário.
     * Rota: BASE_URL/usuarios/novo
     */
    public function novo() {
        $usuario = []; // Array vazio para novo cadastro
        $ccustos = $this->ccustoModel->getAllActive(); // C.C.s disponíveis para atribuição
        $cc_liberados = []; // Nenhum liberado inicialmente

        require_once(VIEW_PATH . 'usuarios/form.php');
    }

    /**
     * Exibe o formulário para edição de um usuário existente.
     * Rota: BASE_URL/usuarios/editar/{id}
     */
    public function editar($id = null) {
        if (!$id) {
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }
        
        $usuario = $this->usuarioModel->getById($id);
        if (!$usuario) {
            echo "Usuário não encontrado!";
            exit;
        }

        $ccustos = $this->ccustoModel->getAllActive();
        // Obtém a lista atual de C.C.s liberados para este usuário (apenas os IDs)
        $cc_liberados = $this->usuarioModel->getLiberatedCCs($id); 

        require_once(VIEW_PATH . 'usuarios/form.php');
    }

    /**
     * Processa o salvamento (novo ou edição) de um usuário.
     * Rota: POST /usuarios/salvar
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }

        $data = $_POST;
        $id_usuario = $data['id_usuario'] ?? null;
        $cc_liberados = $data['cc_liberados'] ?? []; // Array de IDs de C.C.

        // Validação de senhas para novo cadastro ou redefinição
        $password = trim($data['password'] ?? '');
        $password_confirm = trim($data['password_confirm'] ?? '');

        if (!$id_usuario && empty($password)) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Para um novo usuário, a senha é obrigatória.'];
            header('Location: ' . BASE_URL . 'usuarios/novo');
            exit;
        }

        if (!empty($password) && $password !== $password_confirm) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'As senhas não coincidem.'];
            header('Location: ' . BASE_URL . 'usuarios/editar/' . $id_usuario);
            exit;
        }

        // Hash da senha se fornecida
        $data['senha_hashed'] = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : null;
        
        $isUpdate = !empty($id_usuario);

        // Chamada ao Model para persistência
        $result = $isUpdate 
            ? $this->usuarioModel->update($id_usuario, $data, $cc_liberados) 
            : $this->usuarioModel->create($data, $cc_liberados);

        if ($result) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Usuário ' . ($isUpdate ? 'atualizado' : 'cadastrado') . ' com sucesso.'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao salvar o usuário. Verifique se o e-mail já está em uso.'];
        }

        header('Location: ' . BASE_URL . 'usuarios');
        exit;
    }

    /**
     * Remove um usuário do sistema.
     * Rota: GET /usuarios/excluir/{id}
     */
    public function excluir($id = null) {
        if (!$id) {
             header('Location: ' . BASE_URL . 'usuarios');
             exit;
        }

        if ($id == $_SESSION['user_id']) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Você não pode excluir sua própria conta de usuário.'];
        } elseif ($this->usuarioModel->delete($id)) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Usuário excluído com sucesso.'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao excluir usuário.'];
        }

        header('Location: ' . BASE_URL . 'usuarios');
        exit;
    }
}