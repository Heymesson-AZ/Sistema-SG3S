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

    // ===============================
    // Cadastrar Fornecedor
    // ===============================
    public function cadastrarFornecedor(
        $razao_social,
        $cnpj,
        $email,
        $telefones
    ) {
        $this->setRazaoSocialFornecedor($razao_social);
        $this->setCnpjFornecedor($cnpj);
        $this->setEmailFornecedor($email);

        try {
            $this->pdo->beginTransaction();

            // --- Inserir fornecedor ---
            $sql = "INSERT INTO fornecedor (razao_social, cnpj_fornecedor, email) 
                VALUES (:razao_social, :cnpj_fornecedor, :email)";
            $query = $this->pdo->prepare($sql);
            $query->bindValue(':razao_social', $this->getRazaoSocialFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':cnpj_fornecedor', $this->getCnpjFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':email', $this->getEmailFornecedor(), PDO::PARAM_STR);
            $query->execute();

            $id_fornecedor = $this->pdo->lastInsertId();

            // --- Inserir telefones ---
            if (!empty($telefones)) {
                $sqlTelefone = "INSERT INTO telefone_fornecedor (id_fornecedor, tipo, numero)
                            VALUES (:id_fornecedor, :tipo, :numero)";
                $stmtTel = $this->pdo->prepare($sqlTelefone);

                $numerosInseridos = [];
                foreach ($telefones as $tel) {
                    $tipo   = $tel['tipo'] ?? null;
                    $numero = isset($tel['numero']) ? preg_replace('/\D/', '', $tel['numero']) : null;

                    if ($tipo && $numero) {
                        $numeroNormalizado = preg_replace('/\D/', '', $numero);

                        if (in_array($numeroNormalizado, $numerosInseridos)) {
                            continue; // ignora duplicado
                        }

                        $stmtTel->bindValue(":id_fornecedor", $id_fornecedor, PDO::PARAM_INT);
                        $stmtTel->bindValue(":tipo", $tipo, PDO::PARAM_STR);
                        $stmtTel->bindValue(":numero", $numero, PDO::PARAM_STR);
                        $stmtTel->execute();

                        $numerosInseridos[] = $numeroNormalizado;
                    }
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erro ao cadastrar fornecedor: " . $e->getMessage());
            return false;
        }
    }
    // ===============================
    // Alterar Fornecedor
    // ===============================
    public function alterarFornecedor(
        $id_fornecedor,
        $razao_social,
        $cnpj,
        $email,
        $telefones // array [['id_telefone'=>?, 'tipo'=>'...', 'numero'=>'...'], ...]
    ) {
        $this->setRazaoSocialFornecedor($razao_social);
        $this->setCnpjFornecedor($cnpj);
        $this->setEmailFornecedor($email);

        try {
            $this->pdo->beginTransaction();

            // --- Atualizar fornecedor ---
            $sql = "UPDATE fornecedor 
                SET razao_social = :razao_social,
                    cnpj_fornecedor = :cnpj_fornecedor,
                    email = :email
                WHERE id_fornecedor = :id_fornecedor";
            $query = $this->pdo->prepare($sql);
            $query->bindValue(':razao_social', $this->getRazaoSocialFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':cnpj_fornecedor', $this->getCnpjFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':email', $this->getEmailFornecedor(), PDO::PARAM_STR);
            $query->bindValue(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
            $query->execute();

            // --- Buscar telefones atuais ---
            $sqlFones = "SELECT id_telefone, numero 
                        FROM telefone_fornecedor 
                        WHERE id_fornecedor = :id_fornecedor";
            $stmtFones = $this->pdo->prepare($sqlFones);
            $stmtFones->bindValue(":id_fornecedor", $id_fornecedor, PDO::PARAM_INT);
            $stmtFones->execute();
            $telefonesAtuais = $stmtFones->fetchAll(PDO::FETCH_KEY_PAIR);

            $idsEnviados = [];

            // --- Inserir/Atualizar telefones ---
            if (!empty($telefones)) {
                foreach ($telefones as $tel) {
                    $tipo   = $tel['tipo'] ?? null;
                    $numero = isset($tel['numero']) ? preg_replace('/\D/', '', $tel['numero']) : null;
                    $id_tel = $tel['id_telefone'] ?? null;

                    if ($tipo && $numero) {
                        if (!empty($id_tel) && isset($telefonesAtuais[$id_tel])) {
                            // Atualizar existente
                            $sqlUpdateTel = "UPDATE telefone_fornecedor
                                            SET tipo = :tipo, numero = :numero
                                            WHERE id_telefone = :id_telefone AND id_fornecedor = :id_fornecedor";
                            $stmtUpdateTel = $this->pdo->prepare($sqlUpdateTel);
                            $stmtUpdateTel->bindValue(":tipo", $tipo, PDO::PARAM_STR);
                            $stmtUpdateTel->bindValue(":numero", $numero, PDO::PARAM_STR);
                            $stmtUpdateTel->bindValue(":id_telefone", $id_tel, PDO::PARAM_INT);
                            $stmtUpdateTel->bindValue(":id_fornecedor", $id_fornecedor, PDO::PARAM_INT);
                            $stmtUpdateTel->execute();

                            $idsEnviados[] = $id_tel;
                        } else {
                            // Inserir novo
                            $sqlInsertTel = "INSERT INTO telefone_fornecedor (id_fornecedor, tipo, numero)
                                            VALUES (:id_fornecedor, :tipo, :numero)";
                            $stmtInsertTel = $this->pdo->prepare($sqlInsertTel);
                            $stmtInsertTel->bindValue(":id_fornecedor", $id_fornecedor, PDO::PARAM_INT);
                            $stmtInsertTel->bindValue(":tipo", $tipo, PDO::PARAM_STR);
                            $stmtInsertTel->bindValue(":numero", $numero, PDO::PARAM_STR);
                            $stmtInsertTel->execute();

                            $idsEnviados[] = $this->pdo->lastInsertId();
                        }
                    }
                }
            }

            // --- Excluir telefones não enviados ---
            foreach ($telefonesAtuais as $id_tel => $numero) {
                if (!in_array($id_tel, $idsEnviados)) {
                    $sqlDeleteTel = "DELETE FROM telefone_fornecedor
                                    WHERE id_telefone = :id_telefone 
                                    AND id_fornecedor = :id_fornecedor";
                    $stmtDeleteTel = $this->pdo->prepare($sqlDeleteTel);
                    $stmtDeleteTel->bindValue(":id_telefone", $id_tel, PDO::PARAM_INT);
                    $stmtDeleteTel->bindValue(":id_fornecedor", $id_fornecedor, PDO::PARAM_INT);
                    $stmtDeleteTel->execute();
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erro ao alterar fornecedor: " . $e->getMessage());
            return false;
        }
    }
    public function excluirFornecedor($id_fornecedor)
    {
        try {
            // verificar se o fornecedor tem produtos vinculados
            $sqlCheck = "SELECT COUNT(*) AS quantidade FROM produto WHERE id_fornecedor = :id_fornecedor";
            $queryCheck = $this->conectarBanco()->prepare($sqlCheck);
            $queryCheck->bindParam(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
            $queryCheck->execute();
            $resultadoCheck = $queryCheck->fetch(PDO::FETCH_OBJ);

            if ($resultadoCheck->quantidade > 0) {
                // Retorna mensagem de erro em vez de lançar exceção
                return [
                    'sucesso' => false,
                    'mensagem' => "Não é possível excluir o fornecedor, pois existem produtos associados."
                ];
            }

            // query sql para excluir o fornecedor no banco de dados
            $sql = "DELETE FROM fornecedor WHERE id_fornecedor = :id_fornecedor";
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindParam(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
            $query->execute();

            return [
                'sucesso' => true,
                'mensagem' => "Fornecedor excluído com sucesso."
            ];
        } catch (Exception $e) {
            // Se der algum erro de banco
            return [
                'sucesso' => false,
                'mensagem' => "Erro ao excluir fornecedor: "
            ];
        }
    }

    // metodo de consultar fornecedor
    public function consultarFornecedor($razao_social = null, $cnpj_fornecedor = null)
    {
        // settar atributos
        $this->setRazaoSocialFornecedor($razao_social);
        $this->setCnpjFornecedor($cnpj_fornecedor);
        // Query base com GROUP_CONCAT para agrupar telefones
        $sql = "SELECT
                f.id_fornecedor,
                f.razao_social,
                f.cnpj_fornecedor,
                f.email,
                GROUP_CONCAT(
                    CONCAT(tf.id_telefone, ':', tf.tipo, ':', tf.numero)
                    ORDER BY tf.id_telefone SEPARATOR ','
                ) AS telefones
                FROM fornecedor AS f
                LEFT JOIN telefone_fornecedor AS tf
                ON f.id_fornecedor = tf.id_fornecedor";

        // Condições dinâmicas
        $condicoes = [];
        if (!empty($this->getRazaoSocialFornecedor())) {
            $condicoes[] = "f.razao_social LIKE :razao_social";
        }
        if (!empty($this->getCnpjFornecedor())) {
            $condicoes[] = "f.cnpj_fornecedor LIKE :cnpj_fornecedor";
        }

        if ($condicoes) {
            $sql .= " WHERE " . implode(" AND ", $condicoes);
        }

        // Agrupamento para o GROUP_CONCAT
        $sql .= " GROUP BY
                f.id_fornecedor,
                f.razao_social,
                f.cnpj_fornecedor,
                f.email
                ORDER BY f.razao_social ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);

            // Bind dinâmico
            if (!empty($this->getRazaoSocialFornecedor())) {
                $query->bindValue(":razao_social", "%" . $this->getRazaoSocialFornecedor() . "%", PDO::PARAM_STR);
            }
            if (!empty($this->getCnpjFornecedor())) {
                $query->bindValue(":cnpj_fornecedor", "%" . $this->getCnpjFornecedor() . "%", PDO::PARAM_STR);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao consultar fornecedores: " . $e->getMessage());
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
