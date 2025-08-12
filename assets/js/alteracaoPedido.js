document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".modal-alterar-pedido").forEach((modal) => {
    inicializarModalAlteracao(modal);
  });
});
// funcao que inicializa/carrega a modal de alteraca
function inicializarModalAlteracao(modal) {
  const numPedido = modal.id.replace("modal_alterar_pedido_", "");

  // ----------------------------------------------------------
  // VARIÁVEIS GLOBAIS DO CONTEXTO DA MODAL
  // ----------------------------------------------------------
  let valorTotal = calcularTotalProdutos(modal);
  let valorFrete =
    parseValorMoeda(modal.querySelector(`#frete_${numPedido}`).value) || 0;
  let produtoSelecionado = null;

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
      parseFloat(
        valor.replace("R$", "").replace(/\./g, "").replace(",", ".")
      ) || 0
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
  }

  /**
   * Habilita spinner em botão
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
    botao.innerHTML = botao.dataset.originalText;
  }

  // ----------------------------------------------------------
  // BUSCAS AJAX
  // ----------------------------------------------------------

  // busca dinamica de cliente
  function buscarCliente(termo) {
    const input = modal.querySelector(`#cliente_pedido_${numPedido}`);
    const resultado = modal.querySelector(
      `#resultado_busca_cliente_${numPedido}`
    );

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
    const resultado = modal.querySelector(
      `#resultado_busca_produto_${numPedido}`
    );

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
  // EVENTOS DE SELEÇÃO (CLIQUES EM ITENS DE BUSCA)
  // ----------------------------------------------------------

  // selecao de clentes com a buscas ajax
  function ativarSelecaoCliente() {
    modal
      .querySelectorAll(`#resultado_busca_cliente_${numPedido} .cliente-item`)
      .forEach((span) => {
        span.addEventListener("click", function () {
          // coletando os dados via dataset e textContent do cliente
          const nome = this.textContent;
          const id = this.dataset.id;
          const input = modal.querySelector(`#cliente_pedido_${numPedido}`);
          input.value = nome;

          const hidden = modal.querySelector(`input[name="id_cliente"]`);
          if (hidden) hidden.value = id;

          modal.querySelector(
            `#resultado_busca_cliente_${numPedido}`
          ).innerHTML = "";
        });
      });
  }
  // selecao de produtos da busca ajax
  function ativarSelecaoProduto() {
    modal
      .querySelectorAll(`#resultado_busca_produto_${numPedido} .produto-item`)
      .forEach((span) => {
        span.addEventListener("click", function () {
          // pegando os valores vida data-set dos dados do produto
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
        });
      });
  }

  // ----------------------------------------------------------
  // FUNÇÕES DE CÁLCULO
  // ----------------------------------------------------------

  /**
   * Calcula o total dos produtos já na tabela
   */
  function calcularTotalProdutos(modal) {
    let total = 0;
    modal
      .querySelectorAll(`#tbody_lista_pedido_${numPedido} tr`)
      .forEach((tr) => {
        const valor = tr.querySelector(
          `input[name="itens[${tr.dataset.idProduto}][valor_unitario]"]`
        );
        const qtd = tr.querySelector(
          `input[name="itens[${tr.dataset.idProduto}][quantidade]"]`
        );
        if (valor && qtd) {
          total += parseFloat(valor.value) * parseFloat(qtd.value);
        }
      });
    return total;
  }

  // ----------------------------------------------------------
  // ADICIONAR PRODUTO À LISTA
  // ----------------------------------------------------------

  function adicionarProduto() {
    const idProduto = modal.querySelector(
      `#id_produto_hidden_${numPedido}`
    )?.value;
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

          tr.querySelector(
            `input[name="itens[${idProduto}][valor_total]"]`
          ).value = valorLinha.toFixed(2);
          tr.querySelector(
            `input[name="itens[${idProduto}][quantidade]"]`
          ).value = novaQtd;

          atualizarValorTotalComFrete();
          this.dataset.valorAnterior = novaQtd;
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
    // atualizaoo estado do botao de remover
    atualizarEstadoBotoesRemover();
  }

  // ----------------------------------------------------------
  // EVENTOS GERAIS DA MODAL
  // ----------------------------------------------------------

  modal
    .querySelector(`#cliente_pedido_${numPedido}`)
    .addEventListener("input", (e) => {
      buscarCliente(e.target.value.trim());
    });

  modal
    .querySelector(`#produto_pedido_${numPedido}`)
    .addEventListener("input", (e) => {
      buscarProduto(e.target.value.trim());
    });

  modal
    .querySelector(`#adicionar_produto_${numPedido}`)
    .addEventListener("click", adicionarProduto);

  modal.querySelector(`#frete_${numPedido}`).addEventListener("input", (e) => {
    let valor = e.target.value
      .replace("R$", "")
      .replace(/\./g, "")
      .replace(",", ".");
    valorFrete = parseFloat(valor) || 0;
    atualizarValorTotalComFrete();
  });

  // ----------------------------------------------------------
  // SALVAR ALTERAÇÃO DE PEDIDO E ENVIO DO FORMULARIO
  // ----------------------------------------------------------

  modal
    .querySelector(`#alterar_pedido_${numPedido}`)
    .addEventListener("click", (e) => {
      e.preventDefault();
      const btn = e.currentTarget;
      ativarSpinner(btn);

      const idCliente = modal.querySelector(`input[name="id_cliente"]`)?.value;
      const idFormaPagamento = modal.querySelector(
        `select[name="id_forma_pagamento"]`
      ).value;
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

  // ----------------------------------------------------------
  // REMOVER PRODUTOS DA LISTA
  // ----------------------------------------------------------

  const tbody = modal.querySelector(`#tbody_lista_pedido_${numPedido}`);

  // Evento de clique para remover linha
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

  // Função para atualizar o estado de todos os botões de remover
  function atualizarEstadoBotoesRemover() {
    const linhas = tbody.querySelectorAll("tr");

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

  // Adiciona botão de remover às linhas já existentes
  tbody.querySelectorAll("tr").forEach((tr) => {
    if (!tr.querySelector(".btn-remover-linha")) {
      const cellRemove = tr.insertCell(4);
      const btnRemover = document.createElement("button");
      btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-linha";
      btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
      cellRemove.appendChild(btnRemover);
    }
  });

  // Aplica o controle de ativar/desativar no início
  atualizarEstadoBotoesRemover();
}
