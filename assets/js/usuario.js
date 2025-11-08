
function toggleSenha(id_usuario, isConfirm) {
  const inputId = isConfirm ? "confSenha" + id_usuario : "senha" + id_usuario;
  const iconId  = isConfirm ? "toggleConfSenhaIcon" + id_usuario : "toggleSenhaIcon" + id_usuario;

  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  if (!input || !icon) return;

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

// 🔐 expõe para o onclick do HTML
window.toggleSenha = toggleSenha;

// (opcional) seu ready pode ficar só com as máscaras
$(document).ready(function () {
  $('[id^=alterar_usuario]').on('shown.bs.modal', function () {
    $(this).find('input[name="telefone"]').mask('(00) 00000-0000');
    $(this).find('input[name="cpf"]').mask('000.000.000-00');
  });

  $('input[name="telefone"]').mask('(00) 00000-0000');
  $('input[name="cpf"]').mask('000.000.000-00');
  $('input[name="cpf_consulta"]').mask('000.000.000-00');
});