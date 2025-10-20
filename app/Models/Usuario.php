<?php
// app/Models/Usuario.php (Aprimorado)

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection(); 
    }

    // --- FUNÇÕES DE AUTENTICAÇÃO (MANTIDAS) ---
    // public function findByEmail($email) { ... }
    // public function getLiberatedCCs($userId) { ... }


    // --- FUNÇÕES DE GERENCIAMENTO (CRUD) ---

    /**
     * Retorna todos os usuários com detalhes (C.C. Principal).
     */
    public function getAllWithDetails($filters = []) {
        $sql = "SELECT u.*, cc.sigla_cc AS cc_principal_sigla 
                FROM usuarios u
                LEFT JOIN ccustos cc ON u.id_cc_principal = cc.id_cc 
                ORDER BY u.nome ASC";
        
        // NOTA: Implementar lógica de filtros (funcao, nivel_acesso) aqui, similar ao ColaboradorModel.
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Retorna um usuário pelo ID.
     */
    public function getById($id) {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Cria um novo usuário e define seus C.C.s liberados.
     */
    public function create($data, $cc_liberados = []) {
        $this->db->beginTransaction();
        try {
            // 1. Insere o Usuário
            $sql = "INSERT INTO usuarios (nome, email, senha, funcao, id_cc_principal, nivel_acesso) 
                    VALUES (:nome, :email, :senha, :funcao, :id_cc_principal, :nivel_acesso)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nome' => $data['nome'],
                ':email' => $data['email'],
                ':senha' => $data['senha_hashed'], // Já hasheada no Controller
                ':funcao' => $data['funcao'] ?? 'Colaborador',
                ':id_cc_principal' => $data['id_cc_principal'] ?? null,
                ':nivel_acesso' => $data['nivel_acesso'] ?? 'Colaborador',
            ]);
            $userId = $this->db->lastInsertId();

            // 2. Define os C.C.s Liberados
            $this->syncCCLiberados($userId, $cc_liberados);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Logar o erro (ex: e-mail duplicado)
            return false;
        }
    }

    /**
     * Atualiza um usuário e define seus C.C.s liberados.
     */
    public function update($id, $data, $cc_liberados = []) {
        $this->db->beginTransaction();
        try {
            // 1. Atualiza o Usuário (apenas campos que não são a senha)
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, funcao = :funcao, 
                    id_cc_principal = :id_cc_principal, nivel_acesso = :nivel_acesso";
            
            $params = [
                ':nome' => $data['nome'],
                ':email' => $data['email'],
                ':funcao' => $data['funcao'] ?? 'Colaborador',
                ':id_cc_principal' => $data['id_cc_principal'] ?? null,
                ':nivel_acesso' => $data['nivel_acesso'] ?? 'Colaborador',
                ':id_usuario' => $id
            ];

            // Se a senha foi fornecida, inclui o hash na query
            if ($data['senha_hashed']) {
                $sql .= ", senha = :senha";
                $params[':senha'] = $data['senha_hashed'];
            }
            
            $sql .= " WHERE id_usuario = :id_usuario";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // 2. Sincroniza os C.C.s Liberados
            $this->syncCCLiberados($id, $cc_liberados);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Logar o erro
            return false;
        }
    }

    /**
     * Sincroniza os Centros de Custo Liberados do usuário (DELETE e INSERT).
     */
    private function syncCCLiberados($userId, $cc_liberados) {
        // 1. Deleta todas as permissões existentes
        $sqlDelete = "DELETE FROM usuario_cc_liberado WHERE id_usuario = :user_id";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->execute([':user_id' => $userId]);

        // 2. Insere as novas permissões
        if (!empty($cc_liberados) && is_array($cc_liberados)) {
            $sqlInsert = "INSERT INTO usuario_cc_liberado (id_usuario, id_cc) VALUES (:user_id, :cc_id)";
            $stmtInsert = $this->db->prepare($sqlInsert);
            
            foreach ($cc_liberados as $cc_id) {
                if (is_numeric($cc_id)) { // Garante que o ID é válido
                    $stmtInsert->execute([':user_id' => $userId, ':cc_id' => $cc_id]);
                }
            }
        }
    }

    /**
     * Exclui um usuário e suas permissões de C.C.
     */
    public function delete($id) {
        $this->db->beginTransaction();
        try {
            // 1. Remove os C.C.s Liberados (FOREIGN KEY)
            $this->db->prepare("DELETE FROM usuario_cc_liberado WHERE id_usuario = :id")->execute([':id' => $id]);
            // 2. Remove o Usuário
            $this->db->prepare("DELETE FROM usuarios WHERE id_usuario = :id")->execute([':id' => $id]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Logar o erro
            return false;
        }
    }
}