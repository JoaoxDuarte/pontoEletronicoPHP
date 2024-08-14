<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabFacultativo172Model
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabfacultativo172
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabFacultativo172Model.php
 *
 * @author Edinalvo Rosa
 */
class TabFacultativo172Model
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = NULL;

    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    function __construct()
    {
        # Faz conexão
        $this->conexao = new DataBase();
    }


   /*
     * @info Seleciona códigos de crédito e débito que    
     *       não serão exibidos em lista de códigos de    
     *       ocorrência
     *
     * @param string $data_excessao
     * @return string Códigos a excluir
     */
    function CodigoEventoEsportivoNaoExibir($data_excessao='')
    {
        // verifica se há código de crédito
        // de evento esportivo para este dia
        $this->conexao->setMensagem( "Problemas no acesso a Tabela EVENTOS (E200013.".__LINE__.")." );
        $this->conexao->query( "
            SELECT 
                codigo_credito 
            FROM 
                tabfacultativo172
            WHERE 
                (:dia >= compensacao_inicio
                 AND :dia <= compensacao_fim)
            GROUP BY
                codigo_credito;
            ",
            array(
                array( ':dia', conv_data($data_excessao), PDO::PARAM_STR )
            ));
        $codigo_credito = $this->conexao->fetch_object()->codigo_credito;

        // verifica se há código de débito
        // de evento esportivo para este dia
        $this->conexao->setMensagem( "Problemas no acesso a Tabela EVENTOS (E200014.".__LINE__.")." );
        $this->conexao->query( "
            SELECT 
                codigo_debito 
            FROM 
                tabfacultativo172
            WHERE 
                (:dia >= compensacao_inicio
                 AND :dia <= compensacao_fim)
            GROUP BY 
                codigo_debito;
            ",
            array(
                array( ':dia', conv_data($data_excessao), PDO::PARAM_STR )
            ));
        $codigo_debito = $this->conexao->fetch_object()->codigo_debito;

        $this->conexao->setMensagem( "Problemas no acesso a Tabela EVENTOS (E200015.".__LINE__.")." );
        $this->conexao->query( "
            SELECT 
                codigo_debito, codigo_credito
            FROM 
                tabfacultativo172
            WHERE 
                NOT (dia = :dia)
            GROUP BY
                codigo_debito, codigo_credito
            ORDER BY
                codigo_debito;
            ",
            array(
                array( ':dia', conv_data($data_excessao), PDO::PARAM_STR )
            ));

        while ($dados = $this->conexao->fetch_object())
        {
            if ($dados->codigo_credito != $codigo_credito)
            {
                    $codigo_excluir .= ",'" . $dados->codigo_credito . "'";
            }
            if ($dados->codigo_debito != $codigo_debito)
            {
                    $codigo_excluir .= ",'" . $dados->codigo_debito . "'";
            }
        }

        return $codigo_excluir;
    }


    /*
     * @info Códigos de crédito e débito referente a
     *       eventos esportivos facultado compensar
     *
     * @param string $tipo_codigo
     * @return string
     */
    public function EventosCodigos($tipo_codigo='')
    {
        // código ocorrencia - evento
        $sql = "";

        if (Empty($tipo_codigo) || $tipo_codigo == 'debito')
        {
            $sql .= "SELECT codigo_debito AS codigo FROM tabfacultativo172 WHERE ativo = 'S' GROUP BY codigo_debito ";
        }

        if (Empty($tipo_codigo))
        {
            $sql .= " UNION ";
        }

        if (Empty($tipo_codigo) || $tipo_codigo == 'credito')
        {
            $sql .= "SELECT codigo_credito AS codigo FROM tabfacultativo172 WHERE ativo = 'S' GROUP BY codigo_credito ";
        }

        $sql .= "ORDER BY codigo ";

        $codigos = "";

        // pesquisa no banco
        $this->conexao->setMensagem( "Problemas no acesso a Tabela EVENTOS (E200016.".__LINE__.")." );
        $this->conexao->query( $sql );

        while ($dados = $this->conexao->fetch_object())
        {
                $codigos .= (empty($codigos) ? "'" : ",'") . $dados->codigo . "'";
        }

        return $codigos;
    }


    /*
     * @info Verifica se a ocorrência indica pode ser
     *       utilizada no dia desejado
     *
     * @param string $ocor Ocorrência
     * @param string $data Data da ocorrência
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaEventos($ocor='',$data='')
    {
        // resultado
        $resultado = "";

        $resultado = $this->verificaPeriodoCompensacaoEvento($ocor,$data);

        if (empty($resultado))
        {
            $resultado = $this->verificaDiaEventoAutorizado($ocor,$data);
        }

        return $resultado;
    }


    /*
     * @info Seleciona códigos de crédito e débito que
     *       verifica se a data está dentro do período de
     *       compensação das horas devidas em dias de
     *       evento esportivo
     *
     * @param string $ocor Ocorrência
     * @param string $data Data da ocorrência
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaPeriodoCompensacaoEvento($ocor='',$data='')
    {
        // resultado
        $resultado = "";

        // verifica dias permitidos lançamento desta ocorrência, se evento esportivo
        $this->conexao->setMensagem( "Problemas no acesso a Tabela EVENTOS (E200017.".__LINE__.")." );
        $this->conexao->query( "
            SELECT
                codigo_credito,
                IF((:dia >= compensacao_inicio
                    AND :dia <= compensacao_fim),'sim','nao')
                AS periodo_compensacao, 
                compensacao_inicio, 
                compensacao_fim
            FROM
                tabfacultativo172
            WHERE
                ativo = 'S'
                AND codigo_credito = :codigo_credito
            GROUP BY
                codigo_credito
            LIMIT
                1
            ",
            array(
                array( ':dia',            conv_data($data), PDO::PARAM_STR ),
                array( ':codigo_credito', $ocor,            PDO::PARAM_STR ),
            ));
        $oEvento = $this->conexao->fetch_object();

        // Data dentro do período para compensação
        if ($this->conexao->num_rows() > 0 && $oEvento->periodo_compensacao == 'nao')
        {
            $resultado = "Código \"" . $ocor . 
                         "\", uso restrito ao período de " . 
                         databarra($oEvento->compensacao_inicio) . " a " . 
                         databarra($oEvento->compensacao_fim) ."!";
        }

        return $resultado;
    }


    /*
     * @info Seleciona códigos de crédito e débito que    
     *       e se a ocorrência é permitida para este dia
     *
     * @param string $ocor Ocorrência
     * @param string $data Data da ocorrência
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaDiaEventoAutorizado($ocor='',$data='')
    {
        // resultado
        $resultado = "";

        // verifica dias permitidos lançamento desta ocorrência, se evento esportivo
        $this->conexao->setMensagem( "Problemas no acesso a Tabela EVENTOS (E200018.".__LINE__.")." );
        $this->conexao->query( "
            SELECT
                dia, IF(:dia = dia,'sim','nao') AS data_igual_evento
            FROM
                tabfacultativo172
            WHERE
                ativo = 'S'
                AND codigo_debito = :codigo_debito
            GROUP BY
                dia
            ",
            array(
                array( ':dia',           conv_data($data), PDO::PARAM_STR ),
                array( ':codigo_debito', $ocor,            PDO::PARAM_STR ),
            ));

        $novo_mes = false;
        $mes = "";
        $ano = "";
        $dias_autorizados = "";
        $data_igual_evento = false;
        
        while ($oEvento = $this->conexao->fetch_object())
        {
            if (empty($mes))
            {
                $mes = dataMes($oEvento->dia);
                $ano = dataAno($oEvento->dia);
            }
            else if ($mes != dataMes($oEvento->dia))
            {
                //$dias_autorizados = substr($dias_autorizados,0,strlen($dias_autorizados)-4);
                $dias_autorizados .= " de " . $mes . '/' . $ano . "; ";
                $novo_mes = true;

                $mes = dataMes($oEvento->dia);
                $ano = dataAno($oEvento->dia);
            }

            $data_igual_evento = ($data_igual_evento == false ? ($oEvento->data_igual_evento == 'sim') : $data_igual_evento);
            $dias_autorizados .= (empty($dias_autorizados) ? "" : ($novo_mes ? " e " : ", ")) . dataDia($oEvento->dia);
            $novo_mes = false;
        }

        //$dias_autorizados = substr($dias_autorizados,0,strlen($dias_autorizados)-4);
        $dias_autorizados .= " de " . $mes . '/' . $ano;

        // Data dentro do período para compensação
        if ($this->conexao->num_rows() > 0 && !empty($dias_autorizados) && $data_igual_evento == false)
        {
            $resultado = "Código \"".$ocor."\", uso restrito aos dias " . $dias_autorizados ."!";
        }

        return $resultado;
    }
} // END class TabFacultativo172Model
