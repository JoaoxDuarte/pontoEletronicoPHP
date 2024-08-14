<?php

// Inicia a sessуo e carrega as funчѕes de uso geral
include_once("config.php");


/**
 * @class TabHomologadosView
 *        Responsсvel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualizaчуo
 *
 * @info TABELA : homologados
 *       Suas descriчѕes e caracterэsticas
 *
 * @Camada    - View
 * @Diretѓrio - src/views
 * @Arquivo   - TabHomologadosView.php
 *
 * @author Edinalvo Rosa
 */
class TabHomologadosView extends formPadrao
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

} // END class TabHomologadosView
