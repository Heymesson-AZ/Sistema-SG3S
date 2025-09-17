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
    // Define o estilo do alerta
    Object.assign(alerta.style, {
      position: "fixed",
      top: "20px",
      right: "20px",
      zIndex: 1055,
    });
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), duracao);
  }
  // funcao de formatar moeda
  function formatarMoeda(valor) {
    return "R$ " + valor.toFixed(2).replace(".", ",");
  }

  function atualizarValorTotalComFrete() {
    const total = valorTotal + valorFrete;
    document.getElementById("valor_total").value = formatarMoeda(total);
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
    document.getElementById("data").value = new Date()
      .toISOString()
      .split("T")[0];
    valorTotal = 0;
    valorFrete = 0;
  }

  // ===========================
  // FUNÇÕES DE BUSCA AJAX
  // ===========================
  function buscarCliente(termo) {
    const input = document.getElementById("cliente_pedido");
    const resultado = document.getElementById("resultado_busca_cliente");
    // Limpa o resultado se o campo estiver vazio
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
        ativarSelecaoCliente();
      })
      .catch(() => {
        mostrarAlerta("Erro ao buscar cliente.");
        desativarSpinner(input);
      });
  }

  function buscarProduto(termo) {
    const input = document.getElementById("produto_pedido");
    const resultado = document.getElementById("resultado_busca_produto");

    // Limpa o resultado se o campo estiver vazio
    if (!termo) {
      resultado.innerHTML = "";
      return;
    }
    // Ativa o spinner no input
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
        ativarSelecaoProduto(); // aplica eventos aos resultados da busca
      })
      .catch(() => {
        mostrarAlerta("Erro ao buscar produto.");
        desativarSpinner(input);
      });
  }

  // ===========================
  // EVENTOS DE SELEÇÃO
  // ===========================
  function ativarSelecaoCliente() {
    document
      .querySelectorAll("#resultado_busca_cliente .cliente-item")
      .forEach((span) => {
        span.addEventListener("click", function () {
          const nome = this.textContent;
          const id = this.dataset.id;
          const input = document.getElementById("cliente_pedido");
          input.value = nome;

          let hidden = document.getElementById("id_cliente_hidden");
          if (!hidden) {
            hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.id = "id_cliente_hidden";
            hidden.name = "id_cliente";
            input.parentElement.appendChild(hidden);
          }
          hidden.value = id;
          document.getElementById("resultado_busca_cliente").innerHTML = "";
        });
      });
  }

  function ativarSelecaoProduto() {
    document
      .querySelectorAll("#resultado_busca_produto .produto-item")
      .forEach((span) => {
        span.addEventListener("click", function () {
          const input = document.getElementById("produto_pedido");
          const id = this.dataset.id;
          const nome = this.dataset.nome;
          const cor = this.dataset.cor;
          const largura = this.dataset.largura;
          const valor = this.dataset.valorvenda;
          const qtd = this.dataset.quantidade;

          input.value = `${nome} - ${cor} - Largura: ${largura}cm`;

          let hidden = document.getElementById("id_produto_hidden");
          if (!hidden) {
            hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.id = "id_produto_hidden";
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

  // ===========================
  // EVENTOS
  // ===========================
  document.getElementById("cliente_pedido").addEventListener("input", (e) => {
    const termo = e.target.value.trim();
    buscarCliente(termo);
  });

  document.getElementById("produto_pedido").addEventListener("input", (e) => {
    const termo = e.target.value.trim();
    buscarProduto(termo);
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

  document
    .getElementById("adicionar_produto")
    .addEventListener("click", function () {
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

      // dentro do bloco onde cria inputQtd:
      inputQtd.addEventListener("focus", function () {
        // salva o valor original ao entrar no input
        this.dataset.valorAnterior = this.value;
      });
      inputQtd.addEventListener("input", function () {
        const input = this;
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
              input.value = valorAnterior;
              return;
            }
            // se válido, atualiza total
            valorTotal -= valorLinha;
            valorLinha = valorUnitario * novaQtd;
            cellTotal.textContent = formatarMoeda(valorLinha);
            valorTotal += valorLinha;
            hiddenTotal.value = valorLinha.toFixed(2);
            atualizarValorTotalComFrete();

            // salva o novo valor como anterior
            input.dataset.valorAnterior = novaQtd;
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

  document.getElementById("frete").addEventListener("input", (e) => {
    let valor = e.target.value
      .replace("R$", "")
      .replace(/\./g, "")
      .replace(",", ".");
    valorFrete = parseFloat(valor) || 0;
    atualizarValorTotalComFrete();
  });

  document
    .getElementById("limpar_pedido")
    .addEventListener("click", limparCamposPedido);

    document
    .getElementById("salvar_pedido")
    .addEventListener("click", function (e) {
      e.preventDefault();
      const idCliente = document.getElementById("id_cliente_hidden")?.value;
      const status = "Pendente";
      const idPagamento = document.querySelector(
        "select[name='id_forma_pagamento']"
      ).value;

      if (!idCliente || !status || !idPagamento) {
        return mostrarAlerta("Preencha todos os campos obrigatórios!", "warning");
      }

      const origem = document.getElementById("origem").value;
      const data = document.getElementById("data").value;
      const frete = valorFrete.toFixed(2);
      const total = (valorTotal + valorFrete).toFixed(2);

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
      // ============================
      // VERIFICAÇÃO AJAX DO LIMITE
      // ============================
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `verificar_limite=1&id_cliente=${idCliente}&valor_total=${total}`,
      })
      .then(res => res.text())
      .then(resdata => {
        // Se o retorno estiver vazio, limite aceito
        if (!resdata.trim()) {
          // envia o form
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
          return;
        }
        // Se houver retorno, significa limite excedido (o controller envia JSON)
        const json = JSON.parse(data);
        if (json.status === false) {
          mostrarAlerta(
            `⚠️ Limite de crédito excedido!<br>
              <strong>Limite:</strong> R$ ${parseFloat(json.limite_credito).toFixed(2).replace(".", ",")}<br>
              <strong>Pedido:</strong> R$ ${parseFloat(total).toFixed(2).replace(".", ",")}<br>
              <strong>Excedente:</strong> <span style="color:#dc3545; font-weight:bold;">
              R$ ${(parseFloat(total) - parseFloat(json.limite_credito)).toFixed(2).replace(".", ",")}
            </span>`,
            "danger",
            7000
          );
        }
      })
      .catch(() => {
        mostrarAlerta("Erro ao verificar limite de crédito!", "danger");
      });
    });
});
