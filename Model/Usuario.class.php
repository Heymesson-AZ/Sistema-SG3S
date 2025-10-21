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
            print "Erro ao cadastrar usuário. " . $e->getMessage();
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
        FROM usuario as u
        LEFT JOIN perfil_usuario as p ON u.id_perfil = p.id_perfil
        WHERE true";
        // Adicionando condições de filtro
        if (!empty($this->getNomeUsuario())) {
            $sql .= " AND u.nome_usuario LIKE :nome_usuario";
        }
        if (!empty($this->getIdPerfil())) {
            $sql .= " AND p.id_perfil = :id_perfil ";
        }

        $sql .= " ORDER BY p.id_perfil";

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
    public function alterarUsuario($id_usuario, $nome_usuario, $email, $id_perfil, $cpf, $telefone)
    {
        // 1. Define as propriedades do objeto com os dados recebidos
        $this->setIdUsuario($id_usuario);
        $this->setNomeUsuario($nome_usuario);
        $this->setEmailUsuario($email);
        $this->setIdPerfil($id_perfil); // NOVO perfil (do formulário)
        $this->setTelefone($telefone);
        $this->setCpf($cpf); // NOVO cpf (do formulário)

        try {
            $bd = $this->conectarBanco();

            // IDs para facilitar a leitura
            $id_usuario_logado = $_SESSION['id_usuario'];
            $id_usuario_alvo = $this->getIdUsuario(); // ID do usuário a ser editado

            // --- Definição dos Nomes dos Perfis ---
            $PERFIL_MASTER = 'Administrador Master';
            $PERFIL_ADMIN = 'Administrador';

            // 2. Buscar dados do usuário LOGADO e do ALVO (quem está sendo editado)
            // Precisamos do NOME do perfil, ID do perfil ATUAL e CPF ATUAL do alvo
            $sqlPerfis = "SELECT
                            u.id_usuario,
                            u.id_perfil,
                            u.cpf,
                            p.perfil_usuario
                        FROM usuario u
                        JOIN perfil_usuario p ON u.id_perfil = p.id_perfil
                        WHERE u.id_usuario = :id_usuario_logado OR u.id_usuario = :id_usuario_alvo";

            $qPerfis = $bd->prepare($sqlPerfis);
            $qPerfis->bindValue(':id_usuario_logado', $id_usuario_logado, PDO::PARAM_INT);
            $qPerfis->bindValue(':id_usuario_alvo', $id_usuario_alvo, PDO::PARAM_INT);
            $qPerfis->execute();

            $perfisEncontrados = $qPerfis->fetchAll(PDO::FETCH_ASSOC);

            $perfil_nome_logado = null;
            $perfil_nome_alvo = null;
            $id_perfil_atual_alvo = null; // O ID do perfil que o alvo TEM AGORA
            $cpf_atual_alvo = null;       // O CPF que o alvo TEM AGORA

            foreach ($perfisEncontrados as $p) {
                if ($p['id_usuario'] == $id_usuario_logado) {
                    $perfil_nome_logado = $p['nome_perfil'];
                }
                if ($p['id_usuario'] == $id_usuario_alvo) {
                    $perfil_nome_alvo = $p['nome_perfil'];
                    $id_perfil_atual_alvo = $p['id_perfil'];
                    $cpf_atual_alvo = $p['cpf'];
                }
            }

            if (!$perfil_nome_logado || !$perfil_nome_alvo) {
                return "Erro: Usuário logado ou usuário alvo não encontrado no sistema.";
            }

            // --- 3. Definição de Variáveis de Estado ---
            $eh_auto_edicao = ($id_usuario_logado == $id_usuario_alvo);
            $id_perfil_novo = $this->getIdPerfil();
            $cpf_novo = $this->getCpf();

            $perfil_esta_mudando = ($id_perfil_novo != $id_perfil_atual_alvo);
            $cpf_esta_mudando = ($cpf_novo != $cpf_atual_alvo);


            // --- 4. LÓGICA DE VALIDAÇÃO - REGRA "ÚLTIMO MASTER" (Mais específica) ---
            // Esta regra tem prioridade sobre as outras.
            if ($perfil_nome_alvo == $PERFIL_MASTER) {

                // Vamos contar quantos "Master" existem
                $sqlCount = "SELECT COUNT(u.id_usuario) AS total
                            FROM usuario u
                            JOIN perfil_usuario p ON u.id_perfil = p.id_perfil
                            WHERE p.perfil_usuario = :master";

                $qCount = $bd->prepare($sqlCount);
                $qCount->bindValue(':master', $PERFIL_MASTER, PDO::PARAM_STR);
                $qCount->execute();
                $totalMaster = (int)$qCount->fetch(PDO::FETCH_ASSOC)['total'];

                // Se o alvo é o único Master...
                if ($totalMaster <= 1) {

                    // REGRA 1: "Nao se pode mudar o perfil dele"
                    if ($perfil_esta_mudando) {
                        return "Erro: Não é permitido alterar o perfil do único Administrador Master do sistema.";
                    }

                    // REGRA 2: "bloquear a acao de mudanças de ... cpf" (se for auto-edição)
                    if ($eh_auto_edicao && $cpf_esta_mudando) {
                        return "Erro: O único Administrador Master não pode alterar o próprio CPF.";
                    }
                }
            }

            // --- 5. LÓGICA DE VALIDAÇÃO - PERMISSÕES GERAIS ---

            // Esta variável vai guardar o ID de perfil que DEVE ser salvo no banco.
            // Por padrão, é o novo (vindo do form).
            $id_perfil_a_salvar = $id_perfil_novo;

            // REGRA DE AUTO-EDIÇÃO: "nenhum usuario pode alterar seu proprio perfil"
            if ($eh_auto_edicao) {
                if ($perfil_esta_mudando) {
                    return "Erro: Você não pode alterar seu próprio perfil de usuário.";
                }
                // Se não está mudando o perfil, pode salvar os outros dados.
                // Forçamos o ID de perfil a ser o ID antigo, garantindo que não mude.
                $id_perfil_a_salvar = $id_perfil_atual_alvo;
                // (A lógica do CPF do "Último Master" já foi tratada acima)
            }
            // REGRA DO ADMIN: "O administrador... nao os dados do Master"
            else if ($perfil_nome_logado == $PERFIL_ADMIN && $perfil_nome_alvo == $PERFIL_MASTER) {
                return "Erro: Um Administrador não pode alterar os dados de um Administrador Master.";
            }
            // REGRA DO MASTER: "So o Administrador Master pode alterar qualquero dado"
            else if ($perfil_nome_logado == $PERFIL_MASTER) {
                // Master pode. Segue o fluxo. $id_perfil_a_salvar continua sendo $id_perfil_novo.
            }
            // REGRA DO ADMIN (continuação): Editando outros (que não são Master)
            else if ($perfil_nome_logado == $PERFIL_ADMIN && $perfil_nome_alvo != $PERFIL_MASTER) {
                // Admin pode editar não-Masters. Segue o fluxo.
            }
            // REGRA PADRÃO: Outros usuários não podem editar ninguém
            else {
                return "Erro: Você não tem permissão para alterar os dados de outros usuários.";
            }


            // --- 6. EXECUÇÃO DO UPDATE ---

            $sql = "UPDATE usuario SET
                        nome_usuario = :nome,
                        email = :email,
                        cpf = :cpf,
                        telefone = :telefone,
                        id_perfil = :id_perfil
                        WHERE id_usuario = :id_usuario";

            $q = $bd->prepare($sql);
            $q->bindValue(':id_usuario', $id_usuario_alvo, PDO::PARAM_INT);
            $q->bindValue(':nome', $this->getNomeUsuario(), PDO::PARAM_STR);
            $q->bindValue(':email', $this->getEmailUsuario(), PDO::PARAM_STR);
            $q->bindValue(':cpf', $this->getCpf(), PDO::PARAM_STR);
            $q->bindValue(':telefone', $this->getTelefone(), PDO::PARAM_STR);
            $q->bindValue(':id_perfil', $id_perfil_a_salvar, PDO::PARAM_INT);
            $q->execute();
            return true; // Sucesso
        } catch (PDOException $e) {
            return "Erro ao alterar usuário: " . $e->getMessage();
        }
    }
    // metodo para excluir usuario
    public function excluirUsuario($id_usuario)
    {
        // 1. Define o ID do usuário-alvo
        $this->setIdUsuario($id_usuario);

        try {
            $bd = $this->conectarBanco();

            // --- 2. COLETA DE DADOS ESSENCIAIS ---
            $id_usuario_logado = $_SESSION['id_usuario'];
            $id_usuario_alvo = $this->getIdUsuario();

            // --- Definição dos Nomes dos Perfis ---
            $PERFIL_MASTER = 'Administrador Master';
            $PERFIL_ADMIN = 'Administrador';

            // REGRA 1: AUTO-EXCLUSÃO
            // Ninguém pode se auto-excluir.
            if ($id_usuario_logado == $id_usuario_alvo) {
                return "Erro: Você não pode excluir seu próprio usuário.";
            }

            // --- 3. BUSCAR PERFIS (LOGADO E ALVO) ---
            $sqlPerfis = "SELECT u.id_usuario, p.nome_perfil
                    FROM usuario u
                    JOIN perfil p ON u.id_perfil = p.id_perfil
                    WHERE u.id_usuario = :id_usuario_logado OR u.id_usuario = :id_usuario_alvo";

            $qPerfis = $bd->prepare($sqlPerfis);
            $qPerfis->bindValue(':id_usuario_logado', $id_usuario_logado, PDO::PARAM_INT);
            $qPerfis->bindValue(':id_usuario_alvo', $id_usuario_alvo, PDO::PARAM_INT);
            $qPerfis->execute();

            $perfisEncontrados = $qPerfis->fetchAll(PDO::FETCH_ASSOC);

            $perfil_nome_logado = null;
            $perfil_nome_alvo = null;

            foreach ($perfisEncontrados as $p) {
                if ($p['id_usuario'] == $id_usuario_logado) {
                    $perfil_nome_logado = $p['nome_perfil'];
                }
                if ($p['id_usuario'] == $id_usuario_alvo) {
                    $perfil_nome_alvo = $p['nome_perfil'];
                }
            }

            if (!$perfil_nome_logado || !$perfil_nome_alvo) {
                return "Erro: Usuário logado ou usuário alvo não encontrado no sistema.";
            }

            // --- 4. LÓGICA DE VALIDAÇÃO DE EXCLUSÃO ---

            // REGRA 2: PROTEÇÃO "ÚLTIMO MASTER"
            // Se o alvo é um Master, verificar se ele é o único
            if ($perfil_nome_alvo == $PERFIL_MASTER) {

                $sqlCount = "SELECT COUNT(u.id_usuario) AS total
                            FROM usuario u
                            JOIN perfil p ON u.id_perfil = p.id_perfil
                            WHERE p.nome_perfil = :master";

                $qCount = $bd->prepare($sqlCount);
                $qCount->bindValue(':master', $PERFIL_MASTER, PDO::PARAM_STR);
                $qCount->execute();
                $totalMaster = (int)$qCount->fetch(PDO::FETCH_ASSOC)['total'];

                // Se for o único, bloqueia a exclusão
                if ($totalMaster <= 1) {
                    return "Erro: Não é permitido excluir o único Administrador Master do sistema.";
                }
            }

            // REGRA 3: HIERARQUIA (Admin não pode excluir Master)
            if ($perfil_nome_logado == $PERFIL_ADMIN && $perfil_nome_alvo == $PERFIL_MASTER) {
                return "Erro: Um Administrador não pode excluir um Administrador Master.";
            }

            // REGRA 4: PERMISSÃO MASTER (Pode excluir qualquer um, exceto as regras acima)
            else if ($perfil_nome_logado == $PERFIL_MASTER) {
                // Master pode. Segue para o DELETE.
            }

            // REGRA 4: PERMISSÃO ADMIN (Pode excluir não-Masters)
            else if ($perfil_nome_logado == $PERFIL_ADMIN && $perfil_nome_alvo != $PERFIL_MASTER) {
                // Admin pode. Segue para o DELETE.
            }

            // REGRA 5: PADRÃO (Outros perfis não podem excluir)
            else {
                return "Erro: Você não tem permissão para excluir usuários.";
            }

            // --- 5. EXECUÇÃO DA EXCLUSÃO ---
            // Se passou em todas as validações, prosseguir com a exclusão

            $sql = "DELETE FROM usuario WHERE id_usuario = :id_usuario";
            $q = $bd->prepare($sql);
            $q->bindValue(':id_usuario', $this->getIdUsuario(), PDO::PARAM_INT);
            $q->execute();

            return true;
        } catch (PDOException $e) {
            return "No momento não e possível excluir esse usuario, pois possui registros de Auditoria";
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
