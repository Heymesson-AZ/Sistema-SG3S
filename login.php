<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SG3S - Sistema de Gerenciamento</title>

  <!-- Bootstrap 5.3.2 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <!-- Fonte moderna -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>
  <div class="container-principal d-flex flex-column min-vh-100">
    <main class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center">
      <!-- Logo + título -->
      <img src="assets/img/logo.jpg" alt="Logo SG3S" class="tam_img rounded-circle shadow" />
      <h4 class="fw-bold text-white mt-3">SG3S - Sistema de Gerenciamento</h4>
      <!-- Cartão de login -->
      <div class="card login-card shadow-lg p-4 border-0 rounded-4 mt-4" style="max-width: 500px; width: 100%;">
        <form action="index.php" method="POST" autocomplete="off" id="loginForm">
          <div class="mb-3 text-start">
            <label for="cpf" class="form-label">Usuário</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fas fa-user text-primary"></i></span>
              <input type="text" id="cpf" name="cpf" class="form-control" placeholder="Digite o usuário (CPF)"
                required pattern=".{11,}" title="Digite os 11 números do CPF" />
            </div>
          </div>

          <div class="mb-3 text-start">
            <label for="senha" class="form-label">Senha</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fas fa-lock text-primary"></i></span>
              <input type="password"
                id="senha"
                name="senha"
                class="form-control"
                placeholder="Digite sua senha (mínimo 12 caracteres)"
                required
                minlength="12"
                autocomplete="current-password"
                title="A senha deve ter pelo menos 12 caracteres." />
              <span class="input-group-text bg-white" style="cursor: pointer;" onclick="toggleSenha()">
                <i class="fas fa-eye" id="toggleSenhaIcon"></i>
              </span>
            </div>
          </div>
          <br>
          <div class="mb-3 d-flex justify-content-center">
            <div class="g-recaptcha" data-sitekey="6LdgIPwrAAAAALE8-aI4jtCwV49iNjYmaHBHI8wq"></div>
          </div>

          <button type="submit" name="login" class="btn btn-primary w-100 rounded-pill">Entrar</button>
          <div class="text-center mt-3">
            <a href="recuperarSenha.php" class="text-decoration-none small text-muted link-info">Esqueci minha senha</a>
          </div>
        </form>
      </div>
    </main>

    <!-- Rodapé -->
    <footer class="text-center">
      <p class="mb-0">&copy; 2025 Sistema de Gerenciamento SG3S</p>
      <p class="mb-0">Desenvolvido por Heymesson Azêvedo</p>
    </footer>
  </div>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <script src="assets/js/login.js"></script>