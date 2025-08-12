<?php
session_start();
error_reporting(~E_ALL & ~E_NOTICE & ~E_WARNING);

// Carregamento do autoload
include_once "autoload.php";

require __DIR__ . '/vendor/autoload.php';

// Carregamento manual do .env
// Verifica se o arquivo .env existe
$env = parse_ini_file(__DIR__ . '/.env', false, INI_SCANNER_RAW) ?: [];
// Verifica se o arquivo .env foi carregado corretamente
foreach ($env as $key => $value) {
    $value = trim($value, "\"'");
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
// recuperar senha
if (isset($_POST['recuperar_senha'])) {
    // Verifica se o campo de email foi enviado
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $objController = new Controller();
    $objController->recuperarSenha($email);
    exit();
}
// login do usuário
if (isset($_POST['login'])) {
    // Verifica se o campo de CPF e senha foram enviados limpos
    $objController = new Controller();
    $cpf = htmlspecialchars(trim($_POST['cpf']));
    $senha = htmlspecialchars(trim($_POST['senha']));
    $cpfLimpo = str_replace(['.', '-'], '', $cpf);
    $objController->validar($cpfLimpo, $senha);
} else {
    // Verifica se o usuário já está logado
    $objController = new Controller();
    $objController->validarSessao();
    include_once "router.php";
}
