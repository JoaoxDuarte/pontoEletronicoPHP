<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabUsuariosModel.php" );


/**
 * @class TabUsuariosController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : diversas
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
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
     * @info Seleção dos registros
     *
     * @param string $siape Matrícula do servidor
     * @return object
     */
    public function dadosUsuarioPorID($siape=null)
    {
      return $this->tabUsuariosModel->dadosUsuarioPorID($siape);
    }


    /**
     * @info Alterar dados do usuário
     *
     * @param array $dados Dados do usuário
     * @param string $siape Matrícula do servidor
     * @return resource
     */
    function update($siape = null, $dados = null)
    {
        return $this->tabUsuariosModel->update( $siape, $dados );
    }


    /**
     * @info Incluir dados do usuário
     *
     * @param array $dados Dados da UORG
     * @return resource
     */
    function insert($dados)
    {
        return $this->tabUsuariosModel->insert($dados);
    }
}