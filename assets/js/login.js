$(document).ready(function () {

    // Sua máscara de CPF
    $('input[name="cpf"]').mask("000.000.000-00");

    // ---- NOVA VERIFICAÇÃO DO RECAPTCHA ----
    $('#loginForm').submit(function (event) {

        // Pega a resposta do reCAPTCHA
        var recaptchaResponse = grecaptcha.getResponse();

        // Seleciona a div de erro que criamos
        var errorDiv = $('#recaptchaError');

        // Verifica se a resposta está vazia
        if (recaptchaResponse.length === 0) {

            // Impede o envio do formulário
            event.preventDefault();
            errorDiv.text("Por favor, marque a caixa 'Não sou um robô'.");
            errorDiv.show();

        } else {
            // Se estiver marcado, esconde qualquer erro antigo
            errorDiv.hide();
        }
    });
});

function toggleSenha() {
    const input = document.getElementById("senha");
    const icon = document.getElementById("toggleSenhaIcon");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}