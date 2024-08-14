<?php

// Inicia a sessуo e carrega as funчѕes de uso geral
include_once("config.php");
include_once( 'class_formpadrao.php' );


/**
 * @class TabFacultativo172View
 *        Responsсvel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualizaчуo
 *
 * @info TABELA : tabfacultativo172
 *       Suas descriчѕes e caracterэsticas
 *
 * @Camada    - View
 * @Diretѓrio - src/models
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
