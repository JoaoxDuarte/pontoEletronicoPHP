<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabGrevesPeriodosModel.php" );
include_once( "src/views/TabGrevesPeriodosView.php" );
include_once( "src/controllers/DadosServidoresController.php" );


/**
 * @class TabGrevesPeriodosController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : greves_periodos
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabGrevesPeriodosController.php
 *
 */

class TabGrevesPeriodosController
{
    /*
    * Atributos
    */
    public $tabGrevesPeriodosModel    = NULL;
    public $tabGrevesPeriodosView     = NULL;
    public $dadosServidoresController = NULL;

    public function __construct()
    {
        # instancia
        $this->tabGrevesPeriodosModel    = new TabGrevesPeriodosModel();
        $this->tabGrevesPeriodosView     = new TabGrevesPeriodosView();
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
        return $this->tabGrevesPeriodosModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar=null)
    {
        return $this->tabGrevesPeriodosModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id=null )
    {
        $retorno = $this->tabGrevesPeriodosModel->delete( $id );
        
        return $this->tabGrevesPeriodosView->deleteView($retorno, $descricao);
    }


    /*
     * @info SQL Padrão
     *
     * @param $id ID do registro
     * @return object
     */
    public function grevesPeriodoPorCarreira($carreira=null, $ocor=null, $dtini=null, $dtfim=null)
    {
        return $this->tabGrevesPeriodosModel->grevesPeriodoPorCarreira( $carreira, $ocor, $dtini, $dtfim );
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosGreves()
    {
        return $this->tabGrevesPeriodosModel->registrosGreves();
    }


    /*
     * @info Registros greves periodos
     *
     * @param void
     * @return json
     */
    public function registrosGrevesRetornoAjax()
    {
        $this->tabGrevesPeriodosModel->registrosGrevesRetornoAjax();
    }

    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosGrevesPorID($id=null)
    {
        return $this->tabGrevesPeriodosModel->registrosGrevesPorID($id);
    }


    /**
     * @info Lista os registros Isenção de Ponto
     *
     * @param void
     * @return void
     */
    public function listaRegistrosGreves()
    {
        $this->tabGrevesPeriodosView->listaRegistrosGreves( $this );
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

        $this->tabGrevesPeriodosView->showLista( $retorno );
    }


    /**
     * @info Exibe a Formulário para inclusão de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioIncluirGreves()
    {
        if(!empty($_POST) && !empty($_POST['codigo']))
        {
            $retorno = $this->insert($_POST);
        }

        $this->tabGrevesPeriodosView->showFormularioIncluirGreves( $retorno );
    }


    /**
     * @info Exibe a Formulário para alteração de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioAlterarGreves()
    {
        if(!empty($_POST) && !empty($_POST['id']))
        {
            $_GET['greve_periodo'] = $_POST['id'];
            $retorno = $this->update($_POST);
        }
        $dados  = $this->registrosGrevesPorID($_GET['greve_periodo']);
        $this->tabGrevesPeriodosView->showFormularioAlterarGreves( $dados, $retorno);
    }
    
} // END class TabGrevesPeriodosController
