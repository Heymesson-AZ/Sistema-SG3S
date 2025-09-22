<?php
//Classe Cliente
//incluir classe conexao
include_once 'Conexao.class.php';

class Cliente extends Conexao
{
    //atributos
    private $id_cliente = null;
    private $nome_representante = null;
    private $razao_social = null;
    private $nome_fantasia = null;
    private $cnpj_cliente = null;
    private $email = null;
    private $pdo;
    private $limite_credito = null;

    public function __construct()
    {
        $this->pdo = parent::conectarBanco();
    }

    //metodos getters e setters
    public function getIdCliente()
    {
        return $this->id_cliente;
    }
    public function setIdCliente($id_cliente)
    {
        $this->id_cliente = $id_cliente;
    }
    public function getNomeRepresentante()
    {
        return $this->nome_representante;
    }
    public function setNomeRepresentante($nome_representante)
    {
        $this->nome_representante = $nome_representante;
    }
    public function getRazaoSocial()
    {
        return $this->razao_social;
    }
    public function setRazaoSocial($razao_social)
    {
        $this->razao_social = $razao_social;
    }
    public function getNomeFantasia()
    {
        return $this->nome_fantasia;
    }
    public function setNomeFantasia($nome_fantasia)
    {
        $this->nome_fantasia = $nome_fantasia;
    }
    public function getCnpjCliente()
    {
        return $this->cnpj_cliente;
    }
    public function setCnpjCliente($cnpj_cliente)
    {
        $this->cnpj_cliente = $cnpj_cliente;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getLimiteCredito()
    {
        return $this->limite_credito;
    }
    public function setLimiteCredito($limite_credito)
    {
        $this->limite_credito = $limite_credito;
    }

    // metodo de cadastra cliente
    public function cadastrarCliente(
        $nome_representante,
        $razao_social,
        $nome_fantasia,
        $cnpj_cliente,
        $email,
        $limite_credito,
        $telefones,              // array de telefones [['tipo'=>'celular','numero'=>'...'], ...]
        $inscricao_estadual,
        $cidade,
        $estado,
        $bairro,
        $cep,
        $complemento
    ) {
        // settar os atributos
        $this->setNomeRepresentante($nome_representante);
        $this->setRazaoSocial($razao_social);
        $this->setNomeFantasia($nome_fantasia);
        $this->setCnpjCliente($cnpj_cliente);
        $this->setEmail($email);
        $this->setLimiteCredito($limite_credito);

        try {
            // iniciar a transação
            $this->pdo->beginTransaction();

            // --- Inserir cliente ---
            $sql = "INSERT INTO cliente (nome_representante, razao_social, nome_fantasia, cnpj_cliente, email, limite_credito) 
                VALUES (:nome_representante, :razao_social, :nome_fantasia, :cnpj_cliente, :email, :limite_credito);";
            $query = $this->pdo->prepare($sql);
            $query->bindValue(":nome_representante", $this->getNomeRepresentante(), PDO::PARAM_STR);
            $query->bindValue(":razao_social", $this->getRazaoSocial(), PDO::PARAM_STR);
            $query->bindValue(":nome_fantasia", $this->getNomeFantasia(), PDO::PARAM_STR);
            $query->bindValue(":cnpj_cliente", $this->getCnpjCliente(), PDO::PARAM_STR);
            $query->bindValue(":email", $this->getEmail(), PDO::PARAM_STR);
            $query->bindValue(":limite_credito", $this->getLimiteCredito(), PDO::PARAM_STR);
            $query->execute();

            $id_cliente = $this->pdo->lastInsertId();

            // --- Inserir telefones ---
            if (!empty($telefones)) {
                $sqlTelefone = "INSERT INTO telefone_cliente (id_cliente, tipo, numero)
                            VALUES (:id_cliente, :tipo, :numero)";
                $stmtTel = $this->pdo->prepare($sqlTelefone);
                // Array de numeros que ja foram inseridos
                $numerosInseridos = [];
                foreach ($telefones as $tel) {
                    $tipo   = $tel['tipo'];
                    $numero = preg_replace('/\D/', '', $tel['numero']);

                    if ($tipo && $numero) {
                        // Normaliza o número para comparação
                        $numeroNormalizado = preg_replace('/\D/', '', $numero);
                        // Valida duplicidade
                        if (in_array($numeroNormalizado, $numerosInseridos)) {
                            // Ignora número duplicado ou pode lançar exceção
                            continue; // pula este número
                        }
                        // Executa inserção
                        $stmtTel->bindValue(":id_cliente", $id_cliente, PDO::PARAM_INT);
                        $stmtTel->bindValue(":tipo", $tipo, PDO::PARAM_STR);
                        $stmtTel->bindValue(":numero", $numero, PDO::PARAM_STR);
                        $stmtTel->execute();
                        // Registra o número inserido
                        $numerosInseridos[] = $numeroNormalizado;
                    }
                }
            }
            // --- Inserir inscrição estadual ---
            $sqlInscricao = "INSERT INTO inscricao_estadual (inscricao_estadual, id_cliente)
                        VALUES (:inscricao_estadual, :id_cliente)";
            $query = $this->pdo->prepare($sqlInscricao);
            $query->bindValue(":inscricao_estadual", $inscricao_estadual, PDO::PARAM_STR);
            $query->bindValue(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $query->execute();

            // --- Inserir endereço ---
            $sqlEndereco = "INSERT INTO endereco (cidade, estado, bairro, cep, complemento, id_cliente)
                        VALUES (:cidade, :estado, :bairro, :cep, :complemento, :id_cliente)";
            $query = $this->pdo->prepare($sqlEndereco);
            $query->bindValue(":cidade", $cidade, PDO::PARAM_STR);
            $query->bindValue(":estado", $estado, PDO::PARAM_STR);
            $query->bindValue(":bairro", $bairro, PDO::PARAM_STR);
            $query->bindValue(":cep", $cep, PDO::PARAM_STR);
            $query->bindValue(":complemento", $complemento, PDO::PARAM_STR);
            $query->bindValue(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $query->execute();

            // --- Commit da transação ---
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erro ao cadastrar cliente: " . $e->getMessage());
            return false;
        }
    }

    // método de consultar cliente
    public function consultarCliente($nome_fantasia, $razao_social, $cnpj_cliente)
    {
        // settar os atributos
        $this->setNomeFantasia($nome_fantasia);
        $this->setRazaoSocial($razao_social);
        $this->setCnpjCliente($cnpj_cliente);

        // Query base com GROUP_CONCAT para agrupar telefones
        $sql = "SELECT 
                cl.id_cliente,
                cl.nome_representante,
                cl.razao_social,
                cl.nome_fantasia,
                cl.cnpj_cliente,
                cl.email,
                cl.limite_credito,
                ie.inscricao_estadual,
                e.cidade,
                e.estado,
                e.bairro,
                e.cep,
                e.complemento,
                GROUP_CONCAT(
                    CONCAT(tc.tipo, ': ', tc.numero) 
                    ORDER BY tc.id_telefone SEPARATOR ', ') AS telefones
            FROM cliente AS cl
            LEFT JOIN inscricao_estadual AS ie ON cl.id_cliente = ie.id_cliente
            LEFT JOIN endereco AS e ON cl.id_cliente = e.id_cliente
            LEFT JOIN telefone_cliente AS tc ON cl.id_cliente = tc.id_cliente";

        // Condições dinâmicas
        $condicoes = [];
        if (!empty($this->getNomeFantasia())) {
            $condicoes[] = "cl.nome_fantasia LIKE :nome_fantasia";
        }
        if (!empty($this->getRazaoSocial())) {
            $condicoes[] = "cl.razao_social LIKE :razao_social";
        }
        if (!empty($this->getCnpjCliente())) {
            $condicoes[] = "cl.cnpj_cliente LIKE :cnpj_cliente";
        }

        if ($condicoes) {
            $sql .= " WHERE " . implode(" AND ", $condicoes);
        }

        // Agrupamento para o GROUP_CONCAT
        $sql .= " GROUP BY
                cl.id_cliente,
                cl.nome_representante,
                cl.razao_social,
                cl.nome_fantasia,
                cl.cnpj_cliente,
                cl.email,
                cl.limite_credito,
                ie.inscricao_estadual,
                e.cidade,
                e.estado,
                e.bairro,
                e.cep,
                e.complemento
                ORDER BY cl.nome_fantasia ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Bind dinâmico
            if (!empty($this->getNomeFantasia())) {
                $query->bindValue(":nome_fantasia", "%" . $this->getNomeFantasia() . "%", PDO::PARAM_STR);
            }
            if (!empty($this->getRazaoSocial())) {
                $query->bindValue(":razao_social", "%" . $this->getRazaoSocial() . "%", PDO::PARAM_STR);
            }
            if (!empty($this->getCnpjCliente())) {
                $query->bindValue(":cnpj_cliente", "%" . $this->getCnpjCliente() . "%", PDO::PARAM_STR);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao consultar clientes: " . $e->getMessage());
            return false;
        }
    }

    // metodo de alterar cliente
    public function alterarCliente(
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
        // settar os atributos
        $this->setIdCliente($id_cliente);
        $this->setNomeRepresentante($nome_representante);
        $this->setRazaoSocial($razao_social);
        $this->setNomeFantasia($nome_fantasia);
        $this->setCnpjCliente($cnpj_cliente);
        $this->setEmail($email);
        $this->setLimiteCredito($limite_credito);

        try {
            // iniciar a transação
            $this->pdo->beginTransaction();
            // query para atualizar o cliente
            $sql = "UPDATE cliente SET nome_representante = :nome_representante, razao_social = :razao_social,
                    nome_fantasia = :nome_fantasia, cnpj_cliente = :cnpj_cliente, email = :email, limite_credito = :limite_credito
                    WHERE id_cliente = :id_cliente";
            // preparar a query e blindar os parâmetros
            $query = $this->pdo->prepare($sql);
            $query->bindValue(":id_cliente", $this->getIdCliente(), PDO::PARAM_INT);
            $query->bindValue(":nome_representante", $this->getNomeRepresentante(), PDO::PARAM_STR);
            $query->bindValue(":razao_social", $this->getRazaoSocial(), PDO::PARAM_STR);
            $query->bindValue(":nome_fantasia", $this->getNomeFantasia(), PDO::PARAM_STR);
            $query->bindValue(":cnpj_cliente", $this->getCnpjCliente(), PDO::PARAM_STR);
            $query->bindValue(":email", $this->getEmail(), PDO::PARAM_STR);
            $query->bindValue(":limite_credito", $this->getLimiteCredito(), PDO::PARAM_STR);
            // Executa o cadastro do cliente
            $query->execute();
            // atualizar na tabela telefone_cliente
            $sqlTelefone = "UPDATE telefone_cliente SET telefone_celular = :telefone_celular,
                            telefone_fixo = :telefone_fixo WHERE id_cliente = :id_cliente;";
            // preparar a query e blindar os parâmetros da tabela telefone_cliente
            $query = $this->pdo->prepare($sqlTelefone);
            $query->bindValue(":telefone_celular", $telefone_celular, PDO::PARAM_STR);
            $query->bindValue(":telefone_fixo", $telefone_fixo, PDO::PARAM_STR);
            $query->bindValue(":id_cliente", $id_cliente, PDO::PARAM_INT);
            // Executa o cadastro do telefone_cliente
            $query->execute();
            // atualizar na tabela inscricao_estadual
            $sqlInscricao = "UPDATE inscricao_estadual SET inscricao_estadual = :inscricao_estadual
                                WHERE id_cliente = :id_cliente;";
            // preparar a query e blindar os parâmetros da tabela inscricao_estadual
            $query = $this->pdo->prepare($sqlInscricao);
            $query->bindValue(":inscricao_estadual", $inscricao_estadual, PDO::PARAM_STR);
            $query->bindValue(":id_cliente", $id_cliente, PDO::PARAM_INT);
            // Executa o cadastro da inscricao_estadual
            $query->execute();
            // atualizar na tabela endereco
            $sqlEndereco = "UPDATE endereco SET cidade = :cidade, estado = :estado, bairro = :bairro,
                            cep = :cep, complemento = :complemento WHERE id_cliente = :id_cliente;";
            // preparar a query e blindar os parâmetros da tabela endereco
            $query = $this->pdo->prepare($sqlEndereco);
            $query->bindValue(":cidade", $cidade, PDO::PARAM_STR);
            $query->bindValue(":estado", $estado, PDO::PARAM_STR);
            $query->bindValue(":bairro", $bairro, PDO::PARAM_STR);
            $query->bindValue(":cep", $cep, PDO::PARAM_STR);
            $query->bindValue(":complemento", $complemento, PDO::PARAM_STR);
            $query->bindValue(":id_cliente", $id_cliente, PDO::PARAM_INT);
            // Executa o cadastro do endereco
            $query->execute();
            // commit da transação
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // rollback da transação em caso de erro
            $this->pdo->rollBack();
            error_log("Erro ao alterar cliente: " . $e->getMessage());
            return false;
        }
    }
    // metodo de excluir cliente
    public function excluirCliente($id_cliente)
    {
        // settar o atributo
        $this->setIdCliente($id_cliente);
        // criar a query base
        $sql = "DELETE FROM cliente WHERE id_cliente = :id_cliente;";
        // Executar a consulta
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            // Faz o bind dos parâmetros individualmente
            $query->bindValue(":id_cliente", $this->getIdCliente(), PDO::PARAM_INT);
            // Executa a consulta
            return $query->execute();
        } catch (PDOException $e) {
            error_log("Erro ao excluir cliente: " . $e->getMessage());
            return false;
        }
    }
    public function consultarClientePedido($cliente)
    {
        $sql = "SELECT id_cliente, razao_social, nome_fantasia, cnpj_cliente
                FROM cliente
                WHERE razao_social LIKE :cliente
                OR nome_fantasia LIKE :cliente
                OR cnpj_cliente LIKE :cliente";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':cliente', "%" . $cliente . "%", PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }


    public function verificar_LimiteCredito($id_cliente, $valor_totalPedido)
    {
        // settar o atributo
        $this->setIdCliente($id_cliente);
        try {
            $sql = "SELECT limite_credito
                    FROM cliente
                    WHERE id_cliente = :id_cliente";
            $query = $this->pdo->prepare($sql);
            $query->bindValue(":id_cliente", $this->getIdCliente(), PDO::PARAM_INT);
            $query->execute();
            $limite = $query->fetchColumn();
            if ($valor_totalPedido > $limite) {
                return [
                    "status" => false,
                    "mensagem" => "Limite de crédito excedido.",
                    "limite_credito" => $limite,
                    "valor_total" => $valor_totalPedido,
                    "excedente" => $valor_totalPedido - $limite
                ];
            }
        } catch (PDOException $e) {
            error_log("Erro ao verificar limite de crédito: " . $e->getMessage());
            return [
                "status" => "erro",
                "mensagem" => "Erro no banco de dados."
            ];
        }
    }

    //  verificar se o cliente existe
    public function verificarCliente($cnpj_cliente)
    {
        // settar o atributo
        $this->setCnpjCliente($cnpj_cliente);
        $sql = "SELECT * FROM cliente WHERE cnpj_cliente = :cnpj_cliente";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':cnpj_cliente', $this->getCnpjCliente(), PDO::PARAM_STR);
            $query->execute();
            // Retorna o resultado
            $resultado = $query->fetch(PDO::FETCH_OBJ);
            // Captura o resultado da consulta e atribui a variável (quantidade)
            $quantidade = $resultado->quantidade;
            // Verifica se existe pelo menos um registro no banco
            // Se a quantidade for igual a 1, retorna true, caso contrário, retorna false
            if ($quantidade == 1) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }
    //metodo de consultar se um cliente tem algum pedido cadastrado
    public function clienteEmAlgumPedido($id_cliente)
    {
        // settar o atributo
        $this->setIdCliente($id_cliente);

        $sql = "SELECT COUNT(*) as total
                FROM pedido
                WHERE id_cliente = :id_cliente";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':id_cliente', $this->getIdCliente(), PDO::PARAM_INT);
            $query->execute();
            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            if ($resultado && $resultado['total'] > 0) {
                // Cliente está vinculado a pelo menos um pedido
                return true;
            } else {
                // Cliente não está em nenhum pedido
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }
}
