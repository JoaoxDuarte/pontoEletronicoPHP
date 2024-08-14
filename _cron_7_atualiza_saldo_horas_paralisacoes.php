<?php

/*
 ******************************************************************************
 *                                                                            *
 *  Cálculo da diferença devida, para registros de greve, no dia              *
 *  seguinte ao do registro da frequência.                                    *
 *                                                                            *
 ******************************************************************************
 */

include_once( 'config.php' );


/**  @Class
* +--------------------------------------------------------------------+
* | @Class       : AtualizaSaldoHorasParalisacoesCompensacoes          |
* | @description : Verifica substituições registradas e ativa/desativa |
* |                                                                    |
* | Autor: Edinalvo Rosa                                               |
* +--------------------------------------------------------------------+
**/
class AtualizaSaldoHorasParalisacoesCompensacoes
{
	private $conexao; // Conexão com o banco de dados
	private $conexao2; // Conexão com o banco de dados
	private $conexao3; // Conexão com o banco de dados

	public function __construct ()
	{
		// Conexão com o banco de dados
		$this->conexao = new DataBase();
		$this->conexao2 = new DataBase();
		$this->conexao3 = new DataBase();
	}

	// PARALISAÇÕES - CRÉDITOS E DÉBITOS
	public function CarregaRegistrosParalisacoes()
	{
		$competencia_atual = date('Ym');

        $result = $this->conexao->query( "SELECT id, inicio, fim, codigo_debito, codigo_credito, orgao FROM greves_periodos ORDER BY inicio DESC " );
        $greves = $this->conexao;

		// informações - paralizações
		/*
		$dados[1] = array( '1', '2015-07-07', '2015-09-30', "'00137','03131'", "greve_2015_adm " ); // ADM: 00137 (DEBITOS)
		$dados[2] = array( '1', '2015-07-07', '2016-05-31', "'90137'",         "greve_2015_adm " ); // ADM: 90137 (CREDITOS)
		$dados[3] = array( '2', '2015-09-04', '2015-11-25', "'00137','03131'", "greve_2015_pericia_medica " ); // Pericia: 00137 (DEBITOS)
		$dados[4] = array( '2', '2015-09-04', '2016-08-17', "'90137'",         "greve_2015_pericia_medica " ); // Pericia: 90137 (CREDITOS)
		$dados[5] = array( '3', '2015-11-26', '2016-01-22', "'10129'",         "greve_2015_pericia_medica " ); // Pericia: 10129 (DEBITOS)
		$dados[6] = array( '3', '2015-11-26', '2016-08-17', "'90129'",         "greve_2015_pericia_medica " ); // Pericia: 90129 (CREDITOS)
		*/
        /*
		$dados[1] = array( '1', '2015-07-07', '2015-09-30', "'00137','03131'", "greve_2015_adm " ); // ADM: 00137 (DEBITOS)
		$dados[2] = array( '1', '2015-07-07', '2016-12-22', "'90137'",         "greve_2015_adm " ); // ADM: 90137 (CREDITOS)
		$dados[3] = array( '2', '2015-09-04', '2015-11-25', "'00137','03131'", "greve_2015_pericia_medica " ); // Pericia: 00137 (DEBITOS)
		$dados[4] = array( '2', '2015-09-04', '2016-12-22', "'90137'",         "greve_2015_pericia_medica " ); // Pericia: 90137 (CREDITOS)
		$dados[5] = array( '3', '2015-11-26', '2016-01-22', "'10129'",         "greve_2015_pericia_medica " ); // Pericia: 10129 (DEBITOS)
		$dados[6] = array( '3', '2015-11-26', '2016-12-22', "'90129'",         "greve_2015_pericia_medica " ); // Pericia: 90129 (CREDITOS)
        */

        while ($dados1 = $greves->fetch_object())
        {
		    // informações - paralizações
		    $dados[1] = array( $dados1->id, $dados1->inicio, $dados1->fim, $dados1->codigo_debito, $dados1->orgao );
		    $dados[2] = array( $dados1->id, $dados1->inicio, $dados1->fim, $dados1->codigo_credito, $dados1->orgao );
        }

		$truncate = '';

		for ($y = 1; $y <= count($dados); $y++)
		{
		    $this->conexao2->query( "TRUNCATE TABLE greves_registros" );

			$ini = substr($dados[$y][1],0,4).substr($dados[$y][1],5,2);
			$fim = substr($dados[$y][2],0,4).substr($dados[$y][2],5,2);

			$fim = ($fim > $competencia_atual ? $competencia_atual : $fim);

			for ($x = $ini; $x <= $fim; $x++)
			{
				$mes = substr($x,-2);
				$ano = substr($x,0,4);

				$this->conexao2->query( "
				INSERT INTO greves_registros
				SELECT 0, pto.*, '".$dados[$y][0]."' AS id_greve
                    FROM ponto".$mes.$ano." AS pto
                        LEFT JOIN servativ AS cad ON pto.siape = cad.mat_siape
                        LEFT JOIN tabcargo AS cat ON cad.cod_cargo = cat.cod_cargo WHERE (pto.dia BETWEEN '".$dados[$y][1]."' AND '".$dados[$y][2]."') AND pto.oco IN (".$dados[$y][3].")
				" );

				if ($mes >= 12)
				{
					$x = ($ano+1) . '00'; // 'xxxx00' para ao 'for' incrementar o valor ficar xxxx01. Ex: 201600 => 201601
				}
			}
		}
	}

	/* TOTALIZA - CRÉDITOS E DÉBITOS */
	public function TotalizaDebitosCreditos()
	{
        $this->conexao->query( "SELECT id, codigo_debito, codigo_credito, orgao FROM greves_periodos ORDER BY inicio DESC " );
        $greves = $this->conexao;

        while ($dados1 = $greves->fetch_object())
        {
		    // informações - paralizações
		    $dados[1] = array( $dados1->id, $dados1->codigo_debito, $dados1->orgao );
		    $dados[2] = array( $dados1->id, $dados1->codigo_credito, $dados1->orgao );
        }

        // informações - paralizações
        /*
		$dados[1] = array( '1', "00137", "greve_2015_adm " ); // ADM: Debitos
		$dados[2] = array( '1', "90137", "greve_2015_adm " ); // ADM: Creditos
		$dados[3] = array( '2', "00137", "greve_2015_pericia_medica " ); // Pericia: Debitos
		$dados[4] = array( '2', "90137", "greve_2015_pericia_medica " ); // Pericia: Creditos
		$dados[5] = array( '3', "10129", "greve_2015_pericia_medica " ); // Pericia: Debitos
		$dados[6] = array( '3', "90129", "greve_2015_pericia_medica " ); // Pericia: Creditos
        */

		for ($y = 1; $y <= count($dados); $y++)
		{
			$campo = (substr($dados[$y][1],0,1) == '9' ? 'creditos' : 'debitos');

			$this->conexao2->query( "
			UPDATE greves_compensacoes_horas AS gch
			LEFT JOIN greves_registros AS adm ON adm.siape = gch.siape
			SET
			gch.".$campo." = (SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(adm2.jorndif))) FROM greves_registros AS adm2 WHERE adm2.siape = gch.siape AND adm2.oco = '".$dados[$y][1]."')
			WHERE gch.id_greve = '".$dados[$y][0]."'
			" );

		}

		// Totaliza diferença SALDOS
		$this->conexao3->query( "
		UPDATE greves_compensacoes_horas AS gch
		SET
		gch.total = SEC_TO_TIME(TIME_TO_SEC(gch.creditos) - TIME_TO_SEC(gch.debitos))
		" );
	}
}


/*
 * Calcula diferença devida, para registros com código 88888
 */
$AtualizaSaldoHoras = new AtualizaSaldoHorasParalisacoesCompensacoes();
$AtualizaSaldoHoras->CarregaRegistrosParalisacoes();
$AtualizaSaldoHoras->TotalizaDebitosCreditos();

exit();
