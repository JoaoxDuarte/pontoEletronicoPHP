<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/views/TabPlantoesServidoresView.php" );
include_once( "src/models/TabPlantoesServidoresModel.php" );
include_once( "src/controllers/TabPlantoesController.php" );
include_once( "src/controllers/TabEscalasController.php" );

/**
 * @class TabPlantoesServidoresController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : plantoes_servidores
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - inc/controllers
 * @Arquivo   - TabPlantoesServidoresController.php
 *
 */

class TabPlantoesServidoresController
{
    /*
    * Atributos
    */
    public $tabPlantoesServidoresModel = NULL;
    public $tabPlantoesServidoresView  = NULL;
    public $tabPlantoesController      = NULL;
    public $tabEscalasController       = NULL;

    public function __construct()
    {
        # instancia
        $this->tabPlantoesServidoresModel = new TabPlantoesServidoresModel();
        $this->tabPlantoesServidoresView  = new TabPlantoesServidoresView();
        $this->tabPlantoesController      = new TabPlantoesController();
        $this->tabEscalasController       = new TabEscalasController();
    }

    
    /**
     * 
     * @param array $post
     * @return object/boolean
     */
    public function gravar()
    {
      return $this->tabPlantoesServidoresModel->gravar();
    }

    public function registrosPlantoesServidoresPorId( $id )
    {
        return $this->$tabPlantoesController->registrosPlantoesPorID($id);
    }


    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosPlantoesServidores()
    {
        return $this->tabPlantoesServidoresModel->registrosPlantoesServidores();
    }


    /*
     * @info Registros Plantoes
     *
     * @param void
     * @return object
     */
    public function registrosPlantoes()
    {
        return $this->tabPlantoesServidoresModel->registrosPlantoes();
    }


    /**
     * @info Lista plant�es cadastrados
     *
     * @param void
     * @return string HTML
     */
    public function listaPlantoes($siape,$tipo)
    {
        $oDBase = $this->registrosPlantoes();
        $opcoes = $this->tabPlantoesServidoresModel->listaPlantoes( $oDBase );
        return $this->tabPlantoesServidoresView->listaPlantoes( $opcoes, $siape, $tipo );
    }

} // END class TabPlantoesServidoresController
