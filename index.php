<?php
session_start();

// Carregamento do autoload do Composer
require __DIR__ . '/vendor/autoload.php';
// Seu autoload personalizado (se necessário para classes fora do padrão PSR-4)
include_once "autoload.php";

// Carregamento manual do .env
$env = parse_ini_file(__DIR__ . '/.env', false, INI_SCANNER_RAW) ?: [];
foreach ($env as $key => $value) {
    $value = trim($value, "\"'");
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

// Configuração de exibição de erros com base no ambiente
if (getenv('APP_ENV') === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}

// --- ROTAS DE AÇÃO (POST) ---

// 1. Recuperar senha
if (isset($_POST['recuperar_senha'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // TODO: Redirecionar com mensagem de erro
        print "Erro: Formato de e-mail inválido.";
        exit();
    }

    $objController = new Controller();
    $objController->recuperarSenha($email);
    exit();
}

// 2. Login do usuário
if (isset($_POST['login'])) {
    $cpf = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
    $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';

    if (empty($cpf) || empty($senha)) {
        // TODO: Redirecionar com mensagem de erro
        print "Erro: CPF e Senha são obrigatórios.";
        exit();
    }

    $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf); // Remove tudo que não for dígito

    if (strlen($cpfLimpo) != 11) {
        // TODO: Redirecionar com mensagem de erro
        print "Erro: Formato de CPF inválido.";
        exit();
    }

    $objController = new Controller();
    $objController->validar($cpfLimpo, $senha);
    exit();
}

// --- ROTEAMENTO PRINCIPAL (GET ou Sessão Ativa) ---

// Se nenhuma ação POST foi tratada, verifica a sessão e carrega o roteador
$objController = new Controller();
$objController->validarSessao();
include_once "router.php";
