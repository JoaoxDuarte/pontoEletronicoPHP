<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/controllers/DadosServidoresController.php" );
include_once( "src/models/TabOcorrenciaModel.php" );
include_once( "src/views/TabOcorrenciaView.php" );


/**
 * @class TabOcorrenciaController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabocfre
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabOcorrenciaController.php
 *
 */

class TabOcorrenciaController
{
    /*
    * Atributos
    */
    public $tabOcorrenciaModel        = NULL;
    public $tabOcorrenciaView         = NULL;
    public $dadosServidoresController = NULL;

    public function __construct()
    {
        # instancia
        $this->tabOcorrenciaModel        = new TabOcorrenciaModel();
        $this->tabOcorrenciaView         = new TabOcorrenciaView();
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
        return $this->tabOcorrenciaModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar=null)
    {
        return $this->tabOcorrenciaModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id=null )
    {
        $oOcorrencia = $this->tabOcorrenciaModel->registrosPorID($id);
        $descricao = $oOcorrencia->fetch_object()->desc_ocorr;

        $retorno = $this->tabOcorrenciaModel->delete( $id );

        return $this->tabOcorrenciaView->deleteView($retorno, $descricao);
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registros($codigo=null, $semdDescricaoVazia=false, $soAtivo=false)
    {
        return $this->tabOcorrenciaModel->registros($codigo, $semdDescricaoVazia, $soAtivo);
    }


    /*
     * @info Registros cargo
     *
     * @param void
     * @return json
     */
    public function registrosRetornoAjax($codigo=null, $semdDescricaoVazia=true, $soAtivo=false)
    {
        $this->tabOcorrenciaModel->registrosRetornoAjax($codigo, $semdDescricaoVazia, $soAtivo);
    }

    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosPorID($id=null)
    {
        return $this->tabOcorrenciaModel->registrosPorID($id);
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

        $this->tabOcorrenciaView->showLista( $retorno );
    }


    /**
     * @info Exibe a Formulário para inclusão de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioIncluir()
    {
        if(!empty($_POST) && !empty($_POST['siapecad']))
        {
            $retorno = $this->insert($_POST);
        }

        $this->tabOcorrenciaView->showFormularioIncluir( $retorno );
    }


    /**
     * @info Exibe a Formulário para alteração de Isentos de Ponto
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioAlterar()
    {
        if(!empty($_POST) && !empty($_POST['siapecad']))
        {
            $retorno = $this->update($_POST);
        }
        $dados  = $this->registrosPorID($_GET['codigo']);
        $this->tabOcorrenciaView->showFormularioAlterar( $dados, $retorno);
    }


    /**
     * @info Exibe Formulário para consulta
     *
     * @param $id
     * @return string HTML
     */
    public function showFormularioVisualizar()
    {
        $dados = $this->registrosPorID($_GET['codigo']);

        $objDadosCampoENUM = new StClass();
        $objDadosCampoENUM->grupo             = $this->tabOcorrenciaModel->dadosCampoENUM( 'tabocfre', 'grupo' );
        $objDadosCampoENUM->tipo              = $this->tabOcorrenciaModel->dadosCampoENUM( 'tabocfre', 'tipo' );
        $objDadosCampoENUM->tratamento_debito = $this->tabOcorrenciaModel->dadosCampoENUM( 'tabocfre', 'tratamento_debito' );
        $objDadosCampoENUM->padrao            = $this->tabOcorrenciaModel->dadosCampoENUM( 'tabocfre', 'padrao' );
        $objDadosCampoENUM->grupo_cadastral   = $this->tabOcorrenciaModel->dadosCampoENUM( 'tabocfre', 'grupo_cadastral' );
        $objDadosCampoENUM->grupo_ocorrencia  = $this->tabOcorrenciaModel->dadosCampoENUM( 'tabocfre', 'grupo_ocorrencia' );

        $this->tabOcorrenciaView->showFormularioVisualizar( $dados, $objDadosCampoENUM );
    }

    
    /**
     * @info Monta listbox com códigos de ocorrência
     *
     * @param $id
     * @param  string  $valor       Valor para marcarcomo selecionado     
     * @param  integer $tamdescr    Largura do list box                   
     * @param  boolean $imprimir    Retornará como texto ou exibirá       
     * @param  boolean $por_periodo Indica se a ocorrencia eh por periodo 
     * @param  boolean $historico   Indica se a será exibida no histórico 
     * @param  string  $onchange    Função javascript para troca de opção ou seleção
     * @param  string  $grupo       Grupo de origem (acaompanhar/homologar/...)
     * @return string HTML
     */
    public function montaSelectOcorrencias($valor='', $tamdescr='', $imprimir=false, $por_periodo=false, $historico=false, $onchange='', $grupo='', $siape='', $soGrupoOcorr=null)
    {
        if (!is_null($soGrupoOcorr) && !empty($soGrupoOcorr))
        {
            $dados = $this->tabOcorrenciaModel->registros($valor, $semdDescricaoVazia=false, $soAtivo=false, $soGrupoOcorr);
        }
        else
        {
            $dados = $this->tabOcorrenciaModel->carregaSelectOcorrencias($valor, $por_periodo, $historico, $grupo, $siape);
        }
        
        return $this->tabOcorrenciaView->montaSelectOcorrencias($valor, $tamdescr, $imprimir, $onchange, $dados);
    }
    
} // END class TabOcorrenciaController
