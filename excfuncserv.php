<?php

// funcoes de uso geral
include_once( "config.php" );
include_once( "class_form.siape.competencia.php" );

verifica_permissao('sRH e sTabServidor');

// grava em sessao dados do script atual
$_SESSION['sHOrigem_1'] = "excfuncserv.php";
$_SESSION['sHOrigem_2'] = '';
$_SESSION['sHOrigem_3'] = '';
$_SESSION['sHOrigem_4'] = '';


## classe para montagem do formulario padrao
#
$oForm = new formSiapeCompetencia; // instancia o formulário
$oForm->setSoAno(true);
$oForm->setSoMes(true);
$oForm->setJS("excfuncserv.js");
$oForm->setOnLoad("javascript: if($('#matricula')) { $('#matricula').focus() };");

$oForm->setSubTitulo("Vac&acirc;ncia de Ocupante de Fun&ccedil;&atilde;o");

$oForm->setSiapeNome('matricula');
$oForm->setSiapeTitulo('Matrícula SIAPE');

$oForm->setSiapeCompetenciaDestino(""); // pagina de destino (action)
$oForm->exibeForm();

