document.addEventListener("DOMContentLoaded", function () {
  // Inicializa a lógica para cada modal que tiver a classe 'modal-alterar-pedido'.
  document.querySelectorAll(".modal-alterar-pedido").forEach(function (modal) {
    const parts = modal.id.split("_");
    const numeroPedido = parts[parts.length - 1];
    inicializarAlterarPedido(numeroPedido);
  });

  /**
   * Inicializa a lógica específica para um pedido.
   * @param {string} numeroPedido O número/ID do pedido.
   */
  function inicializarAlterarPedido(numeroPedido) {
    // ===========================
    // VARIÁVEIS LOCAIS (por modal)
    // ===========================
    let valorTotal = 0;
    let valorFrete = 0;
    let produtoSelecionado = null;

    // ===========================
    // SELETORES DO DOM
    // ===========================
    const formEl = document.getElementById(`form_${numeroPedido}`);
    const tbody = document.getElementById(`tbody_lista_pedido_${numeroPedido}`);
    const clienteInput = document.getElementById(`cliente_pedido_${numeroPedido}`);
    const produtoInput = document.getElementById(`produto_pedido_${numeroPedido}`);
    const qtdInput = document.getElementById(`quantidade_${numeroPedido}`);
    const btnAdicionar = document.getElementById(`adicionar_produto_${numeroPedido}`);
    const btnSalvar = document.getElementById(`alterar_pedido_${numeroPedido}`);
    const freteEl = document.getElementById(`frete_${numeroPedido}`);
    const selectPagamento = formEl?.querySelector(`select[name='id_forma_pagamento']`);

    // Funções de Alerta, Formatação de Moeda e Spinner
    function mostrarAlerta(mensagem, tipo = "danger", duracao = 3000) {
      const alerta = document.createElement("div");
      alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow`;
      alerta.innerHTML = `${mensagem} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>`;
      Object.assign(alerta.style, { position: "fixed", top: "20px", right: "20px", zIndex: 1055, });
      document.body.appendChild(alerta);
      setTimeout(() => alerta.remove(), duracao);
    }

    function formatarMoeda(valor) {
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
    function getIdCliente() {
      return formEl?.querySelector('input[name="id_cliente"]')?.value;
    }

    function getIdPagamento() {
      return selectPagamento?.value;
    }

    function atualizarValorTotalComFrete() {
      const total = Number((valorTotal + valorFrete).toFixed(2));
      const el = document.getElementById(`valor_total_${numeroPedido}`);
      if (el) el.value = formatarMoeda(total);
      verificarLimiteCredito();
      verificarBotaoSalvar();
    }

    function recalcularTotaisAPartirDaTabela() {
      valorTotal = 0;
      if (!tbody) return;
      tbody.querySelectorAll("tr").forEach(tr => {
        const hiddenTotal = tr.querySelector("input[name$='[valor_total]']");
        if (hiddenTotal) {
          valorTotal += parseFloat(hiddenTotal.value) || 0;
        }
      });
      atualizarValorTotalComFrete();
    }

    function verificarBotaoSalvar() {
      if (!btnSalvar) return;
      const idCliente = getIdCliente();
      const idPagamento = getIdPagamento();
      const possuiProdutos = (tbody?.querySelectorAll("tr").length || 0) > 0;
      btnSalvar.disabled = !(idCliente && idPagamento && possuiProdutos);
    }

    function configurarBotoesRemover() {
      if (!tbody) return;
      const linhas = tbody.querySelectorAll("tr");
      const botoes = tbody.querySelectorAll(".btn-remover-item");
      botoes.forEach(btn => btn.disabled = (linhas.length <= 1));
    }

    function verificarLimiteCredito() {
      const idCliente = getIdCliente();
      const total = Number(valorTotal.toFixed(2));
      if (!idCliente || total <= 0 || !btnSalvar) {
        verificarBotaoSalvar();
        return;
      }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `verificar_limite=1&id_cliente=${idCliente}&valor_total=${total}`,
      })
        .then(res => res.text())
        .then(resdata => {
          if (!resdata.trim()) {
            verificarBotaoSalvar();
            return;
          }
          try {
            const json = JSON.parse(resdata);
            if (json.status === false) {
              btnSalvar.disabled = true;
              mostrarAlerta(`⚠️ Limite de crédito excedido!<br><strong>Limite:</strong> R$ ${parseFloat(json.limite_credito).toFixed(2).replace(".", ",")}<br><strong>Pedido:</strong> R$ ${total.toFixed(2).replace(".", ",")}`, "danger", 6000);
            } else {
              verificarBotaoSalvar();
            }
          } catch (e) {
            verificarBotaoSalvar();
          }
        }).catch(() => {
          btnSalvar.disabled = true;
          mostrarAlerta("Erro ao verificar limite de crédito!", "danger");
        });
    }

    // ===========================
    // BUSCAS (cliente/produto) e DEBOUNCE
    // ===========================
    // (As funções de busca e debounce permanecem inalteradas)
    function buscarCliente(termo) {
      const resultado = document.getElementById(`resultado_busca_cliente_${numeroPedido}`);
      if (!resultado) return;
      if (!termo) {
        resultado.innerHTML = "";
        verificarBotaoSalvar();
        return;
      }
      try { ativarSpinner(clienteInput); } catch (e) { }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `cliente_pedido=${encodeURIComponent(termo)}`,
      })
        .then(res => res.text())
        .then(data => {
          resultado.innerHTML = data;
          try { desativarSpinner(clienteInput); } catch (e) { }
        })
        .catch(() => {
          mostrarAlerta("Erro ao buscar cliente.");
          try { desativarSpinner(clienteInput); } catch (e) { }
        });
    }

    function buscarProduto(termo) {
      const resultado = document.getElementById(`resultado_busca_produto_${numeroPedido}`);
      if (!resultado) return;
      if (!termo) {
        resultado.innerHTML = "";
        document.getElementById(`id_produto_hidden_${numeroPedido}`)?.remove();
        produtoSelecionado = null;
        return;
      }
      try { ativarSpinner(produtoInput); } catch (e) { }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `produto_pedido=${encodeURIComponent(termo)}`,
      })
        .then(res => res.text())
        .then(data => {
          resultado.innerHTML = data;
          try { desativarSpinner(produtoInput); } catch (e) { }
        })
        .catch(() => {
          mostrarAlerta("Erro ao buscar produto.");
          try { desativarSpinner(produtoInput); } catch (e) { }
        });
    }

    let timeoutCliente = null;
    let timeoutProduto = null;
    const DEBOUNCE_DELAY = 800;

    if (clienteInput) {
      clienteInput.addEventListener("input", (e) => {
        clearTimeout(timeoutCliente);
        const termo = e.target.value.trim();
        timeoutCliente = setTimeout(() => buscarCliente(termo), DEBOUNCE_DELAY);
      });
    }

    if (produtoInput) {
      produtoInput.addEventListener("input", (e) => {
        clearTimeout(timeoutProduto);
        const termo = e.target.value.trim();
        timeoutProduto = setTimeout(() => buscarProduto(termo), DEBOUNCE_DELAY);
      });
    }


    // ===========================
    // DELEGAÇÃO: seleção de cliente/produto
    // ===========================
    // (As funções de seleção permanecem inalteradas)
    const resultadoClienteContainer = document.getElementById(`resultado_busca_cliente_${numeroPedido}`);
    if (resultadoClienteContainer) {
      resultadoClienteContainer.addEventListener("click", function (e) {
        const clienteItem = e.target.closest(".cliente-item");
        if (!clienteItem) return;
        const nome = clienteItem.textContent.trim();
        const id = clienteItem.dataset.id;
        if (clienteInput) clienteInput.value = nome;
        const hidden = formEl.querySelector('input[name="id_cliente"]');
        if (hidden) hidden.value = id;
        this.innerHTML = "";
        verificarLimiteCredito();
      });
    }

    const resultadoProdutoContainer = document.getElementById(`resultado_busca_produto_${numeroPedido}`);
    if (resultadoProdutoContainer) {
      resultadoProdutoContainer.addEventListener("click", function (e) {
        const produtoItem = e.target.closest(".produto-item");
        if (!produtoItem) return;

        const { id, nome, cor, largura, valorvenda, quantidade } = produtoItem.dataset;

        if (produtoInput) {
          let displayText = nome;
          if (cor) displayText += ` - Cor: ${cor}`;
          if (largura) displayText += ` - Largura: ${largura}m`;
          produtoInput.value = displayText;
        }

        let hidden = document.getElementById(`id_produto_hidden_${numeroPedido}`);
        if (!hidden) {
          hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.id = `id_produto_hidden_${numeroPedido}`;
          produtoInput?.parentElement.appendChild(hidden);
        }
        hidden.value = id;

        produtoSelecionado = { id, nome, cor: cor || '', largura: largura || '', valorVenda: parseFloat(valorvenda) || 0, quantidade: parseFloat(quantidade) || 0 };
        this.innerHTML = "";
      });
    }

    if (selectPagamento) {
      selectPagamento.addEventListener("change", verificarBotaoSalvar);
    }


    // ===========================
    // FRETE
    // ===========================
    // (A função de frete permanece inalterada)
    if (freteEl) {
      const raw = freteEl.value || "";
      if (raw.trim()) {
        const somenteNumeros = raw.replace(/\D/g, "");
        valorFrete = parseFloat(somenteNumeros) / 100 || 0;
      }

      freteEl.addEventListener("input", (e) => {
        let somenteNumeros = e.target.value.replace(/\D/g, "");
        let valor = parseFloat(somenteNumeros) / 100;
        valorFrete = isNaN(valor) ? 0 : valor;
        e.target.value = formatarMoeda(valorFrete);
        atualizarValorTotalComFrete();
      });
    }

    // ===========================
    // ADICIONAR PRODUTO
    // ===========================
    if (btnAdicionar) {
      btnAdicionar.addEventListener("click", function () {
        const idProduto = document.getElementById(`id_produto_hidden_${numeroPedido}`)?.value;
        // <-- AJUSTADO: Substitui a vírgula por ponto antes de converter para número
        const qtd = parseFloat(qtdInput?.value.replace(',', '.'));

        if (!produtoSelecionado || !idProduto || !qtd || qtd <= 0) {
          return mostrarAlerta("Selecione um produto e uma quantidade válida!", "warning");
        }
        if (!tbody) return;

        if (tbody.querySelector(`tr[data-id-produto="${idProduto}"]`)) {
          return mostrarAlerta("Produto já adicionado! Altere a quantidade na tabela.", "warning");
        }

        fetch("index.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `verificar_quantidade=1&id_produto=${idProduto}&quantidade=${qtd}`,
        })
          .then((res) => res.text())
          .then((data) => {
            if (data.includes("erro_quantidade")) {
              if (qtdInput) qtdInput.value = "";
              return mostrarAlerta("Estoque insuficiente!", "warning");
            }

            const valorUnitario = produtoSelecionado.valorVenda;
            const valorLinha = valorUnitario * qtd;
            const tr = tbody.insertRow();
            tr.dataset.idProduto = idProduto;

            // Criação das células da tabela (estrutura correta)
            const cellNome = tr.insertCell(0);
            cellNome.className = 'text-start';
            cellNome.textContent = produtoSelecionado.nome;
            tr.insertCell(1).textContent = produtoSelecionado.cor || '-';
            tr.insertCell(2).textContent = produtoSelecionado.largura || '-';
            const cellQtd = tr.insertCell(3);
            const inputQtdNaTabela = document.createElement("input");
            inputQtdNaTabela.type = "text";
            inputQtdNaTabela.className = "form-control form-control-sm text-center quantidade-item";
            inputQtdNaTabela.name = `itens[${idProduto}][quantidade]`;
            inputQtdNaTabela.value = String(qtd).replace('.', ','); // Exibe com vírgula na tabela
            inputQtdNaTabela.min = 1;
            inputQtdNaTabela.dataset.valorAnterior = String(qtd).replace('.', ',');
            cellQtd.appendChild(inputQtdNaTabela);
            tr.insertCell(4).textContent = formatarMoeda(valorUnitario);
            tr.insertCell(5).textContent = formatarMoeda(valorLinha);
            const cellBtn = tr.insertCell(6);
            cellBtn.className = 'acao-item';
            const btnRemover = document.createElement("button");
            btnRemover.type = "button";
            btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-item";
            btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
            cellBtn.appendChild(btnRemover);

            const createHiddenInput = (name, value) => {
              const input = document.createElement("input");
              input.type = "hidden";
              input.name = name;
              input.value = value;
              return input;
            };
            tr.appendChild(createHiddenInput(`itens[${idProduto}][id_produto]`, idProduto));
            tr.appendChild(createHiddenInput(`itens[${idProduto}][valor_unitario]`, valorUnitario.toFixed(2)));
            tr.appendChild(createHiddenInput(`itens[${idProduto}][valor_total]`, valorLinha.toFixed(2)));

            recalcularTotaisAPartirDaTabela();
            configurarBotoesRemover();

            if (produtoInput) produtoInput.value = "";
            if (qtdInput) qtdInput.value = "";
            document.getElementById(`id_produto_hidden_${numeroPedido}`)?.remove();
            document.getElementById(`resultado_busca_produto_${numeroPedido}`).innerHTML = "";
            produtoSelecionado = null;
          })
          .catch(() => mostrarAlerta("Erro ao verificar estoque!", "danger"));
      });
    }

    // ===========================
    // DELEGAÇÃO: Alteração e Remoção em Linhas da Tabela
    // ===========================
    if (tbody) {
      tbody.addEventListener("focusin", function (e) {
        const input = e.target.closest(".quantidade-item");
        if (input) {
          input.dataset.valorAnterior = input.value;
        }
      });

      tbody.addEventListener("input", function (e) {
        const input = e.target.closest(".quantidade-item");
        if (!input) return;

        const tr = input.closest("tr");
        if (!tr) return;

        const idProduto = tr.dataset.idProduto;
        // <-- AJUSTADO: Substitui a vírgula por ponto ao editar na tabela
        const novaQtd = parseFloat(input.value.replace(',', '.'));
        const valorUnitario = parseFloat(tr.querySelector("input[name$='[valor_unitario]']")?.value) || 0;
        const hiddenTotal = tr.querySelector("input[name$='[valor_total]']");
        const cellTotal = tr.querySelector("td:nth-child(6)");
        const valorAnterior = input.dataset.valorAnterior;

        if (!idProduto || isNaN(novaQtd) || novaQtd <= 0) {
          mostrarAlerta("Quantidade inválida!", "warning");
          input.value = valorAnterior;
          return;
        }

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
            const novoSubtotal = valorUnitario * novaQtd;
            if (cellTotal) cellTotal.textContent = formatarMoeda(novoSubtotal);
            if (hiddenTotal) hiddenTotal.value = novoSubtotal.toFixed(2);
            recalcularTotaisAPartirDaTabela();
            input.dataset.valorAnterior = input.value; // Atualiza com o valor atual (que pode ter vírgula)
          })
          .catch(() => {
            mostrarAlerta("Erro ao verificar estoque!", "danger");
            input.value = valorAnterior;
          });
      });

      tbody.addEventListener("click", function (e) {
        const btn = e.target.closest(".btn-remover-item");
        if (!btn) return;
        btn.closest("tr")?.remove();
        recalcularTotaisAPartirDaTabela();
        configurarBotoesRemover();
      });
    }

    // ===========================
    // SALVAR ALTERAÇÃO
    // ===========================
    // (A função de salvar permanece inalterada)
    if (btnSalvar) {
      btnSalvar.addEventListener("click", function (e) {
        e.preventDefault();
        const idCliente = getIdCliente();
        const status = document.getElementById(`status_${numeroPedido}`)?.value || "Pendente";
        const idPagamento = getIdPagamento();

        if (this.disabled || !idCliente || !idPagamento || (tbody?.querySelectorAll("tr").length || 0) === 0) {
          return mostrarAlerta("Preencha todos os campos obrigatórios e verifique os produtos e o limite de crédito!", "warning", 4000);
        }

        const origem = formEl?.querySelector('input[name="origem"]')?.value || "";
        const frete = Number(valorFrete).toFixed(2);
        const total = Number((valorTotal + valorFrete).toFixed(2));
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "index.php";

        form.innerHTML = `
          <input type="hidden" name="alterar_pedido" value="1">
          <input type="hidden" name="id_pedido" value="${numeroPedido}">
          <input type="hidden" name="id_cliente" value="${idCliente}">
          <input type="hidden" name="status_pedido" value="${status}">
          <input type="hidden" name="valor_total" value="${total}">
          <input type="hidden" name="id_forma_pagamento" value="${idPagamento}">
          <input type="hidden" name="origem" value="${origem}">
          <input type="hidden" name="valor_frete" value="${frete}">
        `;

        tbody.querySelectorAll("tr").forEach((tr, i) => {
          const idProduto = tr.dataset.idProduto;
          const quantidade = tr.querySelector("input.quantidade-item")?.value.replace(',', '.') || 0; // Envia com ponto
          const valorUnitario = tr.querySelector("input[name$='[valor_unitario]']")?.value || 0;
          const totalValorProduto = tr.querySelector("input[name$='[valor_total]']")?.value || 0;

          form.innerHTML += `
            <input type="hidden" name="itens[${i}][id_produto]" value="${idProduto}">
            <input type="hidden" name="itens[${i}][quantidade]" value="${quantidade}">
            <input type="hidden" name="itens[${i}][valor_unitario]" value="${valorUnitario}">
            <input type="hidden" name="itens[${i}][totalValor_produto]" value="${totalValorProduto}">
          `;
        });

        document.body.appendChild(form);
        form.submit();
        form.remove();
      });
    }


    // ===========================
    // CONFIGURAÇÃO INICIAL
    // ===========================
    function configurarLinhasPreExistentes() {
      if (!tbody) return;
      tbody.querySelectorAll("tr").forEach(tr => {
        let cellBtn = tr.querySelector(".acao-item") || tr.cells[6];
        if (cellBtn && !cellBtn.querySelector(".btn-remover-item")) {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className = "btn btn-outline-danger btn-sm btn-remover-item";
          btn.innerHTML = '<i class="bi bi-trash"></i>';
          cellBtn.appendChild(btn);
        }
        const inputQtd = tr.querySelector("input.quantidade-item");
        if (inputQtd) {
          inputQtd.dataset.valorAnterior = inputQtd.value;
        }
      });
    }

    configurarLinhasPreExistentes();
    configurarBotoesRemover();
    recalcularTotaisAPartirDaTabela();

    const modalEl = document.getElementById(`modal_alterar_pedido_${numeroPedido}`);
    if (modalEl) {
      modalEl.addEventListener("shown.bs.modal", function () {
        configurarLinhasPreExistentes();
        configurarBotoesRemover();
        recalcularTotaisAPartirDaTabela();
      });
    }
  }
});