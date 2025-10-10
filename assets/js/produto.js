$(document).ready(function () {

  // Variável para guardar o estado do formulário principal
  let estadoFormVerificar = {};

  // Função para exibir alertas visuais
  function mostrarAlerta(mensagem, tipo = "danger", duracao = 4000) {
    const alerta = document.createElement("div");
    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow-lg`;
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

  // Funções para controlar o estado de "carregando" (spinner)
  function ativarSpinner(elemento, texto = "Buscando...") {
    if (!elemento) return;
    elemento.disabled = true;
    elemento.dataset.originalText = elemento.tagName === "BUTTON" ? elemento.innerHTML : elemento.value;
    if (elemento.tagName === "BUTTON") {
      elemento.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> ${texto}`;
    }
  }

  function desativarSpinner(elemento) {
    if (!elemento) return;
    elemento.disabled = false;
    if (elemento.dataset.originalText) {
      if (elemento.tagName === "BUTTON") {
        elemento.innerHTML = elemento.dataset.originalText;
      } else {
        elemento.value = elemento.dataset.originalText;
      }
    }
  }

  // ===========================
  // MÁSCARAS DE CAMPOS
  // ===========================
  $(".cnpj").mask("00.000.000/0000-00", { reverse: true });
  $(".dinheiro").mask("R$ 000.000.000,00", { reverse: true });

  function aplicarMascaraDecimal(selector) {
    $(selector).on("input", function () {
      this.value = this.value.replace(/[^0-9.,]/g, "");
      let partes = this.value.split(/[,\.]/);
      if (partes.length > 2) {
        this.value = partes[0] + "." + partes.slice(1).join("");
      }
    });
    $(selector).on("blur", function () {
      let valor = this.value.replace(",", ".");
      if (valor && !isNaN(valor)) {
        this.value = parseFloat(valor).toString();
      } else {
        this.value = "";
      }
    });
  }
  aplicarMascaraDecimal("input[name='largura'], input[name='quantidade'], input[name='quantidade_minima']");

  // ===========================
  // VALIDAÇÃO DE CNPJ
  // ===========================
  $(".cnpj").on("blur", function () {
    const cnpj = $(this).val();
    this.setCustomValidity(validarCNPJ(cnpj) ? "" : "CNPJ inválido");
  });

  function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g, "");
    if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) return false;
    let tamanho = cnpj.length - 2, numeros = cnpj.substring(0, tamanho), digitos = cnpj.substring(tamanho);
    let soma = 0, pos = tamanho - 7;
    for (let i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2) pos = 9;
    }
    let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (resultado !== parseInt(digitos.charAt(0))) return false;
    tamanho += 1; numeros = cnpj.substring(0, tamanho); soma = 0; pos = tamanho - 7;
    for (let i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2) pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    return resultado === parseInt(digitos.charAt(1));
  }

  // ===========================
  // VALIDAÇÃO DE IMAGENS
  // ===========================
  $("input[type='file'][accept^='image/']").on("change", function () {
    const file = this.files[0];
    limparErroVisual(this);
    if (!file) return;
    const tiposPermitidos = ["image/jpeg", "image/png", "image/gif"];
    const maxTamanho = 500 * 1024;
    const maxLargura = 1200, maxAltura = 1200;
    if (!tiposPermitidos.includes(file.type)) {
      marcarErro(this, "Somente JPEG, PNG ou GIF são aceitos.");
      this.value = ""; return;
    }
    if (file.size > maxTamanho) {
      marcarErro(this, "Tamanho máximo permitido é 500KB.");
      this.value = ""; return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        if (img.width > maxLargura || img.height > maxAltura) {
          marcarErro(this, `Imagem deve ter no máximo ${maxLargura}x${maxAltura}px.`);
          this.value = "";
        } else {
          atualizarPreview(this, e.target.result);
        }
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });

  function marcarErro(input, mensagem) { input.classList.add("is-invalid"); input.title = mensagem; }
  function limparErroVisual(input) { input.classList.remove("is-invalid"); input.title = ""; }
  function atualizarPreview(input, base64) {
    let previewDiv, legendaLabel;
    if (input.id === "img_produto") {
      previewDiv = $("#preview_imagem_cadastro")[0];
      legendaLabel = $("#legenda_imagem_cadastro")[0];
    } else {
      const id = input.id.replace("img_produto", "");
      previewDiv = $(`#preview_imagem${id}`)[0];
      legendaLabel = $(`#legenda_imagem${id}`)[0];
    }
    if (previewDiv && legendaLabel) {
      previewDiv.innerHTML = `<img src="${base64}" class="img-thumbnail" style="max-width:80px; height:auto;">`;
      legendaLabel.textContent = "Imagem Selecionada:";
    }
  }

  // ===========================
  // FUNÇÃO GENÉRICA DE BUSCA
  // ===========================
  function inicializarBusca(input, hidden, resultado, bodyKey, itemClass, alertMsg) {
    function buscar(termo) {
      if (!termo) { resultado.innerHTML = ""; return; }
      ativarSpinner(input);
      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `${bodyKey}=${encodeURIComponent(termo)}`
      })
        .then(res => res.ok ? res.text() : Promise.reject())
        .then(data => {
          resultado.innerHTML = data;
          resultado.querySelectorAll(`.${itemClass}`).forEach(item => {
            item.addEventListener("click", function () {
              input.value = this.dataset.nome;
              hidden.value = this.dataset.id;
              resultado.innerHTML = "";
            });
          });
        })
        .catch(() => mostrarAlerta(`Erro ao buscar ${bodyKey}.`))
        .finally(() => desativarSpinner(input));
    }
    input.addEventListener("input", () => {
      hidden.value = "";
      buscar(input.value.trim());
    });
    input.closest("form").addEventListener("submit", function (e) {
      if (input.value.trim() !== "" && !hidden.value && input.required) {
        e.preventDefault();
        mostrarAlerta(alertMsg);
      }
    });
  }

  // =======================================================
  // CADASTRO RÁPIDO COM GERENCIAMENTO DE ESTADO (VERSÃO FINAL)
  // =======================================================
  function configurarSubmitAjaxParaModal(formId, modalId, targetInputId, targetHiddenId) {
    const form = document.getElementById(formId);
    if (!form) return;

    let clickedSubmitButton = null;
    form.querySelectorAll('button[type="submit"]').forEach(button => {
      button.addEventListener('click', function () { clickedSubmitButton = this; });
    });

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      const formData = new FormData(form);
      formData.append("origem", "javascript");
      if (clickedSubmitButton && clickedSubmitButton.name) {
        formData.append(clickedSubmitButton.name, clickedSubmitButton.value || '1');
      }
      const submitButtonParaSpinner = clickedSubmitButton || form.querySelector('button[type="submit"]');

      ativarSpinner(submitButtonParaSpinner, "Cadastrando...");
      fetch('index.php', { method: "POST", body: formData })
        .then(response => response.ok ? response.json() : Promise.reject())
        .then(data => {
          if (data.success) {
            mostrarAlerta(data.message, "success");
            const modalCadastroNode = document.querySelector(modalId);
            if (modalCadastroNode) {
              const modalCadastro = bootstrap.Modal.getInstance(modalCadastroNode);
              if (modalCadastro) modalCadastro.hide();
            }
            form.reset();

            // Atualiza o estado salvo com o novo item
            if (data.newItem) {
              const targetInput = document.getElementById(targetInputId);
              const targetHiddenInput = document.getElementById(targetHiddenId);
              if (targetInput && targetHiddenInput) {
                estadoFormVerificar[targetInput.name] = data.newItem.name;
                estadoFormVerificar[targetHiddenInput.name] = data.newItem.id;
              }
            }

            const modalVerificar = new bootstrap.Modal(document.getElementById('modal_verificar_produto'));
            modalVerificar.show();

          } else {
            mostrarAlerta(data.message || "Ocorreu um erro.", "danger");
          }
        })
        .catch(() => mostrarAlerta("Falha de comunicação. Tente novamente.", "danger"))
        .finally(() => {
          desativarSpinner(submitButtonParaSpinner);
          clickedSubmitButton = null;
        });
    });
  }

  // ===========================
  // INICIALIZAÇÃO E EVENTOS
  // ===========================

  // -- Fornecedor --
  const initFornecedor = (inputId, hiddenId, resultId) => {
    const input = document.getElementById(inputId);
    if (input) inicializarBusca(input, document.getElementById(hiddenId), document.getElementById(resultId), 'buscar_fornecedor', 'fornecedor-item', 'Selecione um fornecedor da lista.');
  };
  initFornecedor("id_fornecedor_produto", "id_fornecedor_hidden", "resultado_busca_fornecedor");
  initFornecedor("id_fornecedor_produto_verificar", "id_fornecedor_hidden_verificar", "resultado_busca_fornecedor_verificar");
  initFornecedor("id_fornecedor_produto_cadastro", "id_fornecedor_hidden_cadastro", "resultado_busca_fornecedor_cadastro");
  document.querySelectorAll(".fornecedor-input").forEach(input => {
    const id = input.id.replace("id_fornecedor_produto", "");
    inicializarBusca(input, document.getElementById(`id_fornecedor_hidden${id}`), document.getElementById(`resultado_busca_fornecedor${id}`), 'buscar_fornecedor', 'fornecedor-item', 'Selecione um fornecedor da lista.');
  });

  // -- Cor --
  const initCor = (inputId, hiddenId, resultId) => {
    const input = document.getElementById(inputId);
    if (input) inicializarBusca(input, document.getElementById(hiddenId), document.getElementById(resultId), 'cor_produto', 'cor-item', 'Selecione uma cor da lista.');
  };
  initCor("cor", "id_cor_hidden", "resultado_busca_cor");
  initCor("cor_cadastro", "id_cor_hidden_cadastro", "resultado_busca_cor_cadastro");
  initCor("cor_consulta", "id_cor_hidden_consulta", "resultado_busca_cor_consulta");

  // -- Tipo de Produto --
  const initTipo = (inputId, hiddenId, resultId) => {
    const input = document.getElementById(inputId);
    if (input) inicializarBusca(input, document.getElementById(hiddenId), document.getElementById(resultId), 'tipo_produto', 'tipo-item', 'Selecione um tipo da lista.');
  };
  initTipo("tipo_produto_verificar", "id_tipo_hidden_verificar", "resultado_busca_tipo_verificar");
  initTipo("tipo_produto_cadastro", "id_tipo_hidden_cadastro", "resultado_busca_tipo_cadastro");
  initTipo("tipo_produto_consulta", "id_tipo_hidden_consulta", "resultado_busca_tipo_consulta");

  // -- Cadastros Rápidos (com preenchimento automático) --
  configurarSubmitAjaxParaModal('formulario_cor_cadastro', '#modal_cor', 'cor', 'id_cor_hidden');
  configurarSubmitAjaxParaModal('formulario_tipo_produto_cadastro', '#modal_tipo_produto', 'tipo_produto_verificar', 'id_tipo_hidden_verificar');

  // Salva o estado do formulário antes de abrir o cadastro rápido
  document.querySelectorAll('.btn-abrir-cadastro-rapido').forEach(button => {
    button.addEventListener('click', () => {
      const formVerificar = document.getElementById('formulario_verificar_produto');
      const formData = new FormData(formVerificar);
      estadoFormVerificar = {}; // Limpa o estado anterior
      for (const [key, value] of formData.entries()) {
        estadoFormVerificar[key] = value;
      }
    });
  });

  // Restaura o estado do formulário ao reabri-lo
  const verificarModalEl = document.getElementById('modal_verificar_produto');
  if (verificarModalEl) {
    verificarModalEl.addEventListener('show.bs.modal', function () {
      if (Object.keys(estadoFormVerificar).length > 0) {
        const form = document.getElementById('formulario_verificar_produto');
        for (const key in estadoFormVerificar) {
          const input = form.querySelector(`[name="${key}"]`);
          if (input) {
            input.value = estadoFormVerificar[key];
          }
        }
        estadoFormVerificar = {}; // Limpa o estado depois de usar
      }
    });
  }
});