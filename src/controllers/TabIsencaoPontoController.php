<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabIsencaoPontoModel.php" );
include_once( "src/views/TabIsencaoPontoView.php" );


/**
 * @class TabIsencaoPontoController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : tabisencao_ponto
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - inc/controllers
 * @Arquivo   - TabIsencaoPontoController.php
 *
 */

class TabIsencaoPontoController
{
    /*
    * Atributos
    */
    public $tabIsencaoPontoModel = NULL;
    public $tabIsencaoPontoView  = NULL;

    public function __construct()
    {
        # instancia
        $this->tabIsencaoPontoModel = new TabIsencaoPontoModel();
        $this->tabIsencaoPontoView  = new TabIsencaoPontoView();
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function insert($alterar=null)
    {
        return $this->tabIsencaoPontoModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar=null)
    {
        return $this->tabIsencaoPontoModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id=null )
    {
        return $this->tabIsencaoPontoModel->delete( $id );
    }


    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosIsencaoDePonto()
    {
        return $this->tabIsencaoPontoModel->registrosIsencaoDePonto();
    }


    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosIsencaoDePontoPorID($id=null)
    {
        return $this->tabIsencaoPontoModel->registrosIsencaoDePontoPorID($id);
    }


    /**
     * @info Lista os registros Isen��o de Ponto
     *
     * @param void
     * @return void
     */
    public function listaRegistrosIsencaoDePonto()
    {
        $this->tabIsencaoPontoView->listaRegistrosIsencaoDePonto( $this );
    }


    /**
     * @info Lista tipos de grupos para indicar Isen��o de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function listaTipoParaIsencao($tipo=null)
    {
        $opcoes = $this->tabIsencaoPontoModel->tipoParaIsencao();
        $this->tabIsencaoPontoView->listaTipoParaIsencao( $opcoes, $tipo );
    }


    /**
     * @info Lista tabelas que hospedam as situa��es de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function listaTabelas()
    {
        $opcoes = $this->tabIsencaoPontoModel->listaTabelas();
        $this->tabIsencaoPontoView->listaTabelas( $opcoes );
    }


    /**
     * @info Exibe a Lista de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showListaIsencaoPonto()
    {
        if(!empty($_POST['autorizacao']))
        {
            $this->delete( $_POST['id'] );
            exit;
        }

        $dados = $this->tabIsencaoPontoModel->registrosIsencaoDePonto();
        $this->tabIsencaoPontoView->showListaIsencaoPonto( $dados );
    }


    /**
     * @info Exibe a Formul�rio para inclus�o de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioIncluirIsencaoPonto()
    {
        if(!empty($_POST) && $_POST['id']=='0')
        {
            $retorno = $this->insert($_POST);
        }

        $opcoes = $this->tabIsencaoPontoModel->tipoParaIsencao();
        $this->tabIsencaoPontoView->showFormularioIncluirIsencaoPonto( $retorno, $opcoes );
    }


    /**
     * @info Exibe a Formul�rio para altera��o de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioAlterarIsencaoPonto()
    {
        if(!empty($_POST) && !empty($_POST['codigo']))
        {
            $retorno = $this->update($_POST);
        }

        $opcoes = $this->tabIsencaoPontoModel->tipoParaIsencao();
        $dados  = $this->registrosIsencaoDePontoPorID($_POST['id']);
        $this->tabIsencaoPontoView->showFormularioAlterarIsencaoPonto( $dados, $retorno, $opcoes );
    }
    
} // END class TabIsencaoPontoController
