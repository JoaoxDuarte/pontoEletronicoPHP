<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabPlantoesModel.php" );
include_once( "src/views/TabPlantoesView.php" );
include_once( "src/controllers/TabEscalasController.php" );


/**
 * @class TabPlantoesController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : plantoes
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - inc/controllers
 * @Arquivo   - TabPlantoesController.php
 *
 */

class TabPlantoesController
{
    /*
    * Atributos
    */
    public $tabPlantoesModel     = NULL;
    public $tabPlantoesView      = NULL;
    public $tabEscalasController = NULL;

    public function __construct()
    {
        # instancia
        $this->tabPlantoesModel     = new TabPlantoesModel();
        $this->tabPlantoesView      = new TabPlantoesView();
        $this->tabEscalasController = new TabEscalasController(); 
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function insert($alterar)
    {
        return $this->tabPlantoesModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar)
    {
        return $this->tabPlantoesModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id )
    {
        $oPlantao = $this->tabPlantoesModel->registrosPlantoesPorID($id);
        $descricao = $oPlantao->fetch_object()->descricao;
    
        $dados = $this->tabPlantoesModel->registrosPlantoesServidoresPorId( $id );
        $num_rows = $dados->num_rows();
        
        $retorno = $this->tabPlantoesModel->delete( $id, $num_rows );
        
        return $this->tabPlantoesView->deleteView($retorno, $descricao);
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosPlantoes()
    {
        return $this->tabPlantoesModel->registrosPlantoes();
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosPlantoesPorID($id)
    {
        return $this->tabPlantoesModel->registrosPlantoesPorID($id);
    }


    /*
     * @info Registros Plantoes por escala
     *
     * @param integer
     * @return object
     */
    public function registrosPlantoesPorEscala($id)
    {
        return $this->tabPlantoesModel->registrosPlantoesPorEscala($id);
    }


    /**
     * @info Lista os registros Isenção de Ponto
     *
     * @param void
     * @return void
     */
    public function listaRegistrosPlantoes()
    {
        $this->tabPlantoesView->listaRegistrosPlantoes( $this );
    }


    /**
     * @info Lista tipos de grupos para indicar Isenção de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function listaEscalas($tipo)
    {
        $oDBase = $this->tabEscalasController->registrosEscalas();
        $opcoes = $this->tabPlantoesModel->listaEscalas( $oDBase );
        $this->tabPlantoesView->listaEscalas( $opcoes, $tipo );
    }


    /**
     * @info Formulário de entrada de dados
     *
     * @param void
     * @return string HTML
     */
    public function formularioCadastroPlantao( $form, $retorno )
    {
        $oDados = $this->registrosPlantoesPorID($_GET['id']);
        $dados  = $oDados->fetch_object();

        $oDBase = $this->tabEscalasController->registrosEscalas();
        $opcoes = $this->tabPlantoesModel->listaEscalas( $oDBase );
        
        $this->tabPlantoesView->formularioCadastroPlantao( $dados, $opcoes, $form, $retorno );
    }

} // END class TabPlantoesController
