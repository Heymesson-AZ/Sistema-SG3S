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
    /**
     * Busca e agrupa os eventos de auditoria de forma otimizada.
     *
     * @return array|false Retorna um array de eventos agrupados ou false em caso de erro.
     */

    // auditorias dos ultimos 7 dias
    public function listarEventosDeAuditoria()
    {
        // SQL DOS EVENTOS PRINCIPAIS: Aprimorada para eliminar o "Registro Geral"
        $sqlEventos = "SELECT
        a.id_auditoria, a.tabela, a.id_registro, a.acao, a.data_hora,
        COALESCE(u.nome_usuario, 'Usuário Excluído') AS nome_usuario,
        -- Lógica de COALESCE aprimorada para criar títulos específicos
        COALESCE(
            cli.nome_fantasia, 
            forn.razao_social, 
            prod.nome_produto, 
            user_alvo.nome_usuario, 
            ped.numero_pedido, 
            CONCAT('Perfil: ', pu.perfil_usuario),
            CONCAT('Forma Pgto: ', fp.descricao),
            CONCAT('Tipo Produto: ', tp.nome_tipo),
            CONCAT('Cor: ', cor.nome_cor)
        ) AS nome_registro_principal
        FROM auditoria a
        LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
        -- Joins para entidades principais
        LEFT JOIN cliente cli ON a.tabela = 'cliente' AND a.id_registro = cli.id_cliente
        LEFT JOIN fornecedor forn ON a.tabela = 'fornecedor' AND a.id_registro = forn.id_fornecedor
        LEFT JOIN produto prod ON a.tabela = 'produto' AND a.id_registro = prod.id_produto
        LEFT JOIN usuario user_alvo ON a.tabela = 'usuario' AND a.id_registro = user_alvo.id_usuario
        LEFT JOIN pedido ped ON a.tabela = 'pedido' AND a.id_registro = ped.id_pedido
        LEFT JOIN perfil_Usuario pu ON a.tabela = 'perfil_Usuario' AND a.id_registro = pu.id_perfil
        -- Joins adicionais para enriquecer o título
        LEFT JOIN forma_Pagamento fp ON a.tabela = 'forma_Pagamento' AND a.id_registro = fp.id_forma_pagamento
        LEFT JOIN tipo_Produto tp ON a.tabela = 'tipo_Produto' AND a.id_registro = tp.id_tipo_produto
        LEFT JOIN cor cor ON a.tabela = 'cor' AND a.id_registro = cor.id_cor
        WHERE a.data_hora >= NOW() - INTERVAL 7 DAY
        ORDER BY a.data_hora DESC, a.id_auditoria DESC";

        try {
            $bd = $this->conectarBanco();

            $queryEventos = $bd->prepare($sqlEventos);
            $queryEventos->execute();
            $eventos = $queryEventos->fetchAll(PDO::FETCH_ASSOC);

            if (empty($eventos)) {
                return [];
            }

            $idsAuditoria = array_column($eventos, 'id_auditoria');
            $placeholders = implode(',', array_fill(0, count($idsAuditoria), '?'));

            // SQL DOS DETALHES: Já está completa da etapa anterior
            $sqlDetalhes = "SELECT
            ad.id_auditoria, ad.campo, ad.valor_antigo, ad.valor_novo, ad.descricao,
            COALESCE(cli_antigo.nome_fantasia, user_antigo.nome_usuario, ped_antigo.numero_pedido, prod_item_antigo.nome_produto, perfil_antigo.perfil_usuario, pag_antigo.descricao, cor_antigo.nome_cor, tipo_antigo.nome_tipo, forn_prod_antigo.razao_social, ad.valor_antigo) AS valor_antigo_legivel,
            COALESCE(cli_novo.nome_fantasia, user_novo.nome_usuario, ped_novo.numero_pedido, prod_item_novo.nome_produto, perfil_novo.perfil_usuario, pag_novo.descricao, cor_novo.nome_cor, tipo_novo.nome_tipo, forn_prod_novo.razao_social, ad.valor_novo) AS valor_novo_legivel
            FROM auditoria_Detalhe ad
            LEFT JOIN cliente cli_antigo ON ad.campo = 'id_cliente' AND ad.valor_antigo = cli_antigo.id_cliente
            LEFT JOIN cliente cli_novo ON ad.campo = 'id_cliente' AND ad.valor_novo = cli_novo.id_cliente
            LEFT JOIN usuario user_antigo ON ad.campo = 'id_usuario' AND ad.valor_antigo = user_antigo.id_usuario
            LEFT JOIN usuario user_novo ON ad.campo = 'id_usuario' AND ad.valor_novo = user_novo.id_usuario
            LEFT JOIN pedido ped_antigo ON ad.campo = 'id_pedido' AND ad.valor_antigo = ped_antigo.id_pedido
            LEFT JOIN pedido ped_novo ON ad.campo = 'id_pedido' AND ad.valor_novo = ped_novo.id_pedido
            LEFT JOIN produto prod_item_antigo ON ad.campo = 'id_produto' AND ad.valor_antigo = prod_item_antigo.id_produto
            LEFT JOIN produto prod_item_novo ON ad.campo = 'id_produto' AND ad.valor_novo = prod_item_novo.id_produto
            LEFT JOIN perfil_Usuario perfil_antigo ON ad.campo = 'id_perfil' AND ad.valor_antigo = perfil_antigo.id_perfil
            LEFT JOIN perfil_Usuario perfil_novo ON ad.campo = 'id_perfil' AND ad.valor_novo = perfil_novo.id_perfil
            LEFT JOIN forma_Pagamento pag_antigo ON ad.campo = 'id_forma_pagamento' AND ad.valor_antigo = pag_antigo.id_forma_pagamento
            LEFT JOIN forma_Pagamento pag_novo ON ad.campo = 'id_forma_pagamento' AND ad.valor_novo = pag_novo.id_forma_pagamento
            LEFT JOIN cor cor_antigo ON ad.campo = 'id_cor' AND ad.valor_antigo = cor_antigo.id_cor
            LEFT JOIN cor cor_novo ON ad.campo = 'id_cor' AND ad.valor_novo = cor_novo.id_cor
            LEFT JOIN tipo_Produto tipo_antigo ON ad.campo = 'id_tipo_produto' AND ad.valor_antigo = tipo_antigo.id_tipo_produto
            LEFT JOIN tipo_Produto tipo_novo ON ad.campo = 'id_tipo_produto' AND ad.valor_novo = tipo_novo.id_tipo_produto
            LEFT JOIN fornecedor forn_prod_antigo ON ad.campo = 'id_fornecedor' AND ad.valor_antigo = forn_prod_antigo.id_fornecedor
            LEFT JOIN fornecedor forn_prod_novo ON ad.campo = 'id_fornecedor' AND ad.valor_novo = forn_prod_novo.id_fornecedor
            WHERE ad.id_auditoria IN ($placeholders)";

            $queryDetalhes = $bd->prepare($sqlDetalhes);
            $queryDetalhes->execute($idsAuditoria);
            $detalhes = $queryDetalhes->fetchAll(PDO::FETCH_ASSOC);

            // Montagem do resultado final (sem alteração)
            $detalhesAgrupados = [];
            foreach ($detalhes as $detalhe) {
                $detalhesAgrupados[$detalhe['id_auditoria']][] = $detalhe;
            }

            $resultadoFinal = [];
            foreach ($eventos as $evento) {
                $id = $evento['id_auditoria'];
                $evento['detalhes'] = isset($detalhesAgrupados[$id]) ? $detalhesAgrupados[$id] : [];
                $resultadoFinal[] = $evento;
            }

            return $resultadoFinal;
        } catch (PDOException $e) {
            print "Erro ao listar eventos de auditoria: " . $e->getMessage();
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
