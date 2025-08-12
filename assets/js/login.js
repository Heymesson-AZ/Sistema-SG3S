$(document).ready(function () {
  $('input[name="cpf"]').mask("000.000.000-00");
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
