$(document).ready(function () {

  function mostrarMensagem(msg, tipo = 'erro') {
    $('#mensagem-telefone').remove();
    const alerta = $(`<div id="mensagem-telefone"
                          class="alert alert-${tipo}"
                          style="margin-top:5px; display:none;">
                        ${msg}
                      </div>`);
    $('.telefone-item:first').append(alerta);
    alerta.fadeIn(300);
    setTimeout(() => alerta.fadeOut(300, () => alerta.remove()), 3000);
  }

  function aplicarMascaraPorTipo($linha) {
    const $input = $linha.find('.telefone');
    const tipo = $linha.find('.telefone-tipo').val();

    $input.unmask().off('.mask');

    if (tipo === 'celular') {
      $input.mask('(00) 0 0000-0000');
    } else if (tipo === 'fixo' || tipo === 'comercial') {
      $input.mask('(00) 0000-0000');
    } else {
      $input.unmask();
    }
  }

  $(".cnpj_cliente").mask("00.000.000/0000-00", { reverse: true });
  $(".cep").mask("00000-000");
  $(".cnpj_cliente_consulta").mask("00.000.000/0000-00", { reverse: true });
  $(".dinheiro").mask("000.000.000,00", { reverse: true });

  const container = $('#telefones-container');

  container.on('change', '.telefone-tipo', function () {
    aplicarMascaraPorTipo($(this).closest('.telefone-item'));
  });

  function atualizarBotoes() {
    const itens = container.find('.telefone-item');
    itens.each(function (i) {
      const $btnAdd = $(this).find('.add-telefone');
      const $btnRem = $(this).find('.remover-telefone');
      if (i === 0) {
        $btnAdd.show();
        $btnRem.hide();
      } else {
        $btnAdd.hide();
        $btnRem.show();
      }
    });
  }

  function atualizarRequired() {
    const itens = container.find('.telefone-item');
    const temMaisDeUm = itens.length > 1;
    const $first = itens.first();
    const $firstSelect = $first.find('.telefone-tipo');
    const $firstInput = $first.find('.telefone-numero');

    if (temMaisDeUm) {
      $firstSelect.removeAttr('required');
      $firstInput.removeAttr('required');
    } else {
      $firstSelect.attr('required', true);
      $firstInput.attr('required', true);
    }
  }

  // ========== Adicionar novo telefone ==========
  container.on('click', '.add-telefone', function () {
    const primeira = container.find('.telefone-item').first();
    const selectTipo = primeira.find('.telefone-tipo');
    const inputNumero = primeira.find('.telefone');

    const tipo = selectTipo.val();
    const numero = inputNumero.val().trim();

    if (!tipo) {
      mostrarMensagem('Selecione o tipo de telefone.', 'danger');
      return;
    }
    if (!numero) {
      mostrarMensagem('Digite um número para adicionar.', 'danger');
      return;
    }

    // Evita duplicados
    const numeroNormalizado = numero.replace(/\D/g, '');
    const duplicado = container.find('.telefone').not(inputNumero)
      .toArray()
      .some(i => $(i).val().replace(/\D/g, '') === numeroNormalizado);
    if (duplicado) {
      mostrarMensagem('Número já adicionado.', 'erro');
      return;
    }

    const index = container.find('.telefone-item').length;
    const clone = primeira.clone(true, true);

    // === Mostra o número e o tipo selecionados no clone ===
    clone.find('.telefone').val(numero);
    clone.find('.telefone-tipo').val(tipo);

    // Ajusta name para array
    clone.find('select, input').each(function () {
      this.name = this.name.replace(/\[\d+\]/, `[${index}]`);
    });

    container.append(clone);

    aplicarMascaraPorTipo(clone);

    // === Limpa os campos da primeira linha ===
    selectTipo.val('');
    inputNumero.val('');
    inputNumero.unmask(); // remove máscara da linha inicial

    atualizarBotoes();
    atualizarRequired();
  });

  // ========== Remover telefone ==========
  container.on('click', '.remover-telefone', function () {
    if (container.find('.telefone-item').length > 1) {
      $(this).closest('.telefone-item').remove();
      atualizarBotoes();
      atualizarRequired();
    }
  });

  atualizarBotoes();
  atualizarRequired();
});


document.addEventListener("DOMContentLoaded", function () {
  const myModal = new bootstrap.Modal(document.getElementById("modal_cliente"));
  myModal.show();
});