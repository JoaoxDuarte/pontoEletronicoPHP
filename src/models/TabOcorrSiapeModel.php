<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");


/**
 * @class TabOcorrSiapeModel
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : tabsetor
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Model
 * @Diret�rio - src/models
 * @Arquivo   - TabOcorrSiapeModel.php
 *
 */
class TabOcorrSiapeModel
{
    /*
     * Atributos
     */
    /* object */ public $conexao;
  
  
    public function __construct()
    {
        # Faz conex�o
        //$this->conexao = new DataBase();
    }

    
    /**
     * @info Sele��o todos os registros
     *
     * @param void
     * @return object
     */
    public function selectOcorr()
    {
        $this->conexao = new DataBase();
        $this->conexao->setMensagem( "Problemas no acesso a Tabela OCORR�NCIAS SIAPE (E200064.".__LINE__.")");
        $this->conexao->query( "
        SELECT
            tabocorr.cod_ocorr, 
            tabocorr.desc_ocorr
        FROM
            tabocorr
        ");

        return $this->conexao;
    }

    
    /**
     * @info Sele��o dos registros por ID
     *
     * @param string $id C�digo de ocorr�ncia SIAPE
     * @return object
     */
    public function selectOcorrSiapePorID($id=null)
    {
        $this->conexao = new DataBase();
        $this->conexao->setMensagem( "Problemas no acesso a Tabela OCORR�NCIAS SIAPE (E200064.".__LINE__.")");
        $this->conexao->query( "
        SELECT
            tabocorr.cod_ocorr, 
            tabocorr.desc_ocorr
        FROM
            tabocorr
        WHERE
            tabocorr.cod_ocorr = :cod_ocorr
        ", array(
            array( ':cod_ocorr', $id, PDO::PARAM_STR ),
        ));

        return $this->conexao;
    }


    /**
     * @info Sele��o dos registros APOSENTADOS
     *
     * @param void
     * @return object
     */
    public function carregarOcorrAposentados()
    {
        $this->conexao = new DataBase();
        $this->conexao->setMensagem("Problemas no acesso a Tabela OCORR�NCIAS SIAPE (E000064.".__LINE__.")");
        $this->conexao->query( "
        SELECT
            tabocorr.cod_ocorr, 
            tabocorr.desc_ocorr
        FROM
            tabocorr
        WHERE
            UPPER(tabocorr.desc_ocorr) NOT LIKE 'INSTITUIDOR%'
            AND LEFT(tabocorr.cod_ocorr,2) IN ('02','05')
        ");

        $codigos = array();

        while ($rows = $this->conexao->fetch_object())
        {
            $codigos[] = $rows->cod_ocorr;
        }

        return $codigos;
    }


    /**
     * @info Sele��o dos registros INSTITUIDOR DE PENS�O
     *
     * @param void
     * @return object
     */
    public function carregarOcorrInstituidor()
    {
        $this->conexao = new DataBase();
        $this->conexao->setMensagem("Problemas no acesso a Tabela OCORR�NCIAS SIAPE (E000064.".__LINE__.")");
        $this->conexao->query( "
        SELECT
            tabocorr.cod_ocorr, 
            tabocorr.desc_ocorr
        FROM
            tabocorr
        WHERE
            UPPER(tabocorr.desc_ocorr) LIKE 'INSTITUIDOR%'
        ");

        $codigos = array();

        while ($rows = $this->conexao->fetch_object())
        {
            $codigos[] = $rows->cod_ocorr;
        }

        return $codigos;
    }
}
