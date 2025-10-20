<?php
// app/Controllers/AuthController.php

class AuthController {
    private $usuarioModel;

    public function __construct() {
        // Inicializa o Model de Usuário para interação com o DB
        // NOTE: Database.php deve ser chamado implicitamente pelo Autoloader antes que Usuario.php seja instanciado
        $this->usuarioModel = new Usuario(); 
    }

    /**
     * Exibe a tela de login e processa a submissão do formulário.
     * Rota: BASE_URL/login
     */
    public function login() {
        // Se o usuário já estiver logado, redireciona para o dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $error_message = '';
        
        // Verifica se houve uma tentativa de submissão do formulário
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Tenta autenticar
            $auth_result = $this->authenticate($email, $password);

            if ($auth_result === true) {
                // Autenticação bem-sucedida, o método authenticate já iniciou a sessão e redirecionou
            } else {
                $error_message = $auth_result; // Retorna a mensagem de erro
            }
        }
        
        // Carrega a view de login
        require_once(VIEW_PATH . 'auth/login.php');
    }

    /**
     * Processa a autenticação do usuário.
     * @param string $email
     * @param string $password
     * @return true|string Retorna true em sucesso ou a mensagem de erro em caso de falha.
     */
    private function authenticate($email, $password) {
        if (empty($email) || empty($password)) {
            return 'Por favor, preencha o e-mail e a senha.';
        }

        // 1. Busca o usuário pelo e-mail
        $user = $this->usuarioModel->findByEmail($email);

        if (!$user) {
            return 'Credenciais inválidas. Verifique seu e-mail e senha.';
        }

        // 2. Verifica a senha
        if (password_verify($password, $user['senha'])) {
            
            // 3. Autenticação bem-sucedida: Inicia a sessão
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['access_level'] = $user['nivel_acesso'];
            $_SESSION['cc_principal'] = $user['id_cc_principal'];
            
            // 4. Carrega C.C. liberados 
            $_SESSION['cc_liberados'] = $this->usuarioModel->getLiberatedCCs($user['id_usuario']); 

            // Redireciona para o dashboard
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        } else {
            // Senha incorreta
            return 'Credenciais inválidas. Verifique seu e-mail e senha.';
        }
    }

    /**
     * Encerra a sessão do usuário.
     * Rota: BASE_URL/auth/logout
     */
    public function logout() {
        // Destrói a sessão
        session_destroy();
        $_SESSION = array(); 
        
        // Redireciona para a página de login
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}