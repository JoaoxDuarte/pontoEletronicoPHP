<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabHomologadosView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : homologados
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/views
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
