<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/RelatorioFrequenciaHomologacoesModel.php" );
include_once( "src/views/relatorios/RelatorioFrequenciaHomologacoesView.php" );
include_once( "src/controllers/DadosServidoresController.php" );


/**
 * @class RelatorioFrequenciaHomologacoesController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : homologados
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
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
     * @param string $ano  Ano da competência da homologação
     * @param string $mes  Mês da competência da homologação
     * @param string $upag UPAG da unidade do servidor/estagiário
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
     * @param string      $ano    Ano da competência da homologação
     * @param string      $mes    Mês da competência da homologação
     * @param string      $upag   UPAG da unidade do servidor/estagiário
     * @param string/null $setor  Unidade do servidor/estagiário
     */
    public function CarregaSelectUnidades($ano, $mes, $upag, $setor=NULL)
    {
        $oDBase = $this->UnidadesTotalDeServidores($ano, $mes, $upag);
        
        $this->relatorioFrequenciaHomologacoesView->CarregaSelectUnidades( $oDBase, $ano, $mes, $upag, $setor );
    }
    

    /*
     * @param string $ano  Ano da competência da homologação
     * @param string $mes  Mês da competência da homologação
     * @param string $upag UPAG da unidade do servidor/estagiário
     *
     * @info Lista as unidades e servidores para verificar se foram homologados
     */
    public function SituacaoHomologacaoPorMatricula( $siape = null, $dt_adm = null, $oco_exclu_dt = null, $compet = null )
    {
        return $this->relatorioFrequenciaHomologacoesModel->SituacaoHomologacaoPorMatricula( $siape, $dt_adm, $oco_exclu_dt, $compet );
    }

    /**
     * @info Seleção e exibição de lista homologações
     *
     * @param void
     * @return string HTML
     */
    public function showFormularioLista()
    {
       // $this->relatorioFrequenciaHomologacoesView->showFormularioLista();
    }
    
} // END class RelatorioFrequenciaHomologacoesController
