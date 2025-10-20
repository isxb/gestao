<?php
// app/Controllers/ColaboradorController.php

class ColaboradorController {
    
    private $colaboradorModel;
    private $ccustoModel;

    public function __construct() {
        // Proteção de rota global para este módulo
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        // Acesso mínimo para Colaboradores é visualizar, mas RH/Gestor que gerenciam
        $allowedAccess = ['Admin', 'RH', 'Gestor'];
        if (!in_array($_SESSION['access_level'], $allowedAccess)) {
            // Se não for Admin, RH ou Gestor, não pode acessar o módulo
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->colaboradorModel = new Colaborador();
        $this->ccustoModel = new CCusto(); // Necessário para filtros e dropdowns
    }

    /**
     * Exibe a lista principal de colaboradores com filtros.
     * Rota: BASE_URL/colaboradores
     */
    public function index() {
        // Obter os filtros da URL (GET)
        $filters = $_GET ?? [];
        $currentPage = intval($filters['page'] ?? 1);
        $limit = 20;
        
        // Define os C.C. que o usuário tem permissão para visualizar
        $cc_permitidos = ($_SESSION['access_level'] == 'Admin' || $_SESSION['access_level'] == 'RH') 
            ? null 
            : $_SESSION['cc_liberados'];

        // 1. Busca os dados dos colaboradores
        $result = $this->colaboradorModel->getAllWithFilters($filters, $cc_permitidos, $limit, $currentPage);
        $colaboradores = $result['data'];
        $totalRecords = $result['total'];
        $totalPages = ceil($totalRecords / $limit);
        
        // 2. Busca dados auxiliares para os filtros (dropdowns)
        $centrosCusto = $this->ccustoModel->getAll();
        $funcoesDisponiveis = $this->colaboradorModel->getDistinctFuncoes();

        // 3. Carrega a view de listagem
        require_once(VIEW_PATH . 'colaboradores/index.php');
    }

    /**
     * Exibe o formulário para cadastro de um novo colaborador.
     * Rota: BASE_URL/colaboradores/novo
     */
    public function novo() {
        // Apenas Admin e RH podem cadastrar novos (Gestor só transfere/edita status no C.C. dele)
        if ($_SESSION['access_level'] != 'Admin' && $_SESSION['access_level'] != 'RH') {
            header('Location: ' . BASE_URL . 'colaboradores');
            exit;
        }
        
        $colaborador = []; // Array vazio para novo cadastro
        $centrosCusto = $this->ccustoModel->getAllActive();

        require_once(VIEW_PATH . 'colaboradores/form.php');
    }

    /**
     * Exibe o formulário para edição de um colaborador existente.
     * Rota: BASE_URL/colaboradores/editar/{matricula}
     */
    public function editar($matricula = null) {
        if (!$matricula) {
            header('Location: ' . BASE_URL . 'colaboradores');
            exit;
        }
        
        // Buscar dados do colaborador
        $colaborador = $this->colaboradorModel->getByMatricula($matricula);
        if (!$colaborador) {
            // Tratar erro 404
            echo "Colaborador não encontrado!";
            exit;
        }

        $centrosCusto = $this->ccustoModel->getAllActive();

        // Verificar permissão de edição (Gestor só edita se o C.C. do colab. estiver na sua lista de liberados)
        if ($_SESSION['access_level'] == 'Gestor') {
            if (!in_array($colaborador['id_cc_atual'], $_SESSION['cc_liberados'])) {
                 header('Location: ' . BASE_URL . 'colaboradores');
                 $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Você não tem permissão para editar colaboradores fora dos seus Centros de Custo liberados.'];
                 exit;
            }
        }

        require_once(VIEW_PATH . 'colaboradores/form.php');
    }

    /**
     * Processa o salvamento (novo ou edição) de um colaborador.
     * Rota: POST /colaboradores/salvar
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'colaboradores');
            exit;
        }

        $data = $_POST;
        $matricula = $data['matricula'] ?? null;

        // Validações básicas (exemplo)
        if (empty($data['nome']) || empty($data['data_admissao']) || empty($data['id_cc_atual'])) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Preencha os campos obrigatórios.'];
            // Se for novo, redireciona para 'novo', se for edição, para 'editar'
            header('Location: ' . BASE_URL . 'colaboradores/' . ($matricula ? 'editar/' . $matricula : 'novo'));
            exit;
        }
        
        $isUpdate = !empty($matricula);
        
        // Chamada ao Model para persistência
        $result = $isUpdate 
            ? $this->colaboradorModel->update($matricula, $data, $_SESSION['user_id']) 
            : $this->colaboradorModel->create($data, $_SESSION['user_id']);

        if ($result) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Colaborador ' . ($isUpdate ? 'atualizado' : 'cadastrado') . ' com sucesso.'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao salvar o colaborador. Verifique os dados.'];
        }

        header('Location: ' . BASE_URL . 'colaboradores');
        exit;
    }

    /**
     * Remove um colaborador. (Apenas Admin/RH)
     * Rota: GET /colaboradores/excluir/{matricula}
     */
    public function excluir($matricula = null) {
        if ($_SESSION['access_level'] != 'Admin' && $_SESSION['access_level'] != 'RH') {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Você não tem permissão para excluir colaboradores.'];
            header('Location: ' . BASE_URL . 'colaboradores');
            exit;
        }

        if ($matricula && $this->colaboradorModel->delete($matricula)) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Colaborador removido (status Desligado).'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao remover colaborador.'];
        }

        header('Location: ' . BASE_URL . 'colaboradores');
        exit;
    }
}