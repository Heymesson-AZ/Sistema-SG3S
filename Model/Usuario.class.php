<?php
// Incluindo classe de conexao
include_once 'Conexao.class.php';
// Classe Usuario
class Usuario extends Conexao
{
    private $id_usuario = null;
    private $nome_usuario = null;
    private $email_usuario = null;
    private $senha = null;
    private $cpf = null;
    private $telefone = null;
    private $id_perfil = null;

    // metodos getters e setters
    public function getIdUsuario()
    {
        return $this->id_usuario;
    }
    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }
    public function getNomeUsuario()
    {
        return $this->nome_usuario;
    }
    public function setNomeUsuario($nome_usuario)
    {
        $this->nome_usuario = $nome_usuario;
    }
    public function getEmailUsuario()
    {
        return $this->email_usuario;
    }
    public function setEmailUsuario($email_usuario)
    {
        $this->email_usuario = $email_usuario;
    }
    public function getSenha()
    {
        return $this->senha;
    }
    public function setSenha($senha)
    {
        $this->senha = $senha;
    }
    public function getCpf()
    {
        return $this->cpf;
    }
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
    }
    public function getTelefone()
    {
        return $this->telefone;
    }
    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }
    public function getIdPerfil()
    {
        return $this->id_perfil;
    }
    public function setIdPerfil($id_perfil)
    {
        $this->id_perfil = $id_perfil;
    }
    // metodo para validar login
    public function validarLogin($cpf, $senha)
    {
        $this->setCpf($cpf);
        $this->setSenha($senha);
        $sql = "SELECT u.id_usuario, u.nome_usuario, u.senha, pu.perfil_usuario
            FROM usuario AS u
            LEFT JOIN perfil_usuario AS pu ON u.id_perfil = pu.id_perfil
            WHERE u.cpf = :cpf";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(':cpf', $this->getCpf(), PDO::PARAM_STR);
            $query->execute();
            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            if (!empty($resultado)) {
                $hash_salvo = $resultado['senha'];
                $nomeUsuario = explode(" ", $resultado['nome_usuario'])[0];
                $perfil_usuario = $resultado['perfil_usuario'];
                $idUsuario = $resultado['id_usuario'];
                if (password_verify($senha, $hash_salvo) && !empty($perfil_usuario)) {
                    return [
                        'validado' => true,
                        'nome'     => $nomeUsuario,
                        'perfil'   => $perfil_usuario,
                        'id_usuario' => $idUsuario
                    ];
                } else {
                    return ['validado' => false];
                }
            } else {
                return ['validado' => false];
            }
        } catch (PDOException $e) {
            error_log("Erro ao validar login: " . $e->getMessage());
            return ['validado' => false];
        }
    }

    // metodo para cadastrar usuario
    public function cadastrarUsuario($nome_usuario, $email_usuario, $senhaHash, $id_perfil, $telefone, $cpf)
    {
        // Setando os atributos
        $this->setNomeUsuario($nome_usuario);
        $this->setEmailUsuario($email_usuario);
        $this->setSenha($senhaHash);
        $this->setCpf($cpf);
        $this->setTelefone($telefone);
        $this->setIdPerfil($id_perfil);

        // Query para inserir usuário
        $sql = "INSERT INTO usuario (nome_usuario, email, senha, cpf, telefone, id_perfil)
                VALUES (:nome_usuario, :email, :senha, :cpf, :telefone, :id_perfil)";

        try {
            //conectar com o banco
            $bd = $this->conectarBanco();
            //preparar o sql
            $query = $bd->prepare($sql);
            //blidagem dos dados

            $query->bindValue(':nome_usuario', $this->getNomeUsuario(), PDO::PARAM_STR);
            $query->bindValue(':email', $this->getEmailUsuario(), PDO::PARAM_STR);
            $query->bindValue(':cpf', $this->getCpf(), PDO::PARAM_STR);
            $query->bindValue(':senha', $this->getSenha(), PDO::PARAM_STR);
            $query->bindValue(':telefone', $this->getTelefone(), PDO::PARAM_STR);
            $query->bindValue(':id_perfil', $this->getIdPerfil(), PDO::PARAM_INT);

            // Executar a query
            $query->execute();
            //retorna true caso tenha sido cadastrado com sucesso
            return true;
        } catch (PDOException $e) {
            //print "Erro ao cadastrar";
            error_log("Erro no banco de dados: " . $e->getMessage());
            return false;
        }
    }
    // metodo para consultar usuario
    public function ConsultarUsuario($nome_usuario, $id_perfil)
    {
        // Settar atributos
        $this->setNomeUsuario($nome_usuario);
        $this->setIdPerfil($id_perfil);
        // Montar a query para consultar um usuário
        $sql = "SELECT u.id_usuario, u.nome_usuario, u.email, u.cpf, u.senha, u.telefone, p.perfil_usuario,p.id_perfil
        FROM Usuario as u
        LEFT JOIN Perfil_Usuario as p ON u.id_perfil = p.id_perfil
        WHERE true";
        // Adicionando condições de filtro
        if (!empty($this->getNomeUsuario())) {
            $sql .= " AND u.nome_usuario LIKE :nome_usuario";
        }
        if (!empty($this->getIdPerfil())) {
            $sql .= " AND p.id_perfil = :id_perfil";
        }
        // Executa a query
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o SQL
            $query = $bd->prepare($sql);
            // Bind dos parâmetros
            if (!empty($this->getNomeUsuario())) {
                $query->bindValue(':nome_usuario', "%" . $this->getNomeUsuario() . "%", PDO::PARAM_STR);
            }
            if (!empty($this->getIdPerfil())) {
                $query->bindValue(':id_perfil', $this->getIdPerfil(), PDO::PARAM_INT);
            }
            // Executar a query
            $query->execute();
            // Retorna o resultado
            $resultado = $query->fetchAll(PDO::FETCH_OBJ);
            return $resultado;
        } catch (PDOException $e) {
            return false;
        }
    }
    // metodo para alterar usuario
    public function alterarUsuario($id_usuario, $nome_usuario, $email,$id_perfil, $cpf, $telefone)
    {
        // Setar os atributos
        $this->setIdUsuario($id_usuario);
        $this->setNomeUsuario($nome_usuario);
        $this->setEmailUsuario($email);
        $this->setIdPerfil($id_perfil);
        $this->setTelefone($telefone);
        $this->setCpf($cpf);
        // Query para alterar usuário
        $sql = "UPDATE usuario
        SET nome_usuario= :nome, email= :email,
        cpf= :cpf, telefone= :telefone,
        id_perfil= :id_perfil
        WHERE usuario.id_usuario = :id_usuario";

        // execução
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o SQL
            $query = $bd->prepare($sql);
            // Bind dos parâmetros
            $query->bindValue(':id_usuario', $this->getIdUsuario(), PDO::PARAM_INT);
            $query->bindValue(':nome', $this->getNomeUsuario(), PDO::PARAM_STR);
            $query->bindValue(':email', $this->getEmailUsuario(), PDO::PARAM_STR);
            $query->bindValue(':senha', $this->getSenha(), PDO::PARAM_STR);
            $query->bindValue(':cpf', $this->getCpf(), PDO::PARAM_STR);
            $query->bindValue(':telefone', $this->getTelefone(), PDO::PARAM_STR);
            $query->bindValue(':id_perfil', $this->getIdPerfil(), PDO::PARAM_INT);
            // executar
            $query->execute();
            // retorno
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao alterar usuário: " . $e->getMessage());
            return false;
        }
    }
    // metodo para excluir usuario
    public function excluirUsuario($id_usuario)
    {
        // settar atributos
        $this->setIdUsuario($id_usuario);
        // Query para excluir usuário
        $sql = "DELETE FROM usuario WHERE id_usuario = :id_usuario";
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o SQL
            $query = $bd->prepare($sql);
            // Bind dos parâmetros
            $query->bindValue(':id_usuario', $this->getIdUsuario(), PDO::PARAM_INT);
            // Executar a query
            $query->execute();
            return true;
        } catch (PDOException $e) {
            print "Erro ao excluir";
            return false;
        }
    }
    // metodo para altera a senha do usuario
    public function alterarSenha($id_usuario, $senha)
    {
        $this->setIdUsuario($id_usuario);
        $this->setSenha($senha);
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            $sql = "UPDATE usuario SET senha = :senha WHERE id_usuario = :id_usuario";
            $query = $bd->prepare($sql);
            $query->bindValue(':id_usuario', $this->getIdUsuario(), PDO::PARAM_INT);
            $query->bindValue(':senha', $senha, PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            print "Erro ao alterar senha: " . $e->getMessage();
            return false;
        }
    }
    // metodo de consultara usuario por cpf
    public function consultarUsuarioCpf($cpf)
    {
        $this->setCpf($cpf);
        // Query para consultar usuário por CPF
        $sql = "SELECT * FROM usuario WHERE cpf = :cpf";
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o SQL
            $query = $bd->prepare($sql);
            // Bind dos parâmetros
            $query->bindValue(':cpf', $this->getCpf(), PDO::PARAM_STR);
            // Executar a query
            $query->execute();
            // Retorna o resultado
            $cpf = $query->fetch(PDO::FETCH_OBJ);
            // cpf do banco de dados
            return $cpf;
        } catch (PDOException $e) {
            return false;
        }
    }
    // metodo para validar o email para recuperar a senha
    public function validarEmail($email)
    {
        $this->setEmailUsuario($email);
        // Query para validar o email
        $sql = "SELECT  count(*) as quantidade FROM usuario WHERE email = :email";
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o SQL
            $query = $bd->prepare($sql);
            // Bind dos parâmetros
            $query->bindValue(':email', $this->getEmailUsuario(), PDO::PARAM_STR);
            // Executar a query
            $query->execute();
            // Retorna o resultado
            $resultado = $query->fetchAll(PDO::FETCH_OBJ);
            // Captura o resultado da consulta e atribui a variável (quantidade)
            foreach ($resultado as $key => $valor) {
                $quantidade = $valor->quantidade;
            }
            // Verifica se existe pelo menos um registro no banco
            // Se a quantidade for igual a 1, retorna true, caso contrário, retorna false
            if ($quantidade == 1) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            //print "Erro ao consultar";
            error_log("Erro ao consultar email: " . $e->getMessage());
            return false;
        }
    }
    // alterar a senha do usuario em recuperacao de senha
    public function alterarSenhaRecuperacao($email, $senha)
    {
        $this->setEmailUsuario($email);
        $this->setSenha($senha);

        // Query para alterar a senha
        $sql = "UPDATE usuario SET senha = :senha WHERE email = :email";
        try {
            // Conectar com o banco
            $bd = $this->conectarBanco();
            // Preparar o SQL
            $query = $bd->prepare($sql);
            // Bind dos parâmetros
            $query->bindValue(':email', $this->getEmailUsuario(), PDO::PARAM_STR);
            $query->bindValue(':senha', password_hash($this->getSenha(), PASSWORD_DEFAULT), PDO::PARAM_STR);
            // Executar a query
            $query->execute();
            return true;
        } catch (PDOException $e) {
            //print "Erro ao alterar senha";
            error_log("Erro ao alterar senha: " . $e->getMessage());
            return false;
        }
    }
}
