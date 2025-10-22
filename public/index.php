<?php
// public/index.php
session_start();

// ----------------------------------------------------------------------
// 1. VERIFICAÇÃO DE INSTALAÇÃO (Se Config.php não existe, roda o Setup)
// ----------------------------------------------------------------------
if (!file_exists('../app/Config.php')) {
    // Definimos caminhos mínimos antes do Config.php existir
    define('ROOT_PATH', dirname(__DIR__) . '/');
    define('APP_PATH', ROOT_PATH . 'app/');
    define('CONTROLLER_PATH', APP_PATH . 'Controllers/');

    $controllerName = 'SetupController';
    $method = 'index';
    
    // Autoloader Simples para SetupController
    $setup_controller_path = CONTROLLER_PATH . 'SetupController.php';
    if (!file_exists($setup_controller_path)) {
        die("Erro fatal: O arquivo SetupController não foi encontrado.");
    }
    require_once($setup_controller_path);
    
    // Ajuste o método se a rota for setup/run
    $uri_segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
    if (isset($uri_segments[1]) && $uri_segments[1] === 'run') {
        $method = 'run';
    }

    $controller = new $controllerName();
    $controller->$method();
    exit; // Termina o script após rodar o setup
}
// ----------------------------------------------------------------------

// Se Config.php existe, carrega as configurações
require_once('../app/Config.php');

/**
 * Autoloader Básico (MANUAL)
 * Carrega classes automaticamente de Models e Controllers.
 * ESSENCIAL para projetos sem Composer instalado.
 */
spl_autoload_register(function ($class_name) {
    // Caminho da classe no diretório de Models
    $model_path = MODEL_PATH . $class_name . '.php';
    if (file_exists($model_path)) {
        require_once $model_path;
        return;
    }

    // Caminho da classe no diretório de Controllers
    $controller_path = CONTROLLER_PATH . $class_name . '.php';
    if (file_exists($controller_path)) {
        require_once $controller_path;
        return;
    }
});


// --------------------------------------------------------------
// 2. Roteamento (Router Simples)
// --------------------------------------------------------------

// Remove a URL base (BASE_URL) da URI para simplificar o roteamento
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// O código abaixo garante que a rota seja limpa, ignorando a lógica de base_path complexa
// pois presumimos que BASE_URL é apenas '/'
if (BASE_URL !== '/') {
    // Lógica para subdiretório (apenas em caso de BASE_URL ser algo como '/gestao/')
    $base_path = trim(parse_url(BASE_URL, PHP_URL_PATH), '/');
    if (!empty($base_path) && strpos($uri, $base_path) === 0) {
        $uri = substr($uri, strlen($base_path));
    }
}
$uri = trim($uri, '/');


// Define o Controller, Método e Parâmetros
$segments = explode('/', $uri);
// Converte o primeiro segmento para ControllerName (Ex: colaborador -> ColaboradorController)
$controllerName = !empty($segments[0]) ? ucfirst(strtolower($segments[0])) . 'Controller' : 'DashboardController';
$method = isset($segments[1]) && !empty($segments[1]) ? strtolower($segments[1]) : 'index';
$params = array_slice($segments, 2);

// --- Tratamento especial para URL vazia ou 'login' ---
if (empty($segments[0]) || strtolower($segments[0]) == 'login') {
    if (!isset($_SESSION['user_id'])) {
        // Se URL vazia ou 'login' e não está logado
        $controllerName = 'AuthController';
        $method = 'login';
    } else {
        // Se URL vazia e está logado, vai para o dashboard
        $controllerName = 'DashboardController';
        $method = 'index';
    }
} else if (strtolower($segments[0]) == 'auth' && isset($segments[1]) && strtolower($segments[1]) == 'logout') {
    // Garante que o logout funcione
    $controllerName = 'AuthController';
    $method = 'logout';
}
// --- FIM do Tratamento ---


// --------------------------------------------------------------
// CORREÇÃO DE CASE SENSITIVITY (RHController, CCustoController)
// --------------------------------------------------------------
// O roteador gera 'RhController' ou 'CcustoController', mas o nome da classe é todo maiúsculo/correto.
if ($controllerName === 'RhController') {
    $controllerName = 'RHController';
}
if ($controllerName === 'CcustoController') {
    $controllerName = 'CCustoController';
}

// 3. Executa o Controller
if (class_exists($controllerName)) {
    $controller = new $controllerName();
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        // Método não encontrado - erro 404 simples
        http_response_code(404);
        echo "<h1>404 - Método '$method' não encontrado no Controller '$controllerName'.</h1>";
    }
} else {
    // Controller não encontrado - erro 404 simples
    http_response_code(404);
    echo "<h1>404 - Página '$controllerName' não encontrada!</h1>";
}