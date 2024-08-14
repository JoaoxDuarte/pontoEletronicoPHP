<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabPontoAuxiliarModel.php" );
//include_once( "src/views/TabPontoAuxiliarView.php" );


/**
 * @class TabPontoAuxiliarController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : pontoMMAAAA_auxiliar
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
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
     * @param  string  $siape  Matr�cula do servidor
     * @param  string  $dia    Dia do registro
     * @return resource  Resultado da sele��o
     */
    public function selecionarDadosPontoAuxiliar( $siape=null, $dia=null, $oco=null )
    {
        return $this->tabPontoAuxiliarModel->selecionarDadosPontoAuxiliar( $siape, $dia, $oco);
    }

    
    /**
     * @param  array    $paramsPonto = array(
     *          'dia'            Data da ocorr�ncia
     *          'matricula'      Matr�cula SIAPE
     *          'hora_ini'       Hora inicial da aus�ncia
     *          'hora_fim'       Hora final da aus�ncia
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            C�digo da ocorr�ncia
     *          'registro_ip'    IP da m�quina do registro
     *          'registro_siape' Matr�cula SIAPE de quem registrou
     *      );
     * @return boolean  TRUE sucesso, FALSE erro
     */
    public function incluirPontoAuxiliarBancoDeHoras( $paramsPonto=null, $saldo=0  )
    {
        return $this->tabPontoAuxiliarModel->incluirPontoAuxiliarBancoDeHoras( $paramsPonto, $saldo );
    }

    
    /**
     * @param  array    $paramsPonto = array(
     *          'dia'            Data da ocorr�ncia
     *          'matricula'      Matr�cula SIAPE
     *          'hora_ini'       Hora inicial da aus�ncia
     *          'hora_fim'       Hora final da aus�ncia
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            C�digo da ocorr�ncia
     *          'registro_ip'    IP da m�quina do registro
     *          'registro_siape' Matr�cula SIAPE de quem registrou
     *      );
     * @return boolean  TRUE sucesso, FALSE erro
     */
    public function incluirPontoAuxiliar( $paramsPonto=null, $saldo=0 )
    {
        return $this->tabPontoAuxiliarModel->incluirPontoAuxiliar( $paramsPonto, $saldo );
    }

    
    /**
     * @param  array    $paramsPonto = array(
     *          'dia'            Data da ocorr�ncia
     *          'matricula'      Matr�cula SIAPE
     *          'hora_ini'       Hora inicial da aus�ncia
     *          'hora_fim'       Hora final da aus�ncia
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            C�digo da ocorr�ncia
     *          'registro_ip'    IP da m�quina do registro
     *          'registro_siape' Matr�cula SIAPE de quem registrou
     *          'ipch'           IP da chefia, se for a chefia quem registra
     *          'iprh'           IP do RH, se for o RH quem registra
     *          'matchef'        Matr�cula da chefia, se for a chefia quem registra
     *          'siaperh'        Matr�cula do servidor do RH, se for o RH quem registra
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
     * @param  string> $oco        C�digo da ocorr�ncia
     * @param  string> $oper       Opera��o executada (altera��o,etc)
     * @return : void
     * @usage  : gravarHistoricoPontoAuxiliar('9999999','2011-11-10','xxxxx','X');                                                |
     * @author  Edinalvo Rosa
     *
     * @info  Registra em hist�rico os dados do ponto auxiliar antes da altera��o
     */
    public function gravarHistoricoPontoAuxiliar( $matricula=null, $dia=null, $oco=null, $oper='A' )
    {
        $this->tabPontoAuxiliarModel->gravarHistoricoPontoAuxiliar( $matricula, $dia, $oco, $oper );
    }


    /**  @Function
     *
     * @param  string  $siape    Matr�cula do servidor
     * @param  string  $dia      Data do registro da frequ�ncia
     * @param  string  $unidade  Unidade do servidor
     * @return void
     * @author Edinalvo Rosa
     * @info   Ajusta saldo da frequencia se houver registro de
     *         consulta m�dica ou GECC
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
