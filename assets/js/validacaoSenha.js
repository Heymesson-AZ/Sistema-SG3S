document.addEventListener("DOMContentLoaded", () => {
    // Expressão regular das regras
    const senhaRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+]).{12,}$/;

    /**
     * Exibe uma mensagem dinâmica de erro ou sucesso abaixo do campo.
     */
    function mostrarMensagem(campo, mensagem, tipo = "erro") {
        const wrapper = campo.parentElement.parentElement;
        if (!wrapper) return; 

        let feedback = wrapper.querySelector(".senha-feedback");

        if (!feedback) {
            feedback = document.createElement("div");
            // Adiciona classes para estilização
            feedback.classList.add("senha-feedback", "mt-1", "small");
            // Insere após o campo de entrada de senha
            campo.parentElement.insertAdjacentElement('afterend', feedback);
        }
        // Define a cor e o texto da mensagem
        const corClasse = (tipo === "erro") ? "text-danger" : "text-success";
        // Remove ambas as classes antes de adicionar a correta
        feedback.classList.remove("text-danger", "text-success");
        // Adiciona a classe correta
        feedback.classList.add(corClasse);
        // Define o texto da mensagem
        feedback.textContent = mensagem;
    }

    /**
     * Limpa a mensagem de feedback de um campo.
     */
    function limparMensagem(campo) {
        const wrapper = campo.parentElement.parentElement;
        if (!wrapper) return;

        let feedback = wrapper.querySelector(".senha-feedback");
        if (feedback) {
            feedback.textContent = "";
        }
    }

    /**
     * Função principal que valida os campos de senha e confirmação.
     * @param {HTMLElement} form - O formulário sendo validado.
     * @param {boolean} isSubmit - true se a validação estiver ocorrendo durante o submit.
     * @returns {boolean} - true se válido, false se inválido.
     */
    function validarCamposSenha(form, isSubmit = false) {
        const senhaInput = form.querySelector('input[name="senha"]');
        const confInput = form.querySelector('input[name="confSenha"]');
        
        if (!senhaInput || !confInput) return true; 

        let valido = true;

        // 1. Valida senha principal (FORMATO E OBRIGATÓRIO)
        if (senhaInput.value.length === 0) {
            if (isSubmit && senhaInput.required) {
                // Só mostra "obrigatório" se for no submit
                mostrarMensagem(senhaInput, "Este campo é obrigatório.", "erro");
                valido = false;
            } else {
                limparMensagem(senhaInput); // Limpa se estiver só digitando e apagar
            }
        } else if (!senhaRegex.test(senhaInput.value)) {
            mostrarMensagem(
                senhaInput,
                "A senha deve ter no mínimo 12 caracteres, incluir letra maiúscula, minúscula e símbolo (ex: !@#$%).",
                "erro"
            );
            valido = false;
        } else {
            mostrarMensagem(senhaInput, "Senha válida ✅", "sucesso");
        }

        // 2. Valida confirmação (CONFERÊNCIA E OBRIGATÓRIO)
        if (confInput.value.length === 0) {
            if (isSubmit && confInput.required) {
                // Só mostra "obrigatório" se for no submit
                mostrarMensagem(confInput, "Este campo é obrigatório.", "erro");
                valido = false;
            } else {
                limparMensagem(confInput); // Limpa se estiver só digitando e apagar
            }
        } else if (confInput.value !== senhaInput.value) {
            mostrarMensagem(confInput, "As senhas não coincidem.", "erro");
            valido = false;
        } else if (senhaRegex.test(senhaInput.value)) { 
            // Só mostra sucesso se a senha principal também for válida
            mostrarMensagem(confInput, "Senhas coincidem ✅", "sucesso");
        } else {
             // Se a senha principal for inválida, mas a confirmação for igual, 
             // não mostre "sucesso", apenas limpe (ou mantenha o "não coincidem" se for o caso)
             limparMensagem(confInput);
        }

        return valido;
    }

    /**
     * Inicializa a validação para todos os formulários da página.
     */
    document.querySelectorAll("form").forEach(form => {
        const senhaInput = form.querySelector('input[name="senha"]');
        const confInput = form.querySelector('input[name="confSenha"]');
        
        if (!senhaInput || !confInput) return;

        // Atualiza dinamicamente (isSubmit = false)
        senhaInput.addEventListener("input", () => validarCamposSenha(form, false));
        confInput.addEventListener("input", () => validarCamposSenha(form, false));

        // Impede o envio (submit) do formulário se for inválido (isSubmit = true)
        form.addEventListener("submit", e => {
            // Roda uma última validação no momento do submit
            if (!validarCamposSenha(form, true)) { // Passa true aqui
                e.preventDefault();   
                e.stopPropagation();  
                
                // Foca no primeiro campo que estiver com problema
                if ((senhaInput.required && senhaInput.value.length === 0) || !senhaRegex.test(senhaInput.value)) {
                    senhaInput.focus();
                } else if ((confInput.required && confInput.value.length === 0) || confInput.value !== senhaInput.value) {
                    confInput.focus();
                }
            }
        });
    });
});