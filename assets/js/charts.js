// Carrega a API do Google Charts
google.charts.load('current', { packages: ['corechart', 'bar', 'table'] });
google.charts.setOnLoadCallback(initDashboard);

// Inicializa o dashboard
function initDashboard() {
    atualizarDashboard();
    // Atualiza a cada 60 segundos
    setInterval(atualizarDashboard, 60000);
}

// Função principal que busca dados e desenha gráficos
function atualizarDashboard() {
    fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=dashboardDados"
    })
        .then(response => response.json())
        .then(data => {
            // === Antigos ===
            if (data.faturamentoMensal) {
                desenharFaturamentoMensal(data.faturamentoMensal);
            }
            if (data.produtosMaisVendidos) {
                desenharProdutosMaisVendidos(data.produtosMaisVendidos);
            }
            if (data.formasPagamentoMaisUsadas) {
                desenharFormasPagamento(data.formasPagamentoMaisUsadas);
            }
            if (data.pedidosRecentes) {
                desenharPedidosRecentes(data.pedidosRecentes);
            }
            if (data.clientesQueMaisCompram) {
                desenharClientesQueMaisCompraram(data.clientesQueMaisCompram);
            }
            if (data.pedidosPorMes) {
                desenharPedidosPorMes(data.pedidosPorMes);
            }
        })
        .catch(error => console.error('Erro ao carregar dados do dashboard:', error));
}

// ===== Funções para desenhar os gráficos =====

// Faturamento Mensal - Coluna
function desenharFaturamentoMensal(dados) {
    let dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'Mês');
    dataTable.addColumn('number', 'Faturamento (R$)');

    dados.forEach(item => {
        dataTable.addRow([obterNomeMes(item.mes), parseFloat(item.faturamento)]);
    });

    let options = {
        title: 'Faturamento Mensal',
        height: 380,
        legend: { position: 'none' },
        vAxis: { title: 'Faturamento (R$)', format: 'currency', textStyle: { bold: true } },
        hAxis: { title: 'Mês' },
        colors: ['#1b9e77'],
        chartArea: { width: '80%', height: '70%' }
    };

    let chart = new google.visualization.ColumnChart(document.getElementById('graficoFaturamentoMensal'));
    chart.draw(dataTable, options);
}

// Produtos Mais Vendidos - Barra
function desenharProdutosMaisVendidos(dados) {
    let dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'Produto');
    dataTable.addColumn('number', 'Quantidade (m)');

    dados.forEach(item => {
        dataTable.addRow([item.nome_produto, parseFloat(item.total_vendido)]);
    });

    let options = {
        title: 'Top Produtos Mais Vendidos',
        height: 380,
        legend: { position: 'none' },
        vAxis: { title: 'Produto' },
        hAxis: { title: 'Quantidade (m)' },
        colors: ['#d95f02'],
        chartArea: { width: '75%', height: '70%' }
    };

    let chart = new google.visualization.BarChart(document.getElementById('graficoProdutosMaisVendidos'));
    chart.draw(dataTable, options);
}

// Formas de Pagamento Mais Usadas - Pizza
function desenharFormasPagamento(dados) {
    let dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'Forma de Pagamento');
    dataTable.addColumn('number', 'Qtd de Pedidos');

    dados.forEach(item => {
        dataTable.addRow([item.descricao, parseInt(item.quantidade)]);
    });

    let options = {
        title: 'Formas de Pagamento Mais Usadas',
        height: 380,
        pieHole: 0.45,
        chartArea: { width: '90%', height: '75%' },
        slices: {
            0: { color: '#f4b400' },
            1: { color: '#0f9d58' },
            2: { color: '#db4437' },
            3: { color: '#4285f4' }
        }
    };

    let chart = new google.visualization.PieChart(document.getElementById('graficoFormasPagamento'));
    chart.draw(dataTable, options);
}

// Pedidos Recentes - Linha Temporal
// Carregar Google Charts já com locale em português
google.charts.load('current', {
    packages: ['corechart'],
    language: 'pt-BR'
});

// Pedidos Recentes - Visual Melhorado
// Função auxiliar para nome dos meses
function obterNomeMes(numero) {
    const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    return meses[numero - 1] || numero;
}

// Pedidos Recentes - Visual Melhorado com meses PT-BR
function desenharPedidosRecentes(dados) {
    let dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'Data');
    dataTable.addColumn('number', 'Valor Total (R$)');
    dataTable.addColumn({ type: 'string', role: 'tooltip' });

    // 🔹 Converte cada item para objeto com Date real + valor + data formatada
    let dadosConvertidos = dados.map(item => {
        let partes = item.data_pedido.split('-');
        let ano = parseInt(partes[0]);
        let mes = parseInt(partes[1]);
        let dia = parseInt(partes[2]);

        let dataReal = new Date(ano, mes - 1, dia);
        let dataFormatada = `${dia} ${obterNomeMes(mes)}`;
        let valor = parseFloat(item.valor_total);

        let tooltip = `📅 ${dia} ${obterNomeMes(mes)} ${ano}\n💰 R$ ${valor.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;

        return { dataReal, dataFormatada, valor, tooltip };
    });

    // 🔹 Ordena os dados pela data real (do menor para o maior)
    dadosConvertidos.sort((a, b) => a.dataReal - b.dataReal);

    // 🔹 Adiciona no DataTable já ordenados
    dadosConvertidos.forEach(item => {
        dataTable.addRow([item.dataFormatada, item.valor, item.tooltip]);
    });

    let options = {
        title: 'Pedidos Recentes (últimos 7 dias)',
        height: 400,
        legend: { position: 'bottom' },
        pointSize: 6,
        colors: ['#1E88E5'],
        tooltip: { isHtml: true },
        hAxis: {
            title: 'Data',
            slantedText: true,
            slantedTextAngle: 45,
            textStyle: { fontSize: 12 }
        },
        vAxis: {
            title: 'Valor Total (R$)',
            format: 'currency',
            gridlines: { color: '#f0f0f0' }
        },
        chartArea: { width: '80%', height: '65%' },
        areaOpacity: 0.25
    };

    let chart = new google.visualization.AreaChart(
        document.getElementById('graficoPedidosRecentes')
    );
    chart.draw(dataTable, options);
}


// Clientes que mais compraram - Barra
function desenharClientesQueMaisCompraram(dados) {
    let dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'Cliente');
    dataTable.addColumn('number', 'Total Comprado (R$)');

    dados.forEach(item => {
        dataTable.addRow([item.nome_fantasia, parseFloat(item.total_comprado)]);
    });

    let options = {
        title: 'Clientes que Mais Compraram',
        height: 380,
        legend: { position: 'none' },
        hAxis: { title: 'Valor (R$)', format: 'currency' },
        vAxis: { title: 'Cliente' },
        colors: ['#7570b3'],
        chartArea: { width: '75%', height: '70%' }
    };

    let chart = new google.visualization.BarChart(document.getElementById('graficoClientesQueMaisCompraram'));
    chart.draw(dataTable, options);
}

// Pedidos por Mês - Coluna empilhada
function desenharPedidosPorMes(dados) {
    let dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'Mês');
    dataTable.addColumn('number', 'Total');
    dataTable.addColumn('number', 'Finalizados');
    dataTable.addColumn('number', 'Cancelados');
    dataTable.addColumn('number', 'Abertos');

    dados.forEach(item => {
        dataTable.addRow([
            obterNomeMes(item.mes),
            parseInt(item.total_pedidos),
            parseInt(item.pedidos_finalizados),
            parseInt(item.pedidos_cancelados),
            parseInt(item.pedidos_abertos)
        ]);
    });

    let options = {
        title: 'Pedidos por Mês',
        height: 380,
        isStacked: true,
        hAxis: { title: 'Mês' },
        vAxis: { title: 'Quantidade de Pedidos', format: '#,###' },
        colors: ['#1b9e77', '#66a61e', '#e7298a', '#7570b3'],
        legend: { position: 'bottom' },
        chartArea: { width: '80%', height: '65%' }
    };

    let chart = new google.visualization.ColumnChart(document.getElementById('graficoPedidosPorMes'));
    chart.draw(dataTable, options);
}

// Função auxiliar para nome dos meses
function obterNomeMes(numero) {
    const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    return meses[numero - 1] || numero;
}
