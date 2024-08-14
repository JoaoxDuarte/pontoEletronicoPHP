<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabUsuariosModel.php" );


/**
 * @class TabUsuariosController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : diversas
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - TabUsuariosController.php
 *
 */
class TabUsuariosController
{
    /*
     * Atributos
     */
    private $conexao;

    public $tabUsuariosModel = NULL;
    //public $tabUsuariosView  = NULL;


    public function __construct()
    {
      # instancia
      $this->conexao          = new DataBase();
      $this->tabUsuariosModel = new TabUsuariosModel();
      //$this->tabUsuariosView  = new TabUsuariosView();
    }


    /**
     * @info Sele��o dos registros
     *
     * @param string $siape Matr�cula do servidor
     * @return object
     */
    public function dadosUsuarioPorID($siape=null)
    {
      return $this->tabUsuariosModel->dadosUsuarioPorID($siape);
    }


    /**
     * @info Alterar dados do usu�rio
     *
     * @param array $dados Dados do usu�rio
     * @param string $siape Matr�cula do servidor
     * @return resource
     */
    function update($siape = null, $dados = null)
    {
        return $this->tabUsuariosModel->update( $siape, $dados );
    }


    /**
     * @info Incluir dados do usu�rio
     *
     * @param array $dados Dados da UORG
     * @return resource
     */
    function insert($dados)
    {
        return $this->tabUsuariosModel->insert($dados);
    }
}