<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabBancoDeCompensacoesModel.php" );
include_once( "src/views/TabBancoDeCompensacoesView.php" );


/**
 * @class TabBancoDeCompensacoesController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : banco_de_horas
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - TabBancoDeCompensacoesController.php
 *
 */

class TabBancoDeCompensacoesController
{
    /*
    * Atributos
    */
    public $tabBancoDeCompensacoesModel = NULL;
    public $tabBancoDeCompensacoesView  = NULL;

    public function __construct()
    {
        # instancia
        $this->tabBancoDeCompensacoesModel = new TabBancoDeCompensacoesModel();
        $this->tabBancoDeCompensacoesView  = new TabBancoDeCompensacoesView();
    }

    
    /**
     * @info Exibe quadro de saldo
     *
     * @param void
     * @return void
     */
    public function DadosAcumuloHoras($siape = null, $dia = null)
    {
        return $this->tabBancoDeCompensacoesModel->DadosAcumuloHoras( $siape, $dia );
    }


    /**
     * @info Exibe quadro de saldo
     *
     * @param void
     * @return void
     */
    public function showQuadroDeSaldo( $siape = null, $mes = null, $ano = null, $status = null )
    {
        //$dados = $this->DadosAcumuloHoras( $siape, $dia );
        $this->tabBancoDeCompensacoesView->showQuadroDeSaldo( $siape = null, $mes = null, $ano = null, $status = null );
    }

} // END class TabBancoDeCompensacoesController
