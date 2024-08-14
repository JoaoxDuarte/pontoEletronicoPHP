<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabFacultativo172Model.php" );
include_once( "src/views/TabFacultativo172View.php" );


/**
 * @class TabFacultativo172Controller
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabfacultativo172
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
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
     * @info Seleciona códigos de crédito e débito que    
     *       não serão exibidos em lista de códigos de    
     *       ocorrência
     *
     * @param string $data_excessao
     * @return string Códigos a excluir
     */
    public function CodigoEventoEsportivoNaoExibir($data_excessao='')
    {
        return $this->tabFacultativo172Model->CodigoEventoEsportivoNaoExibir($data_excessao);
    }
    

    /*
     * @info Códigos de crédito e débito referente a
     *       eventos esportivos facultado compensar
     *
     * @param string $tipo_codigo
     * @return string Códigos de ocorrência do evento
     */
    public function EventosCodigos($tipo_codigo='')
    {
        return $this->tabFacultativo172Model->EventosCodigos($tipo_codigo);
    }


    /*
     * @info Verifica se a ocorrência indica pode ser
     *       utilizada no dia desejado
     *
     * @param string $ocor Ocorrência
     * @param string $data Data da ocorrência
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaEventos($ocor='',$data='')
    {
        return $this->tabFacultativo172Model->verificaEventos($ocor,$data);
    }


    /*
     * @info Seleciona códigos de crédito e débito que
     *       verifica se a data está dentro do período de
     *       compensação das horas devidas em dias de
     *       evento esportivo
     *
     * @param string $ocor Ocorrência
     * @param string $data Data da ocorrência
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaPeriodoCompensacaoEvento($ocor='',$data='')
    {
        return $this->tabFacultativo172Model->verificaPeriodoCompensacaoEvento($ocor,$data);
    }


    /*
     * @info Seleciona códigos de crédito e débito que    
     *       e se a ocorrência é permitida para este dia
     *
     * @param string $ocor Ocorrência
     * @param string $data Data da ocorrência
     * @return string Vazia/Mensagem de erro/aviso
     */
    public function verificaDiaEventoAutorizado($ocor='',$data='')
    {
        return $this->tabFacultativo172Model->verificaDiaEventoAutorizado($ocor,$data);
    }
} // END class TabFacultativo172Controller
