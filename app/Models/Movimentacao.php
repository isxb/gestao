<?php
// app/Models/Movimentacao.php

class Movimentacao {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todas as movimentações pendentes de aprovação.
     * @return array
     */
    public function getPendenciasAprovacao() {
        $sql = "SELECT 
                    m.id_mov, m.data_movimentacao, m.motivo,
                    c.matricula, c.nome AS colaborador_nome,
                    cc_origem.sigla_cc AS cc_origem_sigla,
                    cc_destino.sigla_cc AS cc_destino_sigla,
                    u.nome AS gestor_solicitante
                FROM movimentacoes m
                JOIN colaboradores c ON m.matricula_colaborador = c.matricula
                LEFT JOIN ccustos cc_origem ON m.id_cc_origem = cc_origem.id_cc
                JOIN ccustos cc_destino ON m.id_cc_destino = cc_destino.id_cc
                JOIN usuarios u ON m.id_usuario_registro = u.id_usuario
                WHERE m.status_aprovacao = 'Pendente'
                ORDER BY m.data_movimentacao DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Aprova ou Rejeita uma movimentação pendente.
     * @param int $movId ID da movimentação.
     * @param string $status 'Aprovada' ou 'Rejeitada'.
     * @param int $rhUserId ID do usuário do RH que executou a ação.
     * @return bool
     */
    public function updateStatus($movId, $status, $rhUserId) {
        $this->db->beginTransaction();
        try {
            // 1. Atualiza o status da movimentação
            $sql = "UPDATE movimentacoes SET 
                    status_aprovacao = :status, 
                    id_usuario_registro = :user_id,
                    data_movimentacao = NOW() -- Marca a data da aprovação/rejeição
                    WHERE id_mov = :id_mov AND status_aprovacao = 'Pendente'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':status' => $status,
                ':user_id' => $rhUserId,
                ':id_mov' => $movId
            ]);
            
            // 2. Se for APROVADA e for TRANSFERÊNCIA, atualiza o colaborador
            if ($status === 'Aprovada') {
                $movimentacao = $this->getMovimentacaoById($movId);
                
                // Verifica se é uma transferência (Origem != Destino)
                if ($movimentacao && $movimentacao['tipo_movimentacao'] === 'Transferência' && $movimentacao['id_cc_origem'] != $movimentacao['id_cc_destino']) {
                    
                    $sqlColab = "UPDATE colaboradores SET 
                                 id_cc_atual = :cc_destino, 
                                 status = 'Ativo' -- Garante que o status volte para Ativo após transferência aprovada
                                 WHERE matricula = :matricula";
                    
                    $stmtColab = $this->db->prepare($sqlColab);
                    $stmtColab->execute([
                        ':cc_destino' => $movimentacao['id_cc_destino'],
                        ':matricula' => $movimentacao['matricula_colaborador']
                    ]);
                }
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
     * Retorna detalhes de uma movimentação.
     */
    public function getMovimentacaoById($movId) {
        $sql = "SELECT * FROM movimentacoes WHERE id_mov = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $movId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Retorna o histórico completo de movimentação (para relatórios/auditoria).
     */
    public function getHistoricoCompleto($filters = []) {
        // Implementação da busca complexa para Relatórios de Auditoria
        $where = " WHERE 1=1 ";
        $params = [];
        
        // Filtro por intervalo de datas de movimentação
        if (!empty($filters['data_min'])) {
            $where .= " AND m.data_movimentacao >= :data_min";
            $params[':data_min'] = $filters['data_min'];
        }
        if (!empty($filters['data_max'])) {
            // Adiciona 1 dia (23:59:59) para incluir o dia inteiro no filtro de data_max
            $data_max_end_of_day = $filters['data_max'] . ' 23:59:59';
            $where .= " AND m.data_movimentacao <= :data_max";
            $params[':data_max'] = $data_max_end_of_day;
        }
        
        // FILTRO: Tipo de Movimentação
        if (!empty($filters['tipo'])) {
            $where .= " AND m.tipo_movimentacao = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }
        
        // Consulta base para o 
        $sql = "SELECT 
                    m.*, 
                    c.nome AS colaborador_nome, c.matricula, c.funcao, 
                    cc_origem.sigla_cc AS cc_origem_sigla, 
                    cc_destino.sigla_cc AS cc_destino_sigla,
                    u.nome AS usuario_registro
                FROM movimentacoes m
                JOIN colaboradores c ON m.matricula_colaborador = c.matricula
                LEFT JOIN ccustos cc_origem ON m.id_cc_origem = cc_origem.id_cc
                LEFT JOIN ccustos cc_destino ON m.id_cc_destino = cc_destino.id_cc
                JOIN usuarios u ON m.id_usuario_registro = u.id_usuario
                " . $where . "
                ORDER BY m.data_movimentacao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}