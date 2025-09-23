<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Sistema de Gerenciamento SG3S - Clientes</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- Links Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <main class="container-fluid mt-4">
        <div class="text-center mb-4">
            <h1 class="display-9">Sistema de Gerenciamento SG3S</h1>
            <p class="lead">Utilize as opções acima para navegar pelo Sistema</p>
        </div>
        <div class="row justify-content-center g-3 mt-4">
            <div class="col-md-auto">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_consulta_cnpj_cliente">
                    <i class="bi bi-plus-circle"></i> Cadastrar Cliente</button>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_consulta_cliente">
                    <i class="bi bi-file-earmark-text"></i> Consultar Cliente</button>
            </div>
        </div>
        <!-- Modal de Verificação de CNPJ do Cliente -->
        <div class="modal fade" id="modal_consulta_cnpj_cliente" tabindex="-1" aria-labelledby="modal_consulta_cnpj_clienteLabel" aria-hidden="true">
            <div class="modal-dialog modal-default modal-dialog-centered">
                <div class="modal-content">
                    <!-- Cabeçalho -->
                    <div class="modal-header">
                        <h6 class="modal-title" id="modalCnpjClienteLabel">Consultar CNPJ do Cliente</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <!-- Corpo -->
                    <div class="modal-body">
                        <form action="index.php" method="POST">
                            <fieldset class="border border-black p-3 mb-4">
                                <legend class="float-none w-auto px-2">Informe o CNPJ</legend>
                                <div class="mb-3">
                                    <label for="cnpj_cliente" class="form-label">CNPJ *</label>
                                    <input type="text"
                                        class="form-control cnpj_cliente"
                                        id="cnpj_cliente"
                                        name="cnpj_cliente"
                                        placeholder="00.000.000/0000-00"
                                        required
                                        pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}"
                                        title="Formato esperado: XX.XXX.XXX/XXXX-XX"
                                        autocomplete="off"
                                        maxlength="18" />
                                    <div class="invalid-feedback">Informe um CNPJ válido.</div>
                                </div>
                            </fieldset>
                            <!-- Rodapé com botões -->
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button type="reset" class="btn btn-outline-secondary w-50 py-2">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpar
                                </button>
                                <button type="submit" name="verificar_cliente" class="btn btn-success w-50 py-2">
                                    <i class="bi bi-check-circle-fill"></i> Verificar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal de Consulta de Cliente -->
        <div class="modal fade" id="modal_consulta_cliente" tabindex="-1" aria-labelledby="modalConsultaClienteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-search" id="modalConsultaClienteLabel"></i> Consulta de Clientes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="index.php" method="POST">
                            <div class="row g-3">
                                <!-- Nome Fantasia -->
                                <div class="col-md-6">
                                    <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                                    <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia"
                                        placeholder="Digite o nome fantasia" autocomplete="off" />
                                </div>
                                <!-- Razão Social -->
                                <div class="col-md-6">
                                    <label for="razao_social" class="form-label">Razão Social</label>
                                    <input type="text" class="form-control" id="razao_social" name="razao_social"
                                        placeholder="Digite a razão social" autocomplete="off" />
                                </div>
                                <!-- CNPJ Cliente -->
                                <div class="col-md-6">
                                    <label for="cnpj_cliente" class="form-label">CNPJ</label>
                                    <input type="text" class="form-control cnpj_cliente" id="cnpj_cliente" name="cnpj_cliente"
                                        placeholder="Digite o CNPJ" autocomplete="off" />
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" id="consultar_cliente" name="consultar_cliente" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Consultar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tabela de Clientes  -->
        <div class="container-fluid">
            <div class="row justify-content-center mt-4">
                <div class="col-md-12">
                    <!-- tabela de produtos -->
                    <?php $this->tabelaConsultarCliente($cliente); ?>
                </div>
            </div>
        </div>
        <?php
        // modal de alteração de cliente
        foreach ($cliente as $key => $valor) {
            $this->modal_AlterarCliente(
                $valor->id_cliente,
                $valor->nome_representante,
                $valor->razao_social,
                $valor->nome_fantasia,
                $valor->cnpj_cliente,
                $valor->email,
                $valor->limite_credito,
                $valor->inscricao_estadual,
                $valor->telefones,
                $valor->cidade,
                $valor->estado,
                $valor->bairro,
                $valor->cep,
                $valor->complemento
            );
            // modal de exclusão de cliente
            $this->modal_ExcluirCliente(
                $valor->id_cliente,
                $valor->nome_fantasia
            );
            // modal de detalhes de cliente
            $this->modalDetalhesCliente($cliente);
        };
        ?>
        <!-- modal de cadastro de cliente-->
        <div class="container-fluid">
            <?php
            if (isset($_SESSION['cnpj_cliente'])) {
                $this->modal_CadastroCliente();
                unset($_SESSION['cnpj_cliente']);
            };
            ?>
        </div>
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
    <!--   -->
    <script src="assets/js/cliente.js"></script>
    <!--  -->
    <script src="assets/js/buscarCep.js"></script>
    <!-- ajax de notificações -->
    <script src="assets/js/notificacao.js"></script>
</body>

</html>