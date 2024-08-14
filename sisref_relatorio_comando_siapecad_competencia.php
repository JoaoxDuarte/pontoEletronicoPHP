<?php

// funcoes de uso geral
include_once( "config.php" );
include_once( "class_form.competencia.php" );

// permissao de acesso
verifica_permissao("sRH");


## classe para montagem do formulario
#
$oForm = new formCompetencia();
$oForm->setCaminho('Relatórios » Frequência » Para comando » Cobrança');
$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setJS("sisref_relatorio_comando_siapecad_competencia.js");
$oForm->setOnLoad("$('#mes').focus()");

$oForm->setSubTitulo("Relat&oacute;rio de ocorr&ecirc;ncias para lan&ccedil;amento no siapecad");

$oForm->setObservacaoBase("<center><font style='font-size: 9;'>A emiss&atilde;o de relat&oacute;rios dever&aacute; ser utilizada apenas para compet&ecirc;ncias posteriores a 10/2009 em diante.</font></center>");

$oForm->setCompetenciaDestino("sisref_relatorio_comando_siapecad_html.php");
$oForm->setCompetenciaValidar("return validar()");

$oForm->exibeForm();
