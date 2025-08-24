$(document).ready(function () {

  // máscara para o campo de limite de margem
  $("#limite_margem").mask("000.00", { reverse: true });

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

  // === Função genérica para busca dinâmica ===
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
  const resultadoFornecedor = document.getElementById(
    "resultado_busca_fornecedor"
  );

  // === Busca de fornecedor ===
  document.querySelectorAll(".fornecedor-input").forEach((inputFornecedor) => {
    const id = inputFornecedor.id.replace("id_fornecedor_produto", "");
    const hiddenFornecedor = document.getElementById("id_fornecedor_hidden" + id);
    const resultadoFornecedor = document.getElementById("resultado_busca_fornecedor" + id);

    function buscarFornecedor(termo) {
      if (!termo) {
        resultadoFornecedor.innerHTML = "";
        return;
      }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `buscar_fornecedor=${encodeURIComponent(termo)}`,
      })
        .then((res) => res.text())
        .then((data) => {
          resultadoFornecedor.innerHTML = data;
          resultadoFornecedor.querySelectorAll(".fornecedor-item").forEach((item) => {
            item.addEventListener("click", function () {
              inputFornecedor.value = this.dataset.nome;
              hiddenFornecedor.value = this.dataset.id;
              resultadoFornecedor.innerHTML = "";
            });
          });
        })
        .catch(() => mostrarAlerta("Erro ao buscar fornecedor."));
    }

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
  });
  
  function buscarFornecedor(termo) {
    if (!termo) {
      resultadoFornecedor.innerHTML = "";
      return;
    }
    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `buscar_fornecedor=${encodeURIComponent(termo)}`,
    })
      .then((res) => res.text())
      .then((data) => {
        resultadoFornecedor.innerHTML = data;
        document
          .querySelectorAll("#resultado_busca_fornecedor .fornecedor-item")
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
});