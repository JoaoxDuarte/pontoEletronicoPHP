<?php

// conexao ao banco de dados, funcoes diversas
include_once( "config.php" );
include_once( "class_form.frequencia.php" );
include_once( "src/controllers/TabHomologadosController.php" );

verifica_permissao('sRH e sTabServidor');

include_once('ilegal_grava.php');

// parametro passado por formulario
$observacao = $_REQUEST["observacao"];

// dados em sessao
$mat     = anti_injection($_REQUEST["siape"]);
$lotacao = anti_injection($_REQUEST["lotacao"]);
$mes     = anti_injection($_REQUEST["mes2"]); // mes
$ano     = anti_injection($_REQUEST["ano2"]); // ano
$siape_responsavel = anti_injection($_REQUEST["siape_responsavel"]); // siape_responsavel

$comp    = $mes . $ano; // competencia (mmaaaa)
$compinv = $ano . $mes;

// data atual
$dthomol = date("Y-m-d");

// marca��o: Homologa��o (Chefia) ou Verifica��o (RH)
$freqh = 'V';

$sMatricula = $_SESSION["sMatricula"];


// c�digo de ocorr�ncia 88888 - Registro Parcial
$se_codigo_88888 = $_REQUEST['teste'];
settype($se_codigo_88888);

// c�digo de ocorr�ncia 99999 - Sem Frequ�ncia
$se_codigo_99999 = $_REQUEST['teste9'];
settype($se_codigo_99999);

// c�digo de ocorr�ncia ----- - Sem Ocorr�ncia informada
$se_codigo_tracos = $_REQUEST['teste_tracos'];
settype($se_codigo_tracos);

$total_de_registro_no_mes = $_REQUEST['teste2'];
settype($total_de_registro_no_mes);

$total_de_dias_no_mes = $_REQUEST['teste3'];
settype($total_de_dias_no_mes);

$siape = getNovaMatriculaBySiape($siape);


$oDBase = selecionaServidor($siape);
$sitcad = $oDBase->fetch_object()->sigregjur;

// instancia grupo de ocorrencia
$obj = new OcorrenciasGrupos();
$codigoRegistroParcialPadrao = $obj->CodigoRegistroParcialPadrao($sitcad);
$codigoSemFrequenciaPadrao   = $obj->CodigoSemFrequenciaPadrao($sitcad);
$codigosTrocaObrigatoria     = $obj->CodigosTrocaObrigatoria($sitcad);

// verifica��o dos dados
$registroParcialPadrao = implode(', ', $codigoRegistroParcialPadrao);
$semFrequenciaPadrao   = implode(', ', $codigoSemFrequenciaPadrao);
$trocaObrigatoria      = $codigosTrocaObrigatoria[0];

##
# instancia o formulario para uso
# das funcoes de frequencia
#
$oForm = new formFrequencia;
$oForm->setSiape($mat); // matricula do servidor que se deseja alterar a frequencia
$oForm->setMes($mes); // mes que se deseja alterar a frequencia
$oForm->setAno($ano); // ano que se deseja alterar a frequencia
$oForm->setNomeDoArquivo($_SESSION['sHArquivoTemp']); // nome do arquivo de trabalho, neste caso, o temporario


## classe para montagem do formulario padrao
#
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


if ($se_codigo_88888 > 0 && $se_codigo_99999 > 0 && $se_codigo_tracos > 0)
{
    mensagem('N�o � permitido homologar frequ�ncia com c�digo ' . $registroParcialPadrao . ', ' . $semFrequenciaPadrao . '\\ne dias sem ocorr�ncia indicada (\"' . $trocaObrigatoria . '\") na ficha do servidor! ', null, 1);
}
else if ($se_codigo_88888 > 0 && $se_codigo_99999 > 0) // 88888 e 99999
{
    mensagem('N�o � permitido homologar frequ�ncia com c�digo ' . $registroParcialPadrao . ' e ' . $semFrequenciaPadrao . ' na ficha do servidor!', null, 1);
}
else if ($se_codigo_88888 > 0 && $se_codigo_tracos > 0) // 88888 e -----
{
    mensagem('N�o � permitido homologar frequ�ncia com c�digo ' . $registroParcialPadrao . '\ne dias sem ocorr�ncia indicada (\"' . $trocaObrigatoria . '\") na ficha do servidor!', null, 1);
}
else if ($se_codigo_99999 > 0 && $se_codigo_tracos > 0) // 99999 e -----
{
    mensagem('N�o � permitido homologar frequ�ncia com c�digo ' . $semFrequenciaPadrao . '\ne dias sem ocorr�ncia indicada (\"' . $trocaObrigatoria . '\") na ficha do servidor!', null, 1);
}
else if ($se_codigo_88888 > 0)
{
    mensagem('N�o � permitido homologar frequ�ncia com c�digo ' . $registroParcialPadrao . ' na ficha do servidor!', null, 1);
}
else if ($se_codigo_99999 > 0)
{
    mensagem('N�o � permitido homologar frequ�ncia com c�digo ' . $semFrequenciaPadrao . ' na ficha do servidor!', null, 1);
}
else if ($se_codigo_tracos > 0)
{
    mensagem('N�o � permitido homologar frequ�ncia com dias sem ocorr�ncia indicada (\"' . $trocaObrigatoria . '\") na ficha do servidor!', null, 1);
}
else if ($total_de_registro_no_mes < $total_de_dias_no_mes)
{
    mensagem('Est� faltando dias na ficha do servidor complete para que seja poss�vel homologar!', null, 1);
}

// instancia o banco de dados
$oDBase = new DataBase();
$oDBase->setDestino($_SESSION['voltar_nivel_1']);

// grava o texto (observacao)
$oDBase->setMensagem("Falha na leitura da observa��o (hist�rico)");
$oDBase->query("SELECT * FROM historico_observacoes WHERE siape='$mat' AND compet='$compinv' ");
$rows = $oDBase->num_rows();

if ($rows == 0)
{
    $oDBase->setMensagem("Falha na inclus�o da observa��o (hist�rico)");
    $oDBase->query("INSERT INTO historico_observacoes (compet, siape, observacao, ip, siaperh, registrado_em) VALUES ( '$compinv', '$mat', '$observacao', '$ip', '$sMatricula', now() ) ");
}
else
{
    $oDBase->setMensagem("Falha na grava��o da observa��o (hist�rico)");
    $oDBase->query("UPDATE historico_observacoes SET compet = '$compinv', siape = '$mat', observacao = '$observacao', ip = '$ip', siaperh = '$sMatricula', registrado_em = now() WHERE compet = '$compinv' and siape = '$mat' ");
}


// instancia homologados
$objTabHomologadosController = new TabHomologadosController();
$objTabHomologadosController->registraHomologacao( $mat, $mes, $ano, $siape_responsavel, $lotacao );

// grava o LOG
registraLog(' conclu�da altera��o (hist�rico) da matr�cula ' . $mat, '', '', 'Hist�rico');

$oForm->copiaTemporarioParaPonto();

// instancia o banco de dados
$oDBase->query("INSERT INTO control_historico SET ip = '" . $_SERVER['REMOTE_ADDR'] . "', siape_rh = '" . $sMatricula . "', lotacao = '" . $_SESSION['sLotacao'] . "', siape = '" . $mat . "', compet='" . $compinv . "', operacao = 'Gravou alteracoes.', datahora=NOW() ");

// apaga arquivo temporario, se existir
$oDBase->query("DROP TABLE IF EXISTS " . $_SESSION['sHArquivoTemp']);

$aHorasComuns = recalcularHorasComuns($mat, '11/2009');

// finaliza��o das altera��es
mensagem('Altera��o Hist�rico realizada com sucesso!', 'historico_frequencia.php');


// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
