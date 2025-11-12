<?php
// 1. INICIAR A SESSÃO (CRÍTICO PARA A AUDITORIA)
session_start();

// Configura o cabeçalho para retornar JSON
header("Content-Type: application/json");

// Configuração de Erros em Produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 2. INCLUIR AUTOLOADS
// Autoload do Composer (para JWT e Dotenv)
require_once 'vendor/autoload.php';

// 3. CARREGAR VARIÁVEIS DE AMBIENTE (.env)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Arquivo .env não encontrado.']);
    exit;
}

// 4. PEGAR A CHAVE SECRETA DO AMBIENTE
$chaveSecreta = $_ENV['JWT_SECRET_KEY'] ?? null;
if (is_null($chaveSecreta)) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Chave secreta (JWT_SECRET_KEY) não definida no .env.']);
    exit;
}

// 5. Seu autoload customizado (para a classe Cliente)
include_once 'autoload.php';

// 6. IMPORTAR CLASSES JWT
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;


/**
 * Função para verificar o Token JWT.
 * @param array $headers Cabeçalhos da requisição
 * @param string $chave A chave secreta lida do .env
 * @return array|false Retorna o payload 'data' do usuário se válido, ou 'false'.
 */
function verificarTokenJWT($headers, $chave)
{
    if (!isset($headers['Authorization'])) {
        return false;
    }
    $token = null;
    if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        $token = $matches[1];
    }
    if (!$token) {
        return false;
    }
    try {
        // Usa a $chave (vinda do .env) para decodificar
        $decoded = JWT::decode($token, new Key($chave, 'HS256'));
        return (array) $decoded->data;
    } catch (ExpiredException $e) {
        http_response_code(401);
        echo json_encode(['sucesso' => false, 'erro' => 'Token expirado. Por favor, faça login novamente.']);
        exit;
    } catch (Exception $e) {
        // Token inválido (assinatura errada, etc)
        error_log("Erro de validação JWT: " . $e->getMessage());
        return false;
    }
}

// 7. VALIDAÇÃO DO TOKEN
$headers = getallheaders();
$dadosUsuarioLogado = verificarTokenJWT($headers, $chaveSecreta);

if ($dadosUsuarioLogado === false) {
    http_response_code(401); // 401 Unauthorized
    echo json_encode(['sucesso' => false, 'erro' => 'Token de autorização inválido ou ausente.']);
    exit;
}

// 8. A "PONTE" PARA A AUDITORIA
// Injeta o ID do usuário (vindo do token) na sessão.
// A classe Conexao.class.php irá ler este valor.
$_SESSION['id_usuario'] = $dadosUsuarioLogado['id_usuario'];


// 9. PROCESSAMENTO DA API
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$query_params = $_GET;

// Instancia o Cliente AGORA, *depois* que a sessão foi setada.
$objCliente = new Cliente();

try {
    switch ($method) {
        case 'GET':
            $nome_fantasia = $query_params['nome_fantasia'] ?? $input['nome_fantasia'] ?? null;
            $razao_social = $query_params['razao_social'] ?? $input['razao_social'] ?? null;
            $cnpj_cliente = $query_params['cnpj_cliente'] ?? $input['cnpj_cliente'] ?? null;

            $resultado = $objCliente->consultarCliente($nome_fantasia, $razao_social, $cnpj_cliente);
            http_response_code(200);
            print json_encode($resultado);
            break;

        case 'POST':
            if (!isset($input['razao_social'], $input['nome_fantasia'], $input['cnpj_cliente'])) {
                http_response_code(400);
                print json_encode(['sucesso' => false, 'erro' => 'Razão Social, Nome Fantasia e CNPJ são obrigatórios.']);
                break;
            }
            $sucesso = $objCliente->cadastrarCliente(
                $input['nome_representante'] ?? null,
                $input['razao_social'] ?? null,
                $input['nome_fantasia'] ?? null,
                $input['cnpj_cliente'] ?? null,
                $input['email'] ?? null,
                $input['limite_credito'] ?? 0.0,
                $input['telefones'] ?? [],
                $input['inscricao_estadual'] ?? null,
                $input['cidade'] ?? null,
                $input['estado'] ?? null,
                $input['bairro'] ?? null,
                $input['cep'] ?? null,
                $input['complemento'] ?? null
            );
            http_response_code(201); // 201 Created
            print json_encode(['sucesso' => $sucesso]);
            break;

        case 'PUT':
            if (!isset($input['id_cliente'])) {
                http_response_code(400);
                print json_encode(['sucesso' => false, 'erro' => 'O ID do cliente é obrigatório para alteração.']);
                break;
            }
            $sucesso = $objCliente->alterarCliente(
                $input['id_cliente'],
                $input['nome_representante'] ?? null,
                $input['razao_social'] ?? null,
                $input['nome_fantasia'] ?? null,
                $input['cnpj_cliente'] ?? null,
                $input['email'] ?? null,
                $input['limite_credito'] ?? 0.0,
                $input['telefones'] ?? [],
                $input['inscricao_estadual'] ?? null,
                $input['cidade'] ?? null,
                $input['estado'] ?? null,
                $input['bairro'] ?? null,
                $input['cep'] ?? null,
                $input['complemento'] ?? null
            );
            http_response_code(200);
            print json_encode(['sucesso' => $sucesso]);
            break;

        case 'DELETE':
            if (!isset($input['id_cliente'])) {
                http_response_code(400);
                print json_encode(['sucesso' => false, 'erro' => 'O ID do cliente é obrigatório para exclusão.']);
                break;
            }
            if (method_exists($objCliente, 'clienteEmAlgumPedido')) {
                $emPedido = $objCliente->clienteEmAlgumPedido($input['id_cliente']);
                if ($emPedido) {
                    http_response_code(409); // Conflict
                    print json_encode(['sucesso' => false, 'erro' => 'Este cliente não pode ser excluído pois está vinculado a um ou mais pedidos.']);
                    break;
                }
            }
            $sucesso = $objCliente->excluirCliente($input['id_cliente']);
            http_response_code(200);
            print json_encode(['sucesso' => $sucesso]);
            break;

        default:
            http_response_code(405); // Method Not Allowed
            print json_encode(['sucesso' => false, 'erro' => 'Método não permitido.']);
            break;
    }
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        http_response_code(409);
        print json_encode(['sucesso' => false, 'erro' => 'Erro de integridade: Este cliente não pode ser excluído, verifique vínculos.']);
    } else {
        http_response_code(500);
        error_log("Erro de PDO na API de Cliente: " . $e->getMessage());
        print json_encode(['sucesso' => false, 'erro' => 'Erro interno no servidor.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro na API de Cliente: " . $e->getMessage());
    print json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
