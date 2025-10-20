<?php
// app/Config.php

/**
 * Configurações Gerais do Sistema Reframax - Sistema Gestivo
 */

// Define o fuso horário para garantir consistência em logs e datas
date_default_timezone_set('America/Sao_Paulo');

// Define a URL base do seu projeto (MUDAR PARA SEU AMBIENTE DE PRODUÇÃO)
define('BASE_URL', 'gestao.reframax.app.br/public');

// ------------------------------------------------------------------
// Configurações do Banco de Dados (MySQL)
// ------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'junio413_gestao'); // Nome do banco de dados
define('DB_USER', 'junio413_gestao');           // Usuário do banco de dados
define('DB_PASS', 'K^KX1X92zI5f');               // Senha do banco de dados

// ------------------------------------------------------------------
// Níveis de Acesso (Para consistência no código e DB)
// ------------------------------------------------------------------
const ACESS_LEVELS = [
    'ADMIN'      => 4, // Acesso total
    'RH'         => 3, // Acesso a relatórios e aprovações
    'GESTOR'     => 2, // Acesso ao seu C.C. e movimentação
    'COLABORADOR' => 1  // Acesso limitado (próprios dados)
];

// ------------------------------------------------------------------
// Configurações de Segurança
// ------------------------------------------------------------------
define('SESSION_NAME', 'reframax_sess'); // Nome da sessão para segurança
define('SESSION_EXPIRY', 3600);        // 1 hora de expiração em segundos

// ------------------------------------------------------------------
// Caminhos do Sistema (Não altere, facilita o 'require' e 'include')
// ------------------------------------------------------------------
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'app/');
define('VIEW_PATH', APP_PATH . 'Views/');
define('MODEL_PATH', APP_PATH . 'Models/');
define('CONTROLLER_PATH', APP_PATH . 'Controllers/');