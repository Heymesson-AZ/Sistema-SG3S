<?php
// Incluindo classe de conexao
include_once 'Conexao.class.php';

// Classe Auditoria
class Auditoria extends Conexao
{
    private $id_auditoria = null;
    private $tabela = null;
    private $id_registro = null;
    private $id_usuario = null;
    private $acao = null;
    private $descricao = null;
    private $data_hora = null;

    // ===========================
    // GETTERS E SETTERS
    // ===========================
    public function getIdAuditoria()
    {
        return $this->id_auditoria;
    }
    public function setIdAuditoria($id_auditoria)
    {
        $this->id_auditoria = $id_auditoria;
    }

    public function getTabela()
    {
        return $this->tabela;
    }
    public function setTabela($tabela)
    {
        $this->tabela = $tabela;
    }

    public function getIdRegistro()
    {
        return $this->id_registro;
    }
    public function setIdRegistro($id_registro)
    {
        $this->id_registro = $id_registro;
    }

    public function getIdUsuario()
    {
        return $this->id_usuario;
    }
    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    public function getAcao()
    {
        return $this->acao;
    }
    public function setAcao($acao)
    {
        $this->acao = $acao;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    public function getDataHora()
    {
        return $this->data_hora;
    }
    public function setDataHora($data_hora)
    {
        $this->data_hora = $data_hora;
    }

    // MÉTODOS DE CONSULTA

    // 1. Listar todas as ações de auditoria
    public function listarTudo()
    {
        $sql = "SELECT a.id_auditoria, a.tabela, a.id_registro, u.nome_usuario, a.acao, a.descricao, a.data_hora
                FROM Auditoria a
                JOIN Usuario u ON a.id_usuario = u.id_usuario
                ORDER BY a.data_hora DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao listar auditoria: " . $e->getMessage();
            return false;
        }
    }
    // 2. Filtrar por usuário
    public function listarPorUsuario($nomeUsuario)
    {
        $sql = "SELECT a.id_auditoria, a.tabela, a.id_registro, u.nome_usuario, a.acao, a.descricao, a.data_hora
                FROM Auditoria a
                JOIN Usuario u ON a.id_usuario = u.id_usuario
                WHERE u.nome_usuario = ?
                ORDER BY a.data_hora DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(1, $nomeUsuario);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao listar por usuário: " . $e->getMessage();
            return false;
        }
    }

    // 3. Filtrar por tabela
    public function listarPorTabela($tabela)
    {
        $sql = "SELECT a.id_auditoria, a.id_registro, u.nome_usuario, a.acao, a.descricao, a.data_hora
                FROM Auditoria a
                JOIN Usuario u ON a.id_usuario = u.id_usuario
                WHERE a.tabela = ?
                ORDER BY a.data_hora DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(1, $tabela);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao listar por tabela: " . $e->getMessage();
            return false;
        }
    }

    // 4. Quantidade de ações por tipo
    public function totalPorAcao()
    {
        $sql = "SELECT a.acao, COUNT(*) AS total
                FROM Auditoria a
                GROUP BY a.acao";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao contar ações: " . $e->getMessage();
            return false;
        }
    }

    // 5. Quantidade de alterações por usuário
    public function totalPorUsuario()
    {
        $sql = "SELECT u.nome_usuario, COUNT(*) AS total_acoes
                FROM Auditoria a
                JOIN Usuario u ON a.id_usuario = u.id_usuario
                GROUP BY u.nome_usuario
                ORDER BY total_acoes DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao contar alterações por usuário: " . $e->getMessage();
            return false;
        }
    }

    // 6. Quantidade de alterações por tabela
    public function totalPorTabela()
    {
        $sql = "SELECT a.tabela, COUNT(*) AS total_alteracoes
                FROM Auditoria a
                GROUP BY a.tabela
                ORDER BY total_alteracoes DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao contar alterações por tabela: " . $e->getMessage();
            return false;
        }
    }

    // 7. Alterações feitas nas últimas 24 horas
    public function ultimas24Horas()
    {
        $sql = "SELECT a.id_auditoria, a.tabela, a.id_registro, u.nome_usuario, a.acao, a.descricao, a.data_hora
                FROM Auditoria a
                JOIN Usuario u ON a.id_usuario = u.id_usuario
                WHERE a.data_hora >= NOW() - INTERVAL 1 DAY
                ORDER BY a.data_hora DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao listar últimas 24h: " . $e->getMessage();
            return false;
        }
    }

    // 8. Alterações agrupadas por mês
    public function totalPorMes()
    {
        $sql = "SELECT DATE_FORMAT(a.data_hora, '%Y-%m') AS mes, COUNT(*) AS total_alteracoes
                FROM Auditoria a
                GROUP BY DATE_FORMAT(a.data_hora, '%Y-%m')
                ORDER BY mes DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao contar alterações por mês: " . $e->getMessage();
            return false;
        }
    }
    // 9. Histórico de um registro específico
    public function historicoRegistro($tabela, $idRegistro)
    {
        $sql = "SELECT a.id_auditoria, a.tabela, a.id_registro, u.nome_usuario, a.acao, a.descricao, a.data_hora
                FROM Auditoria a
                JOIN Usuario u ON a.id_usuario = u.id_usuario
                WHERE a.tabela = ? AND a.id_registro = ?
                ORDER BY a.data_hora ASC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->bindValue(1, $tabela);
            $query->bindValue(2, $idRegistro);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao buscar histórico do registro: " . $e->getMessage();
            return false;
        }
    }
    // 10. Top 5 usuários mais ativos
    public function topUsuarios()
    {
        $sql = "SELECT u.nome_usuario, COUNT(*) AS total_acoes
                FROM Auditoria a
                JOIN Usuario u ON a.id_usuario = u.id_usuario
                GROUP BY u.nome_usuario
                ORDER BY total_acoes DESC
                LIMIT 5";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao buscar top usuários: " . $e->getMessage();
            return false;
        }
    }
    // ===== Método para exclusão automática após 6 meses =====
    public function excluirAntigos()
    {
        //NOW() → pega a data e hora atuais do banco.
        //DATE_SUB(NOW(), INTERVAL 6 MONTH) → calcula a data de 6 meses atrás.
        $sql = "DELETE FROM Auditoria WHERE data_hora < DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->rowCount(); // Retorna quantos foram excluídos
        } catch (PDOException $e) {
            print "Erro ao excluir registros antigos: " . $e->getMessage();
            return false;
        }
    }
}
