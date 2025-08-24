$(document).ready(function () {
  $(".cnpj_cliente").mask("00.000.000/0000-00", { reverse: true });
  $(".telefone_celular").mask("(00) 00000-0000");
  $(".telefone_fixo").mask("(00) 0000-0000");
  $(".cep").mask("00000-000");
  $(".cnpj_cliente_consulta").mask("00.000.000/0000-00", { reverse: true });
  // Máscara de moeda para campos dinheiro (custo, valor, etc.)
  $(".dinheiro").maskMoney({
    prefix: "R$ ",
    allowNegative: false,
    thousands: ".",
    decimal: ",",
    affixesStay: true
  });

  $(".cnpj_cliente").on("blur", function () {
    const cnpj = $(this).val();
    if (!validarCNPJ(cnpj)) {
      this.setCustomValidity("CNPJ inválido");
    } else {
      this.setCustomValidity("");
    }
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
});

document.addEventListener("DOMContentLoaded", function () {
  var myModal = new bootstrap.Modal(document.getElementById("modal_cliente"));
  myModal.show();
});
