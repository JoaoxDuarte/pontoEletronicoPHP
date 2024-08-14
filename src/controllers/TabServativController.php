<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabServativModel.php" );
include_once( "src/views/TabServativView.php" );


/**
 * @class TabServativController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : diversas
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabServativController.php
 *
 */
class TabServativController
{
    /*
     * Atributos
     */
    public $tabServativModel = NULL;
    public $tabServativView  = NULL;

    public function __construct()
    {
      # instancia
      $this->tabServativModel = new TabServativModel();
      $this->tabServativView  = new TabServativView();
    }

    
    /* @info  Seleciona servidor/usuário
     *
     * @param  string/null  $chave    Matrícula/CPF do servidor/estagiário
     * @param  string/null  $destino  Destino de retorno em caso de erro
     * @param  string       $limit    Limite de seleção de registro 
     * @return  object/boolean
     *
     * @author Edinalvo Rosa
     */
    public function selecionaServidor($chave = null, $destino = null, $limit = '')
    {
        return $this->tabServativModel->selecionaServidor($chave, $destino, $limit);
    }


    /**
     * @info Seleção dos registros
     *
     * @param string $link  Link
     * @param string $setor Setor
     * @param string $data  Data
     * @return object
     */
    public function selecionaServidoresUnidade($link=null, $setor=null, $data=null)
    {
        return $this->tabServativModel->selecionaServidoresLiberacaoIPS();
    }
    
    
    /* @info  Seleciona dados de servidor e/ou plantonista
     *
     * @param  string  $mat  Matrícula do servidor
     * @return string  Dados do servidor
     *
     * @author Edinalvo Rosa
     */
    public function selecionaDadosServidor_ou_PlantonistaPorMatricula($mat = null)
    {
        return $this->tabServativModel->selecionaDadosServidor_ou_PlantonistaPorMatricula($mat);
    }

    
    /**
     * @info Carrega os dados do SERVATIV para uso no momento que recebe
     *       os dados do Webservice do SIAPE, caso alguns dados venham 
     *       nulo ou em branco usamos estes dados carregados do SERVATIV
     * @param string $mat
     * @return object/boolean
     * 
     * @author Edinalvo Rosa 
     * @version 1.0.0.0 2020-02-22 15.41
     */
    public function updateServerCarregaDadosDoServativ( $mat = null )
    {
        return $this->tabServativModel->updateServerCarregaDadosDoServativ($mat);
    }
    
    
    /**
     * @info Grava os recebidos do Webservice do SIAPE no SERVATIV
     * @param string $mat
     * @param array  $dados
     * @return object/boolean
     * 
     * @author Edinalvo Rosa 
     * @version 1.0.0.0 2020-02-22 16.55
     */
    public function updateServerAtualizarServativ( $mat = null, $dados = null )
    {           
        return $this->tabServativModel->updateServerAtualizarServativ($mat, $dados);
    }


    /* @info  Pega data de admissão para uso
     *        em inserir_dias_sem_frequencia
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @return  string  Data da admissão
     *
     * @author Edinalvo Rosa
     */
    public function getDataAdmissaoDoServidor($siape)
    {
        return $this->tabServativModel->getDataAdmissaoDoServidor($siape);
    }
}
