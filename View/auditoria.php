<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Sistema de Gerenciamento SG3S</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Links Bootstrap -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (opcional, mas útil) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome (para os ícones do menu, se usado) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Favicon -->
    <link rel="icon" href="img/favicon.ico">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <?php
    print $menu;
    ?>
    <main class="container text-center">
        <div class="text-center mb-4">
            <h3 class="display-6">Sistema de Gerenciamento SG3S</h3>
        </div>
        <h4 class="mb-4 text-center">Auditoria</h4>
        <h5 class="mb-4 text-center">Consula de Registros do Sistema</h5>
    </main>
    <div class="modal fade" id="modalCalendario" tabindex="-1" aria-labelledby="modalCalendarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCalendarioLabel"><i class="fas fa-calendar-alt me-2"></i>Calendário do Mês</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php print $this->Calendario(); ?>
                </div>
            </div>
        </div>
    </div>
    <footer class="text-center py-1 mt-6 bg-light">
        <p>&copy; <?= date('Y')?> Sistema de Gerenciamento SG3S. Todos os direitos reservados.</p>
        <p>Developed by Heymesson Azêvedo.</p>
    </footer>
    <!-- jQuery 3.7.1 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- jQuery Mask Plugin 1.14.16 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <!-- Popper.js 2.11.8 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <!-- Bootstrap 5.3.3 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    <!-- ajax de produtos com baixo estoque-->
    <script src="assets/js/produtobaixoEstoque.js"></script>
    
</body>

</html>