<?php

include_once("config.php");
include_once("class_form.siape.php");

verifica_permissao("sRH e sTabServidor");

$_SESSION['inclusaoOrigem']  = "freqincluih.php";
$_SESSION['inclusaoCaminho'] = "Frequência » Atualizar » Mês em homologação » Incluir Ocorrência";

//define a competencia da tabela
$oData = new trata_datasys();
$ano   = $oData->getAno();
$mes   = $oData->getMesAnterior();
$year  = $oData->getAnoAnterior();


## classe para montagem do formulario padrao
#
$oForm = new formSiape();
$oForm->setCaminho($_SESSION['inclusaoCaminho']);
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");

// destino e validação
$oForm->setJS("freqincluih.js");

// Topo do formulário
//
$oForm->setSubTitulo("Inclus&atilde;o de Ocorr&ecirc;ncia");
$oForm->setObservacaoTopo("Utilize essa op&ccedil;&atilde;o para inclus&atilde;o de ocorr&ecirc;ncias no m&ecirc;s em homologa&ccedil;&atilde;o.");

$oForm->setSiapeUsuario($_SESSION['sMatricula']);
$oForm->setSiapeMes($mes);
$oForm->setSiapeAno($ano);
$oForm->setSiapeYear($year);

$oForm->setSiapeDestino("freqinclui2.php");
$oForm->setSiapeValidar("return validar()");
$oForm->exibeForm();
