// Função reutilizável para mostrar alerta visual
function mostrarAlerta(mensagem, tipo = "danger", duracao = 3000) {
    // Cria um elemento de alerta
    const alerta = document.createElement("div");
    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow`;
    // Define o conteúdo do alerta
    alerta.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    `;
    // Estiliza o alerta
    // o objetivo Object.assign é aplicar várias propriedades de estilo de uma vez
    Object.assign(alerta.style, {
        position: "fixed",
        top: "20px",
        right: "20px",
        zIndex: 1055,
    });
    // Adiciona o alerta ao corpo do documento
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), duracao);
    }
    // Adiciona o evento de carregamento do DOM
    document.addEventListener("DOMContentLoaded", () => {
    const cepInput = document.getElementById("cep");
    // Verifica se o elemento existe antes de adicionar o evento
    if (!cepInput) return;
    // Adiciona o evento de blur ao campo de CEP
    // o blur e um evento que ocorre quando o campo perde o foco
    cepInput.addEventListener("input", () => {
        // Remove caracteres não numéricos do CEP
        const cep = cepInput.value.replace(/\D/g, "");
        // Verifica se o CEP tem 8 dígitos
        if (cep.length !== 8) {
        mostrarAlerta("CEP inválido. Digite um CEP com 8 dígitos.", "warning");
        return;
        }
        // busca dinamicamente no ViaCEP
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then((response) => {
            if (!response.ok) {
            throw new Error("Erro ao buscar CEP");
            }
            return response.json();
        })
        // Verifica se a resposta contém erro
        .then((data) => {
            if (data.erro) {
            mostrarAlerta("CEP não encontrado.", "warning");
            return;
            }
            // Preenche os campos de endereço
            document.getElementById("cidade").value = data.localidade || "";
            document.getElementById("estado").value = data.uf || "";
            document.getElementById("bairro").value = data.bairro || "";
            document.getElementById("complemento").value = data.logradouro || "";
        })
        .catch((error) => {
            // Log do erro para depuração
            console.error("Erro ao consultar o ViaCEP:", error);
            // Exibe uma mensagem de erro ao usuário
            mostrarAlerta(
            "Erro ao buscar o endereço. Verifique o CEP e tente novamente.",
            "danger"
            );
        });
    });
    // limpa os campos de o endereço quando o campo de CEP é limpo
    cepInput.addEventListener("input", () => {
        if (cepInput.value.trim() === "") {
            document.getElementById("cidade").value = "";
            document.getElementById("estado").value = "";
            document.getElementById("bairro").value = "";
            document.getElementById("complemento").value = "";
        }
    });
});