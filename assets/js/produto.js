$(document).ready(function () {

  // ===========================
  // MÁSCARAS DE CAMPOS (USANDO jQuery Mask)
  // ===========================

  $(".cnpj").mask("00.000.000/0000-00", { reverse: true });
  $(".telefone_celular").mask("(00) 00000-0000");
  $(".telefone_fixo").mask("(00) 0000-0000");

  // Máscara de moeda (R$)
  $(".dinheiro").on("input", function () {
    let valor = this.value.replace(/[^\d]/g, ""); // remove tudo que não for dígito
    if (valor) {
      valor = (parseFloat(valor) / 100)
        .toFixed(2)
        .replace(".", ",");
      this.value = `R$ ${valor}`;
    } else {
      this.value = "";
    }
  });

  // ===========================
  // MÁSCARA FLEXÍVEL PARA DECIMAIS
  // ===========================
  function aplicarMascaraDecimal(selector) {
    $(selector).on("input", function () {
      // Permite apenas números, vírgula e ponto
      this.value = this.value.replace(/[^0-9.,]/g, "");

      // Se tiver mais de um ponto ou vírgula, mantém só o primeiro
      let partes = this.value.split(/[,\.]/);
      if (partes.length > 2) {
        this.value = partes[0] + "." + partes.slice(1).join("");
      }
    });

    $(selector).on("blur", function () {
      let valor = this.value.replace(",", "."); // vírgula -> ponto
      if (valor && !isNaN(valor)) {
        // Mantém até 3 casas decimais
        this.value = parseFloat(valor).toString();
      } else {
        this.value = "";
      }
    });
  }

  // Aplica máscara aos campos pelo NAME
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
    const maxTamanho = 500 * 1024; // 500KB
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

  // Funções auxiliares
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
});
