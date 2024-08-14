<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * Responsável por gerenciar o fluxo de dados entre
 * a camada de modelo e a de visualização
 *
 *  TABELA : plantoes
 *       Suas descrições e características
 * 
 * @class TabPlantoesServidoresView
 *
 * @Camada    - View
 * @Diretório - inc/models
 * @Arquivo   - TabPlantoesServidoresView.php
 *
 * @author Edinalvo Rosa
 */
class TabPlantoesServidoresView extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = null;

    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    public function __construct()
    {
        parent::formPadrao();
                
    }

    
    /**
     * Monta <select> com tipos para isenção
     * 
     * @param array $opcoes
     * @param string/null $tipo
     * @return HTML
     */
    public function listaPlantoes( $opcoes, $siape, $tipo = null )
    {
      $opcoes_tipo  = "";
      $opcoes_sigla = "";
      $selected     = "";
        
      foreach($opcoes AS $value)
      {
        if ( !empty($value['id']) && $value['id'] == $tipo)
        {
          $opcoes_sigla = $value['escala_sigla'];
          $selected     = " selected";
        }
        
        $opcoes_tipo .= "
          <option value='" . $value['id'] ."'" 
            . $selected . " data-escala='" . $value['escala_sigla'] . "'>" 
            . $value['descricao'] . "
          </option>
        ";
      }

      $select = "
      <select id=\"id_plantao['".$siape."']\" name=\"id_plantao['".$siape."']\" class=\"form-control select2-single\" onchange=\"javascript:exibeEscala(this,'".$siape."');\">
        " . $opcoes_tipo . "
      </select>";
      
      return array('opcoes' => $select, 'sigla' => $opcoes_sigla);
    }

} // END class TabPlantoesServidoresView
