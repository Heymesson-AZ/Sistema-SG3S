    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <title>Sistema de Gerenciamento SG3S</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <!-- Links Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <!-- Favicon -->
        <link rel="icon" href="img/favicon.ico">
        <!-- CSS Personalizado -->
        <link rel="stylesheet" href="assets/css/index.css">
    </head>

    <body>
        <main class="container-fluid mt-4">
            <div class="text-center mb-4">
                <h1 class="display-5">Gerenciamento de Usuários </h1>
                <p class="lead">Utilize as opções abaixo para gerenciar usuarios.</p>
            </div>
            <div class="row justify-content-center g-3 mt-4">
                <div class="col-md-auto">
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_cpf">
                        <i class="bi bi-plus-circle"></i> Cadastrar Usuario
                    </button>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_perfil">
                        <i class="bi bi-plus-circle"></i> Cadastrar Perfil
                    </button>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#consultar_perfil">
                        <i class="bi bi-file-earmark-text"></i> Consultar Perfil
                    </button>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#consultar_usuario">
                        <i class="bi bi-file-earmark-text"></i> Consultar Usuário
                    </button>
                </div>
            </div>
            <!-- Modal de verificar o CPF do Usuário -->
            <div class="modal fade" id="modal_cpf" tabindex="-1" aria-labelledby="modalCpfLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">

                        <!-- Cabeçalho -->
                        <div class="modal-header">
                            <h6 class="modal-title" id="modalCpfLabel">Consultar CPF</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <!-- Corpo -->
                        <div class="modal-body">
                            <form action="index.php" method="POST" id="formulario_consulta_cpf">
                                <fieldset class="border border-black p-3 mb-4">
                                    <legend class="float-none w-auto px-2">Informe o CPF</legend>

                                    <div class="mb-3">
                                        <label for="cpf_consulta" class="form-label">CPF *</label>
                                        <input type="text" class="form-control" id="cpf_consulta" name="cpf_consulta"
                                            required autocomplete="off" placeholder="000.000.000-00"
                                            pattern="\d{3}\.\d{3}\.\d{3}-\d{2}"
                                            title="Formato esperado: XXX.XXX.XXX-XX" />
                                    </div>
                                </fieldset>
                                <!-- Rodapé com botões -->
                                <div class="d-flex justify-content-center gap-2 mt-4">
                                    <button type="reset" class="btn btn-outline-secondary w-50 py-2">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                    </button>
                                    <button type="submit" name="verificar_cpf" class="btn btn-success w-50 py-2">
                                        <i class="bi bi-check-circle-fill"></i> Verificar
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Modal de cadastro de perfil de Usuário -->
            <div class="modal fade" id="modal_perfil" tabindex="-1" aria-labelledby="modalPerfilLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="modalPerfilLabel">Novo Perfil</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <form action="index.php" method="POST" id="formulario_perfil_cadastro">
                                <fieldset class="border border-black p-3 mb-4">
                                    <legend class="float-none w-auto px-2">Dados do Perfil</legend>
                                    <div class="mb-3">
                                        <label for="nome_perfil" class="form-label">Nome do Perfil *</label>
                                        <input type="text" class="form-control" id="nome_perfil" name="nome_perfil"
                                            required autocomplete="off" placeholder="Digite o nome do perfil"
                                            pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"
                                            title="Somente letras e espaços são permitidos" />
                                    </div>
                                </fieldset>
                                <!-- Rodapé com botões -->
                                <div class="d-flex justify-content-center gap-2 mt-4">
                                    <button type="reset" class="btn btn-outline-secondary w-50 py-2">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                    </button>
                                    <button type="submit" name="cadastrar_perfil" class="btn btn-success w-50 py-2">
                                        <i class="bi bi-plus"></i> Cadastrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Consulta de Perfil de Usuário -->
            <div class="modal fade" id="consultar_perfil" tabindex="-1" aria-labelledby="consultarPerfilLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-search"></i> Consulta de Perfil</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Formulário de Consulta -->
                            <form action="index.php" method="post">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="nome_perfil_consulta" class="form-label">Nome do Perfil</label>
                                        <input type="text" class="form-control" id="nome_perfil_consulta" name="nome_perfil" placeholder="Digite o nome do perfil" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="consultar_perfil" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Consultar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Consulta de Usuário -->
            <div class="modal fade " id="consultar_usuario" tabindex="-1" aria-labelledby="consultarUsuarioLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-search"></i> Consulta de Usuário</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Formulário de Consulta -->
                            <form action="index.php" method="post">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nome_usuario_consulta" class="form-label">Nome do Usuário</label>
                                        <input type="text" class="form-control" id="nome_usuario_consulta" name="nome_usuario_consulta" placeholder="Digite o nome do usuário" autocomplete="off" />
                                    </div>
                                    <div class="col-md-6">
                                        <?php $this->select_perfilUsuarioConsulta(); ?>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="consultar_usuario" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Consultar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Cadastro de Usuário -->
            <div class="container-fluid">
                <?php
                if (isset($_SESSION['cpf_cadastro'])) {
                    $this->modal_cadastroUsuario($_SESSION['cpf_cadastro']);
                    unset($_SESSION['cpf_cadastro']);
                }
                ?>
            </div>
            <div class="container-fluid">
                <div class="row justify-content-center mt-4">
                    <div class="col-md-9">
                        <?php $this->tabelaConsultaUsuario($resultado); ?>
                    </div>
                </div>
                <div class="row justify-content-center mt-4">
                    <div class="col-md-6">
                        <?php $this->tabelaConsultaPerfil($perfil); ?>
                    </div>
                </div>
            </div>
            <?php
            // Modal de Alteração e Exclusão de Usuário e Perfil
            foreach ($resultado as $key => $valor) {
                //modal alterar usuario
                $this->modal_AlterarUsuario(
                    $valor->id_usuario,
                    $valor->nome_usuario,
                    $valor->email,
                    $valor->id_perfil,
                    $valor->cpf,
                    $valor->telefone
                );
                //modal excluir usuario
                $this->modalExcluirUsuario(
                    $valor->id_usuario,
                    $valor->nome_usuario
                );
            };
            ?>
            <?php
            foreach ($perfil as $key => $valor) {
                //modal alterar perfil
                $this->modalAlterarPerfil(
                    $valor->id_perfil,
                    $valor->perfil_usuario
                );
                //modal excluir perfil
                $this->modalExcluirPerfil(
                    $valor->id_perfil,
                    $valor->perfil_usuario
                );
            };
            ?>
            <!-- Modal de Alterar senha  -->
            <?php
            foreach ($resultado as $key => $valor) {
                //modal alterar usuario
                $this->modalAlterarSenha(
                    $valor->id_usuario,
                    $valor->nome_usuario,
                    $valor->senha
                );
            };
            ?>
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
        <!-- Footer -->
        <footer class="text-center py-1 mt-6 bg-light">
            <p>&copy; 2025 Sistema de Gerenciamento SG3S. Todos os direitos reservados.</p>
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

        <script src="assets/js/usuario.js"></script>
        <!-- ajax de produtos com baixo estoque-->
        <script src="assets/js/notificacao.js"></script>
    </body>

    </html>