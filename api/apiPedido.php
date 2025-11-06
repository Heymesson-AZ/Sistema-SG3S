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

// 5. Seu autoload customizado (para a classe Pedido)
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
        $decoded = JWT::decode($token, new Key($chave, 'HS256'));
        return (array) $decoded->data;
    } catch (ExpiredException $e) {
        http_response_code(401);
        echo json_encode(['sucesso' => false, 'erro' => 'Token expirado. Por favor, faça login novamente.']);
        exit;
    } catch (Exception $e) {
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

// Instancia o Pedido AGORA, *depois* que a sessão foi setada.
$objPedido = new Pedido();

try {
    // 10. ROTEAMENTO DE AÇÕES
    // Ações customizadas (ex: /pedido_api.php?acao=APROVAR)
    // Usamos POST para ações que alteram o estado (Aprovar, Finalizar, Cancelar)
    $acao = $query_params['acao'] ?? $input['acao'] ?? null;

    if ($method === 'POST' && $acao === 'APROVAR') {
        // --- AÇÃO: APROVAR PEDIDO ---
        if (!isset($input['id_pedido'])) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'id_pedido é obrigatório para aprovar.']);
            exit;
        }
        $resultado = $objPedido->aprovarPedido($input['id_pedido']);
        if ($resultado['success'] === true) {
            http_response_code(200);
            echo json_encode(['sucesso' => true, 'mensagem' => $resultado['message']]);
        } else {
            http_response_code(409); // Conflito (ex: falta de estoque)
            echo json_encode(['sucesso' => false, 'erro' => $resultado['message']]);
        }
        exit;
    }

    if ($method === 'POST' && $acao === 'FINALIZAR') {
        // --- AÇÃO: FINALIZAR PEDIDO ---
        if (!isset($input['id_pedido'])) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'id_pedido é obrigatório para finalizar.']);
            exit;
        }
        $sucesso = $objPedido->finalizarPedido($input['id_pedido'], 'Finalizado');
        http_response_code(200);
        echo json_encode(['sucesso' => $sucesso]);
        exit;
    }

    if ($method === 'POST' && $acao === 'CANCELAR') {
        // --- AÇÃO: CANCELAR PEDIDO ---
        if (!isset($input['id_pedido'])) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'id_pedido é obrigatório para cancelar.']);
            exit;
        }
        $sucesso = $objPedido->cancelarPedido($input['id_pedido'], 'Cancelado');
        http_response_code(200);
        echo json_encode(['sucesso' => $sucesso]);
        exit;
    }


    // 11. ROTEAMENTO CRUD (GET, POST, PUT, DELETE)
    switch ($method) {
        case 'GET':
            // Consultar pedidos
            $numero_pedido = $query_params['numero_pedido'] ?? $input['numero_pedido'] ?? null;
            $id_cliente = $query_params['id_cliente'] ?? $input['id_cliente'] ?? null;
            $status_pedido = $query_params['status_pedido'] ?? $input['status_pedido'] ?? null;
            $data_pedido = $query_params['data_pedido'] ?? $input['data_pedido'] ?? null;
            $id_forma_pagamento = $query_params['id_forma_pagamento'] ?? $input['id_forma_pagamento'] ?? null;

            $resultado = $objPedido->consultarPedido(
                $numero_pedido,
                $id_cliente,
                $status_pedido,
                $data_pedido,
                $id_forma_pagamento
            );
            http_response_code(200);
            print json_encode($resultado);
            break;

        case 'POST':
            // Cadastrar novo pedido (sem 'acao')
            if (!isset($input['id_cliente'], $input['valor_total'], $input['id_forma_pagamento'], $input['itens'])) {
                http_response_code(400);
                print json_encode(['sucesso' => false, 'erro' => 'Campos id_cliente, valor_total, id_forma_pagamento e itens são obrigatórios.']);
                break;
            }
            $sucesso = $objPedido->cadastrarPedido(
                $input['id_cliente'],
                $input['status_pedido'] ?? 'Pendente',
                $input['valor_total'],
                $input['id_forma_pagamento'],
                $input['valor_frete'] ?? 0.0,
                $input['itens']
            );
            http_response_code(201); // 201 Created
            print json_encode(['sucesso' => $sucesso]);
            break;

        case 'PUT':
            // Alterar pedido (só permitido se estiver Pendente)
            if (!isset($input['id_pedido'], $input['id_cliente'], $input['valor_total'], $input['id_forma_pagamento'], $input['itens'])) {
                http_response_code(400);
                print json_encode(['sucesso' => false, 'erro' => 'Campos id_pedido, id_cliente, valor_total, id_forma_pagamento e itens são obrigatórios.']);
                break;
            }
            $sucesso = $objPedido->alterarPedido(
                $input['id_pedido'],
                $input['id_cliente'],
                $input['valor_total'],
                $input['id_forma_pagamento'],
                $input['valor_frete'] ?? 0.0,
                $input['itens']
            );
            http_response_code(200);
            print json_encode(['sucesso' => $sucesso]);
            break;

        case 'DELETE':
            // Excluir pedido (só permitido se estiver Pendente ou Cancelado)
            if (!isset($input['id_pedido'])) {
                http_response_code(400);
                print json_encode(['sucesso' => false, 'erro' => 'O id_pedido é obrigatório para exclusão.']);
                break;
            }
            $sucesso = $objPedido->excluirPedido($input['id_pedido']);
            http_response_code(200);
            print json_encode(['sucesso' => $sucesso]);
            break;

        default:
            http_response_code(405); // Method Not Allowed
            print json_encode(['sucesso' => false, 'erro' => 'Método não permitido.']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro de PDO na API de Pedido: " . $e->getMessage());
    print json_encode(['sucesso' => false, 'erro' => 'Erro interno no servidor (PDO).']);
} catch (Exception $e) {
    // Captura exceções lançadas pela classe Pedido (ex: "Status não permite alteração")
    http_response_code(409); // Conflict (usado para regras de negócio)
    error_log("Erro na API de Pedido: " . $e->getMessage());
    print json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
?>