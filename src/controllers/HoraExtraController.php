<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/HoraExtraModel.php" );
//include_once("hora_extra_autorizacao_funcoes.php");


/**
 * @class HoraExtraController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : autorizacoes_hora_extra
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - inc/controllers
 * @Arquivo   - HoraExtraController.php
 *
 */
class HoraExtraController
{
  /*
   * Atributos
   */
  public $horaExtraModel = NULL;
  //public $horaExtraView  = NULL;

  public function __construct()
  {
    # instancia
    $this->horaExtraModel = new HoraExtraModel();
    //$this->horaExtraView  = new HoraExtraView();
  }


  /**
   * @info Sele;áo dos registros
   * 
   * @param string $var1 Chave de pesquisa
   * @param string $var2 Dados a pesquisar
   * @param string $groupby Campos a agrupar
   * @return object
   */
  public function pesquisaChaveEscolha($var1='', $var2='', $groupby="siape")
  {
    return $this->horaExtraModel->pesquisaChaveEscolha($var1, $var2, $groupby);
  }
}
