<?php
// app/Models/Database.php
require_once(APP_PATH . 'Config.php');

/**
 * Classe Database
 * Responsável pela conexão com o MySQL usando PDO.
 * Padrão Singleton para garantir apenas uma conexão.
 */
class Database {
    private static $instance = null;
    private $conn;

    /**
     * Construtor: Cria a conexão PDO.
     * Protegido para forçar o uso do método getInstance (Singleton).
     */
    protected function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Em ambiente de produção, logar o erro e mostrar uma mensagem genérica
            die('Falha na Conexão com o Banco de Dados: ' . $e->getMessage());
        }
    }

    /**
     * Retorna a única instância da conexão (Singleton Pattern).
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Retorna a conexão PDO para ser usada nos Models.
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Método genérico para execução de consultas (SELECT, INSERT, UPDATE, DELETE).
     * @param string $sql A consulta SQL.
     * @param array $params Os parâmetros para a consulta preparada.
     * @return PDOStatement|bool
     */
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}