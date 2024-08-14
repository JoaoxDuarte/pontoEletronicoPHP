<?php

// funcoes de uso geral
include_once("config.php");
include_once("class_form.competencia.php");

// permissao de acesso
verifica_permissao("sRH");


## classe para montagem do formulario
#
$oForm = new formCompetencia();
$oForm->setCaminho('Relat�rios � Frequ�ncia � Para comando � Cobran�a');
$oForm->setJSDatePicker();
$oForm->setJS("sisref_relatorio_ocorrencia_nao_compensada_competencia.js?v.0.0.0.0.1");

$oForm->setSubTitulo("Relat�rio de Servidores que aparecem com Ocorr�ncia n�o compensada");

$oForm->setCompetenciaDestino("#");
$oForm->setCompetenciaValidar("javascript:return false;");

$oForm->setObservacaoBase("A emiss&atilde;o de relat&oacute;rios dever&aacute; ser utilizada apenas para compet&ecirc;ncias a partir 10/2009.");

// exibe o formulario
$oForm->exibeForm();
