<?php
// Inicia a sessão para compatibilidade com Conexao.class.php
session_start();

// Configura o cabeçalho para retornar JSON
header("Content-Type: application/json");

// Configuração de Erros em Produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 1. Inclua o autoload do Composer (para JWT e Dotenv)
require_once 'vendor/autoload.php';

// 2. Carrega as variáveis de ambiente do .env
// __DIR__ assume que o .env está no mesmo diretório desta API
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Arquivo .env não encontrado. Verifique as Instrucoes.md.']);
    exit;
}

// 3. Pega a chave secreta do ambiente (via $_ENV)
$chaveSecreta = $_ENV['JWT_SECRET_KEY'] ?? null;
if (is_null($chaveSecreta)) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Chave secreta (JWT_SECRET_KEY) não definida no .env.']);
    exit;
}

// 4. Importa a classe JWT
use \Firebase\JWT\JWT;

// 5. Inclua seu autoload customizado (para classe Usuario)
include_once 'autoload.php';

// Verifique se o método é POST
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405); // Método não permitido
    echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido. Use POST para login.']);
    exit;
}

// Leia o JSON enviado
$input = json_decode(file_get_contents('php://input'), true);

// Valide a entrada
if (!isset($input['cpf']) || !isset($input['senha'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['sucesso' => false, 'erro' => 'Campos CPF e Senha são obrigatórios para o login.']);
    exit;
}

try {
    // Tente validar o login
    $objUsuario = new Usuario();
    $resultadoLogin = $objUsuario->validarLogin($input['cpf'], $input['senha']);

    // Verifique o resultado
    if ($resultadoLogin['validado'] === true) {
        // Login SUCESSO! Gere o Token JWT.
        $issuer = "http://seusite.com";
        $audience = "http://seusite.com";
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
        echo json_encode([
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
        echo json_encode(['sucesso' => false, 'erro' => 'CPF ou Senha inválidos.']);
    }
} catch (Exception $e) {
    // Lidar com erros de PDO ou JWT
    http_response_code(500); // 500: Internal Server Error
    error_log("Erro no login: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno ao processar o login.']);
}
