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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" href="img/favicon.ico">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/relatorio.css">
</head>

<body>
    <?php
    print $menu;
    ?>
    <main class="container text-center">
        <div class="text-center mb-4">
            <h1 class="display-9">Sistema de Gerenciamento SG3S</h1>
            <p class="lead">Utilize as opções acima para navegar pelo Sistema</p>
        </div>
        <h4 class="mb-4 text-center">Relatórios Gerenciais</h4>
        <br>
        <div class="row g-3 justify-content-center">
            <!-- Faturamento mensal -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <input type="hidden" name="tipo_relatorio" value="faturamento_mensal" />
                    <h6>Faturamento Detalhado</h6>
                    <small class="text-muted">Mostra o faturamento por mês ou mês específico com estatísticas.</small>
                    <div class="mb-2 mt-2">
                        <label for="ano_faturamento" class="form-label">Ano:</label>
                        <input type="number" id="ano_faturamento" name="ano_faturamento" class="form-control"
                            value="<?= date('Y') ?>" min="2000" max="<?= date('Y') ?>" />
                    </div>
                    <div class="mb-2">
                        <label for="mes_faturamento" class="form-label">Mês (opcional):</label>
                        <select id="mes_faturamento" name="mes_faturamento" class="form-select">
                            <option value="">Todos os Meses</option>
                            <?php
                            $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                            foreach ($meses as $i => $nome) {
                                print "<option value='" . ($i + 1) . "'>$nome</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="consulta_Ano_Faturamento">
                        <i class="bi bi-currency-dollar me-1"></i> Gerar
                    </button>
                </form>
            </div>
            <!-- Pedidos por mês -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <input type="hidden" name="tipo_relatorio" value="pedidos_por_mes" />
                    <h6>Pedidos por Mês</h6>
                    <small class="text-muted">Mostra a quantidade de pedidos realizados por mês no ano escolhido.</small>
                    <div class="mb-2 mt-2">
                        <label for="ano" class="form-label">Ano: </label>
                        <input type="number" id="ano_referencia" name="ano_referencia" class="form-control" value="<?= date('Y') ?>" min="2000" max="<?= date('Y') ?>" />
                    </div>
                    <div class="mb-2">
                        <label for="mes_referencia" class="form-label">Mês (opcional):</label>
                        <select id="mes_referencia" name="mes_referencia" class="form-select">
                            <option value="">Todos os Meses</option>
                            <?php
                            $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                            foreach ($meses as $i => $nome) {
                                print "<option value='" . ($i + 1) . "'>$nome</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="consulta_qtd_mes"><i class="bi bi-calendar-event me-1"></i> Gerar</button>
                </form>
            </div>
            <!-- Produtos mais vendidos -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <input type="hidden" name="tipo_relatorio" value="produtos_mais_vendidos" />
                    <h6>Produtos Mais Vendidos</h6>
                    <small class="text-muted">Exibe os produtos mais vendidos.</small>
                    <div class="mb-3 mt-2">
                        <label for="limite" class="form-label">Limite de Produtos: </label>
                        <input type="number" id="limite" name="limite" class="form-control" value="1" min="1" max="50" />
                    </div>
                    <button type="submit" class="btn btn-primary  mt-2" name="produtos_mais_vendidos"><i class="bi bi-bar-chart-line me-1"></i> Gerar</button>
                </form>
            </div>
            <!-- Formas de Pagamento -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start p-2">
                    <input type="hidden" name="tipo_relatorio" value="formas_pagamento" />
                    <h6>Formas de Pagamento:</h6>
                    <small class="text-muted">Pedidos por forma de pagamento.</small>
                    <button type="submit" class="btn btn-primary mt-1" name="qtd_pedido_formaPagamento"><i class="bi bi-credit-card me-1"></i> Gerar</button>
                </form>
            </div>
            <!-- Clientes que mais compraram -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Clientes que Mais Compraram</h6>
                    <small class="text-muted">Lista os clientes com maior volume de compras.</small>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Ano:</label>
                        <input type="number" id="ano_referencia" name="ano_referencia" class="form-control"
                            value="<?= date('Y') ?>" min="2000" max="<?= date('Y') ?>" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mês:</label>
                        <select name="mes_referencia" class="form-select">
                            <option value="">Todos os Meses</option>
                            <?php foreach ($meses as $i => $nome) {
                                print "<option value='" . ($i + 1) . "'>$nome</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Limite:</label>
                        <input type="number" name="limite_mais_compraram" class="form-control" min="1" max="10" value="1" placeholder="Ex: 10">
                    </div>
                    <button type="submit" name="cliente_mais_compraram" class="btn btn-primary mt-2">Gerar</button>
                </form>
            </div>
            <!-- Pedidos Recentes -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Pedidos Recentes</h6>
                    <small class="text-muted">Consulta pedidos dos últimos X dias.</small>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Quantidade de Dias:</label>
                        <input type="number" name="dias_recentes" class="form-control" min="1" value="7" max="30" placeholder="Ex: 7">
                    </div>
                    <button type="submit" name="pedidos_recentes" class="btn btn-primary mt-2">Gerar</button>
                </form>
            </div>
            <!-- Variação de Vendas por Produto -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Variação de Vendas por Produto</h6>
                    <small class="text-muted">Compara vendas mês a mês de um produto.</small>
                    <div class="col-md-12">
                        <label for="produto_venda" class="form-label">Buscar Produto</label>
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="hidden" id="id_produto_hidden_venda" value="" name="id_produto" />
                                <input type="text" class="form-control" id="produto_venda" placeholder="Digite o nome do produto" autocomplete="off" required />
                            </div>
                            <div id="resultado_busca_produto_venda" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ano:</label>
                        <input type="number" id="ano_faturamento" name="ano_faturamento" class="form-control"
                            value="<?= date('Y') ?>" min="2000" max="<?= date('Y') ?>" required />
                    </div>
                    <button type="submit" name="relatorio_variacao" class="btn btn-primary mt-2">Gerar</button>
                </form>
            </div>
            <!-- Lucro Bruto Mensal -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Lucro Bruto Mensal</h6>
                    <small class="text-muted">Mostra o lucro bruto consolidado por mês.</small>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Ano:</label>
                        <input type="number" id="ano_faturamento" name="ano_faturamento" class="form-control"
                            value="<?= date('Y') ?>" min="2000" max="<?= date('Y') ?>" required />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mês:</label>
                        <select name="mes_lucro" class="form-select">
                            <option value="">Todos os Meses</option>
                            <?php foreach ($meses as $i => $nome) {
                                print "<option value='" . ($i + 1) . "'>$nome</option>";
                            } ?>
                        </select>
                    </div>
                    <button type="submit" name="relatorio_lucro" class="btn btn-primary mt-2">Gerar</button>
                </form>
            </div>
            <!-- Total de pedidos por cliente -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Total de Pedidos por Cliente</h6>
                    <small class="text-muted">Quantidade de pedidos e valor total por cliente.</small>
                    <div class="mb-3 mt-2">
                        <label for="id_cliente_hist" class="form-label">Cliente:</label>
                        <?php
                        $this->selectClientes($id_cliente = null);
                        ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="pedidos_por_cliente"><i class="bi bi-people-fill me-1"></i> Gerar</button>
                </form>
            </div>
            <!-- Pedidos por status -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <input type="hidden" name="tipo_relatorio" value="pedidos_por_status" />
                    <h6>Pedidos por Status</h6>
                    <small class="text-muted">Total por status atual ou em intervalo.</small>
                    <div class="mb-2 mt-2">
                        <label for="status" class="form-label">Status:</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="Aguardando Pagamento">Aguardando Pagamento</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Pendente">Pendente</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="data_inicio" class="form-label">De:</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label for="data_fim" class="form-label">Até:</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="pedidos_por_status"><i class="bi bi-diagram-3-fill me-1"></i> Gerar</button>
                </form>
            </div>
            <!-- Pedidos com maior valor -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <input type="hidden" name="tipo_relatorio" value="pedidos_maior_valor" />
                    <h6>Pedidos com Maior Valor</h6>
                    <small class="text-muted">Exibe os pedidos com maior valor total em pedidos finalizados.</small>
                    <div class="mb-2 mt-2">
                        <label for="limite_valor" class="form-label">Limite:</label>
                        <input type="number" id="limite_valor" name="limite_valor" class="form-control" value="10" min="1" max="100" />
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="pedidos_maior_valor"><i class="bi bi-cash-stack me-1"></i> Gerar</button>
                </form>
            </div>
            <!-- Produtos nunca vendidos -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Produtos Nunca Vendidos</h6>
                    <small class="text-muted">Exibe produtos que nunca foram incluídos em pedidos finalizados.</small>
                    <button type="submit" class="btn btn-primary mt-4" name="produtos_nunca_vendidos"><i class="bi bi-box-seam me-1"></i> Gerar</button>
                </form>
            </div>
            <br>
            <!-- Produtos com Baixo Estoque -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Produtos com Baixo Estoque</h6>
                    <small class="text-muted">Lista produtos com estoque abaixo do limite definido ou da quantidade mínima.</small>
                    <div class="mb-3 mt-2">
                        <label for="estoque_limite" class="form-label">Limite de Estoque: </label>
                        <input type="number" id="estoque_limite" name="estoque_limite" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="buscar_baixo_estoque"><i class="bi bi-exclamation-triangle me-1"></i> Gerar</button>
                </form>
            </div>
            <!-- Custo Total por Produto em Pedidos Realizados-->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Custo Total por Produto</h6>
                    <small class="text-muted">Mostra o custo total investido em cada produto em pedidos finalizados.</small>
                    <div class="col-md-12">
                        <label for="produto_custo" class="form-label">Buscar Produto</label>
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="hidden" id="id_produto_hidden" name="id_produto" value="" />
                                <input type="text" class="form-control" id="produto_custo" placeholder="Digite o nome do produto" autocomplete="off" />
                            </div>
                            <div id="resultado_busca_produto" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4" name="buscar_custo_produto">
                        <i class="bi bi-cash-coin me-1"></i> Gerar
                    </button>
                </form>
            </div>
            <!-- Produtos por Fornecedor -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <input type="hidden" name="tipo_relatorio" value="produtos_por_fornecedor" />
                    <h6>Produtos por Fornecedor</h6>
                    <small class="text-muted">Lista todos os produtos de um fornecedor selecionado.</small>
                    <div class="col-md-12">
                        <label for="produto_custo" class="form-label">Buscar Fornecedor</label>
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="hidden" id="id_fornecedor_hidden" name="id_fornecedor" value="" />
                                <input type="text" class="form-control" id="id_fornecedor_produto" placeholder="Digite o nome do fornecedor" autocomplete="off" />
                            </div>
                            <div id="resultado_busca_fornecedor" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4" name="produtos_fornecedor_buscar">
                        <i class="bi bi-cash-coin me-1"></i> Gerar
                    </button>
                </form>
            </div>
            <!-- Margem do Produtos -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Margem dos Produtos</h6>
                    <small class="text-muted">Busca a margem dos produtos ou filtra por um produto especifico.</small>
                    <div class="col-md-12">
                        <label for="produto_custo" class="form-label">Buscar Produto</label>
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="hidden" id="id_produto_margem_hidden" name="id_produto" value="" />
                                <input type="text" class="form-control" id="produto_margem" placeholder="Digite o nome do produto" autocomplete="off" />
                            </div>
                            <div id="resultado_busca_produto_margem" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 mt-2">
                        <label for="limite_margem" class="form-label porcentagem ">Margem (%):</label>
                        <input type="text"
                            id="limite_margem"
                            name="limite_margem"
                            class="form-control"
                            step="0.01"
                            placeholder="Ex: 10.00">
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="buscar_margem_produto">
                        <i class="bi bi-graph-down me-1"></i> Gerar
                    </button>
                </form>
            </div>
            <?php
            // Tabela de faturamento mensal
            if (isset($meses_faturamento)) {
                $this->tabelaFaturamentoMensal($meses_faturamento, $mes_filtro);
            };
            // Tabela de pedidos por mês
            if (isset($dadosPedidos)) {
                $this->tabelaPedidosPorMes($dadosPedidos, $mes_referencia);
            };
            // Tabela de produtos mais vendidos
            if (isset($produtosMaisVendidos)) {
                $this->tabelaProdutosMaisVendidos($produtosMaisVendidos);
            };
            // tabela de pedidos por forma de pagamento
            if (isset($qtd_pedidos_forma_pagamento)) {
                $this->tabelaFormasPagamentoMaisUsadas($qtd_pedidos_forma_pagamento);
            };
            //Tabela de resumos de pedidos por cliente
            if (isset($resumoPedidosCliente)) {
                $this->tabelaResumoPedidosPorCliente($resumoPedidosCliente);
            };
            // Tabela de pedidos por status
            if (isset($pedidosPorStatus)) {
                $this->tabelaPedidosPorStatus($pedidosPorStatus);
            };
            // Tabela de pedidos com maior valor
            if (isset($pedidosMaiorValor)) {
                $this->tabelaPedidosMaiorValor($pedidosMaiorValor);
            };
            // Tabela de produtos nunca vendidos
            if (isset($produtosNaoVendidos)) {
                $this->tabelaProdutosNaoVendidos($produtosNaoVendidos);
            };
            // tabela de Produtos com estoque baixo
            if (isset($estoqueBaixoProduto)) {
                $this->tabelaProdutosBaixoEstoque($estoqueBaixoProduto);
            };
            //tabela de total de custos por produto
            if (isset($custoTotal_Produto)) {
                $this->tabelaCustoTotalPorProduto($custoTotal_Produto);
            };
            //Tabela de produtos por fornecedor
            if (isset($produtos)) {
                $this->tabelaProdutosPorFornecedor($produtos);
            };
            //Tabela de produto com margem baixa
            if (isset($produtosMargem)) {
                $this->tabelaProdutosMargem($produtosMargem);
            };
            //Tabela de produtos mais caros por margem
            if (isset($produtosMaisCaros)) {
                $this->tabelaProdutosMaisCarosPorMargem($produtosMaisCaros);
            };
            //Tabela de clientes que mais compraram com base no mes e/ ou ano
            if (isset($clientes_mais_compram)) {
                $this->tabelaClientesMaisCompraram($clientes_mais_compram);
            };
            if (isset($pedidosRecentes)) {
                $this->tabelaPedidosRecentes($pedidosRecentes);
            };
            if (isset($variacao_vendas)) {
                $this->tabelaVariacaoVendasProduto($variacao_vendas);
            };
            if (isset($dadosLucroMensal)) {
                $this->tabelaLucroBrutoMensal($dadosLucroMensal);
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

    <script src="assets/js/relatorio.js"></script>
    <!-- ajax de produtos com baixo estoque-->
    <script src="assets/js/notificacao.js"></script>
</body>

</html>