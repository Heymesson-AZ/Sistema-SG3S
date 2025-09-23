// ======================================================
// SCRIPT PARA CADASTRO DE CLIENTE
// ======================================================
$(document).ready(function () {

  function mostrarMensagem($container, msg, tipo = 'danger') {
    $container.find('.alert-temporaria').remove();
    const alerta = $(`
      <div class="alert alert-${tipo} alert-temporaria"
            style="margin-top:5px; display:none;">
        ${msg}
      </div>
    `);
    $container.append(alerta);
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

  // Máscaras gerais
  $(".cnpj_cliente, .cnpj_cliente_consulta").mask("00.000.000/0000-00", { reverse: true });
  $(".cep").mask("00000-000");
  $(".dinheiro").mask("000.000.000,00", { reverse: true });

  // Atualiza botões e required
  function atualizarTelefones($container) {
    const itens = $container.find('.telefone-item');
    itens.each(function (i) {
      const $btnAdd = $(this).find('.add-telefone');
      const $btnRem = $(this).find('.remover-telefone');

      if (i === 0) {
        $btnAdd.show();
        $btnRem.hide();
        $(this).find('.telefone-tipo, .telefone').attr('required', true);
      } else {
        $btnAdd.hide();
        $btnRem.show();
        $(this).find('.telefone-tipo, .telefone').attr('required', true);
      }
    });
  }

  // Trocar máscara ao mudar tipo
  $(document).on('change', '#telefones-container .telefone-tipo', function () {
    aplicarMascaraPorTipo($(this).closest('.telefone-item'));
  });

  // Adicionar telefone
  $(document).on('click', '#telefones-container .add-telefone', function () {
    const $container = $('#telefones-container');
    const $primeira = $container.find('.telefone-item').first();
    const tipo = $primeira.find('.telefone-tipo').val();
    const numero = $primeira.find('.telefone').val().trim();

    if (!tipo) {
      mostrarMensagem($primeira, 'Selecione o tipo de telefone.');
      return;
    }
    if (!numero) {
      mostrarMensagem($primeira, 'Digite um número para adicionar.');
      return;
    }

    const numeroNormalizado = numero.replace(/\D/g, '');
    const duplicado = $container.find('.telefone').not($primeira.find('.telefone'))
      .toArray()
      .some(i => $(i).val().replace(/\D/g, '') === numeroNormalizado);

    if (duplicado) {
      mostrarMensagem($primeira, 'Número já adicionado.');
      return;
    }

    const index = $container.find('.telefone-item').length;
    const clone = $primeira.clone(true, true);

    clone.find('.telefone-tipo').val(tipo);
    clone.find('.telefone')
      .val(numero)
      .mask(tipo === 'celular' ? '(00) 0 0000-0000' : '(00) 0000-0000');

    clone.find('select, input').each(function () {
      this.name = this.name.replace(/\[\d+\]/, `[${index}]`);
    });

    $container.append(clone);

    $primeira.find('.telefone-tipo').val('');
    $primeira.find('.telefone').val('').unmask();

    atualizarTelefones($container);
  });

  // Remover telefone
  $(document).on('click', '#telefones-container .remover-telefone', function () {
    const $container = $('#telefones-container');
    if ($container.find('.telefone-item').length > 1) {
      $(this).closest('.telefone-item').remove();
      atualizarTelefones($container);
    }
  });

  // Inicializar
  const $container = $('#telefones-container');
  atualizarTelefones($container);
  $container.find('.telefone-item').each(function () {
    aplicarMascaraPorTipo($(this));
  });
});


// ======================================================
// SCRIPT PARA ALTERAÇÃO DE CLIENTE
// ======================================================
$(document).ready(function () {

  function mostrarMensagem($container, msg, tipo = 'danger') {
    $container.find('.alert-temporaria').remove();
    const alerta = $(`
      <div class="alert alert-${tipo} alert-temporaria" 
            style="margin-top:5px; display:none;">
        ${msg}
      </div>
    `);
    $container.append(alerta);
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

  // Atualiza botões e required
  function atualizarTelefones($container) {
    const itens = $container.find('.telefone-item');
    itens.each(function (i) {
      const $btnAdd = $(this).find('.add-telefone');
      const $btnRem = $(this).find('.remover-telefone');

      if (i === 0) {
        $btnAdd.show();
        $btnRem.hide();
        $(this).find('.telefone-tipo, .telefone').attr('required', true);
      } else {
        $btnAdd.hide();
        $btnRem.show();
        $(this).find('.telefone-tipo, .telefone').attr('required', true);
      }
    });
  }

  // Trocar máscara ao mudar tipo
  $(document).on('change', '[id^="telefones-container-"] .telefone-tipo', function () {
    aplicarMascaraPorTipo($(this).closest('.telefone-item'));
  });

  // Adicionar telefone
  $(document).on('click', '[id^="telefones-container-"] .add-telefone', function () {
    const $container = $(this).closest('[id^="telefones-container-"]');
    const $primeira = $container.find('.telefone-item').first();
    const tipo = $primeira.find('.telefone-tipo').val();
    const numero = $primeira.find('.telefone').val().trim();

    if (!tipo) {
      mostrarMensagem($primeira, 'Selecione o tipo de telefone.');
      return;
    }
    if (!numero) {
      mostrarMensagem($primeira, 'Digite um número para adicionar.');
      return;
    }

    const numeroNormalizado = numero.replace(/\D/g, '');
    const duplicado = $container.find('.telefone').not($primeira.find('.telefone'))
      .toArray()
      .some(i => $(i).val().replace(/\D/g, '') === numeroNormalizado);

    if (duplicado) {
      mostrarMensagem($primeira, 'Número já adicionado.');
      return;
    }

    const index = $container.find('.telefone-item').length;
    const clone = $primeira.clone(true, true);

    clone.find('.telefone-tipo').val(tipo);
    clone.find('.telefone')
      .val(numero)
      .mask(tipo === 'celular' ? '(00) 0 0000-0000' : '(00) 0000-0000');

    clone.find('select, input').each(function () {
      this.name = this.name.replace(/\[\d+\]/, `[${index}]`);
    });

    $container.append(clone);

    $primeira.find('.telefone-tipo').val('');
    $primeira.find('.telefone').val('').unmask();

    atualizarTelefones($container);
  });

  // Remover telefone
  $(document).on('click', '[id^="telefones-container-"] .remover-telefone', function () {
    const $container = $(this).closest('[id^="telefones-container-"]');
    if ($container.find('.telefone-item').length > 1) {
      $(this).closest('.telefone-item').remove();
      atualizarTelefones($container);
    }
  });
  // Inicializar todos os containers de alteração
  $('[id^="telefones-container-"]').each(function () {
    atualizarTelefones($(this));
    $(this).find('.telefone-item').each(function () {
      aplicarMascaraPorTipo($(this));
    });
  });
});
