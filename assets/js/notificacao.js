document.addEventListener("DOMContentLoaded", () => {
  const contador = document.getElementById("contadorNotificacoes");
  const listaNotificacoes = document.getElementById("listaNotificacoes");
  const sino = document.querySelector('.nav-item.dropdown .fa-bell'); // seletor mais seguro

  let ultimoTotal = 0;

  // Função genérica para buscar notificações
  async function buscarNotificacoes(flag) {
    try {
      const response = await fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: flag,
      });
      return await response.text();
    } catch (error) {
      console.error("Erro ao buscar notificações:", error);
      return "";
    }
  }

  // Função principal de atualização
  async function atualizarNotificacoes() {
    const [htmlProdutos, htmlPedidos] = await Promise.all([
      buscarNotificacoes("buscarProdutosAbaixoMinimo=1"),
      buscarNotificacoes("buscarPedidosPendentes=1"),
    ]);

    // Contar itens de cada retorno
    const tempProdutos = document.createElement("div");
    tempProdutos.innerHTML = htmlProdutos;
    const itensProdutos = tempProdutos.querySelectorAll(".produto-item");

    const tempPedidos = document.createElement("div");
    tempPedidos.innerHTML = htmlPedidos;
    const itensPedidos = tempPedidos.querySelectorAll(".pedido-item");

    const totalNotificacoes = itensProdutos.length + itensPedidos.length;

    // Atualiza contador visual
    contador.textContent = totalNotificacoes;
    contador.style.display = totalNotificacoes > 0 ? "inline-block" : "none";

    // Anima o sino se houver aumento no número
    if (totalNotificacoes > ultimoTotal) {
      sino.classList.add("animate__animated", "animate__tada");
      setTimeout(() => {
        sino.classList.remove("animate__animated", "animate__tada");
      }, 1000);
    }
    ultimoTotal = totalNotificacoes;

    // Monta o conteúdo do dropdown
    let conteudoLista = `
      <li class="dropdown-header text-primary fw-bold">
        <i class="fas fa-bell me-1"></i> Notificações
      </li>
      <li><hr class="dropdown-divider"></li>
    `;

    if (totalNotificacoes === 0) {
      // Nenhuma notificação
      conteudoLista += `
        <li class="dropdown-item text-center text-muted py-3">
          <i class="fas fa-check-circle fa-lg mb-1 text-success"></i>
          Sem notificações
        </li>
      `;
    } else {
      // Adiciona produtos se houver
      if (itensProdutos.length > 0) {
        conteudoLista += htmlProdutos;
      }
      // Adiciona pedidos se houver
      if (itensPedidos.length > 0) {
        conteudoLista += htmlPedidos;
      }
    }

    // Atualiza a lista no dropdown
    listaNotificacoes.innerHTML = conteudoLista;

    // Anima os novos itens
    listaNotificacoes.querySelectorAll(".produto-item, .pedido-item").forEach((item) => {
      item.classList.add("animate__animated", "animate__fadeIn");
    });
  }
  // Primeira carga imediata
  atualizarNotificacoes();
  // Atualiza a cada 30 segundos
  setInterval(atualizarNotificacoes, 30000);
});
