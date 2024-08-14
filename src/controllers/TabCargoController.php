<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabCargoModel.php" );
include_once( "src/views/TabCargoView.php" );
include_once( "src/controllers/DadosServidoresController.php" );


/**
 * @class TabCargoController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabcargo
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabCargoController.php
 *
 */

class TabCargoController
{
    /*
    * Atributos
    */
    public $tabCargoModel             = NULL;
    public $tabCargoView              = NULL;
    public $dadosServidoresController = NULL;

    public function __construct()
    {
	$path_parts = pathinfo($_SERVER['HTTP_REFERER']);
	$pagina_de_origem = $path_parts['filename'];

        $sufixoPaginasCorretas = array( "chefia", "principal_abertura", "tabcargo", "tabcargo_alterar", "tabcargo_incluir" );

	if (empty($pagina_de_origem) || (!in_array($pagina_de_origem, $sufixoPaginasCorretas)))
	{
            //mensagem( "Problema no acesso a página de Manutenção de Cargos!" );
            //replaceLink('chefia.php');
        }
        
        # instancia
        $this->tabCargoModel             = new TabCargoModel();
        $this->tabCargoView              = new TabCargoView();
        $this->dadosServidoresController = new DadosServidoresController();
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function insert($alterar=null)
    {
        return $this->tabCargoModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar=null)
    {
        return $this->tabCargoModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id=null )
    {
        $oCargo = $this->tabCargoModel->registrosCargoPorID($id);
        $descricao = $oCargo->fetch_object()->DESC_CARGO;
    
        $dados = $this->dadosServidoresController->selecionaServidorPorCargo( $id );
        $num_rows = $dados->num_rows();
            
        $retorno = $this->tabCargoModel->delete( $id );
        
        return $this->tabCargoView->deleteView($retorno, $descricao);
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosCargo()
    {
        return $this->tabCargoModel->registrosCargo();
    }


    /*
     * @info Registros cargo
     *
     * @param void
     * @return json
     */
    public function registrosCargoRetornoAjax()
    {
        $this->tabCargoModel->registrosCargoRetornoAjax();
    }

    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosCargoPorID($id=null)
    {
        return $this->tabCargoModel->registrosCargoPorID($id);
    }


    /**
     * @info Lista os registros Isenção de Ponto
     *
     * @param void
     * @return void
     */
    public function listaRegistrosCargo()
    {
        $this->tabCargoView->listaRegistrosCargo( $this );
    }


    /**
     * @info Exibe a Lista de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showLista()
    {
        if(!empty($_POST['autorizacao']))
        {
            $retorno = $this->delete( $_POST['id'] );
        }

        $this->tabCargoView->showLista( $retorno );
    }


    /**
     * @info Exibe a Formulário para inclusão de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioIncluirCargo()
    {
        if(!empty($_POST) && !empty($_POST['codigo']))
        {
            $retorno = $this->insert($_POST);
        }

        $this->tabCargoView->showFormularioIncluirCargo( $retorno );
    }


    /**
     * @info Exibe a Formulário para alteração de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioAlterarCargo()
    {
        if(!empty($_POST) && !empty($_POST['id']))
        {
            $_GET['codcargo'] = $_POST['id'];
            $retorno = $this->update($_POST);
        }
        $dados  = $this->registrosCargoPorID($_GET['codcargo']);
        $this->tabCargoView->showFormularioAlterarCargo( $dados, $retorno);
    }
    
} // END class TabCargoController
