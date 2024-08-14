<?php

	/*
	 *****************************************************************
	 *                                                               *
	 *  Cria tabelas necessárias para o novo ano:                    *
	 *    feriados_<ano>      - feriados no exercício                *
	 *    frq<ano>            - ocorrências agrupadas por período    *
	 *    ponto<mes><ano>     - frequências registradas              *
	 *    histponto<mes><ano> - histórico frequências registradas    *
	 *                                                               *
	 *****************************************************************
	 */
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	ini_set('display_errors','0');

	// conexão BD
	include_once "config.php";

	// Define limite duração do processo
	set_time_limit( 108000 );


/**  @Class
* +--------------------------------------------------------------------+
* | @Class    : CriaTabelasAnoCorrente                                 |
* | @description : Verifica se existem tabelas necessárias para ano    |
* |                corrente                                            |
* |                                                                    |
* | Autor     : Edinalvo Rosa                                          |
* +--------------------------------------------------------------------+
**/
class CriaTabelasAnoCorrente
{
	private $mes;
	private $ano;

	public function __construct ()
	{
		// Conexão com o banco de dados SISREF
		$this->mes = date('m');
		$this->ano = date('Y');
	}

	/*
	 *  FERIADOS_<ANO>
	 *  Datas de feriados nacionais, estaduais e municipais
	 */
	public function FeriadosAnoCorrente()
	{
        $oDBase = new DataBase();

        $anoIni = $this->ano;
        $anoFim = ($this->ano + 4);

        for ($ano = $anoIni; $ano <= $anoFim; $ano++)
        {
    		$table_new = "feriados_".$ano;
    		$table_old = "feriados_2019";

    		$sql = "CREATE TABLE IF NOT EXISTS ".$table_new." LIKE ".$table_old;
    		$oDBase->query( $sql );
        }
	}

	/*
	 *  FRQ_<ANO>
	 *  Agrupa ocorrências iguais, registradas em datas seguidas
	 *  Ex.: 00172 de 01 a 05/10/2009
	 *       33333 de 06 a 06/10/2009
	 *       00169 de 07 a 30/10/2009
	 */
	public function OcorrenciasAgrupadas()
	{
        $oDBase = new DataBase();

        $anoIni = $this->ano;
        $anoFim = ($this->ano + 4);

        for ($ano = $anoIni; $ano <= $anoFim; $ano++)
        {
    		$table_new = "frq".$ano;
    		$table_old = "frq2019";

    		$sql = "CREATE TABLE IF NOT EXISTS ".$table_new." LIKE ".$table_old;
    		$oDBase->query( $sql );
        }
	}

	/*
	 *  PONTO<MESANO>
	 *  Tabelas para registro das frequências dos servidores/estagiários
	 */
	public function RegistraFrequencia()
	{
        $oDBase = new DataBase();

        $anoIni = $this->ano;
        $anoFim = ($this->ano + 4);

        for ($ano = $anoIni; $ano <= $anoFim; $ano++)
        {
            for ($mes = 1; $mes <= 12; $mes++)
            {
                $compet = str_pad($mes, 2, "0", STR_PAD_LEFT) . $ano;

        		$table_new = "ponto".$compet;
        		$table_old = "ponto122019";

        		$sql = "CREATE TABLE IF NOT EXISTS ".$table_new." LIKE ".$table_old;
    		    $oDBase->query( $sql );

        		$sql = "CREATE TABLE IF NOT EXISTS ".$table_new."_auxiliar LIKE ".$table_old;
        		$oDBase->query( $sql );
            }
        }
	}

	/*
	 *  HISTPONTO<MESANO>
	 *  Tabelas para registro das alterações/ajustes em frequência dos servidores/estagiários
	 */
	public function RegistraHistoricoFrequencia()
	{
        $oDBase = new DataBase();

        $anoIni = $this->ano;
        $anoFim = ($this->ano + 4);

        for ($ano = $anoIni; $ano <= $anoFim; $ano++)
        {
            for ($mes = 1; $mes <= 12; $mes++)
            {
                $compet = str_pad($mes, 2, "0", STR_PAD_LEFT) . $ano;

    		    $table_new = "histponto".$compet;
    		    $table_old = "histponto122019";

    		    $sql = "CREATE TABLE IF NOT EXISTS ".$table_new." LIKE ".$table_old;
    		    $oDBase->query( $sql );

    		    $sql = "CREATE TABLE IF NOT EXISTS ".$table_new."_auxiliar LIKE ".$table_old;
        		$oDBase->query( $sql );
            }
        }
	}

}


/*
 * Cria tabelas
 */
$criaTabelasAnoCorrente = new CriaTabelasAnoCorrente();
$criaTabelasAnoCorrente->FeriadosAnoCorrente();
$criaTabelasAnoCorrente->OcorrenciasAgrupadas();
$criaTabelasAnoCorrente->RegistraFrequencia();
$criaTabelasAnoCorrente->RegistraHistoricoFrequencia();

exit();
