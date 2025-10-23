// ===================================================================
// FUNÇÕES UTILITÁRIAS (Sem mudanças, exceto pelo alerta)
// ===================================================================

/**
 * Mostra um alerta flutuante (toast).
 * @param {string} mensagem A mensagem a ser exibida.
 * @param {string} [tipo="danger"] O tipo de alerta (primary, success, warning, danger, info).
 * @param {number} [duracao=3000] Duração em milissegundos.
 */
function mostrarAlerta(mensagem, tipo = "danger", duracao = 3000) {
    const alerta = document.createElement("div");
    // Adiciona classes de sombra e borda para um visual mais moderno
    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow-sm border-0`;
    alerta.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    `;
    Object.assign(alerta.style, {
        position: "fixed",
        top: "20px",
        right: "20px",
        zIndex: 1055, // Garante que fique acima de modais (Bootstrap modal é 1050)
        maxWidth: "350px", // Evita que o alerta fique muito largo
        wordWrap: "break-word"
    });
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), duracao);
}

/**
 * Utilitário "Debounce".
 * Atras a execução de uma função até que o usuário pare de digitar.
 * @param {Function} func A função a ser executada.
 * @param {number} [delay=400] O tempo de espera em milissegundos.
 */
function debounce(func, delay = 400) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

/**
 * Helper para limpar os campos de endereço de um formulário.
 * @param {string} [sufixo=""] O sufixo do ID do formulário (ex: "_cliente" ou "").
 */
function limparCamposEndereco(sufixo = "") {
    // Busca os campos de forma segura
    const cidadeInput = document.getElementById("cidade" + sufixo);
    const estadoInput = document.getElementById("estado" + sufixo);
    const bairroInput = document.getElementById("bairro" + sufixo);
    const complementoInput = document.getElementById("complemento" + sufixo);

    if (cidadeInput) cidadeInput.value = "";
    if (estadoInput) estadoInput.value = "";
    if (bairroInput) bairroInput.value = "";
    if (complementoInput) complementoInput.value = "";
}

// ===================================================================
// FUNÇÃO PRINCIPAL DA BUSCA DE CEP
// ===================================================================

/**
 * Função principal que aplica a busca de CEP otimizada.
 */
function aplicarBuscaCepDinamico() {

    // 1. A lógica de busca é criada UMA VEZ e debounced.
    const debouncedBuscaCep = debounce(async (event) => {
        const cepInput = event.target;
        const cep = cepInput.value.replace(/\D/g, "");
        const sufixo = cepInput.id.replace("cep", "");

        // Pega os campos relacionados
        const cidadeInput = document.getElementById("cidade" + sufixo);
        const estadoInput = document.getElementById("estado" + sufixo);
        const bairroInput = document.getElementById("bairro" + sufixo);
        const complementoInput = document.getElementById("complemento" + sufixo);

        // Agrupa os campos que serão desabilitados
        const camposEndereco = [cidadeInput, estadoInput, bairroInput, complementoInput].filter(Boolean);

        // Se o CEP está vazio, limpa os campos e sai.
        if (cep.length === 0) {
            limparCamposEndereco(sufixo);
            return;
        }

        // Se o CEP não tem 8 dígitos, limpa (caso o usuário esteja apagando) e sai.
        if (cep.length !== 8) {
            limparCamposEndereco(sufixo);
            return; // Não faz a busca
        }

        // 2. Inicia o estado de "carregando" (UX)
        cepInput.disabled = true;
        camposEndereco.forEach(input => input.disabled = true);
        // (Opcional: adicionar um ícone de spinner ao lado do input)

        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            if (!response.ok) {
                // Captura erros de rede (ex: 404, 500)
                throw new Error(`Erro de rede: ${response.statusText}`);
            }
            const data = await response.json();

            if (data.erro) {
                limparCamposEndereco(sufixo);
                mostrarAlerta("CEP não encontrado.", "warning");
                cepInput.focus(); // Devolve o foco ao CEP
            } else {
                // 3. Preenche os campos com sucesso
                if (cidadeInput) cidadeInput.value = data.localidade || "";
                if (estadoInput) estadoInput.value = data.uf || "";
                if (bairroInput) bairroInput.value = data.bairro || "";

                // Melhoria: Popula o logradouro e o complemento (se houver)
                let logradouroCompleto = data.logradouro || "";
                if (data.complemento && data.complemento !== "...") {
                    logradouroCompleto += ` - ${data.complemento}`;
                }

                // Note: Você está colocando o LOGRADOURO no campo COMPLEMENTO.
                // Se isso estiver correto, mantemos.
                if (complementoInput) complementoInput.value = logradouroCompleto;

                // 4. Move o foco para o próximo campo (UX)
                if (complementoInput) {
                    complementoInput.focus();
                }
            }
        } catch (error) {
            console.error("Falha ao buscar CEP:", error);
            limparCamposEndereco(sufixo);
            mostrarAlerta("Erro ao buscar o endereço. Verifique o CEP e tente novamente.", "danger");
        } finally {
            // 5. Remove o estado de "carregando" (SEMPRE executa)
            cepInput.disabled = false;
            camposEndereco.forEach(input => input.disabled = false);
        }
    });

    // 6. Aplica o listener DEBENCED a todos os campos .cep
    document.querySelectorAll(".cep").forEach((cepInput) => {
        // Remove listeners antigos (caso esta função seja chamada mais de uma vez)
        // Isso evita que a busca seja disparada múltiplas vezes
        cepInput.removeEventListener("input", debouncedBuscaCep);

        // Adiciona o novo listener
        cepInput.addEventListener("input", debouncedBuscaCep);
    });
}

// Inicializa ao carregar
document.addEventListener("DOMContentLoaded", aplicarBuscaCepDinamico);
