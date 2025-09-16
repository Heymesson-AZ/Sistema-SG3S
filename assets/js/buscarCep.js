// Função reutilizável para mostrar alerta visual
function mostrarAlerta(mensagem, tipo = "danger", duracao = 3000) {
    const alerta = document.createElement("div");
    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow`;
    alerta.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    `;
    Object.assign(alerta.style, {
        position: "fixed",
        top: "20px",
        right: "20px",
        zIndex: 1055,
    });
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), duracao);
}

// Função que aplica busca de CEP em qualquer modal
function aplicarBuscaCepDinamico() {
    document.querySelectorAll(".cep").forEach((cepInput) => {
        cepInput.addEventListener("input", () => {
            const cep = cepInput.value.replace(/\D/g, "");
            const sufixo = cepInput.id.replace("cep", ""); // pega o número (ex: 15)
            const cidadeInput = document.getElementById("cidade" + sufixo);
            const estadoInput = document.getElementById("estado" + sufixo);
            const bairroInput = document.getElementById("bairro" + sufixo);
            const complementoInput = document.getElementById("complemento" + sufixo);

            function limparCampos() {
                if (cidadeInput) cidadeInput.value = "";
                if (estadoInput) estadoInput.value = "";
                if (bairroInput) bairroInput.value = "";
                if (complementoInput) complementoInput.value = "";
            }

            if (cep.length !== 8) {
                limparCampos();
                if (cep !== "") {
                    mostrarAlerta("CEP inválido. Digite um CEP com 8 dígitos.", "warning");
                }
                return;
            }

            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then((r) => r.json())
                .then((data) => {
                    if (data.erro) {
                        limparCampos();
                        mostrarAlerta("CEP não encontrado.", "warning");
                        return;
                    }
                    if (cidadeInput) cidadeInput.value = data.localidade || "";
                    if (estadoInput) estadoInput.value = data.uf || "";
                    if (bairroInput) bairroInput.value = data.bairro || "";
                    if (complementoInput) complementoInput.value = data.logradouro || "";
                })
                .catch(() => {
                    limparCampos();
                    mostrarAlerta("Erro ao buscar o endereço. Verifique o CEP e tente novamente.", "danger");
                });
        });

        // Sempre que apagar o CEP, limpa os campos relacionados
        cepInput.addEventListener("input", () => {
            if (cepInput.value.trim() === "") {
                const sufixo = cepInput.id.replace("cep", "");
                document.getElementById("cidade" + sufixo).value = "";
                document.getElementById("estado" + sufixo).value = "";
                document.getElementById("bairro" + sufixo).value = "";
                document.getElementById("complemento" + sufixo).value = "";
            }
        });
    });
}
// Inicializa ao carregar
document.addEventListener("DOMContentLoaded", aplicarBuscaCepDinamico);
