<?php
// classe produto
class Produto extends Conexao
{
    // Atributos
    private $id_produto = null;
    private $custo_compra = null;
    private $valor_venda = null;
    private $nome_produto = null;
    private $composicao = null;
    private $largura = null;
    private $data_compra = null;
    private $quantidade = null;
    private $quantidade_minima = null;
    private $ncm_produto = null;
    private $id_fornecedor = null;
    private $img_produto = null;
    private $id_tipo_produto = null;
    private $id_cor = null;
    // metodos getters e setters

    public function getIdProduto()
    {
        return $this->id_produto;
    }
    public function setIdProduto($id_produto)
    {
        $this->id_produto = $id_produto;
    }


    // Define o custo de compra do produto
    public function getCustoCompra()
    {
        return $this->custo_compra;
    }
    // Define o custo de compra do produto
    public function setCustoCompra($custo_compra)
    {
        $this->custo_compra = $custo_compra;
    }

    public function getValorVenda()
    {
        return $this->valor_venda;
    }
    public function setValorVenda($valor_venda)
    {
        $this->valor_venda = $valor_venda;
    }


    public function getNomeProduto()
    {
        return $this->nome_produto;
    }
    public function setNomeProduto($nome_produto)
    {
        $this->nome_produto = $nome_produto;
    }
    public function getComposicao()
    {
        return $this->composicao;
    }
    public function setComposicao($composicao)
    {
        $this->composicao = $composicao;
    }
    public function getLargura()
    {
        return $this->largura;
    }
    public function setLargura($largura)
    {
        $this->largura = $largura;
    }
    public function getIdTipoProduto()
    {
        return $this->id_tipo_produto;
    }
    public function setIdTipoProduto($id_tipo_produto)
    {
        $this->id_tipo_produto = $id_tipo_produto;
    }
    public function getDataCompra()
    {
        return $this->data_compra;
    }
    public function setDataCompra($data_compra)
    {
        $this->data_compra = $data_compra;
    }
    public function getQuantidade()
    {
        return $this->quantidade;
    }
    public function setQuantidade($quantidade)
    {
        $this->quantidade = $quantidade;
    }
    public function getQuantidadeMinima()
    {
        return $this->quantidade_minima;
    }
    public function setQuantidadeMinima($quantidade_minima)
    {
        $this->quantidade_minima = $quantidade_minima;
    }
    public function getIdCor()
    {
        return $this->id_cor;
    }
    public function setIdCor($id_cor)
    {
        $this->id_cor = $id_cor;
    }
    public function getIdFornecedor()
    {
        return $this->id_fornecedor;
    }
    public function setIdFornecedor($id_fornecedor)
    {
        $this->id_fornecedor = $id_fornecedor;
    }
    public function getNcmProduto()
    {
        return $this->ncm_produto;
    }
    public function setNcmProduto($ncm_produto)
    {
        $this->ncm_produto = $ncm_produto;
    }
    public function getImgProduto()
    {
        return $this->img_produto;
    }
    public function setImgProduto($img_produto)
    {
        $this->img_produto = $img_produto;

        return $this;
    }
    // Cadastrar Produtos
    public function cadastrarProduto(
        $nome_produto,
        $id_tipo_produto,
        $id_cor,
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
        // Setando os atributos
        $this->setNomeProduto($nome_produto);
        $this->setIdTipoProduto($id_tipo_produto);   // agora é ID
        $this->setIdCor($id_cor);                    // agora é ID
        $this->setComposicao($composicao);
        $this->setQuantidade($quantidade);
        $this->setQuantidadeMinima($quantidade_minima);
        $this->setLargura($largura);
        $this->setCustoCompra($custo_compra);
        $this->setValorVenda($valor_venda);
        $this->setDataCompra($data_compra);
        $this->setNcmProduto($ncm_produto);
        $this->setIdFornecedor($id_fornecedor);
        $this->setImgProduto($img_produto);

        // Query para inserir produto
        $sql = "INSERT INTO produto (
                nome_produto, id_tipo_produto, id_cor, composicao, quantidade, largura,
                custo_compra, valor_venda, data_compra, ncm_produto, id_fornecedor, img_produto, quantidade_minima
            ) VALUES (
                :nome_produto, :id_tipo_produto, :id_cor, :composicao, :quantidade, :largura,
                :custo_compra, :valor_venda, :data_compra, :ncm_produto, :id_fornecedor, :img_produto, :quantidade_minima
            )";

        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Bind dos parâmetros
            $query->bindValue(':nome_produto', $this->getNomeProduto(), PDO::PARAM_STR);
            $query->bindValue(':id_tipo_produto', $this->getIdTipoProduto(), PDO::PARAM_INT);
            $query->bindValue(':id_cor', $this->getIdCor(), PDO::PARAM_INT);
            $query->bindValue(':composicao', $this->getComposicao(), PDO::PARAM_STR);
            $query->bindValue(':quantidade', $this->getQuantidade(), PDO::PARAM_STR);
            $query->bindValue(':largura', $this->getLargura(), PDO::PARAM_STR);
            $query->bindValue(':custo_compra', $this->getCustoCompra(), PDO::PARAM_STR);
            $query->bindValue(':valor_venda', $this->getValorVenda(), PDO::PARAM_STR);
            $query->bindValue(':data_compra', $this->getDataCompra(), PDO::PARAM_STR);
            $query->bindValue(':ncm_produto', $this->getNcmProduto(), PDO::PARAM_STR);
            $query->bindValue(':id_fornecedor', $this->getIdFornecedor(), PDO::PARAM_INT);
            $query->bindValue(':img_produto', $this->getImgProduto(), PDO::PARAM_STR);
            $query->bindValue(':quantidade_minima', $this->getQuantidadeMinima(), PDO::PARAM_STR);

            // Executar a query
            $query->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Erro ao cadastrar produto: " . $e->getMessage());
            print_r($e->getMessage());
            return false;
        }
    }
    // Consultar Produtos
    public function consultarProduto($nome_produto, $id_tipo_produto, $id_cor, $id_fornecedor)
    {
        // Setando os atributos
        $this->setNomeProduto($nome_produto);
        $this->setIdTipoProduto($id_tipo_produto);
        $this->setIdCor($id_cor);
        $this->setIdFornecedor($id_fornecedor);

        // Base da query com JOINs para trazer nomes de fornecedor, cor e tipo
        $sql = "SELECT
                p.id_produto,
                p.nome_produto,
                p.id_tipo_produto,
                t.nome_tipo AS tipo_produto,
                p.id_cor,
                c.nome_cor AS cor,
                p.composicao,
                p.quantidade,
                p.largura,
                p.custo_compra,
                p.valor_venda,
                p.data_compra,
                p.ncm_produto,
                p.id_fornecedor,
                f.razao_social AS fornecedor,
                p.img_produto,
                p.quantidade_minima
            FROM produto AS p
            LEFT JOIN fornecedor AS f ON p.id_fornecedor = f.id_fornecedor
            LEFT JOIN cor AS c ON p.id_cor = c.id_cor
            LEFT JOIN tipo_produto AS t ON p.id_tipo_produto = t.id_tipo_produto";

        // Array de condições
        $condicoes = [];

        // Filtros
        if (!empty($this->getNomeProduto())) {
            $condicoes[] = "p.nome_produto LIKE :nome_produto";
        }
        if (!empty($this->getIdTipoProduto())) {
            $condicoes[] = "p.id_tipo_produto = :id_tipo_produto";
        }
        if (!empty($this->getIdCor())) {
            $condicoes[] = "p.id_cor = :id_cor";
        }
        if (!empty($this->getIdFornecedor())) {
            $condicoes[] = "p.id_fornecedor = :id_fornecedor";
        }

        // Adiciona WHERE se houver condições
        if (count($condicoes) > 0) {
            $sql .= " WHERE " . implode(" AND ", $condicoes);
        }

        // Ordenação
        $sql .= " ORDER BY p.nome_produto ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Bind dos parâmetros
            if (!empty($this->getNomeProduto())) {
                $query->bindValue(':nome_produto', "%" . $this->getNomeProduto() . "%", PDO::PARAM_STR);
            }
            if (!empty($this->getIdTipoProduto())) {
                $query->bindValue(':id_tipo_produto', $this->getIdTipoProduto(), PDO::PARAM_INT);
            }
            if (!empty($this->getIdCor())) {
                $query->bindValue(':id_cor', $this->getIdCor(), PDO::PARAM_INT);
            }
            if (!empty($this->getIdFornecedor())) {
                $query->bindValue(':id_fornecedor', $this->getIdFornecedor(), PDO::PARAM_INT);
            }

            $query->execute();
            $produto = $query->fetchAll(PDO::FETCH_OBJ);
            return $produto;
        } catch (PDOException $e) {
            error_log("Erro ao consultar produtos: " . $e->getMessage());
            return false;
        }
    }
    // Alterar Produtos
    public function alterarProduto(
        $nome_produto,
        $id_tipo_produto,
        $id_cor,
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
    ) {
        // Setando os atributos
        $this->setIdProduto($id_produto);
        $this->setNomeProduto($nome_produto);
        $this->setIdTipoProduto($id_tipo_produto);
        $this->setIdCor($id_cor);
        $this->setComposicao($composicao);
        $this->setQuantidade($quantidade);
        $this->setQuantidadeMinima($quantidade_minima);
        $this->setLargura($largura);
        $this->setCustoCompra($custo_compra);
        $this->setValorVenda($valor_venda);
        $this->setDataCompra($data_compra);
        $this->setNcmProduto($ncm_produto);
        $this->setIdFornecedor($id_fornecedor);
        $this->setImgProduto($img_produto);

        // Query para alterar produto
        $sql = "UPDATE produto SET
                nome_produto = :nome_produto,
                id_tipo_produto = :id_tipo_produto,
                id_cor = :id_cor,
                composicao = :composicao,
                quantidade = :quantidade,
                quantidade_minima = :quantidade_minima,
                largura = :largura,
                custo_compra = :custo_compra,
                valor_venda = :valor_venda,
                data_compra = :data_compra,
                ncm_produto = :ncm_produto,
                id_fornecedor = :id_fornecedor,
                img_produto = :img_produto
                WHERE id_produto = :id_produto";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Bind dos parâmetros
            $query->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
            $query->bindValue(':nome_produto', $this->getNomeProduto(), PDO::PARAM_STR);
            $query->bindValue(':id_tipo_produto', $this->getIdTipoProduto(), PDO::PARAM_INT);
            $query->bindValue(':id_cor', $this->getIdCor(), PDO::PARAM_INT);
            $query->bindValue(':composicao', $this->getComposicao(), PDO::PARAM_STR);
            $query->bindValue(':quantidade', $this->getQuantidade(), PDO::PARAM_STR);
            $query->bindValue(':quantidade_minima', $this->getQuantidadeMinima(), PDO::PARAM_STR);
            $query->bindValue(':largura', $this->getLargura(), PDO::PARAM_STR);
            $query->bindValue(':custo_compra', $this->getCustoCompra(), PDO::PARAM_STR);
            $query->bindValue(':valor_venda', $this->getValorVenda(), PDO::PARAM_STR);
            $query->bindValue(':data_compra', $this->getDataCompra(), PDO::PARAM_STR);
            $query->bindValue(':ncm_produto', $this->getNcmProduto(), PDO::PARAM_STR);
            $query->bindValue(':id_fornecedor', $this->getIdFornecedor(), PDO::PARAM_INT);
            $query->bindValue(':img_produto', $this->getImgProduto(), PDO::PARAM_STR);
            // Executar a query
            $query->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao alterar produto: " . $e->getMessage());
            print_r($e->getMessage());
            return false;
        }
    }
    // verifica se o produto está vinculado a algum pedido
    public function produtoEmAlgumPedido($id_produto)
    {
        // seta o id do produto
        $this->setIdProduto($id_produto);

        // Exemplo considerando que a tabela seja item_pedido
        $sql = "SELECT COUNT(*) as total
            FROM item_pedido
            WHERE id_produto = :id_produto";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
            $query->execute();

            $resultado = $query->fetch(PDO::FETCH_ASSOC);

            if ($resultado && $resultado['total'] > 0) {
                // Produto está vinculado a pelo menos um pedido
                return true;
            } else {
                // Produto não está em nenhum pedido
                return false;
            }
        } catch (PDOException $e) {
            error_log("Erro ao verificar se produto está em pedido: " . $e->getMessage());
            return false;
        }
    }
    // excluir Produtos
    public function excluirProduto($id_produto)
    {
        // Seta o ID do produto
        $this->setIdProduto($id_produto);

        try {
            // Conecta ao banco
            $bd = $this->conectarBanco();

            // Inicia a transação
            $bd->beginTransaction();

            // Busca a imagem do produto
            $sqlBusca = "SELECT img_produto FROM produto WHERE id_produto = :id_produto";
            $queryBusca = $bd->prepare($sqlBusca);
            $queryBusca->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
            $queryBusca->execute();

            $resultado = $queryBusca->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                // Produto não encontrado
                error_log("Produto não encontrado para exclusão: ID {$this->getIdProduto()}");
                $bd->rollBack();
                return false;
            }

            // Se existe imagem, tenta excluir
            if (!empty($resultado['img_produto'])) {
                $caminhoImagem = $resultado['img_produto'];
                // Verifica se o caminho da imagem é válido
                if (file_exists($caminhoImagem)) {
                    if (!unlink($caminhoImagem)) {
                        // Falha ao excluir o arquivo
                        error_log("Erro ao excluir a imagem: $caminhoImagem");
                        $bd->rollBack();
                        return false;
                    }
                }
            }
            // Exclui o produto do banco
            $sqlDelete = "DELETE FROM produto WHERE id_produto = :id_produto";
            $queryDelete = $bd->prepare($sqlDelete);
            $queryDelete->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
            $queryDelete->execute();
            // Confirma a transação
            $bd->commit();
            return true;
        } catch (PDOException $e) {
            // Em caso de erro, faz rollback
            if ($bd->inTransaction()) {
                $bd->rollBack();
            }
            error_log("Erro ao excluir produto: " . $e->getMessage());
            return false;
        }
    }
    // consultarProdutoPedido
    public function consultarProdutoDinamico($produto)
    {
        $sql = "SELECT id_produto, nome_produto, c.nome_cor AS cor , largura, valor_venda, quantidade
            FROM produto p
            INNER JOIN tipo_produto tp ON p.id_tipo_produto = tp.id_tipo_produto
            INNER JOIN cor c ON p.id_cor = c.id_cor
            WHERE nome_produto LIKE :produto
            OR tp.nome_tipo LIKE :produto
            OR c.nome_cor LIKE :produto";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':produto', "%" . $produto . "%", PDO::PARAM_STR);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            return false;
        }
    }
    public function verificarProduto($nome_produto, $id_cor, $largura, $id_fornecedor, $id_tipo_produto)
    {
        $this->setNomeProduto($nome_produto);
        $this->setIdCor($id_cor);
        $this->setLargura($largura);
        $this->setIdFornecedor($id_fornecedor);
        $this->setIdTipoProduto($id_tipo_produto);

        $sqlProduto = "SELECT id_produto
                        FROM produto
                        WHERE nome_produto = :nome_produto
                        AND id_cor = :id_cor
                        AND largura = :largura
                        AND id_fornecedor = :id_fornecedor
                        AND id_tipo_produto = :id_tipo_produto
                        LIMIT 1";
        $sqlFornecedor = "SELECT razao_social FROM fornecedor WHERE id_fornecedor = :id_fornecedor LIMIT 1";
        $sqlCor        = "SELECT nome_cor FROM cor WHERE id_cor = :id_cor LIMIT 1";
        $sqlTipo  = "SELECT nome_tipo FROM tipo_produto WHERE id_tipo_produto = :id_tipo_produto LIMIT 1";
        try {
            $bd = $this->conectarBanco();

            // 1) Verificar se o produto existe
            $query = $bd->prepare($sqlProduto);
            $query->bindValue(':nome_produto', $this->getNomeProduto(), PDO::PARAM_STR);
            $query->bindValue(':id_cor', $this->getIdCor(), PDO::PARAM_INT);
            $query->bindValue(':largura', $this->getLargura(), PDO::PARAM_STR);
            $query->bindValue(':id_fornecedor', $this->getIdFornecedor(), PDO::PARAM_INT);
            $query->bindValue(':id_tipo_produto', $this->getIdTipoProduto(), PDO::PARAM_INT);
            $query->execute();

            $produto = $query->fetch(PDO::FETCH_ASSOC);

            if ($produto) {
                return ['existe' => true];
            }

            // 2) Buscar nome do fornecedor
            $queryFornecedor = $bd->prepare($sqlFornecedor);
            $queryFornecedor->bindValue(':id_fornecedor', $this->getIdFornecedor(), PDO::PARAM_INT);
            $queryFornecedor->execute();
            $fornecedor = $queryFornecedor->fetch(PDO::FETCH_ASSOC);

            // 3) Buscar nome da cor
            $queryCor = $bd->prepare($sqlCor);
            $queryCor->bindValue(':id_cor', $this->getIdCor(), PDO::PARAM_INT);
            $queryCor->execute();
            $cor = $queryCor->fetch(PDO::FETCH_ASSOC);

            // 3) Buscar nome da cor
            $queryTipoP = $bd->prepare($sqlTipo);
            $queryTipoP->bindValue(':id_tipo_produto', $this->getIdTipoProduto(), PDO::PARAM_INT);
            $queryTipoP->execute();
            $tipo = $queryTipoP->fetch(PDO::FETCH_ASSOC);

            return [
                'existe'     => false,
                'nome_cor'   => $cor['nome_cor'] ?? null,
                'fornecedor' => $fornecedor['razao_social'] ?? null,
                'nome_tipo'  => $tipo['nome_tipo'] ?? null,
            ];
        } catch (PDOException $e) {
            return ['existe' => false, 'erro' => $e->getMessage()];
        }
    }

    // verificar a quantidade de um produto
    public function verificarQuantidadeProduto($id_produto, $quantidade)
    {
        // Setando o id do produto
        $this->setIdProduto($id_produto);

        $sql = "SELECT quantidade FROM produto WHERE id_produto = :id_produto";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
            $query->execute();
            $resultado = $query->fetch(PDO::FETCH_OBJ);
            if ($resultado && $resultado->quantidade >= $quantidade) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Erro ao verificar quantidade do produto: " . $e->getMessage());
            return false; // Erro na consulta
        }
    }
    // produtos a baixo do limite minimo
    public function produtosAbaixoDoMinimo()
    {
        try {
            $bd = $this->conectarBanco();
            $sql = "SELECT
                    p.id_produto,
                    p.nome_produto,
                    p.quantidade,
                    p.quantidade_minima,
                    (p.quantidade_minima - p.quantidade) AS falta
                FROM produto p
                WHERE p.quantidade < p.quantidade_minima
                ORDER BY falta DESC";

            $stmt = $bd->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro ao buscar produtos abaixo do mínimo: " . $e->getMessage());
        }
    }

    //  Relatórios

    // Produtos com baixo estoque (limite opcional)
    public function produtosComBaixoEstoque($limite = null)
    {
        try {
            // Base da query
            $sql = "SELECT
                    p.nome_produto,
                    p.quantidade,
                    p.quantidade_minima,
                    (p.quantidade_minima - p.quantidade) AS falta
                    FROM produto p
                    WHERE p.quantidade < p.quantidade_minima";

            // Array de condições extras
            $condicoes = [];

            // Se limite foi informado, adiciona condição
            if (!empty($limite)) {
                $condicoes[] = "p.quantidade_minima <= :limite";
            }

            // Ordenação
            $sql .= " ORDER BY falta DESC";

            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Bind opcional do limite
            if (!empty($limite)) {
                $query->bindValue(':limite', $limite, PDO::PARAM_INT);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao buscar produtos com baixo estoque: " . $e->getMessage());
            return false;
        }
    }
    // Valor do custo de compra por produto baseado nos pedidos realizados
    public function custoTotalPorProduto($id_produto = null)
    {
        $this->setIdProduto($id_produto);
        try {
            $bd = $this->conectarBanco();
            $sql = "SELECT
                    p.id_produto,
                    p.nome_produto,
                    p.id_tipo_produto,
                    tp.nome_tipo AS tipo_produto,
                    p.largura,
                    p.composicao,
                    SUM(ip.quantidade) AS quantidade_total,
                    -- custo médio ponderado por item (valor histórico)
                    ROUND(SUM(ip.quantidade * ip.custo_compra) / SUM(ip.quantidade), 2) AS custo_unit_medio,
                    -- total investido (custo histórico)
                    ROUND(SUM(ip.quantidade * ip.custo_compra), 2) AS total_investido,
                    -- valor total vendido (valor histórico)
                    ROUND(SUM(ip.quantidade * ip.valor_unitario), 2) AS valor_total_pedidos,
                    -- lucro bruto (valor histórico de venda - custo histórico)
                    ROUND(SUM(ip.quantidade * ip.valor_unitario) - SUM(ip.quantidade * ip.custo_compra), 2) AS lucro_bruto
                    FROM item_pedido ip
                    LEFT JOIN produto p ON p.id_produto = ip.id_produto
                    LEFT JOIN pedido pe ON pe.id_pedido = ip.id_pedido
                    LEFT JOIN tipo_produto tp ON p.id_tipo_produto = tp.id_tipo_produto
                    WHERE pe.status_pedido = 'Finalizado'";
            if (!empty($id_produto)) {
                $sql .= " AND ip.id_produto = :id_produto";
            }

            $sql .= " GROUP BY
                    p.id_produto,
                    p.nome_produto,
                    tp.id_tipo_produto,
                    p.largura,
                    p.composicao
                    ORDER BY lucro_bruto DESC";

            $query = $bd->prepare($sql);

            if (!empty($id_produto)) {
                $query->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
            }

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao calcular custo total por produto: " . $e->getMessage());
            return false;
        }
    }
    //listar todos o produtos de um fornecedor especifico
    public function listarProdutosPorFornecedor($id_fornecedor = null)
    {
        $sql = "SELECT 
                p.id_produto,
                p.nome_produto,
                t.nome_tipo AS tipo_produto,
                c.nome_cor AS cor,
                p.quantidade,
                f.razao_social
            FROM produto p
            INNER JOIN fornecedor f ON p.id_fornecedor = f.id_fornecedor
            INNER JOIN cor c ON p.id_cor = c.id_cor
            INNER JOIN tipo_produto t ON p.id_tipo_produto = t.id_tipo_produto";

        // Adiciona cláusula WHERE apenas se ID for válido
        if (!empty($id_fornecedor)) {
            $this->setIdFornecedor($id_fornecedor);
            $sql .= " WHERE p.id_fornecedor = :id_fornecedor";
        }

        $sql .= " ORDER BY p.nome_produto ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            if (!empty($id_fornecedor)) {
                $query->bindValue(':id_fornecedor', $this->getIdFornecedor(), PDO::PARAM_INT);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao listar produtos por fornecedor: " . $e->getMessage());
            return false;
        }
    }
    // produtos com margem de lucro
    public function produtosMargem($limitePercentual = null, $id_produto = null)
    {
        $this->setIdProduto($id_produto);

        $sql = "SELECT
                nome_produto,
                custo_compra,
                valor_venda,
                ROUND(((valor_venda - custo_compra) / custo_compra) * 100, 2) AS margem_percentual
            FROM produto
            WHERE custo_compra > 0";
        // Adiciona filtro apenas se passado, e nunca os dois juntos
        if ($id_produto !== null) {
            $sql .= " AND id_produto = :id_produto";
        } elseif ($limitePercentual !== null) {
            $sql .= " AND ((valor_venda - custo_compra) / custo_compra) * 100 <= :limitePercentual";
        }
        $sql .= " ORDER BY margem_percentual ASC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            // Aplica o parâmetro condicionalmente (um só)
            if ($id_produto !== null) {
                $query->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
            } elseif ($limitePercentual !== null) {
                $query->bindValue(':limitePercentual', $limitePercentual, PDO::PARAM_STR);
            }
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar margem dos produtos: " . $e->getMessage());
            return false;
        }
    }
};
