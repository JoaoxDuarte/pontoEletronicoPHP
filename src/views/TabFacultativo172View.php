<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( 'class_formpadrao.php' );


/**
 * @class TabFacultativo172View
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : tabfacultativo172
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - View
 * @Diret�rio - src/models
 * @Arquivo   - TabFacultativo172View.php
 *
 * @author Edinalvo Rosa
 */
class TabFacultativo172View extends formPadrao
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
    public function __construct()
    {
        parent::formPadrao();
    }
} // END class TabFacultativo172View
