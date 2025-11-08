document.addEventListener("DOMContentLoaded", () => {
    // Expressão regular das regras
    const senhaRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+]).{12,}$/;

    /**
     * Exibe uma mensagem dinâmica de erro ou sucesso
     */
    function mostrarMensagem(campo, mensagem, tipo = "erro") {
        let feedback = campo.parentElement.querySelector(".senha-feedback");
        if (!feedback) {
            feedback = document.createElement("div");
            feedback.classList.add("senha-feedback", "mt-1");
            campo.parentElement.appendChild(feedback);
        }

        feedback.textContent = mensagem;
        feedback.className = `senha-feedback mt-1 text-${tipo === "erro" ? "danger" : "success"}`;
    }

    /**
     * Valida senha e confirmação dinamicamente
     */
    function validarCamposSenha(form) {
        const senhaInput = form.querySelector('input[name="senha"]');
        const confInput = form.querySelector('input[name="confSenha"]');
        if (!senhaInput || !confInput) return false;

        let valido = true;

        // Valida senha principal
        if (!senhaRegex.test(senhaInput.value)) {
            mostrarMensagem(
                senhaInput,
                "A senha deve ter no mínimo 12 caracteres, incluir letra maiúscula, minúscula e símbolo (ex: !@#$%)."
            );
            valido = false;
        } else {
            mostrarMensagem(senhaInput, "Senha válida ✅", "sucesso");
        }

        // Valida confirmação
        if (confInput.value !== senhaInput.value) {
            mostrarMensagem(confInput, "As senhas não coincidem.");
            valido = false;
        } else if (confInput.value.length > 0) {
            mostrarMensagem(confInput, "Senhas coincidem ✅", "sucesso");
        }

        return valido;
    }

    /**
     * Inicializa a validação para todas as modais com formulários de senha
     */
    document.querySelectorAll("form").forEach(form => {
        const senhaInput = form.querySelector('input[name="senha"]');
        const confInput = form.querySelector('input[name="confSenha"]');
        if (!senhaInput || !confInput) return;

        // Atualiza dinamicamente
        senhaInput.addEventListener("input", () => validarCamposSenha(form));
        confInput.addEventListener("input", () => validarCamposSenha(form));

        // Impede envio se inválido
        form.addEventListener("submit", e => {
            if (!validarCamposSenha(form)) {
                e.preventDefault();
                e.stopPropagation();
                senhaInput.focus();
            }
        });
    });
});
