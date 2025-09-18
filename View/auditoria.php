<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Sistema de Gerenciamento SG3S</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" href="img/favicon.ico">

    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/relatorio.css">
</head>

<body>
    <?php print $menu; ?>
    <main class="container text-center">
        <div class="text-center mb-4">
            <h1 class="display-9">Sistema de Gerenciamento SG3S</h1>
            <p class="lead">Utilize as opções acima para navegar pelo Sistema</p>
        </div>

        <h4 class="mb-4 text-center">Relatórios de Auditoria</h4>
        <br>
        <!-- Auditorias -->
        <div class="row g-3 justify-content-center">
            <!-- Auditorias Gerais -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Auditorias Gerais</h6>
                    <small class="text-muted">Consulta todas as auditorias registradas no ultimos 7 dias.</small>
                    <button type="submit" class="btn btn-primary mt-2" name="auditorias_gerais">
                        <i class="bi bi-list-check me-1"></i> Gerar
                    </button>
                </form>
            </div>
            <!-- Auditorias por Usuário -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6 class="card-title">Auditorias Gerais</h6>
                    <p class="small text-muted">Consulta todas as auditorias registradas.</p>
                    <div class="mb-2 mt-2">
                        <?php $this->selectUsuario($id_usuario = null) ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="auditorias_por_usuario">
                        <i class="bi bi-person-check me-1"></i> Gerar
                    </button>
                </form>
            </div>

            <!-- Auditorias por Ação -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6 class="card-title">Auditorias por Ação</h6>
                    <p class="small text-muted">Filtra auditorias por tipo de ação.</p>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Ação:</label>
                        <select name="acao_auditoria" class="form-select">
                            <option value="">Todas</option>
                            <option value="INSERT">Inserir</option>
                            <option value="UPDATE">Atualizar</option>
                            <option value="DELETE">Deletar</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="auditorias_por_acao">
                        <i class="bi bi-shield-check me-1"></i> Gerar
                    </button>
                </form>
            </div>
            <!-- Auditorias por Período -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6 class="card-title">Auditorias por Período</h6>
                    <p class="small text-muted">Filtra auditorias entre duas datas.</p>
                    <div class="mb-2 mt-2">
                        <label class="form-label">De:</label>
                        <input type="date" name="data_inicio_auditoria" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Até:</label>
                        <input type="date" name="data_fim_auditoria" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="auditorias_por_periodo">
                        <i class="bi bi-calendar-range me-1"></i> Gerar
                    </button>
                </form>
            </div>

            <!-- Auditorias por Usuário e Ação -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Usuário + Ação</h6>
                    <small class="text-muted">Filtra auditorias por usuário e tipo de ação ao mesmo tempo.</small>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Usuário:</label>
                        <?php $this->selectUsuario($id_usuario = null) ?>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ação:</label>
                        <select name="acao_usuario" class="form-select">
                            <option value="">Todas</option>
                            <option value="INSERT">Inserir</option>
                            <option value="UPDATE">Atualizar</option>
                            <option value="DELETE">Deletar</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="auditorias_usuario_acao">
                        <i class="bi bi-person-lines-fill me-1"></i> Gerar
                    </button>
                </form>
            </div>

            <!-- Auditorias por Tabela e Período -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Tabela + Período</h6>
                    <small class="text-muted">Filtra auditorias de uma área específica em determinado período.</small>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Área:</label>
                        <input type="text" name="tabela_periodo" class="form-control" placeholder="Nome da Área" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">De:</label>
                        <input type="date" name="data_inicio_periodo" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Até:</label>
                        <input type="date" name="data_fim_periodo" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="auditorias_tabela_periodo">
                        <i class="bi bi-table me-1"></i> Gerar
                    </button>
                </form>
            </div>

            <!-- Auditorias por Usuário e Período -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Usuário + Período</h6>
                    <small class="text-muted">Filtra auditorias de um usuário específico entre duas datas.</small>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Usuário:</label>
                        <?php $this->selectUsuario($id_usuario = null) ?>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">De:</label>
                        <input type="date" name="data_inicio_usuario" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Até:</label>
                        <input type="date" name="data_fim_usuario" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="auditorias_usuario_periodo">
                        <i class="bi bi-person-lines-fill me-1"></i> Gerar
                    </button>
                </form>
            </div>

            <!-- Auditorias por Usuário + Ação + Período -->
            <div class="col-md-3">
                <form method="POST" action="index.php" class="relatorio-form text-start">
                    <h6>Usuário + Ação + Período</h6>
                    <small class="text-muted">Filtra auditorias de um usuário, tipo de ação e intervalo de datas.</small>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Usuário:</label>
                        <?php $this->selectUsuario($id_usuario = null) ?>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ação:</label>
                        <select name="acao_usuario_periodo" class="form-select">
                            <option value="">Todas</option>
                            <option value="INSERT">Inserir</option>
                            <option value="UPDATE">Atualizar</option>
                            <option value="DELETE">Deletar</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">De:</label>
                        <input type="date" name="data_inicio_total" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Até:</label>
                        <input type="date" name="data_fim_total" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" name="auditorias_usuario_acao_periodo">
                        <i class="bi bi-shield-check me-1"></i> Gerar
                    </button>
                </form>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <?php $this->tabelaAuditoria($todas_auditorias); ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Calendário -->
    <div class="modal fade" id="modalCalendario" tabindex="-1" aria-labelledby="modalCalendarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCalendarioLabel">
                        <i class="fas fa-calendar-alt me-2"></i>Calendário do Mês
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php print $this->Calendario(); ?>
                </div>
            </div> <!-- fecha modal-content -->
        </div> <!-- fecha modal-dialog -->
    </div> <!-- fecha modal -->

    <footer class="text-center py-1 mt-6 bg-light">
        <p>&copy; <?= date('Y') ?> Sistema de Gerenciamento SG3S. Todos os direitos reservados.</p>
        <p>Developed by Heymesson Azêvedo.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

    <!-- Ajax de produtos com baixo estoque -->
    <script src="assets/js/notificacao.js"></script>
</body>

</html>