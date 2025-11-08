document.addEventListener("DOMContentLoaded", () => {
    // Expressão regular das regras da senha
    const senhaRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+]).{12,}$/;

    /**
     * Exibe uma mensagem dinâmica abaixo do campo.
     */
    function mostrarMensagem(campo, mensagem, tipo = "erro") {
        const wrapper = campo.parentElement.parentElement;
        if (!wrapper) return;

        let feedback = wrapper.querySelector(".senha-feedback");

        if (!feedback) {
            feedback = document.createElement("div");
            feedback.classList.add("senha-feedback", "mt-1", "small");
            campo.parentElement.insertAdjacentElement("afterend", feedback);
        }

        const corClasse = tipo === "erro" ? "text-danger" : "text-success";
        feedback.classList.remove("text-danger", "text-success");
        feedback.classList.add(corClasse);
        feedback.textContent = mensagem;
    }

    /**
     * Limpa mensagem de feedback do campo.
     */
    function limparMensagem(campo) {
        const wrapper = campo.parentElement.parentElement;
        if (!wrapper) return;
        const feedback = wrapper.querySelector(".senha-feedback");
        if (feedback) feedback.textContent = "";
    }

    /**
     * Valida senha e confirmação.
     */
    function validarCamposSenha(form, isSubmit = false) {
        const senhaInput = form.querySelector('input[name="senha"]');
        const confInput = form.querySelector('input[name="confSenha"]');
        if (!senhaInput || !confInput) return true;

        let valido = true;

        // === VALIDA SENHA PRINCIPAL ===
        if (senhaInput.value.trim() === "") {
            if (isSubmit) mostrarMensagem(senhaInput, "Este campo é obrigatório.", "erro");
            else limparMensagem(senhaInput);
            valido = false;
        } else if (!senhaRegex.test(senhaInput.value)) {
            mostrarMensagem(
                senhaInput,
                "A senha deve ter no mínimo 12 caracteres, incluindo letra maiúscula, minúscula e símbolo (ex: !@#$%).",
                "erro"
            );
            valido = false;
        } else {
            mostrarMensagem(senhaInput, "Senha válida ✅", "sucesso");
        }

        // === VALIDA CONFIRMAÇÃO ===
        if (confInput.value.trim() === "") {
            if (isSubmit) mostrarMensagem(confInput, "Este campo é obrigatório.", "erro");
            else limparMensagem(confInput);
            valido = false;
        } else if (confInput.value !== senhaInput.value) {
            mostrarMensagem(confInput, "As senhas não coincidem.", "erro");
            valido = false;
        } else if (senhaRegex.test(senhaInput.value)) {
            mostrarMensagem(confInput, "Senhas coincidem ✅", "sucesso");
        } else {
            limparMensagem(confInput);
        }

        return valido;
    }

    // === APLICA A VALIDAÇÃO EM TODOS OS FORMULÁRIOS ===
    document.querySelectorAll("form").forEach(form => {
        const senhaInput = form.querySelector('input[name="senha"]');
        const confInput = form.querySelector('input[name="confSenha"]');
        if (!senhaInput || !confInput) return;

        // Validação dinâmica (enquanto digita)
        senhaInput.addEventListener("input", () => validarCamposSenha(form, false));
        confInput.addEventListener("input", () => validarCamposSenha(form, false));

        // Validação final ao enviar o formulário
        form.addEventListener("submit", e => {
            if (!validarCamposSenha(form, true)) {
                e.preventDefault();  // Bloqueia envio
                e.stopPropagation();

                // Foca no primeiro campo com problema
                if (
                    senhaInput.value.trim() === "" ||
                    !senhaRegex.test(senhaInput.value)
                ) {
                    senhaInput.focus();
                } else if (
                    confInput.value.trim() === "" ||
                    confInput.value !== senhaInput.value
                ) {
                    confInput.focus();
                }
            }
        });
    });
});
