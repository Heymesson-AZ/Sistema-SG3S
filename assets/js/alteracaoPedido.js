document.addEventListener("DOMContentLoaded", function () {
  // Inicializa a lógica para cada modal que tiver a classe 'modal-alterar-pedido'.
  document.querySelectorAll(".modal-alterar-pedido").forEach(function (modal) {
    const parts = modal.id.split("_");
    // Extrai o número do pedido do ID da modal (e.g., modal_alterar_pedido_123 -> 123)
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
    let valorTotal = 0; // Soma dos subtotais dos produtos
    let valorFrete = 0;
    let produtoSelecionado = null; // Armazena dados do produto selecionado na busca

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

    // Funções de Alerta, Formatação de Moeda e Spinner (mantidas/simplificadas)
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

    /** Retorna o ID do cliente. (Ajustado para buscar pelo nome 'id_cliente' dentro do form) */
    function getIdCliente() {
      return formEl?.querySelector('input[name="id_cliente"]')?.value;
    }

    /** Retorna o ID da forma de pagamento. */
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

    /** Recalcula valorTotal (soma dos produtos) lendo a tabela e atualiza o total final. */
    function recalcularTotaisAPartirDaTabela() {
      valorTotal = 0;
      if (!tbody) return;

      Array.from(tbody.querySelectorAll("tr")).forEach(tr => {
        // CORREÇÃO: Lê o valor do hidden input 'valor_total' que é sempre atualizado.
        const hiddenTotal = tr.querySelector("input[name$='[valor_total]']") || tr.querySelector("input[name='valor_total']");
        let subtotal = 0;

        if (hiddenTotal) {
          subtotal = parseFloat(hiddenTotal.value) || 0;
        } else {
          // Fallback para o texto da célula (usado para dados pre-carregados se hidden falhar)
          const text = tr.querySelector("td:nth-child(4)")?.textContent || "R$ 0,00";
          subtotal = parseFloat(text.replace("R$", "").replace(/\./g, "").replace(",", ".")) || 0;
        }
        valorTotal += subtotal;
      });
      atualizarValorTotalComFrete();
    }

    /** Habilita/desabilita o botão salvar. */
    function verificarBotaoSalvar() {
      if (!btnSalvar) return;
      const idCliente = getIdCliente();
      const idPagamento = getIdPagamento();
      const possuiProdutos = (tbody?.querySelectorAll("tr").length || 0) > 0;

      // Requisitos: Cliente selecionado, Pagamento selecionado, Pelo menos 1 produto.
      const requisitosAtendidos = (idCliente && idPagamento && possuiProdutos);

      // Só re-habilita se o limite de crédito não tiver desabilitado o botão anteriormente
      if (requisitosAtendidos) {
        // Se a verificação de limite não desabilitou (ou for assíncrona), habilita
        btnSalvar.disabled = false;
      } else {
        btnSalvar.disabled = true;
      }
    }

    /** Desabilita todos os botões de remover se houver apenas 1 item. */
    function configurarBotoesRemover() {
      if (!tbody) return;
      const linhas = tbody.querySelectorAll("tr");
      const botoes = tbody.querySelectorAll(".btn-remover-item");

      // Desabilita se houver apenas 1 item
      if (linhas.length <= 1) {
        botoes.forEach(btn => btn.disabled = true);
      } else if (linhas.length > 1) {
        botoes.forEach(btn => btn.disabled = false);
      }
    }

    function verificarLimiteCredito() {
      const idCliente = getIdCliente();
      const total = Number((valorTotal).toFixed(2));

      if (!idCliente || total <= 0 || !btnSalvar) {
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
            // Resposta vazia ou OK, reativa a verificação normal do botão salvar
            verificarBotaoSalvar();
            return;
          }
          try {
            const json = JSON.parse(resdata);
            if (json.status === false) {
              btnSalvar.disabled = true;
              mostrarAlerta(
                `⚠️ Limite de crédito excedido!<br>
                                <strong>Limite:</strong> R$ ${parseFloat(json.limite_credito).toFixed(2).replace(".", ",")}<br>
                                <strong>Pedido:</strong> R$ ${total.toFixed(2).replace(".", ",")}`,
                "danger",
                6000
              );
            } else {
              verificarBotaoSalvar();
            }
          } catch (e) {
            verificarBotaoSalvar();
          }
        })
        .catch(() => {
          btnSalvar.disabled = true;
          mostrarAlerta("Erro ao verificar limite de crédito!", "danger");
        });
    }

    // ===========================
    // BUSCAS (cliente/produto) e DEBOUNCE
    // ===========================
    // ... (Lógica de busca de cliente e produto: mantida)
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
        // Limpa o produto selecionado
        const idProdHidden = document.getElementById(`id_produto_hidden_${numeroPedido}`);
        if (idProdHidden) idProdHidden.remove();
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
    const DEBOUNCE_DELAY = 500;

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
    const resultadoClienteContainer = document.getElementById(`resultado_busca_cliente_${numeroPedido}`);
    if (resultadoClienteContainer) {
      resultadoClienteContainer.addEventListener("click", function (e) {
        const clienteItem = e.target.closest(".cliente-item");
        if (!clienteItem) return;
        const nome = clienteItem.textContent.trim();
        const id = clienteItem.dataset.id;

        if (clienteInput) clienteInput.value = nome;

        // CORREÇÃO: Usa o input hidden existente no form (que tem name="id_cliente")
        const hidden = formEl.querySelector('input[name="id_cliente"]');
        if (hidden) hidden.value = id;

        this.innerHTML = "";
        verificarLimiteCredito();
        verificarBotaoSalvar();
      });
    }

    const resultadoProdutoContainer = document.getElementById(`resultado_busca_produto_${numeroPedido}`);
    if (resultadoProdutoContainer) {
      resultadoProdutoContainer.addEventListener("click", function (e) {
        const produtoItem = e.target.closest(".produto-item");
        if (!produtoItem) return;
        const id = produtoItem.dataset.id;
        const nome = produtoItem.dataset.nome;
        const cor = produtoItem.dataset.cor;
        const largura = produtoItem.dataset.largura;
        const valor = produtoItem.dataset.valorvenda;
        const qtdEstoque = produtoItem.dataset.quantidade;

        if (produtoInput) produtoInput.value = `${nome} - Cor: ${cor} - Largura: ${largura}m`;

        // Cria um input hidden temporário para o ID do produto, usado apenas no clique de Adicionar
        let hidden = document.getElementById(`id_produto_hidden_${numeroPedido}`);
        if (!hidden) {
          hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.id = `id_produto_hidden_${numeroPedido}`;
          produtoInput?.parentElement.appendChild(hidden);
        }
        hidden.value = id;

        produtoSelecionado = {
          id,
          nome,
          cor,
          largura,
          valorVenda: parseFloat(valor) || 0,
          quantidade: parseFloat(qtdEstoque) || 0
        };

        this.innerHTML = "";
      });
    }

    // Listener para o select de forma de pagamento
    if (selectPagamento) {
      selectPagamento.addEventListener("change", verificarBotaoSalvar);
    }

    // ===========================
    // FRETE
    // ===========================
    if (freteEl) {
      // Inicializa valorFrete lendo o campo formatado
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
        const nomeProduto = produtoInput?.value || "";
        const qtd = parseFloat(qtdInput?.value);
        const valorUnitario = produtoSelecionado?.valorVenda || 0;

        if (!idProduto || !qtd || qtd <= 0 || valorUnitario === 0) {
          return mostrarAlerta("Selecione um produto e uma quantidade válida!", "warning");
        }

        if (!tbody) return;

        // Verifica se já existe (usando data-id-produto)
        for (let tr of tbody.getElementsByTagName("tr")) {
          if (String(tr.dataset.idProduto) === String(idProduto)) {
            return mostrarAlerta("Produto já adicionado! Altere a quantidade na tabela.", "warning");
          }
        }

        // 1. Verificar estoque via ajax
        fetch("index.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `verificar_quantidade=1&id_produto=${idProduto}&quantidade=${qtd}`,
        })
          .then((res) => res.text())
          .then((data) => {
            if (data.includes("erro_quantidade")) {
              return mostrarAlerta("Estoque insuficiente!", "warning");
            }

            // Estoque OK, pode adicionar
            let valorLinha = valorUnitario * qtd;
            const tr = tbody.insertRow();
            tr.dataset.idProduto = idProduto;

            // 0: Nome
            tr.insertCell(0).textContent = nomeProduto;

            // 1: Quantidade (Input)
            const cellQtd = tr.insertCell(1);
            const inputQtd = document.createElement("input");
            inputQtd.type = "number";
            inputQtd.step = "any";
            inputQtd.value = qtd;
            inputQtd.min = 1;
            // CORREÇÃO: Usando a classe correta conforme o PHP: quantidade-item
            inputQtd.className = "form-control form-control-sm text-center quantidade-item";
            inputQtd.dataset.valorAnterior = qtd;
            cellQtd.appendChild(inputQtd);

            // 2: Valor Unitário (Visível)
            tr.insertCell(2).textContent = formatarMoeda(valorUnitario);

            // 3: Subtotal (Visível)
            const cellTotal = tr.insertCell(3);
            cellTotal.textContent = formatarMoeda(valorLinha);

            // 4: Ação (Botão) - Adiciona o botão na célula de ação
            const cellBtn = tr.insertCell(4);
            cellBtn.className = 'acao-item'; // Mantém a classe

            const btnRemover = document.createElement("button");
            btnRemover.type = "button";
            btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-item";
            btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
            // O listener de delegação cuidará da remoção, mas adicionamos um listener direto por segurança
            btnRemover.addEventListener("click", () => {
              tr.remove();
              recalcularTotaisAPartirDaTabela();
              configurarBotoesRemover();
              verificarBotaoSalvar();
            });
            cellBtn.appendChild(btnRemover);

            // Hidden inputs para submissão (usando a estrutura de array do PHP para itens adicionados dinamicamente)
            // NOTA: O PHP usa `name="itens[ID][campo]"` na modal, vamos replicar o name aqui para a submissão.
            tr.innerHTML += `
                            <input type="hidden" name="itens[${idProduto}][id_produto]" value="${idProduto}">
                            <input type="hidden" name="itens[${idProduto}][valor_unitario]" value="${valorUnitario.toFixed(2)}">
                            <input type="hidden" name="itens[${idProduto}][valor_total]" value="${valorLinha.toFixed(2)}">
                        `;

            // O listener do inputQtd é coberto pelo listener de delegação do tbody

            // 2. Atualizar totais após adicionar
            recalcularTotaisAPartirDaTabela();
            configurarBotoesRemover();

            // 3. Limpar inputs de busca
            if (produtoInput) produtoInput.value = "";
            if (qtdInput) qtdInput.value = "";
            // Remove o hidden ID do produto (temporário)
            const idProdHiddenEl = document.getElementById(`id_produto_hidden_${numeroPedido}`);
            if (idProdHiddenEl) idProdHiddenEl.remove();
            const resultadoProd = document.getElementById(`resultado_busca_produto_${numeroPedido}`);
            if (resultadoProd) resultadoProd.innerHTML = "";
            produtoSelecionado = null;
            verificarBotaoSalvar();
          })
          .catch(() => mostrarAlerta("Erro ao verificar estoque!", "danger"));
      });
    }

    // ===========================
    // DELEGAÇÃO: Alteração de Quantidade e Remoção em Linhas Existentes
    // ===========================
    if (tbody) {

      // Delegação para FOCUSIN (para guardar valor anterior - necessário para validação)
      tbody.addEventListener("focusin", function (e) {
        const input = e.target.closest(".quantidade-item");
        if (input) {
          input.dataset.valorAnterior = input.value;
        }
      });

      // Delegação para INPUT (alteração de quantidade)
      tbody.addEventListener("input", function (e) {
        // CORREÇÃO: Usando a classe correta
        const input = e.target.closest(".quantidade-item");
        if (!input) return;

        const tr = input.closest("tr");
        const idProduto = tr?.dataset?.idProduto;
        const novaQtd = parseFloat(input.value);

        // CORREÇÃO: Busca o hidden input de valor unitário pelo name
        const hiddenUnit = tr.querySelector("input[name$='[valor_unitario]']");
        const valorUnitario = parseFloat(hiddenUnit?.value) || 0;

        // CORREÇÃO: Busca o hidden input de valor total pelo name
        const hiddenTotal = tr.querySelector("input[name$='[valor_total]']");
        const cellTotal = tr.querySelector("td:nth-child(4)"); // Célula de subtotal visível
        const valorAnterior = parseFloat(input.dataset.valorAnterior) || 1;

        if (!idProduto || !novaQtd || novaQtd <= 0) {
          mostrarAlerta("Quantidade inválida!", "warning");
          input.value = valorAnterior;
          return;
        }

        // Validação de estoque via ajax
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

            // Recalcular subtotal da linha e atualizar hidden + célula visível
            const novoSubtotal = valorUnitario * novaQtd;
            cellTotal.textContent = formatarMoeda(novoSubtotal);
            if (hiddenTotal) hiddenTotal.value = novoSubtotal.toFixed(2);

            // Recalcula todos os totais (mais seguro)
            recalcularTotaisAPartirDaTabela();

            input.dataset.valorAnterior = novaQtd; // Atualiza o valor anterior
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
        verificarBotaoSalvar();
        configurarBotoesRemover();
      });
    }

    // ===========================
    // SALVAR ALTERAÇÃO
    // ===========================
    if (btnSalvar) {
      btnSalvar.addEventListener("click", function (e) {
        e.preventDefault();

        const idCliente = getIdCliente();
        const status = document.getElementById(`status_${numeroPedido}`)?.value || "Pendente"; // Status não existe na modal fornecida, assumindo "Pendente"
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

        // Campos principais (re-criados no form dinâmico)
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

        // ITENS - Coletando valores mais atuais da tabela
        Array.from(tbody.querySelectorAll("tr")).forEach((tr, i) => {
          const idProduto = tr.dataset.idProduto;
          // CORREÇÃO: Buscando valores ATUAIS dos inputs e hiddens
          const quantidade = tr.querySelector("input.quantidade-item")?.value || 0;
          const valorUnitario = tr.querySelector("input[name$='[valor_unitario]']")?.value || 0;
          const totalValorProduto = tr.querySelector("input[name$='[valor_total]']")?.value || 0;

          // NOTA: Usando índices numéricos no form dinâmico para garantir o envio correto
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
    // CONFIGURAÇÃO INICIAL (Ao carregar ou abrir a modal)
    // ===========================

    /** Garante que as linhas pre-existentes tenham o botão remover e a classe de input correta. */
    function configurarLinhasPreExistentes() {
      if (!tbody) return;

      tbody.querySelectorAll("tr").forEach(tr => {
        // 1. Garantir o botão remover na célula 'acao-item' (índice 4)
        let cellBtn = tr.querySelector(".acao-item");
        if (!cellBtn) {
          // Se a célula de ação não existir (bug na renderização do PHP), cria uma
          cellBtn = tr.insertCell(4);
          cellBtn.className = 'acao-item';
        }

        let btn = cellBtn.querySelector(".btn-remover-item");
        if (!btn) {
          btn = document.createElement("button");
          btn.type = "button";
          btn.className = "btn btn-outline-danger btn-sm btn-remover-item";
          btn.innerHTML = '<i class="bi bi-trash"></i>';
          // Listener direto para linhas pre-existentes
          btn.addEventListener("click", () => {
            tr.remove();
            recalcularTotaisAPartirDaTabela();
            verificarBotaoSalvar();
            configurarBotoesRemover();
          });
          cellBtn.appendChild(btn);
        }

        // 2. Garantir que o input de quantidade tenha a classe correta
        // CORREÇÃO: Usando a classe correta 'quantidade-item'
        const inputQtd = tr.querySelector("input[type='text'], input[type='number']");
        if (inputQtd && !inputQtd.classList.contains("quantidade-item")) {
          inputQtd.classList.add("quantidade-item");
          inputQtd.dataset.valorAnterior = inputQtd.value;
        }
      });
    }

    // Executa a configuração inicial
    configurarLinhasPreExistentes();
    configurarBotoesRemover();
    recalcularTotaisAPartirDaTabela();
    verificarBotaoSalvar();

    // Garante que o recálculo e o estado dos botões ocorram na abertura (se for modal)
    const modalEl = document.getElementById(`modal_alterar_pedido_${numeroPedido}`);
    if (modalEl) {
      modalEl.addEventListener("shown.bs.modal", function () {
        configurarLinhasPreExistentes();
        configurarBotoesRemover();
        recalcularTotaisAPartirDaTabela();
        verificarBotaoSalvar();
      });
    }
  }
});
