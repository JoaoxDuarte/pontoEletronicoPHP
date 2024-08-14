<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabEscalasModel.php" );
include_once( "src/views/TabEscalasView.php" );
include_once( "src/controllers/TabPlantoesController.php" );


/**
 * @class TabEscalasController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : escalas
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - inc/controllers
 * @Arquivo   - TabEscalasController.php
 *
 */

class TabEscalasController
{
    /*
    * Atributos
    */
    public $tabEscalasModel = NULL;
    public $tabEscalasView  = NULL;

    public function __construct()
    {
        # instancia
        $this->tabEscalasModel = new TabEscalasModel();
        $this->tabEscalasView  = new TabEscalasView();
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function insert($alterar)
    {
        return $this->tabEscalasModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar)
    {
        return $this->tabEscalasModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id )
    {
        $oEscalas = $this->tabEscalasModel->registrosEscalasPorID($id);
        $descricao = $oEscalas->fetch_object()->descricao;
    
        $oPlantoes = new TabPlantoesController();
        $dados = $oPlantoes->registrosPlantoesPorEscala( $id );
        $num_rows = $dados->num_rows();

        // efetua o "delete", se for o caso
        $retorno = $this->tabEscalasModel->delete( $id, $num_rows, $descricao );
        
        $this->tabEscalasView->deleteView($retorno, $descricao);
    }


    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosEscalas()
    {
        return $this->tabEscalasModel->registrosEscalas();
    }


    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosEscalasPorID($id)
    {
        return $this->tabEscalasModel->registrosEscalasPorID($id);
    }


    /**
     * @info Lista os registros Isen��o de Ponto
     *
     * @param void
     * @return void
     */
    public function listaRegistrosEscalas()
    {
        $this->tabEscalasView->listaRegistrosEscalas( $this );
    }


    /**
     * @info Exibe o formul�rio
     *
     * @param void
     * @return void
     */
    public function showFormularioLista()
    {
        $dados = $this->tabEscalasModel->registrosEscalas();
        $this->tabEscalasView->showFormularioLista( $dados );
    }


    /**
     * @info Exibe javascript
     *
     * @param void
     * @return void
     */
    public function showFormularioListaJavascript()
    {
        $dados = $this->tabEscalasModel->registrosEscalas();
        $this->tabEscalasView->showFormularioListaJavascript( $dados );
    }

} // END class TabEscalasController
