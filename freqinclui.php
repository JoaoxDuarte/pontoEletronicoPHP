<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "class_form.siape.php" );

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao('sRH e sTabServidor');

include_once( "frequencia_rh_mes_corrente.php" );

/*
## classe para montagem do formulario padrao
#
$oForm = new formSiape();
$oForm->setCaminho('Frequ�ncia � Atualizar � M�s corrente � Incluir ocorr�ncia');
//$oForm->setJS("freqinclui.js");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setSeparador(30);

$oForm->setSubTitulo("Inclus�o de Ocorr�ncia");
$oForm->setObservacaoTopo("Utilize essa op&ccedil;&atilde;o para inclus&atilde;o de ocorr&ecirc;ncias no m&ecirc;s corrente");

//define a competencia
$oForm->setSiapeAno(date('Y'));
$oForm->setSiapeMes(date('m'));
$oForm->setSiapeYear(date('Y'));
$oForm->setSiapeCmd("1");

// matr�cula do servidor logado
$oForm->setSiapeUsuario($_SESSION['sMatricula']);

$oForm->setSiapeNome('pSiape');
$oForm->setSiapeTitulo('Matr�cula do servidor/estagi�rio');
$oForm->setSiapeTituloClass('ft_13_001');
$oForm->setSiapeCaixa('800');
$oForm->setSiapeCaixaBorda('0');
$oForm->setSiapeCaixaWidth('790');

$oForm->setSiapeDestino("freqinclui2.php");
$oForm->setSiapeValidar("return validar()");
$oForm->exibeForm();
*/
