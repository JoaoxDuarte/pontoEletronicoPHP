<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.competencia.php");

// permissao de acesso
verifica_permissao("sRH");

$qlotacao = $_SESSION['sLotacao'];

## classe para montagem do formulario padrao
#
$oForm = new formCompetencia; // instancia o formul�rio
$oForm->setCaminho('Relat�rio � Frequ�ncia � Para comando � Recesso'); // localizacao deste formulario
$oForm->setSubTitulo("Relat�rio de Servidores que aparecem com Recesso de Fim de Ano n�o compensado"); // sub-titulo principal
$oForm->setOnLoad("$('#periodo').focus();");

$oForm->setObservacaoTopo("<center><font style='font-size: 9;'>Selecionar o per�odo do recesso.</font></center>");

$oForm->setObservacaoBase("<center><font style='font-size: 9;'>A emiss&atilde;o de relat&oacute;rios dever&aacute; ser utilizada apenas para anos de 2018/2019 em diante.</font></center>");

$oForm->setCompetenciaDestino("sisref_relatorio_recesso_nao_compensado_html.php"); // pagina de destino (action)

$oForm->setSoPeriodo(true); // TRUE: so solicitar� o ano, sem o m�s
$oForm->setAnoNome('periodo');
$oForm->setInputPosicao_titulo('topo');

// exibe o formulario
$oForm->exibeFormPeriodoRecesso();
