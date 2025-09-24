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

    // =======================
    // Atualiza botões e required
    // =======================
    function atualizarTelefones($container) {
        const itens = $container.find('.telefone-item');

        itens.each(function (i) {
            const $linha = $(this);
            const $btnAdd = $linha.find('.add-telefone');
            const $btnRem = $linha.find('.remover-telefone');
            const $tipo = $linha.find('select.telefone-tipo');
            const $numero = $linha.find('input.telefone');

            if (i === 0) {
                $btnAdd.show();
                $btnRem.hide();

                // required só se primeira linha estiver realmente vazia
                if ($tipo.val() || $numero.val()) {
                    $tipo.removeAttr('required');
                    $numero.removeAttr('required');
                } else {
                    $tipo.attr('required', true);
                    $numero.attr('required', true);
                }
            } else {
                $btnAdd.hide();
                $btnRem.show();
                $tipo.attr('required', true);
                $numero.attr('required', true);
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

        const index = $container.find('.telefone-item').length;
        const clone = $primeira.clone(true, true);

        clone.find('select.telefone-tipo').val(tipo);
        clone.find('input.telefone')
            .val(numero)
            .mask(tipo === 'celular' ? '(00) 0 0000-0000' : '(00) 0000-0000');

        clone.find('select, input').each(function () {
            this.name = this.name.replace(/\[\d+\]/, `[${index}]`);
        });

        $container.append(clone);

        // Limpa a primeira linha para próximo cadastro
        $primeira.find('select.telefone-tipo').val('');
        $primeira.find('input.telefone').val('').unmask();

        atualizarTelefones($container);
    });

    // =======================
    // Remover telefone
    // =======================
    $(document).on('click', '#telefones-container-fornecedor .remover-telefone', function () {
        const $container = $('#telefones-container-fornecedor');
        if ($container.find('.telefone-item').length > 1) {
            $(this).closest('.telefone-item').remove();
            atualizarTelefones($container);
        }
    });

    // =======================
    // Inicializar
    // =======================
    const $container = $('#telefones-container-fornecedor');
    atualizarTelefones($container);
    $container.find('.telefone-item').each(function () {
        aplicarMascaraPorTipo($(this));
    });

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

    function atualizarTelefones($container) {
        const itens = $container.find('.telefone-item');
        itens.each(function (i) {
            const $btnAdd = $(this).find('.add-telefone');
            const $btnRem = $(this).find('.remover-telefone');

            if (i === 0) {
                $btnAdd.show();
                $btnRem.hide();
                $(this).find('.telefone-tipo, .telefone').attr('required');
            } else {
                $btnAdd.hide();
                $btnRem.show();
                $(this).find('.telefone-tipo, .telefone').attr('required', true);
            }
        });
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

        // Limpar a primeira linha sempre que adicionar
        $primeira.find('.telefone-tipo').val('');
        $primeira.find('.telefone').val('').unmask();

        atualizarTelefones($container);
    });

    // Remover telefone
    $(document).on('click', '[id^="telefones-container-fornecedor-"] .remover-telefone', function () {
        const $container = $(this).closest('[id^="telefones-container-fornecedor-"]');
        if ($container.find('.telefone-item').length > 1) {
            $(this).closest('.telefone-item').remove();
            atualizarTelefones($container);
        }
    });

    // Inicializar todos os containers de alteração
    $('[id^="telefones-container-fornecedor-"]').each(function () {
        atualizarTelefones($(this));
        $(this).find('.telefone-item').each(function () {
            aplicarMascaraPorTipo($(this));
        });
    });
});
