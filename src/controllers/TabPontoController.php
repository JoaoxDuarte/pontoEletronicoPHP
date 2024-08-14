<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/models/TabPontoModel.php" );
include_once( "src/views/TabPontoView.php" );
include_once( "src/controllers/DadosServidoresController.php" );
include_once( "src/controllers/TabBancoDeHorasCiclosController.php" );

/**
 * @class TabPontoController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : pontoMMAAAA
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabPontoController.php
 *
 */

class TabPontoController
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $objOcorrenciasGrupos            = null;
    /* @var OBJECT */ public $tabPontoModel                   = NULL;
    /* @var OBJECT */ public $tabPontoView                    = NULL;
    /* @var OBJECT */ public $dadosServidoresController       = NULL;
    /* @var OBJECT */ public $tabBancoDeHorasCiclosController = NULL;

    public function __construct()
    {
        # instancia
        $this->objOcorrenciasGrupos            = new OcorrenciasGrupos();
        $this->tabPontoModel                   = new TabPontoModel();
        $this->tabPontoView                    = new TabPontoView();
        $this->dadosServidoresController       = new DadosServidoresController();
        $this->tabBancoDeHorasCiclosController = new TabBancoDeHorasCiclosController();
    }


    /*
     * @info Registros frequência por servidor e dia
     *
     * @param string $siape
     * @param string $dia
     * @return object
     */
    public function registrosPorSiapeDia($siape=null, $dia=null, $nome_do_arquivo=null)
    {
        return $this->tabPontoModel->registrosPorSiapeDia($siape, $dia, $nome_do_arquivo);
    }


    /*
     * @info Registros frequência por servidor
     *
     * @param string $siape
     * @param string $comp
     * @return object
     */
    public function registrosPorID($siape=null, $comp=null, $nome_do_arquivo=null)
    {
        return $this->tabPontoModel->registrosPorSiapeDia($siape, $comp, $nome_do_arquivo);
    }


    /*
     * @info Registros frequência por servidor
     *
     * @param string $siape
     * @return object
     */
    public function registrosPorSiapeHistorico($siape=null)
    {
        return $this->tabPontoModel->registrosPorSiapeHistorico($siape);
    }

    /* @info  Seleciona os registros de frequência do
     *        mês desejado e ourros dados do servidor
     *
     * @param  string  $siape Matrícula do servidor/estagiário
     * @param  string  $comp  Mês e ano desejado
     * @return  object  Dados da frequência e outros
     *
     * @author Edinalvo Rosa
     */
    public function selecionaRegistrosFrequenciaDoServidor($siape = null, $compet = null, $nome_do_arquivo=null)
    {
        return $this->tabPontoModel->selecionaRegistrosFrequenciaDoServidor($siape, $compet, $nome_do_arquivo);
    }

    /**
     * @info Inclui novo registro
     *
     * @param array $incluir
     * @return boolean TRUE sucesso
     */
    public function insert($incluir=null, $nome_do_arquivo=null)
    {
        return $this->tabPontoModel->insert($incluir, $nome_do_arquivo);
    }


    /**
     * @info Atualiza registro
     *
     * @param array $alterar
     * @return boolean TRUE sucesso
     */
    public function update($alterar=null, $nome_do_arquivo=null)
    {
        $retorno = $this->tabPontoModel->update($alterar, $nome_do_arquivo);
        
        return $retorno;
    }


    /**
     *
     * @param string $siape
     * @param string $dia
     * @return boolean TRUE sucesso
     */
    public function delete($siape=null, $dia=null)
    {
        /*
        $oPonto = $this->tabPontoModel->registrosPorSiapeDia($siape, $dia);
        $descricao = $oPonto->fetch_object()->desc_ocorr;

        $retorno = $this->tabPontoModel->delete($siape, $dia);

        return $this->tabPontoView->deleteView($retorno, $descricao);
        */
    }


    /**
     * @info Inclui novo registro (histórico - log)
     *
     * @param array $var
     * @param string $oper
     * @return boolean TRUE sucesso
     */
    public function insertHistorico($var=null, $oper='I')
    {
        return $this->tabPontoModel->insertHistorico($var, $oper='I');
    }

} // END class TabPontoController
