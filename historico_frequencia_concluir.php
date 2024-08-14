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

// marcação: Homologação (Chefia) ou Verificação (RH)
$freqh = 'V';

$sMatricula = $_SESSION["sMatricula"];


// código de ocorrência 88888 - Registro Parcial
$se_codigo_88888 = $_REQUEST['teste'];
settype($se_codigo_88888);

// código de ocorrência 99999 - Sem Frequência
$se_codigo_99999 = $_REQUEST['teste9'];
settype($se_codigo_99999);

// código de ocorrência ----- - Sem Ocorrência informada
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

// verificação dos dados
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
    mensagem('Não é permitido homologar frequência com código ' . $registroParcialPadrao . ', ' . $semFrequenciaPadrao . '\\ne dias sem ocorrência indicada (\"' . $trocaObrigatoria . '\") na ficha do servidor! ', null, 1);
}
else if ($se_codigo_88888 > 0 && $se_codigo_99999 > 0) // 88888 e 99999
{
    mensagem('Não é permitido homologar frequência com código ' . $registroParcialPadrao . ' e ' . $semFrequenciaPadrao . ' na ficha do servidor!', null, 1);
}
else if ($se_codigo_88888 > 0 && $se_codigo_tracos > 0) // 88888 e -----
{
    mensagem('Não é permitido homologar frequência com código ' . $registroParcialPadrao . '\ne dias sem ocorrência indicada (\"' . $trocaObrigatoria . '\") na ficha do servidor!', null, 1);
}
else if ($se_codigo_99999 > 0 && $se_codigo_tracos > 0) // 99999 e -----
{
    mensagem('Não é permitido homologar frequência com código ' . $semFrequenciaPadrao . '\ne dias sem ocorrência indicada (\"' . $trocaObrigatoria . '\") na ficha do servidor!', null, 1);
}
else if ($se_codigo_88888 > 0)
{
    mensagem('Não é permitido homologar frequência com código ' . $registroParcialPadrao . ' na ficha do servidor!', null, 1);
}
else if ($se_codigo_99999 > 0)
{
    mensagem('Não é permitido homologar frequência com código ' . $semFrequenciaPadrao . ' na ficha do servidor!', null, 1);
}
else if ($se_codigo_tracos > 0)
{
    mensagem('Não é permitido homologar frequência com dias sem ocorrência indicada (\"' . $trocaObrigatoria . '\") na ficha do servidor!', null, 1);
}
else if ($total_de_registro_no_mes < $total_de_dias_no_mes)
{
    mensagem('Está faltando dias na ficha do servidor complete para que seja possível homologar!', null, 1);
}

// instancia o banco de dados
$oDBase = new DataBase();
$oDBase->setDestino($_SESSION['voltar_nivel_1']);

// grava o texto (observacao)
$oDBase->setMensagem("Falha na leitura da observação (histórico)");
$oDBase->query("SELECT * FROM historico_observacoes WHERE siape='$mat' AND compet='$compinv' ");
$rows = $oDBase->num_rows();

if ($rows == 0)
{
    $oDBase->setMensagem("Falha na inclusão da observação (histórico)");
    $oDBase->query("INSERT INTO historico_observacoes (compet, siape, observacao, ip, siaperh, registrado_em) VALUES ( '$compinv', '$mat', '$observacao', '$ip', '$sMatricula', now() ) ");
}
else
{
    $oDBase->setMensagem("Falha na gravação da observação (histórico)");
    $oDBase->query("UPDATE historico_observacoes SET compet = '$compinv', siape = '$mat', observacao = '$observacao', ip = '$ip', siaperh = '$sMatricula', registrado_em = now() WHERE compet = '$compinv' and siape = '$mat' ");
}


// instancia homologados
$objTabHomologadosController = new TabHomologadosController();
$objTabHomologadosController->registraHomologacao( $mat, $mes, $ano, $siape_responsavel, $lotacao );

// grava o LOG
registraLog(' concluída alteração (histórico) da matrícula ' . $mat, '', '', 'Histórico');

$oForm->copiaTemporarioParaPonto();

// instancia o banco de dados
$oDBase->query("INSERT INTO control_historico SET ip = '" . $_SERVER['REMOTE_ADDR'] . "', siape_rh = '" . $sMatricula . "', lotacao = '" . $_SESSION['sLotacao'] . "', siape = '" . $mat . "', compet='" . $compinv . "', operacao = 'Gravou alteracoes.', datahora=NOW() ");

// apaga arquivo temporario, se existir
$oDBase->query("DROP TABLE IF EXISTS " . $_SESSION['sHArquivoTemp']);

$aHorasComuns = recalcularHorasComuns($mat, '11/2009');

// finalização das alterações
mensagem('Alteração Histórico realizada com sucesso!', 'historico_frequencia.php');


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
