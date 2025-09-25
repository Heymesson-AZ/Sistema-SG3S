$(document).ready(function () {
  // ===========================
  // MÁSCARAS DE CAMPOS pela classe
  // ===========================
  $(".cnpj").mask("00.000.000/0000-00", { reverse: true });
  // Máscara de moeda (R$) com separador de milhar
  $(".dinheiro").mask("R$ 000.000.000,00", { reverse: true });
  // ===========================
  // MÁSCARA FLEXÍVEL PARA DECIMAIS
  // ===========================
  function aplicarMascaraDecimal(selector) {
    $(selector).on("input", function () {
      this.value = this.value.replace(/[^0-9.,]/g, "");
      let partes = this.value.split(/[,\.]/);
      if (partes.length > 2) {
        this.value = partes[0] + "." + partes.slice(1).join("");
      }
    });

    $(selector).on("blur", function () {
      let valor = this.value.replace(",", ".");
      if (valor && !isNaN(valor)) {
        this.value = parseFloat(valor).toString();
      } else {
        this.value = "";
      }
    });
  }
  aplicarMascaraDecimal("input[name='largura'], input[name='quantidade'], input[name='quantidade_minima']");

  // ===========================
  // VALIDAÇÃO DE CNPJ
  // ===========================
  $(".cnpj").on("blur", function () {
    const cnpj = $(this).val();
    this.setCustomValidity(validarCNPJ(cnpj) ? "" : "CNPJ inválido");
  });

  function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g, "");
    if (cnpj.length !== 14) return false;

    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0, tamanho);
    let digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2) pos = 9;
    }

    let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (resultado !== parseInt(digitos.charAt(0))) return false;

    tamanho += 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2) pos = 9;
    }

    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    return resultado === parseInt(digitos.charAt(1));
  }

  // ===========================
  // VALIDAÇÃO DE IMAGENS
  // ===========================
  $("input[type='file'][accept^='image/']").on("change", function () {
    const file = this.files[0];
    limparErroVisual(this);
    if (!file) return;

    const tiposPermitidos = ["image/jpeg", "image/png", "image/gif"];
    const maxTamanho = 500 * 1024;
    const maxLargura = 1200, maxAltura = 1200;

    if (!tiposPermitidos.includes(file.type)) {
      marcarErro(this, "Somente JPEG, PNG ou GIF são aceitos.");
      this.value = "";
      return;
    }
    if (file.size > maxTamanho) {
      marcarErro(this, "Tamanho máximo permitido é 500KB.");
      this.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        if (img.width > maxLargura || img.height > maxAltura) {
          marcarErro(this, `Imagem deve ter no máximo ${maxLargura}x${maxAltura}px.`);
          this.value = "";
        } else {
          atualizarPreview(this, e.target.result);
        }
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });

  function marcarErro(input, mensagem) {
    input.classList.add("is-invalid");
    input.title = mensagem;
  }
  function limparErroVisual(input) {
    input.classList.remove("is-invalid");
    input.title = "";
  }
  function atualizarPreview(input, base64) {
    let previewDiv, legendaLabel;
    if (input.id === "img_produto") {
      previewDiv = $("#preview_imagem_cadastro")[0];
      legendaLabel = $("#legenda_imagem_cadastro")[0];
    } else {
      const id = input.id.replace("img_produto", "");
      previewDiv = $(`#preview_imagem${id}`)[0];
      legendaLabel = $(`#legenda_imagem${id}`)[0];
    }
    if (previewDiv && legendaLabel) {
      previewDiv.innerHTML = `<img src="${base64}" class="img-thumbnail" style="max-width:80px; height:auto;">`;
      legendaLabel.textContent = "Imagem Selecionada:";
    }
  }

  // ===========================
  // BUSCA DE FORNECEDOR (CADASTRO + ALTERAÇÃO)
  // ===========================
  function inicializarBuscaFornecedor(inputFornecedor, hiddenFornecedor, resultadoFornecedor) {
    function buscarFornecedor(termo) {
      if (!termo) {
        resultadoFornecedor.innerHTML = "";
        return;
      }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `buscar_fornecedor=${encodeURIComponent(termo)}`
      })
        .then(res => res.text())
        .then(data => {
          resultadoFornecedor.innerHTML = data;
          resultadoFornecedor.querySelectorAll(".fornecedor-item").forEach(item => {
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
  }

  // --- Consulta ---
  const inputFornecedorConsulta = document.getElementById("id_fornecedor_produto");
  if (inputFornecedorConsulta) {
    inicializarBuscaFornecedor(
      inputFornecedorConsulta,
      document.getElementById("id_fornecedor_hidden"),
      document.getElementById("resultado_busca_fornecedor")
    );
  }

  // --- Consulta ---
  const inputFornecedorVerificar = document.getElementById("id_fornecedor_produto_verificar");
  if (inputFornecedorVerificar) {
    inicializarBuscaFornecedor(
      inputFornecedorVerificar,
      document.getElementById("id_fornecedor_hidden_verificar"),
      document.getElementById("resultado_busca_fornecedor_verificar")
    );
  }

  // --- Cadastro ---
  const inputFornecedorCadastro = document.getElementById("id_fornecedor_produto_cadastro");
  if (inputFornecedorCadastro) {
    inicializarBuscaFornecedor(
      inputFornecedorCadastro,
      document.getElementById("id_fornecedor_hidden_cadastro"),
      document.getElementById("resultado_busca_fornecedor_cadastro")
    );
  }
  // --- Alteração ---
  document.querySelectorAll(".fornecedor-input").forEach((inputFornecedor) => {
    const id = inputFornecedor.id.replace("id_fornecedor_produto", "");
    inicializarBuscaFornecedor(
      inputFornecedor,
      document.getElementById("id_fornecedor_hidden" + id),
      document.getElementById("resultado_busca_fornecedor" + id)
    );
  });


  // ===========================
  // BUSCA DE COR DO PRODUTO
  // ===========================
  function inicializarBuscaCor(inputCor, hiddenCor, resultadoCor) {
    function buscarCor(termo) {
      if (!termo) {
        resultadoCor.innerHTML = "";
        return;
      }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `cor_produto=${encodeURIComponent(termo)}`
      })
        .then(res => res.text())
        .then(data => {
          resultadoCor.innerHTML = data;
          resultadoCor.querySelectorAll(".cor-item").forEach(item => {
            item.addEventListener("click", function () {
              inputCor.value = this.dataset.nome;
              hiddenCor.value = this.dataset.id;
              resultadoCor.innerHTML = "";
            });
          });
        })
        .catch(() => mostrarAlerta("Erro ao buscar cor."));
    }

    inputCor.addEventListener("input", () => {
      hiddenCor.value = "";
      buscarCor(inputCor.value.trim());
    });

    inputCor.closest("form").addEventListener("submit", function (e) {
      if (inputCor.value.trim() !== "" && !hiddenCor.value) {
        e.preventDefault();
        mostrarAlerta("Por favor, selecione uma cor da lista.");
      }
    });
  }

  // ===========================
  // BUSCA DE TIPO DO PRODUTO
  // ===========================
  function inicializarBuscaTipo(inputTipo, hiddenTipo, resultadoTipo) {
    function buscarTipo(termo) {
      if (!termo) {
        resultadoTipo.innerHTML = "";
        return;
      }
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `tipo_produto=${encodeURIComponent(termo)}`
      })
        .then(res => res.text())
        .then(data => {
          resultadoTipo.innerHTML = data;
          resultadoTipo.querySelectorAll(".tipo-item").forEach(item => {
            item.addEventListener("click", function () {
              inputTipo.value = this.dataset.nome;
              hiddenTipo.value = this.dataset.id;
              resultadoTipo.innerHTML = "";
            });
          });
        })
        .catch(() => mostrarAlerta("Erro ao buscar tipo de produto."));
    }

    inputTipo.addEventListener("input", () => {
      hiddenTipo.value = "";
      buscarTipo(inputTipo.value.trim());
    });

    inputTipo.closest("form").addEventListener("submit", function (e) {
      if (inputTipo.value.trim() !== "" && !hiddenTipo.value) {
        e.preventDefault();
        mostrarAlerta("Por favor, selecione um tipo da lista.");
      }
    });
  }

  // ===========================
  // INICIALIZAÇÃO NA PÁGINA
  // ===========================
  const inputCor = document.getElementById("cor");
  if (inputCor) {
    inicializarBuscaCor(
      inputCor,
      document.getElementById("id_cor_hidden"),
      document.getElementById("resultado_busca_cor")
    );
  }

  const inputTipo = document.getElementById("tipo_produto");
  if (inputTipo) {
    inicializarBuscaTipo(
      inputTipo,
      document.getElementById("id_tipo_hidden"),
      document.getElementById("resultado_busca_tipo")
    );
  }


  const inputCorCadastro = document.getElementById("cor_cadastro");
  if (inputCorCadastro) {
    inicializarBuscaCor(
      inputCorCadastro,
      document.getElementById("id_cor_hidden_cadastro"),
      document.getElementById("resultado_busca_cor_cadastro")
    );
  }

  const inputTipoCadastro = document.getElementById("tipo_produto_cadastro");
  if (inputTipoCadastro) {
    inicializarBuscaTipo(
      inputTipoCadastro,
      document.getElementById("id_tipo_hidden_cadastro"),
      document.getElementById("resultado_busca_tipo_cadastro")
    );
  }

  const inputCorConsulta = document.getElementById("cor_consulta");
  if (inputCorConsulta) {
    inicializarBuscaCor(
      inputCorConsulta,
      document.getElementById("id_cor_hidden_consulta"),
      document.getElementById("resultado_busca_cor_consulta")
    );
  }

  const inputTipoConsulta = document.getElementById("tipo_produto_consulta");
  if (inputTipoConsulta) {
    inicializarBuscaTipo(
      inputTipoConsulta,
      document.getElementById("id_tipo_hidden_consulta"),
      document.getElementById("resultado_busca_tipo_consulta")
    );
  }
});
