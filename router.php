<?php
// Instaciando a classe controller
$objController = new Controller();

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
    $senhaForm = $_POST['senha'];
    $confSenha = $_POST['confSenha'];
    $perfil = intval($_POST['id_perfil']);
    $telefone = limparTelefone($_POST['telefone']);
    $cpf = limparCpf($_POST['cpf']);

    if (!validarSenhasIguais($senhaForm, $confSenha)) {
        $menu = $objController->menu();
        include_once 'view/usuario.php';
        $objController->mostrarMensagemErro("As senhas não coincidem");
        return;
    }

    $senhaHash = password_hash($senhaForm, PASSWORD_DEFAULT);
    $objController->alterar_Usuario($id, $nome, $email, $senhaHash, $perfil, $cpf, $telefone);
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

// Função utilitária para sanitizar nome de perfil
function limparNomePerfil($valor)
{
    return htmlspecialchars(trim($valor));
}
// ================= VERIFICAR PERFIL =================
if (isset($_POST['verificar_perfil'])) {
    $nome_perfil = limparNomePerfil($_POST['nome_perfil']);
    $objController->consultar_Perfil($nome_perfil);
}
// ================= CADASTRAR PERFIL =================
if (isset($_POST['cadastrar_perfil'])) {
    $nome_perfil = limparNomePerfil($_POST['nome_perfil']);
    $objController->cadastrar_Perfil($nome_perfil);
}
// ================= CONSULTAR PERFIL =================
if (isset($_POST['consultar_perfil'])) {
    $nome_perfil = limparNomePerfil($_POST['nome_perfil']);
    $objController->consultar_Perfil($nome_perfil);
}
// ================= ALTERAR PERFIL =================
if (isset($_POST['alterar_perfil'])) {
    $id_perfil = intval($_POST['id_perfil']);
    $nome_perfil = limparNomePerfil($_POST['perfil_usuario']);
    $objController->alterar_Perfil($id_perfil, $nome_perfil);
}
// ================= EXCLUIR PERFIL =================
if (isset($_POST['excluir_perfil'])) {
    $id_perfil = intval($_POST['id_perfil']);
    $nome_perfil = limparNomePerfil($_POST['nome_perfil']);
    $objController->excluir_Perfil($id_perfil, $nome_perfil);
}

// FORNECEDOR

// Instanciar o controller uma única vez
$objController = new Controller();

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
    $telefone_celular = limparTelefone($_POST['telefone_celular']);
    $telefone_fixo = limparTelefone($_POST['telefone_fixo']);

    $objController->cadastrar_Fornecedor($razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo);
}
// ================= ALTERAR FORNECEDOR =================
if (isset($_POST['alterar_fornecedor'])) {
    $id_fornecedor = intval($_POST['id_fornecedor']);
    $razao_social = limparTexto($_POST['razao_social']);
    $cnpj = limparCNPJ($_POST['cnpj']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone_celular = limparTelefone($_POST['telefone_celular']);
    $telefone_fixo = limparTelefone($_POST['telefone_fixo']);

    $objController->alterar_Fornecedor($id_fornecedor, $razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo);
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
// ================= CONSULTAR PRODUTO =================
if (isset($_POST['consultar_produto'])) {
    $nome_produto  = limparTexto($_POST['nome_produto']);
    $tipo_produto  = limparTexto($_POST['tipo_produto']);
    $cor           = limparTexto($_POST['cor']);
    $id_fornecedor = htmlspecialchars($_POST['id_fornecedor']); // se for inteiro, usar (int)

    $objController->consultar_Produto($nome_produto, $tipo_produto, $cor, $id_fornecedor);
}
// ================= CADASTRAR PRODUTO =================
if (isset($_POST['cadastrar_produto'])) {
    $nome_produto      = limparTexto($_POST['nome_produto']);
    $tipo_produto      = limparTexto($_POST['tipo_produto']);
    $cor               = limparTexto($_POST['cor']);
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
        $tipo_produto,
        $cor,
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
    $id_produto         = (int) $_POST['id_produto'];
    $nome_produto       = limparTexto($_POST['nome_produto']);
    $tipo_produto       = limparTexto($_POST['tipo_produto']);
    $cor                = limparTexto($_POST['cor']);
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
        $id_produto,
        $nome_produto,
        $tipo_produto,
        $cor,
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
// ================= VERIFICAR PRODUTO =================
if (isset($_POST['verificar_produto'])) {
    $nome_produto = limparTexto($_POST['nome_produto']);
    $cor          = limparTexto($_POST['cor']);
    $largura      = limparNumero($_POST['largura']);

    $objController->verificar_Produto($nome_produto, $cor, $largura);
}
// ================= EXCLUIR PRODUTO =================
if (isset($_POST['excluir_produto'])) {
    $id_produto   = (int) $_POST['id_produto'];
    $nome_produto = limparTexto($_POST['nome_produto']);
    $objController->excluir_Produto($id_produto, $nome_produto);
}


// CLIENTE

// Função utilitária para remover máscaras
function removerMascara($valor, $mascaras = ['.', '-', '/', '(', ')', ' '])
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
// ================= CADASTRAR PRODUTO =================
if (isset($_POST['cadastrar_cliente'])) {
    $nome_representante = sanitizar($_POST['nome_representante']);
    $razao_social       = sanitizar($_POST['razao_social']);
    $nome_fantasia      = sanitizar($_POST['nome_fantasia']);
    $cnpj_cliente       = limparCNPJ($_POST['cnpj_cliente']);
    $inscricao_estadual = sanitizar($_POST['inscricao_estadual']);
    $telefone_celular   = limparTelefone($_POST['telefone_celular']);
    $telefone_fixo      = limparTelefone($_POST['telefone_fixo']);
    $email              = sanitizar($_POST['email']);
    $limite_credito     = sanitizar($_POST['limite_credito']);
    $cep                = removerMascara($_POST['cep'], ['-']);
    $endereco           = sanitizar($_POST['endereco']);
    $numero_endereco    = sanitizar($_POST['numero_endereco']);
    $complemento        = sanitizar($_POST['complemento']);
    $bairro             = sanitizar($_POST['bairro']);
    $cidade             = sanitizar($_POST['cidade']);
    $estado             = sanitizar($_POST['estado']);

    $objController->cadastrar_Cliente(
        $nome_representante,
        $razao_social,
        $nome_fantasia,
        $cnpj_cliente,
        $email,
        $limite_credito,
        $inscricao_estadual,
        $telefone_celular,
        $telefone_fixo,
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
    $telefone_celular   = removerMascara($_POST['telefone_celular']);
    $telefone_fixo      = removerMascara($_POST['telefone_fixo']);
    $email              = sanitizar($_POST['email']);
    $limite_credito     = sanitizar($_POST['limite_credito']);
    $cep                = removerMascara($_POST['cep'], ['-']);
    $complemento        = sanitizar($_POST['complemento']);
    $bairro             = sanitizar($_POST['bairro']);
    $cidade             = sanitizar($_POST['cidade']);
    $estado             = sanitizar($_POST['estado']);

    $objController->alterar_Cliente(
        $id_cliente,
        $nome_representante,
        $razao_social,
        $nome_fantasia,
        $cnpj_cliente,
        $email,
        $limite_credito,
        $inscricao_estadual,
        $telefone_celular,
        $telefone_fixo,
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
    $descricao = limparDescricao($_POST['descricao'] ?? '');
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
    $objController = new Controller();

    $id_cliente = $_POST['id_cliente'];
    $data_pedido = htmlspecialchars(trim($_POST['data_pedido']));
    $status_pedido = htmlspecialchars(trim($_POST['status_pedido']));
    $valor_total = floatval(str_replace(',', '.', $_POST['valor_total']));
    $id_forma_pagamento = $_POST['id_forma_pagamento'];
    $valor_frete = floatval(str_replace(',', '.', $_POST['valor_frete']));
    $origem = $_POST['origem'];
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

    $objController->cadastrar_Pedido(
        $id_cliente,
        $data_pedido,
        $status_pedido,
        $valor_total,
        $id_forma_pagamento,
        $valor_frete,
        $itens,
        $origem
    );
}
// =====================
// CONSULTAR PEDIDOS
// =====================
if (isset($_POST['buscar_pedidos'])) {
    $objController = new Controller();
    $numero_pedido = htmlspecialchars(trim($_POST['numero_pedido']));
    $id_cliente = $_POST['id_cliente'];
    $data_pedido = htmlspecialchars(trim($_POST['data_pedido']));
    $status_pedido = htmlspecialchars(trim($_POST['status_pedido']));
    $id_forma_pagamento = $_POST['id_forma_pagamento'];
    $origem = $_POST['origem'] ?? '';

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
    $objController = new Controller();
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
    $objController = new Controller();
    $ano_faturamento = $_POST['ano_faturamento'];
    $mes_faturamento = $_POST['mes_faturamento'];
    $objController->faturamento_Mensal($ano_faturamento, $mes_faturamento);
};
// produtos mais vendidos
if (isset($_POST['produtos_mais_vendidos'])) {
    $objController = new Controller();
    $limite = $_POST['limite'];
    $objController->produtos_MaisVendidos($limite);
};
// quantidade de pedidos por mes
if (isset($_POST['consulta_qtd_mes'])) {
    $objController = new Controller();
    $ano_referencia = $_POST['ano_referencia'];
    $mes_referencia = $_POST['mes_referencia'];
    $objController->pedidos_Mes($ano_referencia, $mes_referencia);
};
// qunatidade de pedidos por form ade pagamento
if (isset($_POST['qtd_pedido_formaPagamento'])) {
    $objController = new Controller();
    $objController->qtd_PedidoFormaPagamento();
};
// pedidos por cliente
if (isset($_POST['pedidos_por_cliente'])) {
    $objController = new Controller();
    $id_cliente = $_POST['id_cliente'];
    $objController->pedidos_Cliente($id_cliente);
};
// Pedidos por Status
if (isset($_POST['pedidos_por_status'])) {
    $objController = new Controller();
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
    $objController = new Controller();
    $limite = $_POST['estoque_limite'];
    $objController->produtosBaixo_estoque($limite);
};
// custos totais por produtos
if (isset($_POST['buscar_custo_produto'])) {
    $id_produto = $_POST['id_produto'];
    $objController = new Controller();
    $objController->custoTotal_PorProduto($id_produto);
};
// Mostra os produtos por fornecedor
if (isset($_POST['produtos_fornecedor_buscar'])) {
    $id_fornecedor = $_POST['id_fornecedor'];
    $objController = new Controller();
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
    $objController = new Controller();
    $objController->produto_Margem($limitePercentual, $id_produto);
}
// clientes que mais compraram
if (isset($_POST['cliente_mais_compraram'])) {
    $objController = new Controller();
    $limite = $_POST['limite_mais_compraram'];
    $ano_referencia = $_POST['ano_referencia'];
    $mes_referencia = $_POST['mes_referencia'];

    $objController->clientes_MaisCompraram($ano_referencia, $mes_referencia, $limite);
}
