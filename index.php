<?php

session_start();

// Carregamento do autoload do Composer
require __DIR__ . '/vendor/autoload.php';
// Seu autoload personalizado
include_once "autoload.php";

// Carregamento manual do .env
$env = parse_ini_file(__DIR__ . '/.env', false, INI_SCANNER_RAW) ?: [];
foreach ($env as $key => $value) {
    $value = trim($value, "\"'");
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

// Configuração de exibição de erros
if (getenv('APP_ENV') === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}

// --- INSTANCIA O CONTROLLER NO INÍCIO ---
// Agora $objController está disponível para todo o script
$objController = new Controller();
// 1. Recuperar senha
if (isset($_POST['recuperar_senha'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $objController->mostrarMensagemErro("Erro: Formato de e-mail inválido.");
        exit();
    }
    $objController->recuperarSenha($email);
    exit();
}

// 1. Chave secreta
$recaptchaSecret = getenv('RECAPTCHA_SECRET_KEY');

if (!$recaptchaSecret) {
    // CORRIGIDO: Usando o método do controller
    $objController->mostrarMensagemErro("Erro: Chave secreta do reCAPTCHA não configurada no ambiente.");
    exit();
}

// 2. Login do usuário
if (isset($_POST['login'])) {

    // --- INÍCIO DA VALIDAÇÃO RECAPTCHA ---

    if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {

        $gRecaptchaResponse = $_POST['g-recaptcha-response'];
        $remoteIp = $_SERVER['REMOTE_ADDR'];
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $data = [
            'secret'   => $recaptchaSecret,
            'response' => $gRecaptchaResponse,
            'remoteip' => $remoteIp
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($verifyUrl, false, $context);
        $responseData = json_decode($response);

        if (!$responseData || !$responseData->success) {
            $objController->mostrarMensagemErro("Erro: Falha na verificação do reCAPTCHA. Tente novamente.");
            exit();
            header("Location: login.php");
        }
    } else {
        $objController->mostrarMensagemErro("Erro: Por favor, marque a caixa 'Não sou um robô'.");
        exit();
        header("Location: login.php");
    }

    $cpf = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
    $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';

    if (empty($cpf) || empty($senha)) {
        // CORRIGIDO: Usando o método do controller
        $objController->mostrarMensagemErro("Erro: CPF e Senha são obrigatórios.");
        exit();
    }

    $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpfLimpo) != 11) {
        // CORRIGIDO: Usando o método do controller
        $objController->mostrarMensagemErro("Erro: Formato de CPF inválido.");
        exit();
    }

    // Agora validamos o usuário (o $objController já foi criado lá em cima)
    $objController->validar($cpfLimpo, $senha);
    exit();
}

// Se nenhuma ação POST foi tratada, verifica a sessão e carrega o roteador
$objController->validarSessao();
include_once "router.php";
