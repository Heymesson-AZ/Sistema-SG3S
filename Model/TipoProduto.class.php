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

    private function verificarUsoTipoProduto($id_tipo_produto)
    {
        // AÇÃO NECESSÁRIA: Confirme se a tabela 'produto' e a coluna 'id_tipo_produto'
        // estão com os nomes corretos de acordo com seu banco de dados.
        $sql = "SELECT COUNT(*) FROM produto WHERE id_tipo_produto = :id_tipo_produto";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':id_tipo_produto', $id_tipo_produto, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchColumn(); // Retorna a contagem (0 ou mais)
        } catch (PDOException $e) {
            print "Erro ao verificar uso do tipo de produto: " . $e->getMessage();
            return false;
        }
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
    // Método para excluir tipo de produto (COM VERIFICAÇÃO)
    public function excluirTipo($id_tipo_produto)
    {
        $this->setIdTipoProduto($id_tipo_produto);

        try {
            // 1. Verificar se o tipo de produto está em uso
            $emUso = $this->verificarUsoTipoProduto($this->getIdTipoProduto());

            // Se a contagem for maior que 0, o tipo está em uso e não pode ser excluído.
            if ($emUso > 0) {
                return false; // Retorna false para indicar falha na exclusão
            }

            // 2. Se não estiver em uso, prosseguir com a exclusão
            $sql = "DELETE FROM tipo_produto WHERE id_tipo_produto = :id_tipo_produto";
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
    // Método para alterar tipo de produto
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
    // Método para consultar tipos de produto
    public function consultarTipo($nome_tipo = null)
    {
        // É uma boa prática listar as colunas em vez de usar SELECT *
        $sql = "SELECT id_tipo_produto, nome_tipo FROM tipo_produto WHERE 1=1";

        // Adiciona o filtro de busca apenas se a variável não for nula ou vazia
        if ($nome_tipo !== null && !empty($nome_tipo)) {
            $sql .= " AND nome_tipo LIKE :nome_tipo";
        }

        // Altera a ordenação para o campo do nome
        $sql .= " ORDER BY nome_tipo ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Faz o bind do parâmetro dentro da mesma condição
            if ($nome_tipo !== null && !empty($nome_tipo)) {
                $termo_busca = "%" . $nome_tipo . "%";
                $query->bindParam(':nome_tipo', $termo_busca, PDO::PARAM_STR);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            print "Erro ao consultar tipo: " . $e->getMessage();
            return false;
        }
    }
}
