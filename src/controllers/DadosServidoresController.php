<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/DadosServidoresModel.php" );
include_once( "src/views/DadosServidoresView.php" );


/**
 * @class DadosServidoresController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : diversas
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - DadosServidoresController.php
 *
 */
class DadosServidoresController
{
    /*
     * Atributos
     */
    public $dadosServidoresModel = NULL;
    public $dadosServidoresView  = NULL;

    public function __construct()
    {
      # instancia
      $this->dadosServidoresModel = new DadosServidoresModel();
      $this->dadosServidoresView  = new DadosServidoresView();
    }


    /**
     * @info Seleção dos registros
     * 
     * @param string $link  Link
     * @param string $setor Setor
     * @param string $data  Data
     * @return object
     */
    public function selecionaServidoresUnidade($link=null, $setor=null, $data=null, $homologacao=null)
    {
        return $this->dadosServidoresModel->selecionaServidoresUnidade($link, $setor, $data, $homologacao);
    }

    
    /**
     * @info Seleção dos servidores - acúmulo
     * 
     * @param boolean $autorizacoes_required Requer autorização
     * @return object
     */
    public function selecionaServidoresUnidadeBancoHoras($autorizacoes_required = false)
    {
    return $this->dadosServidoresModel->selecionaServidoresUnidadeBancoHoras($autorizacoes_required);
    }


    /**
     * @info Seleção dos servidores - usufruto
     * 
     * @param integer $id ID do registro
     * @return object
     */
    public function selecionaServidoresUnidadeBancoHorasUsufruto($id = null)
    {
      return $this->dadosServidoresModel->selecionaServidoresUnidadeBancoHorasUsufruto($id);
    }


    /**
     * @info Seleção do servidor - usufruto
     * 
     * @param string $siape Matrícula do servidor
     * @return object
     */
    public function selecionaServidorPorMatricula($siape = '', $ciclo = true)
    {
        return $this->dadosServidoresModel->selecionaServidorPorMatricula($siape, $ciclo);
    }


    /**
     * @info Seleção do servidor por cargo
     * 
     * @param string $cargo Código do cargo
     * @return object
     */
    public function selecionaServidorPorCargo($cargo = '')
    {
        return $this->dadosServidoresModel->selecionaServidorPorCargo($cargo);
    }

    
    /**
     * @info Monta listbox de servidores
     * 
     * @param resource $oDados Dados dos servidores
     * @return object
     */
    public function montaSelectDeServidoresUsufruto($valor=null)
    {
        $dados = $this->dadosServidoresModel->selecionaServidoresUnidadeBancoHoras();
        $this->dadosServidoresView->montaSelectDeServidoresUsufruto($dados,$valor);
        return $html;
    }


    /**
     * @info Monta listbox de servidores
     * 
     * @param resource $oDados Dados dos servidores
     * @return object
     */
    public function montaSelectDeServidoresIPS($valor=null, $setor=null)
    {
        //$dados = $this->selecionaServidoresLiberacaoIPS();
        $dados = $this->selecionaServidoresUnidade($link=null, $setor);
        $this->dadosServidoresView->montaSelectDeServidoresLiberacaoIPS($dados,$valor);
        return $html;
    }

    
    /* @info  Seleciona os registros de frequência do
     *        mês e ourros dados do servidor
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @param  string  $competencia  Competencia da frequência
     * @return  object  Dados da frequência e outros
     *
     * @author Edinalvo Rosa
     */
    function selecionaRegistroPorMatricula($siape = null)
    {
        return $this->dadosServidoresModel->selecionaRegistroPorMatricula($siape);
    }
    
    
    /* @info  Lista servidores com liberação de IPs
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @return  string  Data da admissão
     *
     * @author Edinalvo Rosa
     */
    public function selecionaServidoresLiberacaoIPS()
    {
        return $this->dadosServidoresModel->selecionaServidoresLiberacaoIPS();
    }

    
    /* @info  Pega data de admissão
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @return  string  Data da admissão
     *
     * @author Edinalvo Rosa
     */
    public function getDataAdmissaoDoServidor($siape)
    {
        return $this->dadosServidoresModel->getDataAdmissaoDoServidor($siape);
    }

    
    /**
     * @info Carrega a carreira ao qual o cargo está vinculado
     * 
     * @param string $siape Matrícula do servidor/estagiário
     * @return string
     */
    public function getCarreira( $siape = null )
    {
        return $this->dadosServidoresModel->getCarreira( $siape );
    }


    /* @info  Pega nome do servidor
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @return  string  Nome do servidor
     *
     * @author Edinalvo Rosa
     */
    public function getNomeServidor( $siape = null )
    {
        return $this->dadosServidoresModel->getNomeServidor( $siape );
    }


    /* @info  Pega sigla do regime juridico
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @return  string  Sigla do regime juridico
     *
     * @author Edinalvo Rosa
     */
    public function getSigRegJur( $siape = null )
    {
        return $this->dadosServidoresModel->getSigRegJur( $siape );
    }

}
