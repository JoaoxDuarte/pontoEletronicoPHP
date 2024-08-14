<?php

// conexao ao banco de dados, funcoes diversas
include_once( 'config.php' );
include_once( 'class_form.frequencia.php' );
include_once( "src/controllers/TabHomologadosController.php" );

verifica_permissao('sAPS');

// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once( 'ilegal_grava.php' );
// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // enviado via formulario
    $siape                    = anti_injection($_REQUEST['siape']);
    $lotacao                  = anti_injection($_REQUEST['lotacao']);
    $mes                      = anti_injection($_REQUEST['mes2']);
    $ano                      = anti_injection($_REQUEST['ano2']);
    $se_codigo_88888          = anti_injection($_REQUEST['teste']);
    $total_de_registro_no_mes = anti_injection($_REQUEST['teste2']);
    $total_de_dias_no_mes     = anti_injection($_REQUEST['teste3']);
    $se_codigo_99999          = anti_injection($_REQUEST['teste9']);
    $se_codigo_tracos         = anti_injection($_REQUEST['teste_tracos']);
}
else
{
    $dados                    = explode(":|:", base64_decode($dadosorigem));
    $siape                    = $dados[0];
    $lotacao                  = $dados[1];
    $mes                      = $dados[2];
    $ano                      = $dados[3];
    $se_codigo_88888          = $dados[4];
    $total_de_registro_no_mes = $dados[5];
    $total_de_dias_no_mes     = $dados[6];
    $se_codigo_99999          = $dados[7];
    $se_codigo_tracos         = $dados[8];
}

// competência
$comp         = $mes . $ano;
$comp_inverte = $ano . $mes;

$lotacao = ($lotacao == '' ? $_SESSION['sLotacao'] : $dados[1]);

$sMatricula = $_SESSION['sMatricula'];

settype($se_codigo_88888);          // teste        : código de ocorrência 88888 - Registro Parcial
settype($total_de_registro_no_mes); // teste2       : total de registros realizados no mês
settype($total_de_dias_no_mes);     // teste3       : total de dias do mês
settype($se_codigo_99999);          // teste9       : código de ocorrência 99999 - Sem Frequência
settype($se_codigo_tracos);         // teste_tracos : código de ocorrência ----- - Sem Ocorrência informada

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


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
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

// instancia homologados
$objTabHomologadosController = new TabHomologadosController();
$objTabHomologadosController->registraHomologacao( $siape, $mes, $ano, $sMatricula, $lotacao );

// libera memoria
$oDBase->free_result();
$oDBase->close();

// grava o LOG
registraLog(' concluída homologação da matrícula ' . $siape, '', '', 'Homologar');

//Fim da atualização de banco de horas
mensagem('Homologação realizada com sucesso!', 'frequencia_homologar.php?dados=' . $_SESSION['voltar_nivel_0']);


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
