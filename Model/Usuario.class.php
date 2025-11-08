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
    /**
     * Altera os dados de um usuário com base em regras de permissão.
     *
     * REGRAS:
     * 1. Administrador Master pode fazer (quase) tudo.
     * 2. Ninguém pode modificar o PRÓPRIO perfil (auto-edição).
     * 3. Demais informações (nome, email, tel, cpf) podem ser modificadas por si mesmo.
     * 4. Administrador pode alterar qualquer um, EXCETO um Administrador Master.
     * 5. Outros perfis não podem alterar ninguém além de si mesmos (e com restrições).
     * 6. REGRA DE SISTEMA: O perfil do último Administrador Master não pode ser alterado.
     */
    public function alterarUsuario($id_usuario, $nome_usuario, $email, $id_perfil, $cpf, $telefone)
    {
        // 1. Define as propriedades do objeto com os dados recebidos
        $this->setIdUsuario($id_usuario);
        $this->setNomeUsuario($nome_usuario);
        $this->setEmailUsuario($email);
        $this->setIdPerfil($id_perfil); // ID do perfil NOVO
        $this->setTelefone($telefone);
        $this->setCpf($cpf); // CPF NOVO

        try {
            $bd = $this->conectarBanco();

            // IDs para facilitar a leitura
            $id_usuario_logado = $_SESSION['id_usuario'];
            $id_usuario_alvo = $this->getIdUsuario(); // ID do usuário a ser editado

            // --- Definição dos Nomes dos Perfis ---
            $PERFIL_MASTER = 'Administrador Master';
            $PERFIL_ADMIN = 'Administrador';

            // 2. Buscar dados do usuário LOGADO e do ALVO (quem está sendo editado)
            // Precisamos do NOME do perfil (para permissões) e do ID ATUAL (para auto-edição)
            $sqlPerfis = "SELECT
                                u.id_usuario,
                                u.id_perfil AS id_perfil_atual,
                                p.perfil_usuario
                            FROM usuario u
                            JOIN perfil_usuario p ON u.id_perfil = p.id_perfil
                            WHERE u.id_usuario = :id_usuario_logado OR u.id_usuario = :id_usuario_alvo";

            $qPerfis = $bd->prepare($sqlPerfis);
            $qPerfis->bindValue(':id_usuario_logado', $id_usuario_logado, PDO::PARAM_INT);
            $qPerfis->bindValue(':id_usuario_alvo', $id_usuario_alvo, PDO::PARAM_INT);
            $qPerfis->execute();

            $perfisEncontrados = $qPerfis->fetchAll(PDO::FETCH_ASSOC); // Mapeia id_usuario => dados

            $dados_logado = null;
            $dados_alvo = null;

            foreach ($perfisEncontrados as $p) {
                if ($p['id_usuario'] == $id_usuario_logado) {
                    $dados_logado = $p;
                }
                if ($p['id_usuario'] == $id_usuario_alvo) {
                    $dados_alvo = $p;
                }
            }

            if (!$dados_logado || !$dados_alvo) {
                return "Erro: Usuário logado ou usuário alvo não encontrado no sistema.";
            }

            // --- 3. Definição de Variáveis de Estado ---
            $perfil_nome_logado = $dados_logado['perfil_usuario'];
            $perfil_nome_alvo = $dados_alvo['perfil_usuario'];

            $id_perfil_atual_alvo = $dados_alvo['id_perfil_atual'];

            $id_perfil_novo = $this->getIdPerfil();
            $cpf_novo = $this->getCpf(); // Pega o CPF do formulário

            $eh_auto_edicao = ($id_usuario_logado == $id_usuario_alvo);


            // --- 4. LÓGICA DE VALIDAÇÃO DE PERMISSÃO (Quem pode editar Quem) ---

            // Estamos editando OUTRA pessoa
            if (!$eh_auto_edicao) {

                // REGRA 1: Master pode fazer tudo?
                if ($perfil_nome_logado == $PERFIL_MASTER) {

                    // Sim, mas com uma REGRA DE SISTEMA (Último Master)
                    $perfil_esta_mudando = ($id_perfil_novo != $id_perfil_atual_alvo);

                    if ($perfil_nome_alvo == $PERFIL_MASTER && $perfil_esta_mudando) {
                        // Vamos contar quantos "Master" existem
                        $sqlCount = "SELECT COUNT(u.id_usuario) FROM usuario u
                                        JOIN perfil_usuario p ON u.id_perfil = p.id_perfil
                                        WHERE p.perfil_usuario = :master";
                        $qCount = $bd->prepare($sqlCount);
                        $qCount->bindValue(':master', $PERFIL_MASTER, PDO::PARAM_STR);
                        $qCount->execute();
                        $totalMaster = (int)$qCount->fetchColumn();

                        if ($totalMaster <= 1) {
                            return "Erro: Não é permitido alterar o perfil do único Administrador Master do sistema.";
                        }
                    }
                    // Se não for o último master, Master pode prosseguir.

                }
                // REGRA 4: Administrador pode alterar qualquer um, exceto Master
                else if ($perfil_nome_logado == $PERFIL_ADMIN) {
                    if ($perfil_nome_alvo == $PERFIL_MASTER) {
                        return "Erro: Um Administrador não pode alterar os dados de um Administrador Master.";
                    }
                    // Se alvo não for Master, Admin pode prosseguir.

                }
                // REGRA 5: Nenhum outro perfil pode alterar outros usuários
                else {
                    return "Erro: Você não tem permissão para alterar os dados de outros usuários.";
                }
            }

            // --- 5. LÓGICA DE RESTRIÇÃO DE CAMPO (O que pode ser alterado) ---

            // Por padrão, usamos os valores que vieram do formulário
            $id_perfil_a_salvar = $id_perfil_novo;
            $cpf_a_salvar = $cpf_novo; // O CPF do formulário é o padrão

            // REGRA 2: Ninguém pode modificar seu próprio perfil
            if ($eh_auto_edicao) {
                // Forçamos o valor do perfil a ser o valor ATUAL do banco,
                // ignorando o que veio do formulário.
                $id_perfil_a_salvar = $id_perfil_atual_alvo;

                // O CPF não é mais restrito, então $cpf_a_salvar continua
                // sendo $cpf_novo (o valor do formulário).

                // REGRA 3 (implícita): Os outros campos (nome, email, tel, cpf)
                // serão atualizados normalmente no UPDATE abaixo.
            }

            // --- 6. EXECUÇÃO DO UPDATE ---
            // Se chegamos até aqui, a alteração é permitida.

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
            $q->bindValue(':telefone', $this->getTelefone(), PDO::PARAM_STR);

            // Usa as variáveis que passaram pela lógica de restrição
            $q->bindValue(':cpf', $cpf_a_salvar, PDO::PARAM_STR); // Salva o CPF novo
            $q->bindValue(':id_perfil', $id_perfil_a_salvar, PDO::PARAM_INT); // Salva o perfil (com restrição)

            $q->execute();

            return true; // Sucesso

        } catch (PDOException $e) {
            // Em produção, idealmente logar o erro em vez de expor a mensagem
            return "Erro ao alterar usuário: " . $e->getMessage();
        }
    }
    /**
     * Exclui um usuário com base nas regras de permissão.
     *
     * REGRAS DE EXCLUSÃO:
     * 1. Ninguém pode se auto-excluir.
     * 2. O último Administrador Master não pode ser excluído (por ninguém).
     * 3. Administrador não pode excluir um Administrador Master.
     * 4. Perfis comuns não podem excluir ninguém.
     */
    public function excluirUsuario($id_usuario)
    {
        // 1. Define o ID do usuário-alvo
        $this->setIdUsuario($id_usuario);

        try {
            $bd = $this->conectarBanco();

            // --- 2. COLETA DE DADOS ESSENCIAIS ---
            $id_usuario_logado = $_SESSION['id_usuario'];
            $id_usuario_alvo = $this->getIdUsuario();

            // --- Definição dos Nomes dos Perfis (consistente com alterarUsuario) ---
            $PERFIL_MASTER = 'Administrador Master';
            $PERFIL_ADMIN = 'Administrador';

            // REGRA 1: AUTO-EXCLUSÃO
            if ($id_usuario_logado == $id_usuario_alvo) {
                return "Erro: Você não pode excluir seu próprio usuário.";
            }

            // --- 3. BUSCAR PERFIS (LOGADO E ALVO) ---
            // CORRIGIDO: Query usa "perfil_usuario" (tabela e coluna)
            $sqlPerfis = "SELECT u.id_usuario, p.perfil_usuario
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

            foreach ($perfisEncontrados as $p) {
                if ($p['id_usuario'] == $id_usuario_logado) {
                    // CORRIGIDO: Usa a coluna "perfil_usuario"
                    $perfil_nome_logado = $p['perfil_usuario'];
                }
                if ($p['id_usuario'] == $id_usuario_alvo) {
                    // CORRIGIDO: Usa a coluna "perfil_usuario"
                    $perfil_nome_alvo = $p['perfil_usuario'];
                }
            }

            if (!$perfil_nome_logado || !$perfil_nome_alvo) {
                // Checagem de segurança
                if ($id_usuario_alvo == $id_usuario_logado) {
                    return "Erro: Você não pode excluir seu próprio usuário.";
                }
                return "Erro: Usuário alvo não encontrado no sistema.";
            }

            // --- 4. LÓGICA DE VALIDAÇÃO DE EXCLUSÃO ---

            // REGRA 2: PROTEÇÃO "ÚLTIMO MASTER"
            // Se o alvo é um Master, verificar se ele é o único
            if ($perfil_nome_alvo == $PERFIL_MASTER) {

                // CORRIGIDO: Query usa "perfil_usuario" (tabela e coluna)
                $sqlCount = "SELECT COUNT(u.id_usuario)
                                FROM usuario u
                                JOIN perfil_usuario p ON u.id_perfil = p.id_perfil
                                WHERE p.perfil_usuario = :master";
                $qCount = $bd->prepare($sqlCount);
                $qCount->bindValue(':master', $PERFIL_MASTER, PDO::PARAM_STR);
                $qCount->execute();

                // CORRIGIDO: Usa fetchColumn() para consistência
                $totalMaster = (int)$qCount->fetchColumn();

                if ($totalMaster <= 1) {
                    return "Erro: Não é permitido excluir o único Administrador Master do sistema.";
                }
            }

            // REGRA 3, 4, 5: HIERARQUIA E PERMISSÕES

            // É um Master?
            if ($perfil_nome_logado == $PERFIL_MASTER) {
                // Master pode excluir (regra do último master já foi checada acima)
                // Segue para o DELETE
            }
            // É um Admin?
            else if ($perfil_nome_logado == $PERFIL_ADMIN) {
                // Admin pode excluir, EXCETO um Master
                if ($perfil_nome_alvo == $PERFIL_MASTER) {
                    return "Erro: Um Administrador não pode excluir um Administrador Master.";
                }
                // Se não for master, segue para o DELETE
            }
            // É qualquer outro perfil?
            else {
                return "Erro: Você não tem permissão para excluir usuários.";
            }

            // --- 5. EXECUÇÃO DA EXCLUSÃO ---
            // Se passou em todas as validações, prosseguir

            $sql = "DELETE FROM usuario WHERE id_usuario = :id_usuario";
            $q = $bd->prepare($sql);
            $q->bindValue(':id_usuario', $this->getIdUsuario(), PDO::PARAM_INT);
            $q->execute();

            return true;
        } catch (PDOException $e) {
            return "Erro: Não é possível excluir este usuário, pois ele possui registros associados no sistema (como auditorias, pedidos, etc.).";
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
