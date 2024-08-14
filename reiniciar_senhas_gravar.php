<?php

/**
 * Reiniciar a senha do servidor - Gravar
 *
 * @version 
 * @author Edinalvo Rosa
 */
include_once( "config.php" );

// dados - formulario
$siape = $_REQUEST['siape'];
$cpf   = strtr($_REQUEST['cpf'], array('.' => '', ' ' => ''));


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// resulado da operação
$msg_erro = '';

// valida os dados
if (empty($siape))
{
    $msg_erro .= "- Matrícula não informada!\\n";
}
else
{
    $valida   = new valida();
    $valida->siape($siape);
    $msg_erro .= $valida->getMensagem();
}

if (empty($cpf))
{
    $msg_erro .= ". CPF não informada!\\n";
}
else if (validaCPF($cpf) == false)
{
    $msg_erro .= "- CPF inválido!\\n";
}

if ($msg_erro != '')
{
    mensagem($msg_erro, 'reiniciar_senhas.php', 1, 'warning');
}
else
{
    // dados do servidor
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_destino);
    $oDBase->setMensagem("Problema no acesso ao banco de dados,\\npor favor tente outra vez!");

    $siape = getNovaMatriculaBySiape($siape);

    $oDBase->query(" 
    SELECT 
        DATE_FORMAT(a.dt_nasc, '%d%m%Y') AS dt_nasc, a.email 
    FROM 
        servativ AS a 
    WHERE 
        a.cpf = :cpf 
        AND a.mat_siape = :siape 
        AND a.excluido = 'N'
    ", array(
        array(':cpf', $cpf, PDO::PARAM_STR),
        array(':siape', $siape, PDO::PARAM_STR),
    ));

    $oServativ = $oDBase->fetch_object();
    $email     = $oServativ->email;
    $ssenhat   = substr(md5($oServativ->dt_nasc), 0, 14);

    if ($oDBase->num_rows() == 0)
    {
        mensagem("Matrícula e CPF não localizados!", 'reiniciar_senhas.php', 1, 'danger');
    }
    else
    {
        $oDBase->query("
        UPDATE usuarios 
        SET 
            senha = :senha, 
            prazo = '1' 
        WHERE siape = :siape
        ", array(
            array(':senha', $ssenhat, PDO::PARAM_STR),
            array(':siape', $siape, PDO::PARAM_STR),
        ));

        enviarEmail($email, 'REINICIALIZACAO DE SENHA', "<br><br><big>Prezado servidor,<br>Informamos que sua senha foi reinicializada passando a partir desse momento a ser sua <b>data de nascimento<b>, sendo obrigatória a troca desta senha no primeiro acesso.<br>Caso não tenha efetuado esta solicitação altere imediatamente sua senha.<br> Atenciosamente<br> SISREF.</big><br><br>");

        mensagem("Senha reiniciada com sucesso!", 'reiniciar_senhas.php', 1, 'success');
    }
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
