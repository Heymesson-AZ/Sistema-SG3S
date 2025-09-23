<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

// classe de controle
class Controller
{
    // metodo de redirecionar paginas no sistema
    public function redirecionar($pagina)
    {
        session_start();
        $menu = $this->menu();
        include_once 'view/' . $pagina . '.php';
    }
    // calendario
    public function calendario()
    {
        date_default_timezone_set('America/Sao_Paulo');

        $diaAtual = date('j');
        $mesAtual = date('n');
        $anoAtual = date('Y');
        $dataHoraAtual = date('d/m/Y H:i');

        $meses = [
            1 => 'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro'
        ];
        $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        $primeiroDia = mktime(0, 0, 0, $mesAtual, 1, $anoAtual);
        $diasNoMes = date('t', $primeiroDia);
        $diaSemana = date('w', $primeiroDia);

        $html = "<div class='container-fluid'>";
        $html .= "<div class='text-center mb-3'>";
        $html .= "<h4 class='text-primary'>{$meses[$mesAtual]} de $anoAtual</h4>";
        $html .= "<p class='text-muted'><i class='far fa-clock me-1'></i>$dataHoraAtual</p>";
        $html .= "</div>";

        $html .= "<div class='d-flex justify-content-center'>";
        $html .= "<table class='table table-bordered text-center shadow-sm' style='width: 100%; max-width: 700px; font-size: 1rem;'>";
        $html .= "<thead class='table-light'><tr>";
        foreach ($diasSemana as $i => $dia) {
            $classe = ($i == 0 || $i == 6) ? 'text-danger fw-semibold' : 'fw-semibold';
            $html .= "<th class='$classe py-2'>$dia</th>";
        }
        $html .= "</tr></thead><tbody><tr>";

        for ($i = 0; $i < $diaSemana; $i++) {
            $html .= "<td class='py-3'></td>";
        }

        for ($dia = 1; $dia <= $diasNoMes; $dia++) {
            $classe = ($dia == $diaAtual) ? 'bg-warning text-dark fw-bold rounded-2' : '';
            $html .= "<td class='$classe py-3'>$dia</td>";

            $diaSemana++;
            if ($diaSemana == 7) {
                $diaSemana = 0;
                $html .= "</tr><tr>";
            }
        }

        while ($diaSemana > 0 && $diaSemana < 7) {
            $html .= "<td class='py-3'></td>";
            $diaSemana++;
        }

        $html .= "</tr></tbody></table></div></div>";

        return $html;
    }
    // metodo do layout do pdf de pedidos
    public function gerarPdfPedidos(array $pedidos): void
    {
        if (empty($pedidos)) {
            echo "Nenhum pedido para gerar PDF.";
            return;
        }
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');

        $dataHoraGeracao = date('d/m/Y H:i:s');

        // Agrupar itens por pedido
        $pedidosAgrupados = [];
        foreach ($pedidos as $pedido) {
            $id = $pedido->id_pedido;
            // Agrupar pedidos pelo ID
            if (!isset($pedidosAgrupados[$id])) {
                $pedidosAgrupados[$id] = [
                    'dados' => $pedido,
                    'itens' => [],
                ];
            }
            // Agrupar itens pelo ID do pedido
            if (!empty($pedido->id_item_pedido)) {
                $pedidosAgrupados[$id]['itens'][] = [
                    'nome_produto'    => $pedido->nome_produto ?? '',
                    'unidade_medida'  => $pedido->unidade_medida ?? 'un',
                    'valor_unitario'  => $pedido->valor_unitario ?? 0,
                    'quantidade'      => $pedido->quantidade ?? 0,
                    'status_pedido' => $pedido->status_pedido ?? '',
                ];
            }
        }
        // HTML estilizado
        $html = '<!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: DejaVu Sans, Arial, sans-serif;
                    font-size: 12px;
                    color: #333;
                    margin: 20px;
                }
                .topo {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }
                .topo .legenda {
                    font-size: 16px;
                    font-weight: bold;
                    color: #2E86C1;
                }
                .topo .data-hora {
                    font-size: 11px;
                    color: #666;
                }
                .pedido-info {
                    margin-bottom: 10px;
                }
                .pedido-info div {
                    margin-bottom: 4px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                th, td {
                    border: 1px solid #bbb;
                    padding: 8px;
                    text-align: left;
                    vertical-align: top;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 11px;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .valor-total {
                    text-align: right;
                    font-weight: bold;
                    font-size: 13px;
                    margin-top: 15px;
                    color: #1E8449;
                }
                hr {
                    margin: 30px 0;
                    border: none;
                    border-top: 1px solid #ccc;
                }
            </style>
        </head>
        <body>';
        $html .= '<div class="topo">
            <div class="legenda">Pedido</div>
            <div class="data-hora">Gerado em: ' . $dataHoraGeracao . '</div>
        </div>';

        foreach ($pedidosAgrupados as $pedidoAgrupado) {
            $pedido = $pedidoAgrupado['dados'];
            $itens = $pedidoAgrupado['itens'];
            // mascara do cnpj
            $pedido->cnpj_cliente = $this->aplicarMascaraCNPJ($pedido->cnpj_cliente);
            $html .= '<div class="pedido-info">
                <div><strong>Número do Pedido:</strong> ' . htmlspecialchars($pedido->numero_pedido ?? '', ENT_QUOTES, 'UTF-8') . '</div>
                <div><strong>Status do Pedido:</strong> ' . htmlspecialchars($pedido->status_pedido ?? '', ENT_QUOTES, 'UTF-8') . '</div>
                <div><strong>Cliente:</strong> ' . htmlspecialchars($pedido->nome_fantasia ?? '', ENT_QUOTES, 'UTF-8') . '</div>
                <div><strong>CNPJ do Cliente:</strong> ' . htmlspecialchars($pedido->cnpj_cliente ?? '', ENT_QUOTES, 'UTF-8') . '</div>
            </div>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th>Itens</th>
                        <th>Unidade</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário</th>
                        <th>Total por Produto</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($itens as $item) {
                $valorUnitario = (float)$item['valor_unitario'];
                $quantidade = (float)$item['quantidade'];
                $totalProduto = $valorUnitario * $quantidade;
                $html .= '<tr>
                    <td>' . htmlspecialchars($item['nome_produto'], ENT_QUOTES, 'UTF-8') . '</td>;
                    <td>m</td>;
                    <td>' . $quantidade . '</td>;
                    <td>R$ ' . number_format($valorUnitario, 2, ',', '.') . '</td>;
                    <td>R$ ' . number_format($totalProduto, 2, ',', '.') . '</td>
                </tr>';
            }
            $html .= '</tbody></table>';

            $html .= '<div class="valor-total">Valor Total do Pedido: R$ ' .
                number_format((float)($pedido->valor_total ?? 0), 2, ',', '.') . '</div><hr>';
        }

        $html .= '</body></html>';

        // Configurações do Dompdf
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');

        if (ob_get_length()) {
            ob_end_clean();
        }
        // Gera o PDF
        header('Content-Type: application/pdf');
        // essa parte e o nome
        header('Content-Disposition: inline; filename="pedidos_venda.' . $pedido->numero_pedido . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        $dompdf->render();
        echo $dompdf->output();
        exit;
    }
    // medoto de gerar pdf
    public function imprimirPedido($numero_pedido)
    {
        $objPedido = new Pedido();
        if ($objPedido->buscarPedidosPorNumero($numero_pedido) == true) {
            $pedidos = $objPedido->buscarPedidosPorNumero($numero_pedido);

            $this->gerarPdfPedidos($pedidos);
        } else {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro a buscar pedido");
            // Inclui a mesma view
            include_once 'view/principa.php';
        }
    }
    // validar usuario
    public function validar($cpf, $senha)
    {
        // Instancia a classe Usuario
        $objUsuario = new Usuario();
        // Valida o usuário usando o método que revisamos
        $resultado = $objUsuario->validarLogin($cpf, $senha);
        if ($resultado['validado'] === true) {
            session_start();
            // Seta variáveis de sessão
            $_SESSION['usuario']    = $resultado['nome'];
            $_SESSION['perfil']     = $resultado['perfil'];
            $_SESSION['id_usuario'] = $resultado['id_usuario'];
            // Monta o menu
            $menu = $this->menu();
            // Carrega a view principal
            include_once 'View/principal.php';
        } else {
            // Usuário ou senha inválidos: volta para login com mensagem
            // limpando o chache do navegador
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");

            include_once 'login.php';
            $this->mostrarMensagemErro("Login ou senha inválida");
        }
    }

    // validar sessao
    public function validarSessao()
    {
        // limpando o chache do navegador
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Verifica se a sessão está iniciada
        if (!isset($_SESSION['usuario']) and !isset($_SESSION['perfil'])) {
            //acesso negado
            header("location: login.php");
        }
    }
    // validar cpf
    function validarCPF($cpf)
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Evita CPFs com todos os dígitos iguais (ex: 00000000000)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Validação dos dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ($cpf[$t] != $digito) {
                return false;
            }
        }
        return true;
    }
    // validar cnpj
    function validarCNPJ($cnpj)
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/\D/', '', $cnpj);

        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Evita CNPJs com todos os dígitos iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Validação dos dígitos verificadores
        $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        // Calcula o primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $pesos1[$i];
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        // Calcula o segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $pesos2[$i];
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        // Verifica se os dígitos conferem
        if ($cnpj[12] != $digito1 || $cnpj[13] != $digito2) {
            return false;
        }

        return true;
    }
    // permissoes do sistema
    public function temPermissao($perfisPermitidos)
    {
        $perfilAtual = strtolower(trim($_SESSION['perfil']));
        $perfisPermitidos = array_map('strtolower', $perfisPermitidos);
        return in_array($perfilAtual, $perfisPermitidos);
    }
    // sair do sistema
    public function logout()
    {
        // limpando o chache do navegador
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Verifica se a sessão está iniciada
        if (!isset($_SESSION['usuario']) and !isset($_SESSION['perfil'])) {
            //acesso negado
            header("location: login.php");
        }

        // Limpa as variáveis de sessão
        unset($_SESSION['perfil']);
        unset($_SESSION['usuario']);
        unset($_POST['cpf_cadastro']);
        unset($_SESSION['cnpj_cadastro']);
        unset($_SESSION['produto']);
        unset($_SESSION['cnpj_cliente']);
        unset($_SESSION['mensagem_erro']);
        unset($_SESSION['mensagem_sucesso']);
        unset($_SESSION['id_usuario']);

        session_destroy(); // Destrói a sessão

        // Limpa o cache do navegador
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        // Redireciona para a página de login
        header("Location: login.php");
        exit();
    }
    // modal de mensagem de sucesso
    public function mostrarMensagemSucesso($mensagem)
    {
        print '<div class="modal fade" id="mensagemModal" tabindex="-1" aria-labelledby="mensagemLabel" aria-hidden="true">';
        print '  <div class="modal-dialog modal-dialog-centered modal-default">';
        print '    <div class="modal-content shadow rounded-4 border-success">';
        print '      <div class="modal-header bg-success text-white rounded-top-4">';
        print '        <h5 class="modal-title d-flex align-items-center" id="mensagemLabel">';
        print '          <i class="bi bi-check-circle-fill me-2 fs-4"></i> Sucesso';
        print '        </h5>';
        print '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '      </div>';
        print '      <div class="modal-body text-center fs-6">';
        print '        <div class="alert alert-success mb-0 border-0 bg-opacity-75">';
        print            $mensagem;
        print '        </div>';
        print '      </div>';
        print '    </div>';
        print '  </div>';
        print '</div>';
        print '<script>';
        print '  document.addEventListener("DOMContentLoaded", function() {';
        print '    var modalElement = document.getElementById("mensagemModal");';
        print '    var myModal = new bootstrap.Modal(modalElement);';
        print '    myModal.show();';
        print '    setTimeout(function () {';
        print '      myModal.hide();';
        print '    }, 2500);';
        print '  });';
        print '</script>';
    }
    // modal de mensagem de erro
    public function mostrarMensagemErro($mensagem)
    {
        print '<div class="modal fade" id="mensagemModal" tabindex="-1" aria-labelledby="mensagemLabel" aria-hidden="true">';
        print '  <div class="modal-dialog modal-dialog-centered modal-default">';
        print '    <div class="modal-content shadow rounded-4 border-danger">';
        print '      <div class="modal-header bg-danger text-white rounded-top-4">';
        print '        <h5 class="modal-title d-flex align-items-center" id="mensagemLabel">';
        print '          <i class="bi bi-x-circle-fill me-2 fs-4"></i> Erro';
        print '        </h5>';
        print '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '      </div>';
        print '      <div class="modal-body text-center fs-6">';
        print '        <div class="alert alert-danger mb-0 border-0 bg-opacity-75">';
        print            $mensagem;
        print '        </div>';
        print '      </div>';
        print '    </div>';
        print '  </div>';
        print '</div>';
        print '<script>';
        print '  document.addEventListener("DOMContentLoaded", function() {';
        print '    var modalElement = document.getElementById("mensagemModal");';
        print '    var myModal = new bootstrap.Modal(modalElement);';
        print '    myModal.show();';
        print '    setTimeout(function () {';
        print '      myModal.hide();';
        print '    }, 2500);';
        print '  });';
        print '</script>';
    }

    //menu dinamico
    public function menu()
    {
        print '<header class="bg-primary shadow-sm">';
        print '    <nav class="container navbar navbar-expand-lg navbar-dark py-2">';
        // Logo
        print '        <a class="navbar-brand fw-bold text-white d-flex align-items-center" href="index.php?principal">';
        print '            <i class="fas fa-chart-line me-2"></i> Sistema SG3S';
        print '        </a>';

        // Botão Mobile (com atributos de acessibilidade)
        print '        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Alternar navegação">';
        print '            <span class="navbar-toggler-icon"></span>';
        print '        </button>';

        print '        <div class="collapse navbar-collapse" id="navbarNav">';
        print '            <ul class="navbar-nav d-flex align-items-center gap-2">';

        // Links principais
        $links = [
            ['principal', 'fas fa-home', 'Início'],
            ['cliente', 'fas fa-user', 'Cliente'],
            ['produto', 'fas fa-box', 'Produto']
        ];

        foreach ($links as $link) {
            print '                <li class="nav-item">';
            print '                    <a href="index.php?' . $link[0] . '" class="btn btn-outline-light fw-semibold"><i class="' . $link[1] . ' me-1"></i> ' . $link[2] . '</a>';
            print '                </li>';
        }

        // Links administrativos (somente se tiver permissão)
        if ($this->temPermissao(['Administrador'])) {
            $adminLinks = [
                ['usuario', 'fas fa-users', 'Usuário'],
                ['pedido', 'fas fa-shopping-bag', 'Pedidos'],
                ['relatorios', 'fas fa-warehouse', 'Relatórios'],
                ['auditoria', 'fas fa-search', 'Auditoria']
            ];
            foreach ($adminLinks as $link) {
                print '                <li class="nav-item">';
                print '                    <a href="index.php?' . $link[0] . '" class="btn btn-outline-light fw-semibold"><i class="' . $link[1] . ' me-1"></i> ' . $link[2] . '</a>';
                print '                </li>';
            }
        }

        // Calendário
        print '                <li class="nav-item">';
        print '                    <a href="#" class="btn btn-outline-light fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCalendario">';
        print '                        <i class="fas fa-calendar-alt me-1"></i> Calendário';
        print '                    </a>';
        print '                </li>';

        print '            </ul>';

        // Container final (notificações + usuário)
        print '            <div class="ms-auto d-flex align-items-center gap-3">';

        // Notificações
        print '                <div class="nav-item dropdown">';
        print '                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
        print '                        <i class="fas fa-bell fa-lg text-white"></i>';
        print '                        <span id="contadorNotificacoes" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" style="font-size: 0.65rem; display:none;">0</span>';
        print '                    </a>';
        print '                    <ul id="listaNotificacoes" class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="width: 420px; max-height: 600px; overflow-y: auto;">';
        print '                    </ul>';
        print '                </div>';

        // Linha separadora (apenas em telas grandes)
        print '                <div class="vr d-none d-lg-block"></div>';

        // Usuário
        $usuarioNome = htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8');
        print '                <div class="dropdown">';
        print '                    <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="usuarioDropdown" data-bs-toggle="dropdown" aria-expanded="false">';
        print '                        <img src="https://ui-avatars.com/api/?name=' . urlencode($usuarioNome) . '&background=ffffff&color=0d6efd&rounded=true&size=32" alt="avatar" class="rounded-circle me-2" />';
        print '                        <strong>' . $usuarioNome . '</strong>';
        print '                    </a>';
        print '                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="usuarioDropdown">';
        print '                        <li><a class="dropdown-item" href="index.php?sair"><i class="fas fa-sign-out-alt me-2 text-danger"></i> Sair</a></li>';
        print '                    </ul>';
        print '                </div>';
        print '            </div>'; // fecha container notificações + usuário
        print '        </div>'; // fecha collapse
        print '    </nav>';
        print '</header>';
    }

    // USUARIO

    // funcao de envio de email
    function enviarEmailRecuperacao($email, $senha)
    {
        $mail = new PHPMailer(true);
        try {
            // SMTP básico
            $mail->isSMTP();
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = getenv('SMTP_SECURE');
            $mail->Host       = getenv('SMTP_HOST');
            $mail->Port       = getenv('SMTP_PORT');

            // Credenciais (do .env)
            $mail->Username   = getenv('SMTP_USER');
            $mail->Password   = getenv('SMTP_PASS');

            // Remetente
            $mail->setFrom(
                getenv('MAIL_FROM'),
                getenv('MAIL_FROM_NAME')
            );

            // Destinatário e conteúdo
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha - Sistema SG3S';
            $mail->Body    = "<p>Olá,</p><p>Sua nova senha é: <strong>{$senha}</strong></p>";
            $mail->CharSet = 'UTF-8';
            $mail->send();
            return true;
            exit();
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail de recuperação: {$mail->ErrorInfo}");
            return false;
        }
    }
    // funcao de recuperar senha
    public function recuperarSenha($email)
    {
        $objUsuario = new Usuario();
        //valida e-mail
        $objUsuario->validarEmail($email);
        //verifica se o e-mail existe
        if ($objUsuario->validarEmail($email) == false) {
            $this->mostrarMensagemErro("E-mail não cadastrado!");
            include_once 'recuperarSenha.php';
            exit();
        }
        //gera senha temporária de 8 dígitos
        $senha = random_int(1, 9) . str_pad(random_int(0, 9999999), 6, '0', STR_PAD_LEFT);
        $objUsuario->alterarSenhaRecuperacao($email, $senha);
        //verifica se a senha foi alterada
        if ($objUsuario->alterarSenhaRecuperacao($email, $senha) == false) {
            $this->mostrarMensagemErro("Erro ao alterar senha!");
            include_once 'recuperarSenha.php';
            exit();
        }
        //verifica se o e-mail foi enviado
        $this->enviarEmailRecuperacao($email, $senha);
        if ($this->enviarEmailRecuperacao($email, $senha) == false) {
            $this->mostrarMensagemErro("Erro ao enviar e-mail!");
            include_once 'recuperarSenha.php';
            exit();
        } else {
            //mostra a mensagem de sucesso
            $this->mostrarMensagemSucesso("Senha enviada para o e-mail cadastrado!");
            //redireciona para a página de login
            include_once 'login.php';
            exit();
        }
    }
    // verificar se o usuario ja existe
    public function consultarUsuario_Cpf($cpf)
    {
        // instanciando a classe usuario
        $objUsuario = new Usuario();
        $objUsuario->consultarUsuarioCpf($cpf);
        // verficando a existencia no banco de dados
        if ($objUsuario->consultarUsuarioCpf($cpf) == true) {
            // iniciando a sessao
            session_start();
            // incluido menu
            $menu = $this->menu();
            // incluido a view do usuario
            include_once 'view/usuario.php';
            // exibindo mensagem de erro
            $this->mostrarMensagemErro("Usuário já cadastrado");
        } else {
            // iniciando a sessao
            session_start();
            // incluido menu
            $menu = $this->menu();
            // pegando o valor do parametro cpf
            $_SESSION['cpf_cadastro'] = $cpf;
            include_once 'view/usuario.php';
        }
    }
    // modal de cadastro de usuario
    public function modal_cadastroUsuario()
    {
        print '<div class="modal fade" id="modal_usuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-lg modal-dialog-centered">';
        print '<div class="modal-content">';

        // Cabeçalho da modal
        print '<div class="modal-header">';
        print '<h6 class="modal-title" id="modalUsuarioLabel">Novo Usuário</h6>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '</div>';

        // Corpo da modal
        print '<div class="modal-body">';
        print '<form action="index.php" method="POST" id="formulario_usuario">';
        print '<input type="hidden" name="origem" value="usuario">';
        print '<div class="row g-2">';

        // Seção Dados Pessoais
        print '<div class="col-md-12">';
        print '<fieldset class="border border-black p-1 mb-4">';
        print '<legend class="float-none w-auto px-2">Dados Pessoais</legend>';
        print '<div class="row g-2">';

        print '<div class="col-md-6">';
        print '<label for="nome_usuario" class="form-label">Nome *</label>';
        print '<input type="text" class="form-control" name="nome_usuario" id="nome_usuario"
            required autocomplete="off" placeholder="Digite o nome completo"
            pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"
            title="Somente letras e espaços são permitidos">';
        print '</div>';

        print '<div class="col-md-6">';
        $this->select_perfilUsuario();
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="telefone" class="form-label">Telefone *</label>';
        print '<input type="tel" class="form-control" id="telefone" name="telefone"
            required autocomplete="off" placeholder="(00) 00000-0000"
            pattern="\(\d{2}\) \d{4,5}-\d{4}" title="Formato esperado: (XX) XXXXX-XXXX">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="email_usuario" class="form-label">Email *</label>';
        print '<input type="email" class="form-control" id="email_usuario" name="email_usuario"
            required autocomplete="off" placeholder="exemplo@dominio.com">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="cpf_usuario" class="form-label">CPF *</label>';
        print '<input type="text" class="form-control" id="cpf" name="cpf" value="' . $_SESSION['cpf_cadastro'] . '"
                required autocomplete="off" placeholder="000.000.000-00"
                pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" title="Formato esperado: XXX.XXX.XXX-XX">';
        print '</div>';

        print '</div>'; // fecha row
        print '</fieldset>';
        print '</div>'; // fecha col-md-12

        // Seção Senha
        print '<div class="col-md-12">';
        print '<fieldset class="border border-black p-1 mb-4">';
        print '<legend class="float-none w-auto px-2">Segurança</legend>';
        print '<div class="row g-2">';

        print '<div class="col-md-6">';
        print '<label for="senha_usuario" class="form-label">Senha *</label>';
        print '<input type="password" class="form-control" id="senha_usuario" name="senha"
            required autocomplete="new-password" placeholder="Mínimo 6 caracteres"
            pattern=".{6,}" title="A senha deve ter pelo menos 6 caracteres">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="confirma_senha_usuario" class="form-label">Confirme a Senha *</label>';
        print '<input type="password" class="form-control" id="confSenha" name="confSenha"
            required autocomplete="new-password" placeholder="Confirme sua senha"
            pattern=".{6,}" title="A senha deve ter pelo menos 6 caracteres">';
        print '</div>';

        print '</div>'; // fecha row
        print '</fieldset>';
        print '</div>'; // fecha col-md-12

        print '</div>'; // fecha .row g-4
        print '</div>'; // fecha .modal-body

        // Rodapé da modal com botões
        print '<div class="modal-footer">';
        print '<div class="container-fluid">';
        print '<div class="row g-2">';
        print '<div class="col-md-6">';
        print '<button type="reset" class="btn btn-outline-secondary w-100 py-2">';
        print '<i class="bi bi-arrow-counterclockwise"></i> Limpar';
        print '</button>';
        print '</div>';
        print '<div class="col-md-6">';
        print '<button type="submit" name="cadastrar_usuario" class="btn btn-success w-100 py-2">';
        print '<i class="bi bi-check-circle"></i> Cadastrar';
        print '</button>';
        print '</div>';
        print '</div>'; // fecha row
        print '</div>'; // fecha container-fluid
        print '</div>'; // fecha modal-footer
        print '</form>';
        print '</div>'; // fecha modal-content
        print '</div>'; // fecha modal-dialog
        print '</div>'; // fecha modal
        // Script para abrir a modal automaticamente
        print '<script>';
        print '  document.addEventListener("DOMContentLoaded", function() {';
        print '    const modalUsuario = document.getElementById("modal_usuario");';
        // o getOrCreateInstance é usado para garantir que a instância da modal seja criada ou recuperada
        print '    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalUsuario);';
        // Exibe a modal
        print '    modalInstance.show();';
        print '  });';
        print '</script>';
    }
    // cadastrar usuario
    public function cadastrar_Usuario($nome_usuario, $email_usuario, $senhaHash, $id_perfil, $telefone, $cpf)
    {
        $objUsuario = new Usuario();
        // Validação do CPF
        if ($this->validarCPF($cpf) == false) {
            session_start();
            $menu = $this->menu();
            include_once 'view/usuario.php';
            $this->mostrarMensagemErro("CPF inválido");
            exit();
        } else {
            // Invocar o método da classe Usuario para cadastrar o usuário
            if ($objUsuario->cadastrarUsuario($nome_usuario, $email_usuario, $senhaHash, $id_perfil, $telefone, $cpf) == true) {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/usuario.php';
                // Exibir mensagem de sucesso
                $this->mostrarMensagemSucesso("Usuário cadastrado com sucesso");
                exit();
            } else {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/usuario.php';
                // Exibir mensagem de erro
                $this->mostrarMensagemErro("Erro ao cadastrar usuário");
                exit();
            }
        }
    }
    // consultar usuario
    public function consultar_Usuario($nome_usuario, $id_perfil)
    {
        // Instancia a classe Usuario
        $objUsuario = new Usuario();
        // Executa a consulta e armazena o resultado
        $objUsuario->consultarUsuario($nome_usuario, $id_perfil);
        // Verifica se há resultados
        if ($objUsuario->consultarUsuario($nome_usuario, $id_perfil) == true) {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $resultado = $objUsuario->consultarUsuario($nome_usuario, $id_perfil);
            // menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/usuario.php';
        } else {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Usuário não encontrado");
            // Inclui a mesma view
            include_once 'view/usuario.php';
        }
    }
    // =======================
    // ALTERAR USUÁRIO
    // =======================
    public function alterar_Usuario($id_usuario, $nome_usuario, $email, $id_perfil, $cpf, $telefone)
    {
        $objUsuario = new Usuario();

        // Validação do CPF
        if ($this->validarCPF($cpf) == false) {
            session_start();
            $menu = $this->menu();
            include_once 'view/usuario.php';
            $this->mostrarMensagemErro("CPF inválido");
            return;
        }

        // Chama o método do model
        $retorno = $objUsuario->alterarUsuario($id_usuario, $nome_usuario, $email, $id_perfil, $cpf, $telefone);

        session_start();
        $menu = $this->menu();
        include_once 'view/usuario.php';

        if ($retorno === true) {
            $this->mostrarMensagemSucesso("Usuário alterado com sucesso");
        } else {
            $this->mostrarMensagemErro($retorno);
        }
    }
    // EXCLUIR USUÁRIO
    public function excluir_Usuario($id_usuario)
    {
        $objUsuario = new Usuario();

        // Chama o método do model
        $retorno = $objUsuario->excluirUsuario($id_usuario);

        session_start();
        $menu = $this->menu();
        include_once 'view/usuario.php';

        if ($retorno === true) {
            $this->mostrarMensagemSucesso("Usuário excluído com sucesso");
        } else {
            $this->mostrarMensagemErro($retorno);
        }
    }
    // tabela de consulta de Usuario
    public function tabelaConsultaUsuario($resultado)
    {
        if (empty($resultado)) return;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-striped table-hover table-bordered align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Nome</th>';
        print '<th scope="col">Email</th>';
        print '<th scope="col">Telefone</th>';
        print '<th scope="col">Perfil</th>';
        print '<th scope="col">Ações</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        foreach ($resultado as $valor) {
            $nomeUsuario = explode(" ", $valor->nome_usuario)[0];

            print '<tr>';
            print '<td>' . $nomeUsuario . '</td>';
            print '<td>' . $valor->email . '</td>';
            print '<td>' . $this->aplicarMascaraTelefone($valor->telefone) . '</td>';
            print '<td>' . $valor->perfil_usuario . '</td>';
            print '<td>';
            print '<div class="d-flex gap-2 justify-content-center flex-wrap">';
            print '<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#alterar_usuario' . $valor->id_usuario . '"><i class="bi bi-pencil-square"></i></button>';
            print '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#excluir_usuario' . $valor->id_usuario . '"><i class="bi bi-trash"></i></button>';
            print '<button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#alterar_senha' . $valor->id_usuario . '"><i class="bi bi-key"></i></button>';
            print '</div>';
            print '</td>';
            print '</tr>';
        }

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // modal de alterar usuario
    public function modal_AlterarUsuario($id_usuario, $nome_usuario, $email, $id_perfil, $cpf, $telefone)
    {
        print '<div class="modal fade" id="alterar_usuario' . $id_usuario . '" tabindex="-1" aria-labelledby="alterarUsuarioLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-lg modal-dialog-centered">';
        print '<div class="modal-content">';

        // Cabeçalho da modal
        print '<div class="modal-header">';
        print '<h6 class="modal-title" id="modalUsuarioAlterarLabel">Alterar Usuário</h6>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '</div>';

        // Corpo da modal
        print '<div class="modal-body">';
        print '<form action="index.php" method="POST" id="formulario_usuario_alterar_' . $id_usuario . '">';
        print '<input type="hidden" name="origem" value="usuario">';
        print '<input type="hidden" name="id_usuario" value="' . $id_usuario . '">';
        print '<div class="row g-2">';

        // Seção Dados Pessoais
        print '<div class="col-md-12">';
        print '<fieldset class="border border-black p-1 mb-4">';
        print '<legend class="float-none w-auto px-2">Dados Pessoais</legend>';
        print '<div class="row g-2">';

        print '<div class="col-md-6">';
        print '<label for="nome_usuario_alterar" class="form-label">Nome *</label>';
        print '<input type="text" class="form-control" name="nome_usuario" id="nome_usuario_alterar"
        required autocomplete="off" placeholder="Digite o nome completo"
        pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"
        title="Somente letras e espaços são permitidos"
        value="' . htmlspecialchars($nome_usuario) . '">';
        print '</div>';


        if ($id_usuario != $_SESSION['id_usuario']) {
            print '<div class="col-md-6">';
            $this->select_perfilUsuario($id_perfil);
            print '</div>';
        }
        print '<div class="col-md-6">';
        print '<label for="telefone_alterar" class="form-label">Telefone *</label>';
        print '<input type="tel" class="form-control" id="telefone_alterar" name="telefone"
        required autocomplete="off" placeholder="(00) 00000-0000"
        pattern="\\(\\d{2}\\) \\d{4,5}-\\d{4}" title="Formato esperado: (XX) XXXXX-XXXX"
        value="' . htmlspecialchars($telefone) . '">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="email_usuario_alterar" class="form-label">Email *</label>';
        print '<input type="email" class="form-control" id="email" name="email"
        required autocomplete="off" placeholder="exemplo@dominio.com"
        value="' . htmlspecialchars($email) . '">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="cpf_usuario_alterar" class="form-label">CPF *</label>';
        print '<input type="text" class="form-control" id="cpf_usuario_alterar" name="cpf"
        required autocomplete="off" placeholder="000.000.000-00"
        pattern="\\d{3}\\.\\d{3}\\.\\d{3}-\\d{2}" title="Formato esperado: XXX.XXX.XXX-XX"
        value="' . htmlspecialchars($cpf) . '">';
        print '</div>';

        print '</div>'; // fecha row
        print '</fieldset>';
        print '</div>'; // fecha col-md-12
        print '</div>'; // fecha .row g-2
        print '</div>'; // fecha .modal-body
        // Rodapé da modal com botões
        print '<div class="modal-footer">';
        print '<div class="container-fluid">';
        print '<div class="row g-2">';
        print '<div class="col-md-6">';
        print '<button type="reset" class="btn btn-outline-secondary w-100 py-2" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-circle"></i> Cancelar';
        print '</button>';
        print '</div>';
        print '<div class="col-md-6">';
        print '<button type="submit" name="alterar_usuario" class="btn btn-primary w-100 py-2">';
        print '<i class="bi bi-check-circle"></i> Alterar';
        print '</button>';
        print '</div>';
        print '</div>'; // fecha row
        print '</div>'; // fecha container-fluid
        print '</div>'; // fecha modal-footer

        print '</form>';
        print '</div>'; // fecha modal-content
        print '</div>'; // fecha modal-dialog
        print '</div>'; // fecha modal
    }
    // modal de excluir usuario
    public function modalExcluirUsuario($id_usuario, $nome_usuario)
    {
        print '<div class="modal fade" id="excluir_usuario' . $id_usuario . '" tabindex="-1" aria-labelledby="excluirUsuarioLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content">';
        print '<div class="modal-header">';
        print '<h5 class="modal-title" id="excluirUsuarioLabel">Excluir Usuário</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '</div>';
        print '<div class="modal-body">';
        print 'Tem certeza que deseja excluir o usuário <strong>' . $nome_usuario . '</strong>?';
        print '</div>';
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer">';
        print '<input type="hidden" name="id_usuario" value="' . $id_usuario . '">';
        print '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
        print '<button type="submit" name="excluir_usuario" class="btn btn-danger">Excluir</button>';
        print '</div>';
        print '</form>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
    }
    // metodo de alterar a senha
    public function alterar_Senha($id_usuario, $nova_senha)
    {
        // Instancia a classe Usuario
        $objUsuario = new Usuario();
        // Tenta alterar a senha
        if ($objUsuario->alterarSenha($id_usuario, $nova_senha) == true) {
            // Inicia a sessão
            session_start();
            $menu = $this->menu();
            include_once 'view/usuario.php';
            $this->mostrarMensagemSucesso("Senha alterada com sucesso");
        } else {
            // Inicia a sessão
            session_start();
            $menu = $this->menu();
            include_once 'view/usuario.php';
            $this->mostrarMensagemErro("Senha Atual invalida");
        }
    }
    // modal de alterar senha
    public function modalAlterarSenha($id_usuario, $nome_usuario)
    {
        print '<div class="modal fade" id="alterar_senha' . $id_usuario . '" tabindex="-1" aria-labelledby="alterarSenhaLabel' . $id_usuario . '" aria-hidden="true">';
        print '  <div class="modal-dialog modal-dialog-centered">';
        print '    <div class="modal-content rounded-3 shadow-sm">';

        // Cabeçalho
        print '      <div class="modal-header bg-primary text-white">';
        print '        <h5 class="modal-title" id="alterarSenhaLabel' . $id_usuario . '"><i class="bi bi-lock-fill me-2"></i>Alterar Senha</h5>';
        print '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '      </div>';

        // Corpo
        print '      <div class="modal-body px-4 py-4">';
        print '        <form action="index.php" method="post">';

        print '          <h6 class="text-muted text-uppercase mb-3">Nova senha para:</h6>';
        print '          <div class="alert alert-secondary text-center fw-bold mb-4">' . $nome_usuario . '</div>';

        // Campo senha
        print '          <div class="mb-3">';
        print '            <label for="senha' . $id_usuario . '" class="form-label">Nova senha*</label>';
        print '            <div class="input-group">';
        print '              <input type="password" class="form-control form-control-lg" id="senha' . $id_usuario . '" name="senha" required>';
        print '              <span class="input-group-text bg-white" style="cursor: pointer;" onclick="toggleSenha(' . $id_usuario . ', false)">';
        print '                <i class="fas fa-eye" id="toggleSenhaIcon' . $id_usuario . '"></i>';
        print '              </span>';
        print '            </div>';
        print '          </div>';

        // Confirmar senha
        print '          <div class="mb-4">';
        print '            <label for="confSenha' . $id_usuario . '" class="form-label">Confirmar nova senha*</label>';
        print '            <div class="input-group">';
        print '              <input type="password" class="form-control form-control-lg" id="confSenha' . $id_usuario . '" name="confSenha" required>';
        print '              <span class="input-group-text bg-white" style="cursor: pointer;" onclick="toggleSenha(' . $id_usuario . ', true)">';
        print '                <i class="fas fa-eye" id="toggleConfSenhaIcon' . $id_usuario . '"></i>';
        print '              </span>';
        print '            </div>';
        print '          </div>';

        // Botões
        print '          <input type="hidden" name="id_usuario" value="' . $id_usuario . '">';
        print '          <div class="d-grid gap-2 d-md-flex justify-content-md-center">';
        print '            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">';
        print '              <i class="bi bi-x-circle me-1"></i>Fechar';
        print '            </button>';
        print '            <button type="submit" name="alterar_senha" class="btn btn-primary px-4">';
        print '              <i class="bi bi-check-circle me-1"></i>Alterar';
        print '            </button>';
        print '          </div>';

        print '        </form>';
        print '      </div>'; // modal-body

        print '    </div>'; // modal-content
        print '  </div>';   // modal-dialog
        print '</div>';     // modal
    }

    // select de usuarios
    public function selectUsuario($id_usuario = null)
    {
        $objUsuario = new Usuario();
        // Invocar o método da classe Usuario para consultar os perfis de usuário
        $resultado = $objUsuario->consultarUsuario(null, null);
        print '<label for="usuario" class="form-label"> Usuário: </label>';
        print '<select name="id_usuario" class="form-select" aria-label="Default select example">';
        print '<option selected value="">Selecione o Usuario </option>';
        foreach ($resultado as $key => $valor) {
            if ($valor->id_usuario == $id_usuario) {
                print '<option selected value="' . $valor->id_usuario . '">' . $valor->nome_usuario . '</option>';
            } else {
                print '<option value="' . $valor->id_usuario . '">' . $valor->nome_usuario . '</option>';
            }
        }
        print '</select>';
    }

    // PERFIL

    // cadastrar perfil de usuaio
    public function cadastrar_Perfil($perfil_usuario)
    {
        $objPerfil = new Perfil();
        if ($objPerfil->consultarPerfil($perfil_usuario) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/usuario.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Perfil de usuário já cadastrado");
            exit();
        } else {
            // Invocar o método da classe Usuario para cadastrar o perfil de usuário
            if ($objPerfil->cadastrarPerfil($perfil_usuario) == true) {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/usuario.php';
                // Exibir mensagem de sucesso
                $this->mostrarMensagemSucesso("Perfil de usuário cadastrado com sucesso");
            } else {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/usuario.php';
                // Exibir mensagem de erro
                $this->mostrarMensagemErro("Erro ao cadastrar Perfil de Usuário");
            }
        }
    }
    // consultar perfil
    public function consultar_Perfil($perfil_usuario)
    {
        // Instancia a classe Perfil
        $objPerfil = new Perfil();
        // Invocar o método
        if ($objPerfil->consultarPerfil($perfil_usuario) == true) {
            // Inicia a sessão
            session_start();
            // Carrega os dados do perfil
            $perfil = $objPerfil->consultarPerfil($perfil_usuario);
            // Menu
            $menu = $this->menu();
            // Inclui a view
            include_once 'view/usuario.php';
        } else {
            // Inicia a sessão
            session_start();
            $menu = $this->menu();
            // Inclui a view
            include_once 'view/usuario.php';
            // Mostrar mensagem de erro
            $this->mostrarMensagemErro("Erro ao consultar Perfil");
        }
    }
    // Altera perfil
    public function alterar_Perfil($id_perfil, $perfil_usuario)
    {
        // Instancia a classe Perfil
        $objPerfil = new Perfil();
        // Invocar o método
        if ($objPerfil->alterarPerfil($id_perfil, $perfil_usuario) == true) {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/usuario.php';
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Perfil alterado com sucesso");
        } else {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/usuario.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao alterar Perfil");
        }
    }
    // tabela de consulta de perfil
    public function tabelaConsultaPerfil($perfil)
    {
        if (empty($perfil)) return;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-striped table-hover table-bordered align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Perfil</th>';
        print '<th scope="col">Ações</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';
        foreach ($perfil as $valor) {
            print '<tr>';
            print '<td>' . $valor->perfil_usuario . '</td>';
            print '<td>';
            print '<div class="d-flex gap-2 justify-content-center flex-wrap">';
            if ($valor->perfil_usuario != "Administrador") {
                print '<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#alterar_perfil' . $valor->id_perfil . '"><i class="bi bi-pencil-square"></i></button>';
                print '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#excluir_perfil' . $valor->id_perfil . '"><i class="bi bi-trash"></i></button>';
            }
            print '</div>';
            print '</td>';
            print '</tr>';
        }
        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // excluir perfil
    public function excluir_Perfil($id_perfil)
    {
        // Instancia a classe Perfil
        $objPerfil = new Perfil();
        // Invocar o método
        if ($objPerfil->excluirPerfil($id_perfil) == true) {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/usuario.php';
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Perfil excluído com sucesso");
        } else {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/usuario.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Não é possível excluir este perfil, pois existem usuários associados a ele.");
        }
    }
    // modal de alterar Perfil de Usuario
    public function modalAlterarPerfil($id_perfil, $perfil_usuario)
    {
        print '<div class="modal fade" id="alterar_perfil' . $id_perfil . '" tabindex="-1" aria-labelledby="alterarPerfilLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-lg modal-dialog-centered">';
        print '<div class="modal-content">';

        // Cabeçalho da modal
        print '<div class="modal-header">';
        print '<h6 class="modal-title" id="modalPerfilAlterarLabel">Alterar Perfil</h6>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '</div>';

        // Corpo da modal
        print '<div class="modal-body">';
        print '<form action="index.php" method="POST" id="formulario_perfil_alterar_' . $id_perfil . '">';
        print '<input type="hidden" name="id_perfil" value="' . $id_perfil . '">';
        print '<fieldset class="border border-black p-3 mb-4">';
        print '<legend class="float-none w-auto px-2">Dados do Perfil</legend>';

        print '<div class="mb-3">';
        print '<label for="perfil_usuario_alterar_' . $id_perfil . '" class="form-label">Nome do Perfil *</label>';
        print '<input type="text" class="form-control" id="perfil_usuario_alterar_' . $id_perfil . '" name="perfil_usuario" required autocomplete="off" placeholder="Digite o nome do perfil" value="' . htmlspecialchars($perfil_usuario, ENT_QUOTES) . '">';
        print '</div>';

        print '</fieldset>';
        print '</form>';
        print '</div>'; // fecha modal-body

        // Rodapé da modal com botões
        print '<div class="modal-footer">';
        print '<div class="container-fluid">';
        print '<div class="row g-2">';
        print '<div class="col-md-6">';
        print '<button type="button" class="btn btn-outline-secondary w-100 py-2" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-circle"></i> Cancelar';
        print '</button>';
        print '</div>';
        print '<div class="col-md-6">';
        print '<button type="submit" form="formulario_perfil_alterar_' . $id_perfil . '" name="alterar_perfil" class="btn btn-primary w-100 py-2">';
        print '<i class="bi bi-check-circle"></i> Alterar';
        print '</button>';
        print '</div>';
        print '</div>'; // fecha row
        print '</div>'; // fecha container-fluid
        print '</div>'; // fecha modal-footer

        print '</div>'; // fecha modal-content
        print '</div>'; // fecha modal-dialog
        print '</div>'; // fecha modal
    }
    // modal de excluir perfil
    public function modalExcluirPerfil($id_perfil, $perfil_usuario)
    {
        print '<div class="modal fade" id="excluir_perfil' . $id_perfil . '" tabindex="-1" aria-labelledby="excluirPerfilLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content">';
        print '<div class="modal-header">';
        print '<h5 class="modal-title" id="excluirPerfilLabel">Excluir Perfil</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '</div>';
        print '<div class="modal-body">';
        print 'Tem certeza que deseja excluir o perfil <strong>' . $perfil_usuario . '</strong>?';
        print '</div>';
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer">';
        print '<input type="hidden" name="id_perfil" value="' . $id_perfil . '">';
        print '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
        print '<button type="submit" name="excluir_perfil" class="btn btn-danger">Excluir</button>';
        print '</div>';
        print '</form>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
    }
    // select com dados da classe Perfil_Usuario
    public function select_perfilUsuario($id_perfil = null)
    {
        $objUsuario = new Perfil();
        // Invocar o método da classe Usuario para consultar os perfis de usuário
        $resultado = $objUsuario->consultarPerfil(null, null);
        print '<label for="usuario" class="form-label">Perfil de Usuário: </label>';
        print '<select name="id_perfil" class="form-select" aria-label="Default select example" required>';
        print '<option selected value="">Selecione um Perfil</option>';
        foreach ($resultado as $key => $valor) {
            if ($valor->id_perfil == $id_perfil) {
                print '<option selected value="' . $valor->id_perfil . '">' . $valor->perfil_usuario . '</option>';
            } else {
                print '<option value="' . $valor->id_perfil . '">' . $valor->perfil_usuario . '</option>';
            }
        }
        print '</select>';
    }

    public function select_perfilUsuarioConsulta($id_perfil = null)
    {
        $objUsuario = new Perfil();
        // Invocar o método da classe Usuario para consultar os perfis de usuário
        $resultado = $objUsuario->consultarPerfil(null, null);
        print '<label for="usuario" class="form-label">Perfil de Usuário: </label>';
        print '<select name="id_perfil" class="form-select" aria-label="Default select example">';
        print '<option selected value="">Selecione um Perfil</option>';
        foreach ($resultado as $key => $valor) {
            if ($valor->id_perfil == $id_perfil) {
                print '<option selected value="' . $valor->id_perfil . '">' . $valor->perfil_usuario . '</option>';
            } else {
                print '<option value="' . $valor->id_perfil . '">' . $valor->perfil_usuario . '</option>';
            }
        }
        print '</select>';
    }

    // FORNECEDORES

    // consultar cnpj do fornecedor
    public function consultarFornecedor_Cnpj($cnpj_fornecedor)
    {
        // instanciando a classe usuario
        $objFornecedor = new Fornecedor();
        $objFornecedor->consultarFornecedorCnpj($cnpj_fornecedor);
        // verficando a existencia no banco de dados
        if ($objFornecedor->consultarFornecedorCnpj($cnpj_fornecedor) == true) {
            // iniciando a sessao
            session_start();
            // incluido menu
            $menu = $this->menu();
            // incluido a view do usuario
            include_once 'view/produto.php';
            // exibindo mensagem de erro
            $this->mostrarMensagemErro("Fornecedor já cadastrado");
        } else {
            // iniciando a sessao
            session_start();
            // incluido menu
            $menu = $this->menu();
            // pegando o valor do parametro cpf
            $_SESSION['cnpj_cadastro'] = $cnpj_fornecedor;
            include_once 'view/produto.php';
        }
    }
    // Modal de cadastro de fornecedor
    public function modal_CadastroFornecedor()
    {
        print '<div class="modal fade" id="modal_fornecedor" tabindex="-1" aria-labelledby="modalFornecedorLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-lg modal-dialog-centered">';
        print '<div class="modal-content">';

        // Cabeçalho
        print '<div class="modal-header">';
        print '<h6 class="modal-title" id="modalFornecedorLabel">Novo Fornecedor</h6>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
        print '</div>';

        // Corpo da modal
        print '<div class="modal-body">';
        print '<form action="index.php" method="POST" id="formulario_fornecedor">';
        print '<input type="hidden" name="origem" value="fornecedor">';
        print '<div class="row g-2">';

        // Fieldset Dados Cadastrais
        print '<div class="col-md-12">';
        print '<fieldset class="border border-black p-1 mb-4">';
        print '<legend class="float-none w-auto px-2">Dados Cadastrais</legend>';
        print '<div class="row g-2">';

        print '<div class="col-md-6">';
        print '<label for="razao_social" class="form-label">Razão Social *</label>';
        print '<input type="text" class="form-control" id="razao_social" name="razao_social" required autocomplete="off"
            placeholder="Ex: Empresa XYZ">';
        print '</div>';
        print '<div class="col-md-6">';
        print '<label for="cnpj" class="form-label">CNPJ *</label>';
        print '<input type="text" class="form-control cnpj" id="cnpj" name="cnpj"
            value="' . $_SESSION['cnpj_cadastro'] . '" required
            placeholder="00.000.000/0000-00" autocomplete="off"
            pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}" title="Formato esperado: 00.000.000/0000-00">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="email_fornecedor" class="form-label">Email *</label>';
        print '<input type="email" class="form-control" id="email_fornecedor" name="email" required
            placeholder="exemplo@email.com" autocomplete="off">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="telefone_celular" class="form-label">Telefone Celular *</label>';
        print '<input type="tel" class="form-control telefone_celular" id="telefone_celular" name="telefone_celular" required
            placeholder="(00) 00000-0000" autocomplete="off"
            pattern="\(\d{2}\) \d{4,5}-\d{4}" title="Formato esperado: (XX) XXXXX-XXXX">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="telefone_fixo" class="form-label">Telefone Fixo *</label>';
        print '<input type="tel" class="form-control telefone_fixo" id="telefone_fixo" name="telefone_fixo" required
            placeholder="(00) 0000-0000" autocomplete="off"
            pattern="\(\d{2}\) \d{4}-\d{4}" title="Formato esperado: (XX) XXXX-XXXX">';
        print '</div>';

        print '</div>'; // fecha row
        print '</fieldset>';
        print '</div>'; // fecha col-md-12

        print '</div>'; // fecha .row g-2
        print '</div>'; // fecha .modal-body

        // Rodapé da modal com botões
        print '<div class="modal-footer">';
        print '<div class="container-fluid">';
        print '<div class="row g-2">';
        print '<div class="col-md-6">';
        print '<button type="reset" class="btn btn-outline-secondary w-100 py-2">';
        print '<i class="bi bi-arrow-counterclockwise"></i> Limpar';
        print '</button>';
        print '</div>';
        print '<div class="col-md-6">';
        print '<button type="submit" name="cadastrar_fornecedor" class="btn btn-success w-100 py-2">';
        print '<i class="bi bi-check-circle"></i> Cadastrar';
        print '</button>';
        print '</div>';
        print '</div>'; // row
        print '</div>'; // container-fluid
        print '</div>'; // modal-footer

        print '</form>';
        print '</div>'; // modal-content
        print '</div>'; // modal-dialog
        print '</div>'; // modal

        // Script para exibir automaticamente
        // Script para abrir a modal automaticamente
        print '<script>';
        print '  document.addEventListener("DOMContentLoaded", function() {';
        print '    const modalFornecedor = document.getElementById("modal_fornecedor");';
        // o getOrCreateInstance é usado para garantir que a instância da modal seja criada ou recuperada
        print '    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalFornecedor);';
        // Exibe a modal
        print '    modalInstance.show();';
        print '  });';
        print '</script>';
    }
    //Consultar Fornecedor
    public function consultar_Fornecedor($razao_social)
    {
        $objFornecedor = new Fornecedor();
        // Invocar o método da classe Fornecedor para consultar os fornecedores
        if ($objFornecedor->consultarFornecedor($razao_social) == true) {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $fornecedor = $objFornecedor->consultarFornecedor($razao_social);
            // menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
        } else {
            // Inicia a sessão
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Fornecedor não encontrado");
            // Inclui a mesma view
            include_once 'view/produto.php';
        }
    }
    // cadastrar fornecedor
    public function cadastrar_Fornecedor($razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo)
    {
        // instancia a classe
        $objFornecedor = new Fornecedor();
        if ($this->validarCNPJ($cnpj) == false) {
            session_start();
            $menu = $this->menu();
            include_once 'view/produto.php';
            $this->mostrarMensagemErro("CNPJ inválido");
        } else {
            // Invocar o método da classe Usuario para cadastrar o perfil de usuário
            if ($objFornecedor->cadastrarFornecedor($razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo) == true) {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/produto.php';
                // Exibir mensagem de sucesso
                $this->mostrarMensagemSucesso("Fornecedor cadastrado com sucesso");
            } else {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/produto.php';
                // Exibir mensagem de erro
                $this->mostrarMensagemErro("Erro ao cadastrar Fornecedor");
            }
        }
    }
    // alterar fornecedor
    public function alterar_Fornecedor($id_fornecedor, $razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo)
    {
        // instancia a classe
        $objFornecedor = new Fornecedor();
        // usand o metodo de validar cnpj e o metodo de cadastrar fornecedor
        if ($this->validarCNPJ($cnpj) == false) {
            session_start();
            $menu = $this->menu();
            include_once 'view/produto.php';
            $this->mostrarMensagemErro("CNPJ inválido");
        } else {
            // Invocar o método da classe Usuario para cadastrar o perfil de usuário
            if ($objFornecedor->alterarFornecedor($id_fornecedor, $razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo) == true) {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/produto.php';
                // Exibir mensagem de sucesso
                $this->mostrarMensagemSucesso("Fornecedor alterado com sucesso");
            } else {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/produto.php';
                // Exibir mensagem de erro
                $this->mostrarMensagemErro("Erro ao alterar Fornecedor");
            }
        }
    }
    // excluir fornecedor
    public function excluir_Fornecedor($id_fornecedor)
    {
        // instancia a classe
        $objFornecedor = new Fornecedor();
        // Invocar o método da classe Usuario para excluir o perfil de usuário
        if ($objFornecedor->excluirFornecedor($id_fornecedor) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Fornecedor excluído com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao excluir Fornecedor");
        }
    }
    // modal alterar fornecedor
    public function modal_AlterarFornecedor($id_fornecedor, $razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo)
    {
        print '<div class="modal fade" id="alterar_fornecedor' . $id_fornecedor . '" tabindex="-1" aria-labelledby="alterarFornecedorLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-lg modal-dialog-centered">';
        print '<div class="modal-content">';

        // Cabeçalho
        print '<div class="modal-header">';
        print '<h6 class="modal-title" id="alterarFornecedorLabel">Alterar Fornecedor</h6>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '</div>';

        // Corpo
        print '<div class="modal-body">';
        print '<form action="index.php" method="POST" id="formulario_alterar_fornecedor">';

        print '<fieldset class="border border-black p-3 mb-4">';
        print '<legend class="float-none w-auto px-2">Dados do Fornecedor</legend>';

        print '<div class="row g-3">';

        // Razão Social
        print '<div class="col-md-6">';
        print '<label for="razao_social" class="form-label">Razão Social *</label>';
        print '<input type="text" class="form-control" id="razao_social" name="razao_social" value="' . $razao_social . '" required>';
        print '</div>';

        // CNPJ
        print '<div class="col-md-6">';
        print '<label for="cnpj" class="form-label">CNPJ *</label>';
        print '<input type="text" class="form-control cnpj" id="cnpj" name="cnpj" value="' . $cnpj . '" required>';
        print '</div>';

        // Email
        print '<div class="col-md-6">';
        print '<label for="email" class="form-label">E-mail *</label>';
        print '<input type="email" class="form-control" id="email" name="email" value="' . $email . '" required>';
        print '</div>';

        // Telefone Celular
        print '<div class="col-md-6">';
        print '<label for="telefone_celular" class="form-label">Telefone Celular *</label>';
        print '<input type="text" class="form-control telefone_celular" id="telefone_celular" name="telefone_celular" value="' . $telefone_celular . '" required>';
        print '</div>';

        // Telefone Fixo
        print '<div class="col-md-6">';
        print '<label for="telefone_fixo" class="form-label">Telefone Fixo</label>';
        print '<input type="text" class="form-control telefone_fixo" id="telefone_fixo" name="telefone_fixo" value="' . $telefone_fixo . '">';
        print '</div>';

        print '</div>'; // row
        print '</fieldset>';

        // Rodapé com botões
        print '<div class="d-flex justify-content-center gap-2 mt-4">';
        print '<input type="hidden" name="id_fornecedor" value="' . $id_fornecedor . '">';
        print '<button type="button" class="btn btn-outline-secondary w-50 py-2" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-circle"></i> Fechar</button>';
        print '<button type="submit" name="alterar_fornecedor" class="btn btn-primary w-50 py-2">';
        print '<i class="bi bi-check-circle-fill"></i> Alterar</button>';
        print '</div>';

        print '</form>';
        print '</div>'; // modal-body
        print '</div>'; // modal-content
        print '</div>'; // modal-dialog
        print '</div>'; // modal
    }
    // modal excluir fornecedor
    public function modalExcluirFornecedor($id_fornecedor, $razao_social)
    {
        print '<div class="modal fade" id="excluir_fornecedor' . $id_fornecedor . '" tabindex="-1" aria-labelledby="excluirFornecedorLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content">';
        print '<div class="modal-header">';
        print '<h5 class="modal-title" id="excluirFornecedorLabel">Excluir Fornecedor</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '</div>';
        print '<div class="modal-body">';
        print '<p>Tem certeza que deseja excluir o fornecedor <strong>' . $razao_social . '</strong>?</p>';
        print '</div>';
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer">';
        print '<input type="hidden" name="id_fornecedor" value="' . $id_fornecedor . '">';
        print '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
        print '<button type="submit" name="excluir_fornecedor" class="btn btn-danger">Excluir</button>';
        print '</div>';
        print '</form>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
    }
    // tabela de consulta de Fornecedor
    public function tabelaConsultarFornecedor($fornecedor)
    {
        if (empty($fornecedor)) return;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-striped table-hover table-bordered align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Razão Social</th>';
        print '<th scope="col">CNPJ</th>';
        print '<th scope="col">E-mail</th>';
        print '<th scope="col">Telefone Celular</th>';
        print '<th scope="col">Telefone Fixo</th>';
        print '<th scope="col">Ações</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        foreach ($fornecedor as $valor) {
            print '<tr>';
            print '<td>' . $valor->razao_social . '</td>';
            print '<td>' . $this->aplicarMascaraCNPJ($valor->cnpj_fornecedor) . '</td>';
            print '<td>' . $valor->email . '</td>';
            print '<td>' . $this->aplicarMascaraTelefone($valor->telefone_celular) . '</td>';
            print '<td>' . $this->aplicarMascaraTelefone($valor->telefone_fixo) . '</td>';
            print '<td>';
            // Botões de ação
            print '<div class="d-flex gap-2 justify-content-center flex-wrap">';
            print '<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#alterar_fornecedor' . $valor->id_fornecedor . '"><i class="bi bi-pencil-square"></i></button>';
            print '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#excluir_fornecedor' . $valor->id_fornecedor . '"><i class="bi bi-trash"></i></button>';
            print '</div>';
            print '</td>';
            print '</tr>';
        }

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // select com dados da classe Fornecedor
    public function selectFornecedores($id_fornecedor = null)
    {
        $objUsuario = new Fornecedor();
        // Invocar o método da classe Usuario para consultar os perfis de usuário
        $fornecedor = $objUsuario->consultarFornecedor(null);
        print '<label for="fornecedor" class="form-label">Fornecedor do Produto:</label>';
        print '<select name="id_fornecedor" class="form-select" aria-label="Default select example">';
        print '<option selected value="">Selecione o Fornecedor </option>';
        foreach ($fornecedor as $key => $valor) {
            if ($valor->id_fornecedor == $id_fornecedor) {
                print '<option selected value="' . $valor->id_fornecedor . '">' . $valor->razao_social . '</option>';
            } else {
                print '<option value="' . $valor->id_fornecedor . '">' . $valor->razao_social . '</option>';
            }
        }
        print '</select>';
    }
    public function selectFornecedoresCadastro($id_fornecedor = null)
    {
        $objUsuario = new Fornecedor();
        // Invocar o método da classe Usuario para consultar os perfis de usuário
        $fornecedor = $objUsuario->consultarFornecedor(null);
        print '<label for="fornecedor" class="form-label">Fornecedor do Produto:</label>';
        print '<select name="id_fornecedor" class="form-select" aria-label="Default select example" required>';
        print '<option selected value="">Selecione o Fornecedor </option>';
        foreach ($fornecedor as $key => $valor) {
            if ($valor->id_fornecedor == $id_fornecedor) {
                print '<option selected value="' . $valor->id_fornecedor . '">' . $valor->razao_social . '</option>';
            } else {
                print '<option value="' . $valor->id_fornecedor . '">' . $valor->razao_social . '</option>';
            }
        }
        print '</select>';
    }
    // metodo de buscar fornecedor dinamicamente
    public function buscarFornecedor($fornecedor)
    {
        $objFornecedor = new Fornecedor(); // Certifique-se de que a classe Fornecedor esteja carregada
        $fornecedores = $objFornecedor->consultarFornecedorDinamico($fornecedor);

        if (!empty($fornecedores)) {
            foreach ($fornecedores as $valor) {
                $id_fornecedor = $valor['id_fornecedor'];
                $razao_social = htmlspecialchars($valor['razao_social']);
                print "<span class='list-group-item list-group-item-action fornecedor-item'
                data-id='{$id_fornecedor}'
                data-nome='{$razao_social}'>
                {$razao_social}
            </span>";
            }
        } else {
            print "<span class='list-group-item text-danger'>Nenhum fornecedor encontrado</span>";
        }
    }


    // PRODUTO

    // verficar produto
    public function verificar_Produto($nome_produto, $cor, $largura)
    {
        // Instancia a classe
        $objproduto = new Produto();
        // Verifica se o produto existe
        if ($objproduto->verificarProduto($nome_produto, $cor, $largura) == true) {
            session_start();
            $menu = $this->menu();
            $this->mostrarMensagemErro("Produto já cadastrado");
            include_once 'view/produto.php';
        } else {
            session_start();
            // Armazena os dados do produto na sessão
            $_SESSION['produto'] = [
                'nome_produto' => $nome_produto,
                'cor'          => $cor,
                'largura'      => $largura
            ];
            $menu = $this->menu();
            include_once 'view/produto.php';
        }
    }
    // Modal de cadastro de produto
    public function modal_CadastroProduto()
    {
        print '<div class="modal fade" id="modal_produto" tabindex="-1" aria-labelledby="modalProdutoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-xl modal-dialog-centered">';
        print '<div class="modal-content">';

        // Cabeçalho
        print '<div class="modal-header">';
        print '<h6 class="modal-title" id="modalProdutoLabel">Novo Produto</h6>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
        print '</div>';

        // Corpo com scroll interno controlado
        print '<div class="modal-body" style="max-height: 90vh; overflow-y: auto;">';
        print '<form action="index.php" method="POST" enctype="multipart/form-data" id="formulario_produto">';
        print '<input type="hidden" name="origem" value="produto">';

        print '<div class="row g-2">';
        print '<div class="col-md-12">';
        print '<fieldset class="border border-black p-2 mb-4">';
        print '<legend class="float-none w-auto px-2">Dados do Produto</legend>';
        print '<div class="row g-2">';

        // Imagem e Preview
        print '<div class="col-md-6">';
        print '<label for="img_produto" class="form-label">Imagem *</label>';
        print '<input type="file" class="form-control" id="img_produto" name="img_produto" accept="image/*">';
        print '<label id="legenda_imagem_cadastro" class="form-label d-block legenda"></label>';
        print '<div id="preview_imagem_cadastro">';
        print '<img src="" class="img-thumbnail d-none" style="max-width: 80px; height: auto;">';
        print '</div>';
        print '</div>';

        // Nome
        print '<div class="col-md-6">';
        print '<label for="nome_produto" class="form-label">Nome *</label>';
        print '<input type="text" class="form-control" id="nome_produto" name="nome_produto" value="' . $_SESSION['produto']['nome_produto'] . '" required>';
        print '</div>';

        // Tipo, Cor, Quantidade
        print '<div class="col-md-4">';
        print '<label for="tipo_produto" class="form-label">Tipo *</label>';
        print '<input type="text" class="form-control" id="tipo_produto" name="tipo_produto" placeholder="Ex: Matéria-prima" required>';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="cor" class="form-label">Cor *</label>';
        print '<input type="text" class="form-control" id="cor" name="cor" value="' . $_SESSION['produto']['cor'] . '" required>';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="quantidade" class="form-label">Qtd. (m) *</label>';
        print '<input type="text" class="form-control quantidade" id="quantidade" name="quantidade" required autocomplete="off">';
        print '</div>';

        // Quantidade mínima, Largura, Composição
        print '<div class="col-md-4">';
        print '<label for="quantidade_minima" class="form-label">Qtd. Mínima *</label>';
        print '<input type="text" class="form-control quantidade_minima" id="quantidade_minima" name="quantidade_minima" required autocomplete="off">';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="largura" class="form-label">Largura (m) *</label>';
        print '<input type="text" class="form-control" id="largura" name="largura" value="' . $_SESSION['produto']['largura'] . '" required autocomplete="off" >';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="composicao" class="form-label">Composição *</label>';
        print '<input type="text" class="form-control" id="composicao" name="composicao" required>';
        print '</div>';

        // Custo, Valor, Data
        print '<div class="col-md-4">';
        print '<label for="custo_compra" class="form-label">Custo de Compra *</label>';
        print '<div class="input-group">';
        print '<span class="input-group-text">R$</span>';
        print '<input type="text" class="form-control dinheiro" id="custo_compra" name="custo_compra" required autocomplete="off" >';
        print '</div>';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="valor_venda" class="form-label">Valor de Venda *</label>';
        print '<div class="input-group">';
        print '<span class="input-group-text">R$</span>';
        print '<input type="text" class="form-control dinheiro" id="valor_venda" name="valor_venda" required autocomplete="off" >';
        print '</div>';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="data_compra" class="form-label">Data Compra *</label>';
        print '<input type="date" class="form-control" id="data_compra" name="data_compra" required autocomplete="off">';
        print '</div>';

        print '<div class="col-md-6">';
        print '<label for="ncm_produto" class="form-label">NCM *</label>';
        print '<input type="text" class="form-control" id="ncm_produto" name="ncm_produto" required autocomplete="off">';
        print '</div>';
        // Fornecedor
        print '<div class="col-md-6">';
        print '<label for="produto_custo" class="form-label">Fornecedor *</label>';
        print '<div class="position-relative">';
        print '<div class="input-group"> ';
        print '<span class="input-group-text"><i class="bi bi-search"></i></span>';
        print '<input type="hidden" id="id_fornecedor_hidden_cadastro" name="id_fornecedor" value="" />';
        print '<input type="text" class="form-control" id="id_fornecedor_produto_cadastro" placeholder="Digite o nome do fornecedor" autocomplete="off" />';
        print '</div>';
        print '<div id="resultado_busca_fornecedor_cadastro" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;">';
        print '</div>';
        print '</div>';
        print '</div>'; // fecha row do fieldset
        print '</fieldset>';
        print '</div>'; // col-md-12
        print '</div>'; // row g-2
        print '</div>'; // modal-body

        // Rodapé
        print '<div class="modal-footer">';
        print '<div class="container-fluid">';
        print '<div class="row g-2">';
        print '<div class="col-md-4">';
        print '<button type="reset" class="btn btn-outline-secondary w-100 py-2">';
        print '<i class="bi bi-arrow-counterclockwise"></i> Limpar</button>';
        print '</div>';
        print '<div class="col-md-4">';
        print '<button type="submit" class="btn btn-success w-100 py-2" name="cadastrar_produto">';
        print '<i class="bi bi-check-circle-fill"></i> Cadastrar</button>';
        print '</div>';
        print '<div class="col-md-4">';
        print '<button type="button" class="btn btn-danger w-100 py-2" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-octagon"></i> Cancelar</button>';
        print '</div>';
        print '</div>'; // row
        print '</div>'; // container-fluid
        print '</div>'; // modal-footer

        print '</form>';
        print '</div>'; // modal-content
        print '</div>'; // modal-dialog
        print '</div>'; // modal

        // Script de preview de imagem e exibição automática
        print '<script>';
        print 'document.addEventListener("DOMContentLoaded", function() {';
        print '  const modal = document.getElementById("modal_produto");';
        print '  const instance = bootstrap.Modal.getOrCreateInstance(modal);';
        print '  instance.show();';

        // Preview imagem
        print '  const inputImagemCadastro = document.querySelector("#img_produto");';
        print '  const previewDivCadastro = document.querySelector("#preview_imagem_cadastro");';
        print '  const legendaLabelCadastro = document.querySelector("#legenda_imagem_cadastro");';

        print '  if (inputImagemCadastro && previewDivCadastro && legendaLabelCadastro) {';
        print '    inputImagemCadastro.addEventListener("change", function () {';
        print '      const file = this.files[0];';
        print '      if (file) {';
        print '        const reader = new FileReader();';
        print '        reader.onload = function (e) {';
        print '          previewDivCadastro.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" width="80" height="auto">`;';
        print '          legendaLabelCadastro.textContent = "Imagem Selecionada:";';
        print '        };';
        print '        reader.readAsDataURL(file);';
        print '      } else {';
        print '        previewDivCadastro.innerHTML = "";';
        print '        legendaLabelCadastro.textContent = "";';
        print '      }';
        print '    });';
        print '  }';
        print '});';
        print '</script>';
    }
    // Cadastrar Produto
    public function cadastrar_Produto(
        $nome_produto,
        $tipo_produto,
        $cor,
        $composicao,
        $quantidade,
        $quantidade_minima,
        $largura,
        $custo_compra,
        $valor_venda,
        $data_compra,
        $ncm_produto,
        $id_fornecedor,
        $img_produto
    ) {
        // instancia a classe
        $objproduto = new Produto();
        // Invocar o método da classe Usuario para cadastrar produto
        if ($objproduto->cadastrarProduto(
            $nome_produto,
            $tipo_produto,
            $cor,
            $composicao,
            $quantidade,
            $quantidade_minima,
            $largura,
            $custo_compra,
            $valor_venda,
            $data_compra,
            $ncm_produto,
            $id_fornecedor,
            $img_produto
        ) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Produto cadastrado com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao cadastrar Produto");
        }
    }
    // consultar produto
    public function consultar_Produto($tipo_produto, $cor, $nome_produto, $id_fornecedor)
    {
        // instancia a classe
        $objproduto = new Produto();
        // Invocar o método da classe produto para consultar o produto
        if ($objproduto->consultarProduto($tipo_produto, $cor, $nome_produto, $id_fornecedor) == true) {
            session_start();
            $produto = $objproduto->consultarProduto($tipo_produto, $cor, $nome_produto, $id_fornecedor);
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Produto não encontrado");
        }
    }
    // alterar  produdo
    public function alterar_Produto(
        $id_produto,
        $nome_produto,
        $tipo_produto,
        $cor,
        $composicao,
        $quantidade,
        $quantidade_minima,
        $largura,
        $custo_compra,
        $valor_venda,
        $data_compra,
        $ncm_produto,
        $id_fornecedor,
        $img_produto
    ) {
        // instancia a classe
        $objproduto = new Produto();
        // Invocar o método da classe Usuario para cadastrar o perfil de usuário

        if ($objproduto->alterarProduto(
            $nome_produto,
            $tipo_produto,
            $cor,
            $composicao,
            $quantidade,
            $quantidade_minima,
            $largura,
            $custo_compra,
            $valor_venda,
            $data_compra,
            $ncm_produto,
            $id_fornecedor,
            $id_produto,
            $img_produto
        ) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Produto alterado com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao alterar Produto");
        }
    }
    // excluir usuario
    public function excluir_Produto($id_produto)
    {
        // instancia a classe
        $objproduto = new Produto();
        // invocar o metodo de revisar se o produto esta em algum pedido
        if ($objproduto->produtoEmAlgumPedido($id_produto) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/produto.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Produto não pode ser excluído, pois está vinculado a um pedido");
        } else {
            // Invocar o método da classe Usuario para excluir o perfil de usuário
            if ($objproduto->excluirProduto($id_produto) == true) {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/produto.php';
                // Exibir mensagem de sucesso
                $this->mostrarMensagemSucesso("Produto excluído com sucesso");
            } else {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'view/produto.php';
                // Exibir mensagem de erro
                $this->mostrarMensagemErro("Erro ao excluir Produto");
            }
        }
    }
    public function modalAlterarProduto(
        $id_produto,
        $nome_produto,
        $tipo_produto,
        $cor,
        $composicao,
        $quantidade,
        $quantidade_minima,
        $largura,
        $custo_compra,
        $valor_venda,
        $data_compra,
        $ncm_produto,
        $razao_social,
        $img_produto,
        $id_fornecedor
    ) {
        print '<div class="modal fade" id="alterar_produto' . $id_produto . '" tabindex="-1" aria-labelledby="alterarProdutoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-xl modal-dialog-centered">';
        print '<div class="modal-content">';

        // Cabeçalho
        print '<div class="modal-header">';
        print '<h6 class="modal-title" id="alterarProdutoLabel">Alterar Produto</h6>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '</div>';

        // Corpo
        print '<div class="modal-body" style="max-height: 75vh; overflow-y: auto;">';
        print '<form action="index.php" method="POST" enctype="multipart/form-data" id="formulario_alterar_produto">';
        print '<input type="hidden" name="id_produto" value="' . $id_produto . '">';
        print '<div class="row g-3">';

        // Fieldset Imagem
        print '<div class="col-md-12">';
        print '<fieldset class="border border-black p-2 mb-4">';
        print '<legend class="float-none w-auto px-2">Imagem do Produto</legend>';
        print '<div class="row g-3 align-items-center">';

        // Imagem atual
        print '<div class="col-md-6">';
        if (!empty($img_produto) && file_exists($img_produto)) {
            print '<label class="form-label d-block">Imagem Atual:</label>';
            print '<img src="' . $img_produto . '" class="img-thumbnail" style="max-width: 80px; height: auto;">';
        }
        print '</div>';

        // Preview nova imagem
        print '<div class="col-md-6">';
        print '<label id="legenda_imagem' . $id_produto . '" class="form-label d-block legenda"></label>';
        print '<div id="preview_imagem' . $id_produto . '"></div>';
        print '</div>';

        // Input de nova imagem
        print '<div class="col-md-6">';
        print '<input type="file" class="form-control" id="img_produto' . $id_produto . '" name="img_produto" accept="image/*">';
        print '<input type="hidden" name="imagem_antiga" value="' . $img_produto . '">';
        print '</div>';

        print '</div>'; // row imagem
        print '</fieldset>';
        print '</div>'; // col imagem

        // Fieldset Dados Principais
        print '<div class="col-md-12">';
        print '<fieldset class="border border-black p-2 mb-4">';
        print '<legend class="float-none w-auto px-2">Dados do Produto</legend>';
        print '<div class="row g-3">';

        print '<div class="col-md-4">';
        print '<label for="nome_produto" class="form-label">Nome *</label>';
        print '<input type="text" class="form-control" name="nome_produto" value="' . $nome_produto . '" required autocomplete="off">';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="tipo_produto" class="form-label">Tipo *</label>';
        print '<input type="text" class="form-control" name="tipo_produto" value="' . $tipo_produto . '" required>';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="cor" class="form-label">Cor *</label>';
        print '<input type="text" class="form-control" name="cor" value="' . $cor . '" required>';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="composicao" class="form-label">Composição *</label>';
        print '<input type="text" class="form-control" name="composicao" value="' . $composicao . '" required>';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="largura" class="form-label">Largura (m) *</label>';
        print '<input type="text" class="form-control" name="largura" value="' . $largura . '" required autocomplete="off">';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="quantidade" class="form-label">Quantidade (m) *</label>';
        print '<input type="text" class="form-control" name="quantidade" value="' . $quantidade . '" required autocomplete="off">';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="quantidade_minima" class="form-label">Qtd. Mínima *</label>';
        print '<input type="text" class="form-control" name="quantidade_minima" value="' . $quantidade_minima . '" required autocomplete="off">';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="custo_compra" class="form-label">Custo Compra (R$) *</label>';
        print '<div class="input-group">';
        print '<span class="input-group-text">R$</span>';
        print '<input type="text" class="form-control dinheiro" name="custo_compra" value="' . $custo_compra . '" required autocomplete="off"> ';
        print '</div>';
        print '</div>';


        print '<div class="col-md-4">';
        print '<label for="valor_venda" class="form-label">Valor Venda (R$) *</label>';
        print '<div class="input-group">';
        print '<span class="input-group-text">R$</span>';
        print '<input type="text" class="form-control dinheiro" name="valor_venda" value="' . $valor_venda . '" required autocomplete="off" > ';
        print '</div>';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="data_compra" class="form-label">Data da Compra *</label>';
        print '<input type="date" class="form-control" name="data_compra" value="' . $data_compra . '" required autocomplete="off">';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="ncm_produto" class="form-label">NCM *</label>';
        print '<input type="text" class="form-control" name="ncm_produto" value="' . $ncm_produto . '" required autocomplete="off">';
        print '</div>';

        print '<div class="col-md-4">';
        print '<label for="produto_custo" class="form-label">Fornecedor *</label>';
        print '<div class="position-relative">';
        print '<div class="input-group"> ';
        print '<span class="input-group-text"><i class="bi bi-search"></i></span>';
        print '<input type="hidden" id="id_fornecedor_hidden' . $id_produto . '" name="id_fornecedor" value="' . $id_fornecedor . '" />';
        print '<input type="text" class="form-control fornecedor-input"
        id="id_fornecedor_produto' . $id_produto . '"
        placeholder="Digite o nome do fornecedor"
        value="' . $razao_social . '"
        autocomplete="off" />';
        print '</div>';
        print '<div id="resultado_busca_fornecedor' . $id_produto . '"
        class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow"
        style="max-height: 200px; overflow-y: auto;">';
        print '</div>';

        print '</div>'; // row internos
        print '</fieldset>';
        print '</div>'; // col dados

        print '</div>'; // row g-3
        print '</div>'; // modal-body

        // Rodapé com botões
        print '<div class="modal-footer">';
        print '<div class="container-fluid">';
        print '<div class="row g-2">';
        print '<div class="col-md-4">';
        print '<button type="reset" class="btn btn-outline-secondary w-100 py-2">';
        print '<i class="bi bi-arrow-counterclockwise"></i> Limpar';
        print '</button>';
        print '</div>';
        print '<div class="col-md-4">';
        print '<button type="submit" name="alterar_produto" class="btn btn-primary w-100 py-2">';
        print '<i class="bi bi-check-circle"></i> Alterar';
        print '</button>';
        print '</div>';
        print '<div class="col-md-4">';
        print '<button type="button" class="btn btn-danger w-100 py-2" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-octagon"></i> Cancelar';
        print '</button>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>'; // modal-footer

        print '</form>';
        print '</div>'; // modal-content
        print '</div>'; // modal-dialog
        print '</div>'; // modal

        // Script JS para preview da nova imagem
        print '<script>
        document.addEventListener("DOMContentLoaded", function () {
            const inputImagem = document.querySelector("#img_produto' . $id_produto . '");
            const previewDiv = document.querySelector("#preview_imagem' . $id_produto . '");
            const legendaLabel = document.querySelector("#legenda_imagem' . $id_produto . '");

            if (inputImagem && previewDiv && legendaLabel) {
                inputImagem.addEventListener("change", function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewDiv.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" width="80" height="auto">`;
                            legendaLabel.textContent = "Nova Imagem: ";
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewDiv.innerHTML = "";
                        legendaLabel.textContent = "";
                    }
                });
            }
        });
        </script>';
    }
    // modal de excluir produto
    public function modalExcluirProduto($id_produto, $nome_produto)
    {
        print '<div class="modal fade" id="excluir_produto' . $id_produto . '" tabindex="-1" aria-labelledby="excluirProdutoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content">';
        print '<div class="modal-header">';
        print '<h5 class="modal-title" id="excluirProdutoLabel">Excluir Produto</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '</div>';
        print '<div class="modal-body">';
        print '<p>Tem certeza que deseja excluir o Produto <strong>' . $nome_produto . '</strong>?</p>';
        print '</div>';
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer">';
        print '<input type="hidden" name="id_produto" value="' . $id_produto . '">';
        print '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
        print '<button type="submit" name="excluir_produto" class="btn btn-danger">Excluir</button>';
        print '</div>';
        print '</form>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
    }
    // tabela de consulta de produto
    public function tabelaConsultarProduto($produto)
    {
        if (empty($produto)) return;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-striped table-hover table-bordered align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Imagem</th>';
        print '<th scope="col">Produto</th>';
        print '<th scope="col">Cor</th>';
        print '<th scope="col">Largura</th>';
        print '<th scope="col">Quantidade</th>';
        print '<th scope="col">Valor de Venda</th>';

        if ($this->temPermissao(['Administrador'])) {
            print '<th scope="col">Ações</th>';
        }

        print '</tr>';
        print '</thead>';
        print '<tbody>';

        foreach ($produto as $valor) {
            print '<tr>';

            // NOVO: exibição da imagem do produto
            print '<td>';
            if (!empty($valor->img_produto) && file_exists($valor->img_produto)) {
                print '<img src="' . $valor->img_produto . '" alt="Imagem do Produto" class="img-thumbnail rounded mx-auto d-block" style="max-width: 70px; max-height: 70px;">';
            } else {
                print '<span class="text-muted">Sem imagem</span>';
            }
            print '</td>';

            // Campos normais
            print '<td>' . $valor->nome_produto . '</td>';
            print '<td>' . $valor->cor . '</td>';
            print '<td>' . $valor->largura . ' m</td>';
            print '<td>' . $valor->quantidade . ' m</td>';
            print '<td>R$ ' . $valor->valor_venda . '</td>';

            // Ações para administradores
            if ($this->temPermissao(['Administrador'])) {
                print '<td>';
                print '<div class="d-flex gap-2 justify-content-center flex-wrap">';

                // Botão Alterar
                print '<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#alterar_produto' . $valor->id_produto . '">';
                print '<i class="bi bi-pencil-square"></i>';
                print '</button>';

                // Botão Excluir
                print '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#excluir_produto' . $valor->id_produto . '">';
                print '<i class="bi bi-trash"></i>';
                print '</button>';

                // Botão Detalhes
                print '<button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detalhes_produto' . $valor->id_produto . '">';
                print '<i class="bi bi-eye"></i>';
                print '</button>';

                print '</div>';
                print '</td>';
            }
            print '</tr>';
        }
        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // modal de detalhes de um único produto
    public function modalDetalhesProduto($produto)
    {
        if (empty($produto)) return;
        foreach ($produto as $valor) {
            print '
            <div class="modal fade" id="detalhes_produto' . $valor->id_produto . '" tabindex="-1" aria-labelledby="detalhesProdutoLabel' . $valor->id_produto . '" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title" id="detalhesProdutoLabel' . $valor->id_produto . '">Detalhes do Produto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="container">
                                <div class="row mb-2">
                                    <div class="col-md-6"><strong>Tipo:</strong> ' . $valor->tipo_produto . '</div>
                                    <div class="col-md-6"><strong>Cor:</strong> ' . $valor->cor . '</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6"><strong>Composição:</strong> ' . $valor->composicao . '</div>
                                    <div class="col-md-6"><strong>Quantidade:</strong> ' . $valor->quantidade . ' metros</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6"><strong>Quantidade Mínima:</strong> ' . $valor->quantidade_minima . ' metros</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6"><strong>Largura (m):</strong> ' . $valor->largura . '</div>
                                    <div class="col-md-6"><strong>Valor Venda (R$):</strong> ' . $valor->valor_venda . '</div>
                                </div>
                                ' . ($this->temPermissao(['Administrador']) ? '
                                <div class="row mb-2">
                                    <div class="col-md-6"><strong>Custo Compra (R$):</strong> ' . $valor->custo_compra . '</div>
                                    <div class="col-md-6"><strong>Data Compra:</strong> ' . date('d/m/Y', strtotime($valor->data_compra)) . '</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6"><strong>NCM:</strong> ' . $valor->ncm_produto . '</div>
                                    <div class="col-md-6"><strong>Fornecedor:</strong> ' . $valor->razao_social . '</div>' : '') . '
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>';
        }
    }
    // select de produtos
    public function selectProdutos($id_produto = null)
    {
        // instancia a classe
        $objProduto = new Produto();
        // Invocar o método da classe produto para consultar os produtos
        $produtos = $objProduto->consultarProduto(null, null, null, null);
        print '<label for="fornecedor" class="form-label">Produto:</label>';
        print '<select name="id_produto" class="form-select" aria-label="Default select example">';
        print '<option selected value="">Selecione o Produto</option>';
        foreach ($produtos as $key => $valor) {
            if ($valor->id_produto == $id_produto) {
                print '<option selected value="' . $valor->id_produto . '">' . $valor->nome_produto . '</option>';
            } else {
                print '<option value="' . $valor->id_produto . '">' . $valor->nome_produto . '</option>';
            }
        }
        print '</select>';
    }
















    // CLIENTE

    // verificar cliente
    public function consultarCliente_Cnpj($cnpj_cliente)
    {
        // instancia a classe
        $objCliente = new Cliente();
        // Verifica se o cliente existe
        $objCliente->verificarCliente($cnpj_cliente);
        // Se o cliente já estiver cadastrado, exibe mensagem de erro
        if ($objCliente->verificarCliente($cnpj_cliente) == true) {
            session_start();
            $menu = $this->menu();
            $this->mostrarMensagemErro("Cliente já cadastrado");
            include_once 'view/cliente.php';
        } else {
            session_start();
            // Armazena os dados do cliente na sessão
            $_SESSION['cnpj_cliente'] = $cnpj_cliente;
            $menu = $this->menu();
            include_once 'view/cliente.php';
        }
    }
    // consultar cliente
    public function consultar_Cliente($nome_fantasia, $razao_social, $cnpj_cliente)
    {
        // instancia a classe
        $objCliente = new Cliente();
        // Invocar o método da classe cliente para consultar o cliente
        if ($objCliente->consultarCliente($nome_fantasia, $razao_social, $cnpj_cliente) == true) {
            // Inicia a sessão
            session_start();
            $cliente = $objCliente->consultarCliente($nome_fantasia, $razao_social, $cnpj_cliente);
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do cliente
            include_once 'view/cliente.php';
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'view/cliente.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Cliente não encontrado");
        }
    }
    // funcoes auxiliares para aplicar mascaras na tabela e nos detalhes:
    private function aplicarMascaraCNPJ($cnpj)
    {
        return preg_replace("/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/", "$1.$2.$3/$4-$5", $cnpj);
    }
    // funcao auxiliar de mascara para telefone
    private function aplicarMascaraTelefone($numero)
    {
        $numero = preg_replace('/\D/', '', $numero);
        if (strlen($numero) === 11) {
            return preg_replace("/^(\d{2})(\d{5})(\d{4})$/", "($1) $2-$3", $numero);
        } elseif (strlen($numero) === 10) {
            return preg_replace("/^(\d{2})(\d{4})(\d{4})$/", "($1) $2-$3", $numero);
        }
        return $numero;
    }
    // funcao auxiliar para mascara de cep
    private function aplicarMascaraCEP($cep)
    {
        return preg_replace("/^(\d{5})(\d{3})$/", "$1-$2", $cep);
    }
    // tabela de clientes
    public function tabelaConsultarCliente($cliente)
    {
        if (empty($cliente)) return;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-striped table-hover table-bordered align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Responsável</th>';
        print '<th scope="col">Nome Fantasia</th>';
        print '<th scope="col">E-mail</th>';
        print '<th scope="col">Telefone Celular</th>';
        print '<th scope="col">Telefone Fixo</th>';
        print '<th scope="col">Limite de Crédito</th>';

        if ($this->temPermissao(['Administrador'])) {
            print '<th scope="col">Ações</th>';
        }

        print '</tr>';
        print '</thead>';
        print '<tbody>';

        foreach ($cliente as $valor) {
            // Arrays para acumular todos os números
            $celulares = [];
            $fixos     = [];

            if (!empty($valor->telefones)) {
                $lista = explode(',', $valor->telefones);

                foreach ($lista as $t) {
                    $partes = explode(':', $t, 2);
                    if (count($partes) === 2) {
                        $tipo   = strtolower(trim($partes[0]));
                        $numero = $this->aplicarMascaraTelefone(trim($partes[1]));
                        if ($tipo === 'celular') {
                            $celulares[] = $numero;
                        } elseif ($tipo === 'fixo') {
                            $fixos[] = $numero;
                        }
                    }
                }
            }

            // Junta múltiplos números separados por <br> (quebra de linha)
            $celular = $celulares ? implode('<br> <hr>', $celulares) : '—';
            $fixo    = $fixos     ? implode('<br> <hr>', $fixos)     : '—';

            // Formata limite de crédito
            $limite = number_format($valor->limite_credito, 2, ',', '.');

            // Monta a linha da tabela
            print '<tr>';
            print '<td>' . htmlspecialchars($valor->nome_representante) . '</td>';
            print '<td>' . htmlspecialchars($valor->nome_fantasia) . '</td>';
            print '<td>' . htmlspecialchars($valor->email) . '</td>';
            print '<td>' . $celular . '</td>';
            print '<td>' . $fixo . '</td>';
            print '<td>R$ ' . $limite . '</td>';

            if ($this->temPermissao(['Administrador'])) {
                print '<td>';
                print '<div class="d-flex gap-2 justify-content-center flex-wrap">';
                print '<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#alterar_cliente' . $valor->id_cliente . '">
                    <i class="bi bi-pencil-square"></i>
                  </button>';
                print '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#excluir_cliente' . $valor->id_cliente . '">
                    <i class="bi bi-trash"></i>
                  </button>';
                print '<button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detalhes_cliente' . $valor->id_cliente . '">
                    <i class="bi bi-eye"></i>
                  </button>';
                print '</div>';
                print '</td>';
            }

            print '</tr>';
        }

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // modal de detalhes de um único cliente
    public function modalDetalhesCliente($cliente)
    {
        if (empty($cliente)) return;

        foreach ($cliente as $valor) {
            // Telefones
            $celular = [];
            $fixo    = [];
            if (!empty($valor->telefones)) {
                $lista = explode(',', $valor->telefones);
                foreach ($lista as $t) {
                    $partes = explode(':', $t, 2);
                    if (count($partes) === 2) {
                        $tipo   = strtolower(trim($partes[0]));
                        $numero = $this->aplicarMascaraTelefone(trim($partes[1]));
                        if ($tipo === 'celular') {
                            $celular[] = $numero;
                        } elseif ($tipo === 'fixo') {
                            $fixo[] = $numero;
                        }
                    }
                }
            }

            print '
        <div class="modal fade" id="detalhes_cliente' . $valor->id_cliente . '" tabindex="-1"
            aria-labelledby="detalhesClienteLabel' . $valor->id_cliente . '" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="detalhesClienteLabel' . $valor->id_cliente . '">
                            Detalhes do Cliente
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="container text-center"> <!-- centraliza todo o conteúdo -->

                            <div class="row mb-2 align-items-center">
                                <div class="col-md-6"><strong>Nome do Representante:</strong><br>' . htmlspecialchars($valor->nome_representante) . '</div>
                                <div class="col-md-6"><strong>Razão Social:</strong><br>' . htmlspecialchars($valor->razao_social) . '</div>
                            </div>

                            <div class="row mb-2 align-items-center">
                                <div class="col-md-6"><strong>Nome Fantasia:</strong><br>' . htmlspecialchars($valor->nome_fantasia) . '</div>
                                <div class="col-md-6"><strong>CNPJ:</strong><br>' . $this->aplicarMascaraCNPJ($valor->cnpj_cliente) . '</div>
                            </div>

                            <div class="row mb-2 align-items-center">
                                <div class="col-md-6"><strong>Email:</strong><br>' . htmlspecialchars($valor->email) . '</div>
                                <div class="col-md-6"><strong>Inscrição Estadual:</strong><br>' . htmlspecialchars($valor->inscricao_estadual) . '</div>
                            </div>

                            <div class="row mb-2 align-items-center">
                                <div class="col-md-6"><strong>Limite de Crédito:</strong><br>R$ ' .
                number_format($valor->limite_credito, 2, ',', '.') . '</div>
                            </div>

                            <hr>

                            <div class="row mb-2 align-items-center">
                                <div class="col-md-6"><strong>Celular:</strong><br>' .
                (!empty($celular) ? implode('<br>', $celular) : '<span class="text-muted">—</span>') . '</div>
                                <div class="col-md-6"><strong>Fixo:</strong><br>' .
                (!empty($fixo) ? implode('<br>', $fixo) : '<span class="text-muted">—</span>') . '</div>
                            </div>

                            <hr>

                            <div class="row mb-2 align-items-center">
                                <div class="col-md-4"><strong>Cidade:</strong><br>' . htmlspecialchars($valor->cidade) . '</div>
                                <div class="col-md-4"><strong>Estado:</strong><br>' . htmlspecialchars($valor->estado) . '</div>
                                <div class="col-md-4"><strong>Bairro:</strong><br>' . htmlspecialchars($valor->bairro) . '</div>
                            </div>

                            <div class="row mb-2 align-items-center">
                                <div class="col-md-6"><strong>CEP:</strong><br>' . $this->aplicarMascaraCEP($valor->cep) . '</div>
                                <div class="col-md-6"><strong>Complemento:</strong><br>' . htmlspecialchars($valor->complemento) . '</div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>

                </div>
            </div>
        </div>';
        }
    }
    // cadastrar cliente
    public function cadastrar_Cliente(
        $nome_representante,
        $razao_social,
        $nome_fantasia,
        $cnpj_cliente,
        $email,
        $limite_credito,
        $telefones,
        $inscricao_estadual,
        $cidade,
        $estado,
        $bairro,
        $cep,
        $complemento
    ) {
        // instancia a classe
        $objcliente = new Cliente();
        // invocando o metodo de validar cnpj e o metodo de cadastrar cliente
        if ($this->validarCNPJ($cnpj_cliente) == false) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'View/cliente.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("CNPJ inválido");
        } else {
            if ($objcliente->cadastrarCliente(
                $nome_representante,
                $razao_social,
                $nome_fantasia,
                $cnpj_cliente,
                $email,
                $limite_credito,
                $telefones,
                $inscricao_estadual,
                $cidade,
                $estado,
                $bairro,
                $cep,
                $complemento
            ) == true) {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'View/cliente.php';
                // Exibir mensagem de sucesso
                $this->mostrarMensagemSucesso("Cliente cadastrado com sucesso");
            } else {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'View/cliente.php';
                // Exibir mensagem de erro
                $this->mostrarMensagemErro("Erro ao cadastrar Cliente");
            }
        }
    }
    // modal cadastro de cliente
    public function modal_CadastroCliente()
    {
        print '<div class="modal fade" id="modal_cliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">';
        print '  <div class="modal-dialog modal-dialog-centered modal-lg">';
        print '    <div class="modal-content">';

        // Cabeçalho da Modal
        print '      <div class="modal-header">';
        print '        <h6 class="modal-title" id="modalClienteLabel">Novo Cliente</h6>';
        print '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '      </div>';

        // Corpo da Modal
        print '      <div class="modal-body">';
        print '        <form action="index.php" method="POST" id="formulario_cliente" class="needs-validation">';
        print '          <div class="row g-3">';

        // ===== Fieldset Dados Cadastrais =====
        print '            <div class="col-12">';
        print '              <fieldset class="border border-black p-3 mb-4">';
        print '                <legend class="float-none w-auto px-2">Dados Cadastrais</legend>';
        print '                <div class="row g-3">';

        print '                  <div class="col-md-6">';
        print '                    <label for="responsavel" class="form-label">Nome do Responsável *</label>';
        print '                    <input type="text" class="form-control" id="responsavel" name="nome_representante" required placeholder="Digite o nome do responsável" pattern="^[A-Za-zÀ-ÿ\s]{3,}$" minlength="3" maxlength="100" autocomplete="off">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="razao_social" class="form-label">Razão Social *</label>';
        print '                    <input type="text" class="form-control" id="razao_social" name="razao_social" required placeholder="Digite a razão social" minlength="3" maxlength="150" autocomplete="off">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="nome_fantasia" class="form-label">Nome Fantasia *</label>';
        print '                    <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia" required placeholder="Digite o nome fantasia" minlength="3" maxlength="150" autocomplete="off">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="cnpj_cliente" class="form-label">CNPJ *</label>';
        print '                    <input type="text" class="form-control cnpj_cliente" id="cnpj_cliente"  name="cnpj_cliente" value="' . $_SESSION['cnpj_cliente'] . '" required placeholder="00.000.000/0000-00" pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}" autocomplete="off">';
        print '                  </div>';
        // ===== Telefones com layout revisado =====
        print '  <div class="col-md-12">';
        print '    <div id="telefones-container">';
        print '      <div class="row g-3 telefone-item mb-2">';

        print '        <div class="col-md-4">';
        print '          <label class="form-label">Tipo de Telefone * </label>';
        print '          <select name="telefones[0][tipo]" class="form-select telefone-tipo">';
        print '            <option value="">Selecione</option>';
        print '            <option value="celular">Celular</option>';
        print '            <option value="fixo">Fixo</option>';
        print '          </select>';
        print '        </div>';

        print '        <div class="col-md-8">';
        print '          <label class="form-label">Número de Telefone *</label>';
        print '          <div class="input-group">';
        print '            <input type="tel"  name="telefones[0][numero]" class="form-control telefone telefone-numero" id="telefone_0" placeholder="(00) 00000-0000" autocomplete="off">';
        print '            <button type="button" class="btn btn-outline-danger remover-telefone" title="Remover">';
        print '              <i class="bi bi-x-lg"></i>';
        print '            </button>';
        print '           <button type="button" class="btn btn-outline-success add-telefone" title="Adicionar">';
        print '              <i class="bi bi-plus-lg"></i> Adicionar';
        print '            </button>';
        print '          </div>';
        print '        </div>';
        print '      </div>'; // row
        print '    </div>';   // telefones-container
        print '  </div>';     // col-md-12

        print '                  <div class="col-md-6">';
        print '                    <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>';
        print '                    <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" maxlength="20" placeholder="Digite a inscrição estadual" pattern="^[A-Za-z0-9]{3,20}$" autocomplete="off">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="email" class="form-label">E-mail *</label>';
        print '                    <input type="email" class="form-control" id="email" name="email" required placeholder="Digite o email" maxlength="150" autocomplete="off">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="limite_credito" class="form-label">Limite de Crédito *</label>';
        print '                    <div class="input-group">';
        print '                      <span class="input-group-text">R$</span>';
        print '                      <input type="text" class="form-control dinheiro" id="limite_credito" name="limite_credito" required placeholder="0,00" autocomplete="off">';
        print '                    </div>';
        print '                  </div>';

        print '                </div>'; // row g-3
        print '              </fieldset>';
        print '            </div>'; // col-12

        // ===== Fieldset Endereço =====
        print '            <div class="col-12">';
        print '              <fieldset class="border border-black p-3 mb-3">';
        print '                <legend class="float-none w-auto px-2">Endereço</legend>';
        print '                <div class="row g-3">';

        print '                  <div class="col-md-4">';
        print '                    <label for="cep" class="form-label">CEP *</label>';
        print '                    <input type="text" class="form-control cep" id="cep" name="cep" required placeholder="00000-000" pattern="\d{5}-\d{3}" autocomplete="off">';
        print '                  </div>';

        print '                  <div class="col-md-4">';
        print '                    <label for="cidade" class="form-label">Cidade *</label>';
        print '                    <input type="text" class="form-control" id="cidade" name="cidade" required placeholder="Digite a cidade" pattern="^[A-Za-zÀ-ÿ\s]{2,}$" maxlength="100" autocomplete="off" readolnly>';
        print '                  </div>';

        print '                  <div class="col-md-4">';
        print '                    <label for="estado" class="form-label">Estado (UF) *</label>';
        print '                    <input type="text" class="form-control text-uppercase" id="estado" name="estado" required placeholder="Ex: DF" pattern="[A-Za-z]{2}" maxlength="2" autocomplete="off" readonly>';
        print '                  </div>';

        print '                  <div class="col-md-4">';
        print '                    <label for="bairro" class="form-label">Bairro *</label>';
        print '                    <input type="text" class="form-control" id="bairro" name="bairro" required placeholder="Digite o bairro" maxlength="100" autocomplete="off" readonly>';
        print '                  </div>';

        print '                  <div class="col-md-8">';
        print '                    <label for="complemento" class="form-label">Complemento</label>';
        print '                    <input type="text" class="form-control" id="complemento" name="complemento" placeholder="Digite o complemento" maxlength="100" autocomplete="off">';
        print '                  </div>';

        print '                </div>';
        print '              </fieldset>';
        print '            </div>'; // col-12

        print '          </div>'; // row g-3
        print '        </form>';
        print '      </div>'; // modal-body

        // Rodapé da Modal
        print '      <div class="modal-footer d-flex justify-content-end gap-2">';
        print '        <button type="reset" form="formulario_cliente" class="btn btn-outline-secondary">';
        print '          <i class="bi bi-arrow-counterclockwise"></i> Limpar';
        print '        </button>';
        print '        <button type="submit" form="formulario_cliente" class="btn btn-success" id="cadastrar_cliente" name="cadastrar_cliente">';
        print '          <i class="bi bi-check-circle"></i> Cadastrar';
        print '        </button>';
        print '      </div>';

        print '    </div>'; // modal-content
        print '  </div>'; // modal-dialog
        print '</div>'; // modal
    }
    // alterar cliente
    public function alterar_Cliente(
        $id_cliente,
        $nome_representante,
        $razao_social,
        $nome_fantasia,
        $cnpj_cliente,
        $email,
        $limite_credito,
        $inscricao_estadual,
        $telefone_celular,
        $telefone_fixo,
        $cidade,
        $estado,
        $bairro,
        $cep,
        $complemento
    ) {
        // instancia a classe
        $objcliente = new Cliente();
        // Invocar o método de validar cnpj e o metodo de alterar cliente
        if ($this->validarCNPJ($cnpj_cliente) == false) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'View/cliente.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("CNPJ inválido");
        } else {
            if ($objcliente->alterarCliente(
                $id_cliente,
                $nome_representante,
                $razao_social,
                $nome_fantasia,
                $cnpj_cliente,
                $email,
                $limite_credito,
                $inscricao_estadual,
                $telefone_celular,
                $telefone_fixo,
                $cidade,
                $estado,
                $bairro,
                $cep,
                $complemento
            ) == true) {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'View/cliente.php';
                // Exibir mensagem de sucesso
                $this->mostrarMensagemSucesso("Cliente alterado com sucesso");
            } else {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'View/cliente.php';
                // Exibir mensagem de erro
                $this->mostrarMensagemErro("Erro ao alterar Cliente");
            }
        }
    }
    public function modal_AlterarCliente(
        $id_cliente,
        $nome_representante,
        $razao_social,
        $nome_fantasia,
        $cnpj_cliente,
        $email,
        $limite_credito,
        $inscricao_estadual,
        $telefones,
        $cidade,
        $estado,
        $bairro,
        $cep,
        $complemento
    ) {
        print '<div class="modal fade" id="alterar_cliente' . $id_cliente . '" tabindex="-1" aria-labelledby="alterarClienteLabel' . $id_cliente . '" aria-hidden="true">';
        print '  <div class="modal-dialog modal-dialog-centered modal-lg">';
        print '    <div class="modal-content">';

        // Cabeçalho
        print '      <div class="modal-header">';
        print '        <h6 class="modal-title" id="alterarClienteLabel' . $id_cliente . '">Alterar Cliente</h6>';
        print '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '      </div>';

        // Corpo
        print '      <div class="modal-body">';
        print '        <form action="index.php" method="POST" id="form_alterar_cliente' . $id_cliente . '" class="needs-validation">';
        print '          <input type="hidden" name="id_cliente" value="' . $id_cliente . '">';
        print '          <div class="row g-3">';

        // ===== Fieldset Dados Cadastrais =====
        print '            <div class="col-12">';
        print '              <fieldset class="border border-black p-3 mb-4">';
        print '                <legend class="float-none w-auto px-2">Dados Cadastrais</legend>';
        print '                <div class="row g-3">';

        print '                  <div class="col-md-6">';
        print '                    <label for="responsavel_' . $id_cliente . '" class="form-label">Nome do Responsável *</label>';
        print '                    <input type="text" class="form-control" id="responsavel_' . $id_cliente . '" name="nome_representante" required placeholder="Digite o nome do responsável" pattern="^[A-Za-zÀ-ÿ\s]{3,}$" minlength="3" maxlength="100" autocomplete="off" value="' . $nome_representante . '">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="razao_social_' . $id_cliente . '" class="form-label">Razão Social *</label>';
        print '                    <input type="text" class="form-control" id="razao_social_' . $id_cliente . '" name="razao_social" required placeholder="Digite a razão social" minlength="3" maxlength="150" autocomplete="off" value="' . $razao_social . '">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="nome_fantasia_' . $id_cliente . '" class="form-label">Nome Fantasia *</label>';
        print '                    <input type="text" class="form-control" id="nome_fantasia_' . $id_cliente . '" name="nome_fantasia" required placeholder="Digite o nome fantasia" minlength="3" maxlength="150" autocomplete="off" value="' . $nome_fantasia . '">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="cnpj_cliente_' . $id_cliente . '" class="form-label">CNPJ *</label>';
        print '                    <input type="text" class="form-control cnpj_cliente" id="cnpj_cliente_' . $id_cliente . '" name="cnpj_cliente" required placeholder="00.000.000/0000-00" pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}" autocomplete="off" value="' . $cnpj_cliente . '">';
        print '                  </div>';

        // Telefones com parsing inteligente (aceita string do GROUP_CONCAT ou array)
        print '                  <div class="col-md-12">';
        print '                    <fieldset class="border p-2 mb-3">';
        print '                      <legend class="float-none w-auto px-2">Telefones</legend>';
        print '                      <div id="telefones-container-' . $id_cliente . '">';

        // Preparar linhas de telefone a partir de $telefones
        $rows = [];
        if (!empty($telefones)) {
            if (is_array($telefones)) {
                foreach ($telefones as $t) {
                    $tipoRaw   = $t['tipo'] ?? '';
                    $numeroRaw = $t['numero'] ?? '';
                    $numeroLimpo = preg_replace('/\D/', '', $numeroRaw);
                    $rows[] = ['tipo' => strtolower(trim($tipoRaw)), 'numero' => $numeroLimpo];
                }
            } else {
                // string do GROUP_CONCAT, ex: "celular: 61999998888, fixo: 6133334444"
                $lista = preg_split('/\s*,\s*/', $telefones);
                foreach ($lista as $item) {
                    $item = trim($item);
                    if ($item === '') continue;
                    $partes = preg_split('/\s*:\s*/', $item, 2);
                    if (count($partes) === 2) {
                        $tipo = strtolower(trim($partes[0]));
                        $numeroRaw = trim($partes[1]);
                    } else {
                        $tipo = '';
                        $numeroRaw = trim($partes[0]);
                    }
                    $numeroLimpo = preg_replace('/\D/', '', $numeroRaw);
                    $rows[] = ['tipo' => $tipo, 'numero' => $numeroLimpo];
                }
            }
        }

        // Sempre imprime uma primeira linha vazia (para adicionar novo telefone)
        print '      <div class="row g-3 telefone-item mb-2">';
        print '        <div class="col-md-4">';
        print '          <label class="form-label">Tipo de Telefone *</label>';
        print '          <select name="telefones[0][tipo]" class="form-select telefone-tipo">';
        $options = ['' => 'Selecione...', 'celular' => 'Celular', 'fixo' => 'Fixo'];
        foreach ($options as $val => $label) {
            print "<option value=\"$val\">$label</option>";
        }
        print '          </select>';
        print '        </div>';
        print '        <div class="col-md-8">';
        print '          <label class="form-label">Número de Telefone *</label>';
        print '          <div class="input-group">';
        print '            <input type="tel" name="telefones[0][numero]" class="form-control telefone telefone-numero" id="telefone_' . $id_cliente . '_0" placeholder="(00) 00000-0000" autocomplete="off">';
        print '            <button type="button" class="btn btn-outline-danger remover-telefone" title="Remover"><i class="bi bi-x-lg"></i></button>';
        print '            <button type="button" class="btn btn-outline-success add-telefone" title="Adicionar"><i class="bi bi-plus-lg"></i> Adicionar</button>';
        print '          </div>';
        print '        </div>';
        print '      </div>';

        // Agora imprime os telefones existentes (com índice a partir de 1)
        if (!empty($rows)) {
            $index = 1;
            foreach ($rows as $r) {
                $tipoNorm = htmlspecialchars($r['tipo']);
                $numeroVal = htmlspecialchars($r['numero']);
                print '      <div class="row g-3 telefone-item mb-2">';
                print '        <div class="col-md-4">';
                print '          <label class="form-label">Tipo de Telefone *</label>';
                print '          <select name="telefones[' . $index . '][tipo]" class="form-select telefone-tipo" required>';
                $options = ['celular' => 'Celular', 'fixo' => 'Fixo'];
                foreach ($options as $val => $label) {
                    $sel = ($val === strtolower($tipoNorm)) ? 'selected' : '';
                    print "<option value=\"$val\" $sel>$label</option>";
                }
                print '          </select>';
                print '        </div>';
                print '        <div class="col-md-8">';
                print '          <label class="form-label">Número de Telefone *</label>';
                print '          <div class="input-group">';
                print '            <input type="tel" name="telefones[' . $index . '][numero]" class="form-control telefone telefone-numero" id="telefone_' . $id_cliente . '_' . $index . '" placeholder="(00) 00000-0000" autocomplete="off" value="' . $numeroVal . '" required>';
                print '            <button type="button" class="btn btn-outline-danger remover-telefone" title="Remover"><i class="bi bi-x-lg"></i></button>';
                print '            <button type="button" class="btn btn-outline-success add-telefone" title="Adicionar"><i class="bi bi-plus-lg"></i> Adicionar</button>';
                print '          </div>';
                print '        </div>';
                print '      </div>';
                $index++;
            }
        }
        print '                      </div>'; // telefones-container
        print '                    </fieldset>';
        print '                  </div>'; // col-md-12


        // Outros dados
        print '                  <div class="col-md-6">';
        print '                    <label for="inscricao_estadual_' . $id_cliente . '" class="form-label">Inscrição Estadual</label>';
        print '                    <input type="text" class="form-control" id="inscricao_estadual_' . $id_cliente . '" name="inscricao_estadual" maxlength="20" placeholder="Digite a inscrição estadual" pattern="^[A-Za-z0-9]{3,20}$" autocomplete="off" value="' . $inscricao_estadual . '">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="email_' . $id_cliente . '" class="form-label">E-mail *</label>';
        print '                    <input type="email" class="form-control" id="email_' . $id_cliente . '" name="email" required placeholder="Digite o email" maxlength="150" autocomplete="off" value="' . $email . '">';
        print '                  </div>';

        print '                  <div class="col-md-6">';
        print '                    <label for="limite_credito_' . $id_cliente . '" class="form-label">Limite de Crédito *</label>';
        print '                    <div class="input-group">';
        print '                      <span class="input-group-text">R$</span>';
        print '                      <input type="text" class="form-control dinheiro" id="limite_credito_' . $id_cliente . '" name="limite_credito" required placeholder="0,00" autocomplete="off" value="' . $limite_credito . '">';
        print '                    </div>';
        print '                  </div>';

        print '                </div>'; // row g-3
        print '              </fieldset>';
        print '            </div>'; // col-12

        /// ===== Fieldset Endereço (readonly exceto CEP) =====
        print '            <div class="col-12">';
        print '              <fieldset class="border border-black p-3 mb-3">';
        print '                <legend class="float-none w-auto px-2">Endereço</legend>';
        print '                <div class="row g-3">';

        print '                  <div class="col-md-4">';
        print '                    <label for="cep_' . $id_cliente . '" class="form-label">CEP *</label>';
        print '                    <input type="text" class="form-control cep" id="cep_' . $id_cliente . '" name="cep" required value="' . htmlspecialchars($cep) . '" placeholder="00000-000">';
        print '                  </div>';

        print '                  <div class="col-md-4">';
        print '                    <label for="cidade_' . $id_cliente . '" class="form-label">Cidade *</label>';
        print '                    <input type="text" class="form-control" id="cidade_' . $id_cliente . '" name="cidade" value="' . htmlspecialchars($cidade) . '" readonly>';
        print '                  </div>';

        print '                  <div class="col-md-4">';
        print '                    <label for="estado_' . $id_cliente . '" class="form-label">Estado *</label>';
        print '                    <input type="text" class="form-control" id="estado_' . $id_cliente . '" name="estado" value="' . htmlspecialchars($estado) . '" readonly>';
        print '                  </div>';

        print '                  <div class="col-md-4">';
        print '                    <label for="bairro_' . $id_cliente . '" class="form-label">Bairro *</label>';
        print '                    <input type="text" class="form-control" id="bairro_' . $id_cliente . '" name="bairro" value="' . htmlspecialchars($bairro) . '" readonly>';
        print '                  </div>';

        print '                  <div class="col-md-8">';
        print '                    <label for="complemento_' . $id_cliente . '" class="form-label">Complemento</label>';
        print '                    <input type="text" class="form-control" id="complemento_' . $id_cliente . '" name="complemento" value="' . htmlspecialchars($complemento) . '" readonly>';
        print '                  </div>';

        print '                </div>';
        print '              </fieldset>';
        print '            </div>'; // col-12

        print '          </div>'; // row g-3

        // Rodapé
        print '          <div class="modal-footer d-flex justify-content-end gap-2">';
        print '            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">';
        print '              <i class="bi bi-x-circle"></i> Cancelar';
        print '            </button>';
        print '            <button type="submit" class="btn btn-primary" name="alterar_cliente">';
        print '              <i class="bi bi-check-circle"></i> Alterar';
        print '            </button>';
        print '          </div>';
        print '        </form>';
        print '      </div>'; // modal-body

        print '    </div>'; // modal-content
        print '  </div>'; // modal-dialog
        print '</div>'; // modal
    }
    // excluir cliente
    public function excluir_Cliente($id_cliente)
    {
        // instancia a classe
        $objcliente = new Cliente();
        // invocar o metodo de  verificar se o cliente tem pedidos
        if ($objcliente->clienteEmAlgumPedido($id_cliente) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do usuário
            include_once 'View/cliente.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Cliente não pode ser excluído, pois possui pedidos associados");
        } else {
            if ($objcliente->excluirCliente($id_cliente) == true) {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'View/cliente.php';
                // Exibir mensagem de sucesso
                $this->mostrarMensagemSucesso("Cliente excluído com sucesso");
            } else {
                session_start();
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do usuário
                include_once 'View/cliente.php';
                // Exibir mensagem de erro
                $this->mostrarMensagemErro("Erro ao excluir Cliente");
            }
        }
    }
    // modal excluir cliente
    public function modal_ExcluirCliente($id_cliente, $razao_social)
    {
        print '<div class="modal fade" id="excluir_cliente' . $id_cliente . '" tabindex="-1" aria-labelledby="excluirClienteLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content">';
        print '<div class="modal-header">';
        print '<h5 class="modal-title" id="excluirClienteLabel">Excluir Cliente</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '</div>';
        print '<div class="modal-body">';
        print '<p>Tem certeza que deseja excluir o Cliente <strong>' . $razao_social . '</strong>?</p>';
        print '</div>';
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer">';
        print '<input type="hidden" name="id_cliente" value="' . $id_cliente . '">';
        print '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
        print '<button type="submit" name="excluir_cliente" class="btn btn-danger">Excluir</button>';
        print '</div>';
        print '</form>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
    }
    // select de clientes
    public function selectClientes($id_cliente = null)
    {
        // Instancia a classe
        $objCliente = new Cliente();
        // Invocar o método para obter a lista de clientes
        $clientes = $objCliente->consultarCliente(null, null, null);
        print '<select name="id_cliente" class="form-select" aria-label="Default select example">';
        print '<option selected value="">Selecione o Cliente </option>';
        foreach ($clientes as $key => $valor) {
            if ($valor->id_cliente == $id_cliente) {
                print '<option selected value="' . $valor->id_cliente . '">' . $valor->nome_fantasia . '</option>';
            } else {
                print '<option value="' . $valor->id_cliente . '">' . $valor->nome_fantasia . '</option>';
            }
        }
        print '</select>';
    }
















    // FORMA DE PAGAMENTO

    // Método de consultar Forma de Pagamento
    public function consultarForma_Pagamento($forma_pagamento)
    {
        // Instancia a classe
        $objFormaPagamento = new FormaPagamento();

        // Invocar o método uma única vez e armazenar o resultado
        $objFormaPagamento->consultarFormaPagamento($forma_pagamento);

        // Verificar se houve retorno de resultados
        if ($objFormaPagamento->consultarFormaPagamento($forma_pagamento) == true) {
            session_start();
            // Passa os resultados para a view
            $formas_pagamento = $objFormaPagamento->consultarFormaPagamento($forma_pagamento);
            $this->menu();
            include_once 'View/pedido.php';
        } else {
            $this->menu();
            include_once 'View/pedido.php';
            $this->mostrarMensagemErro("Forma de pagamento não encontrada");
        }
    }
    // cadastrar forma de pagamento
    public function cadastrarForma_Pagamento($forma_pagamento)
    {
        // instancia a classe
        $objFormaPagamento = new FormaPagamento();
        // Invocar o método da classe FormaPagamento para cadastrar a forma de pagamento
        if ($objFormaPagamento->cadastrarFormaPagamento($forma_pagamento) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do pedido
            include_once 'View/pedido.php';
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Forma de pagamento cadastrada com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do pedido
            include_once 'View/pedido.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao cadastrar Forma de pagamento");
        }
    }
    //alterar forma de pagamento
    public function alterarForma_Pagamento($id_forma_pagamento, $forma_pagamento)
    {
        // instancia a classe
        $objFormaPagamento = new FormaPagamento();
        // Invocar o método da classe FormaPagamento para alterar a forma de pagamento
        if ($objFormaPagamento->alterarFormaPagamento($id_forma_pagamento, $forma_pagamento) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do pedido
            include_once 'View/pedido.php';
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Forma de pagamento alterada com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do pedido
            include_once 'View/pedido.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao alterar Forma de pagamento");
        }
    }
    // excluir forma de pagamento
    public function excluirForma_Pagamento($id_forma_pagamento, $forma_pagamento)
    {
        // instancia a classe
        $objFormaPagamento = new FormaPagamento();
        // Invocar o método da classe FormaPagamento para excluir a forma de pagamento
        if ($objFormaPagamento->excluirFormaPagamento($id_forma_pagamento, $forma_pagamento) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do pedido
            include_once 'View/pedido.php';
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Forma de pagamento excluída com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view do pedido
            include_once 'View/pedido.php';
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao excluir Forma de pagamento");
        }
    }
    // modal de alterar forma de pagamento
    public function modalAlterarForma_Pagamento($id_forma_pagamento, $forma_pagamento)
    {
        print '<div class="modal fade" id="alterar_forma_pagamento' . $id_forma_pagamento . '" tabindex="-1" aria-labelledby="alterarFormaPagamentoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content">';
        print '<div class="modal-header">';
        print '<h5 class="modal-title" id="alterarFormaPagamentoLabel"> Alterar Forma de Pagamento</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '</div>';
        print '<div class="modal-body">';
        print '<form action="index.php" method="post">';
        print '<div class="row g-3">';
        print '<div class="col-md-12">';
        print '<input type="text" class="form-control" id="forma_pagamento" name="descricao" value="' . $forma_pagamento . '" required>';
        print '</div>';
        print '</div>';
        print '<div class="d-flex justify-content-center gap-2 mt-4">';
        print '<input type="hidden" name="id_forma_pagamento" value="' . $id_forma_pagamento . '">';
        print '<button type="button" class="btn btn-outline-secondary w-50 py-2" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-circle"></i> Fechar';
        print '</button>';
        print '<button type="submit" name="alterar_forma_pagamento" class="btn btn-primary w-50 py-2">';
        print '<i class="bi bi-check-circle"></i> Alterar';
        print '</button>';
        print '</div>';
        print '</form>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
    }
    // modal de exclusão de forma de pagamento
    public function modalExcluirForma_Pagamento($id_forma_pagamento, $forma_pagamento)
    {
        print '<div class="modal fade" id="excluir_forma_pagamento' . $id_forma_pagamento . '" tabindex="-1" aria-labelledby="excluirFormaPagamentoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content">';
        print '<div class="modal-header">';
        print '<h5 class="modal-title" id="excluirFormaPagamentoLabel">Excluir Forma de Pagamento</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '</div>';
        print '<div class="modal-body">';
        print 'Tem certeza que deseja excluir a forma de pagamento <strong>' . $forma_pagamento . '</strong>?';
        print '</div>';
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer">';
        print '<input type="hidden" name="id_forma_pagamento" value="' . $id_forma_pagamento . '">';
        print '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
        print '<button type="submit" name="excluir_forma_pagamento" class="btn btn-danger">Excluir</button>';
        print '</div>';
        print '</form>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
    }
    // tabela de consulta de forma de pagamento
    public function tabelaConsultarForma_Pagamento($forma_pagamento)
    {
        if (empty($forma_pagamento)) return;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-striped table-hover table-bordered align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Descrição</th>';
        if ($this->temPermissao(['Administrador'])) {
            print '<th scope="col">Ações</th>';
        }
        print '</tr>';
        print '</thead>';
        print '<tbody>';
        foreach ($forma_pagamento as $valor) {
            print '<tr>';
            print '<td>' . $valor->descricao . '</td>';
            if ($this->temPermissao(['Administrador'])) {
                print '<td>';
                print '<div class="d-flex gap-2 justify-content-center flex-wrap">';
                print '<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#alterar_forma_pagamento' . $valor->id_forma_pagamento . '"><i class="bi bi-pencil-square"></i></button>';
                print '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#excluir_forma_pagamento' . $valor->id_forma_pagamento . '"><i class="bi bi-trash"></i></button>';
                print '</div>';
                print '</td>';
            }
            print '</tr>';
        }
        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // select de consulta de formas pagamento
    public function selectConsultaForma_Pagamento($id_forma_pagamento = null)
    {
        $objFormaPagamento = new FormaPagamento();
        $formas_pagamento = $objFormaPagamento->consultarFormaPagamento(null);

        print '<label for="id_forma_pagamento" class="form-label">Forma de Pagamento</label>';
        print '<select name="id_forma_pagamento" id="id_forma_pagamento" class="form-select" aria-label="Selecionar forma de pagamento">';
        // Opção inicial
        $selected = empty($id_forma_pagamento) ? 'selected' : '';
        print "<option value=\"\" $selected>Forma de Pagamento</option>";

        // Opções dinâmicas
        foreach ($formas_pagamento as $forma) {
            $selected = ($forma->id_forma_pagamento == $id_forma_pagamento) ? 'selected' : '';
            print "<option value=\"{$forma->id_forma_pagamento}\" $selected>{$forma->descricao}</option>";
        }

        print '</select>';
    }


    //Pedido


    // metodo de buscar cliente AJAX
    public function buscarCliente($cliente)
    {
        $objCliente = new Cliente();
        $clientes = $objCliente->consultarClientePedido($cliente);
        if (!empty($clientes)) {
            foreach ($clientes as $valor) {
                $id_cliente = $valor['id_cliente'];
                $nome = $valor['nome_fantasia'];
                print "<span class='list-group-item list-group-item-action cliente-item' data-id='$id_cliente' >$nome</span>";
            }
        } else {
            print "<span class='list-group-item text-danger'>Nenhum cliente encontrado</span>";
        }
    }
    // metodo de limite de credito
    public function verificarLimiteCredito($id_cliente, $valor_totalPedido)
    {
        $objCliente = new Cliente();
        $resultado = $objCliente->verificar_LimiteCredito($id_cliente, $valor_totalPedido);
        // Só retorna algo se o limite foi excedido
        if ($resultado['status'] === false) {
            header('Content-Type: application/json');
            echo json_encode([
                "status" => false,
                "mensagem" => $resultado['mensagem'],
                "limite_credito" => $resultado['limite_credito'],
                "valor_total" => $resultado['valor_total'],
                "excedente" => $resultado['excedente']
            ]);
            exit;
        }

        // Se o limite for aceito, não retorna nada
    }
    // metodo de buscar produto AJAX
    public function buscarProduto($produto, $tipo)
    {
        $objProduto = new Produto();
        $produtos = $objProduto->consultarProdutoDinamico($produto);
        if (!empty($produtos)) {
            $produtos = array_filter($produtos, function ($produto) {
                return $produto['quantidade'] > 0;
            });
            foreach ($produtos as $valor) {
                $id_produto = $valor['id_produto'];
                $nome = htmlspecialchars($valor['nome_produto']);
                $largura = htmlspecialchars($valor['largura']);
                $cor = htmlspecialchars($valor['cor']);
                if ($tipo === 'relatorio') {
                    print "<span class='list-group-item list-group-item-action produto-item'
                    data-id='{$id_produto}'
                    data-nome='{$nome}'
                    data-cor='{$cor}'
                    data-largura='{$largura}'>
                    {$nome} - {$cor} - {$largura} (m)
                </span>";
                } else {
                    $valor_venda = ($valor['valor_venda']);
                    $quantidade = ($valor['quantidade']);
                    print "<span class='list-group-item list-group-item-action produto-item'
                    data-id='{$id_produto}'
                    data-nome='{$nome}'
                    data-largura='{$largura}'
                    data-cor='{$cor}'
                    data-valorVenda='{$valor_venda}'
                    data-quantidade='{$quantidade}'>
                    {$nome} - Cor: {$cor} - Largura: {$largura} (m) - Valor: R$ {$valor_venda} - Qtd.: {$quantidade} (m)
                </span>";
                }
            }
        } else {
            print "<span class='list-group-item text-danger'>Nenhum produto encontrado</span>";
        }
    }
    // verificar a quantidade do produto no banco de dados
    public function verificarQuantidade($id_produto, $quantidade)
    {
        $objProduto = new Produto();
        // Invocar o método da classe Produto para verificar a quantidade
        if ($objProduto->verificarQuantidadeProduto($id_produto, $quantidade) == true) {
            print "ok";
        } else {
            print "erro_quantidade";
        }
    }
    // cadastrar pedido
    public function cadastrar_Pedido(
        $id_cliente,
        $data_pedido,
        $status_pedido,
        $valor_total,
        $id_forma_pagamento,
        $valor_frete,
        $itens,
        $origem
    ) {
        // instancia a classe
        $objPedido = new Pedido();
        // Invocar o método da classe Pedido para cadastrar o pedido
        if ($objPedido->cadastrarPedido(
            $id_cliente,
            $data_pedido,
            $status_pedido,
            $valor_total,
            $id_forma_pagamento,
            $valor_frete,
            $itens
        ) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            if ($origem === 'pedido') {
                // Incluir a view do pedido
                include_once 'View/pedido.php';
            } else {
                // Incluir a view do pedido
                include_once 'View/principal.php';
            }
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Pedido cadastrado com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            if ($origem === 'pedido') {
                // Incluir a view do pedido
                include_once 'View/pedido.php';
            } else {
                // Incluir a view do pedido
                include_once 'View/principal.php';
            }
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao cadastrar Pedido");
        }
    }
    // consultar pedidos
    public function consultar_Pedido($numero_pedido, $id_cliente, $data_pedido, $status_pedido, $id_forma_pagamento, $origem)
    {
        // Instaciar a classe
        $objPedido = new Pedido();
        // Invocar o método da classe Pedido para consultar os pedidos
        $objPedido->consultarPedido($numero_pedido, $id_cliente, $data_pedido, $status_pedido, $id_forma_pagamento);
        // Verificar se houve retorno de resultados
        if ($objPedido->consultarPedido($numero_pedido, $id_cliente, $data_pedido, $status_pedido, $id_forma_pagamento) == true) {
            session_start();
            // Passa os resultados para a view
            $pedidos = $objPedido->consultarPedido($numero_pedido, $id_cliente, $data_pedido, $status_pedido, $id_forma_pagamento);
            if ($origem === 'pedido') {
                // Carregar o menu
                $menu = $this->menu();
                include_once 'View/pedido.php';
            } else if ($origem === 'principal') {
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do principal
                include_once 'View/principal.php';
            }
        } else {
            session_start();
            if ($origem === 'pedido') {
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do pedido
                include_once 'View/pedido.php';
            } else {
                // Carregar o menu
                $menu = $this->menu();
                // Incluir a view do principal
                include_once 'View/principal.php';
            }
            $this->mostrarMensagemErro("Nenhum pedido encontrado");
        };
    }
    // tabela de comnsulta de pedido
    public function tabelaConsultar_Pedido($pedidos)
    {
        if (empty($pedidos)) return;

        // Agrupar itens por pedido
        $pedidosAgrupados = [];
        foreach ($pedidos as $pedido) {
            $id = $pedido->id_pedido;

            if (!isset($pedidosAgrupados[$id])) {
                $pedidosAgrupados[$id] = [
                    'dados' => $pedido,
                    'itens' => [],
                ];
            }

            if (!empty($pedido->id_item_pedido)) {
                $pedidosAgrupados[$id]['itens'][] = [
                    'nome_produto' => $pedido->nome_produto,
                    'quantidade' => $pedido->quantidade,
                ];
            }
        }

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-striped table-hover table-bordered align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Nº Pedido</th>';
        print '<th scope="col">Cliente</th>';
        print '<th scope="col">Data</th>';
        print '<th scope="col">Status</th>';
        print '<th scope="col">Valor Total</th>';
        print '<th scope="col">Ações</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        foreach ($pedidosAgrupados as $id => $pedidoAgrupado) {
            $pedido = $pedidoAgrupado['dados'];
            $status = $pedido->status_pedido;

            print '<tr>';
            print '<th scope="row">' . htmlspecialchars($pedido->numero_pedido) . '</th>';
            print '<td>' . htmlspecialchars($pedido->nome_fantasia) . '</td>';
            print '<td>' . date('d/m/Y', strtotime($pedido->data_pedido)) . '</td>';
            print '<td>' . htmlspecialchars($status) . '</td>';
            print '<td>R$ ' . number_format($pedido->valor_total, 2, ',', '.') . '</td>';

            // Ações
            print '<td><div class="d-flex gap-2 justify-content-center flex-wrap">';

            if ($this->temPermissao(['Administrador'])) {
                switch ($status) {
                    case 'Pendente':
                        // Pode alterar, aprovar ou excluir
                        $this->botao('success', 'aprovar_pedido' . $pedido->numero_pedido, 'bi-check-circle', 'Aprovar Pedido');
                        $this->botao('warning', 'modal_alterar_pedido_' . $pedido->numero_pedido, 'bi-pencil-square', 'Alterar Pedido');
                        $this->botao('danger', 'excluir_pedido' . $pedido->numero_pedido, 'bi-trash', 'Excluir Pedido');
                        break;

                    case 'Aprovado':
                        // Pode finalizar o pedido
                        $this->botao('success', 'finalizar_pedido' . $pedido->numero_pedido, 'bi-check-circle', 'Finalizar Pedido');
                        break;
                    case 'Aguardando Pagamento':
                        // Pode cancelar ou finalizar
                        $this->botao('danger', 'cancelar_pedido' . $pedido->numero_pedido, 'bi-x-circle', 'Cancelar Pedido');
                        $this->botao('success', 'finalizar_pedido' . $pedido->numero_pedido, 'bi-check-circle', 'Finalizar Pedido');
                        break;
                    // Finalizado e Cancelado: apenas visualização
                    default:
                        // Não ha mais acoes
                        break;
                }
            }
            // Botões sempre visíveis: Detalhes e Imprimir
            $this->botao('info', 'detalhes_pedido' . $pedido->numero_pedido, 'bi-eye', 'Detalhes do Pedido');
            print '<a class="btn btn-secondary btn-sm" title="Imprimir Pedido" href="index.php?acao=imprimir_pedido&numero_pedido=' . urlencode($pedido->numero_pedido) . '" target="_blank">';
            print '<i class="bi bi-printer me-1"></i>';
            print '</a>';
            print '</div>
                    </td>';
            print '</tr>';
        }
        print    '</tbody>
            </table>
        </div>';
    }
    // Método auxiliar para criar botões
    private function botao($classe, $target, $icone, $titulo = '')
    {
        print '<button class="btn btn-' . $classe . ' btn-sm" data-bs-toggle="modal" data-bs-target="#' . $target . '" title="' . $titulo . '">';
        print '<i class="bi ' . $icone . '"></i>';
        print '</button>';
    }
    // modal de detalhes do pedido
    public function modalDetalhesPedido($pedidos)
    {
        if (empty($pedidos)) return;

        // Agrupar itens por pedido
        $pedidosAgrupados = [];

        foreach ($pedidos as $pedido) {
            $id = $pedido->id_pedido;

            if (!isset($pedidosAgrupados[$id])) {
                $pedidosAgrupados[$id] = [
                    'dados' => $pedido,
                    'itens' => [],
                ];
            }

            if (!empty($pedido->id_item_pedido)) {
                $pedidosAgrupados[$id]['itens'][] = [
                    'nome_produto' => $pedido->nome_produto,
                    'quantidade' => $pedido->quantidade,
                    'valor_unitario' => $pedido->valor_unitario,
                    'totalValor_produto' => $pedido->totalValor_produto
                ];
            }
        }

        foreach ($pedidosAgrupados as $id => $pedidoAgrupado) {
            $pedido = $pedidoAgrupado['dados'];
            $itens = $pedidoAgrupado['itens'];

            print '
        <div class="modal fade" id="detalhes_pedido' . $pedido->numero_pedido . '" tabindex="-1" aria-labelledby="detalhesPedidoLabel' . $pedido->numero_pedido . '" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="detalhesPedidoLabel' . $pedido->numero_pedido . '">Pedido Nº ' . $pedido->numero_pedido . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container">

                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-1">Informações do Cliente</h6>
                                </div>
                                <div class="col-md-6"><strong>Cliente:</strong> ' . htmlspecialchars($pedido->nome_fantasia) . '</div>
                                <div class="col-md-6"><strong>Data do Pedido:</strong> ' . date('d/m/Y', strtotime($pedido->data_pedido)) . '</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-1">Dados do Pedido</h6>
                                </div>
                                <div class="col-md-6"><strong>Status:</strong> ' . htmlspecialchars($pedido->status_pedido) . '</div>
                                <div class="col-md-6"><strong>Forma de Pagamento:</strong> ' . htmlspecialchars($pedido->descricao) . '</div>
                                <div class="col-md-6 mt-2"><strong>Valor Total:</strong> R$ ' . number_format($pedido->valor_total, 2, ',', '.') . '</div>
                                <div class="col-md-6 mt-2"><strong>Valor do Frete:</strong> R$ ' . number_format($pedido->valor_frete, 2, ',', '.') . '</div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-1">Itens do Pedido</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Produto</th>
                                                    <th class="text-center">Quantidade (m)</th>
                                                    <th class="text-center">Valor Unitario (R$) </th>
                                                    <th class="text-center">Total Produto (R$) </th>
                                                </tr>
                                            </thead>
                                            <tbody>';
            if (!empty($itens)) {
                foreach ($itens as $item) {
                    print '
                                                <tr>
                                                    <td>' . htmlspecialchars($item['nome_produto']) . '</td>
                                                    <td class="text-center">' . $item['quantidade'] . '</td>
                                                    <td class="text-center">' . $item['valor_unitario'] . '</td>
                                                    <td class="text-center">' . $item['totalValor_produto'] . '</td>
                                                </tr>';
                }
            } else {
                print '
                                                <tr>
                                                    <td colspan="2" class="text-center">Nenhum item encontrado.</td>
                                                </tr>';
            }

            print '
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>';
        }
    }
    //metodo de excluir pedido
    public function excluir_Pedido($id_pedido, $origem)
    {
        // instancia a classe
        $objPedido = new Pedido();

        // Invocar o método da classe Pedido para excluir o pedido
        if ($objPedido->excluirPedido($id_pedido) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view
            if ($origem === 'pedido') {
                include_once 'View/pedido.php';
            } else {
                include_once 'View/principal.php';
            }
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Pedido excluído com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view
            if ($origem === 'pedido') {
                include_once 'View/pedido.php';
            } else {
                include_once 'View/principal.php';
            }
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao excluir Pedido");
        }
    }
    // modal de excluir pedido
    public function modalExcluirPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia)
    {
        print '<div class="modal fade" id="excluir_pedido' . $numero_pedido . '" tabindex="-1" aria-labelledby="excluirPedidoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content">';
        print '<div class="modal-header">';
        print '<h5 class="modal-title" id="excluirPedidoLabel">Excluir Pedido Nº ' . $numero_pedido . '</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        print '</div>';
        print '<div class="modal-body">';
        print 'Tem certeza que deseja excluir o pedido <strong>Nº ' . $numero_pedido . '</strong> do cliente <strong>' . htmlspecialchars($nome_fantasia) . '</strong>?';
        print '</div>';
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer">';
        print '<input type="hidden" name="id_pedido" value="' . $id_pedido . '">';
        print '<input type="hidden" name="origem" value="' . $origem . '">';
        print '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
        print '<button type="submit" name="excluir_pedido" class="btn btn-danger">Excluir</button>';
        print '</div>';
        print '</form>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
    }
    // modal de alterar pedido
    public function modalAlterarPedido($pedidos)
    {
        if (empty($pedidos)) return;

        // Agrupar itens por pedido
        $pedidosAgrupados = [];

        foreach ($pedidos as $pedido) {
            $id = $pedido->id_pedido;

            if (!isset($pedidosAgrupados[$id])) {
                $pedidosAgrupados[$id] = [
                    'dados' => $pedido,
                    'itens' => [],
                ];
            }

            if (!empty($pedido->id_item_pedido)) {
                $pedidosAgrupados[$id]['itens'][] = [
                    'id_produto'     => $pedido->id_produto,
                    'nome_produto'   => $pedido->nome_produto,
                    'quantidade'     => $pedido->quantidade,
                    'valor_unitario' => $pedido->valor_unitario,
                ];
            }
        }

        foreach ($pedidosAgrupados as $pedido) {
            $idModal = 'modal_alterar_pedido_' . $pedido['dados']->numero_pedido;
            $dados   = $pedido['dados'];
            $itens   = $pedido['itens'];

            $valorFrete = isset($dados->valor_frete) ? $dados->valor_frete : 0;

            print '<div class="modal fade modal-alterar-pedido" id="' . $idModal . '" tabindex="-1" aria-labelledby="' . $idModal . '_label" aria-hidden="true">';
            print '  <div class="modal-dialog modal-xl modal-dialog-centered">';
            print '    <div class="modal-content">';

            // Cabeçalho
            print '      <div class="modal-header bg-light">';
            print '        <h5 class="modal-title fw-bold" id="' . $idModal . '_label">';
            print '          <i class="bi bi-pencil-square me-2"></i> Alterar Pedido #' . $dados->numero_pedido;
            print '        </h5>';
            print '        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
            print '      </div>';
            // Corpo
            print '      <div class="modal-body">';
            print '        <form action="index.php" method="POST" class="form-alterar-pedido" id="form_' . $dados->numero_pedido . '">';
            print '          <input type="hidden" name="origem" value="pedido">';
            print '          <input type="hidden" name="id_pedido" value="' . $dados->id_pedido . '">';
            print '          <input type="hidden" name="alterar_pedido" value="1">';
            print '          <div class="row g-4">';
            /** LADO ESQUERDO **/
            print '            <div class="col-md-4">';
            // Cliente
            print '              <fieldset class="border rounded p-3 mb-4">';
            print '                <legend class="float-none w-auto px-3 fw-semibold text-primary">Cliente</legend>';
            print '                <div class="mb-3 position-relative">';
            print '                  <label for="cliente_pedido_' . $dados->numero_pedido . '" class="form-label">Buscar Cliente</label>';
            print '                  <div class="input-group">';
            print '                    <span class="input-group-text"><i class="bi bi-search"></i></span>';
            print '                    <input type="text" class="form-control" id="cliente_pedido_' . $dados->numero_pedido . '" name="cliente_pedido" value="' . htmlspecialchars($dados->nome_fantasia) . '" placeholder="Nome fantasia, razão social ou CNPJ" autocomplete="off">';
            print '                  </div>';
            print '                  <div id="resultado_busca_cliente_' . $dados->numero_pedido . '" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;"></div>';
            print '                  <input type="hidden" name="id_cliente" value="' . $dados->id_cliente . '">';
            print '                </div>';
            print '              </fieldset>';

            // Dados do pedido
            print '              <fieldset class="border rounded p-3 mb-4">';
            print '                <legend class="float-none w-auto px-3 fw-semibold text-primary">Dados do Pedido</legend>';
            print '                <div class="row g-3">';
            print '                  <div class="col-md-6">';
            print '                    <label for="frete_' . $dados->numero_pedido . '" class="form-label">Frete</label>';
            print '                    <input type="text" class="form-control frete" id="frete_' . $dados->numero_pedido . '" name="valor_frete" value="R$ ' . number_format($valorFrete, 2, ',', '.') . '">';
            print '                  </div>';
            print '                  <div class="col-md-6">';
            print '                    <label for="valor_total_' . $dados->numero_pedido . '" class="form-label">Valor Total</label>';
            print '                    <input type="text" class="form-control" id="valor_total_' . $dados->numero_pedido . '" name="valor_total" readonly value="R$ ' . number_format($dados->valor_total, 2, ',', '.') . '">';
            print '                  </div>';
            print '                  <div class="col-12">';
            $this->selectConsultaForma_Pagamento($dados->id_forma_pagamento);
            print '                  </div>';
            print '                </div>';
            print '              </fieldset>';
            print '            </div>';

            /** LADO DIREITO **/
            print '            <div class="col-md-8">';
            print '              <fieldset class="border rounded p-3 h-100">';
            print '                <legend class="float-none w-auto px-3 fw-semibold text-primary">Produtos</legend>';

            // Busca produto
            print '                <div class="row g-3 align-items-end mb-3">';
            print '                  <div class="col-md-8 position-relative">';
            print '                    <label for="produto_pedido_' . $dados->numero_pedido . '" class="form-label">Buscar Produto</label>';
            print '                    <div class="input-group">';
            print '                      <span class="input-group-text"><i class="bi bi-search"></i></span>';
            print '                      <input type="text" class="form-control" id="produto_pedido_' . $dados->numero_pedido . '" name="produto_pedido" placeholder="Digite o nome, cor ou código do produto" autocomplete="off">';
            print '                    </div>';
            print '                    <div id="resultado_busca_produto_' . $dados->numero_pedido . '" class="list-group position-absolute top-100 start-0 w-100 zindex-dropdown shadow" style="max-height: 200px; overflow-y: auto;"></div>';
            print '                  </div>';
            print '                  <div class="col-md-4">';
            print '                    <label for="quantidade_' . $dados->numero_pedido . '" class="form-label">Quantidade</label>';
            print '                    <div class="input-group">';
            print '                      <input type="text" class="form-control" id="quantidade_' . $dados->numero_pedido . '" name="quantidade" min="1" autocomplete="off">';
            print '                      <button type="button" class="btn btn-outline-primary" id="adicionar_produto_' . $dados->numero_pedido . '">';
            print '                        <i class="bi bi-plus"></i>';
            print '                      </button>';
            print '                    </div>';
            print '                  </div>';
            print '                </div>';
            // Tabela produtos
            print '                <div class="table-responsive">';
            print '                  <label class="form-label fw-semibold">Produtos do Pedido</label>';
            print '                  <table class="table table-bordered table-striped table-sm align-middle text-center">';
            print '                    <thead class="table-light">';
            print '                      <tr>';
            print '                        <th>Produto</th>';
            print '                        <th>Quantidade</th>';
            print '                        <th>Valor Unitário</th>';
            print '                        <th>Valor Total</th>';
            print '                        <th>Ação</th>';
            print '                      </tr>';
            print '                    </thead>';
            print '                    <tbody id="tbody_lista_pedido_' . $dados->numero_pedido . '">';

            foreach ($itens as $item) {
                $valorTotalLinha = $item['quantidade'] * $item['valor_unitario'];
                print '                      <tr data-id-produto="' . $item['id_produto'] . '">';
                print '                        <td>' . htmlspecialchars($item['nome_produto']) . '</td>';
                print '                        <td><input type="text" class="form-control form-control-sm text-center" name="itens[' . $item['id_produto'] . '][quantidade]" value="' . $item['quantidade'] . '" min="1"></td>';
                print '                        <td>R$ ' . number_format($item['valor_unitario'], 2, ',', '.') . '</td>';
                print '                        <td>R$ ' . number_format($valorTotalLinha, 2, ',', '.') . '</td>';
                // A célula da ação vai ficar vazia — o JS insere o botão
                print '                        <td></td>';
                print '                        <input type="hidden" name="itens[' . $item['id_produto'] . '][id_produto]" value="' . $item['id_produto'] . '">';
                print '                        <input type="hidden" name="itens[' . $item['id_produto'] . '][valor_unitario]" value="' . $item['valor_unitario'] . '">';
                print '                        <input type="hidden" name="itens[' . $item['id_produto'] . '][valor_total]" value="' . $valorTotalLinha . '">';
                print '                      </tr>';
            }
            print '                    </tbody>';
            print '                  </table>';
            print '                </div>';
            print '              </fieldset>';
            print '            </div>';

            print '          </div>'; // fim row

            // Rodapé
            print '          <div class="modal-footer">';
            print '            <button type="submit" class="btn btn-success" id="alterar_pedido_' . $dados->numero_pedido . '">';
            print '              <i class="bi bi-check-circle me-1"></i> Salvar Alterações';
            print '            </button>';
            print '            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">';
            print '              <i class="bi bi-x-lg me-1"></i> Fechar';
            print '            </button>';
            print '          </div>';
            print '        </form>';
            print '      </div>';
            print '    </div>';
            print '  </div>';
            print '</div>';
        }
    }
    // metodo para alterar o pedido
    public function alterar_Pedido(
        $id_pedido,
        $id_cliente,
        $valor_total,
        $id_forma_pagamento,
        $valor_frete,
        $itens,
        $origem
    ) {
        // instancia a classe
        $objPedido = new Pedido();
        // Invocar o método da classe Pedido para cadastrar o pedido
        if ($objPedido->alterarPedido(
            $id_pedido,
            $id_cliente,
            $valor_total,
            $id_forma_pagamento,
            $valor_frete,
            $itens
        ) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            if ($origem === 'pedido') {
                // Incluir a view do pedido
                include_once 'View/pedido.php';
            } else {
                // Incluir a view do pedido
                include_once 'View/principal.php';
            }
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Pedido alterado com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            if ($origem === 'pedido') {
                // Incluir a view do pedido
                include_once 'View/pedido.php';
            } else {
                // Incluir a view do pedido
                include_once 'View/principal.php';
            }
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao alterar Pedido");
        }
    }
    // modal de aprovar o pedido
    public function modalAprovarPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia)
    {
        print '<div class="modal fade" id="aprovar_pedido' . $numero_pedido . '" tabindex="-1" aria-labelledby="aprovarPedidoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content border-warning shadow">';
        // Cabeçalho
        print '<div class="modal-header bg-warning text-dark">';
        print '<h5 class="modal-title fw-bold" id="aprovarPedidoLabel">';
        print '<i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmação de Aprovação';
        print '</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '</div>';
        // Corpo
        print '<div class="modal-body">';
        print '<p class="mb-3 text-center">';
        print '<strong>Você está prestes a aprovar o pedido <span class="text-danger">Nº ' . $numero_pedido . '</span></strong><br>';
        print 'do cliente <strong>' . htmlspecialchars($nome_fantasia) . '</strong>.';
        print '</p>';
        print '<div class="alert alert-warning text-center" role="alert">';
        print '<i class="bi bi-info-circle-fill me-2"></i> Após a aprovação, os <strong>itens ou cliente não poderão mais ser alterados</strong>.';
        print '</div>';
        print '</div>';
        // Rodapé com formulário
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer d-flex justify-content-between">';
        print '<input type="hidden" name="id_pedido" value="' . $id_pedido . '">';
        print '<input type="hidden" name="origem" value="' . $origem . '">';
        print '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-circle me-1"></i>Cancelar';
        print '</button>';
        print '<button type="submit" name="aprovar_pedido" class="btn btn-success">';
        print '<i class="bi bi-check-circle me-1"></i>Aprovar Pedido';
        print '</button>';
        print '</div>';
        print '</form>';
        print '</div>'; // modal-content
        print '</div>'; // modal-dialog
        print '</div>'; // modal
    }
    // metodo de aprovar o pedido
    public function aprovar_Pedido($id_pedido, $origem)
    {
        $objPedido = new Pedido();
        $resultado = $objPedido->aprovarPedido($id_pedido);

        session_start();
        // Carregar o menu
        $menu = $this->menu();

        if ($origem === 'pedido') {
            include_once 'View/pedido.php';
        } else {
            include_once 'View/principal.php';
        }

        // Exibir mensagem de acordo com resultado
        if ($resultado['success']) {
            $this->mostrarMensagemSucesso($resultado['message']);
        } else {
            $this->mostrarMensagemErro("Erro ao aprovar pedido: " . $resultado['message']);
        }
    }
    // modal de cancelar o pedido
    public function modalCancelarPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia)
    {
        print '<div class="modal fade" id="cancelar_pedido' . $numero_pedido . '" tabindex="-1" aria-labelledby="cancelarPedidoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content border-danger shadow">';
        // Cabeçalho
        print '<div class="modal-header bg-danger text-white">';
        print '<h5 class="modal-title fw-bold" id="cancelarPedidoLabel">';
        print '<i class="bi bi-x-octagon-fill me-2"></i>Confirmação de Cancelamento';
        print '</h5>';
        print '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '</div>';
        // Corpo
        print '<div class="modal-body">';
        print '<p class="mb-3 text-center">';
        print '<strong>Você está prestes a <span class="text-danger">cancelar</span> o pedido Nº <span class="text-danger">' . $numero_pedido . '</span></strong><br>';
        print 'do cliente <strong>' . htmlspecialchars($nome_fantasia) . '</strong>.';
        print '</p>';
        print '<div class="alert alert-danger text-center" role="alert">';
        print '<i class="bi bi-info-circle-fill me-2"></i> Esta ação é <strong>irreversível</strong> e afetará o controle de estoque e relatórios.';
        print '</div>';
        print '</div>';

        // Rodapé com formulário
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer d-flex justify-content-between">';
        print '<input type="hidden" name="id_pedido" value="' . $id_pedido . '">';
        print '<input type="hidden" name="origem" value="' . $origem . '">';
        print '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-circle me-1"></i>Fechar';
        print '</button>';
        print '<input type="hidden" name="status_pedido" value="Cancelado">';
        print '<button type="submit" name="cancelar_pedido" class="btn btn-danger">';
        print '<i class="bi bi-x-octagon me-1"></i>Cancelar Pedido';
        print '</button>';
        print '</div>';
        print '</form>';
        print '</div>'; // modal-content
        print '</div>'; // modal-dialog
        print '</div>'; // modal
    }
    // metodo de cancelar pedido
    public function cancelar_Pedido($id_pedido, $status_pedido, $origem)
    {
        // instancia a classe
        $objPedido = new Pedido();
        // Invocar o método da classe Pedido para cancelar o pedido
        if ($objPedido->cancelarPedido($id_pedido, $status_pedido) == true) {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view
            if ($origem === 'pedido') {
                include_once 'View/pedido.php';
            } else {
                include_once 'View/principal.php';
            }
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Pedido cancelado com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view
            if ($origem === 'pedido') {
                include_once 'View/pedido.php';
            } else {
                include_once 'View/principal.php';
            }
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao cancelar Pedido");
        }
    }
    // modal finalizar pedido
    public function modalFinalizarPedido($origem, $id_pedido, $numero_pedido, $nome_fantasia)
    {
        print '<div class="modal fade" id="finalizar_pedido' . $numero_pedido . '" tabindex="-1" aria-labelledby="finalizarPedidoLabel" aria-hidden="true">';
        print '<div class="modal-dialog modal-dialog-centered">';
        print '<div class="modal-content border-success shadow">';

        // Cabeçalho
        print '<div class="modal-header bg-success text-white">';
        print '<h5 class="modal-title fw-bold" id="finalizarPedidoLabel">';
        print '<i class="bi bi-check-circle-fill me-2"></i>Confirmação de Finalização';
        print '</h5>';
        print '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        print '</div>';

        // Corpo
        print '<div class="modal-body">';
        print '<p class="mb-3 text-center">';
        print '<strong>Você está prestes a finalizar o pedido <span class="text-success">Nº ' . $numero_pedido . '</span></strong><br>';
        print 'do cliente <strong>' . htmlspecialchars($nome_fantasia) . '</strong>.';
        print '</p>';
        print '<div class="alert alert-success text-center" role="alert">';
        print '<i class="bi bi-info-circle-fill me-2"></i> Após a finalização, não será possível fazer alterações neste pedido.';
        print '</div>';
        print '</div>';

        // Rodapé com formulário
        print '<form action="index.php" method="post">';
        print '<div class="modal-footer d-flex justify-content-between">';
        print '<input type="hidden" name="id_pedido" value="' . $id_pedido . '">';
        print '<input type="hidden" name="origem" value="' . $origem . '">';
        print '<input type="hidden" name="status_pedido" value="Finalizado">';
        print '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">';
        print '<i class="bi bi-x-circle me-1"></i>Cancelar';
        print '</button>';
        print '<button type="submit" name="finalizar_pedido" class="btn btn-success">';
        print '<i class="bi bi-check-circle me-1"></i>Finalizar Pedido';
        print '</button>';
        print '</div>';
        print '</form>';
        print '</div>'; // modal-content
        print '</div>'; // modal-dialog
        print '</div>'; // modal
    }
    // metodo de aprovar o pedido
    public function finalizar_Pedido($id_pedido, $status_pedido, $origem)
    {
        // instancia a classe
        $objPedido = new Pedido();
        // Invocar o método da classe Pedido para aprovar o pedido
        if ($objPedido->finalizarPedido($id_pedido, $status_pedido) == true) {
            session_start();
            // Incluir a view
            if ($origem === 'pedido') {
                // Carregar o menu
                $menu = $this->menu();
                include_once 'View/pedido.php';
            } else {
                // Carregar o menu
                $menu = $this->menu();
                include_once 'View/principal.php';
            }
            // Exibir mensagem de sucesso
            $this->mostrarMensagemSucesso("Pedido finalizado com sucesso");
        } else {
            session_start();
            // Carregar o menu
            $menu = $this->menu();
            // Incluir a view
            if ($origem === 'pedido') {
                include_once 'View/pedido.php';
            } else {
                include_once 'View/principal.php';
            }
            // Exibir mensagem de erro
            $this->mostrarMensagemErro("Erro ao finalizar Pedido");
        }
    }



    // Relatorios de pedidos

    // faturamento do mes por ano inserido
    public function faturamento_Mensal($ano_faturamento, $mes_faturamento)
    {
        $objPedido = new Pedido();
        $objPedido->faturamentoMensal($ano_faturamento, $mes_faturamento);
        if ($objPedido->faturamentoMensal($ano_faturamento, $mes_faturamento) == true) {
            $meses_faturamento = $objPedido->faturamentoMensal($ano_faturamento, $mes_faturamento);
            $mes_filtro = $mes_faturamento;
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Nenhum faturamento encontrado nesse periodo!");
        }
    }
    //  tabela de mes faturamento
    public function tabelaFaturamentoMensal($meses_faturamento, $mes_filtro)
    {
        if (!is_array($meses_faturamento)) return;

        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        // Reorganiza os dados vindos do banco com chave pelo número do mês
        $infoPorMes = [];
        foreach ($meses_faturamento as $item) {
            $infoPorMes[(int)$item->mes] = $item;
        }

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-hover table-striped align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Mês</th>';
        print '<th scope="col">Faturamento (R$)</th>';
        print '<th scope="col">Total de Pedidos</th>';
        print '<th scope="col">Pedidos Cancelados</th>';
        print '<th scope="col">Pedidos em Aberto</th>';
        print '<th scope="col">% Crescimento</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        $faturamentoAnterior = null;

        // Define os meses a exibir: todos se não houver filtro, ou apenas o mês filtrado
        $mesesParaMostrar = empty($mes_filtro) ? range(1, 12) : [(int)$mes_filtro];

        foreach ($mesesParaMostrar as $numero) {
            $nome = $meses[$numero] ?? 'Mês Desconhecido';

            if (isset($infoPorMes[$numero])) {
                $f = $infoPorMes[$numero];
                $faturamento = number_format($f->faturamento ?? 0, 2, ',', '.');
                $totalPedidos = $f->total_pedidos ?? 0;
                $cancelados = $f->total_cancelados ?? 0;
                $abertos = $f->total_abertos ?? 0;

                // Cálculo de crescimento
                if ($faturamentoAnterior !== null && $faturamentoAnterior > 0) {
                    $crescimento = (($f->faturamento - $faturamentoAnterior) / $faturamentoAnterior) * 100;
                    $crescimentoTexto = number_format($crescimento, 1, ',', '.') . '%';
                    $crescimentoColor = $crescimento >= 0 ? 'text-success' : 'text-danger';
                    $crescimentoHTML = "<span class='$crescimentoColor'>$crescimentoTexto</span>";
                } else {
                    $crescimentoHTML = '-';
                }

                $faturamentoAnterior = $f->faturamento;
            } else {
                $faturamento = $totalPedidos = $cancelados = $abertos = $crescimentoHTML = '-';
            }

            print "<tr>";
            print "<td>$nome</td>";
            print "<td>R$ $faturamento</td>";
            print "<td>$totalPedidos</td>";
            print "<td>$cancelados</td>";
            print "<td>$abertos</td>";
            print "<td>$crescimentoHTML</td>";
            print "</tr>";
        }

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // metodo de produtos mais vendidos
    public function produtos_MaisVendidos($limite)
    {
        $objPedido = new Pedido();
        $objPedido->produtosMaisVendidos($limite);
        if ($objPedido->produtosMaisVendidos($limite) == true) {
            $produtosMaisVendidos = $objPedido->produtosMaisVendidos($limite);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao consultar produtos mais vendidos!");
        }
    }
    // tabela de produtos mais vendidos
    public function tabelaProdutosMaisVendidos($produtosMaisVendidos)
    {
        if (empty($produtosMaisVendidos)) return;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>#</th>';
        print '<th>Produto</th>';
        print '<th>Total Vendido</th>';
        print '<th>Valor Médio Venda (R$)</th>';
        print '<th>Faturamento (R$)</th>';
        print '<th>Lucro Líquido (R$)</th>';
        print '<th>Margem (%)</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        $contador = 1;
        foreach ($produtosMaisVendidos as $item) {
            print '<tr>';
            print '<td>' . $contador++ . '</td>';
            print '<td>' . htmlspecialchars($item->nome_produto) . '</td>';
            print '<td>' . number_format((float)$item->total_vendido, 2, ',', '.') . '</td>';
            print '<td>' . number_format((float)$item->valor_medio_venda, 2, ',', '.') . '</td>';
            print '<td>' . number_format((float)$item->faturamento_total, 2, ',', '.') . '</td>';
            print '<td>' . number_format((float)$item->lucro_liquido, 2, ',', '.') . '</td>';
            print '<td>' . number_format((float)$item->margem_lucro, 2, ',', '.') . '%</td>';
            print '</tr>';
        }

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // metodo de quantidade de pedidos por mes
    public function pedidos_Mes($ano_referencia, $mes_referencia)
    {
        $objPedido = new Pedido();
        $objPedido->pedidosPorMes($ano_referencia, $mes_referencia);
        if ($objPedido->pedidosPorMes($ano_referencia, $mes_referencia) == true) {
            $dadosPedidos = $objPedido->pedidosPorMes($ano_referencia, $mes_referencia);
            $mes_referencia;
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("No ano base digitado nao foi finalizado nenhum pedido!");
        }
    }
    //tabela de quantidade de pedidos por mes
    public function tabelaPedidosPorMes($dadosPedidos, $mes_referencia = null)
    {
        if (!is_array($dadosPedidos)) return;

        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        $pedidosPorMes = [];
        foreach ($dadosPedidos as $item) {
            $pedidosPorMes[(int)$item->mes] = $item;
        }

        $totalPedidos = 0;
        $totalFaturamento = 0;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>Mês</th>';
        print '<th>Total de Pedidos</th>';
        print '<th>Finalizados</th>';
        print '<th>Cancelados</th>';
        print '<th>Em Aberto</th>';
        print '<th>Faturamento (R$)</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        $mesesParaMostrar = empty($mes_referencia) ? range(1, 12) : [(int)$mes_referencia];

        foreach ($mesesParaMostrar as $numero) {
            $nome = $meses[$numero] ?? 'Desconhecido';

            if (isset($pedidosPorMes[$numero])) {
                $dados = $pedidosPorMes[$numero];
                $total = $dados->total_pedidos ?? 0;
                $finalizados = $dados->pedidos_finalizados ?? 0;
                $cancelados = $dados->pedidos_cancelados ?? 0;
                $abertos = $dados->pedidos_abertos ?? 0;
                $faturamento = number_format($dados->faturamento ?? 0, 2, ',', '.');

                $totalPedidos += $total;
                $totalFaturamento += $dados->faturamento ?? 0;
            } else {
                $total = $finalizados = $cancelados = $abertos = '-';
                $faturamento = '-';
            }

            print '<tr>';
            print "<td>$nome</td>";
            print "<td>$total</td>";
            print "<td>$finalizados</td>";
            print "<td>$cancelados</td>";
            print "<td>$abertos</td>";
            print "<td>R$ $faturamento</td>";
            print '</tr>';
        }

        // Linha de total
        print '<tr class="fw-bold table-secondary">';
        print '<td>Total</td>';
        print "<td>$totalPedidos</td>";
        print '<td colspan="3"></td>';
        print '<td>R$ ' . number_format($totalFaturamento, 2, ',', '.') . '</td>';
        print '</tr>';

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // metodo de quantidade de pedido por forma de pagamento
    public function qtd_PedidoFormaPagamento()
    {
        $objPedido = new Pedido();
        $objPedido->formasPagamentoMaisUsadas();
        if ($objPedido->formasPagamentoMaisUsadas() == true) {
            $qtd_pedidos_forma_pagamento = $objPedido->formasPagamentoMaisUsadas();
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao cosultar quantidade de pedidos por forma de pagamento!");
        }
    }
    // tabela de quantidade de pedidos por forma de pagamento
    public function tabelaFormasPagamentoMaisUsadas($qtd_pedidos_forma_pagamento)
    {
        if (empty($qtd_pedidos_forma_pagamento)) return;
        // Total geral
        $totalGeral = 0;
        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th scope="col">Forma de Pagamento</th>';
        print '<th scope="col">Quantidade de Usos</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        foreach ($qtd_pedidos_forma_pagamento as $item) {
            $descricao = htmlspecialchars($item->descricao);
            $quantidade = (int)$item->quantidade;
            $totalGeral += $quantidade;

            print '<tr>';
            print "<td>$descricao</td>";
            print "<td>$quantidade</td>";
            print '</tr>';
        }

        // Linha de total
        print '<tr class="fw-bold table-secondary">';
        print '<td>Total</td>';
        print "<td>$totalGeral</td>";
        print '</tr>';

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // metodo de resumo de pedidos por cliente
    public function pedidos_Cliente($id_cliente)
    {
        $objPedido = new Pedido();
        $objPedido->resumoPedidosPorCliente();

        if ($objPedido->resumoPedidosPorCliente($id_cliente) == true) {
            $resumoPedidosCliente = $objPedido->resumoPedidosPorCliente($id_cliente);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao consultar os pedidos desse cliente!");
        }
    }
    // tabela de resumo de pedidos por cliente
    public function tabelaResumoPedidosPorCliente($resumoPedidosCliente)
    {
        if (empty($resumoPedidosCliente)) return;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>#</th>';
        print '<th>Cliente</th>';
        print '<th>Último Pedido</th>';
        print '<th>Total de Pedidos</th>';
        print '<th>Pendentes</th>';
        print '<th>Em Andamento</th>';
        print '<th>Cancelados</th>';
        print '<th>Finalizados</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        $contador = 1;
        foreach ($resumoPedidosCliente as $item) {
            print '<tr>';
            print '<td>' . $contador++ . '</td>';
            print '<td>' . htmlspecialchars($item->nome_fantasia) . '</td>';
            print '<td>' . date('d/m/Y', strtotime($item->data_ultimo_pedido)) . '</td>';
            print '<td>' . $item->total_pedidos . '</td>';
            print '<td>' . $item->total_pendente . '</td>';
            print '<td>' . $item->total_em_andamento . '</td>';
            print '<td>' . $item->total_cancelado . '</td>';
            print '<td>' . $item->total_finalizado . '</td>';
            print '</tr>';
        }

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // metodo de pedidos por status com ou sem intervalo
    public function pedidos_Status($status, $data_inicio, $data_fim)
    {
        $objPedido = new Pedido();
        $objPedido->pedidosPorStatus($status, $data_inicio, $data_fim);
        if ($objPedido->pedidosPorStatus($status, $data_inicio, $data_fim) == true) {
            $pedidosPorStatus = $objPedido->pedidosPorStatus($status, $data_inicio, $data_fim);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao consultar pedidos por status!");
        }
    }
    // Tabela de pedidos por status
    public function tabelaPedidosPorStatus($pedidosPorStatus)
    {
        if (empty($pedidosPorStatus)) {
            return;
        }

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>#</th>';
        print '<th>Status do Pedido</th>';
        print '<th>Total de Pedidos</th>';
        print '<th>Valor Total (R$)</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';
        $contador = 1;
        $totalGeralPedidos = 0;
        $valorGeral = 0;
        foreach ($pedidosPorStatus as $item) {
            print '<tr>';
            print '<td>' . $contador++ . '</td>';
            print '<td>' . htmlspecialchars($item->status_pedido) . '</td>';
            print '<td>' . $item->total_pedidos . '</td>';
            print '<td>' . number_format($item->valor_total, 2, ',', '.') . '</td>';
            print '</tr>';
            $totalGeralPedidos += $item->total_pedidos;
            $valorGeral += $item->valor_total;
        }
        print '<tfoot class="table-light fw-bold">';
        print '<tr>';
        print '<td colspan="2">Total Geral</td>';
        print '<td>' . $totalGeralPedidos . '</td>';
        print '<td>' . number_format($valorGeral, 2, ',', '.') . '</td>';
        print '</tr>';
        print '</tfoot>';
        print '</tbody>';
        print '</table>';
        print '</div>';
    }
    // metodo de pedidos com maior valor
    public function pedidos_MaiorValor($limite)
    {
        $objPedido = new Pedido();
        $objPedido->pedidosMaiorValor($limite);
        if ($objPedido->pedidosMaiorValor($limite) == true) {
            $pedidosMaiorValor = $objPedido->pedidosMaiorValor($limite);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao consultar pedidos com maior valor!");
        }
    }
    // tabela de pedidos com maior valor
    public function tabelaPedidosMaiorValor($pedidosMaiorValor)
    {
        if (empty($pedidosMaiorValor)) {
            return;
        }
        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print "<tr>
            <th>#</th>
            <th>Número do Pedido</th>
            <th>Cliente</th>
            <th>Data do Pedido</th>
            <th>Valor Total (R$)</th>
        </tr>";
        print "</thead>";
        print "<tbody>";
        $contador = 1;
        foreach ($pedidosMaiorValor as $pedido) {
            print "<tr>";
            print "<td>" . ($contador++) . "</td>";
            print "<td>" . htmlspecialchars($pedido->numero_pedido) . "</td>";
            print "<td>" . htmlspecialchars($pedido->nome_fantasia) . "</td>";
            print "<td>" . date("d/m/Y", strtotime($pedido->data_pedido)) . "</td>";
            print "<td>R$ " . number_format($pedido->valor_total, 2, ',', '.') . "</td>";
            print "</tr>";
        }
        print "</tbody>";
        print "</table>";
        print "</div>";
    }
    // metodo de produtos que nunca foram vendidos
    public function produtos_NuncaVendidos()
    {
        $objPedido = new Pedido();
        $objPedido->produtosNuncaVendidos();
        if ($objPedido->produtosNuncaVendidos() == true) {
            $produtosNaoVendidos = $objPedido->produtosNuncaVendidos();
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao consultar produtos nunca vendidos!");
        }
    }
    // tabela de produtos que nunca foram vendidos
    public function tabelaProdutosNaoVendidos($produtosNaoVendidos)
    {
        if (empty($produtosNaoVendidos)) {
            return;
        }

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print "<tr>
            <th>#</th>
            <th>Produto</th>
            <th>Valor de Venda</th>
            <th>Quantidade em Estoque</th>
            </tr>";
        print "</thead>";
        print "<tbody>";

        $contador = 1;
        foreach ($produtosNaoVendidos as $produto) {
            print "<tr>";
            print "<td>" . ($contador++) . "</td>";
            print "<td>" . htmlspecialchars($produto->nome_produto) . "</td>";
            print "<td>R$ " . number_format($produto->valor_venda, 2, ',', '.') . "</td>";
            print "<td>" . (int) $produto->quantidade . "</td>";
            print "</tr>";
        }
        print "</tbody>";
        print "</table>";
        print "</div>";
    }
    // método de pedidos recentes
    public function pedidos_Recentes($dias)
    {
        $objPedido = new Pedido();
        $objPedido->pedidosRecentes();
        if ($objPedido->pedidosRecentes($dias) == true) {
            $pedidosRecentes = $objPedido->pedidosRecentes($dias);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Nenhum pedido recente encontrado!");
        }
    }
    // tabela de pedidos recentes
    public function tabelaPedidosRecentes($pedidosRecentes)
    {
        if (empty($pedidosRecentes)) {
            return;
        }
        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print "<tr>
        <th>#</th>
        <th>Número do Pedido</th>
        <th>Data do Pedido</th>
        <th>Cliente</th>
        <th>Forma de Pagamento</th>
        <th>Valor Total</th>
        </tr>";
        print "</thead>";
        print "<tbody>";

        $contador = 1;
        foreach ($pedidosRecentes as $pedido) {
            print "<tr>";
            print "<td>" . ($contador++) . "</td>";
            print "<td>" . htmlspecialchars($pedido->numero_pedido) . "</td>";
            print "<td>" . date('d/m/Y', strtotime($pedido->data_pedido)) . "</td>";
            print "<td>" . htmlspecialchars($pedido->cliente) . "</td>";
            print "<td>" . htmlspecialchars($pedido->forma_pagamento) . "</td>";
            print "<td>R$ " . number_format($pedido->valor_total, 2, ',', '.') . "</td>";
            print "</tr>";
        }

        print "</tbody>";
        print "</table>";
        print "</div>";
    }
    // metodo de Variação de Vendas por Produto
    public function variacaoVenda_Produto($id_produto, $ano_faturamento)
    {
        $objPedido = new Pedido();
        $objPedido->variacaoVendasPorProduto($id_produto, $ano_faturamento);
        if ($objPedido->variacaoVendasPorProduto($id_produto, $ano_faturamento) == true) {
            $variacao_vendas = $objPedido->variacaoVendasPorProduto($id_produto, $ano_faturamento);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao buscar variacao de vendas por produto");
        }
    }
    // Tabela de variação de vendas por produto
    public function tabelaVariacaoVendasProduto($variacao_vendas)
    {
        if (empty($variacao_vendas)) {
            return;
        }
        // Nomes dos meses
        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-sm align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>Mês</th>';
        print '<th>Total Vendido (qtd.)</th>';
        print '<th>Total Faturado (R$)</th>';
        print '</tr>';
        print '</thead><tbody>';

        foreach ($variacao_vendas as $dados) {
            $mesNome = $meses[$dados['mes']];
            $totalQtd = number_format($dados['total_quantidade'], 0, ',', '.');
            $totalFat = number_format($dados['total_vendido'], 2, ',', '.');

            print '<tr>';
            print "<td>{$mesNome}</td>";
            print "<td>{$totalQtd} m</td>";
            print "<td>R$ {$totalFat}</td>";
            print '</tr>';
        }
        print '</tbody></table></div>';
    }
    // metodo de lucro mensal Bruto
    public function lucroBruto_Mensal($ano, $mes)
    {
        $objPedido = new Pedido();
        $objPedido->lucroBrutoMensal($ano, $mes);
        if ($objPedido->lucroBrutoMensal($ano, $mes) == true) {
            $dadosLucroMensal = $objPedido->lucroBrutoMensal($ano, $mes);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao buscar Lucro bruto de vendas");
        }
    }
    // Tabela de Lucro Mensal Bruto
    public function tabelaLucroBrutoMensal($dadosLucroMensal)
    {
        if (empty($dadosLucroMensal)) {
            return;
        }

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-sm align-middle text-center">';
        print '    <thead class="table-primary">';
        print '        <tr>';
        print '            <th>Mês/Ano</th>';
        print '            <th>Total Vendas (R$)</th>';
        print '            <th>Total Custos (R$)</th>';
        print '            <th>Lucro (R$)</th>';
        print '            <th>Margem (%)</th>';
        print '        </tr>';
        print '    </thead>';
        print '    <tbody>';

        foreach ($dadosLucroMensal as $linha) {
            // Mes/Ano (ano virá do método de cálculo, então precisa ser incluído no SELECT)
            $mesAno = str_pad($linha['mes'], 2, '0', STR_PAD_LEFT) . '/' . $linha['ano'];

            // Formatação de valores
            $totalVendas = number_format($linha['total_vendas'], 2, ',', '.');
            $totalCustos = number_format($linha['total_custo'], 2, ',', '.');
            $lucro       = number_format($linha['lucro_bruto'], 2, ',', '.');

            // Margem percentual - evitar divisão por zero
            $margem = $linha['total_vendas'] > 0
                ? number_format(($linha['lucro_bruto'] / $linha['total_vendas']) * 100, 2, ',', '.')
                : '0,00';

            print '        <tr>';
            print "            <td>{$mesAno}</td>";
            print "            <td>{$totalVendas}</td>";
            print "            <td>{$totalCustos}</td>";
            print "            <td>{$lucro}</td>";
            print "            <td>{$margem}%</td>";
            print '        </tr>';
        }

        print '    </tbody>';
        print '</table>';
        print '</div>';
    }

    // Relatorios de Produtos

    //  metodo de produtos com baixo estoque
    public function produtosBaixo_estoque($limite)
    {
        $objProduto = new Produto();
        $objProduto->produtosComBaixoEstoque($limite);
        if ($objProduto->produtosComBaixoEstoque($limite) == true) {
            $estoqueBaixoProduto = $objProduto->produtosComBaixoEstoque($limite);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao buscar produtos com baixo estoque!");
        }
    }
    // tabela de pedidos com baixo estoque
    public function tabelaProdutosBaixoEstoque($estoqueBaixoProduto)
    {
        if (empty($estoqueBaixoProduto)) {
            print '<div class="alert alert-info mt-4">Nenhum produto com baixo estoque encontrado.</div>';
            return;
        }

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>Produto</th>';
        print '<th>Quantidade</th>';
        print '<th>Quantidade Mínima</th>';
        print '<th>Falta</th>';
        print '</tr>';
        print '</thead><tbody>';

        foreach ($estoqueBaixoProduto as $p) {
            print '<tr>';
            print "<td>" . htmlspecialchars($p->nome_produto) . "</td>";
            print "<td>" . $p->quantidade . "</td>";
            print "<td>" . $p->quantidade_minima . "</td>";
            print "<td>" . $p->falta . "</td>";
            print '</tr>';
        }

        print '</tbody></table></div>';
    }

    // metodo de Custo Total por Produto
    public function custoTotal_PorProduto($id_produto)
    {
        $objProduto = new Produto();
        $objProduto->custoTotalPorProduto();
        if ($objProduto->custoTotalPorProduto($id_produto) == true) {
            $custoTotal_Produto = $objProduto->custoTotalPorProduto($id_produto);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao buscar produtos e seus custos!");
        }
    }
    // tabela de  Custo Total por Produto em pedidos realizados
    public function tabelaCustoTotalPorProduto($custoTotal_Produto)
    {
        if (empty($custoTotal_Produto)) return;

        $totalInvestido = 0;
        $totalVendas = 0;
        $totalLucroBruto = 0;

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-hover align-middle text-center table-sm">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>Produto</th>';
        print '<th>Tipo</th>';
        print '<th>Largura</th>';
        print '<th>Composição</th>';
        print '<th>Qtd. Vendida</th>';
        print '<th>Custo Médio</th>';
        print '<th>Total Investido</th>';
        print '<th>Total de Vendas</th>';
        print '<th>Lucro Bruto</th>';
        print '<th>% Lucro</th>';
        print '</tr>';
        print '</thead><tbody>';

        foreach ($custoTotal_Produto as $p) {
            $investido = $p->total_investido ?? 0;
            $vendas = $p->valor_total_pedidos ?? 0;
            $lucroBruto = $p->lucro_bruto ?? 0;
            $percLucro = ($vendas > 0) ? ($lucroBruto / $vendas) * 100 : 0;

            $totalInvestido += $investido;
            $totalVendas += $vendas;
            $totalLucroBruto += $lucroBruto;

            print '<tr>';
            print '<td>' . htmlspecialchars($p->nome_produto) . '</td>';
            print '<td>' . htmlspecialchars($p->tipo_produto) . '</td>';
            print '<td>' . number_format($p->largura, 2, ',', '.') . ' m</td>';
            print '<td>' . htmlspecialchars($p->composicao) . '</td>';
            print '<td>' . number_format($p->quantidade_total, 2, ',', '.') . ' m</td>';
            print '<td>R$ ' . number_format($p->custo_unit_medio, 2, ',', '.') . '</td>';
            print '<td>R$ ' . number_format($investido, 2, ',', '.') . '</td>';
            print '<td>R$ ' . number_format($vendas, 2, ',', '.') . '</td>';
            print '<td>R$ ' . number_format($lucroBruto, 2, ',', '.') . '</td>';
            print '<td>' . number_format($percLucro, 1, ',', '.') . ' %</td>';
            print '</tr>';
        }

        // Totais
        $percLucroTotal = ($totalVendas > 0) ? ($totalLucroBruto / $totalVendas) * 100 : 0;

        print '<tr class="fw-bold table-secondary">';
        print '<td colspan="6">Totais Gerais</td>';
        print '<td>R$ ' . number_format($totalInvestido, 2, ',', '.') . '</td>';
        print '<td>R$ ' . number_format($totalVendas, 2, ',', '.') . '</td>';
        print '<td>R$ ' . number_format($totalLucroBruto, 2, ',', '.') . '</td>';
        print '<td>' . number_format($percLucroTotal, 1, ',', '.') . ' %</td>';
        print '</tr>';

        print '</tbody></table></div>';
    }
    // metodo de Custo Total por Produto
    public function produto_Fornecedor($id_fornecedor)
    {
        $objProduto = new Produto();
        $objProduto->listarProdutosPorFornecedor($id_fornecedor);
        if ($objProduto->listarProdutosPorFornecedor($id_fornecedor) == true) {
            $produtos = $objProduto->listarProdutosPorFornecedor($id_fornecedor);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao buscar produtos e seus custos!");
        }
    }
    public function tabelaProdutosPorFornecedor($produtos)
    {
        if (empty($produtos)) return;
        // Agrupar produtos por razão social do fornecedor
        $agrupado = [];
        foreach ($produtos as $p) {
            $fornecedor = $p->razao_social;
            // Salvar os dados formatados do produto para esse fornecedor
            $agrupado[$fornecedor][] = '• ' . htmlspecialchars($p->nome_produto);
        }
        //Corpo da tabela
        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-sm align-middle">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>Fornecedor</th>';
        print '<th>Produtos</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        foreach ($agrupado as $fornecedor => $produtosFornecedor) {
            print '<tr>';
            // Coluna do Fornecedor
            print '<td><strong>' . htmlspecialchars($fornecedor) . '</strong></td>';
            // Coluna dos Produtos
            print '<td>';
            print '<div class="d-flex flex-column">'; // container vertical
            foreach ($produtosFornecedor as $produto) {
                print '<div class="border rounded px-2 py-1 mb-2 bg-light text-dark">'
                    . $produto .
                    '</div>';
            }
            print '</div>';
            print '</td>';
            print '</tr>';
        }

        print '</tbody>
            </table>
            </div>';
    }
    // metodo de Produtos com Baixa Margem
    public function produto_Margem($limitePercentual, $id_produto)
    {
        $objProduto = new Produto();
        $objProduto->produtosMargem($limitePercentual, $id_produto);
        if ($objProduto->produtosMargem($limitePercentual, $id_produto) == true) {
            $produtosMargem = $objProduto->produtosMargem($limitePercentual, $id_produto);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao buscar produtos e suas margens!");;
        }
    }
    // tabela de produtos com baixa margem
    public function tabelaProdutosMargem($produtosMargem)
    {
        if (empty($produtosMargem)) {
            return;
        }

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-sm align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>Produto</th>';
        print '<th>Custo (R$)</th>';
        print '<th>Venda (R$)</th>';
        print '<th>Margem (%)</th>';
        print '</tr>';
        print '</thead><tbody>';

        foreach ($produtosMargem as $p) {
            $nome = isset($p['nome_produto']) ? htmlspecialchars($p['nome_produto']) : '';
            $custo = isset($p['custo_compra']) ? number_format($p['custo_compra'], 2, ',', '.') : '0,00';
            $venda = isset($p['valor_venda']) ? number_format($p['valor_venda'], 2, ',', '.') : '0,00';
            $margem = isset($p['margem_percentual']) ? number_format($p['margem_percentual'], 2, ',', '.') . '%' : '0,00%';

            print '<tr>';
            print "<td>$nome</td>";
            print "<td>$custo</td>";
            print "<td>$venda</td>";
            print "<td>$margem</td>";
            print '</tr>';
        }
        print '</tbody></table></div>';
    }
    // metodo de clientes que mais compraram
    public function clientes_MaisCompraram($ano_referencia, $mes_referencia, $limite)
    {
        $objPedido = new Pedido();
        $objPedido->clientesQueMaisCompraram($ano_referencia, $mes_referencia, $limite);
        if ($objPedido->clientesQueMaisCompraram($ano_referencia, $mes_referencia, $limite) == true) {
            $clientes_mais_compram = $objPedido->clientesQueMaisCompraram($ano_referencia, $mes_referencia, $limite);
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/relatorios.php';
            $this->mostrarMensagemErro("Erro ao consultar clientes que mais compraram!");
        }
    }
    // tabela de clientes que mais compraram
    public function tabelaClientesMaisCompraram($clientes_mais_compram)
    {
        if (empty($clientes_mais_compram)) {
            return;
        }

        print '<div class="table-responsive mt-4">';
        print '<table class="table table-bordered table-striped table-sm align-middle text-center">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>Cliente</th>';
        print '<th>Total de Pedidos (qtd.)</th>';
        print '<th>Total Comprado (R$)</th>';
        print '</tr>';
        print '</thead><tbody>';

        foreach ($clientes_mais_compram as $clientes) {
            $nome = ($clientes->nome_fantasia);
            $totalPedidos = $clientes->total_pedidos;
            $totalComprado = ($clientes->total_comprado);
            print '<tr>';
            print "<td>$nome</td>";
            print "<td>$totalPedidos</td>";
            print "<td>R$ $totalComprado</td>";
            print '</tr>';
        }

        print '</tbody></table></div>';
    }

    // NOTIFICAÇOES

    // Método para buscar produtos abaixo do estoque mínimo (Notificaçoes)
    public function buscarProdutosAbaixoMinimo()
    {
        $objProduto = new Produto();
        $produtos = $objProduto->produtosAbaixoDoMinimo();
        // Cabeçalho da seção
        print "
        <li class='dropdown-item text-center fw-bold text-warning bg-light'>
            <i class='fas fa-exclamation-triangle me-1'></i> Produtos com estoque baixo
        </li>
        <li><hr class='dropdown-divider'></li>
        ";
        // Loop dos produtos
        foreach ($produtos as $produto) {
            $id_produto     = $produto['id_produto'];
            $nome_produto   = htmlspecialchars($produto['nome_produto']);
            $estoque_atual  = (float) $produto['quantidade'];
            $estoque_minimo = (float) $produto['quantidade_minima'];

            print "
            <li class='produto-item dropdown-item d-flex justify-content-between align-items-center'
                data-nome='{$nome_produto}'
                data-estoque-atual='{$estoque_atual}'
                data-estoque-minimo='{$estoque_minimo}'>
                <div>
                    <i class='fas fa-box text-secondary me-2'></i> {$nome_produto}
                </div>
                <span class='badge bg-danger'>{$estoque_atual}</span>
                <small class='text-muted ms-1'>(mín.: {$estoque_minimo})</small>
            </li>
        ";
        }
    }
    // notificaoes de pedidos - Aguardando Pagamento e Pendendente
    public function buscarPedidosPendentesOuAguardando()
    {
        $objPedido = new Pedido();
        $resultado = $objPedido->pedidosPendentesOuAguardando();

        $temPedidos = !empty($resultado['lista']);
        // Cabeçalho
        print "
        <li class='dropdown-item text-center fw-bold text-warning bg-light'>
            <i class='fas fa-exclamation-triangle me-1'></i> Pedidos em Espera
        </li>
        <li><hr class='dropdown-divider'></li>
        ";

        // Agrupar pedidos por status
        $agrupados = [];
        foreach ($resultado['lista'] as $pedido) {
            $status = $pedido['status_pedido'];
            $agrupados[$status][] = $pedido;
        }

        // Mostrar apenas status com registros
        foreach ($agrupados as $status => $pedidos) {
            // Subtítulo de status
            print "
            <li class='dropdown-item text-primary fw-bold bg-light'>
                <i class='fas fa-tags me-1'></i> {$status}
            </li>
        ";

            foreach ($pedidos as $pedido) {
                $numero_pedido  = htmlspecialchars($pedido['numero_pedido']);
                $nome_cliente   = htmlspecialchars($pedido['nome_cliente']);

                print "
                <li class='pedido-item dropdown-item d-flex justify-content-between align-items-center'
                    data-numero-pedido='{$numero_pedido}'
                    data-nome-cliente='{$nome_cliente}'
                    data-status='{$status}'>
                    <div>
                        <i class='fas fa-file-invoice text-secondary me-2'></i> Pedido #{$numero_pedido} - {$nome_cliente}
                    </div>
                </li>
            ";
            }
            print "<li><hr class='dropdown-divider'></li>";
        }
    }

    // Auditoria
    public function listar_Auditorias()
    {
        $objAuditoria = new Auditoria();
        $objAuditoria->listarTudo();
        if ($objAuditoria->listarTudo() == true) {
            $todas_auditorias = $objAuditoria->listarTudo();
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/auditoria.php';
        } else {
            // menu
            $menu = $this->menu();
            // view
            include_once 'View/auditoria.php';
            $this->mostrarMensagemErro("Erro ao consultar Auditorias Gerais!");
        }
    }
    public function tabelaAuditoria($auditorias)
    {
        if (empty($auditorias)) return;

        $modals = '';

        /**
         * ======================================================
         * 1. AGRUPAR auditorias pelo id_auditoria
         * ======================================================
         */
        $auditoriasAgrupadas = [];
        foreach ($auditorias as $aud) {
            $id = $aud['id_auditoria'];

            if (!isset($auditoriasAgrupadas[$id])) {
                $auditoriasAgrupadas[$id] = [
                    'id_auditoria' => $id,
                    'nome_usuario' => $aud['nome_usuario'],
                    'acao' => $aud['acao'],
                    'descricao_relacionada' => $aud['descricao_relacionada'],
                    'data_hora' => $aud['data_hora'],
                    'detalhes' => []
                ];
            }

            // Detalhes agrupados em um array por ação
            if (!empty($aud['campo'])) {
                // Formata valores monetários se forem numéricos
                $valorAntigo = is_numeric($aud['valor_antigo']) ? 'R$ ' . number_format($aud['valor_antigo'], 2, ',', '.') : $aud['valor_antigo'];
                $valorNovo = is_numeric($aud['valor_novo']) ? 'R$ ' . number_format($aud['valor_novo'], 2, ',', '.') : $aud['valor_novo'];

                $auditoriasAgrupadas[$id]['detalhes'][] = [
                    'campo' => $aud['campo'],
                    'valor_antigo' => $valorAntigo,
                    'valor_novo' => $valorNovo,
                    'tabela' => $aud['tabela_relacionada'] ?? 'Principal'
                ];
            }
        }

        /**
         * ======================================================
         * 2. GERAR TABELA PRINCIPAL
         * ======================================================
         */
        print '<div class="table-responsive mt-4">';
        print '<table class="table table-hover table-bordered align-middle text-center shadow-sm table-lg">';
        print '<thead class="table-primary">';
        print '<tr>';
        print '<th>Usuário</th>';
        print '<th>Ação</th>';
        print '<th>Data</th>';
        print '<th>Hora</th>';
        print '<th>Detalhes</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';

        foreach ($auditoriasAgrupadas as $auditoria) {
            $id = md5($auditoria['id_auditoria']);
            $nomeUsuario = explode(" ", $auditoria['nome_usuario'])[0];
            $acao = htmlspecialchars($auditoria['acao']);
            $descricao = htmlspecialchars($auditoria['descricao_relacionada']);
            $data = date('d/m/Y', strtotime($auditoria['data_hora']));
            $hora = date('H:i:s', strtotime($auditoria['data_hora']));

            print '<tr>';
            print '<td class="fw-bold">' . $nomeUsuario . '</td>';
            print '<td><span class="badge bg-' .
                ($acao === 'Cadastro' ? 'success' : ($acao === 'Alteração' ? 'warning text-dark' : 'danger')) . '">' . $acao . '</span></td>';
            print '<td>' . $data . '</td>';
            print '<td>' . $hora . '</td>';
            print '<td>
                <button type="button" class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#detalhes_auditoria' . $id . '">
                    <i class="bi bi-eye"></i> Ver
                </button>
              </td>';
            print '</tr>';

            /**
             * ======================================================
             * 3. MODAL COM AGRUPAMENTO DE DADOS
             * ======================================================
             */
            $modal  = '<div class="modal fade" id="detalhes_auditoria' . $id . '" tabindex="-1">';
            $modal .= '  <div class="modal-dialog modal-xl modal-dialog-scrollable">';
            $modal .= '    <div class="modal-content">';
            $modal .= '      <div class="modal-header bg-primary text-white">';
            $modal .= '        <h5 class="modal-title">📋 Detalhes da Auditoria</h5>';
            $modal .= '        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
            $modal .= '      </div>';
            $modal .= '      <div class="modal-body">';
            $modal .= '        <div class="mb-3">';
            $modal .= '          <strong>Usuário:</strong> ' . htmlspecialchars($auditoria['nome_usuario']) . '<br>';
            $modal .= '          <strong>Ação:</strong> <span class="badge bg-primary">' . $acao . '</span><br>';
            $modal .= '          <strong>Data:</strong> ' . $data . '<br>';
            $modal .= '          <strong>Hora:</strong> ' . $hora;
            $modal .= '        </div><hr>';

            // Blocos de detalhes agrupados por tabela relacionada
            if (!empty($auditoria['detalhes'])) {
                $porTabela = [];
                foreach ($auditoria['detalhes'] as $d) {
                    $tabela = $d['tabela'] ?? 'Principal';
                    $porTabela[$tabela][] = $d;
                }

                foreach ($porTabela as $tabelaNome => $campos) {
                    $modal .= '<h6 class="mt-3 text-primary">Tabela: ' . htmlspecialchars($tabelaNome) . '</h6>';
                    $modal .= '<div class="table-responsive">';
                    $modal .= '<table class="table table-sm table-bordered">';
                    $modal .= '<thead class="table-light">';
                    $modal .= '<tr><th>Campo</th><th>Valor Antigo</th><th>Valor Novo</th></tr>';
                    $modal .= '</thead><tbody>';

                    foreach ($campos as $c) {
                        $modal .= '<tr>';
                        $modal .= '<td>' . htmlspecialchars($c['campo']) . '</td>';
                        $modal .= '<td>' . htmlspecialchars($c['valor_antigo']) . '</td>';
                        $modal .= '<td>' . htmlspecialchars($c['valor_novo']) . '</td>';
                        $modal .= '</tr>';
                    }
                    $modal .= '</tbody></table></div>';
                }
            } else {
                $modal .= '<div class="alert alert-secondary">Sem detalhes registrados</div>';
            }

            $modal .= '      </div>';
            $modal .= '      <div class="modal-footer">';
            $modal .= '        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
            $modal .= '      </div>';
            $modal .= '    </div>';
            $modal .= '  </div>';
            $modal .= '</div>';

            $modals .= $modal;
        }

        print '</tbody></table></div>';
        print $modals;
    }

    //  Charts
    public function dashboardDados()
    {
        // Instancia o Model
        $objPedido = new Pedido();

        // --- PARÂMETROS BÁSICOS ---
        $anoAtual = date('Y');

        // --- 1. Faturamento Mensal ---
        $faturamentoMensal = $objPedido->faturamentoMensal($anoAtual,);
        // --- 2. Formas de Pagamento Mais Usadas ---
        $formasPagamento = $objPedido->formasPagamentoMaisUsadas();
        // --- 3. Produtos mais vendidos (opcional para um top 5 no dashboard) ---
        $produtosMaisVendidos = $objPedido->produtosMaisVendidos(5);
        // --- 4 Pedidos Recentes
        $pedidosRecentes = $objPedido->pedidosRecentes(7);
        // --- 5 Clientes que mais compram
        $clientesQueMaisCompram = $objPedido->clientesQueMaisCompraram($anoAtual, null, 5);
        // --- 6 Pedidos por mês (para linha do tempo anual) ---
        $pedidosPorMes = $objPedido->pedidosPorMes(date('Y'));

        // --- MONTA O RETORNO JSON ---
        // O Google Charts pode consumir esse formato diretamente via AJAX
        $dados = [
            'faturamentoMensal'        => $faturamentoMensal,
            'formasPagamentoMaisUsadas' => $formasPagamento,
            'produtosMaisVendidos'     => $produtosMaisVendidos,
            'pedidosRecentes'          => $pedidosRecentes,
            'clientesQueMaisCompram'   => $clientesQueMaisCompram,
            'pedidosPorMes'            => $pedidosPorMes
        ];

        header('Content-Type: application/json');
        print json_encode($dados);
        exit;
    }
}
