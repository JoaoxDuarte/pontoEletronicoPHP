<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");

// carrega classe formulario
include_once( "class_form.siape.php" );

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao("sRH");


## classe para montagem do formulario
#
$oForm = new formSiape();
$oForm->setCaminho('Relat�rio � Movimenta��o � Consulta hist�rico');
$oForm->setCSS(_DIR_CSS_ . 'estilos_new_laytou.css');
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setDialogModal();
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setSeparador(30);

// destino e valida��o
$oForm->setJS('sisref_relatorio_consulta_movimentacao_servidor.js');

$oForm->setSubTitulo("Consulta Hist�rico de Movimenta��o do Servidor");

// matr�cula do servidor logado
$oForm->setSiapeUsuario($_SESSION['sMatricula']);

$oForm->setSiapeNome('pSiape');
$oForm->setSiapeTitulo('Informe a matr�cula do servidor/estagi�rio');
$oForm->setSiapeTituloClass('ft_13_001');
$oForm->setSiapeCaixa('800');
$oForm->setSiapeCaixaBorda('0');
$oForm->setSiapeCaixaWidth('790');

$oForm->setInputHidden('saldo', $_REQUEST['saldo']);

$oForm->setSiapeDestino("sisref_relatorio_consulta_historico.php");
$oForm->setSiapeValidar("return validar()");

$oForm->exibeForm();
