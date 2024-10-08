<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabPontoView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : pontoMMAAAA
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/views
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
