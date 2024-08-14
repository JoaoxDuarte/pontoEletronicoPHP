<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabGrevesPeriodosModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : greves_periodos
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabGrevesPeriodosModel.php
 *
 * @author Edinalvo Rosa
 */
class TabGrevesPeriodosModel
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
     * @info SQL Padrão
     *
     * @param $id ID do registro
     * @return object
     */
    public function padraoGrevesSQL($id=null)
    {
        if (is_null($id) || empty($id))
        {
            $where  = "";
            $params = null;
        }
        else
        {
            $where  = " AND greves_periodos.id = :id ";
            $params = array(
                array( ':id', $id, PDO::PARAM_STR),
            );
        }

        $query = "
        SELECT 
            id, descricao, codigo_credito, codigo_debito, 
            carreira, carreira_excluida, orgao, inicio, fim, 
            inicio_compensacao, fim_compensacao
            FROM greves_periodos
                WHERE greves_periodos.ativo 
                      $where
                        ORDER BY greves_periodos.inicio DESC
        ";

        $this->conexao->query( $query, $params );

        return $this->conexao;
    }


    /*
     * @info SQL Padrão
     *
     * @param $id ID do registro
     * @return object
     */
    public function grevesPeriodoPorCarreira($carreira=null, $ocor=null, $dtini=null, $dtfim=null)
    {
        if ((is_null($carreira) || empty($carreira)) || 
            (is_null($ocor) || empty($ocor)) || 
            (validaData($dtini) == false) || (validaData($dtfim) == false))
        {
            return false;
        }

        $query = "
            SELECT inicio, fim 
                FROM greves_periodos 
                WHERE 
                    (:dtini >= inicio AND :dtfim <= fim) 
                    AND codigo_debito = :ocor
                    AND (carreira LIKE '%".$carreira."%' OR carreira = '')
                        ORDER BY greves_periodos.inicio DESC
        ";

        $params = array(
            array( ':dtini', $dtini, PDO::PARAM_STR ),
            array( ':dtfim', $dtfim, PDO::PARAM_STR ),
            array( ':ocor',  $ocor,  PDO::PARAM_STR ),
        );
        
        $this->conexao->query( $query, $params );

        return $this->conexao;
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosGreves()
    {
        return $this->padraoGrevesSQL();
    }


    /*
     * @info Registros cargo
     *
     * @param void
     * @return json
     */
    public function registrosGrevesRetornoAjax()
    {
        $oDBase = $this->registrosGreves();

        $result = array();

        while ($dados = $oDBase->fetch_object())
        {
            $alterar   = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"text-align:center;\" href=\"greves_alterar.php?codcargo=". tratarHTML($dados->id) . "\"><span class=\"glyphicon glyphicon-pencil\" alt=\"Editar Períodos de Greve\" title=\"Editar Períodos de Greve\"></span></a>";
            $separador = ""; //&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $excluir   = ""; //"<a class=\"text-center\" href=\"javascript:DeleteGreves('" . tratarHTML($dados->id) . "');\"><span class=\"glyphicon glyphicon-trash\" alt=\"Excluir Período de Greve\" title=\"Excluir Período de Greve\"></span></a>";

            $result[] = array(
                utf8_encode(
                    $alterar .
                    $separador .
                    $excluir
                ),
                "<font style=\"text-align:center;\">".$dados->id."</font>",
                utf8_encode($dados->descricao),
                utf8_encode($dados->carreira),
                databarra($dados->inicio),
                databarra($dados->fim),
            );
        }

        $myData = array(
            'data' => $result);

        print json_encode($myData);
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosGrevesPeriodosPorID($id=null)
    {
        return $this->padraoGrevesPeriodosSQL($id);
    }


    /**
     * @info Inlcui novo registro de isenção de ponto
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var=null)
    {
        $retorno = '';

        if ( !is_null($var) )
        {
            // verifica se já existe item igual
            $isencao = $this->registrosGrevesPeriodosPorID($var['codigo']);

            if ($isencao->num_rows() > 0)
            {
                $retorno = 'ja_existe';
            }
            else
            {
                $this->conexao->query("
                INSERT INTO greves_periodos
                    (id, descricao, codigo_credito, codigo_debito, 
                     carreira, carreira_excluida, orgao, inicio, fim,  
                     inicio_compensacao, fim_compensacao)
                    VALUES
                    (:id, :descricao, :codigo_credito, :codigo_debito, 
                     :carreira, :carreira_excluida, :orgao, :inicio, :fim,  
                     :inicio_compensacao, :fim_compensacao)
                ",
                array(
                    array(":id",                 $var['id'],                            PDO::PARAM_STR),
                    array(":descricao",          mb_strtoupper($var['descricao']),      PDO::PARAM_STR),
                    array(":codigo_credito",     $var['codigo_credito'],                PDO::PARAM_STR),
                    array(":codigo_debito",      $var['codigo_debito'],                 PDO::PARAM_STR),
                    array(":carreira",           $var['carreira'],                      PDO::PARAM_STR),
                    array(":carreira_excluida",  $var['carreira_excluida'],             PDO::PARAM_STR),
                    array(":orgao",              $var['orgao'],                         PDO::PARAM_STR),
                    array(":inicio",             conv_data($var['inicio']),             PDO::PARAM_STR),
                    array(":fim",                conv_data($var['fim']),                PDO::PARAM_STR),
                    array(":inicio_compensacao", conv_data($var['inicio_compensacao']), PDO::PARAM_STR),
                    array(":fim_compensacao",    conv_data($var['fim_compensacao']),    PDO::PARAM_STR),
                ));

                $affected_rows = ($this->conexao->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Greves (períodos) (".databarra($var['inicio'])." a ".databarra($var['fim']).") registrado com sucesso!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Greves (períodos) (".databarra($var['inicio'])." a ".databarra($var['fim']).") NÃO foi registrado!");
                    $retorno = 'nao_gravou';
                }
            }
        }

        return $retorno;
    }

    /**
     * @info Inlcui novo registro de isenção de ponto
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function update($var=null)
    {
        $retorno = '';

        if ( !is_null($var) )
        {
            // verifica se já existe item igual
            $isencao = $this->registrosGrevesPeriodosPorID($var['id']);

            if ($isencao->num_rows() == 0)
            {
                $retorno = 'nao_existe';
            }
            else
            {
                $this->conexao->query("
                UPDATE greves_periodos
                    SET
                        descricao           = :descricao,
                        codigo_credito      = :codigo_credito,
                        codigo_debito       = :codigo_debito,
                        carreira            = :carreira,
                        carreira_excluida   = :carreira_excluida,
                        orgao               = :orgao,
                        inicio              = :inicio,
                        fim                 = :fim,
                        inicio_compensacao  = :inicio_compensacao,
                        fim_compensacao     = :fim_compensacao
                    WHERE id = :id
                ",
                array(
                    array(":id",                 $var['id'],                            PDO::PARAM_STR),
                    array(":descricao",          mb_strtoupper($var['descricao']),      PDO::PARAM_STR),
                    array(":codigo_credito",     $var['codigo_credito'],                PDO::PARAM_STR),
                    array(":codigo_debito",      $var['codigo_debito'],                 PDO::PARAM_STR),
                    array(":carreira",           $var['carreira'],                      PDO::PARAM_STR),
                    array(":carreira_excluida",  $var['carreira_excluida'],             PDO::PARAM_STR),
                    array(":orgao",              $var['orgao'],                         PDO::PARAM_STR),
                    array(":inicio",             conv_data($var['inicio']),             PDO::PARAM_STR),
                    array(":fim",                conv_data($var['fim']),                PDO::PARAM_STR),
                    array(":inicio_compensacao", conv_data($var['inicio_compensacao']), PDO::PARAM_STR),
                    array(":fim_compensacao",    conv_data($var['fim_compensacao']),    PDO::PARAM_STR),
                ));

                $affected_rows = ($this->conexao->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Alterado com sucesso (".$var['id']." - ".databarra($var['inicio'])." a ".databarra($var['fim']).")!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Alteração NÃO foi realizada (".$var['id']." - ".databarra($var['inicio'])." a ".databarra($var['fim']).")!");
                    $retorno = 'nao_gravou';
                }
            }
        }

        return $retorno;
    }


    /**
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id=null )
    {
        $resultado = "error";

        if ($num_rows > 0)
        {
            $resultado = "warning";
        }
        else
        {
            $query = "
            UPDATE greves_periodos
                SET ativo          = :ativo,
                    registro_siape = :registro_siape,
                    registro_data  = NOW()
                        WHERE greves_periodos.id = :id
            ";

            $params = array(
                array(":id",             $id, PDO::PARAM_STR),
                array(":ativo",          'N', PDO::PARAM_STR),
                array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
            );

            $this->conexao->query( $query, $params );

            if ($this->conexao->affected_rows() > 0)
            {
                $resultado = "success";
                registraLog("Deletou o período ".$id);
            }
        }

        return $resultado;
    }

} // END class GrevesPeriodosModel
