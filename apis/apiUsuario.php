<?php
header("Content-Type: application/json");
error_reporting(~E_ALL & ~E_NOTICE & ~E_WARNING);
define('API_TOKEN', '781e5e245d69b566979b86e28d23f2c7');

function verificarToken($headers)
{
    if (!isset($headers['Authorization'])) return false;
    if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        return $matches[1] === API_TOKEN;
    }
    return false;
}

$headers = getallheaders();
if (!verificarToken($headers)) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token inválido ou ausente.']);
    exit;
}

include_once 'autoload.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$objUsuario = new Usuario();

switch ($method) {
    case 'GET':
        try {
            $nome_usuario = $input['nome_usuario'];
            $id_perfil = $input['id_perfil'];
            $resultado = $objUsuario->ConsultarUsuario($nome_usuario, $id_perfil);
            print json_encode($resultado);
        } catch (PDOException $e) {
            print json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        if (!isset($input['nome_usuario'], $input['email'], $input['senha'], $input['cpf'], $input['telefone'], $input['id_perfil'])) {
            print json_encode(['error' => 'Todos os campos são obrigatórios!']);
            break;
        }
        try {
            $senhaHash = password_hash($input['senha'], PASSWORD_DEFAULT);
            $sucesso = $objUsuario->cadastrarUsuario(
                $input['nome_usuario'],
                $input['email'],
                $senhaHash,
                $input['cpf'],
                $input['telefone'],
                $input['id_perfil']
            );
            print json_encode(['sucesso' => $sucesso]);
        } catch (PDOException $e) {
            print json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        if (!isset($input['id_usuario'])) {
            print json_encode(['error' => 'O ID do usuário é obrigatório.']);
            break;
        }
        try {
            $senhaHash = password_hash($input['senha'], PASSWORD_DEFAULT);
            $sucesso = $objUsuario->alterarUsuario(
                $input['id_usuario'],
                $input['nome_usuario'],
                $input['email'],
                $senhaHash,
                $input['id_perfil'],
                $input['cpf'],
                $input['telefone']
            );
            print json_encode(['sucesso' => $sucesso]);
        } catch (PDOException $e) {
            print json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        if (!isset($input['id_usuario'])) {
            print json_encode(['error' => 'O ID do usuário é obrigatório.']);
            break;
        }
        try {
            $sucesso = $objUsuario->excluirUsuario($input['id_usuario']);
            print json_encode(['sucesso' => $sucesso]);
        } catch (PDOException $e) {
            print json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        print json_encode(['error' => 'Método não permitido.']);
        break;
}
