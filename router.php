<?php
// Instaciando a classe controller
$objController = new Controller();

session_start();

// pegar url
$url = explode('?', $_SERVER['REQUEST_URI']);
$pagina = $url[1];

//rotas de redirecionamento
if (isset($pagina)) {
    $objController->redirecionar($pagina);
};

//  notificacoes:

// Requisição para buscar produtos abaixo do estoque mínimo
if (isset($_POST['buscarProdutosAbaixoMinimo'])) {
    $objController->buscarProdutosAbaixoMinimo();
    exit;
}

// Requisição para buscar pedidos pendentes ou aguardando pagamento
if (isset($_POST['buscarPedidosPendentes'])) {
    $objController->buscarPedidosPendentesOuAguardando();
    exit;
}

// imprimir os pedidos
if (isset($_GET['acao']) && $_GET['acao'] === 'imprimir_pedido') {
    $numero_pedido = htmlspecialchars(trim($_GET['numero_pedido']));
    $objController->imprimirPedido($numero_pedido);
}

// Usuario

// Função utilitária para sanitizar CPF e telefone
function limparCpf($cpf)
{
    return preg_replace('/[^0-9]/', '', $cpf);
}
function limparTelefone($telefone)
{
    return preg_replace('/[^0-9]/', '', $telefone);
}
// Função para comparar e validar senhas
function validarSenhasIguais($senha1, $senha2)
{
    return $senha1 === $senha2;
}
// ================= RECUPERAR SENHA =================
if (isset($_POST['recuperar_senha'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $objController->recuperarSenha($email);
}
// ================= CONSULTAR CPF =================
if (isset($_POST['verificar_cpf'])) {
    $cpf = limparCpf(filter_input(INPUT_POST, 'cpf_consulta'));
    $objController->consultarUsuario_Cpf($cpf);
}
// ================= CADASTRAR USUÁRIO =================
if (isset($_POST['cadastrar_usuario'])) {
    $nome = htmlspecialchars($_POST['nome_usuario']);
    $email = filter_input(INPUT_POST, 'email_usuario', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    $confSenha = $_POST['confSenha'];
    $perfil = $_POST['id_perfil'];
    $telefone = limparTelefone($_POST['telefone']);
    $cpf = limparCpf($_POST['cpf']);

    if (!validarSenhasIguais($senha, $confSenha)) {
        $menu = $objController->menu();
        include_once 'view/usuario.php';
        $objController->mostrarMensagemErro("As senhas não coincidem");
        return;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $objController->cadastrar_Usuario($nome, $email, $senhaHash, $perfil, $telefone, $cpf);
}
// ================= CONSULTAR USUÁRIO =================
if (isset($_POST['consultar_usuario'])) {
    $nome = htmlspecialchars($_POST['nome_usuario_consulta']);
    $perfil = intval($_POST['id_perfil']);
    $objController->consultar_Usuario($nome, $perfil);
}
// ================= EXCLUIR USUÁRIO =================
if (isset($_POST['excluir_usuario'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $nome = htmlspecialchars($_POST['nome_usuario']);
    $objController->excluir_Usuario($id_usuario, $nome);
}
// ================= ALTERAR USUÁRIO =================
if (isset($_POST['alterar_usuario'])) {
    $id = intval($_POST['id_usuario']);
    $nome = htmlspecialchars($_POST['nome_usuario']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $perfil = intval($_POST['id_perfil']);
    $telefone = limparTelefone($_POST['telefone']);
    $cpf = limparCpf($_POST['cpf']);
    $objController->alterar_Usuario($id, $nome, $email, $perfil, $cpf, $telefone);
}
// ================= ALTERAR SENHA =================
if (isset($_POST['alterar_senha'])) {
    $id = intval($_POST['id_usuario']);
    $nome = htmlspecialchars($_POST['nome_usuario']);
    $novaSenha = $_POST['senha'];
    $confSenha = $_POST['confSenha'];

    if (!validarSenhasIguais($novaSenha, $confSenha)) {
        $menu = $objController->menu();
        include_once 'view/usuario.php';
        $objController->mostrarMensagemErro("As senhas não coincidem");
        return;
    }

    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $objController->alterar_Senha($id, $senhaHash);
}

// PERFIL


// ================= VERIFICAR PERFIL =================
if (isset($_POST['verificar_perfil'])) {
    $nome_perfil = limparTexto($_POST['nome_perfil']);
    $objController->consultar_Perfil($nome_perfil);
}
// ================= CADASTRAR PERFIL =================
if (isset($_POST['cadastrar_perfil'])) {
    $nome_perfil = limparTexto($_POST['nome_perfil']);
    $objController->cadastrar_Perfil($nome_perfil);
}
// ================= CONSULTAR PERFIL =================
if (isset($_POST['consultar_perfil'])) {
    $nome_perfil = limparTexto($_POST['nome_perfil']);
    $objController->consultar_Perfil($nome_perfil);
}
// ================= ALTERAR PERFIL =================
if (isset($_POST['alterar_perfil'])) {
    $id_perfil = $_POST['id_perfil'];
    $nome_perfil = limparTexto($_POST['perfil_usuario']);
    $objController->alterar_Perfil($id_perfil, $nome_perfil);
}
// ================= EXCLUIR PERFIL =================
if (isset($_POST['excluir_perfil'])) {
    $id_perfil = $_POST['id_perfil'];
    $nome_perfil = limparTexto($_POST['nome_perfil']);
    $objController->excluir_Perfil($id_perfil, $nome_perfil);
}

// FORNECEDOR

// Funções auxiliares para sanitização
function limparCNPJ($cnpj)
{
    return preg_replace('/[^0-9]/', '', $cnpj);
}

function limparTexto($texto)
{
    return htmlspecialchars(trim($texto));
}

// ================= VERIFICAR CNPJ =================
if (isset($_POST['verificar_cnpj'])) {
    $cnpj = limparCNPJ($_POST['cnpj_consulta']);
    $objController->consultarFornecedor_Cnpj($cnpj);
}
// ================= CONSULTAR FORNECEDOR =================
if (isset($_POST['consultar_fornecedor'])) {
    $razao_social = limparTexto($_POST['razao_social']);
    $objController->consultar_Fornecedor($razao_social);
}
// ================= CADASTRAR FORNECEDOR =================
if (isset($_POST['cadastrar_fornecedor'])) {
    $razao_social = limparTexto($_POST['razao_social']);
    $cnpj = limparCNPJ($_POST['cnpj']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    // Array de telefones será tratado depois
    $telefones = $_POST['telefones'];

    $objController->cadastrar_Fornecedor($razao_social, $cnpj, $email, $telefones);
}
// ================= ALTERAR FORNECEDOR =================
if (isset($_POST['alterar_fornecedor'])) {
    $id_fornecedor = intval($_POST['id_fornecedor']);
    $razao_social = limparTexto($_POST['razao_social']);
    $cnpj = limparCNPJ($_POST['cnpj']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    // Array de telefones será tratado depois
    $telefones = $_POST['telefones'];

    $objController->alterar_Fornecedor($id_fornecedor, $razao_social, $cnpj, $email, $telefones);
}
// ================= EXCLUIR FORNECEDOR =================
if (isset($_POST['excluir_fornecedor'])) {
    $id_fornecedor = intval($_POST['id_fornecedor']);
    $razao_social = limparTexto($_POST['razao_social']);
    $objController->excluir_Fornecedor($id_fornecedor, $razao_social);
}
// ================= BUSCA DINÂMICA DE FORNECEDOR =================
if (isset($_POST['buscar_fornecedor'])) {
    $busca = strtolower(trim($_POST['buscar_fornecedor']));
    $objController->buscarFornecedor($busca);
    exit;
}

// PRODUTO


function limparNumero($valor)
{
    return (float) str_replace(',', '.', preg_replace('/[^\d,.-]/', '', $valor));
}

// ================= BUSCA DINÂMICA Da Cor do Produto=================
if (isset($_POST['cor_produto'])) {
    // strlower para minusculo e trim para tirar espaco
    $cor_produto = strtolower(trim($_POST['cor_produto']));
    $objController->buscarCorProduto($cor_produto);
    exit;
}
//================= BUSCA DINÂMICA DO Tipo do Produto =================
if (isset($_POST['tipo_produto'])) {
    // strlower para minusculo e trim para tirar espaco
    $tipo_produto = strtolower(trim($_POST['tipo_produto']));
    $objController->buscarTipoProduto($tipo_produto);
    exit;
}
// ================= CONSULTAR PRODUTO =================
if (isset($_POST['consultar_produto'])) {
    $nome_produto  = limparTexto($_POST['nome_produto']);
    $id_tipo_produto  = limparTexto($_POST['id_tipo_produto']);
    $id_cor           = limparTexto($_POST['id_cor']);
    $id_fornecedor = htmlspecialchars($_POST['id_fornecedor']);
    $objController->consultar_Produto($nome_produto, $id_tipo_produto, $id_cor, $id_fornecedor);
}
// ================= CADASTRAR PRODUTO =================
if (isset($_POST['cadastrar_produto'])) {
    $nome_produto      = limparTexto($_POST['nome_produto']);
    $id_tipo_produto      = limparTexto($_POST['id_tipo_produto']);
    $id_cor               = limparTexto($_POST['id_cor']);
    $composicao        = limparTexto($_POST['composicao']);
    $quantidade        = limparNumero($_POST['quantidade']);
    $quantidade_minima = limparNumero($_POST['quantidade_minima']);
    $largura           = limparNumero($_POST['largura']);
    $custo_compra      = limparNumero($_POST['custo_compra']);
    $valor_venda       = limparNumero($_POST['valor_venda']);
    $data_compra       = limparTexto($_POST['data_compra']);
    $ncm_produto       = limparTexto($_POST['ncm_produto']);
    $id_fornecedor     = $_POST['id_fornecedor'];
    $img_produto = '';

    // Upload de imagem
    if (isset($_FILES['img_produto']) && $_FILES['img_produto']['error'] === 0) {
        $permitidos   = ['image/jpeg', 'image/png', 'image/gif'];
        $maxTamanho   = 500 * 1024;
        $maxLargura   = 1200;
        $maxAltura    = 1200;

        $imagem       = $_FILES['img_produto'];
        $tipoArquivo  = $imagem['type'];
        $tamanho      = $imagem['size'];
        // tmp e uma pasta temporaria que armazena o arquivo de imagem para segunranca
        //Isso evita que arquivos maliciosos sejam salvos diretamente no seu servidor, sem validação.
        //Isso simplifica o gerenciamento e economiza recursos até que você decida o que fazer com o arquivo
        //Previne acesso direto e ataques
        // o tmp_name e o caminho temporario criado pelo php
        $tmp          = $imagem['tmp_name'];
        //basename() extrai apenas o nome do arquivo, removendo qualquer caminho que possa vir junto.
        $nomeOriginal = basename($imagem['name']);

        if (!in_array($tipoArquivo, $permitidos)) {
            print "Erro: Apenas imagens JPEG, PNG ou GIF são permitidas.";
            exit;
        }

        if ($tamanho > $maxTamanho) {
            print "Erro: A imagem excede o tamanho máximo permitido (500KB).";
            exit;
        }

        list($larguraImg, $alturaImg) = getimagesize($tmp);
        if ($larguraImg > $maxLargura || $alturaImg > $maxAltura) {
            print "Erro: A imagem deve ter no máximo {$maxLargura}x{$maxAltura} pixels.";
            exit;
        }
        //o is_dir Verifica se um caminho existe e é um diretório.
        // o mkdir Cria um novo diretório.
        // O valor 0755 em mkdir() define as permissões do diretório
        $pastaDestino = 'assets/img/';
        if (!is_dir($pastaDestino)) mkdir($pastaDestino, 0755, true);
        //o uniqid Gera um identificador único com base na hora atual.
        $nomeUnico = uniqid() . '_' . $nomeOriginal;
        $caminho_destino = $pastaDestino . $nomeUnico;

        if (!move_uploaded_file($tmp, $caminho_destino)) {
            print "Erro ao salvar a imagem!";
            exit;
        }
        $img_produto = $caminho_destino;
    }

    $objController->cadastrar_Produto(
        $nome_produto,
        $id_tipo_produto,
        $id_cor,
        $composicao,
        $quantidade,
        $quantidade_minima,
        $largura,
        $custo_compra,
        $valor_venda,
        $data_compra,
        $ncm_produto,
        $id_fornecedor,
        $img_produto
    );
}
// ================= ALTERAR PRODUTO =================
if (isset($_POST['alterar_produto'])) {
    $id_produto         = $_POST['id_produto'];
    $nome_produto       = limparTexto($_POST['nome_produto']);
    $id_tipo_produto       = limparTexto($_POST['id_tipo_produto']);
    $id_cor                = limparTexto($_POST['id_cor']);
    $composicao         = limparTexto($_POST['composicao']);
    $quantidade         = limparNumero($_POST['quantidade']);
    $quantidade_minima  = limparNumero($_POST['quantidade_minima']);
    $largura            = limparNumero($_POST['largura']);
    $custo_compra       = limparNumero($_POST['custo_compra']);
    $valor_venda        = limparNumero($_POST['valor_venda']);
    $data_compra        = limparTexto($_POST['data_compra']);
    $ncm_produto        = limparTexto($_POST['ncm_produto']);
    $id_fornecedor      = $_POST['id_fornecedor'];

    $imagem_antiga = $_POST['imagem_antiga'] ?? '';
    $img_produto = $imagem_antiga;

    if (isset($_FILES['img_produto']) && $_FILES['img_produto']['error'] === 0) {
        $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        $maxTamanho = 500 * 1024;
        $maxLargura = 1200;
        $maxAltura  = 1200;

        $imagem        = $_FILES['img_produto'];
        $tipoArquivo   = $imagem['type'];
        $tamanho       = $imagem['size'];
        // tmp e uma pasta temporaria que armazena o arquivo de imagem para segunranca
        //Isso evita que arquivos maliciosos sejam salvos diretamente no seu servidor, sem validação.
        //Isso simplifica o gerenciamento e economiza recursos até que você decida o que fazer com o arquivo
        //Previne acesso direto e ataques
        // o tmp_name e o caminho temporario criado pelo php
        $tmp           = $imagem['tmp_name'];
        //basename() extrai apenas o nome do arquivo, removendo qualquer caminho que possa vir junto.
        $nomeOriginal = basename($imagem['name']);

        if (!in_array($tipoArquivo, $permitidos)) {
            print "Erro: Apenas imagens JPEG, PNG ou GIF são permitidas.";
            exit;
        }

        if ($tamanho > $maxTamanho) {
            print "Erro: A imagem excede o tamanho máximo permitido (500KB).";
            exit;
        }

        list($larguraImg, $alturaImg) = getimagesize($tmp);
        if ($larguraImg > $maxLargura || $alturaImg > $maxAltura) {
            print "Erro: A imagem deve ter no máximo {$maxLargura}x{$maxAltura} pixels.";
            exit;
        }
        //o is_dir Verifica se um caminho existe e é um diretório.
        // o mkdir Cria um novo diretório.
        // O valor 0755 em mkdir() define as permissões do diretório
        $pastaDestino = 'assets/img/';
        if (!is_dir($pastaDestino)) mkdir($pastaDestino, 0755, true);
        //o uniqid Gera um identificador único com base na hora atual.
        $nomeUnico = uniqid() . '_' . $nomeOriginal;
        $caminho_destino = $pastaDestino . $nomeUnico;
        if (move_uploaded_file($tmp, $caminho_destino)) {
            // se a imagem antiga estiver la, ela exixtir, apaga com o unlink
            if (!empty($imagem_antiga) && file_exists($imagem_antiga)) {
                unlink($imagem_antiga);
            }
            $img_produto = $caminho_destino;
        } else {
            print "Erro ao salvar a imagem!";
            exit;
        }
    }

    $objController->alterar_Produto(
        $nome_produto,
        $id_tipo_produto,
        $id_cor,
        $composicao,
        $quantidade,
        $quantidade_minima,
        $largura,
        $custo_compra,
        $valor_venda,
        $data_compra,
        $ncm_produto,
        $id_fornecedor,
        $id_produto,
        $img_produto
    );
}
// ================= VERIFICAR PRODUTO =================
if (isset($_POST['verificar_produto'])) {
    $nome_produto = limparTexto($_POST['nome_produto']);
    $id_cor           = limparTexto($_POST['id_cor']);
    $largura      = limparNumero($_POST['largura']);
    $id_fornecedor = $_POST['id_fornecedor'];
    $id_tipo_produto = $_POST['id_tipo_produto'];
    $objController->verificar_Produto($nome_produto, $id_cor, $largura, $id_fornecedor, $id_tipo_produto);
}
// ================= EXCLUIR PRODUTO =================
if (isset($_POST['excluir_produto'])) {
    $id_produto   = (int) $_POST['id_produto'];
    $nome_produto = limparTexto($_POST['nome_produto']);
    $objController->excluir_Produto($id_produto, $nome_produto);
}



// cadastrar tipo do produto
// ================= CADASTRAR TIPO DO PRODUTO =================
if (isset($_POST['cadastrar_tipo_produto'])) {
    $nome_tipo = limparTexto($_POST['nome_tipo']);
    // CORREÇÃO: Pega 'origem' se existir, senão, define como uma string vazia.
    $origem = $_POST['origem'] ?? '';
    $objController->cadastrar_TipoProduto($nome_tipo, $origem);
}
// ================= CONSULTAR  TIPO DO PRODUTO =================
if (isset($_POST['consultar_tipo_produto'])) {
    $nome_tipo = limparTexto($_POST['nome_tipo']);
    $objController->consultar_TipoProduto($nome_tipo);
}
// ================= ALTERAR TIPO DO PRODUTO =================
if (isset($_POST['alterar_tipo_produto'])) {
    $id_tipo_produto = $_POST['id_tipo_produto'];
    $nome_tipo = limparTexto($_POST['nome_tipo']);
    $objController->alterar_TipoProduto($id_tipo_produto, $nome_tipo);
}

// ================= EXCLUIR TIPO DO PRODUTO =================
if (isset($_POST['excluir_tipo_produto'])) {
    $id_tipo_produto = $_POST['id_tipo_produto'];
    $nome_tipo = limparTexto($_POST['nome_tipo']);
    $objController->excluir_TipoProduto($id_tipo_produto, $nome_tipo);
}




// cadastrar cor do produto
// ================= CADASTRAR COR DO PRODUTO =================
if (isset($_POST['cadastrar_cor_produto'])) {
    $nome_cor = limparTexto($_POST['nome_cor']);
    // CORREÇÃO: Pega 'origem' se existir, senão, define como uma string vazia.
    $origem = $_POST['origem'] ?? '';
    $objController->cadastrar_CorProduto($nome_cor, $origem);
}
// ================= CONSULTAR COR DO PRODUTO =================
if (isset($_POST['consultar_cor_produto'])) {
    $nome_cor = limparTexto($_POST['nome_cor']);
    $objController->consultar_CorProduto($nome_cor);
}
// ================= ALTERAR COR DO PRODUTO =================
if (isset($_POST['alterar_cor_produto'])) {
    $id_cor = $_POST['id_cor'];
    $nome_cor = limparTexto($_POST['nome_cor']);
    $objController->alterar_CorProduto($id_cor, $nome_cor);
}
// ================= EXCLUIR COR DO PRODUTO =================
if (isset($_POST['excluir_cor_produto'])) {
    $id_cor = $_POST['id_cor'];
    $nome_cor = limparTexto($_POST['nome_cor']);
    $objController->excluir_CorProduto($id_cor, $nome_cor);
}


// CLIENTE

// Função utilitária para remover máscaras
function removerMascara($valor, $mascaras = ['.', '-', '/', '(', ')', ' ', 'R$'])
{
    return str_replace($mascaras, '', $valor);
}
// Função utilitária para sanitizar string
function sanitizar($valor)
{
    return htmlspecialchars(trim($valor));
}

// ================= VerificaR CNPJ do CLIENTE =================
if (isset($_POST['verificar_cliente'])) {
    $cnpj_cliente = limparCNPJ($_POST['cnpj_cliente']);
    $objController->consultarCliente_Cnpj($cnpj_cliente);
}
// ================= CONSULTAR CLIENTE =================
if (isset($_POST['consultar_cliente'])) {
    $nome_fantasia = sanitizar($_POST['nome_fantasia']);
    $razao_social  = sanitizar($_POST['razao_social']);
    $cnpj_cliente  = removerMascara($_POST['cnpj_cliente'], ['.', '-', '/']);
    $objController->consultar_Cliente($nome_fantasia, $razao_social, $cnpj_cliente);
}
// Função auxiliar para limpar limite de crédito (ex. "R$ 1.234,56" → 1234.56)
function limparLimiteCredito($valor)
{
    // Remove R$ e espaços extras
    $valor = str_replace('R$', ' ', $valor);

    // Remove pontos de milhar
    $valor = str_replace('.', '', $valor);

    // Troca vírgula decimal por ponto
    $valor = str_replace(',', '.', $valor);

    // Retorna em formato float
    return trim($valor);
}
// ================= CADASTRAR CLIENTE =================
if (isset($_POST['cadastrar_cliente'])) {
    $nome_representante = sanitizar($_POST['nome_representante']);
    $razao_social       = sanitizar($_POST['razao_social']);
    $nome_fantasia      = sanitizar($_POST['nome_fantasia']);
    $cnpj_cliente       = removerMascara($_POST['cnpj_cliente'], ['.', '-', '/']);
    $inscricao_estadual = sanitizar($_POST['inscricao_estadual']);
    $email              = sanitizar($_POST['email']);
    $limite_credito     = limparLimiteCredito($_POST['limite_credito']);
    $cep                = removerMascara($_POST['cep'], ['-']);
    $complemento        = sanitizar($_POST['complemento']);
    $bairro             = sanitizar($_POST['bairro']);
    $cidade             = sanitizar($_POST['cidade']);
    $estado             = sanitizar($_POST['estado']);

    // Array de telefones será tratado depois
    $telefones = $_POST['telefones'];

    $objController->cadastrar_Cliente(
        $nome_representante,
        $razao_social,
        $nome_fantasia,
        $cnpj_cliente,
        $email,
        $limite_credito,
        $telefones,
        $inscricao_estadual,
        $cidade,
        $estado,
        $bairro,
        $cep,
        $complemento
    );
}
// ================= ALTERAR CLIENTE =================
if (isset($_POST['alterar_cliente'])) {
    $id_cliente         = $_POST['id_cliente'];
    $nome_representante = sanitizar($_POST['nome_representante']);
    $razao_social       = sanitizar($_POST['razao_social']);
    $nome_fantasia      = sanitizar($_POST['nome_fantasia']);
    $cnpj_cliente       = removerMascara($_POST['cnpj_cliente'], ['.', '-', '/']);
    $inscricao_estadual = sanitizar($_POST['inscricao_estadual']);
    $email              = sanitizar($_POST['email']);
    $limite_credito     = limparLimiteCredito($_POST['limite_credito']);
    $cep                = removerMascara($_POST['cep'], ['-']);
    $complemento        = sanitizar($_POST['complemento']);
    $bairro             = sanitizar($_POST['bairro']);
    $cidade             = sanitizar($_POST['cidade']);
    $estado             = sanitizar($_POST['estado']);

    // Array de telefones (tipo e numero)
    $telefones = $_POST['telefones'];

    $objController->alterar_Cliente(
        $id_cliente,
        $nome_representante,
        $razao_social,
        $nome_fantasia,
        $cnpj_cliente,
        $email,
        $limite_credito,
        $telefones,
        $inscricao_estadual,
        $cidade,
        $estado,
        $bairro,
        $cep,
        $complemento
    );
}
// ================= EXCLUIR CLINTE =================
if (isset($_POST['excluir_cliente'])) {
    $id_cliente    = $_POST['id_cliente'];
    $nome_fantasia = sanitizar($_POST['nome_fantasia']);
    $objController->excluir_Cliente($id_cliente, $nome_fantasia);
}




//  FORMA DE PAGAMENTO

// sanitizar descrição
function limparDescricao($valor)
{
    return htmlspecialchars(trim($valor));
}
// === CONSULTAR FORMA DE PAGAMENTO ===
if (isset($_POST['consultar_forma_pagamento'])) {
    $descricao = limparDescricao($_POST['descricao']);
    $objController->consultarForma_Pagamento($descricao);
}

// === CADASTRAR FORMA DE PAGAMENTO ===
if (isset($_POST['cadastrar_forma_pagamento'])) {
    $descricao = limparDescricao($_POST['descricao_cadastro']);
    $objController->cadastrarForma_Pagamento($descricao);
}

// === ALTERAR FORMA DE PAGAMENTO ===
if (isset($_POST['alterar_forma_pagamento'])) {
    $id_forma_pagamento = $_POST['id_forma_pagamento'];
    $descricao = limparDescricao($_POST['descricao']);
    $objController->alterarForma_Pagamento($id_forma_pagamento, $descricao);
}

// === EXCLUIR FORMA DE PAGAMENTO ===
if (isset($_POST['excluir_forma_pagamento'])) {
    $id_forma_pagamento = $_POST['id_forma_pagamento'] ?? null;
    $descricao = limparDescricao($_POST['descricao']);
    $objController->excluirForma_Pagamento($id_forma_pagamento, $descricao);
}

// PEDIDO


// =====================
// BUSCA DINÂMICA DE CLIENTES
// =====================
if (isset($_POST['cliente_pedido'])) {
    $cliente = strtolower(trim($_POST['cliente_pedido']));
    $objController->buscarCliente($cliente);
    exit;
}

if (isset($_POST['cliente_pedido_consulta'])) {
    $cliente = strtolower(trim($_POST['cliente_pedido']));
    $objController->buscarCliente($cliente);
    exit;
}
// =====================
// BUSCA DINÂMICA DO LIMITE DE CREDITO DO CLIENTE
// =====================
if (isset($_POST['verificar_limite'], $_POST['id_cliente'], $_POST['valor_total'])) {
    $id_cliente = (int) $_POST['id_cliente'];
    $valor_totalPedido = (float) $_POST['valor_total'];
    $objController->verificarLimiteCredito($id_cliente, $valor_totalPedido);
    exit;
}
// =====================
// BUSCA DINÂMICA DE PRODUTO PARA PEDIDO
// =====================
if (isset($_POST['produto_pedido'])) {
    $produto = strtolower(trim($_POST['produto_pedido']));
    $tipo = 'pedido';
    $objController->buscarProduto($produto, $tipo);
}
// =====================
// BUSCA DINÂMICA DE PRODUTO PARA RELATÓRIO DE CUSTO
// =====================
if (isset($_POST['produto_custo'])) {
    $produto = strtolower(trim($_POST['produto_custo']));
    $tipo = 'relatorio';
    $objController->buscarProduto($produto, $tipo);
}
// =====================
// BUSCA DINÂMICA DE PRODUTO PARA RELATÓRIO DE MARGEM
// =====================
if (isset($_POST['produto_margem'])) {
    $produto = strtolower(trim($_POST['produto_margem']));
    $tipo = 'relatorio';
    $objController->buscarProduto($produto, $tipo);
}
// =====================
// BUSCA DINÂMICA DE PRODUTO PARA RELATÓRIO DE VENDA MES A MÊS
// =====================
if (isset($_POST['produto_venda'])) {
    $produto = strtolower(trim($_POST['produto_venda']));
    $tipo = 'relatorio';
    $objController->buscarProduto($produto, $tipo);
}

// =====================
// VERIFICAR QUANTIDADE DO PRODUTO
// =====================
if (isset($_POST['verificar_quantidade'])) {
    $id_produto = $_POST['id_produto'];
    $quantidade = $_POST['quantidade'] ?? $_POST['novaQuantidade'] ?? 0;
    $objController->verificarQuantidade($id_produto, $quantidade);
}
// =====================
// CADASTRAR PEDIDO
// =====================
if (isset($_POST['salvar_pedido'])) {
    $id_cliente = $_POST['id_cliente'];
    $status_pedido = htmlspecialchars(trim($_POST['status_pedido']));
    $valor_total = floatval(str_replace(',', '.', $_POST['valor_total']));
    $id_forma_pagamento = $_POST['id_forma_pagamento'];
    $valor_frete = floatval(str_replace(',', '.', $_POST['valor_frete']));
    $origem = $_POST['origem'];
    $itensForm = $_POST['itens'];

    $itens = [];
    foreach ($itensForm as $item) {
        $id_produto = filter_var($item['id_produto'], FILTER_SANITIZE_NUMBER_INT);
        $quantidade = filter_var($item['quantidade'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $valor_unitario = filter_var($item['valor_unitario'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $totalValor_produto = filter_var($item['totalValor_produto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if ($id_produto && $quantidade > 0) {
            $itens[] = compact('id_produto', 'quantidade', 'valor_unitario', 'totalValor_produto');
        }
    }
    if (empty($itens)) {
        $objController->mostrarMensagemErro("Itens vazios");
        exit;
    }

    $objController->cadastrar_Pedido(
        $id_cliente,
        $status_pedido,
        $valor_total,
        $id_forma_pagamento,
        $valor_frete,
        $itens,
        $origem
    );
    exit;
}
// =====================
// CONSULTAR PEDIDOS
// =====================
if (isset($_POST['buscar_pedidos'])) {
    $numero_pedido = htmlspecialchars(trim($_POST['numero_pedido']));
    $id_cliente = $_POST['id_cliente'];
    $data_pedido = htmlspecialchars(trim($_POST['data_pedido']));
    $status_pedido = htmlspecialchars(trim($_POST['status_pedido']));
    $id_forma_pagamento = $_POST['id_forma_pagamento'];
    $origem = $_POST['origem'];

    $objController->consultar_Pedido(
        $numero_pedido,
        $id_cliente,
        $status_pedido,
        $data_pedido,
        $id_forma_pagamento,
        $origem
    );
}
// =====================
// ALTERAR PEDIDO
// =====================

if (isset($_POST['alterar_pedido'])) {
    $id_pedido = $_POST['id_pedido'];
    $id_cliente = $_POST['id_cliente'];
    $valor_total = floatval($_POST['valor_total']);
    $valor_frete = floatval($_POST['valor_frete']);
    $id_forma_pagamento = $_POST['id_forma_pagamento'];
    $origem = $_POST['origem'] ?? '';
    $itensForm = $_POST['itens'] ?? [];

    $itens = [];
    foreach ($itensForm as $item) {
        $id_produto = filter_var($item['id_produto'], FILTER_SANITIZE_NUMBER_INT);
        $quantidade = filter_var($item['quantidade'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $valor_unitario = filter_var($item['valor_unitario'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $totalValor_produto = filter_var($item['totalValor_produto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ($id_produto && $quantidade > 0) {
            $itens[] = compact('id_produto', 'quantidade', 'valor_unitario', 'totalValor_produto');
        }
    }
    if (empty($itens)) {
        $objController->mostrarMensagemErro("Itens vazios");
        exit;
    }
    $objController->alterar_Pedido(
        $id_pedido,
        $id_cliente,
        $valor_total,
        $id_forma_pagamento,
        $valor_frete,
        $itens,
        $origem
    );
}
// =====================
// EXCLUIR PEDIDO
// =====================
if (isset($_POST['excluir_pedido'])) {
    $id_pedido = $_POST['id_pedido'];
    $origem = $_POST['origem'] ?? '';
    $objController->excluir_Pedido($id_pedido, $origem);
}
// =====================
// APROVAR PEDIDO
// =====================
if (isset($_POST['aprovar_pedido'])) {
    $id_pedido = $_POST['id_pedido'];
    $origem = $_POST['origem'];
    $objController->aprovar_Pedido($id_pedido, $origem);
}
// =====================
// CANCELAR PEDIDO
// =====================
if (isset($_POST['cancelar_pedido'])) {
    $id_pedido = $_POST['id_pedido'];
    $status_pedido = $_POST['status_pedido'];
    $origem = $_POST['origem'];
    $objController->cancelar_Pedido($id_pedido, $status_pedido, $origem);
}
// =====================
// FINALIZAR PEDIDO
// =====================
if (isset($_POST['finalizar_pedido'])) {
    $id_pedido = $_POST['id_pedido'];
    $status_pedido = $_POST['status_pedido'];
    $origem = $_POST['origem'];
    $objController->finalizar_Pedido($id_pedido, $status_pedido, $origem);
}


// RELATÓRIOS

//faturamento por mes
if (isset($_POST['consulta_Ano_Faturamento'])) {
    $ano_faturamento = $_POST['ano_faturamento'];
    $mes_faturamento = $_POST['mes_faturamento'];
    $objController->faturamento_Mensal($ano_faturamento, $mes_faturamento);
};
// produtos mais vendidos
if (isset($_POST['produtos_mais_vendidos'])) {
    $limite = $_POST['limite'];
    $objController->produtos_MaisVendidos($limite);
};
// quantidade de pedidos por mes
if (isset($_POST['consulta_qtd_mes'])) {
    $ano_referencia = $_POST['ano_referencia'];
    $mes_referencia = $_POST['mes_referencia'];
    $objController->pedidos_Mes($ano_referencia, $mes_referencia);
};
// quantidade de pedidos por forma de pagamento
if (isset($_POST['qtd_pedido_formaPagamento'])) {
    $objController->qtd_PedidoFormaPagamento();
};
// pedidos por cliente
if (isset($_POST['pedidos_por_cliente'])) {
    $id_cliente = $_POST['id_cliente'];
    $objController->pedidos_Cliente($id_cliente);
};

// Pedidos por Status
if (isset($_POST['pedidos_por_status'])) {
    $status = $_POST['status'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $objController->pedidos_Status($status, $data_inicio, $data_fim);
};
// pedidos com maior valor
if (isset($_POST['pedidos_maior_valor'])) {
    $limite = $_POST['limite'];
    $objController->pedidos_MaiorValor($limite);
}
// produtos que nunca foram vendidos
if (isset($_POST['produtos_nunca_vendidos'])) {
    $objController->produtos_NuncaVendidos();
};
// pedidos recentes
if (isset($_POST['pedidos_recentes'])) {
    $dias = $_POST['dias_recentes'];
    $objController->pedidos_Recentes($dias);
};
//variacao de vendas de produtos
if (isset($_POST['relatorio_variacao'])) {
    $id_produto = $_POST['id_produto'];
    $ano_faturamento = $_POST['ano_faturamento'];
    $objController->variacaoVenda_Produto($id_produto, $ano_faturamento);
};
// Lucro bruto Mensal
if (isset($_POST['relatorio_lucro'])) {
    $ano = $_POST['ano_faturamento'];
    $mes = $_POST['mes_lucro'];
    $objController->lucroBruto_Mensal($ano, $mes);
};

//relatorios de Produtos

// baixo estoque de produtos
if (isset($_POST['buscar_baixo_estoque'])) {
    $limite = $_POST['estoque_limite'];
    $objController->produtosBaixo_estoque($limite);
};
// custos totais por produtos
if (isset($_POST['buscar_custo_produto'])) {
    $id_produto = $_POST['id_produto'];
    $objController->custoTotal_PorProduto($id_produto);
};
// Mostra os produtos por fornecedor
if (isset($_POST['produtos_fornecedor_buscar'])) {
    $id_fornecedor = $_POST['id_fornecedor'];
    $objController->produto_Fornecedor($id_fornecedor);
};
// Margem de lucro dos produtos
if (isset($_POST['buscar_margem_produto'])) {
    // Converte vírgula para ponto no campo de margem
    $valor = str_replace(',', '.', $_POST['limite_margem']);
    // Se o valor for numérico, converte para float; senão, define como null
    $limitePercentual = is_numeric($valor) ? (float)$valor : null;
    $id_produto = $_POST['id_produto'];
    // Se o valor estiver vazio (string vazia), também converte para null
    if ($id_produto === '') $id_produto = null;
    // Regras de negócio: se ambos forem enviados (o que não faz sentido nesse caso), prioriza o produto
    // e ignora o filtro de margem
    if ($id_produto !== null && $limitePercentual !== null) {
        $limitePercentual = null;
    }
    $objController->produto_Margem($limitePercentual, $id_produto);
}
// clientes que mais compraram
if (isset($_POST['cliente_mais_compraram'])) {
    $limite = $_POST['limite_mais_compraram'];
    $ano_referencia = $_POST['ano_referencia'];
    $mes_referencia = $_POST['mes_referencia'];
    $objController->clientes_MaisCompraram($ano_referencia, $mes_referencia, $limite);
}

// Auditoria

// Card 1: Auditorias Gerais (últimos 7 dias)
if (isset($_POST['auditorias_gerais'])) {
    $objController->listar_Auditorias();
}
// Card 2: Auditorias por Usuário
if (isset($_POST['auditorias_por_usuario']) && !empty($_POST['id_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $objController->listar_AuditoriasPorUsuario($id_usuario);
}
// Card 3: Auditorias por Ação
if (isset($_POST['auditorias_por_acao']) && !empty($_POST['acao_auditoria'])) {
    $acao = $_POST['acao_auditoria'];
    $objController->listar_AuditoriasPorAcao($acao);
}
// Card 4: Auditorias por Período
if (isset($_POST['auditorias_por_periodo']) && !empty($_POST['data_inicio_auditoria']) && !empty($_POST['data_fim_auditoria'])) {
    $data_inicio = $_POST['data_inicio_auditoria'];
    $data_fim = $_POST['data_fim_auditoria'];
    $objController->listar_AuditoriasPorPeriodo($data_inicio, $data_fim);
}
// Card 6: Auditorias por Tabela e Período
if (isset($_POST['auditorias_tabela_periodo']) && !empty($_POST['tabela_periodo']) && !empty($_POST['data_inicio_periodo']) && !empty($_POST['data_fim_periodo'])) {
    $tabela = $_POST['tabela_periodo'];
    $data_inicio = $_POST['data_inicio_periodo'];
    $data_fim = $_POST['data_fim_periodo'];
    $objController->listar_AuditoriasTabelaPeriodo($tabela, $data_inicio, $data_fim);
}
// Card 7: Auditorias por Usuário e Período
if (isset($_POST['auditorias_usuario_periodo']) && !empty($_POST['id_usuario']) && !empty($_POST['data_inicio_usuario']) && !empty($_POST['data_fim_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $data_inicio = $_POST['data_inicio_usuario'];
    $data_fim = $_POST['data_fim_usuario'];
    $objController->listar_AuditoriasUsuarioPeriodo($id_usuario, $data_inicio, $data_fim);
}


// Card 5: Auditorias por Usuário e Ação
if (isset($_POST['auditorias_usuario_acao']) && !empty($_POST['id_usuario']) && !empty($_POST['acao_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $acao = $_POST['acao_usuario'];
    $objController->listar_AuditoriasUsuarioAcao($id_usuario, $acao);
}
// Card 8: Auditorias por Usuário, Ação e Período
if (isset($_POST['auditorias_usuario_acao_periodo']) && !empty($_POST['id_usuario']) && !empty($_POST['acao_usuario_periodo']) && !empty($_POST['data_inicio_total']) && !empty($_POST['data_fim_total'])) {
    $id_usuario = $_POST['id_usuario'];
    $acao = $_POST['acao_usuario_periodo'];
    $data_inicio = $_POST['data_inicio_total'];
    $data_fim = $_POST['data_fim_total'];
    $objController->listar_AuditoriasUsuarioAcaoPeriodo($id_usuario, $acao, $data_inicio, $data_fim);
}

// DASHBOARD
if (isset($_POST['action']) && $_POST['action'] === "dashboardDados") {
    $objController->dashboardDados();
}
