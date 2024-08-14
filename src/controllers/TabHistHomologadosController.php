<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabHistHomologadosModel.php" );
include_once( "src/views/TabHistHomologadosView.php" );
include_once( "src/controllers/DadosServidoresController.php" );


/**
 * @class TabHistHomologadosController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : homologados_historico
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - TabHistHomologadosController.php
 *
 */

class TabHistHomologadosController
{
    /*
    * Atributos
    */
    public $tabHistHomologadosModel   = NULL;
    public $tabHistHomologadosView    = NULL;
    public $dadosServidoresController = NULL;

    public function __construct()
    {
        # instancia
        $this->tabHistHomologadosModel   = new TabHistHomologadosModel();
        $this->tabHistHomologadosView    = new TabHistHomologadosView();
        $this->dadosServidoresController = new DadosServidoresController();
    }


    /*
     * @info Registros cargo
     *
     * @param void
     * @return json
     */
    public function registrosCargoRetornoAjax()
    {
        $this->tabHistHomologadosModel->registrosCargoRetornoAjax();
    }


    /**
     * @info Exibe a Lista de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showLista()
    {
        $this->tabHistHomologadosView->showLista();
    }
    
} // END class TabHistHomologadosController
