<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabOcorrSiapeModel.php" );


/**
 * @class TabOcorrSiapeController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : diversas
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabOcorrSiapeController.php
 *
 */
class TabOcorrSiapeController
{
    /*
     * Atributos
     */
    private $conexao;

    public $tabOcorrSiapeModel = NULL;


    public function __construct()
    {
      # instancia
      $this->conexao            = new DataBase();
      $this->tabOcorrSiapeModel = new TabOcorrSiapeModel();
    }


    /**
     * @info Seleção dos registros por ID
     *
     * @param string $id Código de ocorrência SIAPE
     * @return object
     */
    public function selectOcorr()
    {
      return $this->tabOcorrSiapeModel->selectOcorr();
    }


    /**
     * @info Seleção dos registros por ID
     *
     * @param string $id Código de ocorrência SIAPE
     * @return object
     */
    public function selectOcorrSiapePorID($id=null)
    {
      return $this->tabOcorrSiapeModel->selectOcorrSiapePorID($id);
    }


    /**
     * @info Seleção das ocorrências de aposentados
     *
     * @return object
     */
    public function carregarOcorrAposentados()
    {
      return $this->tabOcorrSiapeModel->carregarOcorrAposentados();
    }


    /**
     * @info Seleção dos registros INSTITUIDOR DE PENSÃO
     *
     * @return object
     */
    public function carregarOcorrInstituidor()
    {
      return $this->tabOcorrSiapeModel->carregarOcorrInstituidor();
    }
}