<?php
// classe Fornecedor

class Fornecedor extends Conexao
{
    // atributos
    private $id_fornecedor = null;
    private $razao_social_fornecedor = null;
    private $cnpj_fornecedor = null;
    private $email_fornecedor = null;
    private $pdo;

    public function __construct()
    {
        $this->pdo = parent::conectarBanco();
    }

    // métodos getters e setters
    public function getIdFornecedor()
    {
        return $this->id_fornecedor;
    }
    public function setIdFornecedor($id_fornecedor)
    {
        $this->id_fornecedor = $id_fornecedor;
    }
    public function getRazaoSocialFornecedor()
    {
        return $this->razao_social_fornecedor;
    }
    public function setRazaoSocialFornecedor($razao_social_fornecedor)
    {
        $this->razao_social_fornecedor = $razao_social_fornecedor;
    }
    public function getCnpjFornecedor()
    {
        return $this->cnpj_fornecedor;
    }
    public function setCnpjFornecedor($cnpj_fornecedor)
    {
        $this->cnpj_fornecedor = $cnpj_fornecedor;
    }
    public function getEmailFornecedor()
    {
        return $this->email_fornecedor;
    }
    public function setEmailFornecedor($email_fornecedor)
    {
        $this->email_fornecedor = $email_fornecedor;
    }

    // Cadastrar fornecedor
    public function cadastrarFornecedor($razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo)
    {
        // settar os atributos
        $this->setRazaoSocialFornecedor($razao_social);
        $this->setCnpjFornecedor($cnpj);
        $this->setEmailFornecedor($email);

        try {
            // iniciar a transação
            $this->pdo->beginTransaction();

            // query para inserir o fornecedor
            $sql = "INSERT INTO fornecedor (razao_social, cnpj_fornecedor, email) 
                VALUES (:razao_social, :cnpj_fornecedor, :email)";
            $query = $this->pdo->prepare($sql);
            $query->bindValue(':razao_social', $this->getRazaoSocialFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':cnpj_fornecedor', $this->getCnpjFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':email', $this->getEmailFornecedor(), PDO::PARAM_STR);
            $query->execute();

            // pegar o id do fornecedor criado
            $idFornecedor = $this->pdo->lastInsertId();

            // inserir na tabela telefone
            $sqlTelefone = "INSERT INTO telefone_fornecedor (id_fornecedor, telefone_celular, telefone_fixo) 
                        VALUES (:id_fornecedor, :telefone_celular, :telefone_fixo)";
            $query = $this->pdo->prepare($sqlTelefone);
            $query->bindValue(':id_fornecedor', $idFornecedor, PDO::PARAM_INT);
            $query->bindValue(':telefone_celular', $telefone_celular, PDO::PARAM_STR);
            $query->bindValue(':telefone_fixo', $telefone_fixo, PDO::PARAM_STR);
            $query->execute();

            // commit da transação
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // rollback em caso de erro
            $this->pdo->rollBack();
            print "Erro: " . $e->getMessage();
            return false;
        }
    }
    //Alterar fornecedor
    public function alterarFornecedor($id_fornecedor, $razao_social, $cnpj, $email, $telefone_celular, $telefone_fixo)
    {
        // settar os atributos
        $this->setRazaoSocialFornecedor($razao_social);
        $this->setCnpjFornecedor($cnpj);
        $this->setEmailFornecedor($email);
        try {
            // iniciar a transacao
            $this->pdo->beginTransaction();
            // conectar com o banco
            $bd = $this->conectarBanco();

            // atualizar dados na tabela fornecedor
            $sql = "UPDATE fornecedor 
                SET razao_social = :razao_social, 
                cnpj_fornecedor = :cnpj_fornecedor, 
                email = :email 
                WHERE id_fornecedor = :id_fornecedor";

            $query = $bd->prepare($sql);
            $query->bindValue(':razao_social', $this->getRazaoSocialFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':cnpj_fornecedor', $this->getCnpjFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':email', $this->getEmailFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
            $query->execute();
            // atualizar dados na tabela telefone_fornecedor
            $sqlTelefone = "UPDATE telefone_fornecedor 
                        SET telefone_celular = :telefone_celular, 
                        telefone_fixo = :telefone_fixo 
                        WHERE id_fornecedor = :id_fornecedor";
            $query = $bd->prepare($sqlTelefone);
            $query->bindValue(':telefone_celular', $telefone_celular, PDO::PARAM_STR);
            $query->bindValue(':telefone_fixo', $telefone_fixo, PDO::PARAM_STR);
            $query->bindValue(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
            $query->execute();
            // commit
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            print "Erro: " . $e->getMessage();
            return false;
        };
    }
    //Excluir fornecedor
    public function excluirFornecedor($id_fornecedor)
    {
        // query sql para excluir o fornecedor no banco de dados
        $sql = "DELETE FROM fornecedor WHERE id_fornecedor = :id_fornecedor";
        // Tentar executar a query
        try {
            // Conectar ao banco
            $bd = $this->conectarBanco();
            // Preparar a query
            $query = $bd->prepare($sql);
            // Bindar valores
            $query->bindParam(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
            // Executar query
            $query->execute();
            // Retornar resultado
            return true;
        } catch (Exception $e) {
            print "Erro: " . $e->getMessage();
            return false;
        }
    }
    //Consultar fornecedores
    public function consultarFornecedor($razao_social = null)
    {
        // settar os atributos
        $this->setRazaoSocialFornecedor($razao_social);
        // query sql para buscar o fornecedor no banco de dados
        // Iniciar a query
        $sql = "SELECT f.id_fornecedor,f.razao_social,f.cnpj_fornecedor,f.email,
        tf.telefone_celular,tf.telefone_fixo 
        FROM fornecedor as f 
        left join telefone_fornecedor as tf 
        on f.id_fornecedor = tf.id_fornecedor WHERE 1=1 ";

        // Filtrar por razão social
        if ($razao_social !== null) {
            $sql .= " AND razao_social LIKE :razao_social";
        }
        // ordenar
        $sql .= " ORDER BY id_fornecedor ASC";
        // Tentar executar a query
        try {
            // Conectar ao banco
            $bd = $this->conectarBanco();
            // Preparar a query
            $query = $bd->prepare($sql);
            // Bindar valores
            if ($razao_social !== null) {
                $razao_social = "%" . $razao_social . "%";
                $query->bindParam(':razao_social', $razao_social, PDO::PARAM_STR);
            }
            // Executar query
            $query->execute();
            // Retornar resultados
            $fornecedor = $query->fetchAll(PDO::FETCH_OBJ);
            return $fornecedor;
        } catch (PDOException $e) {
            // Em caso de erro
            print "Erro ao consultar: " . $e->getMessage();
            return false;
        }
    }
    // metodo de consultar fornecedor por cnpj
    public function consultarFornecedorCnpj($cnpj_fornecedor)
    {
        // settar os atributos
        $this->setCnpjFornecedor($cnpj_fornecedor);
        // query sql para buscar o fornecedor no banco de dados
        $sql = "SELECT * FROM fornecedor WHERE cnpj_fornecedor = :cnpj_fornecedor";
        try {
            // Conectar ao banco
            $bd = $this->conectarBanco();
            // Preparar a query
            $query = $bd->prepare($sql);
            // Bindar valores
            $query->bindValue(':cnpj_fornecedor', $this->getCnpjFornecedor(), PDO::PARAM_STR);
            // Executar query
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
            // Em caso de erro
            print "Erro ao consultar: " . $e->getMessage();
            return false;
        }
    }
    // metodo consultar fornecedor dinamico
    public function consultarFornecedorDinamico($fornecedor)
    {
        $sql = "SELECT f.id_fornecedor,f.razao_social
            FROM fornecedor as f
            WHERE razao_social LIKE :razao_social";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':razao_social', "%" . $fornecedor . "%", PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);;
        } catch (PDOException $e) {
            return false;
        }
    }
}
