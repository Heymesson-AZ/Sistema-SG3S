<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Sistema de Gerenciamento SG3S</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="icon" href="img/favicon.ico">

    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/relatorio.css">
    <style>
        /* Garante que o corpo do card cresça e empurre o botão para baixo */
        .card-body {
            display: flex;
            flex-direction: column;
        }

        .card-form-content {
            flex-grow: 1;
        }
    </style>
</head>

<body>
    <?php print $menu; ?>
    <main class="container my-5">
        <div class="text-center mb-4">
            <h1 class="display-6">Sistema de Gerenciamento SG3S</h1>
            <p class="lead">Utilize as opções abaixo para gerar relatórios</p>
        </div>

        <h4 class="mb-4 text-center">Relatórios de Auditoria</h4>

        <div class="row g-4 justify-content-center">

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-form-content">
                            <h6 class="card-title">Auditorias Gerais</h6>
                            <p class="card-text small text-muted">Consulta todas as auditorias registradas nos últimos 7 dias.</p>
                        </div>
                        <form method="POST" action="index.php" class="mt-auto">
                            <button type="submit" class="btn btn-primary w-100" name="auditorias_gerais">
                                <i class="bi bi-list-check me-1"></i> Gerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <form method="POST" action="index.php" class="d-flex flex-column h-100">
                            <div class="card-form-content">
                                <h6 class="card-title">Auditorias por Usuário</h6>
                                <p class="card-text small text-muted">Consulta todas as auditorias por um usuário específico.</p>
                                <div class="mb-3">
                                    <label for="usuario_select_1" class="form-label">Usuário:</label>
                                    <?php $this->selectUsuario($id_usuario = null, 'id="usuario_select_1" required'); ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-auto" name="auditorias_por_usuario">
                                <i class="bi bi-person-check me-1"></i> Gerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <form method="POST" action="index.php" class="d-flex flex-column h-100">
                            <div class="card-form-content">
                                <h6 class="card-title">Auditorias por Ação</h6>
                                <p class="card-text small text-muted">Filtra auditorias pelo tipo de ação (cadastro, alteração, etc.).</p>
                                <div class="mb-3">
                                    <label for="acao_auditoria_1" class="form-label">Ação:</label>
                                    <select name="acao_auditoria" id="acao_auditoria_1" class="form-select" required>
                                        <option value="" disabled selected>Selecione uma ação</option>
                                        <option value="Cadastro">Cadastro</option>
                                        <option value="Alteracao">Alteração</option>
                                        <option value="Exclusao">Exclusão</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-auto" name="auditorias_por_acao">
                                <i class="bi bi-shield-check me-1"></i> Gerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <form method="POST" action="index.php" class="d-flex flex-column h-100">
                            <div class="card-form-content">
                                <h6 class="card-title">Auditorias por Período</h6>
                                <p class="card-text small text-muted">Filtra auditorias entre duas datas específicas.</p>
                                <div class="mb-2">
                                    <label for="data_inicio_auditoria" class="form-label">De:</label>
                                    <input type="date" name="data_inicio_auditoria" id="data_inicio_auditoria" class="form-control" required />
                                </div>
                                <div class="mb-3">
                                    <label for="data_fim_auditoria" class="form-label">Até:</label>
                                    <input type="date" name="data_fim_auditoria" id="data_fim_auditoria" class="form-control" required />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-auto" name="auditorias_por_periodo">
                                <i class="bi bi-calendar-range me-1"></i> Gerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <form method="POST" action="index.php" class="d-flex flex-column h-100">
                            <div class="card-form-content">
                                <h6 class="card-title">Área + Período</h6>
                                <p class="card-text small text-muted">Filtra por área e período.</p>
                                <div class="mb-2">
                                    <label for="tabela_periodo" class="form-label">Área:</label>
                                    <input type="text" name="tabela_periodo" id="tabela_periodo" class="form-control" placeholder="Nome da Área" required />
                                </div>
                                <div class="mb-2">
                                    <label for="data_inicio_periodo" class="form-label">De:</label>
                                    <input type="date" name="data_inicio_periodo" id="data_inicio_periodo" class="form-control" required />
                                </div>
                                <div class="mb-3">
                                    <label for="data_fim_periodo" class="form-label">Até:</label>
                                    <input type="date" name="data_fim_periodo" id="data_fim_periodo" class="form-control" required />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-auto" name="auditorias_tabela_periodo">
                                <i class="bi bi-table me-1"></i> Gerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <form method="POST" action="index.php" class="d-flex flex-column h-100">
                            <div class="card-form-content">
                                <h6 class="card-title">Usuário + Período</h6>
                                <p class="card-text small text-muted">Filtra por usuário e período.</p>
                                <div class="mb-2">
                                    <label for="usuario_select_3" class="form-label">Usuário:</label>
                                    <?php $this->selectUsuario($id_usuario = null, 'id="usuario_select_3" required'); ?>
                                </div>
                                <div class="mb-2">
                                    <label for="data_inicio_usuario" class="form-label">De:</label>
                                    <input type="date" name="data_inicio_usuario" id="data_inicio_usuario" class="form-control" required />
                                </div>
                                <div class="mb-3">
                                    <label for="data_fim_usuario" class="form-label">Até:</label>
                                    <input type="date" name="data_fim_usuario" id="data_fim_usuario" class="form-control" required />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-auto" name="auditorias_usuario_periodo">
                                <i class="bi bi-person-calendar me-1"></i> Gerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <form method="POST" action="index.php" class="d-flex flex-column h-100">
                            <div class="card-form-content">
                                <h6 class="card-title">Usuário + Ação</h6>
                                <p class="card-text small text-muted">Filtra por usuário e tipo de ação.</p>
                                <div class="mb-2">
                                    <label for="usuario_select_2" class="form-label">Usuário:</label>
                                    <?php $this->selectUsuario($id_usuario = null, 'id="usuario_select_2" required'); ?>
                                </div>
                                <div class="mb-3">
                                    <label for="acao_auditoria_2" class="form-label">Ação:</label>
                                    <select name="acao_usuario" id="acao_auditoria_2" class="form-select" required>
                                        <option value="" disabled selected>Selecione</option>
                                        <option value="Cadastro">Cadastro</option>
                                        <option value="Alteracao">Alteração</option>
                                        <option value="Exclusao">Exclusão</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-auto" name="auditorias_usuario_acao">
                                <i class="bi bi-person-lines-fill me-1"></i> Gerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <form method="POST" action="index.php" class="d-flex flex-column h-100">
                            <div class="card-form-content">
                                <h6 class="card-title">Filtro Completo</h6>
                                <p class="card-text small text-muted">Filtra por usuário, ação e período.</p>
                                <div class="mb-2">
                                    <label for="usuario_select_4" class="form-label">Usuário:</label>
                                    <?php $this->selectUsuario($id_usuario = null, 'id="usuario_select_4" required'); ?>
                                </div>
                                <div class="mb-3">
                                    <label for="acao_auditoria_2" class="form-label">Ação:</label>
                                    <select name="acao_usuario_periodo" id="acao_auditoria_2" class="form-select" required>
                                        <option value="" disabled selected>Selecione</option>
                                        <option value="Cadastro">Cadastro</option>
                                        <option value="Alteracao">Alteração</option>
                                        <option value="Exclusao">Exclusão</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="data_inicio_total" class="form-label">De:</label>
                                    <input type="date" name="data_inicio_total" id="data_inicio_total" class="form-control" required />
                                </div>
                                <div class="mb-3">
                                    <label for="data_fim_total" class="form-label">Até:</label>
                                    <input type="date" name="data_fim_total" id="data_fim_total" class="form-control" required />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-auto" name="auditorias_usuario_acao_periodo">
                                <i class="bi bi-filter-circle-fill me-1"></i> Gerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <?php
                // Se a variável com os dados existir e não estiver vazia, renderiza a tabela
                if (isset($dadosParaView) && !empty($dadosParaView)) {
                    $this->renderizarTabelaDeAuditoria($dadosParaView);
                };
                ?>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalCalendario" tabindex="-1" aria-labelledby="modalCalendarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCalendarioLabel">
                        <i class="fas fa-calendar-alt me-2"></i>Calendário do Mês
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php print $this->Calendario(); ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-3 mt-5 bg-light">
        <p class="mb-1">&copy; <?= date('Y') ?> Sistema de Gerenciamento SG3S. Todos os direitos reservados.</p>
        <p class="mb-0 small">Developed by Heymesson Azêvedo.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script src="assets/js/notificacao.js"></script>
</body>

</html>