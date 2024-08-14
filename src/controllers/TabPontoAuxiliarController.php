<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabPontoAuxiliarModel.php" );
//include_once( "src/views/TabPontoAuxiliarView.php" );


/**
 * @class TabPontoAuxiliarController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : pontoMMAAAA_auxiliar
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabPontoAuxiliarController.php
 *
 */

class TabPontoAuxiliarController
{
    /*
    * Atributos
    */
        public $tabPontoAuxiliarModel = NULL;
    //public $tabPontoAuxiliarView  = NULL;

    public function __construct()
    {
        # instancia
        $this->tabPontoAuxiliarModel = new TabPontoAuxiliarModel();
        //$this->tabPontoAuxiliarView  = new TabPontoAuxiliarView();
    }


    /**
     * @param  string  $siape  Matrícula do servidor
     * @param  string  $dia    Dia do registro
     * @return resource  Resultado da seleção
     */
    public function selecionarDadosPontoAuxiliar( $siape=null, $dia=null, $oco=null )
    {
        return $this->tabPontoAuxiliarModel->selecionarDadosPontoAuxiliar( $siape, $dia, $oco);
    }

    
    /**
     * @param  array    $paramsPonto = array(
     *          'dia'            Data da ocorrência
     *          'matricula'      Matrícula SIAPE
     *          'hora_ini'       Hora inicial da ausência
     *          'hora_fim'       Hora final da ausência
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            Código da ocorrência
     *          'registro_ip'    IP da máquina do registro
     *          'registro_siape' Matrícula SIAPE de quem registrou
     *      );
     * @return boolean  TRUE sucesso, FALSE erro
     */
    public function incluirPontoAuxiliarBancoDeHoras( $paramsPonto=null, $saldo=0  )
    {
        return $this->tabPontoAuxiliarModel->incluirPontoAuxiliarBancoDeHoras( $paramsPonto, $saldo );
    }

    
    /**
     * @param  array    $paramsPonto = array(
     *          'dia'            Data da ocorrência
     *          'matricula'      Matrícula SIAPE
     *          'hora_ini'       Hora inicial da ausência
     *          'hora_fim'       Hora final da ausência
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            Código da ocorrência
     *          'registro_ip'    IP da máquina do registro
     *          'registro_siape' Matrícula SIAPE de quem registrou
     *      );
     * @return boolean  TRUE sucesso, FALSE erro
     */
    public function incluirPontoAuxiliar( $paramsPonto=null, $saldo=0 )
    {
        return $this->tabPontoAuxiliarModel->incluirPontoAuxiliar( $paramsPonto, $saldo );
    }

    
    /**
     * @param  array    $paramsPonto = array(
     *          'dia'            Data da ocorrência
     *          'matricula'      Matrícula SIAPE
     *          'hora_ini'       Hora inicial da ausência
     *          'hora_fim'       Hora final da ausência
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            Código da ocorrência
     *          'registro_ip'    IP da máquina do registro
     *          'registro_siape' Matrícula SIAPE de quem registrou
     *          'ipch'           IP da chefia, se for a chefia quem registra
     *          'iprh'           IP do RH, se for o RH quem registra
     *          'matchef'        Matrícula da chefia, se for a chefia quem registra
     *          'siaperh'        Matrícula do servidor do RH, se for o RH quem registra
     *      );
     *         boolean $por_exclusao
     * @return boolean  TRUE sucesso, FALSE erro
     */
    public function AlterarPontoPrincipal( $paramsPonto=null, $por_exclusao=false, $limite_horas=0 )
    {
        return $this->tabPontoAuxiliarModel->AlterarPontoPrincipal( $paramsPonto, $por_exclusao, $limite_horas );
    }

    
    /**
     * 
     * @param string      $siape
     * @param string      $lotacao
     * @param string/date $dia
     * @return bool/string
     */
    public function JornadaDoServidor( $siape=null, $lotacao=null, $dia=null )
    {
        return $this->tabPontoAuxiliarModel->JornadaDoServidor( $siape, $lotacao, $dia );
    }

    
    /**
     * @param array  $dados
     * @param string $oco
     * @return void
     */
    public function deletePontoAuxiliar( $dados=null, $oco=null )
    {
        $this->tabPontoAuxiliarModel->deletePontoAuxiliar( $dados, $oco );
    }


    /**  @Function
     *
     * @param  string  $matricula  Matricula siape
     * @param  string> $diac       Data invertida (aaaa-mm-dd)
     * @param  string> $oco        Código da ocorrência
     * @param  string> $oper       Operação executada (alteração,etc)
     * @return : void
     * @usage  : gravarHistoricoPontoAuxiliar('9999999','2011-11-10','xxxxx','X');                                                |
     * @author  Edinalvo Rosa
     *
     * @info  Registra em histórico os dados do ponto auxiliar antes da alteração
     */
    public function gravarHistoricoPontoAuxiliar( $matricula=null, $dia=null, $oco=null, $oper='A' )
    {
        $this->tabPontoAuxiliarModel->gravarHistoricoPontoAuxiliar( $matricula, $dia, $oco, $oper );
    }


    /**  @Function
     *
     * @param  string  $siape    Matrícula do servidor
     * @param  string  $dia      Data do registro da frequência
     * @param  string  $unidade  Unidade do servidor
     * @return void
     * @author Edinalvo Rosa
     * @info   Ajusta saldo da frequencia se houver registro de
     *         consulta médica ou GECC
     */
    public function AjustaSaldoFrequenciaSeConsultaMedicaRegistrada( $siape=null, $dia=null, $unidade=NULL )
    {
        $this->tabPontoAuxiliarModel->AjustaSaldoFrequenciaSeConsultaMedicaRegistrada( $siape, $dia, $unidade );
    }


    /**
     * 
     * @param string $unidade
     * @return void
     */
    public function horariosLimite( $unidade=NULL )
    {
        return $this->tabPontoAuxiliarModel->horariosLimite( $unidade );
    }

} // END class TabPontoAuxiliarController
