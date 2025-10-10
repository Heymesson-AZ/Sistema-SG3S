// ======================================================
// SCRIPT PARA CADASTRO DE FORNECEDOR
// ======================================================
$(document).ready(function () {

    // =======================
    // Função para mostrar mensagem
    // =======================
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

    // =======================
    // Aplica máscara de telefone conforme tipo
    // =======================
    function aplicarMascaraPorTipo($linha) {
        const $input = $linha.find('input.telefone');
        const tipo = $linha.find('select.telefone-tipo').val();

        $input.unmask().off('.mask');

        if (tipo === 'celular') {
            $input.mask('(00) 0 0000-0000');
        } else if (tipo === 'fixo' || tipo === 'comercial') {
            $input.mask('(00) 0000-0000');
        }
    }

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

    // =======================
    // Troca máscara ao mudar tipo
    // =======================
    $(document).on('change', '#telefones-container-fornecedor .telefone-tipo', function () {
        aplicarMascaraPorTipo($(this).closest('.telefone-item'));
    });

    // =======================
    // Adicionar telefone
    // =======================
    $(document).on('click', '#telefones-container-fornecedor .add-telefone', function () {
        const $container = $('#telefones-container-fornecedor');
        const $primeira = $container.find('.telefone-item').first();
        const tipo = $primeira.find('select.telefone-tipo').val();
        const numero = $primeira.find('input.telefone').val().trim();

        if (!tipo) {
            mostrarMensagem($primeira, 'Selecione o tipo de telefone.');
            return;
        }
        if (!numero) {
            mostrarMensagem($primeira, 'Digite um número para adicionar.');
            return;
        }

        const numeroNormalizado = numero.replace(/\D/g, '');
        const duplicado = $container.find('input.telefone').not($primeira.find('input.telefone'))
            .toArray()
            .some(i => $(i).val().replace(/\D/g, '') === numeroNormalizado);

        if (duplicado) {
            mostrarMensagem($primeira, 'Número já adicionado.');
            return;
        }

        const index = Date.now(); // Usar timestamp para garantir índice único
        const clone = $primeira.clone(true, true);

        clone.find('select.telefone-tipo').val(tipo);
        clone.find('input.telefone').val(numero);

        clone.find('select, input').each(function () {
            this.name = this.name.replace(/\[\d+\]/, `[${index}]`);
        });

        $container.append(clone);
        aplicarMascaraPorTipo(clone);

        $primeira.find('select.telefone-tipo').val('');
        $primeira.find('input.telefone').val('').unmask();

        atualizarTelefones($container);
    });

    // =======================
    // Remover telefone
    // =======================
    $(document).on('click', '#telefones-container-fornecedor .remover-telefone', function () {
        const $container = $('#telefones-container-fornecedor');
        // Só remove se houver mais de um item (para não remover a linha de entrada)
        if ($container.find('.telefone-item').length > 1) {
            $(this).closest('.telefone-item').remove();
            atualizarTelefones($container);
        }
    });

    // =======================
    // Inicializar
    // =======================
    const $containerCadastro = $('#telefones-container-fornecedor');
    if ($containerCadastro.length) {
        atualizarTelefones($containerCadastro);
        $containerCadastro.find('.telefone-item').each(function () {
            aplicarMascaraPorTipo($(this));
        });
    }
});

// ======================================================
// SCRIPT PARA ALTERAÇÃO DE FORNECEDOR
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

        // A primeira linha (de entrada) só é obrigatória se for a ÚNICA linha e estiver VAZIA.
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

            if (i === 0) { // Primeira linha
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
                $linhaAtual.find('.telefone-tipo, .telefone').prop('required', true);
            }
        });

        // Caso especial: se houver apenas uma linha preenchida (vinda do DB), ela deve ser obrigatória
        if (numItens === 1 && $primeiraLinha.find('.telefone').val().trim() !== '') {
            $primeiraLinha.find('.telefone-tipo, .telefone').prop('required', true);
        }
    }

    // Trocar máscara ao mudar tipo
    $(document).on('change', '[id^="telefones-container-fornecedor-"] .telefone-tipo', function () {
        aplicarMascaraPorTipo($(this).closest('.telefone-item'));
    });

    // Adicionar telefone
    $(document).on('click', '[id^="telefones-container-fornecedor-"] .add-telefone', function () {
        const $container = $(this).closest('[id^="telefones-container-fornecedor-"]');
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

        const index = Date.now();
        const clone = $primeira.clone(true, true);

        clone.find('.telefone-tipo').val(tipo);
        clone.find('.telefone').val(numero);

        clone.find('select, input').each(function () {
            this.name = this.name.replace(/\[\d+\]/, `[${index}]`);
            if (this.id) {
                this.id = this.id + '_' + index;
            }
        });

        $container.append(clone);
        aplicarMascaraPorTipo(clone);

        $primeira.find('.telefone-tipo').val('');
        $primeira.find('.telefone').val('').unmask();

        atualizarTelefones($container);
    });

    // Remover telefone
    $(document).on('click', '[id^="telefones-container-fornecedor-"] .remover-telefone', function () {
        const $container = $(this).closest('[id^="telefones-container-fornecedor-"]');
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
    $('[id^="telefones-container-fornecedor-"]').each(function () {
        const $container = $(this);
        atualizarTelefones($container);
        $container.find('.telefone-item').each(function () {
            aplicarMascaraPorTipo($(this));
        });
    });
});