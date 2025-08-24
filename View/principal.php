<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Sistema de Gerenciamento SG3S</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Links Bootstrap -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (opcional, mas útil) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome (para os ícones do menu, se usado) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

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
            <h1 class="display-5">Sistema de Gerenciamento SG3S</h1>
            <p class="lead">Utilize as opções acima para navegar pelo Sistema</p>
        </div>
        <div class="row justify-content-center g-3 mt-4">
            <div class="col-md-auto">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_pedido">
                    <i class="bi bi-plus-circle"></i> Novo Pedido</button>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_consulta_pedido">
                    <i class="bi bi-file-earmark-text"></i> Consultar Pedidos</button>
            </div>
        </div>
        <!-- Modal de cadastro do pedido -->
        <div class="modal fade" id="modal_pedido" tabindex="-1" aria-labelledby="modalPedidoLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="modalPedidoLabel">Novo Pedido</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="index.php" method="POST" id="formulario_pedido">
                            <input type="hidden" name="origem" id="origem" value="principal">
                            <div class="row g-4">
                                <!-- Lado esquerdo: cliente e dados do pedido -->
                                <div class="col-md-6">
                                    <!-- Seção Cliente -->
                                    <fieldset class="border border-black p-3 mb-4 h-80">
                                        <legend class="float-none w-auto px-3">Cliente</legend>
                                        <div class="mb-3 position-relative">
                                            <label for="cliente_pedido" class="form-label">Buscar Cliente</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                <input type="text" class="form-control" id="cliente_pedido" name="cliente_pedido" placeholder="Nome fantasia, razão social ou CNPJ" autocomplete="off" />
                                            </div>
                                            <div id="resultado_busca_cliente" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;"></div>
                                        </div>
                                    </fieldset>
                                    <!-- Seção Dados do Pedido -->
                                    <fieldset class="border border-black p-3 mb-4 h-100">
                                        <legend class="float-none w-auto px-4">Dados do Pedido</legend>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="data" class="form-label">Data</label>
                                                <input type="date" id="data" name="data" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly />
                                            </div>
                                            <div class="col-md-4">
                                                <label for="frete" class="form-label">Frete</label>
                                                <input type="text" class="form-control" id="frete" name="frete" placeholder="R$ 0,00" autocomplete="off" />
                                            </div>
                                            <div class="col-md-4">
                                                <label for="valor_total" class="form-label">Valor Total</label>
                                                <input type="text" class="form-control" id="valor_total" name="valor_total" placeholder="R$ 0,00" readonly />
                                            </div>
                                            <div class="col-md-6">
                                                <?php $this->selectConsultaForma_Pagamento(); ?>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <!-- Lado direito: produtos -->
                                <div class="col-md-6">
                                    <fieldset class="border border-black p-3 mb-4 h-100">
                                        <legend class="float-none w-auto px-3">Produtos</legend>
                                        <div class="mb-3 row">
                                            <div class="col-md-8 position-relative">
                                                <label for="produtos" class="form-label">Buscar Produto</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                    <input type="text" class="form-control" id="produto_pedido" name="produto_pedido" placeholder="Digite o nome, cor ou código do produto" autocomplete="off" />
                                                </div>
                                                <div id="resultado_busca_produto" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="quantidade" class="form-label">Quantidade</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" autocomplete="off" />
                                                    <button type="button" class="btn btn-outline-primary" id="adicionar_produto">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive mt-3">
                                            <label class="form-label" for="produtos">Produtos do Pedido</label>
                                            <table class="table table-bordered table-striped table-sm align-middle text-center">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Produto</th>
                                                        <th>Qtd</th>
                                                        <th>Valor Unit.</th>
                                                        <th>Valor Total</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody_lista_pedido"></tbody>
                                            </table>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                            <!-- Ações -->
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" id="limpar_pedido" class="btn btn-outline-secondary w-100 py-2">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success w-100 py-2" name="salvar_pedido" id="salvar_pedido">
                                        <i class="bi bi-check-circle"></i> Finalizar Pedido
                                    </button>
                                </div>
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
                        <h5 class="modal-title" id="modalConsultaPedidoLabel"><i class="bi bi-search"></i>Consultar Pedidos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario_consulta_pedido" action="index.php" method="POST">
                            <input type="hidden" name="origem" value="principal">
                            <div class="row g-4">
                                <!-- Numero do Pedido -->
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
                                <div class="col-md-4">
                                    <label for="cliente" class="form-label">Cliente</label>
                                    <?php $this->selectClientes($id_cliente = null); ?>
                                </div>
                                <div class="col-md-4">
                                    <?php $this->selectConsultaForma_Pagamento($id_forma_pagamento = null); ?>
                                </div>
                            </div>
                            <!-- Botão Buscar Pedidos -->
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary mb-3" id="buscar_pedidos" name="buscar_pedidos">
                                    <i class="bi bi-search"></i> Buscar Pedidos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Calendario -->
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
        <!-- Tabela da consulta de pedidos -->
        <div class="container-fluid">
            <div class="row justify-content-center mt-4">
                <!-- tabela de Pedidos -->
                <div class="col-md-12">
                    <?php
                    // Origem da requisição
                    $origem = 'principal';
                    $this->tabelaConsultar_Pedido($pedidos);
                    // Detalhes do Pedido e Modal de Exclusão
                    $this->modalDetalhesPedido($pedidos);
                    // Modais de Aprovar e Excluir Pedido
                    // Percorre os pedidos para criar os modais de exclusão e aprovação
                    foreach ($pedidos as $key => $valor) {
                        $id_pedido = $valor->id_pedido;
                        $numero_pedido = $valor->numero_pedido;
                        $nome_fantasia = $valor->nome_fantasia;
                        // Modal de Excluir Pedido
                        $this->modalExcluirPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia);
                    };
                    $this->modalAlterarPedido($pedidos);
                    ?>
                    <?php
                    foreach ($pedidos as $key => $valor) {
                        $id_pedido = $valor->id_pedido;
                        $numero_pedido = $valor->numero_pedido;
                        $nome_fantasia = $valor->nome_fantasia;
                        // Modal de Aprovar Pedido
                        $this->modalAprovarPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia);
                    };
                    foreach ($pedidos as $key => $valor) {
                        $id_pedido = $valor->id_pedido;
                        $numero_pedido = $valor->numero_pedido;
                        $nome_fantasia = $valor->nome_fantasia;
                        // Modal de Aprovar Pedido
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
    <footer class="text-center py-1 mt-6 bg-light">
        <p>&copy; 2025 Sistema de Gerenciamento SG3S. Todos os direitos reservados.</p>
        <p>Developed by Heymesson Azêvedo.</p>
    </footer>
    <!-- jQuery 3.7.1 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- jQuery Mask Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- script personalizado -->
    <!-- cadastro -->
    <script src="assets/js/cadastroPedido.js"></script>
    <!-- alteração -->
    <script src="assets/js/alteracaoPedido.js"></script>
    <!-- ajax de produtos com baixo estoque-->
    <script src="assets/js/notificacao.js"></script>
</body>

</html>