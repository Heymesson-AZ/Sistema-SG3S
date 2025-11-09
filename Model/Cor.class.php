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
    private function verificarUsoCor($id_cor)
    {
        $sql = "SELECT COUNT(*) FROM produto WHERE id_cor = :id_cor";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':id_cor', $id_cor, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchColumn(); // Retorna a contagem
        } catch (PDOException $e) {
            print "Erro ao verificar uso da cor: " . $e->getMessage();
            return false;
        }
    }
    // Método para excluir cor
    public function excluirCor($id_cor)
    {
        $this->setIdCor($id_cor);

        try {
            // 1. Verificar se a cor está em uso
            $emUso = $this->verificarUsoCor($this->getIdCor());

            if ($emUso > 0) {
                return [
                    'sucesso' => false,
                    'mensagem' => "Não é possível excluir esta cor, pois existem produtos associados a ela."
                ];
            }

            // 2. Se não estiver em uso, prosseguir com a exclusão
            $sql = "DELETE FROM cor WHERE id_cor = :id_cor";
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':id_cor', $this->getIdCor(), PDO::PARAM_INT);
            $query->execute();
            return [
                'sucesso' => true,
                'mensagem' => "Cor excluída com sucesso."
            ];
        } catch (PDOException $e) {
            // Em caso de erro na consulta ou exclusão
            return [
                'sucesso' => false,
                'mensagem' => "Erro ao excluir cor: " . $e->getMessage()
            ];
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
        $sql = "SELECT * FROM cor WHERE 1=1";

        if ($nome_cor !== null) {
            $sql .= " AND nome_cor LIKE :nome_cor";
        }

        $sql .= " ORDER BY nome_cor ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            if ($nome_cor !== null) {
                $nomeCor = "%" . $nome_cor . "%";
                $query->bindParam(':nome_cor', $nomeCor, PDO::PARAM_STR);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            print "Erro ao consultar cor: " . $e->getMessage();
            return false;
        }
    }
}
