<?php

include_once( "config.php" );

// dados - formulario
$formCPF      = $_REQUEST['cpf'];
$formIdUnica  = $_REQUEST['idunica'];
$formSiapeCAD = $_REQUEST['siapecad'];

// dados - sessao
$aDados   = explode('|', $_SESSION['sReiniciaSenha']);
$siape    = $aDados[0];
$cpf      = $aDados[1];
$idunica  = $aDados[2];
$siapecad = $aDados[3];
$email    = $aDados[4];

// resulado da operação
$msg_erro = '';
$result   = array();

// valida os dados
if (empty($formCPF))
{
    $msg_erro .= ". CPF não informado!\n";
}
else if (strlen($formCPF) < 11)
{
    $msg_erro .= ". CPF menor que 11 caracteres númericos!\n";
}
else if ($formCPF != $cpf)
{
    $msg_erro .= ". CPF diferente do registrado para este usuário!\n";
}

if (empty($formIdUnica))
{
    $msg_erro .= ". Identificação Única não informada!\n";
}
else if (strlen($formIdUnica) < 9)
{
    $msg_erro .= ". Identificação Única menor que 9 caracteres númericos!\n";
}
else if ($formIdUnica != $idunica)
{
    $msg_erro .= ". Identificação Única diferente da registrada para este usuário!\n";
}

if (empty($formSiapeCAD))
{
    $msg_erro .= ". SIAPECAD não informado!\n";
}
else if (strlen($formSiapeCAD) < 8)
{
    $msg_erro .= ". SIAPECAD menor que 8 caracteres númericos!\n";
}
else if ($formSiapeCAD != $siapecad)
{
    $msg_erro .= ". SIAPECAD diferente do registrado para este usuário!\n";
}

if ($msg_erro != '')
{
    $result[0] = array(
        'siape'    => '',
        'mensagem' => utf8_iso88591($msg_erro)
    );
}
else
{
    // dados do servidor
    $oDBase    = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_destino);
    $oDBase->setMensagem("Problema no acesso ao banco de dados,\\npor favor tente outra vez!");
    $oDBase->query("
			SELECT
				DATE_FORMAT(cad.dt_nasc, '%d%m%Y') AS dt_nasc,
				cad.email
			FROM
				servativ AS cad
			WHERE
				cad.mat_siape = '" . $siape . "'
				AND cad.mat_siapecad = '" . $siapecad . "'
				AND cad.ident_unica = '" . $idunica . "'
				AND cad.cpf = '" . $cpf . "'
				AND cad.excluido = 'N'
		");
    $oServativ = $oDBase->fetch_object();
    $email     = $oServativ->email;
    $dt_nasc   = $oServativ->dt_nasc;
    $ssenhat   = substr(md5($dt_nasc), 0, 14);

    if ($oDBase->num_rows() == 0)
    {
        $msg_erro = "Problemas na reinicialização da senha, tente mais tarde!";
    }
    else
    {
        $oDBase->query("UPDATE usuarios SET senha = '$ssenhat', prazo='1' WHERE siape='$siape' ");
        enviarEmail($email, 'SOLICITACAO DE REINICIALIZACAO DE SENHA', "<br><br><big>Prezado servidor,<br>Informamos que sua senha foi reinicializada passando a partir desse momento a ser \"" . $dt_nasc . "\" (sua data de nascimento), sendo obrigatória a troca desta senha no primeiro acesso.<br>Caso não tenha efetuado esta solicitação altere imediatamente sua senha.<br> Atenciosamente<br> SISREF.</big><br><br>");

        // grava o LOG
        registraLog("reinicializou a senha do usuário " . $siape . " na máquina de IP " . getIpReal());
        // fim do LOG

        $msg_erro = "Senha reiniciada com sucesso!";
    }
    $result[0] = array(
        'siape'    => $siape,
        'mensagem' => utf8_iso88591($msg_erro)
    );
}

$myData = array('dados' => $result);

print json_encode($myData);
