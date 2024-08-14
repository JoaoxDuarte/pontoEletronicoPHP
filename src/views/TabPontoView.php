<?php

// Inicia a sessуo e carrega as funчѕes de uso geral
include_once("config.php");


/**
 * @class TabPontoView
 *        Responsсvel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualizaчуo
 *
 * @info TABELA : pontoMMAAAA
 *       Suas descriчѕes e caracterэsticas
 *
 * @Camada    - View
 * @Diretѓrio - src/views
 * @Arquivo   - TabPontoView.php
 *
 * @author Edinalvo Rosa
 */
class TabPontoView extends formPadrao
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

} // END class TabPontoView
