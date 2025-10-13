$(document).ready(function () {
  // Máscara para o campo de limite/margem
  $("#limite_margem").mask("000.00", { reverse: true });

  // Função para exibir alertas na tela
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

  // === Função genérica para busca dinâmica de PRODUTO ===
  function configurarBuscaProduto({
    inputId,
    hiddenId,
    resultadoId,
    campoPost = "produto_custo",
    alertaMensagem = "Por favor, selecione um produto da lista.",
  }) {
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const resultado = document.getElementById(resultadoId);

    function buscarProduto(termo) {
      if (!termo) {
        resultado.innerHTML = "";
        return;
      }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `${campoPost}=${encodeURIComponent(termo)}`,
      })
        .then((res) => res.text())
        .then((data) => {
          resultado.innerHTML = data;
          ativarSelecao();
        })
        .catch(() => mostrarAlerta("Erro ao buscar produto."));
    }

    function ativarSelecao() {
      resultado.querySelectorAll(".produto-item").forEach((item) => {
        item.addEventListener("click", function () {
          input.value = this.dataset.nome;
          hidden.value = this.dataset.id;
          resultado.innerHTML = "";
        });
      });
    }

    if (input) {
      input.addEventListener("input", () => {
        hidden.value = "";
        buscarProduto(input.value.trim());
      });
      input.closest("form").addEventListener("submit", function (e) {
        if (input.value.trim() !== "" && !hidden.value) {
          e.preventDefault();
          mostrarAlerta(alertaMensagem);
        }
      });
    }
  }

  // === Busca de produto (Custo Total) ===
  configurarBuscaProduto({
    inputId: "produto_custo",
    hiddenId: "id_produto_hidden",
    resultadoId: "resultado_busca_produto",
  });

  // === Busca de produto (Margem Baixa) ===
  configurarBuscaProduto({
    inputId: "produto_margem",
    hiddenId: "id_produto_margem_hidden",
    resultadoId: "resultado_busca_produto_margem",
    campoPost: "produto_margem",
    alertaMensagem: "Por favor, selecione um produto da lista (margem).",
  });

  // === Busca de produto (Variação de Vendas por Produto) ===
  configurarBuscaProduto({
    inputId: "produto_venda",
    hiddenId: "id_produto_hidden_venda",
    resultadoId: "resultado_busca_produto_venda",
    campoPost: "produto_venda",
    alertaMensagem: "Por favor, selecione um produto da lista (venda).",
  });

  // === Busca de fornecedor ===
  const inputFornecedor = document.getElementById("id_fornecedor_produto");
  const hiddenFornecedor = document.getElementById("id_fornecedor_hidden");
  const resultadoFornecedor = document.getElementById("resultado_busca_fornecedor");

  function buscarFornecedor(termo) {
    if (!termo) {
      if (resultadoFornecedor) resultadoFornecedor.innerHTML = "";
      return;
    }
    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `buscar_fornecedor=${encodeURIComponent(termo)}`,
    })
      .then((res) => res.text())
      .then((data) => {
        if (resultadoFornecedor) resultadoFornecedor.innerHTML = data;
        document.querySelectorAll("#resultado_busca_fornecedor .fornecedor-item")
          .forEach((item) => {
            item.addEventListener("click", function () {
              inputFornecedor.value = this.dataset.nome;
              hiddenFornecedor.value = this.dataset.id;
              resultadoFornecedor.innerHTML = "";
            });
          });
      })
      .catch(() => mostrarAlerta("Erro ao buscar fornecedor."));
  }

  if (inputFornecedor) {
    inputFornecedor.addEventListener("input", () => {
      hiddenFornecedor.value = "";
      buscarFornecedor(inputFornecedor.value.trim());
    });
    inputFornecedor.closest("form").addEventListener("submit", function (e) {
      if (inputFornecedor.value.trim() !== "" && !hiddenFornecedor.value) {
        e.preventDefault();
        mostrarAlerta("Por favor, selecione um fornecedor da lista.");
      }
    });
  }

  // ==========================================================
  // NOVA SEÇÃO: BUSCA DE CLIENTE NA MODAL DE CONSULTA DE PEDIDOS
  // ==========================================================
  let timeoutCliente = null;
  const DEBOUNCE_DELAY = 500; // Atraso para não sobrecarregar o servidor

  const inputClienteConsulta = document.getElementById("cliente_pedido_consulta");
  const resultadoClienteConsulta = document.getElementById("resultado_busca_cliente_consulta");
  const formConsulta = document.getElementById("formulario_consulta_pedido");

  function buscarCliente(termo) {
    if (!termo) {
      if (resultadoClienteConsulta) resultadoClienteConsulta.innerHTML = "";
      return;
    }
    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `cliente_pedido=${encodeURIComponent(termo)}`, // Parâmetro que o backend espera
    })
      .then(res => res.text())
      .then(data => {
        if (resultadoClienteConsulta) resultadoClienteConsulta.innerHTML = data;
      })
      .catch(() => mostrarAlerta("Erro ao buscar cliente."));
  }

  // Listener para o campo de digitação
  if (inputClienteConsulta) {
    inputClienteConsulta.addEventListener("input", (e) => {
      clearTimeout(timeoutCliente);
      const termo = e.target.value.trim();

      // Remove o campo hidden se o usuário apagar o texto
      const hiddenInput = document.getElementById("id_cliente_consulta_hidden");
      if (!termo && hiddenInput) {
        hiddenInput.remove();
      }

      timeoutCliente = setTimeout(() => buscarCliente(termo), DEBOUNCE_DELAY);
    });
  }

  // Listener para a lista de resultados
  if (resultadoClienteConsulta) {
    resultadoClienteConsulta.addEventListener("click", function (e) {
      const clienteItem = e.target.closest(".cliente-item");
      if (!clienteItem) return;

      // Preenche o campo visível com o nome
      inputClienteConsulta.value = clienteItem.textContent;

      // Cria ou atualiza um campo hidden com o ID do cliente
      let hiddenIdCliente = document.getElementById("id_cliente_consulta_hidden");
      if (!hiddenIdCliente) {
        hiddenIdCliente = document.createElement("input");
        hiddenIdCliente.type = "hidden";
        hiddenIdCliente.id = "id_cliente_consulta_hidden";
        hiddenIdCliente.name = "id_cliente"; // Nome que será enviado pelo formulário
        if (formConsulta) {
          formConsulta.appendChild(hiddenIdCliente);
        }
      }
      hiddenIdCliente.value = clienteItem.dataset.id; // Pega o ID do atributo data-id

      // Limpa os resultados
      this.innerHTML = "";
    });
  }

  // Validação opcional para o formulário de consulta
  if (formConsulta) {
    formConsulta.addEventListener("submit", function (e) {
      const hiddenInput = document.getElementById("id_cliente_consulta_hidden");
      // Se o campo de texto tem algo digitado, mas o campo hidden não foi criado/preenchido, impede o envio
      if (inputClienteConsulta.value.trim() !== "" && (!hiddenInput || !hiddenInput.value)) {
        e.preventDefault();
        mostrarAlerta("Por favor, selecione um cliente válido da lista de sugestões.");
      }
    });
  }
});