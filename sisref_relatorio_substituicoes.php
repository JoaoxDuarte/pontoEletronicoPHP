<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.competencia.php");

// permissao de acesso
verifica_permissao("sRH");

// historico de navegacao
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);


## classe para montagem do formulario padrao
#
$oForm = new formCompetencia(); // instancia o formul�rio
$oForm->setCaminho('Relat�rio � Gerencial � Substitui��es � Substitui��es da UPAG'); // localizacao deste formulario
$oForm->setSubTitulo("Relat�rio de Substitui��es na UPAG"); // sub-titulo principal
$oForm->setOnLoad("$('#mes').focus();");

$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setJS("sisref_relatorio_substituicoes.js"); // script extras utilizados pelo formulario

$oForm->setObservacaoTopo("<center><font style='font-size: 9;'>Informe a compet&ecirc;ncia que deseja verificar.</font></center>");

$oForm->setCompetenciaDestino("sisref_relatorio_substituicoes_mes.php"); // pagina de destino (action)
//$oForm->setCompetenciaValidar( "javascript:return validar();" ); // script de teste dos dados do formul�rio e envio (onSubmit)
// exibe o formulario
$oForm->exibeForm();
