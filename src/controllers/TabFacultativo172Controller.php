<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabFacultativo172Model.php" );
include_once( "src/views/TabFacultativo172View.php" );


/**
 * @class TabFacultativo172Controller
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : tabfacultativo172
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - TabFacultativo172Controller.php
 *
 */

class TabFacultativo172Controller
{
    /*
    * Atributos
    */
    public $tabFacultativo172Model = NULL;
    public $tabFacultativo172View  = NULL;

    public function __construct()
    {
        # instancia
        $this->tabFacultativo172Model = new TabFacultativo172Model();
        $this->tabFacultativo172View  = new TabFacultativo172View();
    }


   /*
     * @info Seleciona c�digos de cr�dito e d�bito que    
     *       n�o ser�o exibidos em lista de c�digos de    
     *       ocorr�ncia
     *
     * @param string $data_excessao
     * @return string C�digos a excluir
     */
    public function CodigoEventoEsportivoNaoExibir($data_excessao='')
    {
        return $this->tabFacultativo172Model->CodigoEventoEsportivoNaoExibir($data_excessao);
    }
    

    /*
     * @info C�digos de cr�dito e d�bito referente a
     *       eventos esportivos facultado compensar
     *
     * @param string $tipo_codigo
     * @return string C�digos de ocorr�ncia do evento
     */
    public function EventosCodigos($tipo_codigo='')
    {
        return $this->tabFacultativo172Model->EventosCodigos($tipo_codigo);
    }


    /*
     * @info Verifica se a ocorr�ncia indica pode ser
     *       utilizada no dia desejado
     *
     * @param string $ocor Ocorr�ncia
     * @param string $data Data da ocorr�ncia
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaEventos($ocor='',$data='')
    {
        return $this->tabFacultativo172Model->verificaEventos($ocor,$data);
    }


    /*
     * @info Seleciona c�digos de cr�dito e d�bito que
     *       verifica se a data est� dentro do per�odo de
     *       compensa��o das horas devidas em dias de
     *       evento esportivo
     *
     * @param string $ocor Ocorr�ncia
     * @param string $data Data da ocorr�ncia
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaPeriodoCompensacaoEvento($ocor='',$data='')
    {
        return $this->tabFacultativo172Model->verificaPeriodoCompensacaoEvento($ocor,$data);
    }


    /*
     * @info Seleciona c�digos de cr�dito e d�bito que    
     *       e se a ocorr�ncia � permitida para este dia
     *
     * @param string $ocor Ocorr�ncia
     * @param string $data Data da ocorr�ncia
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaDiaEventoAutorizado($ocor='',$data='')
    {
        return $this->tabFacultativo172Model->verificaDiaEventoAutorizado($ocor,$data);
    }
} // END class TabFacultativo172Controller
