<?php
//Classe Cliente
//incluir classe conexao
include_once 'Conexao.class.php';

class FormaPagamento extends Conexao
{
    // atributos
    private $id_forma_pagamento;
    private $descricao;

    // metodos getters e setters
    public function getIdFormaPagamento()
    {
        return $this->id_forma_pagamento;
    }
    public function setIdFormaPagamento($id_forma_pagamento)
    {
        $this->id_forma_pagamento = $id_forma_pagamento;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    // metodo cadastrar forma de pagamento
    public function cadastrarFormaPagamento($descricao)
    {
        // settar atributos
        $this->setDescricao($descricao);

        try {
            //blidagem dos dados
            // sql para inserir no banco de dados
            $sql = "INSERT INTO forma_pagamento (descricao) VALUES (:descricao)";
            // preparar a query e blindar os parâmetros da tabela forma de pagamento
            //conectar com o banco
            $bd = $this->conectarBanco();
            //preparar o sql
            $query = $bd->prepare($sql);
            // bind dos parâmetros
            $query->bindValue(":descricao", $this->getDescricao(), PDO::PARAM_STR);
            // executar a query
            $query->execute();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao cadastrar forma de pagamento: " . $e->getMessage());
            return false;
        }
    }
    // metodo de alterar forma de pagamento
    public function alterarFormaPagamento($id_forma_pagamento, $descricao)
    {
        // settar atributos
        $this->setIdFormaPagamento($id_forma_pagamento);
        $this->setDescricao($descricao);

        try {
            // sql para atualizar no banco de dados
            $sql = "UPDATE forma_pagamento SET descricao = :descricao WHERE id_forma_pagamento = :id_forma_pagamento";
            // preparar a query e blindar os parâmetros da tabela forma de pagamento
            //conectar com o banco
            $bd = $this->conectarBanco();
            //preparar o sql
            $query = $bd->prepare($sql);
            // bind dos parâmetros
            $query->bindValue(":id_forma_pagamento", $this->getIdFormaPagamento(), PDO::PARAM_INT);
            $query->bindValue(":descricao", $this->getDescricao(), PDO::PARAM_STR);
            // executar a query
            $query->execute();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao alterar forma de pagamento: " . $e->getMessage());
            return false;
        }
    }
    // metodo de excluir forma de pagamento
    public function excluirFormaPagamento($id_forma_pagamento)
    {
        // settar atributos
        $this->setIdFormaPagamento($id_forma_pagamento);

        try {
            // sql para excluir no banco de dados
            $sql = "DELETE FROM forma_pagamento WHERE id_forma_pagamento = :id_forma_pagamento";
            // preparar a query e blindar os parâmetros da tabela forma de pagamento
            //conectar com o banco
            $bd = $this->conectarBanco();
            //preparar o sql
            $query = $bd->prepare($sql);
            // bind dos parâmetros
            $query->bindValue(":id_forma_pagamento", $this->getIdFormaPagamento(), PDO::PARAM_INT);
            // executar a query
            $query->execute();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao excluir forma de pagamento: " . $e->getMessage());
            return false;
        }
    }
    // metodo de cosultar forma de pagamento
    public function consultarFormaPagamento($descricao)
    {
        // Armazena o valor recebido no atributo da classe
        $this->setDescricao($descricao);

        // SQL base
        $sql = "SELECT * FROM forma_pagamento";

        // Se a descrição não estiver vazia, adiciona condição
        if (!empty($this->getDescricao())) {
            $sql .= " WHERE descricao LIKE :descricao";
        }

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Se houver descrição, faz o bind com o parâmetro LIKE
            if (!empty($this->getDescricao())) {
                $query->bindValue(":descricao", "%" . $this->getDescricao() . "%", PDO::PARAM_STR);
            }
            $query->execute();
            // Retorna todos os resultados encontrados
            $resultados = $query->fetchAll(PDO::FETCH_OBJ);
            return $resultados;
        } catch (Exception $e) {
            error_log("Erro ao consultar forma de pagamento: " . $e->getMessage());
            return false;
        }
    }
}
