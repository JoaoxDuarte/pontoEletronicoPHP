<?php

	include_once("config.php");

	verifica_permissao( 'logado' );

	// parametros passados por formulario
	$lSiape     = anti_injection($_POST['lSiape']);
	$senhaatual = anti_injection($_POST['senhaatual']);
	$senhanova  = anti_injection($_POST['senhanova']);
	$senhanova_confirmar = anti_injection($_POST['senhanova_confirmar']);

	// resulado da operação
	$msg_erro = '';
	$result = array();

	// valida os dados
	if (empty($senhaatual))           { $msg_erro .= ". Senha utilizada atualmente não informada!\n"; }
	else if (strlen($senhaatual) < 8) { $msg_erro .= ". É obrigatório informar a senha utilizada atualmente!\n"; }

	if (empty($senhanova))              { $msg_erro .= ". NOVA Senha não informada!\n"; }
	else if (strlen($senhanova) < 8)    { $msg_erro .= ". A NOVA Senha é obrigatória e deve ter 8 caracteres alfanuméricos!\n"; }
	else if ($senhaatual == $senhanova) { $msg_erro .= ". A NOVA Senha deverá ser diferente da senha utilizada atualmente!\n"; }

	if (empty($senhanova_confirmar))             { $msg_erro .= ". Senha de confirmação não informada!\n"; }
	else if (strlen($senhanova_confirmar) < 8)   { $msg_erro .= ". A Senha de confirmação é obrigatória e deve ter 8 caracteres alfanuméricos!\n"; }
	else if ($senhanova != $senhanova_confirmar) { $msg_erro .= ". A Senha de confirmação não confere com a NOVA Senha!\n"; }

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

		// verifica usuário e senha
		$oDBase->query( "SELECT * FROM usuarios WHERE siape = '$lSiape' AND senha='$senhaatual' " );
		$numrows = $oDBase->num_rows();

		if ($numrows > 0)
		{
			$senhanova = substr(md5($senhanova),0,14);
			$oDBase->query( "UPDATE usuarios SET senha='$senhanova', prazo='0' WHERE siape='$lSiape' " );

			$msg_erro = "Senha alterada com sucesso!";

			// grava o LOG
			registraLog( "alterou a senha do usuário $lSiape na máquina de IP ".getIpReal() );
			// fim do LOG
		}
		else
		{
			$msg_erro = "Usuário não encontrado!";
		}
		$result[0] = array(
			'siape'    => $siape,
			'mensagem' => utf8_iso88591($msg_erro)
		);
	}

	$myData = array('dados' => $result);

	print json_encode($myData);
