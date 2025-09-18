document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".modal-alterar-pedido").forEach((modal) => {
    inicializarModalAlteracao(modal);
  });
});

// funcao que inicializa/carrega a modal de alteracao
function inicializarModalAlteracao(modal) {
  const numPedido = modal.id.replace("modal_alterar_pedido_", "");

  // ----------------------------------------------------------
  // VARIÁVEIS GLOBAIS DO CONTEXTO DA MODAL
  // ----------------------------------------------------------
  let valorTotal = calcularTotalProdutos(modal);
  let valorFrete =
    parseValorMoeda(modal.querySelector(`#frete_${numPedido}`)?.value) || 0;
  let produtoSelecionado = null;

  // Debounce timers
  let timeoutCliente = null;
  let timeoutProduto = null;
  const DEBOUNCE_DELAY = 500;

  // Inicializa exibição do total
  atualizarValorTotalComFrete();

  // ----------------------------------------------------------
  // UTILITÁRIAS
  // ----------------------------------------------------------

  /**
   * Exibe alerta visual flutuante
   */
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

  /**
   * Formata número como moeda brasileira
   */
  function formatarMoeda(valor) {
    return "R$ " + valor.toFixed(2).replace(".", ",");
  }

  /**
   * Converte string monetária (R$ x.xxx,xx) em float
   */
  function parseValorMoeda(valor) {
    if (!valor) return 0;
    return (
      parseFloat(valor.toString().replace("R$", "").replace(/\./g, "").replace(",", ".")) || 0
    );
  }

  /**
   * Atualiza o campo de valor total com o frete incluso
   */
  function atualizarValorTotalComFrete() {
    const total = valorTotal + valorFrete;
    const inputTotal = modal.querySelector(`#valor_total_${numPedido}`);
    if (inputTotal) {
      inputTotal.value = formatarMoeda(total);
    }
    verificarLimiteCredito(); // manter mesma verificação do cadastro
  }

  /**
   * Habilita spinner em botão (texto de processamento)
   */
  function ativarSpinner(botao) {
    botao.disabled = true;
    botao.dataset.originalText = botao.innerHTML;
    botao.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Processando...`;
  }

  /**
   * Desativa spinner em botão
   */
  function desativarSpinner(botao) {
    botao.disabled = false;
    if (botao.dataset.originalText) botao.innerHTML = botao.dataset.originalText;
  }

  // ----------------------------------------------------------
  // BUSCAS AJAX (cliente / produto) - com debounce
  // ----------------------------------------------------------

  // busca dinamica de cliente
  function buscarCliente(termo) {
    const input = modal.querySelector(`#cliente_pedido_${numPedido}`);
    const resultado = modal.querySelector(`#resultado_busca_cliente_${numPedido}`);

    // se vazio limpa
    if (!termo) {
      resultado.innerHTML = "";
      return;
    }

    ativarSpinner(input);
    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `cliente_pedido=${encodeURIComponent(termo)}`,
    })
      .then((res) => res.text())
      .then((data) => {
        resultado.innerHTML = data;
        desativarSpinner(input);
        // usamos ativarSelecaoCliente para ligar listeners aos itens retornados
        ativarSelecaoCliente();
      })
      .catch((error) => {
        console.error(error);
        mostrarAlerta("Erro ao buscar cliente.");
        desativarSpinner(input);
      });
  }

  // busca dinamica do produto
  function buscarProduto(termo) {
    const input = modal.querySelector(`#produto_pedido_${numPedido}`);
    const resultado = modal.querySelector(`#resultado_busca_produto_${numPedido}`);

    // se vazio limpa
    if (!termo) {
      resultado.innerHTML = "";
      return;
    }

    ativarSpinner(input);
    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `produto_pedido=${encodeURIComponent(termo)}`,
    })
      .then((res) => res.text())
      .then((data) => {
        resultado.innerHTML = data;
        desativarSpinner(input);
        ativarSelecaoProduto();
      })
      .catch((error) => {
        console.error(error);
        mostrarAlerta("Erro ao buscar produto.");
        desativarSpinner(input);
      });
  }

  // ----------------------------------------------------------
  // EVENTOS DE INPUT COM DEBOUNCE (cliente/produto)
  // ----------------------------------------------------------
  const inputClienteEl = modal.querySelector(`#cliente_pedido_${numPedido}`);
  if (inputClienteEl) {
    inputClienteEl.addEventListener("input", (e) => {
      clearTimeout(timeoutCliente);
      const termo = e.target.value.trim();
      timeoutCliente = setTimeout(() => buscarCliente(termo), DEBOUNCE_DELAY);
    });
  }

  const inputProdutoEl = modal.querySelector(`#produto_pedido_${numPedido}`);
  if (inputProdutoEl) {
    inputProdutoEl.addEventListener("input", (e) => {
      clearTimeout(timeoutProduto);
      const termo = e.target.value.trim();
      timeoutProduto = setTimeout(() => buscarProduto(termo), DEBOUNCE_DELAY);
    });
  }

  // ----------------------------------------------------------
  // EVENTOS DE SELEÇÃO (CLIQUES EM ITENS DE BUSCA)
  // ----------------------------------------------------------

  // selecao de clientes com a buscas ajax (liga listeners aos spans retornados)
  function ativarSelecaoCliente() {
    modal
      .querySelectorAll(`#resultado_busca_cliente_${numPedido} .cliente-item`)
      .forEach((span) => {
        span.addEventListener("click", function () {
          const nome = this.textContent;
          const id = this.dataset.id;
          const input = modal.querySelector(`#cliente_pedido_${numPedido}`);
          input.value = nome;
          const hidden = modal.querySelector(`input[name="id_cliente"]`);
          if (hidden) hidden.value = id;
          modal.querySelector(`#resultado_busca_cliente_${numPedido}`).innerHTML = "";
          verificarLimiteCredito(); // revalida limite quando cliente muda
        });
      });
  }

  // selecao de produtos da busca ajax (liga listeners aos spans retornados)
  function ativarSelecaoProduto() {
    modal
      .querySelectorAll(`#resultado_busca_produto_${numPedido} .produto-item`)
      .forEach((span) => {
        span.addEventListener("click", function () {
          const id = this.dataset.id;
          const nome = this.dataset.nome;
          const cor = this.dataset.cor;
          const largura = this.dataset.largura;
          const valor = this.dataset.valorvenda;
          const qtd = this.dataset.quantidade;

          const input = modal.querySelector(`#produto_pedido_${numPedido}`);
          input.value = `${nome} - ${cor} - Largura: ${largura}cm`;

          let hidden = modal.querySelector(`#id_produto_hidden_${numPedido}`);
          if (!hidden) {
            hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.id = `id_produto_hidden_${numPedido}`;
            hidden.name = "id_produto";
            input.parentElement.appendChild(hidden);
          }
          hidden.value = id;

          produtoSelecionado = {
            id,
            nome,
            cor,
            largura,
            valorVenda: parseFloat(valor),
            quantidade: parseInt(qtd),
          };

          // limpa resultados visuais
          modal.querySelector(`#resultado_busca_produto_${numPedido}`).innerHTML = "";
        });
      });
  }

  // ----------------------------------------------------------
  // FUNÇÕES DE CÁLCULO
  // ----------------------------------------------------------

  /**
   * Calcula o total dos produtos já na tabela
   */
  function calcularTotalProdutos(modalRef) {
    let total = 0;
    modalRef
      .querySelectorAll(`#tbody_lista_pedido_${numPedido} tr`)
      .forEach((tr) => {
        const valor = tr.querySelector(
          `input[name^="itens"][name$="[valor_unitario]"], input[name*="[valor_unitario]"]`
        );
        const qtd = tr.querySelector(
          `input[name^="itens"][name$="[quantidade]"], input[name*="[quantidade]"]`
        );
        if (valor && qtd) {
          total += parseFloat(valor.value) * parseFloat(qtd.value);
        } else {
          // fallback: tenta ler colunas visíveis (td) se houver
          const cellValor = tr.querySelector("td:nth-child(3)");
          const cellTotal = tr.querySelector("td:nth-child(4)");
          if (cellTotal) {
            total += parseValorMoeda(cellTotal.textContent || cellTotal.innerText || "0");
          }
        }
      });
    return total;
  }

  // ----------------------------------------------------------
  // ADICIONAR PRODUTO À LISTA
  // ----------------------------------------------------------

  function adicionarProduto() {
    const idProduto = modal.querySelector(`#id_produto_hidden_${numPedido}`)?.value;
    const nome = modal.querySelector(`#produto_pedido_${numPedido}`).value;
    const qtd = parseInt(modal.querySelector(`#quantidade_${numPedido}`).value);
    const valorUnitario = produtoSelecionado?.valorVenda || 0;

    if (!idProduto || !qtd || qtd <= 0) {
      return mostrarAlerta("Dados do produto incompletos!", "warning");
    }

    const tbody = modal.querySelector(`#tbody_lista_pedido_${numPedido}`);

    // Evita duplicidade
    for (let tr of tbody.getElementsByTagName("tr")) {
      if (tr.dataset.idProduto === idProduto) {
        return mostrarAlerta("Produto já adicionado!", "warning");
      }
    }

    let valorLinha = valorUnitario * qtd;

    const tr = tbody.insertRow();
    tr.dataset.idProduto = idProduto;

    tr.insertCell(0).textContent = nome;

    // Qtd input
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

    // Hidden inputs
    const hiddenFields = [
      ["valor_unitario", valorUnitario.toFixed(2)],
      ["valor_total", valorLinha.toFixed(2)],
      ["id_produto", idProduto],
      ["quantidade", qtd],
    ];

    hiddenFields.forEach(([name, value]) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = `itens[${idProduto}][${name}]`;
      input.value = value;
      tr.appendChild(input);
    });

    // Remover linha (botão)
    const cellRemove = tr.insertCell(4);
    const btnRemover = document.createElement("button");
    btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-linha";
    btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
    cellRemove.appendChild(btnRemover);

    // Eventos de alteração de quantidade
    inputQtd.addEventListener("focus", function () {
      this.dataset.valorAnterior = this.value;
    });

    inputQtd.addEventListener("input", function () {
      const novaQtd = parseInt(this.value);
      const valorAnterior = parseInt(this.dataset.valorAnterior) || qtd;

      if (isNaN(novaQtd) || novaQtd <= 0) {
        mostrarAlerta("Quantidade inválida!", "warning");
        this.value = valorAnterior;
        return;
      }

      // verifica estoque no backend
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

          tr.querySelector(`input[name="itens[${idProduto}][valor_total]"]`).value =
            valorLinha.toFixed(2);
          tr.querySelector(`input[name="itens[${idProduto}][quantidade]"]`).value = novaQtd;

          atualizarValorTotalComFrete();
          this.dataset.valorAnterior = novaQtd;
        })
        .catch(() => {
          mostrarAlerta("Erro ao verificar estoque.", "danger");
          this.value = valorAnterior;
        });
    });

    valorTotal += valorLinha;
    atualizarValorTotalComFrete();

    // Limpa campos após inclusão
    modal.querySelector(`#produto_pedido_${numPedido}`).value = "";
    modal.querySelector(`#quantidade_${numPedido}`).value = "";
    modal.querySelector(`#id_produto_hidden_${numPedido}`)?.remove();
    modal.querySelector(`#resultado_busca_produto_${numPedido}`).innerHTML = "";
    produtoSelecionado = null;
    // atualiza estado do botão remover
    atualizarEstadoBotoesRemover();
  }

  // ----------------------------------------------------------
  // EVENTOS GERAIS DA MODAL (botões, frete, adicionar)
  // ----------------------------------------------------------
  const btnAdicionar = modal.querySelector(`#adicionar_produto_${numPedido}`);
  if (btnAdicionar) btnAdicionar.addEventListener("click", adicionarProduto);

  // Frete - formata como R$ 0,00 (aceita digitação com máscara estilo cadastro)
  const freteEl = modal.querySelector(`#frete_${numPedido}`);
  if (freteEl) {
    // Aplicar formatação semelhante ao JS de cadastro (digitar apenas números -> R$ x,xx)
    freteEl.addEventListener("input", (e) => {
      // remove tudo que não seja número
      let somenteNumeros = e.target.value.replace(/\D/g, "");
      let valor = parseFloat(somenteNumeros) / 100;
      valorFrete = isNaN(valor) ? 0 : valor;
      e.target.value = formatarMoeda(valorFrete);
      atualizarValorTotalComFrete();
    });

    // se o campo já vem com R$... parse inicial (já feito), garantir exibição correta
    freteEl.value = formatarMoeda(valorFrete);
  }

  // ----------------------------------------------------------
  // SALVAR ALTERAÇÃO DE PEDIDO E ENVIO DO FORMULARIO
  // ----------------------------------------------------------
  const btnAlterar = modal.querySelector(`#alterar_pedido_${numPedido}`);
  if (btnAlterar) {
    btnAlterar.addEventListener("click", (e) => {
      e.preventDefault();
      const btn = e.currentTarget;
      ativarSpinner(btn);

      const idCliente = modal.querySelector(`input[name="id_cliente"]`)?.value;
      const idFormaPagamento = modal.querySelector(`select[name="id_forma_pagamento"]`)?.value;
      const data = modal.querySelector(`#data_pedido_${numPedido}`)?.value;
      const origem = modal.querySelector(`input[name="origem"]`)?.value;
      const frete = valorFrete.toFixed(2);
      const total = (valorTotal + valorFrete).toFixed(2);
      const idPedido = modal.querySelector(`input[name="id_pedido"]`)?.value;

      const itens = [];
      modal
        .querySelectorAll(`#tbody_lista_pedido_${numPedido} tr`)
        .forEach((tr) => {
          const idProduto = tr.dataset.idProduto;
          const qtd = tr.querySelector("input[type='number']").value;
          const valorUnitario = tr.querySelector(
            `input[name="itens[${idProduto}][valor_unitario]"]`
          ).value;
          const valorTotalProd = tr.querySelector(
            `input[name="itens[${idProduto}][valor_total]"]`
          ).value;

          itens.push({
            id_produto: idProduto,
            quantidade: qtd,
            valor_unitario: valorUnitario,
            totalValor_produto: valorTotalProd,
          });
        });

      if (!idCliente || !idFormaPagamento) {
        mostrarAlerta("Preencha todos os campos obrigatórios!", "warning");
        desativarSpinner(btn);
        return;
      }

      if (itens.length === 0) {
        mostrarAlerta("Adicione pelo menos um produto!", "warning");
        desativarSpinner(btn);
        return;
      }

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "index.php";

      form.innerHTML = `
        <input type="hidden" name="alterar_pedido" value="1">
        <input type="hidden" name="id_pedido" value="${idPedido}">
        <input type="hidden" name="id_cliente" value="${idCliente}">
        <input type="hidden" name="data_pedido" value="${data}">
        <input type="hidden" name="valor_total" value="${total}">
        <input type="hidden" name="id_forma_pagamento" value="${idFormaPagamento}">
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
    });
  }

  // ----------------------------------------------------------
  // REMOVER PRODUTOS DA LISTA (delegação) / controle botões
  // ----------------------------------------------------------

  const tbody = modal.querySelector(`#tbody_lista_pedido_${numPedido}`);

  // Evento de clique para remover linha (delegação)
  if (tbody) {
    tbody.addEventListener("click", (e) => {
      if (e.target.closest(".btn-remover-linha")) {
        const tr = e.target.closest("tr");
        const linhas = tbody.querySelectorAll("tr");

        if (linhas.length <= 1) {
          mostrarAlerta(
            "Não é possível remover. O pedido deve ter pelo menos um produto!",
            "warning"
          );
          return;
        }

        const valorTotalProd = parseValorMoeda(
          tr.querySelector("td:nth-child(4)").textContent
        );

        valorTotal -= valorTotalProd;
        atualizarValorTotalComFrete();

        tr.remove();

        // Após a remoção, atualizar o estado corretamente
        atualizarEstadoBotoesRemover();
      }
    });
  }

  // Função para atualizar o estado de todos os botões de remover
  function atualizarEstadoBotoesRemover() {
    const linhas = tbody ? tbody.querySelectorAll("tr") : [];

    // Primeiro, reabilita todos
    linhas.forEach((linha) => {
      const btn = linha.querySelector(".btn-remover-linha");
      if (btn) btn.disabled = false;
    });

    // Se sobrou só uma linha, desabilita o botão dela
    if (linhas.length === 1) {
      const btnUnico = linhas[0].querySelector(".btn-remover-linha");
      if (btnUnico) btnUnico.disabled = true;
    }
  }

  // Adiciona botão de remover às linhas já existentes (se não tiver)
  if (tbody) {
    tbody.querySelectorAll("tr").forEach((tr) => {
      if (!tr.querySelector(".btn-remover-linha")) {
        const cellRemove = tr.insertCell(4);
        const btnRemover = document.createElement("button");
        btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-linha";
        btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
        cellRemove.appendChild(btnRemover);
      }
    });
  }

  // Aplica o controle de ativar/desativar no início
  atualizarEstadoBotoesRemover();

  // ----------------------------------------------------------
  // LIGAÇÃO DE LISTENERS EM QUANTIDADES EXISTENTES (linhas pré-carregadas)
  // ----------------------------------------------------------

  // Para cada linha existente, ligar a lógica de alteração de quantidade
  if (tbody) {
    tbody.querySelectorAll("tr").forEach((tr) => {
      const inputQtd = tr.querySelector("input[type='number']");
      const idProduto = tr.dataset.idProduto;
      if (!inputQtd || !idProduto) return;

      // garante dataset valorAnterior
      inputQtd.dataset.valorAnterior = inputQtd.value;

      inputQtd.addEventListener("focus", function () {
        this.dataset.valorAnterior = this.value;
      });

      inputQtd.addEventListener("input", function () {
        const novaQtd = parseInt(this.value);
        const valorAnterior = parseInt(this.dataset.valorAnterior) || 1;
        const valorUnitarioEl = tr.querySelector(`input[name="itens[${idProduto}][valor_unitario]"]`);
        const valorUnitario = valorUnitarioEl ? parseFloat(valorUnitarioEl.value) : 0;
        const cellTotal = tr.querySelector("td:nth-child(4)");

        if (isNaN(novaQtd) || novaQtd <= 0) {
          mostrarAlerta("Quantidade inválida!", "warning");
          this.value = valorAnterior;
          return;
        }

        // verifica estoque no backend
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

            // recalcula linha
            const valorLinhaAnterior = parseValorMoeda(cellTotal.textContent);
            const novoValorLinha = valorUnitario * novaQtd;

            valorTotal = (valorTotal - valorLinhaAnterior) + novoValorLinha;

            cellTotal.textContent = formatarMoeda(novoValorLinha);

            // atualiza hidden inputs correspondentes
            const hiddenTotal = tr.querySelector(`input[name="itens[${idProduto}][valor_total]"]`);
            const hiddenQtd = tr.querySelector(`input[name="itens[${idProduto}][quantidade]"]`);
            if (hiddenTotal) hiddenTotal.value = novoValorLinha.toFixed(2);
            if (hiddenQtd) hiddenQtd.value = novaQtd;

            atualizarValorTotalComFrete();
            this.dataset.valorAnterior = novaQtd;
          })
          .catch(() => {
            mostrarAlerta("Erro ao verificar estoque.", "danger");
            this.value = valorAnterior;
          });
      });
    });
  }

  // ----------------------------------------------------------
  // VERIFICAÇÃO DE LIMITE EM TEMPO REAL (semelhante ao cadastro)
  // ----------------------------------------------------------
  function verificarLimiteCredito() {
    const idCliente = modal.querySelector("input[name='id_cliente']")?.value;
    const total = Number((valorTotal).toFixed(2));
    const btn = modal.querySelector(`#alterar_pedido_${numPedido}`);

    // sem cliente ou pedido zerado → botão desabilitado
    if (!idCliente || total <= 0) {
      if (btn) btn.disabled = true;
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
          if (btn) btn.disabled = false;
          return;
        }
        const json = JSON.parse(resdata);
        if (json.status === false) {
          if (btn) btn.disabled = true;
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
        if (btn) btn.disabled = true;
        mostrarAlerta("Erro ao verificar limite de crédito!", "danger");
      });
  }

  // Garante checagem inicial do botão (caso já haja cliente e itens)
  verificarLimiteCredito();

  // também re-verifica quando forma de pagamento muda (pode afetar regras)
  const formaPagamentoEl = modal.querySelector(`select[name="id_forma_pagamento"]`);
  if (formaPagamentoEl) {
    formaPagamentoEl.addEventListener("change", verificarLimiteCredito);
  }

  // ----------------------------------------------------------
  // Foco/UX: fechar resultados ao clicar fora (limpeza)
  // ----------------------------------------------------------
  document.addEventListener("click", (e) => {
    // se clicar fora dos resultados do cliente/produto, limpa os boxes
    const alvo = e.target;
    if (!modal.contains(alvo)) return; // só dentro da modal
    if (!alvo.closest(`#resultado_busca_cliente_${numPedido}`) && !alvo.closest(`#cliente_pedido_${numPedido}`)) {
      const resCli = modal.querySelector(`#resultado_busca_cliente_${numPedido}`);
      if (resCli) resCli.innerHTML = "";
    }
    if (!alvo.closest(`#resultado_busca_produto_${numPedido}`) && !alvo.closest(`#produto_pedido_${numPedido}`)) {
      const resProd = modal.querySelector(`#resultado_busca_produto_${numPedido}`);
      if (resProd) resProd.innerHTML = "";
    }
  });

  // ----------------------------------------------------------
  // Garantia final: se a modal for re-aberta, recalcula tudo (opcional)
  // ----------------------------------------------------------
  modal.addEventListener("shown.bs.modal", () => {
    // recalcula totais e reativa listeners caso necessário
    valorTotal = calcularTotalProdutos(modal);
    valorFrete = parseValorMoeda(modal.querySelector(`#frete_${numPedido}`)?.value) || 0;
    atualizarValorTotalComFrete();
    atualizarEstadoBotoesRemover();
  });
}
