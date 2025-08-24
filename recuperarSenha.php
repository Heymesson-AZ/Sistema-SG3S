<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recuperar Senha - SG3S</title>

    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- FontAwesome 6.5.0 -->
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
            <h4 class="fw-bold text-white mt-3">Recuperar Senha - SG3S</h4>

            <!-- Cartão de recuperação -->
            <div class="card login-card shadow-lg p-4 border-0 rounded-4 mt-4" style="max-width: 420px; width: 100%;">
                <form action="index.php" method="POST" autocomplete="off">
                    <!-- E-mail -->
                    <div class="mb-3 text-start">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-envelope text-primary"></i></span>
                            <input type="email" id="email" name="email" class="form-control"
                                placeholder="Digite seu e-mail" required autocomplete="off" autocorrect="off"
                                autocapitalize="off" spellcheck="false" />
                        </div>
                    </div>

                    <!-- Botão de envio -->
                    <button type="submit" name="recuperar_senha" class="btn btn-primary w-100 rounded-pill">
                        Enviar e-mail de Recuperação
                    </button>

                    <!-- Link para login -->
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none small text-muted">
                            <i class="fas fa-arrow-left me-1"></i>Voltar para login
                        </a>
                    </div>
                </form>
            </div>
        </main>

        <!-- Rodapé -->
        <footer class="text-center text-muted small py-2">
            <p class="mb-0">&copy; 2025 Sistema de Gerenciamento SG3S.</p>
            <p class="mb-0">Desenvolvido por Heymesson Azêvedo</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html>