<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabHistHomologadosModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : homologados_historico
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabHistHomologadosModel.php
 *
 * @author Edinalvo Rosa
 */
class TabHistHomologadosModel
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
     * @param $cargo Código do cargo (opcional)
     * @return object
     */
    public function padraoCargoSQL($cargo=null)
    {
        if (is_null($cargo) || empty($cargo))
        {
            $where  = "";
            $params = null;
        }
        else
        {
            $where  = " WHERE tabcargo.COD_CARGO = :cargo ";
            $params = array(
                array( ':cargo', $cargo, PDO::PARAM_STR),
            );
        }

        $query = "
        SELECT tabcargo.COD_CARGO, tabcargo.DESC_CARGO,
               tabcargo.PERMITE_BANCO, tabcargo.SUBSIDIOS, tabcargo.ativo
            FROM tabcargo
                $where
                    ORDER BY tabcargo.ativo DESC, tabcargo.DESC_CARGO
        ";

        $this->conexao->query( $query, $params );

        return $this->conexao;
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosCargo()
    {
        return $this->padraoCargoSQL();
    }


    /*
     * @info Registros cargo
     *
     * @param void
     * @return json
     */
    public function registrosHistHomologadosRetornoAjax()
    {
        $oDBase = $this->registrosCargo();

        $result = array();

        while ($dados = $oDBase->fetch_object())
        {
            $alterar   = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"text-align:center;\" href=\"tabcargo_alterar.php?codcargo=". tratarHTML($dados->COD_CARGO) . "\"><span class=\"glyphicon glyphicon-pencil\" alt=\"Editar cargo\" title=\"Editar cargo\"></span></a>";
            $separador = ""; //&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $excluir   = ""; //"<a class=\"text-center\" href=\"javascript:DeleteCargo('" . tratarHTML($dados->COD_CARGO) . "');\"><span class=\"glyphicon glyphicon-trash\" alt=\"Excluir cargo\" title=\"Excluir cargo\"></span></a>";

            $result[] = array(
                utf8_encode(
                    $alterar .
                    $separador .
                    $excluir
                ),
                "<font style=\"text-align:center;\">".$dados->COD_CARGO."</font>",
                utf8_encode($dados->DESC_CARGO),
                utf8_encode($dados->PERMITE_BANCO),
                utf8_encode($dados->SUBSIDIOS),
                utf8_encode(($dados->ativo != "S" ? "NÃO" : "SIM")),
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
    public function registrosCargoPorID($cargo=null)
    {
        return $this->padraoCargoSQL($cargo);
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
            $isencao = $this->registrosCargoPorID($var['codigo']);

            if ($isencao->num_rows() > 0)
            {
                $retorno = 'ja_existe';
            }
            else
            {
                $this->conexao->query("
                INSERT INTO tabcargo
                    (COD_CARGO, DESC_CARGO, PERMITE_BANCO, SUBSIDIOS, ativo, registro_siape, registro_data)
                    VALUES
                    (:codigo, :nome, :permite, :subsidios, :ativo, :registro_siape, NOW())
                ",
                array(
                    array(":codigo",    $var['codigo'],              PDO::PARAM_STR),
                    array(":nome",      mb_strtoupper($var['nome']), PDO::PARAM_STR),
                    array(":permite",   strtr($var['permite'], array('NÃO' => 'NAO')), PDO::PARAM_STR),
                    array(":subsidios", $var['subsidios'],           PDO::PARAM_STR),
                    array(":ativo",     'S',                         PDO::PARAM_STR),
                    array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
                ));

                $affected_rows = ($this->conexao->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Cargo (".$var['codigo'].") registrado com sucesso!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Cargo (".$var['codigo'].") NÃO foi registrado!");
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
            $isencao = $this->registrosCargoPorID($var['id']);

            if ($isencao->num_rows() == 0)
            {
                $retorno = 'nao_existe';
            }
            else
            {
                $this->conexao->query("
                UPDATE tabcargo
                    SET tabcargo.DESC_CARGO    = :descricao,
                        tabcargo.PERMITE_BANCO = :permite,
                        tabcargo.SUBSIDIOS     = :subsidios,
                        ativo                  = :ativo,
                        registro_siape         = :registro_siape,
                        registro_data          = NOW()
                            WHERE tabcargo.COD_CARGO = :codigo
                ",
                array(
                    array(":codigo",         $var['id'],                  PDO::PARAM_STR),
                    array(":descricao",      mb_strtoupper($var['nome']), PDO::PARAM_STR),
                    array(":permite",        strtr($var['permite'], array('NÃO' => 'NAO')), PDO::PARAM_STR),
                    array(":subsidios",      $var['subsidios'],           PDO::PARAM_STR),
                    array(":ativo",          substr($var['ativo'],0,1),   PDO::PARAM_STR),
                    array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
                ));

                $affected_rows = ($this->conexao->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Alterado com sucesso (".$var['id'].")!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Alteração NÃO foi realizada (".$var['id'].")!");
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
            UPDATE tabcargo
                SET ativo          = :ativo,
                    registro_siape = :registro_siape,
                    registro_data  = NOW()
                        WHERE tabcargo.COD_CARGO = :codigo
            ";

            $params = array(
                array(":codigo",         $id, PDO::PARAM_STR),
                array(":ativo",          'N', PDO::PARAM_STR),
                array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
            );

            $this->conexao->query( $query, $params );

            if ($this->conexao->affected_rows() > 0)
            {
                $resultado = "success";
                registraLog("Deletou o cargo ".$id);
            }
        }

        return $resultado;
    }

} // END class TabHistHomologadosModel
