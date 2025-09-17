$(document).ready(function () {
  // ===========================
  // VARIÁVEIS GLOBAIS
  // ===========================
  let valorTotal = 0;
  let valorFrete = 0;
  let produtoSelecionado = null;

  // ===========================
  // FUNÇÕES UTILITÁRIAS
  // ===========================
  function mostrarAlerta(mensagem, tipo = "danger", duracao = 3000) {
    const alerta = document.createElement("div");
    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow`;
    alerta.innerHTML = `
      ${mensagem}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    `;
    Object.assign(alerta.style, {
      position: "fixed",
      top: "20px",
      right: "20px",
      zIndex: 1055,
    });
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), duracao);
  }

  function formatarMoeda(valor) {
    return "R$ " + valor.toFixed(2).replace(".", ",");
  }

  function atualizarValorTotalComFrete() {
    const total = valorTotal + valorFrete;
    document.getElementById("valor_total").value = formatarMoeda(total);
    verificarLimiteCredito(); // sempre que atualizar total → verifica limite
  }

  function ativarSpinner(botao) {
    botao.disabled = true;
    botao.dataset.originalText = botao.innerHTML;
    botao.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Buscando...`;
  }

  function desativarSpinner(botao) {
    botao.disabled = false;
    botao.innerHTML = botao.dataset.originalText;
  }

  function limparCamposPedido() {
    document.getElementById("tbody_lista_pedido").innerHTML = "";
    document.getElementById("frete").value = "";
    document.getElementById("valor_total").value = "";
    document.getElementById("status").value = "";
    document.querySelector("select[name='id_forma_pagamento']").value = "";
    document.getElementById("cliente_pedido").value = "";
    document.getElementById("id_cliente_hidden")?.remove();
    document.getElementById("resultado_busca_cliente").innerHTML = "";
    document.getElementById("produto_pedido").value = "";
    document.getElementById("id_produto_hidden")?.remove();
    document.getElementById("quantidade").value = "";
    document.getElementById("resultado_busca_produto").innerHTML = "";
    document.getElementById("data").value = new Date().toISOString().split("T")[0];
    valorTotal = 0;
    valorFrete = 0;
    document.getElementById("salvar_pedido").disabled = true;
  }

  // ===========================
  // VERIFICAÇÃO DE LIMITE EM TEMPO REAL
  // ===========================
  function verificarLimiteCredito() {
    const idCliente = document.getElementById("id_cliente_hidden")?.value;
    const total = Number((valorTotal).toFixed(2));

    // sem cliente ou pedido zerado → botão desabilitado
    if (!idCliente || total <= 0) {
      document.getElementById("salvar_pedido").disabled = true;
      return;
    }

    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `verificar_limite=1&id_cliente=${idCliente}&valor_total=${total}`,
    })
      .then((res) => res.text())
      .then((resdata) => {
        if (!resdata.trim()) {
          // dentro do limite
          document.getElementById("salvar_pedido").disabled = false;
          return;
        }
        const json = JSON.parse(resdata);
        if (json.status === false) {
          document.getElementById("salvar_pedido").disabled = true;
          mostrarAlerta(
            `⚠️ Limite de crédito excedido!<br>
            <strong>Limite:</strong> R$ ${parseFloat(json.limite_credito).toFixed(2).replace(".", ",")}<br>
            <strong>Pedido:</strong> R$ ${total.toFixed(2).replace(".", ",")}<br>
            <strong>Excedente:</strong> <span style="color:#dc3545; font-weight:bold;">
              R$ ${(total - parseFloat(json.limite_credito)).toFixed(2).replace(".", ",")}
            </span>`,
            "danger",
            6000
          );
        }
      })
      .catch(() => {
        document.getElementById("salvar_pedido").disabled = true;
        mostrarAlerta("Erro ao verificar limite de crédito!", "danger");
      });
  }


  // ===========================================
  // FUNÇÕES DE BUSCA AJAX
  // ===========================================

  /**
   * Realiza a busca de clientes via AJAX.
   * @param {string} termo - O termo de busca para o cliente.
   */
  function buscarCliente(termo) {
    const input = document.getElementById("cliente_pedido");
    const resultado = document.getElementById("resultado_busca_cliente");

    // Se o termo de busca estiver vazio, limpa os resultados e retorna
    if (!termo) {
      resultado.innerHTML = "";
      return;
    }

    ativarSpinner(input); // Ativa o spinner para indicar carregamento

    // Faz a requisição AJAX para o backend
    fetch("index.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `cliente_pedido=${encodeURIComponent(termo)}`, // Envia o termo no corpo da requisição
    })
      .then((res) => res.text()) // Converte a resposta para texto
      .then((data) => {
        resultado.innerHTML = data; // Insere os resultados da busca na div
        desativarSpinner(input); // Desativa o spinner
        // Com delegação de eventos, não precisamos chamar ativarSelecaoCliente() aqui.
        // O listener no container pai já está pronto para novos elementos.
      })
      .catch(() => {
        mostrarAlerta("Erro ao buscar cliente."); // Exibe alerta em caso de erro
        desativarSpinner(input); // Desativa o spinner mesmo com erro
      });
  }

  /**
   * Realiza a busca de produtos via AJAX.
   * @param {string} termo - O termo de busca para o produto.
   */
  function buscarProduto(termo) {
    const input = document.getElementById("produto_pedido");
    const resultado = document.getElementById("resultado_busca_produto");

    // Se o termo de busca estiver vazio, limpa os resultados e retorna
    if (!termo) {
      resultado.innerHTML = "";
      return;
    }

    ativarSpinner(input); // Ativa o spinner para indicar carregamento

    // Faz a requisição AJAX para o backend
    fetch("index.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `produto_pedido=${encodeURIComponent(termo)}`, // Envia o termo no corpo da requisição
    })
      .then((res) => res.text()) // Converte a resposta para texto
      .then((data) => {
        resultado.innerHTML = data; // Insere os resultados da busca na div
        desativarSpinner(input); // Desativa o spinner
        // Com delegação de eventos, não precisamos chamar ativarSelecaoProduto() aqui.
      })
      .catch(() => {
        mostrarAlerta("Erro ao buscar produto."); // Exibe alerta em caso de erro
        desativarSpinner(input); // Desativa o spinner mesmo com erro
      });
  }

  // ===========================================
  // CONFIGURAÇÃO DOS EVENTOS DE INPUT (COM DEBOUNCE)
  // ===========================================

  // Variáveis para armazenar os temporizadores do debounce
  let timeoutCliente = null;
  let timeoutProduto = null;
  const DEBOUNCE_DELAY = 500; // Atraso de 300 milissegundos

  // Listener para o campo de busca de cliente
  document.getElementById("cliente_pedido").addEventListener("input", (e) => {
    // Limpa qualquer temporizador anterior para que a busca só ocorra após
    // um breve período de inatividade na digitação.
    clearTimeout(timeoutCliente);
    const termo = e.target.value.trim(); // Obtém o valor atual do input e remove espaços em branco
    // Configura um novo temporizador
    timeoutCliente = setTimeout(() => {
      buscarCliente(termo); // Chama a função de busca após o atraso
    }, DEBOUNCE_DELAY);
  });

  // Listener para o campo de busca de produto
  document.getElementById("produto_pedido").addEventListener("input", (e) => {
    // Limpa qualquer temporizador anterior
    clearTimeout(timeoutProduto);
    const termo = e.target.value.trim(); // Obtém o valor atual do input
    // Configura um novo temporizador
    timeoutProduto = setTimeout(() => {
      buscarProduto(termo); // Chama a função de busca após o atraso
    }, DEBOUNCE_DELAY);
  });

  // ===========================================
  // CONFIGURAÇÃO DOS EVENTOS DE SELEÇÃO (COM DELEGAÇÃO DE EVENTOS)
  // ===========================================

  // Listener para o container de resultados de cliente (delegação de eventos)
  document.getElementById("resultado_busca_cliente").addEventListener("click", function (e) {
    // Usa 'closest' para verificar se o elemento clicado
    // corresponde ao seletor '.cliente-item'. Isso permite cliques em filhos do span.
    const clienteItem = e.target.closest(".cliente-item");

    // Se um item de cliente foi clicado
    if (clienteItem) {
      const nome = clienteItem.textContent; // Obtém o texto do item (nome do cliente)
      const id = clienteItem.dataset.id; // Obtém o ID do cliente do atributo data-id

      const inputCliente = document.getElementById("cliente_pedido");
      inputCliente.value = nome; // Preenche o campo de input com o nome selecionado

      // Lógica para criar/atualizar um input hidden para armazenar o ID do cliente
      let hiddenIdCliente = document.getElementById("id_cliente_hidden");
      if (!hiddenIdCliente) {
        hiddenIdCliente = document.createElement("input");
        hiddenIdCliente.type = "hidden";
        hiddenIdCliente.id = "id_cliente_hidden";
        hiddenIdCliente.name = "id_cliente";
        inputCliente.parentElement.appendChild(hiddenIdCliente);
      }
      hiddenIdCliente.value = id; // Define o valor do input hidden

      // Limpa a lista de resultados após a seleção
      this.innerHTML = "";

      // Chama uma função para verificar limites de crédito, etc.
      verificarLimiteCredito();
    }
  });

  // Listener para o container de resultados de produto (delegação de eventos)
  document.getElementById("resultado_busca_produto").addEventListener("click", function (e) {
    // Verifica se o elemento clicado (ou um de seus pais) é um '.produto-item'
    const produtoItem = e.target.closest(".produto-item");
    // Se um item de produto foi clicado
    if (produtoItem) {
      // Obtém os dados do produto dos atributos data-*
      const id = produtoItem.dataset.id;
      const nome = produtoItem.dataset.nome;
      const cor = produtoItem.dataset.cor;
      const largura = produtoItem.dataset.largura;
      const valor = produtoItem.dataset.valorvenda;
      const qtd = produtoItem.dataset.quantidade;

      const inputProduto = document.getElementById("produto_pedido");
      // Preenche o campo de input com uma descrição formatada do produto
      inputProduto.value = `${nome} - Cor: ${cor} - Largura: ${largura}cm`;

      // Lógica para criar/atualizar um input hidden para armazenar o ID do produto
      let hiddenIdProduto = document.getElementById("id_produto_hidden");
      if (!hiddenIdProduto) {
        hiddenIdProduto = document.createElement("input");
        hiddenIdProduto.type = "hidden";
        hiddenIdProduto.id = "id_produto_hidden";
        hiddenIdProduto.name = "id_produto";
        inputProduto.parentElement.appendChild(hiddenIdProduto);
      }
      hiddenIdProduto.value = id; // Define o valor do input hidden
      // Armazena os detalhes completos do produto em uma variável global
      produtoSelecionado = {
        id,
        nome,
        cor,
        largura,
        valorVenda: parseFloat(valor),
        quantidade: parseInt(qtd),
      };
      // Limpa a lista de resultados após a seleção
      this.innerHTML = "";
    }
  });
  document.getElementById("quantidade").addEventListener("input", function () {
    const qtd = this.value;
    const id = document.getElementById("id_produto_hidden")?.value;
    if (!id || !qtd) return;

    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `verificar_quantidade=1&id_produto=${id}&quantidade=${qtd}`,
    })
      .then((res) => res.text())
      .then((data) => {
        if (data.includes("erro_quantidade")) {
          mostrarAlerta("Quantidade insuficiente em estoque!");
          this.value = "";
        }
      });
  });

  document.getElementById("adicionar_produto").addEventListener("click", function () {
    const idProduto = document.getElementById("id_produto_hidden")?.value;
    const nome = document.getElementById("produto_pedido").value;
    const qtd = parseInt(document.getElementById("quantidade").value);
    const valorUnitario = produtoSelecionado?.valorVenda || 0;

    if (!idProduto || !qtd || qtd <= 0) {
      return mostrarAlerta("Dados do produto incompletos!", "warning");
    }

    const tbody = document.getElementById("tbody_lista_pedido");
    for (let tr of tbody.getElementsByTagName("tr")) {
      if (tr.dataset.idProduto === idProduto) {
        return mostrarAlerta("Produto já adicionado!", "warning");
      }
    }

    let valorLinha = valorUnitario * qtd;
    const tr = tbody.insertRow();
    tr.dataset.idProduto = idProduto;

    tr.insertCell(0).textContent = nome;

    const cellQtd = tr.insertCell(1);
    const inputQtd = document.createElement("input");
    inputQtd.type = "number";
    inputQtd.value = qtd;
    inputQtd.min = 1;
    inputQtd.className = "form-control form-control-sm text-center";
    cellQtd.appendChild(inputQtd);

    tr.insertCell(2).textContent = formatarMoeda(valorUnitario);

    const cellTotal = tr.insertCell(3);
    cellTotal.textContent = formatarMoeda(valorLinha);

    const btnRemover = document.createElement("button");
    btnRemover.className = "btn btn-outline-danger btn-sm";
    btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
    btnRemover.addEventListener("click", () => {
      valorTotal -= valorLinha;
      atualizarValorTotalComFrete();
      tr.remove();
    });
    tr.insertCell(4).appendChild(btnRemover);

    const hiddenUnit = document.createElement("input");
    hiddenUnit.type = "hidden";
    hiddenUnit.name = "valor_unitario";
    hiddenUnit.value = valorUnitario.toFixed(2);
    tr.appendChild(hiddenUnit);

    const hiddenTotal = document.createElement("input");
    hiddenTotal.type = "hidden";
    hiddenTotal.name = "valor_total";
    hiddenTotal.value = valorLinha.toFixed(2);
    tr.appendChild(hiddenTotal);

    inputQtd.addEventListener("focus", function () {
      this.dataset.valorAnterior = this.value;
    });

    inputQtd.addEventListener("input", function () {
      const valorAnterior = parseFloat(this.dataset.valorAnterior) || 1;
      const novaQtd = parseFloat(this.value);

      if (!novaQtd || novaQtd <= 0) {
        mostrarAlerta("Quantidade inválida!", "warning");
        this.value = valorAnterior;
        return;
      }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `verificar_quantidade=1&id_produto=${idProduto}&quantidade=${novaQtd}`,
      })
        .then((res) => res.text())
        .then((data) => {
          if (data.includes("erro_quantidade")) {
            mostrarAlerta("Estoque insuficiente!", "warning");
            this.value = valorAnterior;
            return;
          }
          valorTotal -= valorLinha;
          valorLinha = valorUnitario * novaQtd;
          cellTotal.textContent = formatarMoeda(valorLinha);
          valorTotal += valorLinha;
          hiddenTotal.value = valorLinha.toFixed(2);
          atualizarValorTotalComFrete();
          this.dataset.valorAnterior = novaQtd;
        });
    });

    valorTotal += valorLinha;
    atualizarValorTotalComFrete();

    document.getElementById("produto_pedido").value = "";
    document.getElementById("quantidade").value = "";
    document.getElementById("id_produto_hidden")?.remove();
    document.getElementById("resultado_busca_produto").innerHTML = "";
    produtoSelecionado = null;
  });

  // Função utilitária já existente
function formatarMoeda(valor) {
  return "R$ " + valor.toFixed(2).replace(".", ",");
}

// Aplica no campo de frete também
document.getElementById("frete").addEventListener("input", (e) => {
  // remove tudo que não seja número
  let somenteNumeros = e.target.value.replace(/\D/g, "");
  let valor = parseFloat(somenteNumeros) / 100;
  valorFrete = isNaN(valor) ? 0 : valor;
  e.target.value = formatarMoeda(valorFrete);
  // atualiza o total com frete
  atualizarValorTotalComFrete();
});


  document.getElementById("limpar_pedido").addEventListener("click", limparCamposPedido);

  // ===========================
  // SALVAR PEDIDO
  // ===========================
  document.getElementById("salvar_pedido").addEventListener("click", function (e) {
    e.preventDefault();
    if (this.disabled) return; // segurança extra
    const idCliente = document.getElementById("id_cliente_hidden")?.value;
    const status = "Pendente";
    const idPagamento = document.querySelector("select[name='id_forma_pagamento']").value;
    if (!idCliente || !status || !idPagamento) {
      return mostrarAlerta("Preencha todos os campos obrigatórios!", "warning");
    }

    const origem = document.getElementById("origem").value;
    const hoje = new Date();
    const ano = hoje.getFullYear();
    const mes = String(hoje.getMonth() + 1).padStart(2, '0');
    const dia = String(hoje.getDate()).padStart(2, '0');
    const data = `${ano}-${mes}-${dia}`;
    const frete = valorFrete.toFixed(2);
    const total = Number((valorTotal + valorFrete).toFixed(2));

    const itens = [];
    document.querySelectorAll("#tbody_lista_pedido tr").forEach((tr) => {
      itens.push({
        id_produto: tr.dataset.idProduto,
        quantidade: tr.querySelector("input[type='number']").value,
        valor_unitario: tr.querySelector("input[name='valor_unitario']").value,
        totalValor_produto: tr.querySelector("input[name='valor_total']").value,
      });
    });
    if (itens.length === 0) {
      return mostrarAlerta("Adicione pelo menos um produto!", "warning");
    }
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "index.php";
    form.innerHTML = `
      <input type="hidden" name="salvar_pedido" value="1">
      <input type="hidden" name="id_cliente" value="${idCliente}">
      <input type="hidden" name="data_pedido" value="${data}">
      <input type="hidden" name="status_pedido" value="${status}">
      <input type="hidden" name="valor_total" value="${total}">
      <input type="hidden" name="id_forma_pagamento" value="${idPagamento}">
      <input type="hidden" name="valor_frete" value="${frete}">
      <input type="hidden" name="origem" value="${origem}">
    `;
    itens.forEach((item, i) => {
      form.innerHTML += `
          <input type="hidden" name="itens[${i}][id_produto]" value="${item.id_produto}">
          <input type="hidden" name="itens[${i}][quantidade]" value="${item.quantidade}">
          <input type="hidden" name="itens[${i}][valor_unitario]" value="${item.valor_unitario}">
          <input type="hidden" name="itens[${i}][totalValor_produto]" value="${item.totalValor_produto}">
        `;
    });
    document.body.appendChild(form);
    form.submit();
    form.remove();
    limparCamposPedido();
  });
});
