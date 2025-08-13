document.addEventListener("DOMContentLoaded", () => {
  const contador = document.getElementById("contadorNotificacoes");
  const listaNotificacoes = document.getElementById("listaNotificacoes");
  const sino = document.querySelector(".fa-bell"); // Ícone do sino

  let ultimoTotal = 0;

  async function atualizarNotificacoes() {
    try {
      const response = await fetch("index.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "acao=buscarProdutosAbaixoMinimo",
      });

      const html = await response.text();

      // Criar elemento temporário para contar itens
      const tempDiv = document.createElement("div");
      tempDiv.innerHTML = html;
      const itens = tempDiv.querySelectorAll(".produto-item");

      // Atualizar contador
      contador.textContent = itens.length;
      contador.style.display = itens.length > 0 ? "inline-block" : "none";

      // Verificar se a quantidade mudou
      if (itens.length > ultimoTotal) {
        sino.classList.add("animate__animated", "animate__tada");
        setTimeout(() => {
          sino.classList.remove("animate__animated", "animate__tada");
        }, 1000);
      }
      ultimoTotal = itens.length;

      // Atualizar lista com efeito fade-in
      listaNotificacoes.innerHTML = `
                <li class="dropdown-header">Notificações</li>
                <li><hr class="dropdown-divider"></li>
                ${html}
            `;

      // Adicionar destaque temporário aos itens
      const novosItens = listaNotificacoes.querySelectorAll(".produto-item");
      novosItens.forEach((item) => {
        item.classList.add("animate__animated", "animate__fadeIn");
      });
    } catch (error) {
      console.error("Erro ao atualizar notificações:", error);
      listaNotificacoes.innerHTML = `
                <li class="dropdown-header">Notificações</li>
                <li><hr class="dropdown-divider"></li>
                <li class="text-center text-danger small">Erro ao carregar</li>
            `;
    }
  }
  atualizarNotificacoes();
  setInterval(atualizarNotificacoes, 30000);
});
