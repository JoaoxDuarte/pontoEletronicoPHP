<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/DadosServidoresModel.php" );
include_once( "src/views/DadosServidoresView.php" );


/**
 * @class DadosServidoresController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : diversas
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
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
     * @info Sele��o dos registros
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
     * @info Sele��o dos servidores - ac�mulo
     * 
     * @param boolean $autorizacoes_required Requer autoriza��o
     * @return object
     */
    public function selecionaServidoresUnidadeBancoHoras($autorizacoes_required = false)
    {
    return $this->dadosServidoresModel->selecionaServidoresUnidadeBancoHoras($autorizacoes_required);
    }


    /**
     * @info Sele��o dos servidores - usufruto
     * 
     * @param integer $id ID do registro
     * @return object
     */
    public function selecionaServidoresUnidadeBancoHorasUsufruto($id = null)
    {
      return $this->dadosServidoresModel->selecionaServidoresUnidadeBancoHorasUsufruto($id);
    }


    /**
     * @info Sele��o do servidor - usufruto
     * 
     * @param string $siape Matr�cula do servidor
     * @return object
     */
    public function selecionaServidorPorMatricula($siape = '', $ciclo = true)
    {
        return $this->dadosServidoresModel->selecionaServidorPorMatricula($siape, $ciclo);
    }


    /**
     * @info Sele��o do servidor por cargo
     * 
     * @param string $cargo C�digo do cargo
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

    
    /* @info  Seleciona os registros de frequ�ncia do
     *        m�s e ourros dados do servidor
     *
     * @param  string  $siape  Matr�cula do servidor/estagi�rio
     * @param  string  $competencia  Competencia da frequ�ncia
     * @return  object  Dados da frequ�ncia e outros
     *
     * @author Edinalvo Rosa
     */
    function selecionaRegistroPorMatricula($siape = null)
    {
        return $this->dadosServidoresModel->selecionaRegistroPorMatricula($siape);
    }
    
    
    /* @info  Lista servidores com libera��o de IPs
     *
     * @param  string  $siape  Matr�cula do servidor/estagi�rio
     * @return  string  Data da admiss�o
     *
     * @author Edinalvo Rosa
     */
    public function selecionaServidoresLiberacaoIPS()
    {
        return $this->dadosServidoresModel->selecionaServidoresLiberacaoIPS();
    }

    
    /* @info  Pega data de admiss�o
     *
     * @param  string  $siape  Matr�cula do servidor/estagi�rio
     * @return  string  Data da admiss�o
     *
     * @author Edinalvo Rosa
     */
    public function getDataAdmissaoDoServidor($siape)
    {
        return $this->dadosServidoresModel->getDataAdmissaoDoServidor($siape);
    }

    
    /**
     * @info Carrega a carreira ao qual o cargo est� vinculado
     * 
     * @param string $siape Matr�cula do servidor/estagi�rio
     * @return string
     */
    public function getCarreira( $siape = null )
    {
        return $this->dadosServidoresModel->getCarreira( $siape );
    }


    /* @info  Pega nome do servidor
     *
     * @param  string  $siape  Matr�cula do servidor/estagi�rio
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
     * @param  string  $siape  Matr�cula do servidor/estagi�rio
     * @return  string  Sigla do regime juridico
     *
     * @author Edinalvo Rosa
     */
    public function getSigRegJur( $siape = null )
    {
        return $this->dadosServidoresModel->getSigRegJur( $siape );
    }

}
