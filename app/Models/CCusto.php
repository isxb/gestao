<?php
// app/Models/CCusto.php (CRUD Completo)

class CCusto {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- FUNÇÕES DE READ ---

    /**
     * Retorna todos os Centros de Custo (ativos e inativos).
     */
    public function getAll() {
        $sql = "SELECT id_cc, sigla_cc, nome_cc, status FROM ccustos ORDER BY sigla_cc ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Retorna apenas os Centros de Custo Ativos.
     */
    public function getAllActive() {
        $sql = "SELECT id_cc, sigla_cc, nome_cc FROM ccustos WHERE status = 'Ativo' ORDER BY sigla_cc ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Retorna um C.C. pelo ID.
     */
    public function getById($id) {
        $sql = "SELECT * FROM ccustos WHERE id_cc = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // --- FUNÇÕES DE WRITE (CREATE, UPDATE) ---

    /**
     * Cria um novo Centro de Custo.
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO ccustos (nome_cc, sigla_cc, status) VALUES (:nome, :sigla, :status)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nome' => $data['nome_cc'],
                ':sigla' => $data['sigla_cc'],
                ':status' => $data['status'] ?? 'Ativo',
            ]);
        } catch (Exception $e) {
            // Capturar erro de UNIQUE KEY (sigla_cc duplicada)
            return false;
        }
    }

    /**
     * Atualiza um Centro de Custo.
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE ccustos SET nome_cc = :nome, sigla_cc = :sigla, status = :status WHERE id_cc = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nome' => $data['nome_cc'],
                ':sigla' => $data['sigla_cc'],
                ':status' => $data['status'] ?? 'Ativo',
                ':id' => $id
            ]);
        } catch (Exception $e) {
            // Capturar erro de UNIQUE KEY (sigla_cc duplicada)
            return false;
        }
    }
    
    /**
     * Altera apenas o status de um C.C.
     */
    public function updateStatus($id, $status) {
        try {
            $sql = "UPDATE ccustos SET status = :status WHERE id_cc = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':status' => $status, ':id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    // A função DELETE (exclusão permanente) é desaconselhada devido às chaves estrangeiras.
    // Usamos a função updateStatus para marcar como 'Inativo' em vez de excluir.
}