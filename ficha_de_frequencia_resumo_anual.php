<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.siape.competencia.php");

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
//verifica_permissao( "ponto_servidor" );
verifica_permissao('sRH ou Chefia');

## classe para montagem do formulario padrao
#
$oForm = new formSiapeCompetencia; // instancia o formul�rio
$oForm->setJS("ficha_de_frequencia_resumo_anual.js"); // script extras utilizados pelo formulario
$oForm->setSiapeCompetenciaDestino(""); // pagina de destino (action)
$oForm->setSiapeCompetenciaValidar(""); // script de teste dos dados do formul�rio e envio (onSubmit)

$oForm->setSubTitulo("Consulta Ficha de Frequ&ecirc;ncia por Matr&iacute;cula"); // sub-titulo principal
$oForm->setOnLoad("$('#siape').focus();");


// campos hidden
$oForm->initInputHidden();
$oForm->setInputHidden("anoa", date('Y')); // ano da data atual
$oForm->setInputHidden("opcao", 'pfmat'); // ?????

$oForm->setSiapeNome("siape"); // matricula para consulta
$oForm->setSoAno(true); // TRUE: so solicitar� o ano, sem o m�s
$oForm->setAnoNome("ano"); // ano da consulta
// exibe o formulario
$oForm->exibeForm();
