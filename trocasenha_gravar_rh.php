<?php

	include_once("config.php");

	verifica_permissao( 'logado' );

	// parametros passados por formulario
	$lSiape     = anti_injection($_POST['lSiape']);
	$senhaatual = anti_injection($_POST['senhaatual']);
	$senhanova  = anti_injection($_POST['senhanova']);
	$senhanova_confirmar = anti_injection($_POST['senhanova_confirmar']);

	// resulado da opera��o
	$msg_erro = '';
	$result = array();

	// valida os dados
	if (empty($senhaatual))           { $msg_erro .= ". Senha utilizada atualmente n�o informada!\n"; }
	else if (strlen($senhaatual) < 8) { $msg_erro .= ". � obrigat�rio informar a senha utilizada atualmente!\n"; }

	if (empty($senhanova))              { $msg_erro .= ". NOVA Senha n�o informada!\n"; }
	else if (strlen($senhanova) < 8)    { $msg_erro .= ". A NOVA Senha � obrigat�ria e deve ter 8 caracteres alfanum�ricos!\n"; }
	else if ($senhaatual == $senhanova) { $msg_erro .= ". A NOVA Senha dever� ser diferente da senha utilizada atualmente!\n"; }

	if (empty($senhanova_confirmar))             { $msg_erro .= ". Senha de confirma��o n�o informada!\n"; }
	else if (strlen($senhanova_confirmar) < 8)   { $msg_erro .= ". A Senha de confirma��o � obrigat�ria e deve ter 8 caracteres alfanum�ricos!\n"; }
	else if ($senhanova != $senhanova_confirmar) { $msg_erro .= ". A Senha de confirma��o n�o confere com a NOVA Senha!\n"; }

	if ($msg_erro != '')
	{
		$result[0] = array(
			'siape'    => '',
			'mensagem' => utf8_iso88591($msg_erro)
		);
	}
	else
	{
		$senhaatual2 = $senhaatual;
		$senhaatual = substr(md5($senhaatual),0,14);

		// instancia o banco de dados
		$oDBase = new DataBase('PDO');
		$oDBase->setMensagem( 'Erro no acesso ao banco de dados!' );
		$oDBase->setDestino( $_SESSION['sHOrigem_1'] );

		// verifica usu�rio e senha
		$oDBase->query( "SELECT * FROM usuarios WHERE siape = '$lSiape' AND senha='$senhaatual' " );
		$numrows = $oDBase->num_rows();

		if ($numrows > 0)
		{
			$senhanova = substr(md5($senhanova),0,14);
			$oDBase->query( "UPDATE usuarios SET senha='$senhanova', prazo='0' WHERE siape='$lSiape' " );

			$msg_erro = "Senha alterada com sucesso!";

			// grava o LOG
			registraLog( "alterou a senha do usu�rio $lSiape na m�quina de IP ".getIpReal() );
			// fim do LOG
		}
		else
		{
			$msg_erro = "Usu�rio n�o encontrado!";
		}
		$result[0] = array(
			'siape'    => $siape,
			'mensagem' => utf8_iso88591($msg_erro)
		);
	}

	$myData = array('dados' => $result);

	print json_encode($myData);
