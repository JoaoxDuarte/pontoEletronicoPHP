<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");


/**
 * @class TabPontoView
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : pontoMMAAAA
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - View
 * @Diret�rio - src/views
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
