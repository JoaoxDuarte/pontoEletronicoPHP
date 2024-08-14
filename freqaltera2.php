<?php

include_once("config.php");
include_once("class_form.siape.php");

verifica_permissao("sRH e sTabServidor");

//define a competencia da tabela
$oData = new trata_datasys();
$ano   = $oData->getAno();
$mes   = $oData->getMesAnterior();
$year  = $oData->getAnoAnterior();


## classe para montagem do formulario padrao
#
$oForm = new formSiape();
$oForm->setCaminho("Frequência » Atualizar » Mês em homologação » Alterar Ocorrência");
$oForm->setJS("freqaltera2.js");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");

$oForm->setSubTitulo("Altera&ccedil;&atilde;o de Ocorr&ecirc;ncia");
$oForm->setObservacaoTopo("Utilize essa op&ccedil;&atilde;o para alterar ocorr&ecirc;ncias no m&ecirc;s de homologa&ccedil;&atilde;o.");

$oForm->setSiapeUsuario($_SESSION['sMatricula']);
$oForm->setSiapeNome('mat');
$oForm->setSiapeMes($mes);
$oForm->setSiapeAno($ano);
$oForm->setSiapeYear($year);

$oForm->setSiapeDestino("regfreq6.php");
$oForm->setSiapeValidar("return validar()");
$oForm->exibeForm();
