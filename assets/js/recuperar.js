// Validação do reCAPTCHA para o formulário de recuperação
    $('#recoveryForm').submit(function (event) {

        // Pega a resposta do reCAPTCHA
        var recaptchaResponse = grecaptcha.getResponse();

        // Seleciona a div de erro
        var errorDiv = $('#recaptchaError');

        // Verifica se a resposta está vazia
        if (recaptchaResponse.length === 0) {

            // Impede o envio do formulário
            event.preventDefault();
            
            // Mostra a mensagem de erro
            errorDiv.text("Por favor, marque a caixa 'Não sou um robô'.");
            errorDiv.show();

        } else {
            // Se estiver marcado, esconde qualquer erro antigo
            errorDiv.hide();
        }
    });