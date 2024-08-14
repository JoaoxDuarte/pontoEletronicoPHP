<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.competencia.php");

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao('estrategica');


## classe para montagem do formulario padrao
#
$oForm = new formCompetencia; // instancia o formul�rio
$oForm->setJS("pesquisa_ip.js"); // script extras utilizados pelo formulario
$oForm->setCompetenciaDestino("regsalterados.php"); // pagina de destino (action)
$oForm->setCompetenciaValidar("javascript:return validar();"); // script de teste dos dados do formul�rio e envio (onSubmit)
$oForm->setSeparador(30);

$oForm->setCaminho('Utilit�rios � Auditoria � Registros alterados � Altera��o de frequ�ncia'); // localizacao deste formulario
$oForm->setSubTitulo("Consulta Servidores com Registro de Frequ�ncia Alterado(s) ou Exclu�do(s)"); // sub-titulo principal
$oForm->setOnLoad("$('#mes').focus();");

// campos hidden
$oForm->initInputHidden();
$oForm->setInputHidden("an", date('Y')); // ano da   data atual
// definicao de campos
$oForm->setMesNome("mes"); // mes da consulta
$oForm->setMesTitulo('M�s');
$oForm->setAnoNome("ano"); // ano da consulta
$oForm->setAnoTitulo('Ano');


// exibe o formulario
$oForm->exibeForm();
