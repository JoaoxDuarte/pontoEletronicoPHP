<?php

include_once( "config.php" );
include_once( "src/controllers/TabUsuariosController.php" );

// dados - formulario
$formSiape   = anti_injection($_REQUEST['siape']);
$formIdUnica = anti_injection($_REQUEST['idunica']);
$formDtNasc  = anti_injection($_REQUEST['dt_nasc']);

$formDtNascTeste = $formDtNasc;

$formDtNasc  = substr($formDtNasc,4,4) . "-" . 
               substr($formDtNasc,2,2) . "-" . 
               substr($formDtNasc,0,2);

$dados = explode("|",$_SESSION['sReiniciaSenha']);
$siape   = $dados[0];
$cpf     = $dados[1];
$idunica = $dados[2];
$dt_nasc = $dados[3];
$email   = $dados[4];
$lotacao = $dados[5];
$upag    = $dados[6];

if ( !array_key_exists( "upag", $_SESSION ))
{
    $_SESSION['upag'] = $upag;
}

$severidade = 'danger';

// class valida
$validar = new valida();
$validar->setDestino( $destino_erro );
$validar->setExibeMensagem( false );

## MATRÍCULA SIAPE
#
$validar->siape( $formSiape );
$mensagem = $validar->getMensagem();

if ( !empty($mensagem) )
{
    retornaInformacao(str_replace('-','',$mensagem),$severidade);
}
if ($siape != getNovaMatriculaBySiape($formSiape))
{
    $mensagem = "Matrícula SIAPE informada não pertence a este usuário!";
    retornaInformacao($mensagem,$severidade);
}


## IDENTIFICAÇÃO ÚNICA
#
$vetIdUnica = array(
    '000000000x',
    '111111111',
    '222222222',
    '333333333',
    '444444444',
    '555555555',
    '666666666',
    '777777777',
    '888888888x',
    '999999999'
);

if (strlen(trim($formIdUnica)) < 9)
{
    $mensagem = "É obrigatório informar a identificação única com 9 números!";
    retornaInformacao($mensagem,$severidade);
}
else if (in_array($formIdUnica,$vetIdUnica))
{
    $mensagem = "Identificação única inválida!";
    retornaInformacao($mensagem,$severidade);
}
else if ($idunica != $formIdUnica)
{
    $mensagem = "Identificação Única informada não pertence a este usuário!";
    retornaInformacao($mensagem,$severidade);
}


## DATA DE NASCIMENTO
#
if (validaData($formDtNasc) == false)
{
    $mensagem = "Data ".databarra($formDtNasc).", inválida!";
    retornaInformacao($mensagem,$severidade);
}
else if ($dt_nasc != $formDtNascTeste)
{
    $mensagem = "Data de Nascimento informada não pertence a este usuário!";
    retornaInformacao($mensagem,$severidade);
}

$siape   = getNovaMatriculaBySiape($formSiape);

// dados do servidor
$oServativ = verificaSeExisteServidor($siape);
$email     = $oServativ->email;
$ssenhat   = substr(md5($oServativ->dt_nasc), 0, 14);

atualizaSenhaUsuario($siape, $ssenhat, $email, $oServativ);

exit();



/* *********************************************************
 *                                                         *
 *                 FUNÇÕES COMPLEMENTARES                  *
 *                                                         *
 ********************************************************* */

/**
 *
 * @param string $siape
 * @return object
 */
function verificaSeExisteServidor($siape)
{
    global $pagina_de_destino;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_destino);
    $oDBase->setMensagem("Problema no acesso ao cadstro de Usuários!");

    $oDBase->query("
    SELECT 
        DATE_FORMAT(a.dt_nasc, '%d%m%Y') AS dt_nasc, 
        a.email,
        a.nome_serv,
        a.cod_lot,
        tabsetor.upag,
        a.defvis
    FROM 
        servativ AS a
    LEFT JOIN 
        tabsetor ON a.cod_lot = tabsetor.codigo
    WHERE   
        a.mat_siape = :siape
        AND a.excluido='N'
    ",
    array(
        array( ':siape', getNovaMatriculaBySiape($siape), PDO::PARAM_STR ),
    ));

    if ($oDBase->num_rows() == 0)
    {
        $msg_erro = "Servidor não localizado, Matrícula inválida!";
        $result[0] = array(
            'siape'    => $siape,
            'mensagem' => utf8_iso88591($msg_erro)
        );

        retornaInformacao($result,$severidade);
    }

    return $oDBase->fetch_object();
}


/**
 *
 * @global string $pagina_de_destino
 *
 * @param string $siape
 * @param string $ssenhat
 * @param string $email
 */
function atualizaSenhaUsuario($siape, $ssenhat, $email, $dados)
{
    global $pagina_de_destino;

    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Erro no acesso ao cadastro de Usuário!");
    $oDBase->setDestino($pagina_de_destino);

    $oDBase->query("SELECT siape FROM usuarios WHERE siape = :siape", array(
        array(':siape', $siape, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() == 0)
    {
        $oDBase->query("
        INSERT INTO usuarios
            SET
                siape  = :siape,
                nome   = :nome,
                acesso = :acesso,
                setor  = :setor,
                senha  = :senha,
                prazo  = :prazo,
                upag   = :upag,
                defvis = :defvis
        ",
        array(
            array(':siape',  getNovaMatriculaBySiape($siape),   PDO::PARAM_STR),
            array(':nome',   $dados->nome_serv, PDO::PARAM_STR),
            array(':acesso', 'NNSNNNNNNNNNN',     PDO::PARAM_STR),
            array(':setor',  $dados->cod_lot,   PDO::PARAM_STR),
            array(':senha',  $ssenhat,            PDO::PARAM_STR),
            array(':prazo',  '1',                 PDO::PARAM_STR),
            array(':upag',   $dados->upag,      PDO::PARAM_STR),
            array(':defvis', $dados->defvis,    PDO::PARAM_STR),
        ));
    }
    else
    {
        $oDBase->query("
        UPDATE usuarios
            SET senha = :senha, prazo = '1'
                WHERE siape = :siape
        ",
        array(
            array( ':senha', $ssenhat,   PDO::PARAM_STR ),
            array( ':siape', getNovaMatriculaBySiape($siape), PDO::PARAM_STR ),
        ));
    }

    // grava o LOG
    registraLog("reinicializou a senha do usuário ".getNovaMatriculaBySiape($siape)." na máquina de IP ".getIpReal());
    // fim do LOG
    
    enviarEmail(
        $email,
    "SOLICITACAO DE REINICIALIZACAO DE SENHA", 
    "<br><br><big>"
    . "Prezado servidor,<br>"
    . "Informamos que sua senha foi reinicializada passando a partir desse "
    . "momento a ser \"" . $dt_nasc . "\" (sua data de nascimento), sendo "
    . "obrigatória a troca desta senha no primeiro acesso.<br>"
    . "Caso não tenha efetuado esta solicitação altere imediatamente sua "
    . "senha.<br>"
    . " Atenciosamente<br> "
    . "Equipe SISREF."
    . "</big><br><br>"
    );

    $mensagem = "Senha reiniciada com sucesso!";
    retornaInformacao($mensagem,"success");
}
