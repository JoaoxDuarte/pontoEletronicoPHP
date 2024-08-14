<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabEscalasModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : escalas
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - inc/models
 * @Arquivo   - TabEscalasModel.php
 *
 * @author Edinalvo Rosa
 */
class TabEscalasModel extends formPadrao
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
     * @info Registros Escalas
     *
     * @param void
     * @return object
     */
    public function registrosEscalas()
    {
        $query = "
        SELECT id, jornada, trabalhar, folgar, descricao, ativo
            FROM escalas
                WHERE ativo = 'S'
        ";
        
        $this->conexao->query( $query );

        return $this->conexao;
    }


    /*
     * @info Registros Escalas
     *
     * @param void
     * @return object
     */
    public function registrosEscalasPorID($id)
    {
        $query = "
        SELECT id, jornada, trabalhar, folgar, descricao, ativo
            FROM escalas
                WHERE id = :id
                      AND ativo = 'S'
        ";

        $param = array(
            array( ':id', $id, PDO::PARAM_INT),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }


    /*
     * @info Registros Escalas por jornada, horas trabalhar e horas folgar
     *
     * @param void
     * @return object
     */
    public function registrosEscalasPorGrupo($jornada, $trabalhar, $folgar)
    {
        $query = "
        SELECT id, jornada, trabalhar, folgar, descricao,
               ativo, registro_siape, registro_data
            FROM escalas
                WHERE jornada = :jornada
                      AND trabalhar = :trabalhar
                      AND folgar = :folgar
                      AND ativo = 'S'
                    ORDER BY jornada, trabalhar, folgar
        ";

        $param = array(
            array( ':jornada', $jornada, PDO::PARAM_INT),
            array( ':trabalhar', $trabalhar, PDO::PARAM_INT),
            array( ':folgar', $folgar, PDO::PARAM_INT),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }

    /**
     * @info Inlcui novo registro de Escalas
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var)
    {
        $retorno = '';

        // verifica se já existe item igual
        $dados = $this->registrosEscalasPorGrupo($var['jornada'], $var['trabalhar'], $var['folgar']);

        if ($dados->num_rows() > 0)
        {
            $retorno = 'ja_existe';
        }
        else
        {
            $this->conexao->query("
            INSERT INTO escalas
                (jornada, trabalhar, folgar, descricao, ativo,
                registro_siape, registro_data)
                    VALUES (:jornada, :trabalhar, :folgar, :descricao, :ativo,
                            :registro_siape, NOW())
            ",
            array(
                array(":jornada",   $var['jornada'],   PDO::PARAM_INT),
                array(":trabalhar", $var['trabalhar'], PDO::PARAM_INT),
                array(":folgar",    $var['folgar'],    PDO::PARAM_INT),
                array(":descricao", $var['descricao'], PDO::PARAM_STR),
                array(":ativo",     $var['ativo'],     PDO::PARAM_STR),
                array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
            ));

            $affected_rows = ($this->conexao->affected_rows() > 0);

            if ($affected_rows)
            {
                //$this->historico(0,'I'); // Inclusão realizada
                registraLog("Item de Escalas (".$var['codigo'].") registrado com sucesso!");
                $retorno = 'gravou';
            }
            else
            {
                registraLog("Item de Escalas (".$var['codigo'].") NÃO foi registrado!");
                $retorno = 'nao_gravou';
            }
        }
        return $retorno;
    }

    /**
     * @info Inlcui novo registro de Escalas
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function update($var)
    {
        $retorno = '';

        // verifica se já existe item igual
        $dados = $this->registrosEscalasPorID($var['id']);

        if ($dados->num_rows() == 0)
        {
            $retorno = 'nao_existe';
        }
        else
        {
            $this->historico($var['id'],'A'); // Alteração realizada

            $this->conexao->query("
            UPDATE escalas
                SET jornada        = :jornada,
                    trabalhar      = :trabalhar,
                    folgar         = :folgar,
                    descricao      = :descricao, 
                    ativo          = :ativo,
                    registro_siape = :registro_siape,
                    registro_data  = NOW()
                        WHERE id = :id
            ",
            array(
                array(":id",        $var['id'],        PDO::PARAM_INT),
                array(":jornada",   $var['jornada'],   PDO::PARAM_INT),
                array(":trabalhar", $var['trabalhar'], PDO::PARAM_INT),
                array(":folgar",    $var['folgar'],    PDO::PARAM_INT),
                array(":descricao", $var['descricao'], PDO::PARAM_STR),
                array(":ativo",     $var['ativo'],     PDO::PARAM_STR),
                array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
            ));

            $affected_rows = ($this->conexao->affected_rows() > 0);

            if ($affected_rows)
            {
                registraLog("Alterado com sucesso (".$var['descricao'].")!");
                $retorno = 'gravou';
            }
            else
            {
                registraLog("Alteração NÃO foi realizada (".$var['descricao'].")!");
                $retorno = 'nao_gravou';
            }
        }

        return $retorno;
    }


    /**
     * @info Desabilita o registro, ativo = 'N'
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id, $num_rows, $descricao )
    {
        $resultado = "error";
        
        if ($num_rows > 0)
        {
            $resultado = "warning";
        }
        else
        {
            $this->conexao->query("
            UPDATE escalas
                SET ativo          = :ativo,
                    registro_siape = :registro_siape,
                    registro_data  = NOW()
                        WHERE id = :id
            ",  
            array(
                array(":id",        $id, PDO::PARAM_INT),
                array(":ativo",     'N', PDO::PARAM_STR),
                array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
            ));

            $affected_rows = ($this->conexao->affected_rows() > 0);

            if ($affected_rows)
            {
                $resultado = "success";
                registraLog("Deletou o registro da escala de ID ".$id." (".$descricao.")");
                $this->historico($id,'E');
            }
        }

        return $resultado;
    }

    /**
     * @info Histórico
     *
     * @param array $var
     * @return void
     */
    public function historico($id,$oper)
    {
        $this->conexao->query("
        INSERT INTO escalas_historico
            SELECT 0, :id, jornada, trabalhar, folgar, descricao, 
                   ativo, registro_siape, registro_data, :oper, 
                   :operador_siape, NOW()
                    FROM escalas
                        WHERE escalas.id = :id
        ",
        array(
            array(":id",   $id,   PDO::PARAM_INT),
            array(":oper", $oper, PDO::PARAM_STR),
            array(":operador_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        ));
    }

} // END class TabEscalasModel
