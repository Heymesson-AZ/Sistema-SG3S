<?php
// Incluindo classe de conexao
include_once 'Conexao.class.php';

// Classe Usuario
class Perfil extends Conexao
{
    private $id_perfil = null;
    private $perfil_usuario = null;

    // getters e setters
    public function getIdPerfil()
    {
        return $this->id_perfil;
    }
    public function setIdPerfil($id_perfil)
    {
        $this->id_perfil = $id_perfil;
    }
    public function getPerfilUsuario()
    {
        return $this->perfil_usuario;
    }
    public function setPerfilUsuario($perfil_usuario)
    {
        $this->perfil_usuario = $perfil_usuario;
    }
    // Método para cadastrar um novo Perfil
    public function cadastrarPerfil($perfil_usuario)
    {
        // Atributos para
        $this->setPerfilUsuario($perfil_usuario);
        // Montar query
        $sql = "INSERT INTO perfil_usuario (perfil_usuario) VALUES (:perfil_usuario)";
        // Executar a query
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o sql
            $query = $bd->prepare($sql);
            // Bindar valores
            $query->bindParam(':perfil_usuario', $this->getPerfilUsuario(), PDO::PARAM_STR);
            // Executar query
            $query->execute();
            // Retornar true se cadastrado com sucesso
            return true;
        } catch (PDOException $e) {
            // Caso de erro, printar o erro
            print "Erro ao cadastrar: " . $e->getMessage();
            // Retornar false
            return false;
        }
    }
    // Método para excluir um Perfil
    public function excluirPerfil($id_perfil)
    {
        // Atributos para
        $this->setIdPerfil($id_perfil);
        // Montar query
        $sql = "DELETE FROM perfil_usuario WHERE id_perfil = :id_perfil";
        // Executar a query
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o sql
            $query = $bd->prepare($sql);
            // Bindar valores
            $query->bindParam(':id_perfil', $this->getIdPerfil(), PDO::PARAM_INT);
            // Executar query
            $query->execute();
            // Retornar true se excluído com sucesso
            return true;
        } catch (PDOException $e) {
            // Retornar false
            return false;
        }
    }
    // Método para atualizar um Perfil
    public function alterarPerfil($id_perfil, $perfil_usuario)
    {
        // Atributos para
        $this->setIdPerfil($id_perfil);
        $this->setPerfilUsuario($perfil_usuario);
        // Montar query
        $sql = "UPDATE perfil_usuario SET perfil_usuario = :perfil_usuario WHERE perfil_usuario.id_perfil = :id_perfil";
        // Executar a query
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o sql
            $query = $bd->prepare($sql);
            // Bindar valores
            $query->bindParam(':id_perfil', $this->getIdPerfil(), PDO::PARAM_INT);
            $query->bindParam(':perfil_usuario', $this->getPerfilUsuario(), PDO::PARAM_STR);
            // Executar query
            $query->execute();
            // Retornar true se atualizado com sucesso
            return true;
        } catch (PDOException $e) {
            // Caso de erro, printar o erro
            print "Erro ao atualizar: " . $e->getMessage();
            // Retornar false
            return false;
        }
    }
    // Método para consultara perfis
    public function consultarPerfil($perfil_usuario)
    {
        // Setar o atributo
        $this->setPerfilUsuario($perfil_usuario);
        // Montar query inicial
        $sql = "SELECT * FROM perfil_usuario WHERE 1=1";
        // Filtro
        if ($perfil_usuario !== null) {
            $sql .= " AND perfil_usuario LIKE :perfil_usuario";
        }
        // ordenar
        $sql.= " ORDER BY id_perfil ASC";
        try {
            // Conectar ao banco
            $bd = $this->conectarBanco();
            // Preparar a query
            $query = $bd->prepare($sql);
            // Bindar valores
            if ($perfil_usuario !== null) {
                $perfil_usuario = "%" . $perfil_usuario . "%";
                $query->bindParam(':perfil_usuario', $perfil_usuario, PDO::PARAM_STR);
            }
            // Executar query
            $query->execute();
            // Retornar resultados
            $resultado = $query->fetchAll(PDO::FETCH_OBJ);
            return $resultado;
        } catch (PDOException $e) {
            // Em caso de erro
            print "Erro ao consultar: " . $e->getMessage();
            return false;
        }
    }
}
