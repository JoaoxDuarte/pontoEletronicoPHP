<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabRecessoFimDeAnoModel.php" );
include_once( "src/views/TabRecessoFimDeAnoView.php" );


/**
 * @class TabRecessoFimDeAnoController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : tabrecesso_fimdeano
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - TabRecessoFimDeAnoController.php
 *
 */

class TabRecessoFimDeAnoController
{
    /*
    * Atributos
    */
    public $tabRecessoFimDeAnoModel = NULL;
    public $tabRecessoFimDeAnoView  = NULL;

    public function __construct()
    {
        # instancia
        $this->tabRecessoFimDeAnoModel = new TabRecessoFimDeAnoModel();
        $this->tabRecessoFimDeAnoView  = new TabRecessoFimDeAnoView();
    }

    
    /**
     * @info Lista dados do per�odo
     *
     * @param object $obj Ano Inicial
     * @return result
     */
    public function dadosPeriodoRecesso( $periodo = null )
    {
        return $this->tabRecessoFimDeAnoModel->dadosPeriodoRecesso( $periodo );
    }

    
    /**
     * @info Carrega registros
     *
     * @param string $siape Matr�cula
     * @param result $recesso Dados do per�odo do recesso
     * @param string $periodo Per�odo do recesso. Ex.2018/2019
     * @return array
     */
    public function registrosRecessoFrequencia( $siape = null, $recesso = null, $periodo = null )
    {
        if ( (is_null($recesso) || !is_object($recesso)) && is_string($periodo) )
        {
            $recesso = $this->dadosPeriodoRecesso( $periodo );
        }

        return $this->tabRecessoFimDeAnoModel->registrosRecessoFrequencia( $siape, $recesso );
    }


    /**
     * @info Exibe o formul�rio
     *
     * @param void
     * @return void
     */
    public function showRecessoQuadroDemonstrativo($siape = null, $periodo = null)
    {
        $recesso = $this->dadosPeriodoRecesso( $periodo );
        $dados   = $this->registrosRecessoFrequencia( $siape, $recesso, $periodo );
        $this->tabRecessoFimDeAnoView->showRecessoQuadroDemonstrativo( $dados, $periodo );
    }

} // END class TabRecessoFimDeAnoController
