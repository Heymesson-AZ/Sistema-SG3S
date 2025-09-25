<?php
// Incluindo classe de conexão
include_once 'Conexao.class.php';

// Classe Cor
class Cor extends Conexao
{
    private $id_cor = null;
    private $nome_cor = null;

    // Getters e Setters
    public function getIdCor()
    {
        return $this->id_cor;
    }
    public function setIdCor($id_cor)
    {
        $this->id_cor = $id_cor;
    }
    public function getNomeCor()
    {
        return $this->nome_cor;
    }
    public function setNomeCor($nome_cor)
    {
        $this->nome_cor = $nome_cor;
    }

    // Método para cadastrar cor
    public function cadastrarCor($nome_cor)
    {
        $this->setNomeCor($nome_cor);
        $sql = "INSERT INTO cor (nome_cor) VALUES (:nome_cor)";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':nome_cor', $this->getNomeCor(), PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            print "Erro ao cadastrar cor: " . $e->getMessage();
            return false;
        }
    }
    // Método para excluir cor
    public function excluirCor($id_cor)
    {
        $this->setIdCor($id_cor);
        $sql = "DELETE FROM cor WHERE id_cor = :id_cor";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':id_cor', $this->getIdCor(), PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            print "Erro ao excluir cor: " . $e->getMessage();
            return false;
        }
    }
    // Método para alterar cor
    public function alterarCor($id_cor, $nome_cor)
    {
        $this->setIdCor($id_cor);
        $this->setNomeCor($nome_cor);
        $sql = "UPDATE cor SET nome_cor = :nome_cor WHERE id_cor = :id_cor";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':id_cor', $this->getIdCor(), PDO::PARAM_INT);
            $query->bindParam(':nome_cor', $this->getNomeCor(), PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            print "Erro ao alterar cor: " . $e->getMessage();
            return false;
        }
    }
    // Método para consultar cores
    public function consultarCor($nome_cor = null)
    {
        $this->setNomeCor($nome_cor);
        $sql = "SELECT id_cor, nome_cor FROM cor WHERE 1=1";
        if ($nome_cor !== null) {
            $sql .= " AND nome_cor LIKE :nome_cor";
        }
        $sql .= " ORDER BY id_cor ASC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            if ($nome_cor !== null) {
                $nome_cor = "%" . $nome_cor . "%";
                $query->bindParam(':nome_cor', $nome_cor, PDO::PARAM_STR);
            }
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            print "Erro ao consultar cor: " . $e->getMessage();
            return false;
        }
    }
}
