<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabFuncaoModel.php" );
include_once( "src/views/TabFuncaoView.php" );
include_once( "src/controllers/DadosServidoresController.php" );
include_once( "src/controllers/TabSetoresController.php" );


/**
 * @class TabFuncaoController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : tabcargo
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - TabFuncaoController.php
 *
 */

class TabFuncaoController
{
    /*
    * Atributos
    */
    public $tabFuncaoModel            = NULL;
    public $tabFuncaoView             = NULL;
    public $dadosServidoresController = NULL;
    public $tabSetoresController      = NULL;

    public function __construct()
    {
	$path_parts = pathinfo($_SERVER['HTTP_REFERER']);
	$pagina_de_origem = $path_parts['filename'];

        $sufixoPaginasCorretas = array( "chefia", "principal_abertura", "tabfuncao", "tabfuncao_alterar", "tabfuncao_incluir" );

	if (empty($pagina_de_origem) || (!in_array($pagina_de_origem, $sufixoPaginasCorretas)))
	{
            //mensagem( "Problema no acesso a p�gina de Manuten��o de Fun��es!" );
            //replaceLink('chefia.php');
        }
        
        # instancia
        $this->tabFuncaoModel            = new TabFuncaoModel();
        $this->tabFuncaoView             = new TabFuncaoView();
        $this->dadosServidoresController = new DadosServidoresController();
        $this->tabSetoresController      = new TabSetoresController();
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function insert($alterar=null)
    {
        return $this->tabFuncaoModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar=null)
    {
        return $this->tabFuncaoModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id=null )
    {
        $oFuncao = $this->tabFuncaoModel->registrosPorID($id);
        $descricao = $oFuncao->fetch_object()->DESC_CARGO;
    
        $dados = $this->dadosServidoresController->selecionaServidorPor( $id );
        $num_rows = $dados->num_rows();
            
        $retorno = $this->tabFuncaoModel->delete( $id );
        
        return $this->tabFuncaoView->deleteView($retorno, $descricao);
    }


    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registros()
    {
        return $this->tabFuncaoModel->registros();
    }


    /*
     * @info Registros cargo
     *
     * @param void
     * @return json
     */
    public function registrosRetornoAjax()
    {
        $this->tabFuncaoModel->registrosRetornoAjax();
    }

    /*
     * @info Registros Isen��o de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosPorID($id=null)
    {
        return $this->tabFuncaoModel->registrosPorID($id);
    }


    /*
     * @info Registros
     *
     * @param integer $id
     * @param string $acao
     * @return resource
     */
    public function registrosPorFuncao($var=null)
    {
        return $this->tabFuncaoModel->registrosPorFuncao($var);
    }
    

    /**
     * @info Lista os registros Isen��o de Ponto
     *
     * @param void
     * @return void
     */
    public function listaRegistros()
    {
        $this->tabFuncaoView->listaRegistros( $this );
    }


    /*
     * @info Registros Fun��es Siape
     *
     * @param void
     * @return object
     */
    public function dadosFuncoesSiapeCodigo($id=null)
    {
        return $this->tabFuncaoModel->dadosFuncoesSiapeCodigo( $id );
    }


    /**
     * @info Lista c�digos de fun��es (SIAPE)
     *
     * @param void
     * 
     * @return object C�digos das fun��es e dewscri��o
     */
    public function CarregaCodigoFuncoesSiape()
    {
        return $this->tabFuncaoModel->CarregaCodigoFuncoesSiape();
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

        $this->tabFuncaoView->showLista( $retorno );
    }


    /**
     * @info Exibe a Formul�rio para inclus�o de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioIncluir()
    {
        if(!empty($_POST) && !empty($_POST['COD_FUNCAO']))
        {
            $retorno = $this->insert($_POST);
        }
        
        $codigos_funcoes = $this->CarregaCodigoFuncoesSiape();
        $codigos_setores = $this->tabSetoresController->selecionaUnidadesPorUpag( $_SESSION['upag'] );
        $this->tabFuncaoView->showFormularioIncluir( $retorno, $codigos_funcoes, $codigos_setores );
    }


    /**
     * @info Exibe a Formul�rio para altera��o de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioAlterar()
    {
        if(!empty($_POST) && !empty($_POST['COD_FUNCAO']))
        {
            $retorno = $this->update($_POST);
        }
        
        $dados_funcoes   = $this->registrosPorID($_POST['id']);
        $codigos_funcoes = $this->CarregaCodigoFuncoesSiape();
        $codigos_setores = $this->tabSetoresController->selecionaUnidadesPorUpag( $_SESSION['upag'] );
        $this->tabFuncaoView->showFormularioAlterar( $dados_funcoes, $retorno, $codigos_funcoes, $codigos_setores );
    }
    
} // END class TabFuncaoController
