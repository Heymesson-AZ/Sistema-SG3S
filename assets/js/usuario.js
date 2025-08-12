$(document).ready(function () {
    // Aplica máscara nos campos do modal de alteração quando o modal for exibido
    $('[id^=alterar_usuario]').on('shown.bs.modal', function () {
        $(this).find('input[name="telefone"]').mask('(00) 00000-0000');
        $(this).find('input[name="cpf"]').mask('000.000.000-00');
    });

    // Se tiver formulário de cadastro que já está na página, aplica diretamente
    $('input[name="telefone"]').mask('(00) 00000-0000');
    $('input[name="cpf"]').mask('000.000.000-00');
    $('input[name="cpf_consulta"]').mask('000.000.000-00');
});
