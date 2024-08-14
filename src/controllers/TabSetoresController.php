<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabSetoresModel.php" );


/**
 * @class TabSetoresController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : diversas
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabSetoresController.php
 *
 */
class TabSetoresController
{
    /*
     * Atributos
     */
    private $conexao;

    public $tabSetoresModel = NULL;
    //public $tabSetoresView  = NULL;


    public function __construct()
    {
      # instancia
      $this->tabSetoresModel = new TabSetoresModel();
      //$this->tabSetoresView  = new TabSetoresView();
    }


    /**
     * @info Seleção dos registros
     *
     * @param string $setor Setor
     * @return object
     */
    public function dadosUnidadePorCodigo($setor=null)
    {
      return $this->tabSetoresModel->dadosUnidadePorCodigo($setor);
    }


    /**
     * @info Seleção dos registros por upag
     *
     * @param string $upag Código da UPAG
     * @return object
     */
    public function selecionaUnidadesPorUpag($upag=null)
    {
      return $this->tabSetoresModel->selecionaUnidadesPorUpag($upag);
    }


    /**
     * @info Alterar dados do setor
     *
     * @param array $dados Dados da UORG
     * @param string $codigo Código da UORG
     * @return resource
     */
    function updateSetor($dados , $codigo)
    {
        return $this->tabSetoresModel->updateSetor($dados, $codigo='');
    }


    /**
     * @info Incluir dados do setor
     *
     * @param array $dados Dados da UORG
     * @return resource
     */
    function cadastrarSetor($dados)
    {
        return $this->tabSetoresModel->cadastrarSetor($dados);
    }
}