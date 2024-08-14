<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabBancoDeHorasAcumulosModel.php" );
include_once( "src/views/TabBancoDeHorasAcumulosView.php" );


/**
 * @class TabBancoDeHorasAcumulosController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : acumulos_horas
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - TabBancoDeHorasAcumulosController.php
 *
 */

class TabBancoDeHorasAcumulosController
{
    /*
    * Atributos
    */
    public $tabBancoDeHorasAcumulosModel = NULL;
    public $tabBancoDeHorasAcumulosView  = NULL;

    public function __construct()
    {
        # instancia
        $this->tabBancoDeHorasAcumulosModel = new TabBancoDeHorasAcumulosModel();
        $this->tabBancoDeHorasAcumulosView  = new TabBancoDeHorasAcumulosView();
    }
    

    /**
     * @info Exibe quadro de saldo
     *
     * @param string      $mat
     * @param integer     $ciclo_id
     * @param date/string $dia
     * @return array (assoc)
     */
    public function SaldosBancoDeHorasCicloYear($mat = null, $ciclo_id = null, $ano = null, $so_ano_corrente = true)
    {
        return $this->tabBancoDeHorasAcumulosModel->SaldosBancoDeHorasCicloYear( $mat, $ciclo_id, $ano, $so_ano_corrente );
    }
    

    /**
     * @info Exibe quadro de saldo
     *
     * @param void
     * @return void
     */
    public function DadosAcumuloHoras($mat = null, $ciclo_id = null, $dia = null)
    {
        return $this->tabBancoDeHorasAcumulosModel->DadosAcumuloHoras( $mat, $ciclo_id, $dia );
    }


    /**
     * @info Verifica se ocorrencia usofruto
     *
     * @param object $dados
     * @return void
     */
    public function verificaSeOcorrenciaTipoUsufrutoBancoDeHoras( $dados = null )
    {
        return $this->tabBancoDeHorasAcumulosModel->verificaSeOcorrenciaTipoUsufrutoBancoDeHoras( $dados );
    }


    /**

     * @info Verifica se ocorrencia origem � de usofruto
     *
     * @param object $dados
     * @return void
     */
    public function verificaSeOcorrenciaOrigemTipoUsufruto( $dados = null )
    {
        return $this->tabBancoDeHorasAcumulosModel->verificaSeOcorrenciaOrigemTipoUsufruto( $dados );
    }


    /**
     * @info Verifica se ocorrencia destino � de usofruto
     *
     * @param object $dados
     * @return void
     */
    public function verificaSeOcorrenciaDestinoTipoUsufruto( $dados = null )
    {
        return $this->tabBancoDeHorasAcumulosModel->verificaSeOcorrenciaDestinoTipoUsufruto( $dados );
    }


    /**
     * @info Verifica condi��es para usufruto Banco de Horas
     *
     * @param object  $dados
     *          $dados->siape            // string  : matr�cula do servidor
     *          $dados->dia              // string  : data da ocorr�ncia
     *          $dados->grupo            // string  : grupo/m�dulo (acompanhar,hsit�rico,etc)
     *          $dados->tipoUsufruto     // string  : tipo do usufruto (parcial,total)
     *          $dados->ocorrencia       // string  : c�digo de ocorr�ncia
     *          $dados->debitoPadrao     // string  : c�digo d�bito padr�o
     *          $dados->diferenca        // string  : diferen�a no dia
     *          $dados->jornp            // string  : jornada prevista no dia
     *          $dados->jornd            // string  : jornada realizada no dia
     *          $dados->jornadaPrevista  // string  : jornada prevista (cadastro/jornada hist�rico)
     *          $dados->jornadaRealizada // string  : jornada realizada (cadastro/jornada hist�rico)
     *          $dados->idreg            // string  : indica quem registrou (S)servidor, (C)hefia, etc
     *          $dados->registro_ip      // string  : IP da m�quina
     *          $dados->registro_siape   // string  : Matr�cula do operador
     * @return object
     *          $retorno->ocorrencia         // string  : ocorrencia destina��o
     *          $retorno->horasNegativas     // boolean : Horas negativas (true)
     *          $retorno->mensagemNegativa   // astring : mensagem, se hora negativa
     *          $retorno->diferenca          // diferen�a restante ap�s usufruto Banco de Horas
     *          $retorno->bool               // Resultado das valida��es do limite semanal e mensal
     *          $retorno->horasUsadaUsufruto // Horas utilizadas do Banco de Horas
     */
    public function verificaCondicoesUsufrutoBancoDeHoras( $dados )
    {
        return $this->tabBancoDeHorasAcumulosModel->verificaCondicoesUsufrutoBancoDeHoras( $dados );
    }
    

    /**
     * @param string $mat
     * @param string $tipoUsufruto
     * @return bool
     */
    public function verifyExistsAutorization($mat, $tipoUsufruto = 'total')
    {
        return $this->tabBancoDeHorasAcumulosModel->verifyExistsAutorization($mat, $tipoUsufruto);
    }


    /**
     * @param $matricula
     * @param $diferenca
     * @return bool
     * @info Valida��o de osufruto dentro do m?s corrente
     */
    public function validateBalanceMonth($matricula = null, $diferenca = null)
    {
        return $this->tabBancoDeHorasAcumulosModel->validateBalanceMonth($matricula, $diferenca);
    }

    /**
     * @info Valida��o de o sufruto dentro da semana corrente
     *
     * @param $matricula
     * @param $diferenca
     * @return bool
     */
    public function validateBalanceWeek($matricula = null, $diferenca = null, $dia = null)
    {
        return $this->tabBancoDeHorasAcumulosModel->validateBalanceWeek($matricula, $diferenca, $dia = null);
    }
    
    /**
     * 
     * @param string      $mat Matr�cula do servidor
     * @param string/date $dia Dia da ocorr�ncia
     * @return array
     */
    public function selectAcumuloBancoDeHorasPorSiapeDia( $mat=null, $dia=null )
    {
        return $this->tabBancoDeHorasAcumulosModel->selectAcumuloBancoDeHorasPorSiapeDia( $mat, $dia );
    }
    

    /**
     * @info Seleciona dados de acumulo de banco de horas
     *
     * @param string  $mat Matr�cula SIAPE
     * @param integer $ciclo_id ID do ciclo de acumulo/usufruto
     * @return array  ('usufruto','saldo')
     */
    public function selectAcumuloBancoDeHorasPorSiapeCiclo( $mat = null, $ciclo_id = null )
    {
        return $this->tabBancoDeHorasAcumulosModel->selectAcumuloBancoDeHorasPorSiapeCiclo( $mat, $ciclo_id );
    }


    /**
     * @info Atualiza os saldos de acumulo de banco de horas
     *
     * @param string  $mat                      Matr�cula SIAPE
     * @param integer $ciclo_id                 ID do ciclo de acumulo/usufruto
     * @param integer $saldoAtualizadoUsufruto  VAlor para atualizar banco de horas
     * @return integer/false
     */
    public function updateAcumuloBancoDeHorasUsufruto( $mat = null, $ciclo_id = null , $saldoAtualizadoUsufruto = 0 )
    {
        return $this->tabBancoDeHorasAcumulosModel->updateAcumuloBancoDeHorasUsufruto( $mat, $ciclo_id, $saldoAtualizadoUsufruto );
    }


    /**
     * @info Atualiza os saldos de acumulo de banco de horas
     *
     * @param string  $mat                      Matr�cula SIAPE
     * @param integer $ciclo_id                 ID do ciclo de acumulo/usufruto
     * @param integer $saldoAtualizadoUsufruto  VAlor para atualizar banco de horas
     * @return integer/false
     */
    public function insertAutorizacoesServidoresUsufruto( $mat=null, $ciclo_id=null, $tipo_solicitacao=null, $dateiniformated=null, $datefimformated=null )
    {
        return $this->tabBancoDeHorasAcumulosModel->insertAutorizacoesServidoresUsufruto( $mat, $ciclo_id, $tipo_solicitacao, $dateiniformated, $datefimformated );
    }


    /**
     * @info Atualiza os saldos de acumulo de banco de horas - Hist�rico
     *
     * @param string  $mat                      Matr�cula SIAPE
     * @param integer $ciclo_id                 ID do ciclo de acumulo/usufruto
     * @param integer $saldoAtualizadoUsufruto  VAlor para atualizar banco de horas
     * @return integer/false
     */
    public function insertAutorizacoesServidoresUsufrutoHistorico( $mat=null, $ciclo_id=null, $dateiniformated=null, $datefimformated=null, $tipo_solicitacao=null  )
    {
        return $this->tabBancoDeHorasAcumulosModel->insertAutorizacoesServidoresUsufrutoHistorico( $mat, $ciclo_id, $dateiniformated, $datefimformated, $tipo_solicitacao );
    }
    

    /**
     * @info Apura o saldo de usufruto dentro da semana corrente
     *
     * @param string $mat Matr�cula SIAPE
     * @param string $inicio Data inicial
     * @param string $fim Data final
     * @return boolean
     */
    public function saldoHistoricoMovimentacoesAcumulosBancoDeHorasPorSiapePeriodo( $mat = null, $inicio = null, $fim = null )
    {
        return $this->tabBancoDeHorasAcumulosModel->saldoHistoricoMovimentacoesAcumulosBancoDeHorasPorSiapePeriodo( $mat, $inicio, $fim );
    }


    /**
     * @info Apura o saldo de usufruto por per�odo
     * 
     * @param string $mat Matr�cula SIAPE
     * @param string $inicio Data inicial
     * @param string $fim Data final
     * @return boolean
     */
    public function getUsufrutoPorSiapePeriodo( $mat = null, $inicio = null, $fim = null )
    {
        return $this->tabBancoDeHorasAcumulosModel->getUsufrutoPorSiapePeriodo( $mat, $inicio, $fim );
    }


    /**
     * @info Apura o saldo de horas acumuladas por per�odo
     *
     * @param string $mat Matr�cula SIAPE
     * @param string $inicio Data inicial
     * @param string $fim Data final
     * @return boolean
     */
    public function getAcumuloHorasPorSiapePeriodo( $mat = null, $inicio = null, $fim = null )
    {
        return $this->tabBancoDeHorasAcumulosModel->getAcumuloHorasPorSiapePeriodo( $mat, $inicio, $fim );
    }


    /**
     * @info Apura o saldo de usufruto por per�odo
     *
     * 
     * @param string $mat Matr�cula SIAPE
     * @param string $inicio Data inicial
     * @param string $fim Data final
     * @return boolean
     */
    public function getSaldoBancoDeHorasPorSiapePeriodo( $mat = null, $inicio = null, $fim = null )
    {
        return $this->tabBancoDeHorasAcumulosModel->getSaldoBancoDeHorasPorSiapePeriodo( $mat, $inicio, $fim );
    }


    /**
     * @info Exibe quadro de saldo
     *
     * @param void
     * @return void
     */
    public function showQuadroDeSaldo($mat = null, $ciclo_id = null, $ano = null, $so_ano_corrente = true)
    {
        $dados = $this->SaldosBancoDeHorasCicloYear( $mat, $ciclo_id, $ano, $so_ano_corrente );
        $this->tabBancoDeHorasAcumulosView->showQuadroDeSaldo( $dados );
    }

} // END class TabBancoDeHorasAcumulosController
