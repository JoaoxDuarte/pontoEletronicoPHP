<?php

	// Inicia a sessão e carrega as funções de uso geral
	include_once("config.php");

	// Verifica se existe um usuário logado e se possui permissão para este acesso
	verifica_permissao( 'sRH' );

	$modo = $_REQUEST['modo'];

	// instancia banco de dados
	$oDBase = new DataBase;

	if ($modo == "1")
	{

		//Recebe  a opção da página anterior
		//Recebendo os dados a serem incluidos
		$ini = $_REQUEST['inicio'];
		$rlot = $_REQUEST['rlot'];
		$sits = $_REQUEST['sits'];
		$siape = $_REQUEST['matricula'];
		$Nfuncao = $_REQUEST['Nfuncao'];
		$chefia = $_REQUEST['chefia'];
		$chef = $_REQUEST['chef'];
		$acesso = $_REQUEST['acesso'];
		$acess = $_REQUEST['acess'];
		$id = $_REQUEST['id'];
		$sit = $_REQUEST['situacao'];

		$vDatas = date("Y-m-d");

		if ( $sit == 'TITULAR')
		{
			$sit2 = "T";
		}
		elseif ( $sit == 'SUBSTITUTO')
		{
			$sit2 = "S";
		}
		elseif ( $sit == 'INTERINO')
		{
			$sit2 = "R";
		}

		// atualiza tabelas
		//$oDBase->query( "UPDATE servativ SET chefia = '$chef' WHERE mat_siape = '$siape' " );
		//$oDBase->query( "UPDATE usuarios SET acesso = '$acess' WHERE siape = '$siape' " );

		mensagem( "Função ocupada pelo servidor não permite mudar o status de chefia!\\nEsse campo não foi alterado." );
		reloadOpener();
		close();

	}
	elseif ($modo == "2" || $modo == "3")
	{

		$id = $_REQUEST['id'];
		$sit = $_REQUEST['sit'];
		$siape = $_REQUEST['siape'];

		$oDBase->query( "SELECT und.upag FROM servativ AS cad LEFT JOIN tabsetor AS und  ON cad.cod_lot = und.codigo AND und.ativo='S' WHERE cad.mat_siape='$siape' AND cad.excluido='N' AND cad.cod_sitcad NOT IN ('02','08','15') " );
		$upag = $oDBase->fetch_object()->upag;

		if ($_SESSION['upag'] != $upag)
		{
			mensagem( "Servidor não pertence a sua UPAG!" );
		}
		else
		{
			$oDBase->query( "SELECT chf.resp_lot FROM ocupantes AS chf WHERE chf.mat_siape='$siape' AND chf.dt_fim='0000-00-00' AND chf.resp_lot='S' AND chf.sit_ocup <> 'S' " );
			$resp_lot = $oDBase->fetch_object()->resp_lot;

			if ($sit == 'A')
			{
				$chefia = 'S';
			}
			else
			{
				$chefia = ($resp_lot == 'S' ?  'S' : 'N');
			}

			$oDBase->query( "UPDATE substituicao SET situacao = '$sit' WHERE siape = '$siape' AND id = '$id' " );
			$oDBase->query( "UPDATE servativ SET chefia = '".$chefia."' WHERE mat_siape = '$siape' " );
			$oDBase->query( "UPDATE usuarios SET acesso = CONCAT(LEFT(acesso,1),'".$chefia."',RIGHT(acesso,LENGTH(acesso)-2)) WHERE siape = '$siape' " );

			mensagem( "Dados Situação da substituição alterada sucesso!" );
		}

		reloadOpener();
		close();

	}
