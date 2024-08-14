<?php

// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );
include_once( "class_form.justificativa.php" );

verifica_permissao("sRH ou Chefia");

$sLotacao = $_SESSION["sLotacao"];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    // Valores passados - encriptados
    $dados         = explode(":|:", base64_decode($dadosorigem));
    $mat           = $dados[0];
    $dia           = $dados[1];
    $cod_sitcad    = $dados[2];
    $cmd           = $dados[3];
    $so_ver        = $dados[4];
    $grupoOperacao = $dados[5]; //acompanhar ou homologar
}

// dados voltar
switch ($grupoOperacao)
{
    case 'acompanhar':
        $_SESSION['voltar_nivel_3'] = "frequencia_ver_justificativa.php?dados=" . $dadosorigem;
        $_SESSION['voltar_nivel_4'] = '';
        $_SESSION['voltar_nivel_5'] = '';
        break;

    case 'homologar':
        $_SESSION['voltar_nivel_1'] = $dadosorigem;
        $_SESSION['voltar_nivel_2'] = '';
        $_SESSION['voltar_nivel_3'] = '';
        $_SESSION['voltar_nivel_4'] = '';
        break;
}

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona nome do servidor e jornada
$oDBase->query("
		SELECT
			cad.nome_serv, cad.jornada, cad.cod_lot, pto.just, pto.oco, pto.idreg, pto.justchef
		FROM
			servativ AS cad
		LEFT JOIN
			ponto" . dataMes($dia) . dataAno($dia) . " AS pto ON cad.mat_siape = pto.siape
		WHERE
			cad.mat_siape = :mat_siape
			AND pto.dia=:dia
	",array(
    array( ':mat_siape', $mat, PDO::PARAM_STR ),
    array( ':dia', conv_data($dia), PDO::PARAM_STR ),
));

$oServidor = $oDBase->fetch_object();
$nome      = trata_aspas($oServidor->nome_serv);
$lot       = $oServidor->cod_lot;
$just      = trata_aspas($oServidor->just);
$justchef  = trata_aspas($oServidor->justchef);
$oco       = $oServidor->oco;
$idreg     = $oServidor->idreg;
$jnd1      = $oServidor->jornada;
$jnd       = formata_jornada_para_hhmm($jnd1);

$_SESSION['justificativa_chefia']   = $justchef;
$_SESSION['justificativa_servidor'] = $just;

if ($so_ver != 'sim' && ($_SESSION['sAPS'] == 'S'))
{
    $frequencia_alterar    = base64_encode($mat . ':|:' . $nome . ':|:' . $dia . ':|:' . $oco . ':|:' . $sLotacao . ':|:' . $idreg . ':|:' . $cmd . ':|:' . $jnd . ':|:' . $cod_sitcad . ':|:acompanhar');
    $destino_botao_avancar = 'javascript:window.location.replace("frequencia_alterar.php?dados=' . $frequencia_alterar . '");';
}
$destino_botao_voltar = 'javascript:window.location.replace("' . $_SESSION['voltar_nivel_2'] . '");';

## classe para montagem do formulario padrao
#
$oForm = new formJustificativa();
//$oForm->setJS( 'frequencia_justificativa_abono.js' );
$oForm->setCaminho('Frequência » Acompanhar » Registro de Comparecimento » Ver Justificativa');
$oForm->setSeparador(0);
$oForm->setLargura('795px');

$oForm->setSubTitulo("Justificativa para ocorr&ecirc;ncia");

$oForm->setAction("#");
$oForm->setOnSubmit("");

$oForm->setInputHidden("cmd", $cmd);
$oForm->setInputHidden("grupo", $grupo_nome);
$oForm->setInputHidden('dados', '');

$oForm->setSiape($mat);
$oForm->setNome($nome);
$oForm->setLotacao($sLotacao);

$oForm->setDia($dia);
$oForm->setOcorrencia($oco);

$oForm->setExigeJustificativaChefia(false);
$oForm->setJustificativaServidor($just, 'readonly');
$oForm->setJustificativaChefia($justchef, 'readonly');

$oForm->setDestinoAvancar($destino_botao_avancar);
$oForm->setDestinoRetorno($destino_botao_voltar);

$oForm->showForm();
