<?php
// app/Models/Colaborador.php (Aprimorado)

class Colaborador {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- FUNÇÕES DE LISTAGEM E FILTROS ---

    /**
     * Retorna todos os colaboradores com filtros avançados e paginação.
     * @param array $filters Filtros GET (funcao, status, cc, datas, etc.)
     * @param array|null $cc_permitidos Lista de IDs de C.C. que o usuário pode ver.
     * @param int $limit Limite de registros por página.
     * @param int $page Página atual.
     * @return array Dados e total de registros.
     */
    public function getAllWithFilters($filters, $cc_permitidos, $limit, $page) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $where = " WHERE 1=1 ";

        // 1. Constrói a cláusula WHERE baseada nos filtros
        if (!empty($filters['search'])) {
            $where .= " AND (c.nome LIKE :search OR c.matricula LIKE :search_matricula)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search_matricula'] = $filters['search'] . '%';
        }
        if (!empty($filters['funcao'])) {
            $where .= " AND c.funcao = :funcao";
            $params[':funcao'] = $filters['funcao'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'Todos') {
            $where .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['cc'])) {
            $where .= " AND cc.id_cc = :cc_id";
            $params[':cc_id'] = $filters['cc'];
        }
        // Exemplo: Filtrar por data de admissão
        if (!empty($filters['data_admissao_min'])) {
            $where .= " AND c.data_admissao >= :data_min";
            $params[':data_min'] = $filters['data_admissao_min'];
        }

        // Aplica o filtro de permissão de C.C. para Gestores
        if ($cc_permitidos && is_array($cc_permitidos) && count($cc_permitidos) > 0) {
            $placeholders = implode(',', array_fill(0, count($cc_permitidos), '?'));
            $where .= " AND c.id_cc_atual IN ({$placeholders})";
            // Adiciona os IDs dos C.C. permitidos aos parâmetros, mas como PDO não suporta named placeholders em IN,
            // devemos ajustar o array $params ou usar prepared statements puros. Por simplicidade, vamos injetar os IDs na query.
            // Para produção, o ideal é usar uma biblioteca que trate isso ou usar parâmetros dinâmicos.
            // Aqui, usamos um método simples, assumindo que $cc_permitidos contém apenas inteiros validados.
            $where .= " AND c.id_cc_atual IN (" . implode(',', array_map('intval', $cc_permitidos)) . ")";
        }

        // 2. Consulta para o TOTAL (para paginação)
        $sqlTotal = "SELECT COUNT(c.matricula) FROM colaboradores c JOIN ccustos cc ON c.id_cc_atual = cc.id_cc" . $where;
        $stmtTotal = $this->db->prepare($sqlTotal);
        // Bind parameters for total query (excluindo os C.C. injetados)
        foreach ($params as $key => &$val) { $stmtTotal->bindParam($key, $val); }
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        // 3. Consulta para os DADOS (com LIMIT e OFFSET)
        $sqlData = "SELECT c.*, cc.sigla_cc, cc.nome_cc 
                    FROM colaboradores c 
                    JOIN ccustos cc ON c.id_cc_atual = cc.id_cc 
                    " . $where . " 
                    ORDER BY c.nome ASC 
                    LIMIT :limit OFFSET :offset";
        
        $stmtData = $this->db->prepare($sqlData);
        // Bind parameters for data query
        foreach ($params as $key => &$val) { $stmtData->bindParam($key, $val); }
        $stmtData->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmtData->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmtData->execute();

        return [
            'data' => $stmtData->fetchAll(),
            'total' => $totalRecords
        ];
    }
    
    /**
     * Retorna um array de todas as funções distintas presentes no sistema.
     */
    public function getDistinctFuncoes() {
        $sql = "SELECT DISTINCT funcao FROM colaboradores ORDER BY funcao ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    // --- FUNÇÕES DE CRUD ---

    /**
     * Busca um colaborador pela matrícula.
     */
    public function getByMatricula($matricula) {
        $sql = "SELECT * FROM colaboradores WHERE matricula = :matricula";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':matricula', $matricula, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Cria um novo colaborador.
     * @param array $data Dados do formulário.
     * @param int $userId ID do usuário que registrou a ação.
     * @return bool Sucesso/falha.
     */
    public function create($data, $userId) {
        // Iniciar Transação
        $this->db->beginTransaction();
        try {
            // 1. Insere o Colaborador
            $sql = "INSERT INTO colaboradores (matricula, nome, funcao, data_admissao, id_cc_atual, tipo_contrato, status) 
                    VALUES (:matricula, :nome, :funcao, :data_admissao, :id_cc_atual, :tipo_contrato, 'Ativo')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':matricula' => $data['matricula'],
                ':nome' => $data['nome'],
                ':funcao' => $data['funcao'] ?? 'Não Definido',
                ':data_admissao' => $data['data_admissao'],
                ':id_cc_atual' => $data['id_cc_atual'],
                ':tipo_contrato' => $data['tipo_contrato'] ?? 'CLT',
            ]);

            // 2. Registra a movimentação inicial (Admissão)
            $this->logMovimentacao($data['matricula'], null, $data['id_cc_atual'], 'Admissão', 'Primeiro cadastro do colaborador.', $userId);
            
            // 3. Commit
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            // Logar o erro $e->getMessage()
            return false;
        }
    }

    /**
     * Atualiza um colaborador e registra a movimentação, se o C.C. mudar.
     * @param int $matricula
     * @param array $data
     * @param int $userId
     * @return bool
     */
    public function update($matricula, $data, $userId) {
        $colabAntigo = $this->getByMatricula($matricula);
        if (!$colabAntigo) return false;

        $this->db->beginTransaction();
        try {
            // 1. Atualiza o Colaborador
            $sql = "UPDATE colaboradores SET 
                    nome = :nome, funcao = :funcao, data_admissao = :data_admissao, 
                    id_cc_atual = :id_cc_atual, tipo_contrato = :tipo_contrato, status = :status
                    WHERE matricula = :matricula";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':matricula' => $matricula,
                ':nome' => $data['nome'],
                ':funcao' => $data['funcao'] ?? $colabAntigo['funcao'],
                ':data_admissao' => $data['data_admissao'],
                ':id_cc_atual' => $data['id_cc_atual'],
                ':tipo_contrato' => $data['tipo_contrato'] ?? $colabAntigo['tipo_contrato'],
                ':status' => $data['status'] ?? $colabAntigo['status'],
            ]);

            // 2. Se o Centro de Custo MUDOU ou o STATUS MUDOU, registra a movimentação
            if ($colabAntigo['id_cc_atual'] != $data['id_cc_atual']) {
                $this->logMovimentacao($matricula, $colabAntigo['id_cc_atual'], $data['id_cc_atual'], 'Transferência', $data['motivo_movimentacao'] ?? 'Transferência de C.C. via edição.', $userId);
            }
            if ($colabAntigo['status'] != $data['status']) {
                 $this->logMovimentacao($matricula, $colabAntigo['id_cc_atual'], $colabAntigo['id_cc_atual'], ($data['status'] == 'Desligado' ? 'Desligamento' : 'Status Alterado'), 'Status alterado de ' . $colabAntigo['status'] . ' para ' . $data['status'] . '.', $userId);
            }

            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            // Logar o erro
            return false;
        }
    }

    /**
     * Altera o status para 'Desligado'.
     */
    public function delete($matricula) {
        // Não deletamos o registro, apenas alteramos o status para manter o histórico
        $colabAntigo = $this->getByMatricula($matricula);
        if (!$colabAntigo) return false;

        $this->db->beginTransaction();
        try {
            $sql = "UPDATE colaboradores SET status = 'Desligado' WHERE matricula = :matricula";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':matricula', $matricula, PDO::PARAM_INT);
            $stmt->execute();
            
            // Registra o Desligamento
            $this->logMovimentacao($matricula, $colabAntigo['id_cc_atual'], $colabAntigo['id_cc_atual'], 'Desligamento', 'Colaborador desligado do sistema.', $_SESSION['user_id']);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Função auxiliar para registrar todas as alterações de C.C./Status.
     */
    private function logMovimentacao($matricula, $ccOrigem, $ccDestino, $tipo, $motivo, $userId) {
        $sql = "INSERT INTO movimentacoes (matricula_colaborador, id_cc_origem, id_cc_destino, tipo_movimentacao, motivo, id_usuario_registro) 
                VALUES (:matricula, :origem, :destino, :tipo, :motivo, :user_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':matricula' => $matricula,
            ':origem' => $ccOrigem,
            ':destino' => $ccDestino,
            ':tipo' => $tipo,
            ':motivo' => $motivo,
            ':user_id' => $userId
        ]);
    }
    
    // --- Funções de Dashboard já implementadas (getTotalAtivos, getDistribuicaoPorCC, etc.) ---
    // ...
}