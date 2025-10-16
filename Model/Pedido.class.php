<?php
//incluir classe conexao
include_once 'Conexao.class.php';
//Classe Cliente
class Pedido extends Conexao
{
    // atributos da classe pedido
    private $id_pedido =  null;
    private $id_cliente = null;
    private $id_usuario = null;
    private $numero_pedido = null;
    private $data_pedido = null;
    private $status_pedido = null;
    private $valor_total = null;
    private $id_forma_pagamento = null;
    private $valor_frete = null;
    private $itens = null;

    private $data_finalizacao;


    // metodos gettes e setters
    public function getIdPedido()
    {
        return $this->id_pedido;
    }
    public function setIdPedido($id_pedido)
    {
        $this->id_pedido = $id_pedido;
    }
    public function getIdCliente()
    {
        return $this->id_cliente;
    }
    public function setIdCliente($id_cliente)
    {
        $this->id_cliente = $id_cliente;
    }
    public function getIdUsuario()
    {
        return $this->id_usuario;
    }
    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    public function getNumeroPedido()
    {
        return $this->numero_pedido;
    }
    public function setNumeroPedido($numero_pedido)
    {
        $this->numero_pedido = $numero_pedido;
    }
    public function getDataPedido()
    {
        return $this->data_pedido;
    }
    public function setDataPedido($data_pedido)
    {
        $this->data_pedido = $data_pedido;
    }
    public function getStatusPedido()
    {
        return $this->status_pedido;
    }
    public function setStatusPedido($status_pedido)
    {
        $this->status_pedido = $status_pedido;
    }
    public function getValorTotal()
    {
        return $this->valor_total;
    }
    public function setValorTotal($valor_total)
    {
        $this->valor_total = $valor_total;
    }
    public function getIdFormaPagamento()
    {
        return $this->id_forma_pagamento;
    }
    public function setIdFormaPagamento($id_forma_pagamento)
    {
        $this->id_forma_pagamento = $id_forma_pagamento;
    }
    public function getValorFrete()
    {
        return $this->valor_frete;
    }
    public function setValorFrete($valor_frete)
    {
        $this->valor_frete = $valor_frete;
    }
    public function getItens()
    {
        return $this->itens;
    }
    public function setItens($itens)
    {
        $this->itens = $itens;
    }

    public function getDataFinalizacao()
    {
        return $this->data_finalizacao;
    }

    public function setDataFinalizacao($data_finalizacao): self
    {
        $this->data_finalizacao = $data_finalizacao;

        return $this;
    }

    // Metodo de consultar pedido
    public function consultarPedido(
        $numero_pedido,
        $id_cliente,
        $status_pedido,
        $data_pedido,
        $id_forma_pagamento
    ) {
        // Atribuições
        $this->setNumeroPedido($numero_pedido);
        $this->setIdCliente($id_cliente);
        $this->setStatusPedido($status_pedido);
        $this->setDataPedido($data_pedido);
        $this->setIdFormaPagamento($id_forma_pagamento);

        // Monta a query
        $sql = "SELECT
                p.id_pedido,
                p.numero_pedido,
                p.data_pedido,
                p.status_pedido,
                p.valor_total,
                p.valor_frete,
                c.id_cliente,
                c.nome_fantasia,
                p.id_forma_pagamento,
                p.descricao_forma_pagamento,
                ip.id_item_pedido,
                ip.id_produto,
                ip.nome_produto,
                ip.nome_cor,
                ip.nome_tipo_produto,
                prod.largura,
                ip.quantidade,
                ip.valor_unitario,
                ip.totalValor_produto
                FROM pedido p
                LEFT JOIN cliente c ON p.id_cliente = c.id_cliente
                LEFT JOIN item_pedido ip ON p.id_pedido = ip.id_pedido
                LEFT JOIN produto prod ON ip.id_produto = prod.id_produto
                WHERE 1=1";

        // Filtros dinâmicos
        if (!empty($this->getNumeroPedido())) {
            $sql .= " AND p.numero_pedido LIKE CONCAT('%', :numero_pedido, '%')";
        }
        if (!empty($this->getIdCliente())) {
            $sql .= " AND p.id_cliente = :id_cliente";
        }
        if (!empty($this->getStatusPedido())) {
            $sql .= " AND p.status_pedido = :status_pedido";
        }
        if (!empty($this->getDataPedido())) {
            $sql .= " AND DATE(p.data_pedido) = :data_pedido";
        }
        if (!empty($this->getIdFormaPagamento())) {
            $sql .= " AND p.id_forma_pagamento = :id_forma_pagamento";
        }

        $sql .= " ORDER BY p.id_pedido DESC, ip.id_item_pedido ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Bind dinâmico
            if (!empty($this->getNumeroPedido())) {
                $query->bindValue(':numero_pedido', $this->getNumeroPedido(), PDO::PARAM_STR);
            }
            if (!empty($this->getIdCliente())) {
                $query->bindValue(':id_cliente', $this->getIdCliente(), PDO::PARAM_INT);
            }
            if (!empty($this->getStatusPedido())) {
                $query->bindValue(':status_pedido', $this->getStatusPedido(), PDO::PARAM_STR);
            }
            if (!empty($this->getDataPedido())) {
                $query->bindValue(':data_pedido', $this->getDataPedido(), PDO::PARAM_STR);
            }
            if (!empty($this->getIdFormaPagamento())) {
                $query->bindValue(':id_forma_pagamento', $this->getIdFormaPagamento(), PDO::PARAM_INT);
            }

            $query->execute();
            $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar os resultados
            $pedidosAgrupados = [];
            foreach ($resultados as $linha) {
                $idPedido = $linha['id_pedido'];
                if (!isset($pedidosAgrupados[$idPedido])) {
                    $pedidosAgrupados[$idPedido] = [
                        'id_pedido' => $idPedido,
                        'numero_pedido' => $linha['numero_pedido'],
                        'data_pedido' => $linha['data_pedido'],
                        'status_pedido' => $linha['status_pedido'],
                        'valor_total' => $linha['valor_total'],
                        'valor_frete' => $linha['valor_frete'],
                        'id_cliente' => $linha['id_cliente'],
                        'nome_fantasia' => $linha['nome_fantasia'],
                        'id_forma_pagamento' => $linha['id_forma_pagamento'],
                        'descricao_forma_pagamento' => $linha['descricao_forma_pagamento'],
                        'itens' => []
                    ];
                }

                // Adiciona o item ao pedido correspondente, se houver item
                if ($linha['id_item_pedido']) {
                    $pedidosAgrupados[$idPedido]['itens'][] = [
                        'id_item_pedido' => $linha['id_item_pedido'],
                        'id_produto' => $linha['id_produto'],
                        'nome_produto' => $linha['nome_produto'],
                        'nome_cor' => $linha['nome_cor'],
                        'nome_tipo_produto' => $linha['nome_tipo_produto'],
                        'largura' => $linha['largura'], // ADICIONADO: inclui a largura nos dados do item
                        'quantidade' => $linha['quantidade'],
                        'valor_unitario' => $linha['valor_unitario'],
                        'totalValor_produto' => $linha['totalValor_produto']
                    ];
                }
            }

            return array_values($pedidosAgrupados); // Retorna um array indexado numericamente

        } catch (PDOException $e) {
            error_log("Erro ao consultar pedidos: " . $e->getMessage());
            return false;
        }
    }
    // método de cadastrar pedido da classe Pedido
    public function cadastrarPedido(
        $id_cliente,
        $status_pedido,
        $valor_total,
        $id_forma_pagamento,
        $valor_frete,
        $itens
    ) {
        // Setters
        $this->setIdCliente($id_cliente);
        $this->setStatusPedido($status_pedido);
        $this->setValorTotal($valor_total);
        $this->setIdFormaPagamento($id_forma_pagamento);
        $this->setValorFrete($valor_frete);
        $this->setItens($itens);

        // Fuso Horário de Brasília
        $fusoHorarioBrasil = new DateTimeZone('America/Sao_Paulo');
        $dataHoraBrasil = new DateTime('now', $fusoHorarioBrasil);
        $dataFormatada = $dataHoraBrasil->format("Y-m-d H:i:s");
        $this->setDataPedido($dataFormatada);

        // Validações
        if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
            throw new Exception("Usuário não logado. Não é possível cadastrar pedido.");
        }
        $this->setIdUsuario(intval($_SESSION['id_usuario']));

        $statusValidos = ['Pendente', 'Aguardando Pagamento', 'Finalizado', 'Cancelado'];
        if (!in_array($status_pedido, $statusValidos)) {
            throw new Exception("Status de pedido inválido.");
        }

        if (!is_array($this->itens) || count($this->itens) === 0) {
            throw new Exception("Nenhum item válido para o pedido.");
        }

        try {
            $bd = $this->conectarBanco();
            $bd->beginTransaction();

            // ========================================================================
            // BUSCAR A DESCRIÇÃO DA FORMA DE PAGAMENTO
            // ========================================================================
            $sql_pagamento = "SELECT descricao FROM forma_pagamento WHERE id_forma_pagamento = :id_forma_pagamento LIMIT 1";
            $query_pagamento = $bd->prepare($sql_pagamento);
            $query_pagamento->bindValue(':id_forma_pagamento', $this->getIdFormaPagamento(), PDO::PARAM_INT);
            $query_pagamento->execute();
            $descricao_pagamento = $query_pagamento->fetchColumn();

            if ($descricao_pagamento === false) {
                throw new Exception("Forma de pagamento inválida ou não encontrada.");
            }

            // Gerar número do pedido
            $proximoId = $bd->query("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name = 'pedido' AND table_schema = DATABASE()")->fetchColumn();
            $numero_pedido_gerado = str_pad($proximoId, 6, "0", STR_PAD_LEFT);
            $this->setNumeroPedido($numero_pedido_gerado);

            // ========================================================================
            // 2. AJUSTE: INSERIR O PEDIDO COM A DESCRIÇÃO DO PAGAMENTO
            // ========================================================================
            $sql = "INSERT INTO pedido
                (id_cliente, id_usuario, data_pedido, status_pedido, valor_total, id_forma_pagamento, descricao_forma_pagamento, valor_frete, numero_pedido)
                VALUES (:id_cliente, :id_usuario, :data_pedido, :status_pedido, :valor_total, :id_forma_pagamento, :descricao_forma_pagamento, :valor_frete, :numero_pedido)"; // << MODIFICADO

            $query = $bd->prepare($sql);
            $query->bindValue(':id_cliente', $this->getIdCliente(), PDO::PARAM_INT);
            $query->bindValue(':id_usuario', $this->getIdUsuario(), PDO::PARAM_INT);
            $query->bindValue(':data_pedido', $this->getDataPedido());
            $query->bindValue(':status_pedido', $this->getStatusPedido(), PDO::PARAM_STR);
            $query->bindValue(':valor_total', (float)$this->getValorTotal());
            $query->bindValue(':id_forma_pagamento', $this->getIdFormaPagamento(), PDO::PARAM_INT);
            $query->bindValue(':descricao_forma_pagamento', $descricao_pagamento, PDO::PARAM_STR); // << NOVO
            $query->bindValue(':valor_frete', (float)$this->getValorFrete());
            $query->bindValue(':numero_pedido', $this->getNumeroPedido(), PDO::PARAM_STR);
            $query->execute();

            $id_pedido = $bd->lastInsertId();

            // ========================================================================
            // CONSULTAS PARA BUSCAR DADOS COMPLETOS DOS PRODUTOS
            // ========================================================================
            $sql_busca_produto = "SELECT
                                p.nome_produto, p.custo_compra, p.id_cor, p.id_tipo_produto,
                                c.nome_cor,
                                tp.nome_tipo
                                FROM produto p
                                LEFT JOIN cor c ON p.id_cor = c.id_cor
                                LEFT JOIN tipo_produto tp ON p.id_tipo_produto = tp.id_tipo_produto
                                WHERE p.id_produto = :id_produto LIMIT 1";
            $query_busca_produto = $bd->prepare($sql_busca_produto);

            // Preparar o insert do item_pedido uma vez, fora do loop
            $sql_item = "INSERT INTO item_pedido
                    (id_pedido, id_produto, id_cor, id_tipo_produto, nome_produto, nome_cor, nome_tipo_produto, quantidade, valor_unitario, totalValor_produto, custo_compra)
                    VALUES (:id_pedido, :id_produto, :id_cor, :id_tipo_produto, :nome_produto, :nome_cor, :nome_tipo_produto, :quantidade, :valor_unitario, :totalValor_produto, :custo_compra)"; // << MODIFICADO
            $query_item = $bd->prepare($sql_item);


            // Inserir itens do pedido
            foreach ($this->itens as $item) {
                $id_produto = (int)$item['id_produto'];

                // Buscar todos os dados "fotografáveis" do produto
                $query_busca_produto->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
                $query_busca_produto->execute();
                $dados_produto = $query_busca_produto->fetch(PDO::FETCH_ASSOC);

                if ($dados_produto === false) {
                    throw new Exception("Produto com ID $id_produto não encontrado.");
                }

                $query_item->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                $query_item->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
                $query_item->bindValue(':id_cor', $dados_produto['id_cor'], PDO::PARAM_INT);
                $query_item->bindValue(':id_tipo_produto', $dados_produto['id_tipo_produto'], PDO::PARAM_INT);
                $query_item->bindValue(':nome_produto', $dados_produto['nome_produto'], PDO::PARAM_STR);
                $query_item->bindValue(':nome_cor', $dados_produto['nome_cor'], PDO::PARAM_STR);
                $query_item->bindValue(':nome_tipo_produto', $dados_produto['nome_tipo'], PDO::PARAM_STR);
                $query_item->bindValue(':quantidade', (float)$item['quantidade']);
                $query_item->bindValue(':valor_unitario', (float)$item['valor_unitario']);
                $query_item->bindValue(':totalValor_produto', (float)$item['totalValor_produto']);
                $query_item->bindValue(':custo_compra', (float)$dados_produto['custo_compra']);
                $query_item->execute();
            }
            $bd->commit();
            return true;
        } catch (PDOException | Exception $e) {
            if ($bd->inTransaction()) $bd->rollBack();
            error_log("Erro ao cadastrar pedido: " . $e->getMessage());
            // Em um ambiente de produção, você poderia retornar uma mensagem mais genérica.
            // echo "Ocorreu um erro ao processar seu pedido. Tente novamente mais tarde.";
            echo "Erro: " . $e->getMessage();
            return false;
        }
    }
    // método de alterar pedido
    public function alterarPedido(
        $id_pedido,
        $id_cliente,
        $valor_total,
        $id_forma_pagamento,
        $valor_frete,
        $itens
    ) {
        $this->setIdPedido($id_pedido);
        $this->setIdCliente($id_cliente);
        $this->setValorTotal($valor_total);
        $this->setIdFormaPagamento($id_forma_pagamento);
        $this->setValorFrete($valor_frete);
        $this->setItens($itens);

        if (!is_array($this->itens) || count($this->itens) === 0) {
            throw new Exception("Nenhum item foi enviado para alteração.");
        }

        try {
            $bd = $this->conectarBanco();

            // Verifica status do pedido
            $sqlStatus = "SELECT status_pedido FROM pedido WHERE id_pedido = :id_pedido";
            $queryStatus = $bd->prepare($sqlStatus);
            $queryStatus->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $queryStatus->execute();
            $statusResult = $queryStatus->fetch(PDO::FETCH_ASSOC);

            if (!$statusResult) throw new Exception("Pedido não encontrado.");
            if (strtolower($statusResult['status_pedido']) !== 'pendente') {
                throw new Exception("Alteração não permitida. Apenas pedidos PENDENTES podem ser alterados.");
            }

            $bd->beginTransaction();

            // ========================================================================
            // BUSCAR A DESCRIÇÃO DA FORMA DE PAGAMENTO
            // ========================================================================
            $sql_pagamento = "SELECT descricao FROM forma_pagamento WHERE id_forma_pagamento = :id_forma_pagamento LIMIT 1";
            $query_pagamento = $bd->prepare($sql_pagamento);
            $query_pagamento->bindValue(':id_forma_pagamento', $this->getIdFormaPagamento(), PDO::PARAM_INT);
            $query_pagamento->execute();
            $descricao_pagamento = $query_pagamento->fetchColumn();

            if ($descricao_pagamento === false) {
                throw new Exception("Forma de pagamento inválida ou não encontrada.");
            }

            // ========================================================================
            // ATUALIZAR O PEDIDO COM A DESCRIÇÃO DO PAGAMENTO
            // ========================================================================
            $sql = "UPDATE pedido
                SET id_cliente = :id_cliente,
                    valor_total = :valor_total,
                    id_forma_pagamento = :id_forma_pagamento,
                    descricao_forma_pagamento = :descricao_forma_pagamento, -- << NOVO
                    valor_frete = :valor_frete
                WHERE id_pedido = :id_pedido";
            $query = $bd->prepare($sql);
            $query->bindValue(':id_cliente', $this->getIdCliente(), PDO::PARAM_INT);
            $query->bindValue(':valor_total', (float)$this->getValorTotal());
            $query->bindValue(':id_forma_pagamento', $this->getIdFormaPagamento(), PDO::PARAM_INT);
            $query->bindValue(':descricao_forma_pagamento', $descricao_pagamento, PDO::PARAM_STR); // << NOVO
            $query->bindValue(':valor_frete', (float)$this->getValorFrete());
            $query->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $query->execute();

            // 🔹 Lógica para atualizar itens (excluir, alterar, inserir)
            $sqlItensAtuais = "SELECT id_produto FROM item_pedido WHERE id_pedido = :id_pedido";
            $queryItens = $bd->prepare($sqlItensAtuais);
            $queryItens->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
            $queryItens->execute();
            $produtosAtuais = $queryItens->fetchAll(PDO::FETCH_COLUMN);
            $produtosNovos = array_map(fn($i) => (int)$i['id_produto'], $this->itens);

            // Excluir itens que foram removidos
            foreach ($produtosAtuais as $produtoAntigo) {
                if (!in_array($produtoAntigo, $produtosNovos)) {
                    $sqlDelete = "DELETE FROM item_pedido WHERE id_pedido = :id_pedido AND id_produto = :id_produto";
                    $queryDelete = $bd->prepare($sqlDelete);
                    $queryDelete->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                    $queryDelete->bindValue(':id_produto', $produtoAntigo, PDO::PARAM_INT);
                    $queryDelete->execute();
                }
            }

            // ========================================================================
            // PREPARAR CONSULTAS FORA DO LOOP PARA EFICIÊNCIA
            // ========================================================================

            // Consulta para buscar todos os dados de um NOVO produto
            $sql_busca_produto = "SELECT p.nome_produto, p.custo_compra, p.id_cor, p.id_tipo_produto, c.nome_cor, tp.nome_tipo AS nome_tipo_produto
                                FROM produto p
                                LEFT JOIN cor c ON p.id_cor = c.id_cor
                                LEFT JOIN tipo_produto tp ON p.id_tipo_produto = tp.id_tipo_produto
                                WHERE p.id_produto = :id_produto LIMIT 1";
            $query_busca_produto = $bd->prepare($sql_busca_produto);

            // Consulta para INSERIR um novo item
            $sql_insert_item = "INSERT INTO item_pedido (id_pedido, id_produto, id_cor, id_tipo_produto, nome_produto, nome_cor, nome_tipo_produto, quantidade, valor_unitario, totalValor_produto, custo_compra)
                            VALUES (:id_pedido, :id_produto, :id_cor, :id_tipo_produto, :nome_produto, :nome_cor, :nome_tipo_produto, :quantidade, :valor_unitario, :totalValor_produto, :custo_compra)";
            $query_insert_item = $bd->prepare($sql_insert_item);

            // Consulta para ATUALIZAR um item existente
            $sql_update_item = "UPDATE item_pedido SET quantidade = :quantidade, valor_unitario = :valor_unitario, totalValor_produto = :totalValor_produto, custo_compra = :custo_compra
                            WHERE id_pedido = :id_pedido AND id_produto = :id_produto";
            $query_update_item = $bd->prepare($sql_update_item);

            // Inserir ou atualizar itens
            foreach ($this->itens as $item) {
                $id_produto = (int)$item['id_produto'];

                // Apenas o custo de compra é sempre re-buscado, pois pode ter sido atualizado
                $custo_compra = (float)$bd->query("SELECT custo_compra FROM produto WHERE id_produto = $id_produto LIMIT 1")->fetchColumn();

                if (in_array($id_produto, $produtosAtuais)) {
                    // Bloco para ATUALIZAR item existente
                    $query_update_item->bindValue(':quantidade', (float)$item['quantidade']);
                    $query_update_item->bindValue(':valor_unitario', (float)$item['valor_unitario']);
                    $query_update_item->bindValue(':totalValor_produto', (float)$item['totalValor_produto']);
                    $query_update_item->bindValue(':custo_compra', $custo_compra);
                    $query_update_item->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                    $query_update_item->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
                    $query_update_item->execute();
                } else {
                    // Bloco para INSERIR novo item: busca todos os dados para a "fotografia"
                    $query_busca_produto->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
                    $query_busca_produto->execute();
                    $dados_produto = $query_busca_produto->fetch(PDO::FETCH_ASSOC);

                    if ($dados_produto === false) {
                        throw new Exception("Produto com ID $id_produto não encontrado ao tentar adicioná-lo.");
                    }

                    $query_insert_item->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                    $query_insert_item->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
                    $query_insert_item->bindValue(':id_cor', $dados_produto['id_cor'], PDO::PARAM_INT);
                    $query_insert_item->bindValue(':id_tipo_produto', $dados_produto['id_tipo_produto'], PDO::PARAM_INT);
                    $query_insert_item->bindValue(':nome_produto', $dados_produto['nome_produto'], PDO::PARAM_STR);
                    $query_insert_item->bindValue(':nome_cor', $dados_produto['nome_cor'], PDO::PARAM_STR);
                    $query_insert_item->bindValue(':nome_tipo_produto', $dados_produto['nome_tipo_produto'], PDO::PARAM_STR);
                    $query_insert_item->bindValue(':quantidade', (float)$item['quantidade']);
                    $query_insert_item->bindValue(':valor_unitario', (float)$item['valor_unitario']);
                    $query_insert_item->bindValue(':totalValor_produto', (float)$item['totalValor_produto']);
                    $query_insert_item->bindValue(':custo_compra', (float)$dados_produto['custo_compra']);
                    $query_insert_item->execute();
                }
            }
            $bd->commit();
            return true;
        } catch (PDOException | Exception $e) {
            if ($bd->inTransaction()) $bd->rollBack();
            error_log("Erro ao alterar pedido: " . $e->getMessage());
            print "Erro ao alterar pedido: " . $e->getMessage();
            return false;
        }
    }
    // Excluir pedido
    public function excluirPedido($id_pedido)
    {
        $this->setIdPedido($id_pedido);
        try {
            $bd = $this->conectarBanco();

            // 1. Verifica status do pedido
            $sqlStatus = "SELECT status_pedido FROM pedido WHERE id_pedido = :id_pedido";
            $queryStatus = $bd->prepare($sqlStatus);
            $queryStatus->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $queryStatus->execute();
            $status = $queryStatus->fetch(PDO::FETCH_ASSOC);

            if (!$status) {
                throw new Exception("Pedido não encontrado.");
            }

            $statusPedido = strtolower(trim($status['status_pedido']));

            if (in_array($statusPedido, ['finalizado', 'aguardando pagamento', 'finalizado'])) {
                throw new Exception("Não é possível excluir pedido com status $statusPedido.");
            }
            $bd->beginTransaction();
            // 1 Exclui itens do pedido
            $sqlDeleteItens = "DELETE FROM item_pedido WHERE id_pedido = :id_pedido";
            $queryDeleteItens = $bd->prepare($sqlDeleteItens);
            $queryDeleteItens->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $queryDeleteItens->execute();

            // 1 Exclui o pedido
            $sqlDeletePedido = "DELETE FROM pedido WHERE id_pedido = :id_pedido";
            $queryDeletePedido = $bd->prepare($sqlDeletePedido);
            $queryDeletePedido->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $queryDeletePedido->execute();

            $bd->commit();

            return true;
        } catch (PDOException | Exception $e) {
            if ($bd->inTransaction()) {
                $bd->rollBack();
            }
            error_log("Erro ao excluir pedido: " . $e->getMessage());
            echo "Erro ao excluir pedido: " . $e->getMessage();
            return false;
        }
    }
    // Aprovar pedido
    // e realizado uma consulta do pedido no banco de dados
    public function aprovarPedido($id_pedido)
    {
        $this->setIdPedido($id_pedido);

        $sql = "SELECT ip.id_produto, ip.quantidade
                FROM pedido p
                INNER JOIN item_pedido ip ON p.id_pedido = ip.id_pedido
                WHERE p.id_pedido = :id_pedido";

        try {
            $bd = $this->conectarBanco();
            $bd->beginTransaction();

            $query = $bd->prepare($sql);
            $query->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $query->execute();
            $itens = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($itens)) {
                throw new Exception("Pedido não encontrado ou sem itens.");
            }

            // Verificar estoque
            foreach ($itens as $item) {
                $id_produto = $item['id_produto'];
                $quantidade = $item['quantidade'];
                $estoqueAtual = $this->consultarEstoque($id_produto);

                if ($estoqueAtual < $quantidade) {
                    throw new Exception("Estoque insuficiente para o produto ID {$id_produto}. Disponível: {$estoqueAtual}, necessário: {$quantidade}.");
                }
            }

            // Atualizar estoque
            foreach ($itens as $item) {
                if (!$this->atualizarEstoque($item['id_produto'], $item['quantidade'])) {
                    throw new Exception("Erro ao atualizar estoque para o produto ID {$item['id_produto']}.");
                }
            }

            // Atualizar status
            $sqlStatus = "UPDATE pedido SET status_pedido = 'Aguardando Pagamento' WHERE id_pedido = :id_pedido";
            $stmtStatus = $bd->prepare($sqlStatus);
            $stmtStatus->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $stmtStatus->execute();

            $bd->commit();
            return ["success" => true, "message" => "Pedido aprovado com sucesso"];
        } catch (PDOException | Exception $e) {
            if ($bd->inTransaction()) {
                $bd->rollBack();
            }
            error_log("Erro ao aprovar pedido: " . $e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    // Consulta estoque atual de um produto
    public function consultarEstoque($id_produto)
    {
        $bd = $this->conectarBanco();
        $sql = "SELECT quantidade FROM produto WHERE id_produto = :id_produto";
        try {
            $query = $bd->prepare($sql);
            $query->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['quantidade'] : 0;
        } catch (PDOException $e) {
            error_log("Erro ao consultar estoque: " . $e->getMessage());
            return 0;
        }
    }
    // Atualizar estoque
    // ao aprovar um pedido, o status do pedido é alterado para "Aguardando Pagamento"
    // faz um update na quantidade do pedido no estoque
    public function atualizarEstoque($id_produto, $quantidade)
    {
        $bd = $this->conectarBanco();
        $sql = "UPDATE produto SET quantidade = quantidade - :quantidade WHERE id_produto = :id_produto";
        try {
            $query = $bd->prepare($sql);
            $query->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
            $query->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar estoque: " . $e->getMessage());
            return false;
        }
    }
    // metodo de cancelar pedido
    public function cancelarPedido($id_pedido, $status_pedido)
    {
        $this->setIdPedido($id_pedido);
        $this->setStatusPedido($status_pedido);

        try {
            $bd = $this->conectarBanco();
            // Verifica o status atual do pedido
            $sqlStatus = "SELECT status_pedido FROM pedido WHERE id_pedido = :id_pedido";
            $queryStatus = $bd->prepare($sqlStatus);
            $queryStatus->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $queryStatus->execute();
            $statusAtual = $queryStatus->fetchColumn();

            if (!$statusAtual) {
                throw new Exception("Pedido não encontrado.");
            }

            $bd->beginTransaction();

            // Se o pedido estava aprovado ou aguardando pagamento, devolve ao estoque
            if (in_array($statusAtual, ['Aguardando Pagamento'])) {
                $sqlItens = "SELECT id_produto, quantidade FROM item_pedido WHERE id_pedido = :id_pedido";
                $queryItens = $bd->prepare($sqlItens);
                $queryItens->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
                $queryItens->execute();
                $itens = $queryItens->fetchAll(PDO::FETCH_ASSOC);
                foreach ($itens as $item) {
                    $sqlUpdateEstoque = "UPDATE produto SET quantidade = quantidade + :quantidade WHERE id_produto = :id_produto";
                    $queryEstoque = $bd->prepare($sqlUpdateEstoque);
                    $queryEstoque->bindValue(':quantidade', $item['quantidade'], PDO::PARAM_INT);
                    $queryEstoque->bindValue(':id_produto', $item['id_produto'], PDO::PARAM_INT);
                    $queryEstoque->execute();
                }
            }

            // Atualiza o status do pedido e data_finalizacao (se status for Cancelado)
            $sqlUpdateStatus = "UPDATE pedido
                            SET status_pedido = :status_pedido";

            if (in_array($this->getStatusPedido(), ['Cancelado', 'Finalizado'])) {
                $sqlUpdateStatus .= ", data_finalizacao = :data_finalizacao";
            }

            $sqlUpdateStatus .= " WHERE id_pedido = :id_pedido";

            $queryStatus = $bd->prepare($sqlUpdateStatus);
            $queryStatus->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $queryStatus->bindValue(':status_pedido', $this->getStatusPedido(), PDO::PARAM_STR);

            if (in_array($this->getStatusPedido(), ['Cancelado'])) {
                $queryStatus->bindValue(':data_finalizacao', date('Y-m-d'));
            }

            $queryStatus->execute();

            $bd->commit();
            return true;
        } catch (PDOException | Exception $e) {
            if ($bd->inTransaction()) {
                $bd->rollBack();
            }
            error_log("Erro ao cancelar o pedido: " . $e->getMessage());
            return false;
        }
    }
    // metodo para finalizar o pedido
    public function finalizarPedido($id_pedido, $status_pedido)
    {
        $this->setIdPedido($id_pedido);
        $this->setStatusPedido($status_pedido);

        // Define o fuso horário para 'America/Sao_Paulo', que é o Fuso Horário de Brasília
        $fusoHorarioBrasil = new DateTimeZone('America/Sao_Paulo');

        // Cria um objeto DateTime para a data e hora atual, aplicando o fuso horário de Brasília
        $dataHoraBrasil = new DateTime('now', $fusoHorarioBrasil);

        // Formata a data no formato desejado ("Y-m-d H:i:s")
        $dataFormatada = $dataHoraBrasil->format("Y-m-d H:i:s");

        $this->setdataFinalizacao($dataFormatada);


        $sql = "UPDATE pedido
            SET status_pedido = :status_pedido";

        if (in_array($this->getStatusPedido(), ['Cancelado', 'Finalizado'])) {
            $sql .= ", data_finalizacao = :data_finalizacao";
        }

        $sql .= " WHERE id_pedido = :id_pedido";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
            $query->bindValue(':status_pedido', $this->getStatusPedido(), PDO::PARAM_STR);
            if (in_array($this->getStatusPedido(), ['Finalizado'])) {
                // data e hora atual `${ano}-${mes}-${dia} ${hora}:${minuto}:${segundo}`;
                $query->bindValue(':data_finalizacao',  $this->getdataFinalizacao());
            }

            $query->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao finalizar o pedido: " . $e->getMessage());
            print "Erro ao finalizar o pedido: " . $e->getMessage();
            return false;
        }
    }



    // RELATÓRIOS


    // faturamento mensal *
    public function faturamentoMensal($ano_faturamento, $mes_faturamento = null)
    {
        $sql = "SELECT
            MONTH(data_pedido) AS mes,
            -- Contando os Pedidos finalizados
            COUNT(CASE WHEN status_pedido = 'Finalizado' THEN 1 END) AS total_pedidos,
            SUM(CASE WHEN status_pedido = 'Finalizado' THEN valor_total ELSE 0 END) AS faturamento,
            -- Contando os Pedidos cancelados
            COUNT(CASE WHEN status_pedido = 'Cancelado' THEN 1 END) AS total_cancelados,
            -- Contando os Pedidos em aberto ou outro status
            COUNT(CASE WHEN status_pedido NOT IN ('Finalizado', 'Cancelado') THEN 1 END) AS total_abertos
            FROM pedido
            WHERE YEAR(data_pedido) = :ano_faturamento";

        if (!empty($mes_faturamento)) {
            $sql .= " AND MONTH(data_pedido) = :mes_faturamento";
        }

        $sql .= " GROUP BY MONTH(data_pedido)
                    ORDER BY mes ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':ano_faturamento', (int)$ano_faturamento, PDO::PARAM_INT);
            if (!empty($mes_faturamento)) {
                $query->bindValue(':mes_faturamento', (int)$mes_faturamento, PDO::PARAM_INT);
            }
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao buscar faturamento mensal: " . $e->getMessage());
            return false;
        }
    }
    public function produtosMaisVendidos($limite)
    {
        // -- Usando CTE para pré-calcular o valor total de cada pedido (Common Table Expression).
        //  -- view temporária" só válida durante a execução da query.
        $sql = " WITH PedidoTotais AS (
                SELECT
                    id_pedido,
                    SUM(quantidade * valor_unitario) AS valor_total_itens
                FROM item_pedido
                GROUP BY id_pedido
            )
            SELECT
                pr.id_produto,
                pr.nome_produto,
                SUM(ip.quantidade) AS total_vendido,
                SUM(ip.quantidade * ip.valor_unitario) AS faturamento_total,
                -- Evita divisão por zero se o produto não tiver vendas
                CASE WHEN SUM(ip.quantidade) > 0 THEN
                    SUM(ip.quantidade * ip.valor_unitario) / SUM(ip.quantidade)
                ELSE 0 END AS valor_medio_venda,

                -- Calcula o lucro bruto (Faturamento - Custo)
                SUM(ip.quantidade * ip.valor_unitario) - SUM(ip.quantidade * ip.custo_compra) AS lucro_bruto,
                -- Calcula o frete proporcional de forma eficiente
                SUM(
                    CASE WHEN pt.valor_total_itens > 0 THEN
                        (ip.quantidade * ip.valor_unitario / pt.valor_total_itens) * p.valor_frete
                    ELSE 0 END
                ) AS frete_proporcional,
            -- Lucro Líquido (Lucro Bruto - Frete Proporcional)
            (SUM(ip.quantidade * ip.valor_unitario) - SUM(ip.quantidade * ip.custo_compra)) - SUM(
                CASE WHEN pt.valor_total_itens > 0 THEN
                    (ip.quantidade * ip.valor_unitario / pt.valor_total_itens) * p.valor_frete
                ELSE 0 END
            ) AS lucro_liquido,

            -- Margem de Lucro
            CASE WHEN SUM(ip.quantidade * ip.valor_unitario) > 0 THEN
                ROUND(
                    ((SUM(ip.quantidade * ip.valor_unitario) - SUM(ip.quantidade * ip.custo_compra)) - SUM(
                        CASE WHEN pt.valor_total_itens > 0 THEN
                            (ip.quantidade * ip.valor_unitario / pt.valor_total_itens) * p.valor_frete
                        ELSE 0 END
                    )) / SUM(ip.quantidade * ip.valor_unitario) * 100, 2
                )
            ELSE 0 END AS margem_lucro

            FROM item_pedido ip
            INNER JOIN produto pr ON ip.id_produto = pr.id_produto
            INNER JOIN pedido p ON ip.id_pedido = p.id_pedido
            -- Juntamos os totais pré-calculados
            INNER JOIN PedidoTotais pt ON p.id_pedido = pt.id_pedido
            WHERE p.status_pedido = 'Finalizado'
            GROUP BY pr.id_produto, pr.nome_produto
            ORDER BY total_vendido DESC
            LIMIT :limite";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao buscar produtos mais vendidos: " . $e->getMessage());
            return false;
        }
    }
    //  as formas de pagamento mais usadas *
    public function formasPagamentoMaisUsadas()
    {
        $sql = "SELECT
                fp.descricao,
                COUNT(*) AS quantidade
            FROM pedido p
            INNER JOIN forma_pagamento fp ON p.id_forma_pagamento = fp.id_forma_pagamento
            GROUP BY fp.descricao
            ORDER BY quantidade DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao buscar formas de pagamento mais usadas: " . $e->getMessage());
            return false;
        }
    }
    //metodo de pedidos recentes *
    public function pedidosRecentes($dias = 7)
    {
        $dias = (int)$dias;
        if ($dias < 1 || $dias > 30) {
            $dias = 7;
        }

        $sql = "SELECT
            p.id_pedido,
            p.numero_pedido,
            p.data_pedido,
            p.status_pedido,
            p.valor_total,
            c.nome_fantasia AS cliente,
            fp.descricao AS forma_pagamento
            FROM pedido p
            INNER JOIN cliente c ON p.id_cliente = c.id_cliente
            LEFT JOIN forma_pagamento fp ON p.id_forma_pagamento = fp.id_forma_pagamento
            WHERE p.data_pedido >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
            ORDER BY p.data_pedido DESC, p.numero_pedido DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':dias', $dias, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao buscar pedidos recentes: " . $e->getMessage());
            return false;
        }
    }
    // clientes que mais compraram, com filtros por ano, mês e limite *
    public function clientesQueMaisCompraram($ano_referencia = null, $mes_referencia = null, $limite = 10)
    {
        $ano = $ano_referencia ?? date('Y');

        $sql = "SELECT
            c.nome_fantasia,
            COUNT(p.id_pedido) AS total_pedidos,
            SUM(p.valor_total) AS total_comprado
            FROM pedido AS p
            LEFT JOIN cliente AS c ON p.id_cliente = c.id_cliente
            WHERE p.status_pedido = 'Finalizado'
            AND YEAR(p.data_finalizacao) = :ano";

        if (!empty($mes_referencia)) {
            $sql .= " AND MONTH(p.data_finalizacao) = :mes";
        }

        $sql .= "
                GROUP BY p.id_cliente
                ORDER BY total_comprado DESC
                LIMIT :limite";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            $query->bindValue(':ano', $ano, PDO::PARAM_STR);

            if (!empty($mes_referencia)) {
                $query->bindValue(':mes', $mes_referencia, PDO::PARAM_STR);
            }

            $query->bindValue(':limite', $limite, PDO::PARAM_INT);

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao buscar clientes que mais compraram: " . $e->getMessage());
            return false;
        }
    }
    // quantidade de pedidos por mes ou por periodo *
    public function pedidosPorMes($ano_referencia, $mes_referencia = null)
    {
        $ano = $ano_referencia ?? date('Y');

        $sql = "SELECT
                MONTH(data_pedido) AS mes,
                COUNT(*) AS total_pedidos,
                SUM(CASE WHEN status_pedido = 'Finalizado' THEN 1 ELSE 0 END) AS pedidos_finalizados,
                SUM(CASE WHEN status_pedido = 'Cancelado' THEN 1 ELSE 0 END) AS pedidos_cancelados,
                SUM(CASE WHEN status_pedido NOT IN ('Finalizado', 'Cancelado') THEN 1 ELSE 0 END) AS pedidos_abertos,
                SUM(CASE WHEN status_pedido = 'Finalizado' THEN valor_total ELSE 0 END) AS faturamento
                FROM pedido
                WHERE YEAR(data_pedido) = :ano";

        if (!empty($mes_referencia)) {
            $sql .= " AND MONTH(data_pedido) = :mes";
        }

        $sql .= " GROUP BY MONTH(data_pedido)
                ORDER BY mes ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':ano', $ano, PDO::PARAM_INT);
            if (!empty($mes_referencia)) {
                $query->bindValue(':mes', $mes_referencia, PDO::PARAM_INT);
            }
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao buscar pedidos por mês: " . $e->getMessage());
            return false;
        }
    }

    // Resumo de Pedidos por Cliente
    public function resumoPedidosPorCliente($id_cliente = null)
    {

        $this->setIdCliente($id_cliente);
        $sql = "SELECT
                c.id_cliente,
                c.nome_fantasia,
                MAX(p.data_pedido) AS data_ultimo_pedido,
                COUNT(p.id_pedido) AS total_pedidos,
                SUM(CASE WHEN p.status_pedido = 'Pendente' THEN 1 ELSE 0 END) AS total_pendente,
                SUM(CASE WHEN p.status_pedido = 'Aguardando Pagamento' THEN 1 ELSE 0 END) AS total_em_andamento,
                SUM(CASE WHEN p.status_pedido = 'Cancelado' THEN 1 ELSE 0 END) AS total_cancelado,
                SUM(CASE WHEN p.status_pedido = 'Finalizado' THEN 1 ELSE 0 END) AS total_finalizado
            FROM pedido p
            INNER JOIN cliente c ON p.id_cliente = c.id_cliente";

        if (!empty($id_cliente)) {
            $sql .= " WHERE c.id_cliente = :id_cliente";
        }

        $sql .= " GROUP BY c.id_cliente, c.nome_fantasia
                ORDER BY data_ultimo_pedido DESC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            if (!empty($id_cliente)) {
                $query->bindParam(':id_cliente', $this->getIdCliente(), PDO::PARAM_INT);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }
    // Pedidos por status
    public function pedidosPorStatus($status = null, $data_inicio = null, $data_fim = null)
    {
        $sql = "SELECT
                status_pedido,
                COUNT(*) AS total_pedidos,
                SUM(valor_total) AS valor_total
            FROM pedido
            WHERE 1=1";

        // Filtra por status se informado
        if (!empty($status)) {
            $sql .= " AND status_pedido = :status";
        }

        // Filtro de datas - dependendo do status, usa uma data diferente
        if (!empty($data_inicio)) {
            //
            if (in_array($status, ['Finalizado', 'Cancelado'])) {
                $sql .= " AND data_finalizacao >= :data_inicio";
            } else {
                $sql .= " AND data_pedido >= :data_inicio";
            }
        }

        if (!empty($data_fim)) {
            if (in_array($status, ['Finalizado', 'Cancelado'])) {
                $sql .= " AND data_finalizacao <= :data_fim";
            } else {
                $sql .= " AND data_pedido <= :data_fim";
            }
        }

        // Se não filtrar por status, agrupa para trazer todos os status
        if (empty($status)) {
            $sql .= " GROUP BY status_pedido ORDER BY total_pedidos DESC";
        }

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            if (!empty($status)) {
                $query->bindValue(':status', $status, PDO::PARAM_STR);
            }
            if (!empty($data_inicio)) {
                $query->bindValue(':data_inicio', $data_inicio);
            }
            if (!empty($data_fim)) {
                $query->bindValue(':data_fim', $data_fim);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro em pedidosPorStatus: " . $e->getMessage());
            return false;
        }
    }
    // Pedidos com maior valor
    public function pedidosMaiorValor($limite)
    {
        $limite = (int)$limite;
        if ($limite <= 0) {
            $limite = 10;
        }

        $sql = "SELECT
                p.id_pedido,
                p.numero_pedido,
                p.data_pedido,
                c.nome_fantasia,
                p.valor_total
            FROM pedido p
            INNER JOIN cliente c ON p.id_cliente = c.id_cliente
            WHERE p.status_pedido = 'Finalizado'
            ORDER BY p.valor_total DESC
            LIMIT $limite";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->query($sql);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro em pedidosMaiorValor: " . $e->getMessage());
            return false;
        }
    }
    // Produtos que nunca foram vendidos
    public function produtosNuncaVendidos()
    {
        // Consulta para buscar produtos que nunca foram vendidos
        // o select principal busca os dados dos produtos
        // e o subselect verifica se o produto já foi vendido em algum pedido finalizado
        // o select distinct busca apenas produtos únicos
        $sql = "SELECT
                pr.id_produto,
                pr.nome_produto,
                pr.valor_venda,
                pr.quantidade
            FROM produto pr
            WHERE pr.id_produto NOT IN (
                SELECT DISTINCT ip.id_produto FROM item_pedido ip
                INNER JOIN pedido p ON ip.id_pedido = p.id_pedido
                WHERE p.status_pedido = 'Finalizado'
            )
            ORDER BY pr.nome_produto ASC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro em produtosNuncaVendidos: " . $e->getMessage());
            return false;
        }
    }


    // Retorna variação de vendas mês a mês para um produto específico
    public function variacaoVendasPorProduto($id_produto, $ano_faturamento)
    {
        try {
            $bd = $this->conectarBanco();
            // Consulta agregada por mês
            $sql = "SELECT
                    MONTH(p.data_pedido) AS mes,
                    SUM(ip.quantidade) AS total_quantidade,
                    SUM(ip.totalValor_produto) AS total_vendido
                    FROM pedido p
                    INNER JOIN item_pedido ip ON p.id_pedido = ip.id_pedido
                    WHERE ip.id_produto = :id_produto
                        AND YEAR(p.data_pedido) = :ano_faturamento
                        AND p.status_pedido = 'Finalizado'
                    GROUP BY MONTH(p.data_pedido)
                    ORDER BY mes ASC";

            $query = $bd->prepare($sql);
            $query->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
            $query->bindValue(':ano_faturamento', $ano_faturamento, PDO::PARAM_STR);
            $query->execute();

            // Inicializa todos os meses com zero
            $resultado = [];
            for ($i = 1; $i <= 12; $i++) {
                $resultado[$i] = [
                    'mes' => $i,
                    'total_quantidade' => 0,
                    'total_vendido' => 0.00
                ];
            }

            // Preenche com dados reais
            foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $linha) {
                $resultado[(int)$linha['mes']] = [
                    'mes' => (int)$linha['mes'],
                    'total_quantidade' => (float)$linha['total_quantidade'],
                    'total_vendido' => (float)$linha['total_vendido']
                ];
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log("Erro ao buscar variação de vendas: " . $e->getMessage());
            return false;
        }
    }
    // Lucro Bruto Mensal
    public function lucroBrutoMensal($ano, $mes)
    {
        try {
            $bd = $this->conectarBanco();

            $sql = "SELECT
                    YEAR(p.data_pedido) AS ano,
                    MONTH(p.data_pedido) AS mes,
                    SUM(ip.totalValor_produto) AS total_vendas,
                    SUM(pr.custo_compra * ip.quantidade) AS total_custo,
                    (SUM(ip.totalValor_produto) - SUM(pr.custo_compra * ip.quantidade)) AS lucro_bruto
                FROM pedido p
                LEFT JOIN item_pedido ip ON p.id_pedido = ip.id_pedido
                LEFT JOIN produto pr ON ip.id_produto = pr.id_produto
                WHERE YEAR(p.data_pedido) = :ano
                  AND p.status_pedido = 'Finalizado'";

            // Filtro opcional por mês
            if (!empty($mes)) {
                $sql .= " AND MONTH(p.data_pedido) = :mes";
            }

            $sql .= " GROUP BY MONTH(p.data_pedido)
                    ORDER BY mes ASC";

            $query = $bd->prepare($sql);
            $query->bindValue(':ano', $ano, PDO::PARAM_INT);

            if (!empty($mes)) {
                $query->bindValue(':mes', $mes, PDO::PARAM_INT);
            }

            $query->execute();
            $dados = $query->fetchAll(PDO::FETCH_ASSOC);

            // Se for retorno de todos os meses, inicializa meses vazios
            if (empty($mes)) {
                $resultado = [];
                for ($i = 1; $i <= 12; $i++) {
                    $resultado[$i] = [
                        'ano' => $ano,
                        'mes' => $i,
                        'total_vendas' => 0,
                        'total_custo' => 0,
                        'lucro_bruto' => 0
                    ];
                }
                foreach ($dados as $linha) {
                    $resultado[(int)$linha['mes']] = $linha;
                }
                return $resultado;
            }
            return $dados;
        } catch (PDOException $e) {
            error_log("Erro ao calcular lucro bruto mensal: " . $e->getMessage());
            return false;
        }
    }
    // busca de pedido para a impresssao de pedido
    public function buscarPedidosPorNumero($numero_pedido)
    {
        $this->setNumeroPedido($numero_pedido);

        $sql = "SELECT
            p.numero_pedido,
            p.data_pedido,
            p.status_pedido,
            p.valor_total,
            p.valor_frete,
            p.status_pedido,

            -- Cliente
            c.id_cliente,
            c.nome_fantasia,
            c.cnpj_cliente,
            -- Forma de pagamento
            fp.id_forma_pagamento,
            fp.descricao,

            -- Itens do pedido
            ip.id_item_pedido,
            ip.id_produto,
            pr.nome_produto,
            ip.quantidade,
            ip.valor_unitario,
            ip.totalValor_produto,
            pr.largura,
            cr.nome_cor as cor,

            -- usuario que fez o pedido
            u.nome_usuario

            FROM pedido p
            INNER JOIN cliente c ON p.id_cliente = c.id_cliente
            INNER JOIN forma_pagamento fp ON p.id_forma_pagamento = fp.id_forma_pagamento
            LEFT JOIN item_pedido ip ON p.id_pedido = ip.id_pedido
            LEFT JOIN produto pr ON ip.id_produto = pr.id_produto
            LEFT JOIN cor cr ON pr.id_cor = cr.id_cor
            LEFT JOIN usuario u ON p.id_usuario = u.id_usuario
            WHERE p.numero_pedido = :numero_pedido";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':numero_pedido', $this->getNumeroPedido(), PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao consultar pedidos: " . $e->getMessage());
            return false;
        }
    }
    //metodo de carrega os dados da notificao de pedidos Pendentes ou Aguardando Pagamento
    public function pedidosPendentesOuAguardando()
    {
        try {
            $bd = $this->conectarBanco();

            $sql = "SELECT 
                c.nome_fantasia AS nome_cliente,
                p.numero_pedido,
                p.status_pedido
                FROM pedido p
                INNER JOIN cliente c ON c.id_cliente = p.id_cliente
                WHERE p.status_pedido IN ('Pendente', 'Aguardando Pagamento')
                ORDER BY p.data_pedido DESC
        ";

            $query = $bd->prepare($sql);
            $query->execute();
            $pedidos = $query->fetchAll(PDO::FETCH_ASSOC);

            // Contagem separada por status
            $contagem = [
                'Pendente' => 0,
                'Aguardando Pagamento' => 0
            ];

            foreach ($pedidos as $pedido) {
                if (isset($contagem[$pedido['status_pedido']])) {
                    $contagem[$pedido['status_pedido']]++;
                }
            }

            return [
                'lista' => $pedidos,
                'contagem' => $contagem
            ];
        } catch (PDOException $e) {
            error_log("Erro ao buscar pedidos pendentes/aguardando: " . $e->getMessage());
            return false;
        }
    }
};
