<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabPlantoesModel.php" );
include_once( "src/views/TabPlantoesView.php" );
include_once( "src/controllers/TabEscalasController.php" );


/**
 * @class TabPlantoesController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : plantoes
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - inc/controllers
 * @Arquivo   - TabPlantoesController.php
 *
 */

class TabPlantoesController
{
    /*
    * Atributos
    */
    public $tabPlantoesModel     = NULL;
    public $tabPlantoesView      = NULL;
    public $tabEscalasController = NULL;

    public function __construct()
    {
        # instancia
        $this->tabPlantoesModel     = new TabPlantoesModel();
        $this->tabPlantoesView      = new TabPlantoesView();
        $this->tabEscalasController = new TabEscalasController(); 
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function insert($alterar)
    {
        return $this->tabPlantoesModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar)
    {
        return $this->tabPlantoesModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id )
    {
        $oPlantao = $this->tabPlantoesModel->registrosPlantoesPorID($id);
        $descricao = $oPlantao->fetch_object()->descricao;
    
        $dados = $this->tabPlantoesModel->registrosPlantoesServidoresPorId( $id );
        $num_rows = $dados->num_rows();
        
        $retorno = $this->tabPlantoesModel->delete( $id, $num_rows );
        
        return $this->tabPlantoesView->deleteView($retorno, $descricao);
    }


    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosPlantoes()
    {
        return $this->tabPlantoesModel->registrosPlantoes();
    }


    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosPlantoesPorID($id)
    {
        return $this->tabPlantoesModel->registrosPlantoesPorID($id);
    }


    /*
     * @info Registros Plantoes por escala
     *
     * @param integer
     * @return object
     */
    public function registrosPlantoesPorEscala($id)
    {
        return $this->tabPlantoesModel->registrosPlantoesPorEscala($id);
    }


    /**
     * @info Lista os registros Isen��o de Ponto
     *
     * @param void
     * @return void
     */
    public function listaRegistrosPlantoes()
    {
        $this->tabPlantoesView->listaRegistrosPlantoes( $this );
    }


    /**
     * @info Lista tipos de grupos para indicar Isen��o de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function listaEscalas($tipo)
    {
        $oDBase = $this->tabEscalasController->registrosEscalas();
        $opcoes = $this->tabPlantoesModel->listaEscalas( $oDBase );
        $this->tabPlantoesView->listaEscalas( $opcoes, $tipo );
    }


    /**
     * @info Formul�rio de entrada de dados
     *
     * @param void
     * @return string HTML
     */
    public function formularioCadastroPlantao( $form, $retorno )
    {
        $oDados = $this->registrosPlantoesPorID($_GET['id']);
        $dados  = $oDados->fetch_object();

        $oDBase = $this->tabEscalasController->registrosEscalas();
        $opcoes = $this->tabPlantoesModel->listaEscalas( $oDBase );
        
        $this->tabPlantoesView->formularioCadastroPlantao( $dados, $opcoes, $form, $retorno );
    }

} // END class TabPlantoesController
