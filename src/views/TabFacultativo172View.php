<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( 'class_formpadrao.php' );


/**
 * @class TabFacultativo172View
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabfacultativo172
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/models
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
