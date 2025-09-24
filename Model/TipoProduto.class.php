<?php
// Incluindo classe de conexão
include_once 'Conexao.class.php';

// Classe TipoProduto
class TipoProduto extends Conexao
{
    private $id_tipo_produto = null;
    private $nome_tipo = null;

    // Getters e Setters
    public function getIdTipoProduto()
    {
        return $this->id_tipo_produto;
    }
    public function setIdTipoProduto($id_tipo_produto)
    {
        $this->id_tipo_produto = $id_tipo_produto;
    }
    public function getNomeTipo()
    {
        return $this->nome_tipo;
    }
    public function setNomeTipo($nome_tipo)
    {
        $this->nome_tipo = $nome_tipo;
    }

    // Método para cadastrar tipo de produto
    public function cadastrarTipo($nome_tipo)
    {
        $this->setNomeTipo($nome_tipo);
        $sql = "INSERT INTO tipo_produto (nome_tipo) VALUES (:nome_tipo)";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':nome_tipo', $this->getNomeTipo(), PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            print "Erro ao cadastrar tipo: " . $e->getMessage();
            return false;
        }
    }

    // Método para excluir tipo
    public function excluirTipo($id_tipo_produto)
    {
        $this->setIdTipoProduto($id_tipo_produto);
        $sql = "DELETE FROM tipo_produto WHERE id_tipo_produto = :id_tipo_produto";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':id_tipo_produto', $this->getIdTipoProduto(), PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            print "Erro ao excluir tipo: " . $e->getMessage();
            return false;
        }
    }

    // Método para alterar tipo
    public function alterarTipo($id_tipo_produto, $nome_tipo)
    {
        $this->setIdTipoProduto($id_tipo_produto);
        $this->setNomeTipo($nome_tipo);
        $sql = "UPDATE tipo_produto SET nome_tipo = :nome_tipo WHERE id_tipo_produto = :id_tipo_produto";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':id_tipo_produto', $this->getIdTipoProduto(), PDO::PARAM_INT);
            $query->bindParam(':nome_tipo', $this->getNomeTipo(), PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            print "Erro ao alterar tipo: " . $e->getMessage();
            return false;
        }
    }

    // Método para consultar tipos
    public function consultarTipo($nome_tipo = null)
    {
        $this->setNomeTipo($nome_tipo);
        $sql = "SELECT * FROM tipo_produto WHERE 1=1";
        if ($nome_tipo !== null) {
            $sql .= " AND nome_tipo LIKE :nome_tipo";
        }
        $sql .= " ORDER BY id_tipo_produto ASC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            if ($nome_tipo !== null) {
                $nome_tipo = "%" . $nome_tipo . "%";
                $query->bindParam(':nome_tipo', $nome_tipo, PDO::PARAM_STR);
            }
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            print "Erro ao consultar tipo: " . $e->getMessage();
            return false;
        }
    }
}
?>