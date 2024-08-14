<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabOcorrSiapeModel.php" );


/**
 * @class TabOcorrSiapeController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : diversas
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
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
     * @info Sele��o dos registros por ID
     *
     * @param string $id C�digo de ocorr�ncia SIAPE
     * @return object
     */
    public function selectOcorr()
    {
      return $this->tabOcorrSiapeModel->selectOcorr();
    }


    /**
     * @info Sele��o dos registros por ID
     *
     * @param string $id C�digo de ocorr�ncia SIAPE
     * @return object
     */
    public function selectOcorrSiapePorID($id=null)
    {
      return $this->tabOcorrSiapeModel->selectOcorrSiapePorID($id);
    }


    /**
     * @info Sele��o das ocorr�ncias de aposentados
     *
     * @return object
     */
    public function carregarOcorrAposentados()
    {
      return $this->tabOcorrSiapeModel->carregarOcorrAposentados();
    }


    /**
     * @info Sele��o dos registros INSTITUIDOR DE PENS�O
     *
     * @return object
     */
    public function carregarOcorrInstituidor()
    {
      return $this->tabOcorrSiapeModel->carregarOcorrInstituidor();
    }
}