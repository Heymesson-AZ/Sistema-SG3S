<?php
define('IS_API_CALL', true);
session_start();

// Configura o cabeçalho para retornar JSON
header("Content-Type: application/json");

// Configuração de Erros em Produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Define a raiz do projeto (um nível ACIMA do diretório atual 'api')
$raizDoProjeto = dirname(__DIR__);

// 1. Inclua o autoload do Composer (da pasta raiz)
require_once $raizDoProjeto . '/vendor/autoload.php';

// 2. Carrega as variáveis de ambiente do .env (da pasta raiz)
try {
    // Aponta o Dotenv para a pasta raiz onde o .env está
    $dotenv = Dotenv\Dotenv::createImmutable($raizDoProjeto);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Arquivo .env não encontrado na raiz do projeto.']);
    exit;
}

// 3. Pega a chave secreta do ambiente
$chaveSecreta = $_ENV['JWT_SECRET_KEY'] ?? null;
if (is_null($chaveSecreta)) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Chave secreta (JWT_SECRET_KEY) não definida no .env.']);
    exit;
}

// 4. Importa a classe JWT
use \Firebase\JWT\JWT;

// 5. Inclua seu autoload customizado (da pasta raiz)
// Este autoload agora está seguro por causa da bandeira IS_API_CALL
include_once $raizDoProjeto . '/autoload.php';

// Verifique se o método é POST
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405); // Método não permitido
    print json_encode(['sucesso' => false, 'erro' => 'Método não permitido. Use POST para login.']);
    exit;
}

// Leia o JSON enviado
$input = json_decode(file_get_contents('php://input'), true);

// Valide a entrada
if (!isset($input['cpf']) || !isset($input['senha'])) {
    http_response_code(400); // Bad Request
    print json_encode(['sucesso' => false, 'erro' => 'Campos CPF e Senha são obrigatórios para o login.']);
    exit;
}

try {
    // Tente validar o login
    // A classe Usuario (ou Conexao) não vai mais redirecionar
    $objUsuario = new Usuario();
    $resultadoLogin = $objUsuario->validarLogin($input['cpf'], $input['senha']);

    // Verifique o resultado
    if ($resultadoLogin['validado'] === true) {
        // Login SUCESSO! Gere o Token JWT.
        $issuer = "https://sg3s.tds104-senac.online"; // Use seu site real
        $audience = "https://sg3s.tds104-senac.online"; // Use seu site real
        $issuedAt = time();
        $notBefore = $issuedAt;
        $expire = $issuedAt + (60 * 60 * 8); // Expira em 8 horas

        // O "corpo" do token
        $payload = [
            'iss' => $issuer,
            'aud' => $audience,
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => [ // Nossos dados personalizados
                'id_usuario' => $resultadoLogin['id_usuario'],
                'perfil' => $resultadoLogin['perfil']
            ]
        ];

        // Assina e cria o token usando a chave do .env
        $jwtToken = JWT::encode($payload, $chaveSecreta, 'HS256');

        // Envie o token e os dados do usuário
        http_response_code(200);
        print json_encode([
            'sucesso' => true,
            'token' => $jwtToken,
            'validade' => '8 horas',
            'usuario' => [
                'id_usuario' => $resultadoLogin['id_usuario'],
                'nome' => $resultadoLogin['nome'],
                'perfil' => $resultadoLogin['perfil']
            ]
        ]);
    } else {
        // Login FALHOU
        http_response_code(401); // 401: Unauthorized (Não Autorizado)
        print json_encode(['sucesso' => false, 'erro' => 'CPF ou Senha inválidos.']);
    }
} catch (Exception $e) {
    // Lidar com erros de PDO ou JWT
    http_response_code(500); // 500: Internal Server Error
    error_log("Erro no login: " . $e->getMessage());
    print json_encode(['sucesso' => false, 'erro' => 'Erro interno ao processar o login.', 'detalhe' => $e->getMessage()]);
}
