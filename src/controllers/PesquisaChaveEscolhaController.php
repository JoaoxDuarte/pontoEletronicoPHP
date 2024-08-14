<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/PesquisaChaveEscolhaModel.php" );


/**
 * @class PesquisaChaveEscolhaController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : servativ
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - inc/controllers
 * @Arquivo   - PesquisaChaveEscolhaController.php
 *
 */
class PesquisaChaveEscolhaController
{
  /*
   * Atributos
   */
  public $pesquisaChaveEscolhaModel = NULL;
  //public $pesquisaChaveEscolhaView  = NULL;

  public function __construct()
  {
    # instancia
    $this->pesquisaChaveEscolhaModel = new PesquisaChaveEscolhaModel();
    //$this->pesquisaChaveEscolhaView  = new PesquisaChaveEscolhaView();
  }

  /**
   * @info Carrega contéudo de "chave"
   *
   * @param void
   * @return string
   */
  public function getChave()
  {
    return $this->pesquisaChaveEscolhaModel->getChave();
  }

  /**
   * @info Carrega contéudo de "escolha"
   *
   * @param void
   * @return string
   */
  public function getEscolha()
  {
    return $this->pesquisaChaveEscolhaModel->getEscolha();
  }

  /**
   * @info Carrega contéudo de "var1"
   *
   * @param void
   * @return string
   */
  public function getChaveVar1()
  {
    return $this->pesquisaChaveEscolhaModel->getChaveVar1();
  }

  /**
   * @info Carrega contéudo de "var2"
   *
   * @param void
   * @return string
   */
  public function getEscolhaVar2()
  {
    return $this->pesquisaChaveEscolhaModel->getEscolhaVar2();
  }

  /**
   * @info Carrega acesso "primeira vez"
   *
   * @param void
   * @return boolean
   */
  public function getPrimeiraVez()
  {
    return $this->pesquisaChaveEscolhaModel->getPrimeiraVez();
  }

  /**
   * @info Carrega dados passados por
   *       $_GET/$_POST/$_REQUEST
   *
   * @param void
   * @return void
   */
  public function argumentosGet()
  {
    return $this->pesquisaChaveEscolhaModel->argumentosGet();
  }

  /**
   * @info Utilizada para carregar o SQL gerado para 
   *       impressao sua alimentação será via ajax 
   *       (jquery.js), a chamada encontra-se no sorttable.js
   * 
   * @param void
   */
  public function dadosParaPesquisa()
  {
    return $this->pesquisaChaveEscolhaModel->dadosParaPesquisa();
  }
  
  /*
   * @info Seleciona registros com base no campo e dados informados
   *
   * @param string $var1    Campo de pesquisar
   * @param string $var2    Valor a pesquisar
   * @param string $groupby Campo a agrupar
   * @return  object  Resultado da pesquisa
   *
   * @author Edinalvo Rosa
   */
  public function pesquisaChaveEscolha($var1='', $var2='', $groupby="siape")
  {
    return $this->pesquisaChaveEscolhaModel->pesquisaChaveEscolha($var1, $var2, $groupby);
  }
}