<?php

/*
 ******************************************************************************
 *                                                                            *
 *  Cálculo da diferença devida, para registros com código 88888              *
 *  No dia seguinte ao do registro da frequência                              *
 *                                                                            *
 ******************************************************************************
 */

include_once( 'config.php' );


/**  @Class
* +--------------------------------------------------------------------+
* | @Class    : CalculoDiferenca88888                                  |
* | @description : Apura horas trabalhadas em dia com registro parcial |
* |                (88888), registra diferença calculada (negativa) em |
* |                dia anterior e não alteradas pela chefia imediata   |
* |                                                                    |
* | Autor     : Edinalvo Rosa                                          |
* +--------------------------------------------------------------------+
**/
class CalculoDiferenca88888
{
    private $dataSys;

	private $data;
	private $mes;
	private $ano;

	public function __construct ()
	{
		$this->dataSys = date('Y-m-d');

        $data_menos_um_dia = subtrai_dias_da_data($this->dataSys, 1);

		$this->data = conv_data($data_menos_um_dia);
		$this->mes = dataMes( $this->data );
		$this->ano = dataAno( $this->data );
	}

	public function executa()
	{
		if ($this->GravaHistorico88888())
		{
			$this->CalculaDiferenca88888();
			$this->AtualizaCampoDiferenca88888();
			$this->RegistraNoHistoricoPontoDiferenca88888();
			$this->AlteraPontoDiferenca88888();
		}
	}


	/*
	 * OCORRENCIAS 88888 PENDENTES, PARA REGISTRO NO HISTORICO
	 * ANTES DA ATUALIZAÇÃO DO PONTOmmAAAA APÓS CÁLCULO DA
	 * DIFERENÇA EXISTENTE, NOS CASOS DE SALDOS NEGATIVOS
	*/
	private function GravaHistorico88888()
	{
        $oDBase = new DataBase();

		$sql = "SHOW TABLES LIKE 'ponto".$this->mes.$this->ano."' ";
		$oDBase->query( $sql );
        $num = $oDBase->num_rows();

        if ($num > 0)
		{
			$sql = "CREATE TABLE IF NOT EXISTS _ocorrencias_88888_para_historico_antes_do_calculo_".$this->ano." LIKE ponto".$this->mes.$this->ano;
			$oDBase->query( $sql );

			$sql = "INSERT INTO _ocorrencias_88888_para_historico_antes_do_calculo_".$this->ano."
			SELECT pto.* FROM ponto".$this->mes.$this->ano." AS pto LEFT JOIN servativ AS cad ON pto.siape = cad.mat_siape WHERE pto.oco = '88888' AND pto.dia='".$this->data."' AND cad.excluido = 'N' AND cad.cod_sitcad NOT IN ('02','08','15','18') ORDER BY pto.siape ";
			$oDBase->query( $sql );
		}

		if ($oDBase->affected_rows() > 0)
        {
            $this->PrintMensagem( "Sucesso Grava Historico 88888()." );
        }
        else if ($num == 0)
        {
            $this->PrintMensagem( "Sem registro para Grava Historico 88888()." );
        }
        else
        {
            $this->PrintMensagem( "Erro ao executar Grava Historico 88888()." );
        }

		return ($num > 0);
	}


	/*
	 * OCORRENCIAS 88888 PENDENTES
	 * - CÁLCULO DAS DIFERENÇAS EXISTENTES
	*/
	private function CalculaDiferenca88888()
	{
        $oDBase = new DataBase();

		$sql = "SHOW TABLES LIKE '_ocorrencias_88888_calculo_diferenca_".$this->ano."' ";
		$oDBase->query( $sql );

        if ($oDBase->num_rows() == 0)
		{
    	    $sql = "CREATE TABLE IF NOT EXISTS _ocorrencias_88888_calculo_diferenca_".$this->ano;
        }
        else
        {
    	    $sql = "INSERT INTO _ocorrencias_88888_calculo_diferenca_".$this->ano;
        }

		$sql .= "
		SELECT
			pto.dia, pto.siape, pto.entra, pto.intini, pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif, pto.oco,
			SEC_TO_TIME(
			(
				IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,
					TIME_TO_SEC('00:00:00'),
					IF((TIME_TO_SEC(intsai)<>0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,
						TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),
						(TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))
					)
				)
				-
				IF(IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(jornp,5)),IF((TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))) > TIME_TO_SEC('07:01'), IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,0,TIME_TO_SEC('03:00')),TIME_TO_SEC('00:00')))
			) AS jorn_d,

			jornp AS jorn_p,

			IF(LEFT(SEC_TO_TIME(
			(
				IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,
					TIME_TO_SEC('00:00:00'),
					IF((TIME_TO_SEC(intsai)<>0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,
						TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),
						(TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))
					)
				)
				-
				IF(IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(jornp,5)),IF((TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))) > TIME_TO_SEC('07:01'), IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,0,TIME_TO_SEC('03:00')),TIME_TO_SEC('00:00'))
			) - TIME_TO_SEC(jornp)),1)='-','-','+') AS sinal,

			LEFT(RIGHT(SEC_TO_TIME(
			(
				IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,
					TIME_TO_SEC('00:00:00'),
					IF((TIME_TO_SEC(intsai)<>0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,
						TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),
						(TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))
					)
				)
				-
				IF(IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(jornp,5)),IF((TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))) > TIME_TO_SEC('07:01'), IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,0,TIME_TO_SEC('03:00')),TIME_TO_SEC('00:00'))
			) - TIME_TO_SEC(jornp)),8),5) AS jorn_dif

			FROM ponto".$this->mes.$this->ano." AS pto LEFT JOIN servativ AS cad ON pto.siape = cad.mat_siape WHERE pto.oco = '88888' AND pto.dia='".$this->data."' AND cad.excluido = 'N' AND cad.cod_sitcad NOT IN ('02','08','15','18') ORDER BY pto.siape
		";
		$oDBase->query( $sql );

		$sql = "SHOW INDEX FROM _ocorrencias_88888_calculo_diferenca_".$this->ano." ";
		$oDBase->query( $sql );

		if ($oDBase->num_rows() == 0)
		{
			$sql = "ALTER TABLE _ocorrencias_88888_calculo_diferenca_".$this->ano." ADD INDEX siape (siape), ADD INDEX dia (dia), ADD INDEX oco (oco) ";
			$oDBase->query( $sql );
		}
	}


	/*
	 * OCORRENCIAS 88888 PENDENTES ATUALIZAÇÃO DO JORNDIF COM O
	 * RESULTADO DO CÁLCULO DAS DIFERENÇAS NEGATIVAS EXISTENTES
	*/
	private function AtualizaCampoDiferenca88888()
	{
        $oDBase = new DataBase();

		$sql = "
		UPDATE _ocorrencias_88888_calculo_diferenca_".$this->ano." AS pto
		SET
			pto.jornd = LEFT(pto.jorn_d,5),
			pto.jorndif = pto.jorn_dif
		WHERE
			pto.oco = '88888' AND pto.sinal = '-' AND pto.dia='".$this->data."'
		";
		$oDBase->query( $sql );
	}


	/*
	 * REGISTRO NO HISTORICO DO PONTO DA FREQUENCIA QUE SOFRERÃO
	 * ALTERAÇÃO COM A ATUALIZAÇÃO DO JORNDIF DO PONTO COM O
	 * RESULTADO DO CÁLCULO DAS DIFERENÇAS NEGATIVAS EXISTENTES
	*/
	private function RegistraNoHistoricoPontoDiferenca88888()
	{
        $oDBase = new DataBase();

		$sql = "
		INSERT histponto".$this->mes.$this->ano."
		SELECT
			pto.dia, pto.siape, pto.entra, pto.intini, pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif, pto.oco, pto.idreg, pto.ip, pto.ip2, pto.ip3, pto.ip4, pto.ipch, pto.iprh, pto.matchef, pto.siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d') AS diaalt, DATE_FORMAT(NOW(),'%H:%i:%s') AS horaalt, 'XXXXXXX' AS siapealt, '' AS ipalt, 'A' AS idaltexc, pto.just, pto.justchef
		FROM _ocorrencias_88888_para_historico_antes_do_calculo_".$this->ano." AS pto LEFT JOIN _ocorrencias_88888_calculo_diferenca_".$this->ano." AS calc ON pto.siape = calc.siape AND pto.dia=calc.dia WHERE  pto.oco = '88888' AND calc.sinal = '-' AND pto.dia='".$this->data."' ORDER BY pto.dia
		";

        //print "[ " . date("Y-m-d H:i:s:xx") . " ] >> Sucesso.\n\n";
        //grava em banco
        //envia email

        $oDBase->query( $sql );
	}


	/*
	 * OCORRENCIAS 88888 PENDENTES ATUALIZAÇÃO DO JORNDIF COM O
	 * RESULTADO DO CÁLCULO DAS DIFERENÇAS NEGATIVAS EXISTENTES
	*/
	private function AlteraPontoDiferenca88888()
	{
        $oDBase = new DataBase();

		$sql = "
		UPDATE ponto".$this->mes.$this->ano." AS pto
		LEFT JOIN _ocorrencias_88888_calculo_diferenca_".$this->ano." AS dif ON pto.siape = dif.siape AND  pto.dia = dif.dia
		SET
			pto.jornd = LEFT(dif.jorn_d,5),
			pto.jorndif = dif.jorn_dif
		WHERE pto.oco = '88888' AND dif.sinal = '-' AND pto.dia='".$this->data."';
		";
		$oDBase->query( $sql );
	}


	/*
	 * PRINT MENSAGENS
	*/
    private function PrintMensagem($msg)
    {
        print "[" . date("Y-m-d H:i:s:u") . "] " . $msg;
    }
}



/*
 * Calcula
 */
$calculaDiferenca88888 = new CalculoDiferenca88888();
$calculaDiferenca88888->executa();

exit();
