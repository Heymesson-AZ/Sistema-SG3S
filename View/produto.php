    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <title>Sistema de Gerenciamento SG3S</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <!-- Links Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <!-- Favicon -->
        <link rel="icon" href="img/favicon.ico">
        <!-- CSS Personalizado -->
        <link rel="stylesheet" href="assets/css/index.css">
    </head>

    <body>
        <main class="container-fluid mt-4">
            <div class="text-center mb-4">
                <h1 class="display-9">Sistema de Gerenciamento SG3S</h1>
                <p class="lead">Utilize as opções acima para navegar pelo Sistema</p>
            </div>
            <div class="row justify-content-center g-4 mt-4">
                <?php if ($this->temPermissao(['Administrador'])): ?>
                    <!-- Linha de Cadastros -->
                    <div class="col-md-auto">
                        <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal_verificar_produto">
                            <i class="bi bi-plus-circle"></i> Cadastrar Produto
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal_cnpj">
                            <i class="bi bi-plus-circle"></i> Cadastrar Fornecedor
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal_cor">
                            <i class="bi bi-plus-lg"></i> Cadastrar Cor
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal_tipo_produto">
                            <i class="bi bi-plus-lg"></i> Cadastrar Tipo de Produto
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Linha de Consultas -->
                <div class="w-100"></div> <!-- quebra de linha -->
                <div class="col-md-auto">
                    <button class="btn btn-secondary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal_consultar_produto">
                        <i class="bi bi-file-earmark-text"></i> Consultar Produto
                    </button>
                </div>
                <?php if ($this->temPermissao(['Administrador'])): ?>
                    <div class="col-md-auto">
                        <button class="btn btn-secondary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal_consulta_fornecedor">
                            <i class="bi bi-file-earmark-text"></i> Consultar Fornecedor
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-secondary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal_consultar_cor">
                            <i class="bi bi-file-earmark-text"></i> Consultar Cor
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-secondary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal_consultar_tipo_produto">
                            <i class="bi bi-file-earmark-text"></i> Consultar Tipo de Produto
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal de Verificação de Produto -->
            <div class="modal fade" id="modal_verificar_produto" tabindex="-1" aria-labelledby="modalVerificarProdutoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <!-- Cabeçalho -->
                        <div class="modal-header">
                            <h6 class="modal-title" id="modalVerificarProdutoLabel">Consultar Produto</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <!-- Corpo -->
                        <div class="modal-body">
                            <form action="index.php" method="POST" id="formulario_verificar_produto">
                                <fieldset class="border border-black p-3 mb-4">
                                    <legend class="float-none w-auto px-2">Informe os dados do Produto</legend>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="nome_produto" class="form-label">Nome do Produto *</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="nome_produto" name="nome_produto"
                                                    placeholder="Digite o nome do produto" required autocomplete="off" />
                                            </div>
                                            <br>
                                            <label for="cor" class="form-label">Cor *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                <input type="hidden" id="id_cor_hidden" name="id_cor" value="" />
                                                <input type="text" class="form-control" id="cor" placeholder="Digite a cor" autocomplete="off" required />
                                                <div id="resultado_busca_cor" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height:200px; overflow-y:auto;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="largura" class="form-label">Largura (m) *</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="largura" name="largura"
                                                    placeholder="Informe a largura" required autocomplete="off" />
                                            </div>
                                            <br>
                                            <label for="id_fornecedor_produto" class="form-label">Fornecedor</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                <input type="hidden" id="id_fornecedor_hidden_verificar" name="id_fornecedor" value="" />
                                                <input type="text" class="form-control" id="id_fornecedor_produto_verificar"
                                                    placeholder="Digite o fornecedor" autocomplete="off" />
                                                <div id="resultado_busca_fornecedor_verificar"
                                                    class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow"
                                                    style="max-height:200px; overflow-y:auto; z-index:1050;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <!-- Rodapé com botões -->
                                <div class="d-flex justify-content-center gap-2 mt-4">
                                    <button type="reset" class="btn btn-outline-secondary w-50 py-2">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                    </button>
                                    <button type="submit" name="verificar_produto" class="btn btn-success w-50 py-2">
                                        <i class="bi bi-check-circle-fill"></i> Verificar
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Modal de Consulta de Produto -->
            <div class="modal fade" id="modal_consultar_produto" tabindex="-1" aria-labelledby="modalConsultaProdutoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-search"></i> Consulta de Produto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form action="index.php" method="POST">
                                <div class="row g-3 mt-2">
                                    <!-- Nome do Produto -->
                                    <div class="col-md-6">
                                        <label for="nome_produto_consulta" class="form-label">Nome do Produto</label>
                                        <input type="text" class="form-control" id="nome_produto_consulta" name="nome_produto"
                                            placeholder="Nome do produto" autocomplete="off" />

                                        <!-- Tipo do Produto -->
                                        <br>
                                        <label for="tipo_produto_consulta" class="form-label">Tipo do Produto</label>
                                        <div class="input-group">
                                            <input type="hidden" id="id_tipo_hidden_consulta" name="id_tipo_produto" value="" />
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="tipo_produto_consulta"
                                                placeholder="Ex: Tecido, Plástico" autocomplete="off" />
                                            <div id="resultado_busca_tipo_consulta"
                                                class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow"
                                                style="max-height:200px; overflow-y:auto; z-index:1050;">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Tipo do Produto -->
                                    <div class="col-md-6">
                                        <label for="cor_consulta" class="form-label">Cor *</label>
                                        <div class="input-group">
                                            <input type="hidden" id="id_cor_hidden_consulta" name="id_cor" value="" />
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="cor_consulta" placeholder="Digite a cor" autocomplete="off" />
                                            <div id="resultado_busca_cor_consulta"
                                                class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow"
                                                style="max-height:200px; overflow-y:auto; z-index:1050;">
                                            </div>

                                        </div>
                                        <!-- fornecedor -->
                                        <br>
                                        <label for="id_fornecedor_produto" class="form-label">Fornecedor</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="hidden" id="id_fornecedor_hidden" name="id_fornecedor" value="" />
                                            <input type="text" class="form-control" id="id_fornecedor_produto"
                                                placeholder="Digite o fornecedor" autocomplete="off" />
                                            <div id="resultado_busca_fornecedor"
                                                class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow"
                                                style="max-height:200px; overflow-y:auto; z-index:1050;"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Botão Consultar -->
                                <div class="text-center mt-4">
                                    <button type="submit" id="consultar_produto" name="consultar_produto" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Consultar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de cadastro de tipo de produto -->
            <div class="modal fade" id="modal_tipo_produto" tabindex="-1" aria-labelledby="modalTipoProdutoLabel" aria-hidden="true">
                <div class="modal-dialog modal-default modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="modalTipoProdutoLabel">Novo Tipo de Produto</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <form action="index.php" method="POST">
                                <fieldset class="border border-black p-3 mb-4">
                                    <legend class="float-none w-auto px-2">Dados do Tipo de Produto</legend>
                                    <div class="mb-3">
                                        <label for="nome_tipo_produto" class="form-label">Nome do Tipo de Produto *</label>
                                        <input type="text" class="form-control" id="nome_tipo_produto" name="nome_tipo_produto"
                                            required autocomplete="off" placeholder="Digite o nome do tipo de produto"
                                            pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"
                                            title="Somente letras e espaços são permitidos" />
                                    </div>
                                </fieldset>
                                <!-- Rodapé com botões -->
                                <div class="d-flex justify-content-center gap-2 mt-4">
                                    <button type="reset" class="btn btn-outline-secondary w-50 py-2">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                    </button>
                                    <button type="submit" name="cadastrar_tipo_produto" class="btn btn-success w-50 py-2">
                                        <i class="bi bi-plus"></i> Cadastrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de cadastro de Cor -->
            <div class="modal fade" id="modal_cor" tabindex="-1" aria-labelledby="modalCorLabel" aria-hidden="true">
                <div class="modal-dialog modal-default modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="modalCorLabel">Nova Cor</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <form action="index.php" method="POST" id="formulario_cor_cadastro">
                                <fieldset class="border border-black p-3 mb-4">
                                    <legend class="float-none w-auto px-2">Dados da Cor</legend>
                                    <div class="mb-3">
                                        <label for="nome_cor" class="form-label">Nome da Cor *</label>
                                        <input type="text" class="form-control" id="nome_cor" name="nome_cor"
                                            required autocomplete="off" placeholder="Digite o nome da cor"
                                            pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"
                                            title="Somente letras e espaços são permitidos" />
                                    </div>
                                </fieldset>
                                <!-- Rodapé com botões -->
                                <div class="d-flex justify-content-center gap-2 mt-4">
                                    <button type="reset" class="btn btn-outline-secondary w-50 py-2">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                    </button>
                                    <button type="submit" name="cadastrar_cor" class="btn btn-success w-50 py-2">
                                        <i class="bi bi-plus"></i> Cadastrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Consulta de Tipo de Produto -->
            <div class="modal fade" id="modal_consultar_tipo_produto" tabindex="-1" aria-labelledby="modalTipoProdutoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-search"></i> Consulta de Tipo de Produto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <form action="index.php" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="nome_tipo_produto" class="form-label">Nome do Tipo de Produto</label>
                                        <input type="text" class="form-control" id="nome_tipo_produto" name="nome_tipo_produto"
                                            placeholder="Digite o nome do tipo de produto"
                                            autocomplete="off"
                                            pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"
                                            title="Somente letras e espaços são permitidos" />
                                        <div class="invalid-feedback">Informe o nome do tipo de produto.</div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="consultar_tipo_produto" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Consultar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Consulta de Cor -->
            <div class="modal fade" id="modal_consultar_cor" tabindex="-1" aria-labelledby="modalCorLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-search"></i> Consulta de Cor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <form action="index.php" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="nome_cor" class="form-label">Nome da Cor</label>
                                        <input type="text" class="form-control" id="nome_cor" name="nome_cor"
                                            placeholder="Digite o nome da cor"
                                            autocomplete="off"
                                            pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"
                                            title="Somente letras e espaços são permitidos" />
                                        <div class="invalid-feedback">Informe o nome da cor.</div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="consultar_cor" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Consultar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Consulta de Fornecedor -->
            <div class="modal fade" id="modal_consulta_fornecedor" tabindex="-1" aria-labelledby="modalConsultaFornecedorLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-search"></i> Consulta de Fornecedor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form action="index.php" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="razao_social_consulta" class="form-label">Razão Social</label>
                                        <input type="text" class="form-control" id="razao_social_consulta" name="razao_social"
                                            placeholder="Digite a razão social" autocomplete="off" />
                                        <div class="invalid-feedback">Informe a razão social.</div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" id="consultar_fornecedor" name="consultar_fornecedor" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Consultar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Verificação de CNPJ do Fornecedor -->
            <div class="modal fade" id="modal_cnpj" tabindex="-1" aria-labelledby="modalCnpjLabel" aria-hidden="true">
                <div class="modal-dialog modal-default modal-dialog-centered">
                    <div class="modal-content">
                        <!-- Cabeçalho -->
                        <div class="modal-header">
                            <h6 class="modal-title" id="modalCnpjLabel">Consultar CNPJ do Fornecedor</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <!-- Corpo -->
                        <div class="modal-body">
                            <form action="index.php" method="POST" id="formulario_consulta_cnpj">
                                <fieldset class="border border-black p-3 mb-4">
                                    <legend class="float-none w-auto px-2">Informe o CNPJ</legend>
                                    <div class="mb-3">
                                        <label for="cnpj_consulta" class="form-label">CNPJ *</label>
                                        <input type="text" class="form-control cnpj " id="cnpj_consulta" name="cnpj_consulta"
                                            placeholder="00.000.000/0000-00" required autocomplete="off"
                                            pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}"
                                            title="Formato esperado: XX.XXX.XXX/XXXX-XX" />
                                        <div class="invalid-feedback">Informe um CNPJ válido.</div>
                                    </div>
                                </fieldset>
                                <!-- Rodapé com botões -->
                                <div class="d-flex justify-content-center gap-2 mt-4">
                                    <button type="reset" class="btn btn-outline-secondary w-50 py-2">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                    </button>
                                    <button type="submit" name="verificar_cnpj" class="btn btn-success w-50 py-2">
                                        <i class="bi bi-check-circle-fill"></i> Verificar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <?php
                if (isset($_SESSION['cnpj_cadastro'])) {
                    $this->modal_CadastroFornecedor($_SESSION['cnpj_cadastro']);
                    unset($_SESSION['cnpj_cadastro']);
                }
                ?>
            </div>
            <div class="container-fluid">
                <?php
                if (isset($_SESSION['produto_cadastro'])) {
                    $this->modal_CadastroProduto($_SESSION['produto_cadastro']);
                    unset($_SESSION['produto_cadastro']);
                }
                ?>
            </div>
            <div class="container-fluid">
                <div class="row justify-content-center mt-4">
                    <!-- tabela de fornecedores -->
                    <div class="col-md-12">
                        <?php $this->tabelaConsultarFornecedor($fornecedor); ?>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row justify-content-center mt-4">
                    <div class="col-md-9">
                        <!-- tabela de produtos -->
                        <?php if (isset($produto)) {
                            $this->tabelaConsultarProduto($produto);
                        };
                        ?>
                    </div>
                </div>
            </div>
            <?php
            // fornecedor
            foreach ($fornecedor as $key => $valor) {
                //modal alterar usuario
                $this->modal_AlterarFornecedor(
                    $valor->id_fornecedor,
                    $valor->razao_social,
                    $valor->cnpj_fornecedor,
                    $valor->email,
                    $valor->telefones,
                );
                //modal excluir usuario
                $this->modalExcluirFornecedor(
                    $valor->id_fornecedor,
                    $valor->razao_social
                );
                // modal de detalhes de Fornecedor
                $this->modalDetalhesFornecedor($fornecedor);
            };
            // produto
            foreach ($produto as $key => $valor) {
                //modal alterar usuario
                $this->modalAlterarProduto(
                    $valor->id_produto,
                    $valor->nome_produto,
                    $valor->tipo_produto,
                    $valor->id_tipo_produto,
                    $valor->id_cor,
                    $valor->cor,
                    $valor->composicao,
                    $valor->quantidade,
                    $valor->quantidade_minima,
                    $valor->largura,
                    $valor->custo_compra,
                    $valor->valor_venda,
                    $valor->data_compra,
                    $valor->ncm_produto,
                    $valor->fornecedor,
                    $valor->img_produto,
                    $valor->id_fornecedor,
                );
                //modal excluir usuario
                $this->modalExcluirProduto(
                    $valor->id_produto,
                    $valor->nome_produto
                );
                // modal de detalhes de Produtos
                $this->modalDetalhesProduto($produto);
            };
            ?>
        </main>
        <div class="modal fade" id="modalCalendario" tabindex="-1" aria-labelledby="modalCalendarioLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalCalendarioLabel"><i class="fas fa-calendar-alt me-2"></i>Calendário do Mês</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <?php print $this->Calendario(); ?>
                    </div>
                </div>
            </div>
        </div>
        <footer class="text-center py-1 mt-6 bg-light">
            <p>&copy; 2025 Sistema de Gerenciamento SG3S. Todos os direitos reservados.</p>
            <p>Developed by Heymesson Azêvedo.</p>
        </footer>

        <!-- jQuery 3.7.1 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <!-- jQuery Mask Plugin 1.14.16 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

        <!-- Popper.js 2.11.8 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>

        <!-- Bootstrap 5.3.3 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

        <!-- Seu arquivo JavaScript personalizado -->
        <script src="assets/js/produto.js"></script>
        <script src="assets/js/fornecedor.js"></script>
        <!-- ajax de produtos com baixo estoque-->
        <script src="assets/js/notificacao.js"></script>
    </body>

    </html>