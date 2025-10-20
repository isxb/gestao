<?php
// app/Controllers/SetupController.php

class SetupController {

    private $db_connection = null;

    /**
     * Exibe o formulário de configuração inicial.
     * Rota: BASE_URL/setup
     */
    public function index() {
        // Redireciona para o login se o Config.php já existir
        if (file_exists(APP_PATH . 'Config.php')) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        // Valores de fallback para o formulário
        $error_message = $_GET['error'] ?? null;
        $db_host = 'localhost';
        $db_name = 'junio413_gestao';
        $db_user = 'junio413_gestao'; // Usuário com prefixo de host é o mais comum
        $db_pass = '';
        $admin_nome = 'Admin Mestre';
        $admin_email = 'admin@reframax.com.br';
        
        require_once(VIEW_PATH . 'setup/index.php');
    }

    /**
     * Processa a submissão do formulário e executa a instalação.
     * Rota: BASE_URL/setup/run
     */
    public function run() {
        // Garantir que é um POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'setup');
            exit;
        }

        // 1. Coleta e sanitiza os dados do Formulário
        $db_host = trim($_POST['db_host'] ?? 'localhost');
        $db_name = trim($_POST['db_name'] ?? 'reframax_gestivo');
        $db_user = trim($_POST['db_user'] ?? 'root');
        $db_pass = $_POST['db_pass'] ?? '';
        
        $admin_nome_input = trim($_POST['admin_nome'] ?? '');
        $admin_email_input = trim($_POST['admin_email'] ?? '');
        $admin_password_input = $_POST['admin_password'] ?? '';

        // --- DEFINIÇÃO DO ADMIN (FALLBACK para credenciais padrão) ---
        $admin_nome = !empty($admin_nome_input) ? $admin_nome_input : 'Admin Mestre';
        $admin_email = !empty($admin_email_input) ? $admin_email_input : 'admin@reframax.com.br';
        // Senha padrão forte de fallback: 'Reframax2025'
        $admin_password = !empty($admin_password_input) ? $admin_password_input : 'Reframax2025'; 
        // -----------------------------------------------------------

        // Validação mínima
        if (empty($db_host) || empty($db_name) || empty($db_user)) {
             $error_message = "Credenciais do Banco de Dados incompletas.";
             require_once(VIEW_PATH . 'setup/index.php');
             return;
        }

        // 2. Tenta conectar e criar as tabelas
        try {
            // Tenta conectar ao servidor de banco de dados
            $this->db_connection = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
            $this->db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Cria o banco de dados se não existir (Útil em ambiente localhost/desenvolvimento)
            $this->db_connection->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

            // Reconecta-se ao banco de dados específico
            $this->db_connection = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $this->db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Executa o script SQL (criação de tabelas e C.C.s iniciais)
            $sql_file = APP_PATH . 'setup/database_setup.sql';
            if (!file_exists($sql_file)) {
                 throw new Exception("Arquivo de setup SQL não encontrado.");
            }
            // Lê o conteúdo do arquivo SQL
            $sql_content = file_get_contents($sql_file);
            // Executa o script (pode demorar dependendo do tamanho)
            $this->db_connection->exec($sql_content);

        } catch (PDOException $e) {
            $error_message = "Erro de conexão/DB: Verifique suas credenciais de banco de dados e se o usuário tem privilégios totais. Mensagem: " . $e->getMessage();
            require_once(VIEW_PATH . 'setup/index.php');
            return;
        } catch (Exception $e) {
            $error_message = "Erro fatal na instalação: " . $e->getMessage();
            require_once(VIEW_PATH . 'setup/index.php');
            return;
        }

        // 3. Cria o arquivo app/Config.php
        $this->createConfigFile($db_host, $db_name, $db_user, $db_pass);
        
        // Agora que Config.php existe, podemos usar as constantes do sistema
        require_once(APP_PATH . 'Config.php');
        
        // 4. Insere o Admin inicial
        try {
            // Hash da senha
            $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);
            
            // BUSCA O C.C. 'ADM' (C.C. Principal do Admin)
            $sql_get_cc_admin = "SELECT id_cc FROM ccustos WHERE sigla_cc = 'ADM'";
            $stmt_cc = $this->db_connection->query($sql_get_cc_admin);
            $cc_admin = $stmt_cc->fetch(PDO::FETCH_ASSOC);
            $cc_id = $cc_admin['id_cc'] ?? null; // ID do CC 'ADM'

            // Insere o usuário Admin
            $sql_admin = "INSERT INTO usuarios (nome, email, senha, funcao, id_cc_principal, nivel_acesso) 
                          VALUES (:nome, :email, :senha, 'Admin Geral', :cc_id, 'Admin')";
            $stmt = $this->db_connection->prepare($sql_admin);
            $stmt->execute([
                ':nome' => $admin_nome, 
                ':email' => $admin_email, 
                ':senha' => $hashed_password, 
                ':cc_id' => $cc_id
            ]);
            $admin_id = $this->db_connection->lastInsertId();

            // Libera TODOS os C.C.s para o Admin Mestre
            $sql_libera = "INSERT INTO usuario_cc_liberado (id_usuario, id_cc) 
                           SELECT :user_id, id_cc FROM ccustos";
            $stmt_libera = $this->db_connection->prepare($sql_libera);
            $stmt_libera->execute([':user_id' => $admin_id]);

        } catch (Exception $e) {
             $error_message = "Erro ao inserir dados do Admin. Mensagem: " . $e->getMessage();
             // Em um caso real, deveria ser feito um rollback no banco também.
             require_once(VIEW_PATH . 'setup/index.php');
             return;
        }
        
        // 5. Sucesso: Redireciona para o login
        $_SESSION['setup_complete'] = true;
        header('Location: ' . BASE_URL . 'login?status=installed');
        exit;
    }

    /**
     * Gera e salva o arquivo Config.php com as credenciais fornecidas.
     */
    private function createConfigFile($db_host, $db_name, $db_user, $db_pass) {
        $content = "<?php
// app/Config.php - Arquivo gerado automaticamente em " . date('Y-m-d H:i:s') . "

date_default_timezone_set('America/Sao_Paulo');

// **ATENÇÃO: BASE_URL deve ser a raiz do seu site (normalmente '/')**
define('BASE_URL', '/'); 

// ------------------------------------------------------------------
// Configurações do Banco de Dados (MySQL)
// ------------------------------------------------------------------
define('DB_HOST', '" . addslashes($db_host) . "');
define('DB_NAME', '" . addslashes($db_name) . "');
define('DB_USER', '" . addslashes($db_user) . "');
define('DB_PASS', '" . addslashes($db_pass) . "');

// ------------------------------------------------------------------
// Níveis de Acesso (Para consistência no código e DB)
// ------------------------------------------------------------------
const ACESS_LEVELS = [
    'ADMIN'      => 4, 
    'RH'         => 3, 
    'GESTOR'     => 2, 
    'COLABORADOR' => 1
];

// ------------------------------------------------------------------
// Configurações de Segurança
// ------------------------------------------------------------------
define('SESSION_NAME', 'reframax_sess');
define('SESSION_EXPIRY', 3600);

// ------------------------------------------------------------------
// Caminhos do Sistema (Não altere, facilita o 'require' e 'include')
// ------------------------------------------------------------------
// Usa __DIR__ para ser o caminho da pasta APP/
define('APP_PATH', __DIR__ . '/');
define('ROOT_PATH', dirname(APP_PATH) . '/');
define('VIEW_PATH', APP_PATH . 'Views/');
define('MODEL_PATH', APP_PATH . 'Models/');
define('CONTROLLER_PATH', APP_PATH . 'Controllers/');
";
        // Salva o arquivo. Necessário permissão de escrita na pasta 'app/'
        file_put_contents(APP_PATH . 'Config.php', $content);
    }
}