<?php
// Incluindo classe de conexao
include_once 'Conexao.class.php';

class Auditoria extends Conexao
{
    private $id_auditoria = null;
    private $tabela = null;
    private $id_registro = null;
    private $id_usuario = null;
    private $acao = null;
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

    public function getDataHora()
    {
        return $this->data_hora;
    }
    public function setDataHora($data_hora)
    {
        $this->data_hora = $data_hora;
    }

    // ===========================
    // MÉTODOS DE CONSULTA
    // ===========================

    // 1. Listar todas as ações de auditoria agrupadas
    public function listarTudo()
    {
        $sql = "SELECT
                a.id_auditoria,
                a.tabela AS tabela_principal,
                a.id_registro,
                u.nome_usuario,
                a.acao,
                a.data_hora,
                ad.campo,
                ad.valor_antigo,
                ad.valor_novo,
                ad.descricao,
                -- campo virtual para agrupar no modal
                CASE
                    WHEN a.tabela = 'Cliente' THEN 'Cliente'
                    WHEN a.tabela = 'Endereco' THEN 'Endereco'
                    WHEN a.tabela = 'Telefone_Cliente' THEN 'Telefone_Cliente'
                    WHEN a.tabela = 'Inscricao_Estadual' THEN 'Inscricao_Estadual'
                    WHEN a.tabela = 'Pedido' THEN 'Pedido'
                    WHEN a.tabela = 'Item_Pedido' THEN 'Item_Pedido'
                    WHEN a.tabela = 'Produto' THEN 'Produto'
                    WHEN a.tabela = 'Fornecedor' THEN 'Fornecedor'
                    WHEN a.tabela = 'Telefone_Fornecedor' THEN 'Telefone_Fornecedor'
                    WHEN a.tabela = 'Forma_Pagamento' THEN 'Forma_Pagamento'
                    WHEN a.tabela = 'Usuario' THEN 'Usuario'
                    WHEN a.tabela = 'Perfil_Usuario' THEN 'Perfil_Usuario'
                    ELSE 'Principal'
                END AS tabela_relacionada
            FROM Auditoria a
            JOIN Usuario u ON a.id_usuario = u.id_usuario
            LEFT JOIN Auditoria_Detalhe ad ON a.id_auditoria = ad.id_auditoria
            WHERE a.data_hora >= NOW() - INTERVAL 7 DAY
            ORDER BY a.data_hora DESC, a.id_auditoria ASC";

        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao listar auditorias: " . $e->getMessage();
            return false;
        }
    }


    // 2. Listar auditoria por usuário
    public function listarPorUsuario()
    {
        $sql = "SELECT
            a.id_auditoria,
            a.tabela,
            a.id_registro,
            u.nome_usuario,
            a.acao,
            a.data_hora,
            CASE
                WHEN a.tabela = 'Cliente' THEN CONCAT('Cliente: ', c.nome_representante, ' (', c.razao_social, ')')
                WHEN a.tabela = 'Endereco' THEN CONCAT('Endereço do Cliente: ', c.nome_representante, ' - ', e.cidade, '/', e.estado, ' - ', e.bairro)
                WHEN a.tabela = 'Telefone_Cliente' THEN CONCAT('Telefone do Cliente: ', c.nome_representante, ' - Cel: ', tc.telefone_celular, ' / Fixo: ', tc.telefone_fixo)
                WHEN a.tabela = 'Inscricao_Estadual' THEN CONCAT('Inscrição Estadual do Cliente: ', c.nome_representante, ' - ', ie.inscricao_estadual)
                WHEN a.tabela = 'Pedido' THEN CONCAT('Pedido: ', p.numero_pedido, ' do Cliente: ', c.nome_representante)
                WHEN a.tabela = 'Item_Pedido' THEN CONCAT('Item do Pedido: ', ip.id_item_pedido, ' - Produto: ', pr.nome_produto, ' do Pedido: ', p.numero_pedido)
                WHEN a.tabela = 'Produto' THEN CONCAT('Produto: ', pr.nome_produto, ' - Fornecedor: ', f.razao_social)
                WHEN a.tabela = 'Fornecedor' THEN CONCAT('Fornecedor: ', f.razao_social)
                WHEN a.tabela = 'Telefone_Fornecedor' THEN CONCAT('Telefone do Fornecedor: ', f.razao_social, ' - Cel: ', tf.telefone_celular, ' / Fixo: ', tf.telefone_fixo)
                WHEN a.tabela = 'Forma_Pagamento' THEN CONCAT('Forma de Pagamento: ', fp.descricao)
                WHEN a.tabela = 'Usuario' THEN CONCAT('Usuário: ', u2.nome_usuario, ' - Perfil: ', pu.perfil_usuario)
                WHEN a.tabela = 'Perfil_Usuario' THEN CONCAT('Perfil de Usuário: ', pu.perfil_usuario)
                ELSE a.tabela
            END AS descricao_relacionada,
            ad.campo,
            ad.valor_antigo,
            ad.valor_novo
        FROM Auditoria a
        JOIN Usuario u ON a.id_usuario = u.id_usuario
        LEFT JOIN Auditoria_Detalhe ad ON a.id_auditoria = ad.id_auditoria
        LEFT JOIN Cliente c ON (a.tabela IN ('Cliente','Endereco','Telefone_Cliente','Inscricao_Estadual','Pedido') AND a.id_registro = c.id_cliente)
        LEFT JOIN Endereco e ON (a.tabela = 'Endereco' AND a.id_registro = e.id_endereco)
        LEFT JOIN Telefone_Cliente tc ON (a.tabela = 'Telefone_Cliente' AND a.id_registro = tc.id_telefone)
        LEFT JOIN Inscricao_Estadual ie ON (a.tabela = 'Inscricao_Estadual' AND a.id_registro = ie.id_inscricao)
        LEFT JOIN Pedido p ON (a.tabela = 'Pedido' AND a.id_registro = p.id_pedido)
        LEFT JOIN Item_Pedido ip ON (a.tabela = 'Item_Pedido' AND a.id_registro = ip.id_item_pedido)
        LEFT JOIN Produto pr ON ((a.tabela = 'Item_Pedido' AND ip.id_produto = pr.id_produto) OR (a.tabela = 'Produto' AND a.id_registro = pr.id_produto))
        LEFT JOIN Fornecedor f ON ((a.tabela = 'Produto' AND pr.id_fornecedor = f.id_fornecedor) OR (a.tabela = 'Fornecedor' AND a.id_registro = f.id_fornecedor))
        LEFT JOIN Telefone_Fornecedor tf ON (a.tabela = 'Telefone_Fornecedor' AND a.id_registro = tf.id_telefone)
        LEFT JOIN Forma_Pagamento fp ON (a.tabela = 'Forma_Pagamento' AND a.id_registro = fp.id_forma_pagamento)
        LEFT JOIN Usuario u2 ON (a.tabela = 'Usuario' AND a.id_registro = u2.id_usuario)
        LEFT JOIN Perfil_Usuario pu ON ((a.tabela = 'Usuario' AND u2.id_perfil = pu.id_perfil) OR (a.tabela = 'Perfil_Usuario' AND a.id_registro = pu.id_perfil))
        ORDER BY a.data_hora DESC";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print "Erro ao listar auditoria por usuário: " . $e->getMessage();
            return false;
        }
    }

    // 3. Listar auditoria por tabela
    public function listarPorTabela($tabela)
    {
        $sql = "SELECT a.id_auditoria, a.id_registro, u.nome_usuario, a.acao, a.data_hora
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
        $sql = "SELECT a.acao, COUNT(*) AS total FROM Auditoria a GROUP BY a.acao";
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
    // 5. Quantidade de ações por usuário
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
            print "Erro ao contar por usuário: " . $e->getMessage();
            return false;
        }
    }
    // 6. Quantidade de ações por tabela
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
            print "Erro ao contar por tabela: " . $e->getMessage();
            return false;
        }
    }
    // 7. Últimas 24 horas
    public function ultimas24Horas()
    {
        $sql = "SELECT a.id_auditoria, a.tabela, a.id_registro, u.nome_usuario, a.acao, a.data_hora
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
            print "Erro últimas 24h: " . $e->getMessage();
            return false;
        }
    }
    // 8. Agrupamento mensal
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
            print "Erro total por mês: " . $e->getMessage();
            return false;
        }
    }
    // 9. Histórico de um registro específico (com detalhes)
    public function historicoRegistro($tabela, $idRegistro)
    {
        $sql = "SELECT a.id_auditoria, a.tabela, a.id_registro, u.nome_usuario, a.acao, a.data_hora,
                       d.campo, d.valor_antigo, d.valor_novo, d.descricao
                FROM Auditoria a
                JOIN Usuario u ON a.id_usuario = u.id_usuario
                LEFT JOIN Auditoria_Detalhe d ON a.id_auditoria = d.id_auditoria
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
            print "Erro histórico registro: " . $e->getMessage();
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
            print "Erro top usuários: " . $e->getMessage();
            return false;
        }
    }
    // Excluir registros antigos (> 6 meses)
    public function excluirAntigos()
    {
        $sql = "DELETE FROM Auditoria WHERE data_hora < DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        try {
            $bd = $this->conectarBanco();
            $query = $bd->prepare($sql);
            $query->execute();
            return $query->rowCount();
        } catch (PDOException $e) {
            print "Erro excluir antigos: " . $e->getMessage();
            return false;
        }
    }
}
