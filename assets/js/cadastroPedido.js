document.addEventListener("DOMContentLoaded", function () {
  // ===========================
  // VARIÁVEIS GLOBAIS
  // ===========================
  let valorTotal = 0;
  let valorFrete = 0;
  let produtoSelecionado = null;

  // ===========================
  // SELETORES DO DOM
  // ===========================
  const inputCliente = document.getElementById("cliente_pedido");
  const inputProduto = document.getElementById("produto_pedido");
  const inputQuantidade = document.getElementById("quantidade");
  const resultadoCliente = document.getElementById("resultado_busca_cliente");
  const resultadoProduto = document.getElementById("resultado_busca_produto");
  const tbody = document.getElementById("tbody_lista_pedido");
  const btnSalvar = document.getElementById("salvar_pedido");
  const btnAdicionar = document.getElementById("adicionar_produto");
  const freteEl = document.getElementById("frete");
  const selectPagamento = document.querySelector("select[name='id_forma_pagamento']");

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
    // Garante que o valor seja um número antes de formatar
    return "R$ " + Number(valor).toFixed(2).replace(".", ",");
  }

  function ativarSpinner(botaoOuInput) {
    try {
      botaoOuInput.disabled = true;
      botaoOuInput.dataset.originalText = botaoOuInput.innerHTML ?? botaoOuInput.value ?? "";
      if (botaoOuInput.tagName === "BUTTON") {
        botaoOuInput.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Buscando...`;
      } else {
        botaoOuInput.value = "";
      }
    } catch (e) { /* silent */ }
  }

  function desativarSpinner(botaoOuInput) {
    try {
      botaoOuInput.disabled = false;
      if (botaoOuInput.tagName === "BUTTON") {
        botaoOuInput.innerHTML = botaoOuInput.dataset.originalText || botaoOuInput.innerHTML;
      } else {
        botaoOuInput.value = botaoOuInput.dataset.originalText || botaoOuInput.value;
      }
    } catch (e) { /* silent */ }
  }

  // ===========================
  // ATUALIZAÇÔES DE TOTAIS E BOTÕES
  // ===========================

  function atualizarValorTotalComFrete() {
    const total = valorTotal + valorFrete;
    const totalEl = document.getElementById("valor_total");
    if (totalEl) totalEl.value = formatarMoeda(total);
    verificarLimiteCredito(); // sempre que atualizar total → verifica limite
    verificarBotaoSalvar(); // verifica se campos obrigatórios estão preenchidos
  }

  /** Novo: Recalcula valorTotal lendo os hidden inputs da tabela. */
  function recalcularTotaisAPartirDaTabela() {
    valorTotal = 0;
    if (!tbody) return;

    Array.from(tbody.querySelectorAll("tr")).forEach(tr => {
      // Busca o hidden input 'valor_total'
      const hiddenTotal = tr.querySelector("input[name$='[valor_total]']") || tr.querySelector("input[name='valor_total']");
      let subtotal = parseFloat(hiddenTotal?.value) || 0;
      valorTotal += subtotal;
    });
    atualizarValorTotalComFrete();
  }

  /** Novo: Verifica se os campos obrigatórios estão preenchidos para habilitar o Salvar. */
  function verificarBotaoSalvar() {
    if (!btnSalvar) return;
    const idCliente = document.getElementById("id_cliente_hidden")?.value;
    const idPagamento = selectPagamento?.value;
    const possuiProdutos = (tbody?.querySelectorAll("tr").length || 0) > 0;

    // Habilita se TIVER cliente, TIVER pagamento selecionado e TIVER produtos na lista
    btnSalvar.disabled = !(idCliente && idPagamento && possuiProdutos);
  }


  function limparCamposPedido() {
    // ... (lógica de limpeza: mantida)
    if (tbody) tbody.innerHTML = "";
    if (freteEl) freteEl.value = "";
    const valorTotalEl = document.getElementById("valor_total");
    if (valorTotalEl) valorTotalEl.value = "";
    const statusEl = document.getElementById("status");
    if (statusEl) statusEl.value = "";
    if (selectPagamento) selectPagamento.value = "";
    if (inputCliente) inputCliente.value = "";
    document.getElementById("id_cliente_hidden")?.remove();
    if (resultadoCliente) resultadoCliente.innerHTML = "";
    if (inputProduto) inputProduto.value = "";
    document.getElementById("id_produto_hidden")?.remove();
    if (inputQuantidade) inputQuantidade.value = "";
    if (resultadoProduto) resultadoProduto.innerHTML = "";
    // Adicionando a data de volta (assumindo que o campo existe)
    const dataEl = document.getElementById("data");
    if (dataEl) dataEl.value = new Date().toISOString().split("T")[0];

    valorTotal = 0;
    valorFrete = 0;
    if (btnSalvar) btnSalvar.disabled = true;
  }

  // ===========================
  // VERIFICAÇÃO DE LIMITE EM TEMPO REAL
  // ===========================
  function verificarLimiteCredito() {
    const idCliente = document.getElementById("id_cliente_hidden")?.value;
    const total = Number((valorTotal).toFixed(2));

    if (!idCliente || total <= 0) {
      if (btnSalvar) btnSalvar.disabled = true;
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
          verificarBotaoSalvar(); // Se OK, reativa a verificação normal
          return;
        }
        try {
          const json = JSON.parse(resdata);
          if (json.status === false) {
            if (btnSalvar) btnSalvar.disabled = true;
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
          } else {
            verificarBotaoSalvar();
          }
        } catch (e) {
          // Se não for JSON, confia na verificação padrão
          verificarBotaoSalvar();
        }
      })
      .catch(() => {
        if (btnSalvar) btnSalvar.disabled = true;
        mostrarAlerta("Erro ao verificar limite de crédito!", "danger");
      });
  }


  // ===========================================
  // FUNÇÕES DE BUSCA AJAX
  // ===========================================

  function buscarCliente(termo) {
    if (!termo) {
      if (resultadoCliente) resultadoCliente.innerHTML = "";
      document.getElementById("id_cliente_hidden")?.remove();
      verificarLimiteCredito();
      return;
    }
    ativarSpinner(inputCliente);

    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `cliente_pedido=${encodeURIComponent(termo)}`,
    })
      .then((res) => res.text())
      .then((data) => {
        if (resultadoCliente) resultadoCliente.innerHTML = data;
        desativarSpinner(inputCliente);
      })
      .catch(() => {
        mostrarAlerta("Erro ao buscar cliente.");
        desativarSpinner(inputCliente);
      });
  }

  function buscarProduto(termo) {
    if (!termo) {
      if (resultadoProduto) resultadoProduto.innerHTML = "";
      document.getElementById("id_produto_hidden")?.remove();
      produtoSelecionado = null;
      return;
    }
    ativarSpinner(inputProduto);

    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `produto_pedido=${encodeURIComponent(termo)}`,
    })
      .then((res) => res.text())
      .then((data) => {
        if (resultadoProduto) resultadoProduto.innerHTML = data;
        desativarSpinner(inputProduto);
      })
      .catch(() => {
        mostrarAlerta("Erro ao buscar produto.");
        desativarSpinner(inputProduto);
      });
  }

  // ===========================================
  // CONFIGURAÇÃO DOS EVENTOS DE INPUT (COM DEBOUNCE)
  // ===========================================

  let timeoutCliente = null;
  let timeoutProduto = null;
  const DEBOUNCE_DELAY = 500;

  if (inputCliente) {
    inputCliente.addEventListener("input", (e) => {
      clearTimeout(timeoutCliente);
      const termo = e.target.value.trim();
      timeoutCliente = setTimeout(() => buscarCliente(termo), DEBOUNCE_DELAY);
    });
  }

  if (inputProduto) {
    inputProduto.addEventListener("input", (e) => {
      clearTimeout(timeoutProduto);
      const termo = e.target.value.trim();
      timeoutProduto = setTimeout(() => buscarProduto(termo), DEBOUNCE_DELAY);
    });
  }

  // ===========================================
  // CONFIGURAÇÃO DOS EVENTOS DE SELEÇÃO (DELEGAÇÃO)
  // ===========================================

  // 1. Seleção de Cliente
  if (resultadoCliente) {
    resultadoCliente.addEventListener("click", function (e) {
      const clienteItem = e.target.closest(".cliente-item");
      if (!clienteItem) return;

      const nome = clienteItem.textContent;
      const id = clienteItem.dataset.id;

      if (inputCliente) inputCliente.value = nome;

      let hiddenIdCliente = document.getElementById("id_cliente_hidden");
      if (!hiddenIdCliente) {
        hiddenIdCliente = document.createElement("input");
        hiddenIdCliente.type = "hidden";
        hiddenIdCliente.id = "id_cliente_hidden";
        hiddenIdCliente.name = "id_cliente";
        inputCliente?.parentElement.appendChild(hiddenIdCliente);
      }
      hiddenIdCliente.value = id;
      this.innerHTML = "";
      verificarLimiteCredito();
    });
  }

  // 2. Seleção de Produto
  if (resultadoProduto) {
    resultadoProduto.addEventListener("click", function (e) {
      const produtoItem = e.target.closest(".produto-item");
      if (!produtoItem) return;

      const id = produtoItem.dataset.id;
      const nome = produtoItem.dataset.nome;
      const cor = produtoItem.dataset.cor;
      const largura = produtoItem.dataset.largura;
      const valor = produtoItem.dataset.valorvenda;
      const qtdEstoque = produtoItem.dataset.quantidade;

      if (inputProduto) inputProduto.value = `${nome} - Cor: ${cor} - Largura: ${largura}m`;

      let hiddenIdProduto = document.getElementById("id_produto_hidden");
      if (!hiddenIdProduto) {
        hiddenIdProduto = document.createElement("input");
        hiddenIdProduto.type = "hidden";
        hiddenIdProduto.id = "id_produto_hidden";
        // Este name não é usado no submit final, mas é bom para referências
        hiddenIdProduto.name = "id_produto";
        inputProduto?.parentElement.appendChild(hiddenIdProduto);
      }
      hiddenIdProduto.value = id;

      produtoSelecionado = {
        id,
        nome,
        cor,
        largura,
        valorVenda: parseFloat(valor) || 0,
        quantidade: parseFloat(qtdEstoque) || 0,
      };
      this.innerHTML = "";
    });
  }

  // 3. Verificação de estoque no input de quantidade (pré-adicionar)
  if (inputQuantidade) {
    inputQuantidade.addEventListener("input", function () {
      const qtd = parseFloat(this.value);
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
  }

  // ===========================
  // ADICIONAR PRODUTO NA TABELA
  // ===========================
  if (btnAdicionar) {
    btnAdicionar.addEventListener("click", function () {
      const idProduto = document.getElementById("id_produto_hidden")?.value;
      const nome = inputProduto?.value;
      const qtd = parseFloat(inputQuantidade?.value);
      const valorUnitario = produtoSelecionado?.valorVenda || 0;

      if (!idProduto || !qtd || qtd <= 0 || valorUnitario === 0) {
        return mostrarAlerta("Selecione um produto e defina uma quantidade válida!", "warning");
      }

      if (!tbody) return;

      // Verifica se já existe
      for (let tr of tbody.getElementsByTagName("tr")) {
        if (tr.dataset.idProduto === idProduto) {
          return mostrarAlerta("Produto já adicionado! Altere a quantidade na tabela.", "warning");
        }
      }

      let valorLinha = valorUnitario * qtd;
      const tr = tbody.insertRow();
      tr.dataset.idProduto = idProduto;

      // 0: Nome
      tr.insertCell(0).textContent = nome;

      // 1: Quantidade (Input)
      const cellQtd = tr.insertCell(1);
      const inputQtd = document.createElement("input");
      inputQtd.type = "number";
      inputQtd.step = "any";
      inputQtd.value = qtd;
      inputQtd.min = 1;
      // Adiciona a classe de controle para delegação
      inputQtd.className = "form-control form-control-sm text-center quantidade-item";
      inputQtd.dataset.valorAnterior = qtd;
      cellQtd.appendChild(inputQtd);

      // 2: Valor Unitário
      tr.insertCell(2).textContent = formatarMoeda(valorUnitario);

      // 3: Subtotal
      const cellTotal = tr.insertCell(3);
      cellTotal.textContent = formatarMoeda(valorLinha);

      // 4: Ação (Botão Remover)
      const cellBtn = tr.insertCell(4);
      const btnRemover = document.createElement("button");
      btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-item";
      btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
      cellBtn.appendChild(btnRemover);

      // Hidden inputs para submissão (Usando índice numérico no name para arrays simples)
      // NOTA: No código de salvar, usaremos índices dinâmicos baseados na ordem das TRs
      tr.innerHTML += `
                <input type="hidden" name="valor_unitario" value="${valorUnitario.toFixed(2)}">
                <input type="hidden" name="valor_total" value="${valorLinha.toFixed(2)}">
            `;

      // Recalcula o total geral (mais seguro)
      recalcularTotaisAPartirDaTabela();

      // Limpa inputs de busca
      if (inputProduto) inputProduto.value = "";
      if (inputQuantidade) inputQuantidade.value = "";
      document.getElementById("id_produto_hidden")?.remove();
      if (resultadoProduto) resultadoProduto.innerHTML = "";
      produtoSelecionado = null;
    });
  }

  // ===========================================
  // DELEGAÇÃO: CONTROLE DA TABELA DE PRODUTOS
  // ===========================================
  if (tbody) {
    // Delegação para FOCUSIN (para guardar valor anterior antes de editar)
    tbody.addEventListener("focusin", function (e) {
      const input = e.target.closest(".quantidade-item");
      if (input) {
        input.dataset.valorAnterior = input.value;
      }
    });

    // Delegação para INPUT (alteração de quantidade)
    tbody.addEventListener("input", function (e) {
      const input = e.target.closest(".quantidade-item");
      if (!input) return;

      const tr = input.closest("tr");
      const idProduto = tr?.dataset?.idProduto;
      const novaQtd = parseFloat(input.value);

      const hiddenUnit = tr.querySelector("input[name='valor_unitario']");
      const valorUnitario = parseFloat(hiddenUnit?.value) || 0;

      const hiddenTotal = tr.querySelector("input[name='valor_total']");
      const cellTotal = tr.querySelector("td:nth-child(4)");
      const valorAnterior = parseFloat(input.dataset.valorAnterior) || 1;

      if (!idProduto || !novaQtd || novaQtd <= 0) {
        mostrarAlerta("Quantidade inválida!", "warning");
        input.value = valorAnterior;
        return;
      }

      // 1. Validação de estoque
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `verificar_quantidade=1&id_produto=${idProduto}&quantidade=${novaQtd}`,
      })
        .then(res => res.text())
        .then(data => {
          if (data.includes("erro_quantidade")) {
            mostrarAlerta("Estoque insuficiente!", "warning");
            input.value = valorAnterior;
            return;
          }

          // 2. Atualizar subtotal da linha e hidden
          const novoSubtotal = valorUnitario * novaQtd;
          cellTotal.textContent = formatarMoeda(novoSubtotal);
          if (hiddenTotal) hiddenTotal.value = novoSubtotal.toFixed(2);

          // 3. Recalcula o total geral a partir da tabela
          recalcularTotaisAPartirDaTabela();

          input.dataset.valorAnterior = novaQtd;
        })
        .catch(() => {
          mostrarAlerta("Erro ao verificar estoque!", "danger");
          input.value = valorAnterior;
        });
    });

    // Delegação para CLICK (remover item)
    tbody.addEventListener("click", function (e) {
      const btn = e.target.closest(".btn-remover-item");
      if (!btn) return;
      const tr = btn.closest("tr");
      if (!tr) return;

      tr.remove();
      recalcularTotaisAPartirDaTabela();
    });
  }

  // Listener para Forma de Pagamento (obrigatoriedade)
  if (selectPagamento) {
    selectPagamento.addEventListener("change", verificarBotaoSalvar);
  }

  // Listener para o campo de frete
  if (freteEl) {
    freteEl.addEventListener("input", (e) => {
      let somenteNumeros = e.target.value.replace(/\D/g, "");
      let valor = parseFloat(somenteNumeros) / 100;
      valorFrete = isNaN(valor) ? 0 : valor;
      e.target.value = formatarMoeda(valorFrete);
      atualizarValorTotalComFrete();
    });
  }


  document.getElementById("limpar_pedido")?.addEventListener("click", limparCamposPedido);

  // ===========================
  // SALVAR PEDIDO
  // ===========================
  document.getElementById("salvar_pedido")?.addEventListener("click", function (e) {
    e.preventDefault();
    if (this.disabled) return;

    const idCliente = document.getElementById("id_cliente_hidden")?.value;
    const status = "Pendente"; // Status fixo para cadastro
    const idPagamento = selectPagamento?.value;

    if (!idCliente || !idPagamento || (tbody?.querySelectorAll("tr").length || 0) === 0) {
      return mostrarAlerta("Preencha todos os campos obrigatórios e adicione produtos!", "warning");
    }

    const origem = document.getElementById("origem")?.value || "";
    const frete = valorFrete.toFixed(2);
    const total = Number((valorTotal + valorFrete).toFixed(2));

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "index.php";

    form.innerHTML = `
            <input type="hidden" name="salvar_pedido" value="1">
            <input type="hidden" name="id_cliente" value="${idCliente}">
            <input type="hidden" name="status_pedido" value="${status}">
            <input type="hidden" name="valor_total" value="${total}">
            <input type="hidden" name="id_forma_pagamento" value="${idPagamento}">
            <input type="hidden" name="origem" value="${origem}">
            <input type="hidden" name="valor_frete" value="${frete}">`;

    // Coletando itens da tabela
    Array.from(tbody.querySelectorAll("tr")).forEach((tr, i) => {
      const idProduto = tr.dataset.idProduto;
      // Busca o valor atualizado do input de quantidade e dos hidden inputs
      const quantidade = tr.querySelector("input.quantidade-item")?.value || 0;
      const valorUnitario = tr.querySelector("input[name='valor_unitario']")?.value || 0;
      const totalValorProduto = tr.querySelector("input[name='valor_total']")?.value || 0;

      form.innerHTML += `
                <input type="hidden" name="itens[${i}][id_produto]" value="${idProduto}">
                <input type="hidden" name="itens[${i}][quantidade]" value="${quantidade}">
                <input type="hidden" name="itens[${i}][valor_unitario]" value="${valorUnitario}">
                <input type="hidden" name="itens[${i}][totalValor_produto]" value="${totalValorProduto}">`;
    });

    document.body.appendChild(form);
    form.submit();
    limparCamposPedido();
    form.remove();
  });

  // Chamada inicial para garantir o estado do botão 'Salvar'
  verificarBotaoSalvar();
});
