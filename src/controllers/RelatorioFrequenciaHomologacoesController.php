<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/RelatorioFrequenciaHomologacoesModel.php" );
include_once( "src/views/relatorios/RelatorioFrequenciaHomologacoesView.php" );
include_once( "src/controllers/DadosServidoresController.php" );


/**
 * @class RelatorioFrequenciaHomologacoesController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : homologados
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - RelatorioFrequenciaHomologacoesController.php
 *
 */

class RelatorioFrequenciaHomologacoesController
{
    /*
    * Atributos
    */
    public $relatorioFrequenciaHomologacoesModel = NULL;
    public $relatorioFrequenciaHomologacoesView  = NULL;
    public $dadosServidoresController            = NULL;

    public function __construct()
    {
        # instancia
        $this->relatorioFrequenciaHomologacoesModel = new RelatorioFrequenciaHomologacoesModel();
        $this->relatorioFrequenciaHomologacoesView  = new RelatorioFrequenciaHomologacoesView();
        $this->dadosServidoresController            = new DadosServidoresController();
    }


    /*
     * @param string $ano  Ano da compet�ncia da homologa��o
     * @param string $mes  M�s da compet�ncia da homologa��o
     * @param string $upag UPAG da unidade do servidor/estagi�rio
     *
     * @info Total de servidores/estagiarios por UPAG
     */
    public function UnidadesTotalDeServidores($ano, $mes, $upag)
    {
        return $this->relatorioFrequenciaHomologacoesModel->UnidadesTotalDeServidores( $ano, $mes, $upag );
    }


    /**
     * @info Lista mes e ano
     *
     * @param string/null $ano
     * @param string/null $mes
     * @param string/null $opcao_selecione
     */
    public function CarregaSelectCompetencia($ano=NULL, $mes=NULL, $opcao_selecione=false)
    {
        $this->relatorioFrequenciaHomologacoesView->CarregaSelectCompetencia( $ano, $mes, $opcao_selecione );
    }
    

    /*
     * @info Lista as unidades
     *
     * @param string      $oDBase Dados dos servidores
     * @param string      $ano    Ano da compet�ncia da homologa��o
     * @param string      $mes    M�s da compet�ncia da homologa��o
     * @param string      $upag   UPAG da unidade do servidor/estagi�rio
     * @param string/null $setor  Unidade do servidor/estagi�rio
     */
    public function CarregaSelectUnidades($ano, $mes, $upag, $setor=NULL)
    {
        $oDBase = $this->UnidadesTotalDeServidores($ano, $mes, $upag);
        
        $this->relatorioFrequenciaHomologacoesView->CarregaSelectUnidades( $oDBase, $ano, $mes, $upag, $setor );
    }
    

    /*
     * @param string $ano  Ano da compet�ncia da homologa��o
     * @param string $mes  M�s da compet�ncia da homologa��o
     * @param string $upag UPAG da unidade do servidor/estagi�rio
     *
     * @info Lista as unidades e servidores para verificar se foram homologados
     */
    public function SituacaoHomologacaoPorMatricula( $siape = null, $dt_adm = null, $oco_exclu_dt = null, $compet = null )
    {
        return $this->relatorioFrequenciaHomologacoesModel->SituacaoHomologacaoPorMatricula( $siape, $dt_adm, $oco_exclu_dt, $compet );
    }

    /**
     * @info Sele��o e exibi��o de lista homologa��es
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioLista()
    {
       // $this->relatorioFrequenciaHomologacoesView->showFormularioLista();
    }
    
} // END class RelatorioFrequenciaHomologacoesController
