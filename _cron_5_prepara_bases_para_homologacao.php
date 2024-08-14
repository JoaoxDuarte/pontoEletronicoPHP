<?php

/*
 ******************************************************************************
 *                                                                            *
 *  Inicializa os bancos de dados para o início da homologação                *
 *  (SISREF), todo dia 1o. de cada mês às 3hs da manhã.                       *
 *                                                                            *
 *  Atualiza as seguintes tabela:                                             *
 *    - TabValida;                                                            *
 *    - ServAtiv;                                                             *
 *                                                                            *
 ******************************************************************************
 */

include_once( 'config.php' );


/**  @Class
* +--------------------------------------------------------------------+
* | @Class       : AbreParaHomologacao                                 |
* | @description : Atualiza o cadastro SISREF/SERVATIV a partir do     |
* |                cadastro SIAPECAD/CADMES (SIAPE)                    |
* | Autor: Edinalvo Rosa                                               |
* +--------------------------------------------------------------------+
**/
class AbreParaHomologacao
{
	public function __construct ()
	{
		//
	}

	public function executa()
	{
        $oDBase = new DataBase();

		$dia = date('j'); // dia do mês atual

		// abre para homologação
		if ($dia == 1)
		{
			$sysano = date('Y'); // ano atual do sistema
			$sysmes = date('n'); // mes atual do sistema

			$anoAnterior = ($sysmes == 1 ? ($sysano - 1) : $sysano);
			$mesAnterior = ($sysmes == 1 ? '12' : substr('00'.($sysmes - 1), -2));
			$mes_ano = $mesAnterior . $anoAnterior;

			$sql = 'UPDATE tabvalida SET ativo = "N" '; // desativa todos os meses
			$oDBase->query( $sql );

			$sql = 'UPDATE tabvalida SET ativo = "S" WHERE compi = "'.$mes_ano.'" '; // ativa o mes corrente
			$oDBase->query( $sql );

			$sql = 'UPDATE tabsetor SET tfreq = "N", dfreq = "N" WHERE ativo = "S" '; // registra N para tfreq e dfreq
			$oDBase->query( $sql );

			$sql = 'UPDATE servativ SET freqh = "N", motidev = "" '; // registra N freqh limpa motidev
			$oDBase->query( $sql );
		}
	}
}


/*
 * ABRE para HOMOLOGAÇÃO
 */
$obj = new AbreParaHomologacao();
$obj->executa();

exit();
