<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Sistema de Gerenciamento SG3S - Clientes</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- Links Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/modal.css">
</head>

<body>
    <?php
    print $menu;
    ?>
    <main class="container text-center mt-4">
        <div class="text-center mb-4">
            <h1 class="display-9">Sistema de Gerenciamento SG3S</h1>
            <p class="lead">Utilize as opções acima para navegar pelo Sistema</p>
        </div>
        <!-- BOTÕES DE NAVEGAÇÃO ALTERADOS -->
        <div class="row justify-content-center g-3 mt-4">
            <!-- Botões de Pedido -->
            <div class="col-md-auto">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_pedido">
                    <i class="bi bi-plus-circle"></i> Novo Pedido
                </button>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_consulta_pedido">
                    <i class="bi bi-file-earmark-text"></i> Consultar Pedidos
                </button>
            </div>
            <!-- Botões de Forma de Pagamento (separados) -->
            <div class="col-md-auto">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_cadastrar_pagamento">
                    <i class="bi bi-plus-circle"></i> Cadastrar Pagamento
                </button>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_consultar_forma_pagamento">
                    <i class="bi bi-search"></i> Consultar Pagamento
                </button>
            </div>
        </div>
        <!-- Modal de cadastro do pedido -->
        <div class="modal fade" id="modal_pedido" tabindex="-1" aria-labelledby="modalPedidoLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <!-- Cabeçalho -->
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="modalPedidoLabel">
                            <i class="bi bi-cart-plus me-2"></i> Novo Pedido
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <!-- Corpo -->
                    <div class="modal-body">
                        <form action="index.php" method="POST">
                            <input type="hidden" name="origem" value="pedido" id="origem">
                            <div class="row g-4">
                                <!-- Coluna Esquerda: Cliente + Dados do Pedido -->
                                <div class="col-md-4">
                                    <!-- Cliente -->
                                    <fieldset class="border rounded p-3 mb-4">
                                        <legend class="float-none w-auto px-3 fw-semibold text-primary">Cliente</legend>
                                        <div class="mb-3 position-relative">
                                            <label for="cliente_pedido" class="form-label">Buscar Cliente</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                <input type="text" class="form-control" id="cliente_pedido" name="cliente_pedido" placeholder="Nome fantasia, razão social ou CNPJ" autocomplete="off">
                                            </div>
                                            <div id="resultado_busca_cliente" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;"></div>
                                        </div>
                                    </fieldset>
                                    <!-- Dados do Pedido -->
                                    <fieldset class="border rounded p-3 mb-4">
                                        <legend class="float-none w-auto px-3 fw-semibold text-primary">Dados do Pedido</legend>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="frete" class="form-label">Frete</label>
                                                <input type="text" class="form-control frete" id="frete" name="frete" placeholder="R$ 0,00" autocomplete="off">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="valor_total" class="form-label">Valor Total</label>
                                                <input type="text" class="form-control" id="valor_total" name="valor_total" placeholder="R$ 0,00" readonly>
                                            </div>
                                            <div class="col-12">
                                                <?php $this->selectConsultaForma_Pagamento(); ?>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                                <!-- Coluna Direita: Produtos -->
                                <div class="col-md-8">
                                    <fieldset class="border rounded p-3 h-100">
                                        <legend class="float-none w-auto px-3 fw-semibold text-primary">Produtos</legend>
                                        <!-- Busca de Produto -->
                                        <div class="row g-3 align-items-end mb-3">
                                            <div class="col-md-8 position-relative">
                                                <label for="produto_pedido" class="form-label">Buscar Produto</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                    <input type="text" class="form-control" id="produto_pedido" name="produto_pedido" placeholder="Digite o nome, cor ou código do produto" autocomplete="off">
                                                </div>
                                                <div id="resultado_busca_produto" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="quantidade" class="form-label">Quantidade</label>
                                                <div class="input-group">
                                                    <!-- MUDANÇA: type="text" para melhor controle de vírgula -->
                                                    <input type="text" class="form-control" id="quantidade" name="quantidade" min="1" autocomplete="off">
                                                    <button type="button" class="btn btn-outline-primary" id="adicionar_produto">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Lista de Produtos -->
                                        <div class="table-responsive">
                                            <label class="form-label fw-semibold">Produtos do Pedido</label>
                                            <table class="table table-bordered table-striped table-sm align-middle text-center">
                                                <thead class="table-light">
                                                    <!-- AJUSTE: Cabeçalho da tabela igual ao da modal de alterar -->
                                                    <tr>
                                                        <th class="text-start">Produto</th>
                                                        <th>Cor</th>
                                                        <th>Largura (m)</th>
                                                        <th>Quantidade</th>
                                                        <th>Valor Unitário</th>
                                                        <th>Valor Total</th>
                                                        <th>Ação</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody_lista_pedido"></tbody>
                                            </table>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                            <!-- Rodapé -->
                            <div class="modal-footer">
                                <button type="button" id="limpar_pedido" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Limpar
                                </button>
                                <button type="button" class="btn btn-success" id="salvar_pedido" disabled>
                                    <i class="bi bi-check-circle me-1"></i> Finalizar Pedido
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal de consulta de pedidos -->
        <div class="modal fade" id="modal_consulta_pedido" tabindex="-1" aria-labelledby="modalConsultaPedidoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalConsultaPedidoLabel">
                            <i class="bi bi-search"></i> Consultar Pedidos
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <form id="formulario_consulta_pedido" action="index.php" method="POST">
                            <input type="hidden" name="origem" value="pedido">
                            <div class="row g-3">
                                <!-- Número do Pedido -->
                                <div class="col-md-4">
                                    <label for="numero_pedido" class="form-label">Número do Pedido</label>
                                    <input type="text" class="form-control" id="numero_pedido" name="numero_pedido" placeholder="Digite o número do pedido" autocomplete="off">
                                </div>

                                <!-- Data do Pedido -->
                                <div class="col-md-4">
                                    <label for="data_pedido" class="form-label">Data do Pedido</label>
                                    <input type="date" class="form-control" id="data_pedido" name="data_pedido">
                                </div>

                                <!-- Status do Pedido -->
                                <div class="col-md-4">
                                    <label for="status_pedido" class="form-label">Status do Pedido</label>
                                    <select id="status_pedido" name="status_pedido" class="form-select">
                                        <option value="" selected>Selecione o status</option>
                                        <option value="Pendente">Pendente</option>
                                        <option value="Aguardando Pagamento">Aguardando Pagamento</option>
                                        <option value="Finalizado">Finalizado</option>
                                        <option value="Cancelado">Cancelado</option>
                                    </select>
                                </div>

                                <!-- Cliente -->
                                <div class="col-md-8">
                                    <label for="cliente_pedido_consulta" class="form-label">Buscar Cliente</label>
                                    <div class="input-group position-relative">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="cliente_pedido_consulta" placeholder="Nome fantasia, razão social ou CNPJ" autocomplete="off">
                                        <div id="resultado_busca_cliente_consulta" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;"></div>
                                    </div>
                                </div>

                                <!-- Forma de Pagamento -->
                                <div class="col-md-4">
                                    <?php $this->selectConsultaForma_Pagamento($id_forma_pagamento = null); ?>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary" id="buscar_pedidos" name="buscar_pedidos">
                                    <i class="bi bi-search"></i> Buscar Pedidos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal de Cadastro da Forma de Pagamento -->
        <div class="modal fade" id="modal_cadastrar_pagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
            <div class="modal-dialog modal-default modal-dialog-centered">
                <div class="modal-content">
                    <!-- Cabeçalho da modal -->
                    <div class="modal-header">
                        <h6 class="modal-title" id="modalPagamentoLabel">Nova Forma de Pagamento</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <!-- Corpo da modal -->
                    <div class="modal-body">
                        <form action="index.php" method="POST">
                            <!-- Bloco de dados -->
                            <fieldset class="border border-black p-3 mb-4">
                                <legend class="float-none w-auto px-2">Dados da Forma de Pagamento</legend>
                                <div class="mb-3">
                                    <label for="forma_pagamento_cadastro" class="form-label">Descrição da Forma de Pagamento *</label>
                                    <input type="text" class="form-control" id="forma_pagamento_cadastro" name="descricao_cadastro"
                                        required autocomplete="off" placeholder="Digite a forma de pagamento"
                                        pattern="^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s]+$"
                                        title="Somente letras, números e espaços são permitidos" />
                                </div>
                            </fieldset>
                            <!-- Rodapé com botões -->
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button type="reset" class="btn btn-outline-secondary w-50 py-2">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                </button>
                                <button type="submit" name="cadastrar_forma_pagamento" class="btn btn-success w-50 py-2">
                                    <i class="bi bi-plus"></i> Cadastrar
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>
        <!-- Modal Consulta da forma de pagamento -->
        <div class="modal fade" id="modal_consultar_forma_pagamento" tabindex="-1" aria-labelledby="modalConsultaFormaPagamentoLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-search"></i> Consulta de Forma de Pagamento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="index.php" method="post">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <input type="text" class="form-control" id="forma_pagamento_consulta" name="descricao_consulta"
                                        placeholder="Digite a forma de pagamento" autocomplete="off" />
                                    <div class="invalid-feedback">Informe a forma de pagamento.</div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" id="consultar_forma_pagamento" name="consultar_forma_pagamento" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Consultar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal: Calendário -->
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
        <!-- Tabela de forma de pagamento -->
        <div class="container-fluid">
            <div class="row justify-content-center mt-4">
                <div class="col-md-6">
                    <?php
                    // Certifique-se de que $descricao está definida
                    $this->tabelaConsultarForma_Pagamento($formas_pagamento);
                    ?>
                </div>
            </div>
        </div>
        <?php
        foreach ($formas_pagamento as $valor) {
            // modal alterar forma de pagamento
            $this->modalAlterarForma_Pagamento(
                $valor->id_forma_pagamento,
                $valor->descricao
            );
            // modal excluir forma de pagamento
            $this->modalExcluirForma_Pagamento(
                $valor->id_forma_pagamento,
                $valor->descricao
            );
        }
        ?>
        <!-- Tabela da consulta de pedidos -->
        <div class="container-fluid">
            <div class="row justify-content-center mt-4">
                <!-- tabela de Pedidos -->
                <div class="col-md-12">
                    <?php
                    $origem = 'pedido';
                    // Origem da requisição
                    $this->tabelaConsultar_Pedido($pedidos);
                    // Detalhes do Pedido e Modal de Exclusão
                    $this->modalDetalhesPedido($pedidos);
                    // Modal de Excluir Pedido
                    foreach ($pedidos as $key => $valor) {
                        $id_pedido = $valor->id_pedido;
                        $numero_pedido = $valor->numero_pedido;
                        $nome_fantasia = $valor->nome_fantasia;
                        // Modal de Excluir Pedido
                        $this->modalExcluirPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia);
                    };
                    // Modal de Alterar Pedido
                    $this->modalAlterarPedido($pedidos, $pagina);
                    ?>
                    <?php
                    //modal de Aprovar Pedido
                    foreach ($pedidos as $key => $valor) {
                        $id_pedido = $valor->id_pedido;
                        $numero_pedido = $valor->numero_pedido;
                        $nome_fantasia = $valor->nome_fantasia;
                        // Modal de Aprovar Pedido
                        $this->modalAprovarPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia);
                    };
                    // Modal de Finalizar Pedido
                    foreach ($pedidos as $key => $valor) {
                        $id_pedido = $valor->id_pedido;
                        $numero_pedido = $valor->numero_pedido;
                        $nome_fantasia = $valor->nome_fantasia;
                        $this->modalFinalizarPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia);
                    };
                    // Modal de Cancelar Pedido
                    foreach ($pedidos as $key => $valor) {
                        $id_pedido = $valor->id_pedido;
                        $numero_pedido = $valor->numero_pedido;
                        $nome_fantasia = $valor->nome_fantasia;
                        // Modal de Cancelar Pedido
                        $this->modalCancelarPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia);
                    };
                    ?>
                </div>
            </div>
        </div>
    </main>
    <!-- Rodapé -->
    <footer class="text-center py-1 mt-6 bg-light">
        <p>&copy; 2025 Sistema de Gerenciamento SG3S. Todos os direitos reservados.</p>
        <p>Developed by Heymesson Azêvedo.</p>
    </footer>
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <!-- cadastro -->
    <script src="assets/js/cadastroPedido.js"></script>
    <!-- alteração -->
    <script src="assets/js/alteracaoPedido.js"></script>
    <!-- ajax de produtos com baixo estoque-->
    <script src="assets/js/notificacao.js"></script>
</body>

</html>