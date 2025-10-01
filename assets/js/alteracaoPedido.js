$(document).ready(function () {
  // ===========================
  // VARIÁVEIS GLOBAIS
  // ===========================
  let valorTotalAlterar = 0;
  let valorFreteAlterar = 0;
  let produtoSelecionadoAlterar = null;

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

  function ativarSpinner(botao) {
    botao.disabled = true;
    botao.dataset.originalText = botao.innerHTML;
    botao.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Buscando...`;
  }

  function desativarSpinner(botao) {
    botao.disabled = false;
    botao.innerHTML = botao.dataset.originalText;
  }

  // ===========================
  // ATUALIZA BOTOES DE REMOVER
  // ===========================
  function atualizarBotoesRemover(numeroPedido) {
    const linhas = $(`#tbody_lista_pedido_${numeroPedido} tr`).length;
    if (linhas <= 1) {
      $(`#tbody_lista_pedido_${numeroPedido} .btn-remover-item`).prop("disabled", true);
    } else {
      $(`#tbody_lista_pedido_${numeroPedido} .btn-remover-item`).prop("disabled", false);
    }
    verificarBotaoSalvar(numeroPedido);
  }

  function prepararBotoesRemover(numeroPedido) {
    $(`#tbody_lista_pedido_${numeroPedido} tr`).each(function () {
      const tr = $(this);

      if (tr.find(".btn-remover-item").length === 0) {
        const cellBtn = tr.find("td").last();
        const btnRemover = document.createElement("button");
        btnRemover.type = "button";
        btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-item";
        btnRemover.innerHTML = '<i class="bi bi-trash"></i>';

        btnRemover.addEventListener("click", () => {
          tr.remove();
          calcularTotalInicial(numeroPedido);
          atualizarBotoesRemover(numeroPedido);
        });

        cellBtn.append(btnRemover);
      }
    });

    atualizarBotoesRemover(numeroPedido);
  }

  // ===========================
  // CALCULO DE TOTAL
  // ===========================
  function calcularTotalInicial(numeroPedido) {
    valorTotalAlterar = 0;
    $(`#tbody_lista_pedido_${numeroPedido} tr`).each(function () {
      let subtotal = parseFloat($(this).find('td:eq(3)').text().replace("R$", "").replace(".", "").replace(",", ".").trim()) || 0;
      valorTotalAlterar += subtotal;
    });
    atualizarValorTotalComFreteAlterar(numeroPedido);
  }

  function atualizarValorTotalComFreteAlterar(numeroPedido) {
    const total = valorTotalAlterar + valorFreteAlterar;
    document.getElementById(`valor_total_${numeroPedido}`).value = formatarMoeda(total);
    verificarLimiteCreditoAlterar(numeroPedido);
    verificarBotaoSalvar(numeroPedido);
  }

  // ===========================
  // VERIFICAÇÃO DO BOTÃO SALVAR
  // ===========================
  function verificarBotaoSalvar(numeroPedido) {
    const btnSalvar = document.getElementById(`alterar_pedido_${numeroPedido}`);
    const idCliente = $(`#id_cliente_hidden_${numeroPedido}`).val();
    const idPagamento = $(`#form_${numeroPedido} select[name='id_forma_pagamento']`).val();
    const possuiProdutos = $(`#tbody_lista_pedido_${numeroPedido} tr`).length > 0;

    btnSalvar.disabled = !(idCliente && idPagamento && possuiProdutos);
  }

  // ===========================
  // AJAX BUSCAS
  // ===========================
  function buscarClienteAlterar(termo, numeroPedido) {
    const input = document.getElementById(`cliente_pedido_${numeroPedido}`);
    const resultado = document.getElementById(`resultado_busca_cliente_${numeroPedido}`);
    if (!termo) { resultado.innerHTML = ""; return; }
    ativarSpinner(input);
    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `cliente_pedido=${encodeURIComponent(termo)}`,
    })
      .then(res => res.text())
      .then(data => { resultado.innerHTML = data; desativarSpinner(input); })
      .catch(() => { mostrarAlerta("Erro ao buscar cliente."); desativarSpinner(input); });
  }

  function buscarProdutoAlterar(termo, numeroPedido) {
    const input = document.getElementById(`produto_pedido_${numeroPedido}`);
    const resultado = document.getElementById(`resultado_busca_produto_${numeroPedido}`);
    if (!termo) { resultado.innerHTML = ""; return; }
    ativarSpinner(input);
    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `produto_pedido=${encodeURIComponent(termo)}`,
    })
      .then(res => res.text())
      .then(data => { resultado.innerHTML = data; desativarSpinner(input); })
      .catch(() => { mostrarAlerta("Erro ao buscar produto."); desativarSpinner(input); });
  }

  // ===========================
  // LIMITE DE CRÉDITO
  // ===========================
  function verificarLimiteCreditoAlterar(numeroPedido) {
    const idCliente = document.getElementById(`id_cliente_hidden_${numeroPedido}`)?.value;
    const total = Number(valorTotalAlterar.toFixed(2));
    const btnSalvar = document.getElementById(`alterar_pedido_${numeroPedido}`);
    if (!idCliente || total <= 0) { btnSalvar.disabled = true; return; }

    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `verificar_limite=1&id_cliente=${idCliente}&valor_total=${total}`,
    })
      .then(res => res.text())
      .then(resdata => {
        if (!resdata.trim()) { btnSalvar.disabled = false; return; }
        const json = JSON.parse(resdata);
        if (json.status === false) {
          btnSalvar.disabled = true;
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
      .catch(() => { btnSalvar.disabled = true; mostrarAlerta("Erro ao verificar limite de crédito!", "danger"); });
  }

  // ===========================
  // INICIALIZAÇÃO DA MODAL
  // ===========================
  function inicializarModalAlterar(numeroPedido) {
    valorTotalAlterar = 0;
    valorFreteAlterar = parseFloat(document.getElementById(`frete_${numeroPedido}`).value.replace("R$", "").replace(",", ".")) || 0;
    produtoSelecionadoAlterar = null;

    const DEBOUNCE_DELAY = 500;
    let timeoutCliente = null;
    let timeoutProduto = null;

    // BUSCAS
    $(`#cliente_pedido_${numeroPedido}`).off("input").on("input", function () {
      clearTimeout(timeoutCliente);
      const termo = $(this).val().trim();
      timeoutCliente = setTimeout(() => buscarClienteAlterar(termo, numeroPedido), DEBOUNCE_DELAY);
    });

    $(`#produto_pedido_${numeroPedido}`).off("input").on("input", function () {
      clearTimeout(timeoutProduto);
      const termo = $(this).val().trim();
      timeoutProduto = setTimeout(() => buscarProdutoAlterar(termo, numeroPedido), DEBOUNCE_DELAY);
    });

    // SELEÇÃO CLIENTE
    $(`#resultado_busca_cliente_${numeroPedido}`).off("click").on("click", ".cliente-item", function () {
      const nome = $(this).text();
      const id = $(this).data("id");
      const inputCliente = $(`#cliente_pedido_${numeroPedido}`);
      inputCliente.val(nome);

      let hidden = document.getElementById(`id_cliente_hidden_${numeroPedido}`);
      if (!hidden) {
        hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.id = `id_cliente_hidden_${numeroPedido}`;
        hidden.name = "id_cliente";
        inputCliente.parent()[0].appendChild(hidden);
      }
      hidden.value = id;
      $(this).parent().html("");
      verificarLimiteCreditoAlterar(numeroPedido);
      verificarBotaoSalvar(numeroPedido);
    });

    // SELEÇÃO PRODUTO
    $(`#resultado_busca_produto_${numeroPedido}`).off("click").on("click", ".produto-item", function () {
      const id = $(this).data("id");
      const nome = $(this).data("nome");
      const cor = $(this).data("cor");
      const largura = $(this).data("largura");
      const valor = parseFloat($(this).data("valorvenda"));
      const qtdEstoque = parseFloat($(this).data("quantidade"));

      $(`#produto_pedido_${numeroPedido}`).val(`${nome} - Cor: ${cor} - Largura: ${largura}m`);

      let hidden = document.getElementById(`id_produto_hidden_${numeroPedido}`);
      if (!hidden) {
        hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.id = `id_produto_hidden_${numeroPedido}`;
        hidden.name = "id_produto";
        $(`#produto_pedido_${numeroPedido}`).parent()[0].appendChild(hidden);
      }
      hidden.value = id;

      produtoSelecionadoAlterar = { id, nome, cor, largura, valorVenda: valor, quantidade: qtdEstoque };
      $(this).parent().html("");
    });

    // ADICIONAR PRODUTO
    $(`#adicionar_produto_${numeroPedido}`).off("click").on("click", function () {
      const idProduto = $(`#id_produto_hidden_${numeroPedido}`).val();
      const nome = $(`#produto_pedido_${numeroPedido}`).val();
      const qtd = parseFloat($(`#quantidade_${numeroPedido}`).val());
      const valorUnitario = parseFloat(produtoSelecionadoAlterar?.valorVenda) || 0;

      if (!idProduto || !qtd || qtd <= 0) return mostrarAlerta("Selecione um produto!", "warning");

      const tbody = document.getElementById(`tbody_lista_pedido_${numeroPedido}`);
      for (let tr of tbody.getElementsByTagName("tr")) {
        if (tr.dataset.idProduto === idProduto) return mostrarAlerta("Produto já adicionado!", "warning");
      }

      let valorLinha = valorUnitario * qtd;
      const tr = tbody.insertRow();
      tr.dataset.idProduto = idProduto;
      tr.insertCell(0).textContent = nome;

      const cellQtd = tr.insertCell(1);
      const inputQtd = document.createElement("input");
      inputQtd.type = "text";
      inputQtd.value = qtd;
      inputQtd.min = 1;
      inputQtd.className = "form-control form-control-sm text-center";
      cellQtd.appendChild(inputQtd);

      tr.insertCell(2).textContent = formatarMoeda(valorUnitario);
      const cellTotal = tr.insertCell(3);
      cellTotal.textContent = formatarMoeda(valorLinha);

      const cellBtn = tr.insertCell(4);
      const btnRemover = document.createElement("button");
      btnRemover.type = "button";
      btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-item";
      btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
      btnRemover.addEventListener("click", () => {
        tr.remove();
        valorTotalAlterar -= valorLinha;
        atualizarValorTotalComFreteAlterar(numeroPedido);
        atualizarBotoesRemover(numeroPedido);
      });
      cellBtn.appendChild(btnRemover);

      const hiddenUnit = document.createElement("input");
      hiddenUnit.type = "hidden"; hiddenUnit.name = "valor_unitario"; hiddenUnit.value = valorUnitario.toFixed(2);
      tr.appendChild(hiddenUnit);

      const hiddenTotal = document.createElement("input");
      hiddenTotal.type = "hidden"; hiddenTotal.name = "valor_total"; hiddenTotal.value = valorLinha.toFixed(2);
      tr.appendChild(hiddenTotal);

      inputQtd.addEventListener("focus", function () { this.dataset.valorAnterior = this.value; });
      inputQtd.addEventListener("input", function () {
        const valorAnterior = parseFloat(this.dataset.valorAnterior) || 1;
        const novaQtd = parseFloat(this.value);
        if (!novaQtd || novaQtd <= 0) { mostrarAlerta("Quantidade inválida!", "warning"); this.value = valorAnterior; return; }
        fetch("index.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `verificar_quantidade=1&id_produto=${idProduto}&quantidade=${novaQtd}`
        }).then(res => res.text()).then(data => {
          if (data.includes("erro_quantidade")) { mostrarAlerta("Estoque insuficiente!", "warning"); this.value = valorAnterior; return; }
          valorTotalAlterar -= valorLinha;
          valorLinha = valorUnitario * novaQtd;
          cellTotal.textContent = formatarMoeda(valorLinha);
          hiddenTotal.value = valorLinha.toFixed(2);
          valorTotalAlterar += valorLinha;
          atualizarValorTotalComFreteAlterar(numeroPedido);
          this.dataset.valorAnterior = novaQtd;
        });
      });

      valorTotalAlterar += valorLinha;
      atualizarValorTotalComFreteAlterar(numeroPedido);
      calcularTotalInicial(numeroPedido);
      atualizarBotoesRemover(numeroPedido);
      prepararBotoesRemover(numeroPedido);

      $(`#produto_pedido_${numeroPedido}`).val("");
      $(`#quantidade_${numeroPedido}`).val("");
      $(`#id_produto_hidden_${numeroPedido}`).remove();
      $(`#resultado_busca_produto_${numeroPedido}`).html("");
      produtoSelecionadoAlterar = null;
    });

    // FRETE
    $(`#frete_${numeroPedido}`).off("input").on("input", function () {
      let somenteNumeros = this.value.replace(/\D/g, "");
      let valor = parseFloat(somenteNumeros) / 100;
      valorFreteAlterar = isNaN(valor) ? 0 : valor;
      this.value = formatarMoeda(valorFreteAlterar);
      atualizarValorTotalComFreteAlterar(numeroPedido);
    });

    // ALTERAÇÃO FORMA DE PAGAMENTO
    $(`#form_${numeroPedido} select[name='id_forma_pagamento']`).off("change").on("change", function () {
      verificarBotaoSalvar(numeroPedido);
    });

    // SALVAR ALTERAÇÃO
    $(`#alterar_pedido_${numeroPedido}`).off("click").on("click", function (e) {
      e.preventDefault(); if (this.disabled) return;

      const idCliente = $(`#id_cliente_hidden_${numeroPedido}`).val();
      const status = $(`#status_${numeroPedido}`).val();
      const idPagamento = $(`#form_${numeroPedido} select[name='id_forma_pagamento']`).val();
      if (!idCliente || !status || !idPagamento) return mostrarAlerta("Preencha todos os campos obrigatórios!", "warning");

      const frete = valorFreteAlterar.toFixed(2);
      const total = Number((valorTotalAlterar + valorFreteAlterar).toFixed(2));

      const itens = [];
      $(`#tbody_lista_pedido_${numeroPedido} tr`).each(function () {
        itens.push({
          id_produto: this.dataset.idProduto,
          quantidade: $(this).find("input[type='text']").val(),
          valor_unitario: $(this).find("input[name='valor_unitario']").val(),
          totalValor_produto: $(this).find("input[name='valor_total']").val()
        });
      });
      const origem = document.getElementById("origem").value;

      if (itens.length === 0) return mostrarAlerta("Adicione pelo menos um produto!", "warning");

      const form = document.createElement("form");
      form.method = "POST"; form.action = "index.php";
      form.innerHTML = `
        <input type="hidden" name="alterar_pedido" value="1">
        <input type="hidden" name="id_cliente" value="${idCliente}">
        <input type="hidden" name="status_pedido" value="${status}">
        <input type="hidden" name="valor_total" value="${total}">
        <input type="hidden" name="origem" value="${origem}">
        <input type="hidden" name="id_forma_pagamento" value="${idPagamento}">
        <input type="hidden" name="valor_frete" value="${frete}">`;
      itens.forEach((item, i) => {
        form.innerHTML += `
          <input type="hidden" name="itens[${i}][id_produto]" value="${item.id_produto}">
          <input type="hidden" name="itens[${i}][quantidade]" value="${item.quantidade}">
          <input type="hidden" name="itens[${i}][valor_unitario]" value="${item.valor_unitario}">
          <input type="hidden" name="itens[${i}][totalValor_produto]" value="${item.totalValor_produto}">`;
      });

      document.body.appendChild(form);
      form.submit();
      form.remove();
    });

    // Garantir botões de remover na inicialização
    prepararBotoesRemover(numeroPedido);
    verificarBotaoSalvar(numeroPedido);
  }

  // ===========================
  // INICIALIZAÇÃO DE TODAS MODAIS
  // ===========================
  $(".modal-alterar-pedido").each(function () {
    const numeroPedido = this.id.split("_").pop();
    $(this).on("shown.bs.modal", function () {
      inicializarModalAlterar(numeroPedido);
    });
  });
});
