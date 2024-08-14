<?php

include_once( "config.php" );

verifica_permissao('sRH e sTabServidor');

// dados enviados por formulario
$tSiape  = anti_injection($_REQUEST['tSiape']);
$codocor = anti_injection($_REQUEST['codocor']);
$wnome   = anti_injection($_REQUEST['wnome']);
$dtnasc  = $_REQUEST['dt_nasc'];
$sitcad  = $_REQUEST['sitcad'];

$tSiape  = getNovaMatriculaBySiape($tSiape);

$excluido = 'N';

$ssenhat = substr(md5($dtnasc), 0, 14); // retorna senha para data de nascimento

$destino = 'cadastro_exclusao_cancela.php';


## classe para montagem do formulario padrao
#
$oForm = new formPadrao(); // instancia o formul�rio
$oForm->setSubTitulo("Cancelar Exclus�o de Servidores/Estagi�rios");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// instancia banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino( $destino );

// apaga registro de exclus�o
$oDBase->setMensagem("N�o h� registro de exclus�o desta matr�cula!");
$oDBase->query("
DELETE FROM exclus
WHERE
    siape = :siape
",
array(
    array( ':siape', $tSiape, PDO::PARAM_STR ),
));

// restabelece o registro no cadastro
$oDBase->query("
UPDATE
    servativ
SET
    excluido             = :excluido,
    reg_obito_dt         = '0000-00-00',
    oco_exclu_oco        = '',
    oco_exclu_dt         = '0000-00-00',
    oco_exclu_dl_cod     = '',
    oco_exclu_dl_num     = '',
    oco_exclu_dl_dt_publ = '0000-00-00',
    cod_sitcad           = :cod_sitcad
WHERE
    mat_siape = :siape
",
array(
    array( ':siape',      $tSiape,   PDO::PARAM_STR ),
    array( ':excluido',   $excluido, PDO::PARAM_STR ),
    array( ':cod_sitcad', $sitcad,   PDO::PARAM_STR ),
));

// reinicia a senha do usu�rio, retorna aa data de nascimento
$oDBase->query("
UPDATE
    usuarios
SET
    senha = :senha
WHERE
    siape = :siape
",
array(
    array( ':siape', $tSiape,  PDO::PARAM_STR ),
    array( ':senha', $ssenhat, PDO::PARAM_STR ),
));

// grava o LOG
registraLog(" cancelou a exclusao do servidor/estagi�rio $wnome");

mensagem("Exclus�o cancelada com sucesso!", $destino);


// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
