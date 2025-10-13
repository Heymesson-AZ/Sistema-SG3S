<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Sistema de Gerenciamento SG3S</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Favicon -->
    <link rel="icon" href="img/favicon.ico">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/modal.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
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

        <section class="container mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white py-2 px-3 d-flex align-items-center">
                    <i class="bi bi-lightning-charge me-2"></i>
                    <h6 class="mb-0">Ações Rápidas</h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-2 justify-content-center">
                        <div class="col-6 col-md-4 col-lg-3 d-grid">
                            <button class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#modal_pedido">
                                <i class="bi bi-plus-circle"></i> Novo Pedido
                            </button>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3 d-grid">
                            <button class="btn btn-secondary btn-md" data-bs-toggle="modal" data-bs-target="#modal_consulta_pedido">
                                <i class="bi bi-file-earmark-text"></i> Consultar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container-fluid">
            <div class="row justify-content-center mt-4">
                <div class="col-md-12">
                    <?php
                    // Renderiza a tabela de consulta de pedidos, se houver dados
                    $this->tabelaConsultar_Pedido($pedidos);
                    ?>
                </div>
            </div>
        </div>

        <?php if ($this->temPermissao(['Administrador', 'Administrador Master'])) : ?>
            <section class="container-fluid py-4">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-gradient-secondary text-white d-flex align-items-center">
                                <i class="bi bi-people me-2"></i>
                                <h6 class="mb-0">Clientes que Mais Compraram</h6>
                            </div>
                            <div class="card-body p-3">
                                <div id="graficoClientesQueMaisCompraram" style="height: 380px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-gradient-dark text-white d-flex align-items-center">
                                <i class="bi bi-calendar-week me-2"></i>
                                <h6 class="mb-0">Pedidos por Mês</h6>
                            </div>
                            <div class="card-body p-3">
                                <div id="graficoPedidosPorMes" style="height: 380px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-gradient-primary text-white d-flex align-items-center">
                                <i class="bi bi-bar-chart-line me-2"></i>
                                <h6 class="mb-0">Faturamento Mensal</h6>
                            </div>
                            <div class="card-body p-3">
                                <div id="graficoFaturamentoMensal" style="height: 380px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-gradient-success text-white d-flex align-items-center">
                                <i class="bi bi-box-seam me-2"></i>
                                <h6 class="mb-0">Produtos Mais Vendidos</h6>
                            </div>
                            <div class="card-body p-3">
                                <div id="graficoProdutosMaisVendidos" style="height: 380px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-gradient-warning text-dark d-flex align-items-center">
                                <i class="bi bi-credit-card me-2"></i>
                                <h6 class="mb-0">Formas de Pagamento</h6>
                            </div>
                            <div class="card-body p-3">
                                <div id="graficoFormasPagamento" style="height: 380px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-gradient-info text-white d-flex align-items-center">
                                <i class="bi bi-clock-history me-2"></i>
                                <h6 class="mb-0">Pedidos Recentes</h6>
                            </div>
                            <div class="card-body p-3">
                                <div id="graficoPedidosRecentes" style="height: 380px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <div class="modal fade" id="modal_pedido" tabindex="-1" aria-labelledby="modalPedidoLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="modalPedidoLabel"><i class="bi bi-cart-plus me-2"></i> Novo Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="index.php" method="POST">
                            <input type="hidden" name="origem" value="principal" id="origem">
                            <div class="row g-4">
                                <div class="col-md-4">
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
                                <div class="col-md-8">
                                    <fieldset class="border rounded p-3 h-100">
                                        <legend class="float-none w-auto px-3 fw-semibold text-primary">Produtos</legend>
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
                                                    <input type="text" class="form-control" id="quantidade" name="quantidade" min="1" autocomplete="off">
                                                    <button type="button" class="btn btn-outline-primary" id="adicionar_produto"><i class="bi bi-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <label class="form-label fw-semibold" for="produto_pedido">Produtos do Pedido</label>
                                            <table class="table table-bordered table-striped table-sm align-middle text-center">
                                                <thead class="table-light">
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
                            <div class="modal-footer">
                                <button type="button" id="limpar_pedido" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i> Limpar</button>
                                <button type="button" class="btn btn-success" id="salvar_pedido" disabled><i class="bi bi-check-circle me-1"></i> Finalizar Pedido</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

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
                            <input type="hidden" name="origem" value="principal">

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

        <div class="modal fade" id="modalCalendario" tabindex="-1" aria-labelledby="modalCalendarioLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalCalendarioLabel"><i class="fas fa-calendar-alt me-2"></i> Calendário do Mês</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <?php print $this->Calendario(); ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php
    // Verifica se a variável $pedidos existe e não está vazia para evitar erros
    if (!empty($pedidos) && is_array($pedidos)) {
        $origem = 'principal';

        // Modais que recebem o array completo de pedidos
        $this->modalDetalhesPedido($pedidos);
        $this->modalAlterarPedido($pedidos, $origem);

        // Gera os modais de ação para cada pedido em um único laço
        foreach ($pedidos as $pedido) {
            $this->modalExcluirPedido($origem, $pedido->id_pedido, $pedido->numero_pedido, $pedido->nome_fantasia);
            $this->modalAprovarPedido($origem, $pedido->id_pedido, $pedido->numero_pedido, $pedido->nome_fantasia);
            $this->modalFinalizarPedido($origem, $pedido->id_pedido, $pedido->numero_pedido, $pedido->nome_fantasia);
            $this->modalCancelarPedido($origem, $pedido->id_pedido, $pedido->numero_pedido, $pedido->nome_fantasia);
        }
    }
    ?>
    <footer class="text-center py-1 mt-6 bg-light">
        <p>&copy; 2025 Sistema de Gerenciamento SG3S. Todos os direitos reservados.</p>
        <p>Developed by Heymesson Azêvedo.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/cadastroPedido.js"></script>
    <script src="assets/js/alteracaoPedido.js"></script>

    <script src="assets/js/notificacao.js"></script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="assets/js/charts.js"></script>
</body>

</html>