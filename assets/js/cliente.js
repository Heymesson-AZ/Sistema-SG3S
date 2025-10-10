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

  // =======================================================================
  // FUNÇÃO ATUALIZAR TELEFONES (AJUSTADA)
  // =======================================================================
  function atualizarTelefones($container) {
    const itens = $container.find('.telefone-item');
    const numItens = itens.length;
    const $primeiraLinha = itens.first();

    // A primeira linha (de entrada) só é obrigatória se for a ÚNICA linha.
    if (numItens <= 1) {
      $primeiraLinha.find('.telefone-tipo, .telefone').prop('required', true);
    } else {
      $primeiraLinha.find('.telefone-tipo, .telefone').prop('required', false);
    }

    // Configura botões e a obrigatoriedade para todas as linhas
    itens.each(function (i) {
      const $linhaAtual = $(this);
      const $btnAdd = $linhaAtual.find('.add-telefone');
      const $btnRem = $linhaAtual.find('.remover-telefone');

      if (i === 0) { // Primeira linha (entrada)
        $btnAdd.show();
        $btnRem.hide();
      } else { // Linhas de telefones já adicionados
        $btnAdd.hide();
        $btnRem.show();
        // Garante que os telefones já adicionados sejam obrigatórios
        $linhaAtual.find('.telefone-tipo, .telefone').prop('required', true);
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

    const index = Date.now(); // Usar timestamp para garantir índice único
    const clone = $primeira.clone(true, true);

    clone.find('.telefone-tipo').val(tipo);
    clone.find('.telefone').val(numero);

    clone.find('select, input').each(function () {
      this.name = this.name.replace(/\[\d+\]/, `[${index}]`);
    });

    $container.append(clone);
    aplicarMascaraPorTipo(clone); // Aplica máscara no clone

    $primeira.find('.telefone-tipo').val('');
    $primeira.find('.telefone').val('').unmask();

    atualizarTelefones($container);
  });

  // Remover telefone
  $(document).on('click', '#telefones-container .remover-telefone', function () {
    $(this).closest('.telefone-item').remove();
    atualizarTelefones($('#telefones-container'));
  });

  // Inicializar
  const $containerCadastro = $('#telefones-container');
  if ($containerCadastro.length) {
    atualizarTelefones($containerCadastro);
    $containerCadastro.find('.telefone-item').each(function () {
      aplicarMascaraPorTipo($(this));
    });
  }
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

  // =======================================================================
  // FUNÇÃO ATUALIZAR TELEFONES (AJUSTADA)
  // =======================================================================
  function atualizarTelefones($container) {
    const itens = $container.find('.telefone-item');
    const numItens = itens.length;
    const $primeiraLinha = itens.first();

    // A primeira linha (de entrada) só é obrigatória se for a ÚNICA linha,
    // ou se já veio preenchida do banco e é a única.
    const primeiroTelefoneValor = $primeiraLinha.find('.telefone').val().trim();
    if (numItens <= 1 && primeiroTelefoneValor === '') {
      $primeiraLinha.find('.telefone-tipo, .telefone').prop('required', true);
    } else {
      $primeiraLinha.find('.telefone-tipo, .telefone').prop('required', false);
    }

    // Configura botões e a obrigatoriedade para todas as linhas
    itens.each(function (i) {
      const $linhaAtual = $(this);
      const $btnAdd = $linhaAtual.find('.add-telefone');
      const $btnRem = $linhaAtual.find('.remover-telefone');

      if (i === 0) { // Primeira linha (entrada)
        $btnAdd.show();
        // Esconde o botão remover se for a única linha E estiver vazia
        if (numItens <= 1 && $linhaAtual.find('.telefone').val().trim() === '') {
          $btnRem.hide();
        } else {
          $btnRem.show();
        }
      } else { // Linhas de telefones já adicionados
        $btnAdd.hide();
        $btnRem.show();
        // Garante que os telefones já adicionados sejam obrigatórios
        $linhaAtual.find('.telefone-tipo, .telefone').prop('required', true);
      }
    });
    // Se houver apenas uma linha preenchida, o primeiro campo torna-se obrigatório
    if (numItens === 1 && $primeiraLinha.find('.telefone').val().trim() !== '') {
      $primeiraLinha.find('.telefone-tipo, .telefone').prop('required', true);
    }
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

    const index = Date.now(); // Usar timestamp para garantir índice único
    const clone = $primeira.clone(true, true);

    clone.find('.telefone-tipo').val(tipo);
    clone.find('.telefone').val(numero);

    clone.find('select, input').each(function () {
      this.name = this.name.replace(/\[\d+\]/, `[${index}]`);
      // Garante que o novo campo não tenha um ID duplicado
      if (this.id) {
        this.id = this.id + '_' + index;
      }
    });

    $container.append(clone);
    aplicarMascaraPorTipo(clone); // Aplica máscara no clone

    $primeira.find('.telefone-tipo').val('');
    $primeira.find('.telefone').val('').unmask();

    atualizarTelefones($container);
  });

  // Remover telefone
  $(document).on('click', '[id^="telefones-container-"] .remover-telefone', function () {
    const $container = $(this).closest('[id^="telefones-container-"]');
    if ($container.find('.telefone-item').length > 1) {
      $(this).closest('.telefone-item').remove();
    } else {
      // Se for o último, apenas limpa os campos
      const $linha = $(this).closest('.telefone-item');
      $linha.find('.telefone-tipo').val('');
      $linha.find('.telefone').val('').unmask();
    }
    atualizarTelefones($container);
  });

  // Inicializar todos os containers de alteração
  $('[id^="telefones-container-"]').each(function () {
    const $container = $(this);
    atualizarTelefones($container);
    $container.find('.telefone-item').each(function () {
      aplicarMascaraPorTipo($(this));
    });
  });
});