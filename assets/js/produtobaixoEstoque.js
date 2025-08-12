document.addEventListener("DOMContentLoaded", () => {
    const contador = document.getElementById("contadorNotificacoes");
    const listaNotificacoes = document.getElementById("listaNotificacoes");

    async function atualizarNotificacoes() {
        try {
            const response = await fetch("index.php?acao=buscarProdutosAbaixoMinimo");
            const html = await response.text();

            // Criar elemento temporário só para contar itens
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;
            const itens = tempDiv.querySelectorAll(".produto-item");

            // Atualizar contador
            contador.textContent = itens.length;
            contador.style.display = itens.length > 0 ? "inline-block" : "none";

            // Montar lista na UL da notificação
            listaNotificacoes.innerHTML = `
                <li class="dropdown-header">Notificações</li>
                <li><hr class="dropdown-divider"></li>
                ${itens.length > 0 ? html : `<li class="text-center text-muted small">Nenhuma notificação</li>`}
            `;
        } catch (error) {
            console.error("Erro ao atualizar notificações:", error);
        }
    }
    atualizarNotificacoes();
    setInterval(atualizarNotificacoes, 30000);
});
