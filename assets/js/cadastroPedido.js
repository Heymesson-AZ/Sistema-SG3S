document.addEventListener("DOMContentLoaded", function () {
    // ===========================
    // VARIÁVEIS GLOBAIS
    // ===========================
    let valorTotal = 0;
    let valorFrete = 0;
    let produtoSelecionado = null;

    // ===========================
    // SELETORES DO DOM
    // ===========================
    const inputCliente = document.getElementById("cliente_pedido");
    const inputProduto = document.getElementById("produto_pedido");
    const inputQuantidade = document.getElementById("quantidade");
    const resultadoCliente = document.getElementById("resultado_busca_cliente");
    const resultadoProduto = document.getElementById("resultado_busca_produto");
    const tbody = document.getElementById("tbody_lista_pedido");
    const btnSalvar = document.getElementById("salvar_pedido");
    const btnAdicionar = document.getElementById("adicionar_produto");
    const freteEl = document.getElementById("frete");
    const selectPagamento = document.querySelector("select[name='id_forma_pagamento']");

    // ===========================
    // FUNÇÕES UTILITÁRIAS
    // ===========================
    function mostrarAlerta(mensagem, tipo = "danger", duracao = 3000) {
        const alerta = document.createElement("div");
        alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow`;
        alerta.innerHTML = `${mensagem} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>`;
        Object.assign(alerta.style, {
            position: "fixed", top: "20px", right: "20px", zIndex: 1055,
        });
        document.body.appendChild(alerta);
        setTimeout(() => alerta.remove(), duracao);
    }

    function formatarMoeda(valor) {
        return "R$ " + Number(valor).toFixed(2).replace(".", ",");
    }

    function ativarSpinner(botaoOuInput) {
        try {
            botaoOuInput.disabled = true;
            botaoOuInput.dataset.originalText = botaoOuInput.innerHTML ?? botaoOuInput.value ?? "";
            if (botaoOuInput.tagName === "BUTTON") {
                botaoOuInput.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Buscando...`;
            } else {
                botaoOuInput.value = "";
            }
        } catch (e) { /* silent */ }
    }

    function desativarSpinner(botaoOuInput) {
        try {
            botaoOuInput.disabled = false;
            if (botaoOuInput.tagName === "BUTTON") {
                botaoOuInput.innerHTML = botaoOuInput.dataset.originalText || botaoOuInput.innerHTML;
            } else {
                botaoOuInput.value = botaoOuInput.dataset.originalText || botaoOuInput.value;
            }
        } catch (e) { /* silent */ }
    }

    // ===========================
    // ATUALIZAÇÔES DE TOTAIS E BOTÕES
    // ===========================
    function atualizarValorTotalComFrete() {
        const total = valorTotal + valorFrete;
        const totalEl = document.getElementById("valor_total");
        if (totalEl) totalEl.value = formatarMoeda(total);
        verificarLimiteCredito();
        // A chamada para verificarBotaoSalvar() está dentro de verificarLimiteCredito()
    }

    function recalcularTotaisAPartirDaTabela() {
        valorTotal = 0;
        if (!tbody) return;
        tbody.querySelectorAll("tr").forEach(tr => {
            const hiddenTotal = tr.querySelector("input[name$='[valor_total]']");
            if (hiddenTotal) {
                valorTotal += parseFloat(hiddenTotal.value) || 0;
            }
        });
        atualizarValorTotalComFrete();
    }

    function verificarBotaoSalvar() {
        if (!btnSalvar) return;
        const idCliente = document.getElementById("id_cliente_hidden")?.value;
        const idPagamento = selectPagamento?.value;
        const possuiProdutos = (tbody?.querySelectorAll("tr").length || 0) > 0;

        // Habilita o botão somente se todas as condições forem verdadeiras
        btnSalvar.disabled = !(idCliente && idPagamento && possuiProdutos);
    }

    // ===========================
    // VERIFICAÇÃO DE LIMITE EM TEMPO REAL
    // ===========================
    function verificarLimiteCredito() {
        const idCliente = document.getElementById("id_cliente_hidden")?.value;
        const total = Number(valorTotal.toFixed(2));
        if (!idCliente || total <= 0) {
            verificarBotaoSalvar();
            return;
        }
        fetch("index.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `verificar_limite=1&id_cliente=${idCliente}&valor_total=${total}`,
        })
            .then(res => res.text())
            .then(resdata => {
                let limiteExcedido = false;
                if (resdata.trim()) {
                    try {
                        const json = JSON.parse(resdata);
                        if (json.status === false) {
                            limiteExcedido = true;
                            if (btnSalvar) btnSalvar.disabled = true;
                            mostrarAlerta(`⚠️ Limite de crédito excedido!<br><strong>Limite:</strong> R$ ${parseFloat(json.limite_credito).toFixed(2).replace(".", ",")}<br><strong>Pedido:</strong> R$ ${total.toFixed(2).replace(".", ",")}`, "danger", 6000);
                        }
                    } catch (e) { /* Ignora erro de JSON */ }
                }
                if (!limiteExcedido) {
                    verificarBotaoSalvar();
                }
            }).catch(() => {
                if (btnSalvar) btnSalvar.disabled = true;
                mostrarAlerta("Erro ao verificar limite de crédito!", "danger");
            });
    }

    // ===========================================
    // FUNÇÕES DE BUSCA AJAX (COM DEBOUNCE)
    // ===========================================
    let timeoutCliente = null;
    let timeoutProduto = null;
    const DEBOUNCE_DELAY = 500;

    if (inputCliente) {
        inputCliente.addEventListener("input", (e) => {
            clearTimeout(timeoutCliente);
            const termo = e.target.value.trim();
            // Se limpar o campo, remove o ID do cliente
            if (!termo) {
                document.getElementById("id_cliente_hidden")?.remove();
                verificarLimiteCredito();
            }
            timeoutCliente = setTimeout(() => buscarCliente(termo), DEBOUNCE_DELAY);
        });
    }

    if (inputProduto) {
        inputProduto.addEventListener("input", (e) => {
            clearTimeout(timeoutProduto);
            const termo = e.target.value.trim();
            timeoutProduto = setTimeout(() => buscarProduto(termo), DEBOUNCE_DELAY);
        });
    }

    function buscarCliente(termo) {
        if (!termo) {
            if (resultadoCliente) resultadoCliente.innerHTML = "";
            return;
        }
        ativarSpinner(inputCliente);
        fetch("index.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `cliente_pedido=${encodeURIComponent(termo)}`,
        })
            .then(res => res.text())
            .then(data => {
                if (resultadoCliente) resultadoCliente.innerHTML = data;
                desativarSpinner(inputCliente);
            })
            .catch(() => {
                mostrarAlerta("Erro ao buscar cliente.");
                desativarSpinner(inputCliente);
            });
    }

    function buscarProduto(termo) {
        if (!termo) {
            if (resultadoProduto) resultadoProduto.innerHTML = "";
            document.getElementById("id_produto_hidden")?.remove();
            produtoSelecionado = null;
            return;
        }
        ativarSpinner(inputProduto);
        fetch("index.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `produto_pedido=${encodeURIComponent(termo)}`,
        })
            .then(res => res.text())
            .then(data => {
                if (resultadoProduto) resultadoProduto.innerHTML = data;
                desativarSpinner(inputProduto);
            })
            .catch(() => {
                mostrarAlerta("Erro ao buscar produto.");
                desativarSpinner(inputProduto);
            });
    }

    // ===========================================
    // CONFIGURAÇÃO DOS EVENTOS DE SELEÇÃO (DELEGAÇÃO)
    // ===========================================
    if (resultadoCliente) {
        resultadoCliente.addEventListener("click", function (e) {
            const clienteItem = e.target.closest(".cliente-item");
            if (!clienteItem) return;

            if (inputCliente) inputCliente.value = clienteItem.textContent;

            let hiddenIdCliente = document.getElementById("id_cliente_hidden");
            if (!hiddenIdCliente) {
                hiddenIdCliente = document.createElement("input");
                hiddenIdCliente.type = "hidden";
                hiddenIdCliente.id = "id_cliente_hidden";
                hiddenIdCliente.name = "id_cliente";
                inputCliente?.parentElement.appendChild(hiddenIdCliente);
            }
            hiddenIdCliente.value = clienteItem.dataset.id;
            this.innerHTML = "";
            verificarLimiteCredito(); // Chama a verificação que habilita o botão salvar
        });
    }

    if (resultadoProduto) {
        resultadoProduto.addEventListener("click", function (e) {
            const produtoItem = e.target.closest(".produto-item");
            if (!produtoItem) return;

            const { id, nome, cor, largura, valorvenda, quantidade } = produtoItem.dataset;

            if (inputProduto) {
                let displayText = nome;
                if (cor && cor !== 'null') displayText += ` - Cor: ${cor}`;
                if (largura && largura !== 'null') displayText += ` - Largura: ${largura}m`;
                inputProduto.value = displayText;
            }

            let hiddenIdProduto = document.getElementById("id_produto_hidden");
            if (!hiddenIdProduto) {
                hiddenIdProduto = document.createElement("input");
                hiddenIdProduto.type = "hidden";
                hiddenIdProduto.id = "id_produto_hidden";
                inputProduto?.parentElement.appendChild(hiddenIdProduto);
            }
            hiddenIdProduto.value = id;

            produtoSelecionado = { id, nome, cor: cor || '', largura: largura || '', valorVenda: parseFloat(valorvenda) || 0, quantidade: parseFloat(quantidade) || 0 };
            this.innerHTML = "";
        });
    }

    // ===========================
    // ADICIONAR PRODUTO NA TABELA
    // ===========================
    if (btnAdicionar) {
        btnAdicionar.addEventListener("click", function () {
            const idProduto = document.getElementById("id_produto_hidden")?.value;
            const qtdStr = inputQuantidade?.value.replace(',', '.') || '0';
            const qtd = parseFloat(qtdStr);

            if (!produtoSelecionado || !idProduto || isNaN(qtd) || qtd <= 0) {
                return mostrarAlerta("Selecione um produto e uma quantidade válida!", "warning");
            }
            if (!tbody) return;

            if (tbody.querySelector(`tr[data-id-produto="${idProduto}"]`)) {
                return mostrarAlerta("Produto já adicionado! Altere a quantidade na tabela.", "warning");
            }

            fetch("index.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `verificar_quantidade=1&id_produto=${idProduto}&quantidade=${qtd}`,
            })
                .then(res => res.text())
                .then(data => {
                    if (data.includes("erro_quantidade")) {
                        mostrarAlerta("Quantidade insuficiente em estoque!");
                        if (inputQuantidade) inputQuantidade.value = "";
                        return;
                    }

                    const valorUnitario = produtoSelecionado.valorVenda;
                    const valorLinha = valorUnitario * qtd;
                    const tr = tbody.insertRow();
                    tr.dataset.idProduto = idProduto;

                    // Preenchimento correto das colunas
                    const cellNome = tr.insertCell(0);
                    cellNome.className = 'text-start';
                    cellNome.textContent = produtoSelecionado.nome;
                    tr.insertCell(1).textContent = produtoSelecionado.cor || '-';
                    tr.insertCell(2).textContent = produtoSelecionado.largura || '-';
                    const cellQtd = tr.insertCell(3);
                    const inputQtdNaTabela = document.createElement("input");
                    inputQtdNaTabela.type = "text";
                    inputQtdNaTabela.className = "form-control form-control-sm text-center quantidade-item";
                    inputQtdNaTabela.value = String(qtd).replace('.', ',');
                    inputQtdNaTabela.dataset.valorAnterior = String(qtd).replace('.', ',');
                    cellQtd.appendChild(inputQtdNaTabela);
                    tr.insertCell(4).textContent = formatarMoeda(valorUnitario);
                    tr.insertCell(5).textContent = formatarMoeda(valorLinha);
                    const cellBtn = tr.insertCell(6);
                    const btnRemover = document.createElement("button");
                    btnRemover.type = "button";
                    btnRemover.className = "btn btn-outline-danger btn-sm btn-remover-item";
                    btnRemover.innerHTML = '<i class="bi bi-trash"></i>';
                    // O botão já nasce habilitado por padrão
                    cellBtn.appendChild(btnRemover);

                    const createHiddenInput = (name, value) => {
                        const input = document.createElement("input");
                        input.type = "hidden";
                        input.name = name;
                        input.value = value;
                        tr.appendChild(input);
                    };
                    createHiddenInput(`itens[${idProduto}][valor_unitario]`, valorUnitario.toFixed(2));
                    createHiddenInput(`itens[${idProduto}][valor_total]`, valorLinha.toFixed(2));

                    recalcularTotaisAPartirDaTabela();

                    // Limpa campos para o próximo produto
                    if (inputProduto) inputProduto.value = "";
                    if (inputQuantidade) inputQuantidade.value = "";
                    document.getElementById("id_produto_hidden")?.remove();
                    if (resultadoProduto) resultadoProduto.innerHTML = "";
                    produtoSelecionado = null;
                });
        });
    }

    // ===========================================
    // DELEGAÇÃO: CONTROLE DA TABELA DE PRODUTOS
    // ===========================================
    if (tbody) {
        tbody.addEventListener("focusin", function (e) {
            const input = e.target.closest(".quantidade-item");
            if (input) {
                input.dataset.valorAnterior = input.value;
            }
        });

        tbody.addEventListener("input", function (e) {
            const input = e.target.closest(".quantidade-item");
            if (!input) return;

            const tr = input.closest("tr");
            if (!tr) return;

            const idProduto = tr.dataset.idProduto;
            const novaQtd = parseFloat(input.value.replace(',', '.'));
            const valorUnitario = parseFloat(tr.querySelector("input[name$='[valor_unitario]']")?.value) || 0;
            const hiddenTotal = tr.querySelector("input[name$='[valor_total]']");
            const cellTotal = tr.querySelector("td:nth-child(6)");
            const valorAnterior = input.dataset.valorAnterior;

            if (!idProduto || isNaN(novaQtd) || novaQtd <= 0) {
                mostrarAlerta("Quantidade inválida!", "warning");
                input.value = valorAnterior;
                return;
            }

            fetch("index.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `verificar_quantidade=1&id_produto=${idProduto}&quantidade=${novaQtd}`,
            })
                .then(res => res.text())
                .then(data => {
                    if (data.includes("erro_quantidade")) {
                        mostrarAlerta("Estoque insuficiente!", "warning");
                        input.value = valorAnterior;
                        return;
                    }
                    const novoSubtotal = valorUnitario * novaQtd;
                    if (cellTotal) cellTotal.textContent = formatarMoeda(novoSubtotal);
                    if (hiddenTotal) hiddenTotal.value = novoSubtotal.toFixed(2);
                    recalcularTotaisAPartirDaTabela();
                    input.dataset.valorAnterior = input.value;
                })
                .catch(() => {
                    mostrarAlerta("Erro ao verificar estoque!", "danger");
                    input.value = valorAnterior;
                });
        });

        tbody.addEventListener("click", function (e) {
            const btn = e.target.closest(".btn-remover-item");
            if (!btn) return;
            btn.closest("tr")?.remove();
            recalcularTotaisAPartirDaTabela(); // Recalcula e reavalia o botão 'Salvar'
        });
    }

    // ===========================
    // LISTENERS DIVERSOS
    // ===========================
    if (selectPagamento) {
        selectPagamento.addEventListener("change", verificarBotaoSalvar);
    }

    if (freteEl) {
        freteEl.addEventListener("input", (e) => {
            let somenteNumeros = e.target.value.replace(/\D/g, "");
            let valor = parseFloat(somenteNumeros) / 100;
            valorFrete = isNaN(valor) ? 0 : valor;
            e.target.value = formatarMoeda(valorFrete);
            atualizarValorTotalComFrete();
        });
    }

    document.getElementById("limpar_pedido")?.addEventListener("click", () => {
        if (inputCliente) inputCliente.value = "";
        document.getElementById("id_cliente_hidden")?.remove();
        if (resultadoCliente) resultadoCliente.innerHTML = "";
        if (inputProduto) inputProduto.value = "";
        if (inputQuantidade) inputQuantidade.value = "";
        document.getElementById("id_produto_hidden")?.remove();
        if (resultadoProduto) resultadoProduto.innerHTML = "";
        if (tbody) tbody.innerHTML = "";
        if (freteEl) freteEl.value = "";
        if (selectPagamento) selectPagamento.value = "";

        valorTotal = 0;
        valorFrete = 0;
        produtoSelecionado = null;

        recalcularTotaisAPartirDaTabela(); // Zera os totais e desabilita o botão 'Salvar'
    });

    // ===========================
    // SALVAR PEDIDO
    // ===========================
    if (btnSalvar) {
        btnSalvar.addEventListener("click", function (e) {
            e.preventDefault();
            if (this.disabled) return;

            // ... (código de salvar o pedido permanece o mesmo)
            const idCliente = document.getElementById("id_cliente_hidden")?.value;
            const idPagamento = selectPagamento?.value;
            if (!idCliente || !idPagamento || (tbody?.querySelectorAll("tr").length || 0) === 0) {
                return mostrarAlerta("Preencha cliente, forma de pagamento e adicione produtos!", "warning");
            }

            const form = document.createElement("form");
            form.method = "POST";
            form.action = "index.php";

            const createFormInput = (name, value) => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = name;
                input.value = value;
                form.appendChild(input);
            };

            createFormInput("salvar_pedido", "1");
            createFormInput("id_cliente", idCliente);
            createFormInput("status_pedido", "Pendente");
            createFormInput("valor_total", (valorTotal + valorFrete).toFixed(2));
            createFormInput("id_forma_pagamento", idPagamento);
            createFormInput("origem", document.getElementById("origem")?.value || "");
            createFormInput("valor_frete", valorFrete.toFixed(2));

            tbody.querySelectorAll("tr").forEach((tr) => {
                const idProduto = tr.dataset.idProduto;
                const quantidade = tr.querySelector("input.quantidade-item")?.value.replace(',', '.') || 0;
                const valorUnitario = tr.querySelector("input[name$='[valor_unitario]']")?.value || 0;

                createFormInput(`itens[${idProduto}][id_produto]`, idProduto);
                createFormInput(`itens[${idProduto}][quantidade]`, quantidade);
                createFormInput(`itens[${idProduto}][valor_unitario]`, valorUnitario);
            });

            document.body.appendChild(form);
            form.submit();
            form.remove();
        });
    }

    // Chamada inicial para garantir que o botão 'Salvar' comece desabilitado
    verificarBotaoSalvar();
});